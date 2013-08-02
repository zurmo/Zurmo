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
     * Class that builds demo campaigns.
     */
    class CampaignsDemoDataMaker extends MarketingDemoDataMaker
    {
        protected $index;

        protected $seedData;

        public static function getDependencies()
        {
            return array('marketingLists');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("MarketingList")');

            $campaigns = array();
            for ($this->index = 0; $this->index < 10; $this->index++)
            {
                $campaign                       = new Campaign();
                $this->populateModel($campaign);
                $campaign->marketingList        = $demoDataHelper->getRandomByModelName('MarketingList');
                $campaign->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $saved                          = $campaign->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                $campaign = Campaign::getById($campaign->id);
                ReadPermissionsOptimizationUtil::
                    securableItemGivenPermissionsForGroup($campaign, Group::getByName(Group::EVERYONE_GROUP_NAME));
                $campaign->save();
                $campaigns[]                    = $campaign->id;
            }
            $demoDataHelper->setRangeByModelName('Campaign', $campaigns[0], $campaigns[count($campaigns)-1]);
        }

        public function populateModel(& $model)
        {
            assert('$model instanceof Campaign');
            parent::populateModel($model);
            if (empty($this->seedData))
            {
                $this->seedData =  ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('CampaignsModule',
                                                                                                'Campaign');
            }
            $statusKeys                     = array_keys(Campaign::getStatusDropDownArray());
            $timestamp                      = time();
            $model->name                    = $this->seedData['name'][$this->index];
            $model->subject                 = $this->seedData['subject'][$this->index];
            $model->status                  = RandomDataUtil::getRandomValueFromArray($statusKeys);
            if (!(rand() % 2))
            {
                $timestamp                 += rand(500, 5000);
            }
            $model->sendOnDateTime          = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            $model->supportsRichText        = (rand() % 2);
            $model->htmlContent             = $this->seedData['htmlContent'][$this->index];
            $model->textContent             = $this->seedData['textContent'][$this->index];
            $model->fromName                = $this->seedData['fromName'][$this->index];
            $model->fromAddress             = $this->seedData['fromAddress'][$this->index];
            $model->enableTracking          = (rand() % 2);
            $this->populateMarketingModelWithFiles($model);
        }
    }
?>