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
    class CampaignGenerateDueCampaignItemsJobTest extends ZurmoBaseTest
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

        public function testGetDisplayName()
        {
            $displayName                = CampaignGenerateDueCampaignItemsJob::getDisplayName();
            $this->assertEquals('Generate campaign items', $displayName);
        }

        public function testGetType()
        {
            $type                       = CampaignGenerateDueCampaignItemsJob::getType();
            $this->assertEquals('CampaignGenerateDueCampaignItems', $type);
        }

        public function testGetRecommendedRunFrequencyContent()
        {
            $recommendedRunFrequency    = CampaignGenerateDueCampaignItemsJob::getRecommendedRunFrequencyContent();
            $this->assertEquals('Every hour', $recommendedRunFrequency);
        }

        public function testRunWithoutAnyCampaigns()
        {
            $campaigns              = Campaign::getAll();
            $this->assertEmpty($campaigns);
            $job                    = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
        }

        /**
         * @depends testRunWithoutAnyCampaigns
         */
        public function testRunWithDueButNoActiveCampaigns()
        {
            $marketingList          = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            CampaignTestHelper::createCampaign('Processing',
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
            CampaignTestHelper::createCampaign('Incomplete',
                                                'subject',
                                                'text Content',
                                                'Html Content',
                                                null,
                                                null,
                                                null,
                                                Campaign::STATUS_PAUSED,
                                                null,
                                                null,
                                                $marketingList);
            CampaignTestHelper::createCampaign('Paused',
                                                'subject',
                                                'text Content',
                                                'Html Content',
                                                null,
                                                null,
                                                null,
                                                Campaign::STATUS_PAUSED,
                                                null,
                                                null,
                                                $marketingList);
            $this->assertEmpty(CampaignItem::getAll());
            $job                    = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
            $this->assertEmpty(CampaignItem::getAll());
        }

        /**
         * @depends testRunWithDueButNoActiveCampaigns
         */
        public function testRunWithNonDueActiveCampaigns()
        {
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 02');
            $tenDaysFromNowTimestamp    = time() + 60*60*24*10;
            $tenDaysFromNowDateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime($tenDaysFromNowTimestamp);
            CampaignTestHelper::createCampaign('Active And Non Due',
                                                'subject',
                                                'text Content',
                                                'Html Content',
                                                null,
                                                null,
                                                null,
                                                Campaign::STATUS_ACTIVE,
                                                $tenDaysFromNowDateTime,
                                                null,
                                                $marketingList);
            $this->assertEmpty(CampaignItem::getAll());
            $job                    = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
            $this->assertEmpty(CampaignItem::getAll());
        }

        /**
         * @depends testRunWithNonDueActiveCampaigns
         */
        public function testRunWithDueActiveCampaignsWithNonMembers()
        {
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 03');
            CampaignTestHelper::createCampaign('Active, Due But No Members',
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
            $this->assertEmpty(CampaignItem::getAll());
            $job                    = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
            $this->assertEmpty(CampaignItem::getAll());
        }

        /**
         * @depends testRunWithDueActiveCampaignsWithNonMembers
         */
        public function testRunWithDueActiveCampaignsWithMembers()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 04');
            $marketingListId    = $marketingList->id;
            $contact1           = ContactTestHelper::createContactByNameForOwner('campaignContact 01', $this->user);
            $contact2           = ContactTestHelper::createContactByNameForOwner('campaignContact 02', $this->user);
            $contact3           = ContactTestHelper::createContactByNameForOwner('campaignContact 03', $this->user);
            $contact4           = ContactTestHelper::createContactByNameForOwner('campaignContact 04', $this->user);
            $contact5           = ContactTestHelper::createContactByNameForOwner('campaignContact 05', $this->user);
            $processed          = 0;
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact2);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact3);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact4);
            MarketingListMemberTestHelper::createMarketingListMember($processed, $marketingList, $contact5);
            $marketingList->forgetAll();

            $marketingList      = MarketingList::getById($marketingListId);
            CampaignTestHelper::createCampaign('Active, Due With Members',
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
            $this->assertEmpty(CampaignItem::getAll());
            $job                = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
            $campaign           = Campaign::getByName('Active, Due With Members');
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign[0]->status);
            $allCampaignItems   = CampaignItem::getAll();
            $this->assertNotEmpty(CampaignItem::getAll());
            $this->assertCount(5, $allCampaignItems);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaign[0]->id);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(5, $campaignItems);
        }

        /**
         * @depends testRunWithDueActiveCampaignsWithMembers
         */
        public function testRunWithDueActiveCampaignsWithCustomBatchSize()
        {
            $this->purgeAllCampaigns();
            $contactIds         = array();
            $marketingListIds   = array();
            $campaignIds        = array();
            for ($index = 6; $index < 9; $index++)
            {
                $contact        = ContactTestHelper::createContactByNameForOwner('campaignContact 0' . $index,
                    $this->user);
                $contactIds[] = $contact->id;
                $contact->forgetAll();
            }
            for ($index = 5; $index < 10; $index++)
            {
                $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 0' . $index);
                $marketingListId    = $marketingList->id;
                $marketingListIds[] = $marketingListId;
                foreach ($contactIds as $contactId)
                {
                    $contact        = Contact::getById($contactId);
                    $unsubscribed   = (rand(10, 20) % 2);
                    MarketingListMemberTestHelper::createMarketingListMember($unsubscribed, $marketingList, $contact);
                }
                $marketingList->forgetAll();
                $marketingList      = MarketingList::getById($marketingListId);
                $campaignSuffix     = substr($marketingList->name, -2);
                $campaign           = CampaignTestHelper::createCampaign('campaign ' . $campaignSuffix,
                                                                            'subject ' . $campaignSuffix,
                                                                            'text ' . $campaignSuffix,
                                                                            'html ' . $campaignSuffix,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            null,
                                                                            $marketingList);
                $this->assertNotNull($campaign);
                $campaignIds[]      = $campaign->id;
                $campaign->forgetAll();
            }

            foreach ($campaignIds as $campaignId)
            {
                $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
                $this->assertEmpty($campaignItems);
            }

            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(1);
            $job    = new CampaignGenerateDueCampaignItemsJob();
            $this->assertTrue($job->run());
            foreach ($campaignIds as $index => $campaignId)
            {
                $campaign           = Campaign::getById($campaignId);
                $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
                if ($index === 0)
                {
                    $this->assertNotEmpty($campaignItems);
                    $this->assertCount(3, $campaignItems);
                    $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
                }
                else
                {
                    $this->assertEmpty($campaignItems);
                    $this->assertEquals(Campaign::STATUS_ACTIVE, $campaign->status);
                }
            }

            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(null);
            $this->assertTrue($job->run());
            foreach ($campaignIds as $campaignId)
            {
                $campaign           = Campaign::getById($campaignId);
                $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
                $this->assertNotEmpty($campaignItems);
                $this->assertCount(3, $campaignItems);
                $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
            }
            // TODO: @Shoaibi: Medium: Add tests for the other campaign type.
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