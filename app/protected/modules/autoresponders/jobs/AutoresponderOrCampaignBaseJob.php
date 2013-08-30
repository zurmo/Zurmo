<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    abstract class AutoresponderOrCampaignBaseJob extends BaseJob
    {
        protected $modelIdentifiersForForgottenValidators = array();

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            $className  = get_called_class();
            $type       = substr($className, 0, -3);
            return $type;
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Every hour');
        }

        protected function resolveBatchSize()
        {
            return AutoresponderOrCampaignBatchSizeConfigUtil::getBatchSize();
        }

        /**
         * Not pretty, but gets the job done. Solves memory leak problem.
         * @param AutoresponderItem or CampaignItem $item
         */
        protected function runGarbageCollection($item)
        {
            $item->contact->primaryEmail->forgetValidators();
            $item->contact->forgetValidators();
            $item->emailMessage->content->forgetValidators();
            $item->emailMessage->sender->forgetValidators();
            $item->emailMessage->forgetValidators();
            $this->modelIdentifiersForForgottenValidators[$item->contact->primaryEmail->getModelIdentifier()] = true;
            $this->modelIdentifiersForForgottenValidators[$item->contact->getModelIdentifier()]               = true;
            $this->modelIdentifiersForForgottenValidators[$item->emailMessage->content->getModelIdentifier()] = true;
            $this->modelIdentifiersForForgottenValidators[$item->emailMessage->sender->getModelIdentifier()]  = true;
            $this->modelIdentifiersForForgottenValidators[$item->emailMessage->getModelIdentifier()]          = true;
        }

        protected function forgetModelsWithForgottenValidators()
        {
            foreach ($this->modelIdentifiersForForgottenValidators as $modelIdentifier => $notUsed)
            {
                RedBeanModelsCache::forgetModelByIdentifier($modelIdentifier);
            }
        }

        protected function addMaxmimumProcessingCountMessage($modelsProcessedCount, $startingMemoryUsage)
        {
            assert('is_int($modelsProcessedCount)');
            $endingMemoryUsage = memory_get_usage();
            if ($modelsProcessedCount == 0)
            {
                $costPerModel =  0;
            }
            else
            {
                $costPerModel = ($endingMemoryUsage - $startingMemoryUsage) / $modelsProcessedCount;
            }
            $message = Zurmo::t('CampaignsModule', 'Models processed: {count} Memory cost per model: {cost}',
                                                    array('{count}' => $modelsProcessedCount,
                                                          '{cost}'  => round($costPerModel, 2)));
            $this->getMessageLogger()->addInfoMessage($message);
            $message = Zurmo::t('CampaignsModule', 'Final memory usage: {usage}', array('{usage}' => $endingMemoryUsage));
            $this->getMessageLogger()->addInfoMessage($message);
        }
    }
?>