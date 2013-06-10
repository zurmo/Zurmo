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
    class CampaignsUtilTest extends ZurmoBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testMarkProcessedCampaignsAsCompleted()
        {
            $contact                                = ContactTestHelper::createContactByNameForOwner('contact 01',
                                                                                                        $this->user);
            $marketingList                          = MarketingListTestHelper::populateMarketingListByName(
                                                                                                    'marketingList 01');
            $campaignActiveAllItemsProcessed        = CampaignTestHelper::createCampaign('Active Processed',
                                                                                        'subject',
                                                                                        'text Content',
                                                                                        'Html Content',
                                                                                        null,
                                                                                        null,
                                                                                        null,
                                                                                        Campaign::STATUS_ACTIVE,
                                                                                        null,
                                                                                        null,
                                                                                        $marketingList);
            $campaignActiveAllItemsProcessedId      = $campaignActiveAllItemsProcessed->id;
            CampaignItemTestHelper::createCampaignItem(1,
                                                        $campaignActiveAllItemsProcessed,
                                                        $contact);
            $campaignProcessingAllItemsProcessed    = CampaignTestHelper::createCampaign('Processing Processed',
                                                                                        'subject',
                                                                                        'text Content',
                                                                                        'Html Content',
                                                                                        null,
                                                                                        null,
                                                                                        null,
                                                                                        Campaign::STATUS_PROCESSING,
                                                                                        null,
                                                                                        null,
                                                                                        $marketingList);
            $campaignProcessingAllItemsProcessedId      = $campaignProcessingAllItemsProcessed->id;
            CampaignItemTestHelper::createCampaignItem(1,
                                                        $campaignProcessingAllItemsProcessed,
                                                        $contact);
            $campaignProcessingNoItemsProcessed    = CampaignTestHelper::createCampaign('Processing Not Processed',
                                                                                        'subject',
                                                                                        'text Content',
                                                                                        'Html Content',
                                                                                        null,
                                                                                        null,
                                                                                        null,
                                                                                        Campaign::STATUS_PROCESSING,
                                                                                        null,
                                                                                        null,
                                                                                        $marketingList);
            $campaignProcessingNoItemsProcessedId      = $campaignProcessingNoItemsProcessed->id;
            CampaignItemTestHelper::createCampaignItem(0,
                                                        $campaignProcessingNoItemsProcessed,
                                                        $contact);
            $campaignActiveAllItemsProcessed->forgetAll();
            $campaignProcessingAllItemsProcessed->forgetAll();
            $campaignProcessingNoItemsProcessed->forgetAll();
            $this->assertTrue(CampaignsUtil::markProcessedCampaignsAsCompleted());
            $campaignActiveAllItemsProcessed            = Campaign::getById($campaignActiveAllItemsProcessedId);
            $this->assertNotNull($campaignActiveAllItemsProcessed);
            $this->assertEquals(Campaign::STATUS_ACTIVE, $campaignActiveAllItemsProcessed->status);
            $campaignProcessingAllItemsProcessed        = Campaign::getById($campaignProcessingAllItemsProcessedId);
            $this->assertNotNull($campaignProcessingAllItemsProcessed);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaignProcessingAllItemsProcessed->status);
            $campaignProcessingNoItemsProcessed         = Campaign::getById($campaignProcessingNoItemsProcessedId);
            $this->assertNotNull($campaignProcessingNoItemsProcessed);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaignProcessingNoItemsProcessed->status);
        }

        /**
         * @depends testMarkProcessedCampaignsAsCompleted
         */
        public function testMarkProcessedCampaignsAsCompletedWithCustomPageSize()
        {
            $this->purgeAllCampaigns();
            $contact            = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList      = MarketingListTestHelper::populateMarketingListByName('marketingList 02');
            $campaign01         = CampaignTestHelper::createCampaign('campaign 01',
                                                                        'subject',
                                                                        'text Content',
                                                                        'Html Content',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::STATUS_PROCESSING,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign01);
            $campaign01Id   = $campaign01->id;
            CampaignItemTestHelper::createCampaignItem(1, $campaign01, $contact);
            $campaign02         = CampaignTestHelper::createCampaign('campaign 02',
                                                                        'subject',
                                                                        'text Content',
                                                                        'Html Content',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::STATUS_PROCESSING,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign02);
            $campaign02Id   = $campaign02->id;
            CampaignItemTestHelper::createCampaignItem(1, $campaign02, $contact);
            $campaign03         = CampaignTestHelper::createCampaign('campaign 03',
                                                                        'subject',
                                                                        'text Content',
                                                                        'Html Content',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        Campaign::STATUS_PROCESSING,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign03);
            $campaign03Id   = $campaign03->id;

            $campaign01->forgetAll();
            $campaign02->forgetAll();
            $campaign03->forgetAll();
            $this->assertTrue(CampaignsUtil::markProcessedCampaignsAsCompleted(1));
            $campaign01 = Campaign::getById($campaign01Id);
            $this->assertNotNull($campaign01);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaign01->status);
            $campaign02 = Campaign::getById($campaign02Id);
            $this->assertNotNull($campaign02);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign02->status);
            $campaign03 = Campaign::getById($campaign03Id);
            $this->assertNotNull($campaign03);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign03->status);

            $campaign01->forgetAll();
            $campaign02->forgetAll();
            $campaign03->forgetAll();
            $this->assertTrue(CampaignsUtil::markProcessedCampaignsAsCompleted());
            $campaign01 = Campaign::getById($campaign01Id);
            $this->assertNotNull($campaign01);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaign01->status);
            $campaign02 = Campaign::getById($campaign02Id);
            $this->assertNotNull($campaign02);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaign02->status);
            $campaign03 = Campaign::getById($campaign03Id);
            $this->assertNotNull($campaign03);
            $this->assertEquals(Campaign::STATUS_COMPLETED, $campaign03->status);
        }

        protected function purgeAllCampaigns()
        {
            $campaigns = Campaign::getAll();
            foreach ($campaigns as $campaign)
            {
                $campaign->delete();
            }
        }
    }
?>