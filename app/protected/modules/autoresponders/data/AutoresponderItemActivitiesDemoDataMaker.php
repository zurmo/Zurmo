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

    /**
     * Class that builds demo autoresponderItemActivities.
     */
    class AutoresponderItemActivitiesDemoDataMaker extends EmailMessageActivitiesDemoDataMaker
    {
        protected $ratioToLoad = 3;

        public static function getDependencies()
        {
            return array('contacts', 'emailMessages');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("Contact")');
            assert('$demoDataHelper->isSetRange("EmailMessageUrl")');
            assert('$demoDataHelper->isSetRange("AutoresponderItem")');

            $activities = array();
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $activity                       = new AutoresponderItemActivity();
                $autoresponderItem              = $demoDataHelper->getRandomByModelName('AutoresponderItem');
                $activity->autoresponderItem    = $autoresponderItem;
                $activity->person               = $activity->autoresponderItem->contact;
                if (rand() % 4)
                {
                    $emailMessageUrl                = $demoDataHelper->getRandomByModelName('EmailMessageUrl');
                    $activity->emailMessageUrl      = $emailMessageUrl;
                }

                $this->populateModel($activity);
                $saved                          = $activity->save();
                assert('$saved');
                $activities[]                   = $activity->id;
            }
            $demoDataHelper->setRangeByModelName('AutoresponderItemActivity', $activities[0], $activities[count($activities)-1]);
            $this->populateMarketingItems('AutoresponderItem', $activity->autoresponderItem->autoresponder->subject);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof AutoresponderItemActivity');
            parent::populateModel($model);
            $model->quantity        = rand(10, 100);
            $timestamp              = time() - rand(100, 1000);
            $model->latestDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            $model->latestSourceIP  = '10.11.12.13';
            if ($model->emailMessageUrl->id > 0)
            {
                $model->type        = AutoresponderItemActivity::TYPE_CLICK;
            }
            elseif (mt_rand(1, 10) < 8)
            {
                $model->type        = AutoresponderItemActivity::TYPE_OPEN;
            }
            else
            {
                $model->type        = AutoresponderItemActivity::TYPE_BOUNCE;
            }
        }
    }
?>