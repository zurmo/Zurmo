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
    class CampaignQueueMessagesInOutboxJobTest extends ZurmoBaseTest
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
            $displayName                = CampaignQueueMessagesInOutboxJob::getDisplayName();
            $this->assertEquals('Process campaign messages', $displayName);
        }

        public function testGetType()
        {
            $type                       = CampaignQueueMessagesInOutboxJob::getType();
            $this->assertEquals('CampaignQueueMessagesInOutbox', $type);
        }

        public function testGetRecommendedRunFrequencyContent()
        {
            $recommendedRunFrequency    = CampaignQueueMessagesInOutboxJob::getRecommendedRunFrequencyContent();
            $this->assertEquals('Every hour', $recommendedRunFrequency);
        }

        public function testRunWithoutAnyItems()
        {
            $campaignItems              = CampaignItem::getAll();
            $this->assertEmpty($campaignItems);
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $this->assertTrue($job->run());
        }

        /**
         * @depends testRunWithoutAnyItems
         */
        public function testRunWithoutContact()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                                                                                'subject',
                                                                                'text Content',
                                                                                'Html Content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                0,
                                                                                $marketingList);
            $processed                  = 0;
            CampaignItemTestHelper::createCampaignItem($processed, $campaign);
            $this->assertTrue($job->run());
            $campaignItems         = CampaignItem::getAll();
            $this->assertEmpty($campaignItems);
        }

        /**
         * @depends testRunWithoutContact
         */
        public function testRunWithContactNotContainingPrimaryEmail()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 02',
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

            $processed                  = 0;
            CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->assertTrue($job->run());
            $campaignItems         = CampaignItem::getAll();
            $this->assertCount(1, $campaignItems);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(1, $campaign->id);
            $this->assertCount(1, $campaignItemsProcessed);
        }

        /**
         * @depends testRunWithContactNotContainingPrimaryEmail
         */
        public function testRunWithContactContainingPrimaryEmail()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 03',
                                                                                'subject',
                                                                                'text Content',
                                                                                'Html Content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                0,
                                                                                $marketingList);

            $processed                  = 0;
            CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->assertTrue($job->run());
            $campaignItems         = CampaignItem::getAll();
            $this->assertCount(2, $campaignItems);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(1, $campaign->id);
            $this->assertCount(1, $campaignItemsProcessed);
        }

        /**
         * @depends testRunWithContactContainingPrimaryEmail
         */
        public function testRunWithMarketingListContainingCustomFromNameAndFromAddress()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 04',
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
            $processed                  = 0;
            CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->assertTrue($job->run());
            $campaignItems         = CampaignItem::getAll();
            $this->assertCount(3, $campaignItems);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(1, $campaign->id);
            $this->assertCount(1, $campaignItemsProcessed);
        }

        /**
         * @depends testRunWithMarketingListContainingCustomFromNameAndFromAddress
         */
        public function testRunWithInvalidMergeTags()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 05',
                                                                                'subject',
                                                                                '[[TEXT^CONTENT]]',
                                                                                '[[HTML^CONTENT]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                0,
                                                                                $marketingList,
                                                                                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->assertFalse($job->run());
            $this->assertEquals('Provided content contains few invalid merge tags.', $job->getErrorMessage());
            $campaignItems         = CampaignItem::getAll();
            $this->assertCount(4, $campaignItems);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId( 1, $campaign->id);
            $this->assertCount(0, $campaignItemsProcessed);
            $this->assertTrue($campaignItem->delete()); // Need to get rid of this so it doesn't interfere with next test.
        }

        /**
         * @depends testRunWithInvalidMergeTags
         */
        public function testRunWithValidMergeTags()
        {
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 06',
                                                                                'subject',
                                                                                '[[FIRST^NAME]]',
                                                                                '[[LAST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->assertTrue($job->run());
            $campaignItems         = CampaignItem::getAll();
            $this->assertCount(4, $campaignItems);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(
                                                                                            1,
                                                                                            $campaign->id);
            $this->assertCount(1, $campaignItemsProcessed);
        }

        /**
         * @depends testRunWithValidMergeTags
         */
        public function testRunWithCustomBatchSize()
        {
            $unprocessedItems           = CampaignItem::getByProcessed(0);
            $this->assertEmpty($unprocessedItems);
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 07',
                                                                                            'description goes here',
                                                                                            'fromName',
                                                                                            'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 07',
                                                                                'subject',
                                                                                '[[FIRST^NAME]]',
                                                                                '[[LAST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            for ($i = 0; $i < 10; $i++)
            {
                $processed                  = 0;
                CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            }
            $unprocessedItems               = CampaignItem::getByProcessedAndCampaignId(
                                                                                    0,
                                                                                    $campaign->id);
            $this->assertCount(10, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(5);
            $this->assertTrue($job->run());
            $unprocessedItems               = CampaignItem::getByProcessedAndCampaignId(
                                                                                    0,
                                                                                    $campaign->id);
            $this->assertCount(5, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(3);
            $this->assertTrue($job->run());
            $unprocessedItems               = CampaignItem::getByProcessedAndCampaignId(
                                                                                        0,
                                                                                        $campaign->id);
            $this->assertCount(2, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(10);
            $this->assertTrue($job->run());
            $unprocessedItems               = CampaignItem::getByProcessedAndCampaignId(
                                                                                        0,
                                                                                        $campaign->id);
            $this->assertCount(0, $unprocessedItems);
        }

        /**
         * @depends testRunWithCustomBatchSize
         */
        public function testRunWithContactContainingPrimaryEmailOptedOut()
        {
            $unprocessedItems           = CampaignItem::getByProcessed(0);
            $this->assertEmpty($unprocessedItems);
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $email->optOut              = true;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 07', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 08',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 08',
                                                                                'subject',
                                                                                '[[FIRST^NAME]]',
                                                                                '[[LAST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $unprocessedItems           = CampaignItem::getByProcessedAndCampaignId($processed, $campaign->id);
            $this->assertCount(1, $unprocessedItems);
            $this->assertTrue($job->run());
            $unprocessedItems           = CampaignItem::getByProcessedAndCampaignId($processed, $campaign->id);
            $this->assertCount(0, $unprocessedItems);
            $personId                   = $contact->getClassId('Person');
            $activities                 = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                    CampaignItemActivity::TYPE_SKIP,
                                                                                    $campaignItem->id,
                                                                                    $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
        }
    }
?>