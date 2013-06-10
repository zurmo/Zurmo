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
    class AutoresponderQueueMessagesInOutboxJobTest extends ZurmoBaseTest
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
            $displayName                = AutoresponderQueueMessagesInOutboxJob::getDisplayName();
            $this->assertEquals('Process autoresponder messages', $displayName);
        }

        public function testGetType()
        {
            $type                       = AutoresponderQueueMessagesInOutboxJob::getType();
            $this->assertEquals('AutoresponderQueueMessagesInOutbox', $type);
        }

        public function testGetRecommendedRunFrequencyContent()
        {
            $recommendedRunFrequency    = AutoresponderQueueMessagesInOutboxJob::getRecommendedRunFrequencyContent();
            $this->assertEquals('Every hour', $recommendedRunFrequency);
        }

        public function testRunWithoutAnyItems()
        {
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertEmpty($autoresponderItems);
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $this->assertTrue($job->run());
        }

        /**
         * @depends testRunWithoutAnyItems
         */
        public function testRunWithoutContact()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 01');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 01',
                                                                                        'text content',
                                                                                        'html content',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        false,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder);
            $this->assertTrue($job->run());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertEmpty($autoresponderItems);
        }

        /**
         * @depends testRunWithoutContact
         */
        public function testRunWithContactNotContainingPrimaryEmail()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 02',
                                                                                        'text content',
                                                                                        'html content',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        true,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder, $contact);
            $this->assertTrue($job->run());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(1, $autoresponderItems);
            $autoresponderItemsProcessed = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            1,
                                                                                            $autoresponder->id);
            $this->assertCount(1, $autoresponderItemsProcessed);
        }

        /**
         * @depends testRunWithContactNotContainingPrimaryEmail
         */
        public function testRunWithContactContainingPrimaryEmail()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 03',
                                                                                        'text content',
                                                                                        'html content',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        false,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder, $contact);
            $this->assertTrue($job->run());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(2, $autoresponderItems);
            $autoresponderItemsProcessed = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            1,
                                                                                            $autoresponder->id);
            $this->assertCount(1, $autoresponderItemsProcessed);
        }

        /**
         * @depends testRunWithContactContainingPrimaryEmail
         */
        public function testRunWithMarketingListContainingCustomFromNameAndFromAddress()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 04',
                                                                                        'text content',
                                                                                        'html content',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        true,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder, $contact);
            $this->assertTrue($job->run());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(3, $autoresponderItems);
            $autoresponderItemsProcessed = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            1,
                                                                                            $autoresponder->id);
            $this->assertCount(1, $autoresponderItemsProcessed);
        }

        /**
         * @depends testRunWithMarketingListContainingCustomFromNameAndFromAddress
         */
        public function testRunWithInvalidMergeTags()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 05',
                                                                                            '[[TEXT^CONTENT]]',
                                                                                            '[[HTML^CONTENT]]',
                                                                                            1,
                                                                                            Autoresponder::OPERATION_SUBSCRIBE,
                                                                                            false,
                                                                                            $marketingList,
                                                                                            false);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                $processDateTime,
                                                                                                $autoresponder,
                                                                                                $contact);
            $this->assertFalse($job->run());
            $this->assertEquals('Provided content contains few invalid merge tags.', $job->getErrorMessage());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(4, $autoresponderItems);
            $autoresponderItemsProcessed = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            1,
                                                                                            $autoresponder->id);
            $this->assertCount(0, $autoresponderItemsProcessed);
            $this->assertTrue($autoresponderItem->delete()); // Need to get rid of this so it doesn't interfere with next test.
        }

        /**
         * @depends testRunWithInvalidMergeTags
         */
        public function testRunWithValidMergeTags()
        {
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 06',
                                                                                        '[[FIRST^NAME]]',
                                                                                        '[[LAST^NAME]]',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        true,
                                                                                        $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time()-10);
            AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder, $contact);
            $this->assertTrue($job->run());
            $autoresponderItems         = AutoresponderItem::getAll();
            $this->assertCount(4, $autoresponderItems);
            $autoresponderItemsProcessed = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            1,
                                                                                            $autoresponder->id);
            $this->assertCount(1, $autoresponderItemsProcessed);
        }

        /**
         * @depends testRunWithValidMergeTags
         */
        public function testRunWithCustomBatchSize()
        {
            $unprocessedItems           = AutoresponderItem::getByProcessed(0);
            $this->assertEmpty($unprocessedItems);
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 07',
                                                                                            'description goes here',
                                                                                            'fromName',
                                                                                            'from@domain.com');
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 07',
                                                                                        '[[FIRST^NAME]]',
                                                                                        '[[LAST^NAME]]',
                                                                                        1,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        true,
                                                                                        $marketingList);
            for ($i = 0; $i < 10; $i++)
            {
                $processed                  = 0;
                $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - rand(10, 500));
                AutoresponderItemTestHelper::createAutoresponderItem($processed, $processDateTime, $autoresponder, $contact);
            }
            $unprocessedItems               = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                    0,
                                                                                    $autoresponder->id);
            $this->assertCount(10, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(5);
            $this->assertTrue($job->run());
            $unprocessedItems               = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                    0,
                                                                                    $autoresponder->id);
            $this->assertCount(5, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(3);
            $this->assertTrue($job->run());
            $unprocessedItems               = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        0,
                                                                                        $autoresponder->id);
            $this->assertCount(2, $unprocessedItems);
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize(10);
            $this->assertTrue($job->run());
            $unprocessedItems               = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        0,
                                                                                        $autoresponder->id);
            $this->assertCount(0, $unprocessedItems);
        }

        /**
         * @depends testRunWithCustomBatchSize
         */
        public function testRunWithContactContainingPrimaryEmailOptedOut()
        {
            $unprocessedItems           = AutoresponderItem::getByProcessed(0);
            $this->assertEmpty($unprocessedItems);
            $job                        = new AutoresponderQueueMessagesInOutboxJob();
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
            $autoresponder              = AutoresponderTestHelper::createAutoresponder('subject 08',
                                                                                    '[[FIRST^NAME]]',
                                                                                    '[[LAST^NAME]]',
                                                                                    1,
                                                                                    Autoresponder::OPERATION_SUBSCRIBE,
                                                                                    true,
                                                                                    $marketingList);
            $processed                  = 0;
            $processDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - rand(10, 500));
            $autoresponderItem          = AutoresponderItemTestHelper::createAutoresponderItem($processed,
                                                                                                    $processDateTime,
                                                                                                    $autoresponder,
                                                                                                    $contact);
            $unprocessedItems           = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                                    0,
                                                                                                    $autoresponder->id);
            $this->assertCount(1, $unprocessedItems);
            $this->assertTrue($job->run());
            $unprocessedItems               = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                                    0,
                                                                                                    $autoresponder->id);
            $this->assertCount(0, $unprocessedItems);
            $personId                   = $contact->getClassId('Person');
            $activities                = AutoresponderItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                AutoresponderItemActivity::TYPE_SKIP,
                                                                                $autoresponderItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
        }
    }
?>