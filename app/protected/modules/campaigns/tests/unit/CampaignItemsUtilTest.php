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
    class CampaignItemsUtilTest extends ZurmoBaseTest
    {
        // We don't need to add separate tests for tracking scenarios here because we have already gained more than
        //  sufficient coverage in CampaignItemActivityUtilTest and EmailMessageActivityUtilTest for those.
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

        /**
         * Do not throw exception. That means it passes
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyHtml()
        {
            $html = "[[FIRST^NAME]], You are receiving this email";
            $text = null;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                'subject 01',
                $text,
                $html,
                null,
                null,
                null,
                null,
                null,
                null,
                $marketingList,
                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyHtml
         * Do not throw exception. That means it passes
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyText()
        {
            $text = "[[FIRST^NAME]], You are receiving this email";
            $html = null;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                'subject 01',
                $text,
                $html,
                null,
                null,
                null,
                null,
                null,
                null,
                $marketingList,
                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyText
         * @expectedException NotFoundException
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenNoContactIsAvailable()
        {
            $campaignItem          = new CampaignItem();
            CampaignItemsUtil::processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenNoContactIsAvailable
         * @expectedException NotSupportedException
         * @expectedExceptionMessage Provided content contains few invalid merge tags
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTags()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                                                                                'subject 01',
                                                                                '[[TEXT^CONTENT]]',
                                                                                '[[HTML^CONTENT]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList,
                                                                                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTags
         */
        public function testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 02',
                                                                                'subject 02',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                0,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertNull($emailMessage->subject);
            $this->assertNull($emailMessage->content->textContent);
            $this->assertNull($emailMessage->content->htmlContent);
            $this->assertNull($emailMessage->sender->fromAddress);
            $this->assertNull($emailMessage->sender->fromName);
            $this->assertEquals(0, $emailMessage->recipients->count());
        }

        /**
         * @depends testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasPrimaryEmail()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 03',
                                                                                'subject 03',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);

            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertTrue(strpos($emailMessage->content->textContent, $campaign->textContent) !== false);
            $this->assertTrue(strpos($emailMessage->content->textContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertTrue(strpos($emailMessage->content->htmlContent, $campaign->htmlContent) !== false);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $userToSendMessagesFrom     = BaseJobControlUserConfigUtil::getUserToRunAs();
            $defaultFromAddress         = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $defaultFromName            = strval($userToSendMessagesFrom);
            $this->assertEquals($defaultFromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($defaultFromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueCampaignItemWithCustomFromAddressAndFromName()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 04',
                                                                                'subject 04',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                0,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertTrue(strpos($emailMessage->content->textContent, $campaign->textContent) !== false);
            $this->assertTrue(strpos($emailMessage->content->textContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertTrue(strpos($emailMessage->content->htmlContent, $campaign->htmlContent) !== false);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithCustomFromAddressAndFromName
         */
        public function testProcessDueCampaignItemWithValidMergeTags()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 05',
                                                                                'subject 05',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertTrue(strpos($emailMessage->content->textContent, 'Dr. contact 05 contact 05son') !== false);
            $this->assertTrue(strpos($emailMessage->content->textContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '<b>contact 05son</b>, contact 05') !== false);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithValidMergeTags
         */
        public function testProcessDueCampaignItemWithAttachments()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                                    'description',
                                                                                                    'CustomFromName',
                                                                                                    'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 06',
                                                                                'subject 06',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                  = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $campaign->files->add($file);
            }
            $this->assertTrue($campaign->save());
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertTrue(strpos($emailMessage->content->textContent, 'Dr. contact 06 contact 06son') !== false);
            $this->assertTrue(strpos($emailMessage->content->textContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '<b>contact 06son</b>, contact 06') !== false);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $this->assertNotEmpty($emailMessage->files);
            $this->assertCount(count($files), $emailMessage->files);
            foreach ($files as $index => $file)
            {
                $this->assertEquals($files[$index]['name'], $emailMessage->files[$index]->name);
                $this->assertEquals($files[$index]['type'], $emailMessage->files[$index]->type);
                $this->assertEquals($files[$index]['size'], $emailMessage->files[$index]->size);
                $this->assertEquals($files[$index]['contents'], $emailMessage->files[$index]->fileContent->content);
            }
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithAttachments
         */
        public function testGenerateCampaignItemsForDueCampaigns()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 07');
            $marketingListId    = $marketingList->id;
            $contact1           = ContactTestHelper::createContactByNameForOwner('campaignContact 01', $this->user);
            $contact2           = ContactTestHelper::createContactByNameForOwner('campaignContact 02', $this->user);
            $contact3           = ContactTestHelper::createContactByNameForOwner('campaignContact 03', $this->user);
            $contact4           = ContactTestHelper::createContactByNameForOwner('campaignContact 04', $this->user);
            $contact5           = ContactTestHelper::createContactByNameForOwner('campaignContact 05', $this->user);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList, $contact2);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList, $contact3);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList, $contact4);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList, $contact5);
            $marketingList->forgetAll();

            $marketingList      = MarketingList::getById($marketingListId);
            $campaign           = CampaignTestHelper::createCampaign('campaign 07',
                                                                        'subject 07',
                                                                        'text 07',
                                                                        'html 07',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign);
            $campaign->forgetAll();
            $campaignId         = $campaign->id;
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertEmpty($campaignItems);
            CampaignItemsUtil::generateCampaignItemsForDueCampaigns();
            $campaign           = Campaign::getById($campaignId);
            $this->assertNotNull($campaign);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(5, $campaignItems);
            // TODO: @Shoaibi: Medium: Add tests for the other campaign type.
        }

        /**
         * @depends testGenerateCampaignItemsForDueCampaigns
         */
        public function testGenerateCampaignItemsForDueCampaignsWithCustomBatchSize()
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
            for ($index = 8; $index < 12; $index++)
            {
                $suffix     = $index;
                if ($index < 10)
                {
                    $suffix = "0${suffix}";
                }
                $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList ' . $suffix);
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

            $this->assertTrue(CampaignItemsUtil::generateCampaignItemsForDueCampaigns(1));
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

            $this->assertTrue(CampaignItemsUtil::generateCampaignItemsForDueCampaigns());
            foreach ($campaignIds as $index => $campaignId)
            {
                $campaign           = Campaign::getById($campaignId);
                $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
                $this->assertNotEmpty($campaignItems);
                $this->assertCount(3, $campaignItems);
                $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
            }
            // TODO: @Shoaibi: Medium: Add tests for the other campaign type.
        }

        /**
         * @depends testGenerateCampaignItemsForDueCampaignsWithCustomBatchSize
         */
        public function testProcessDueCampaignItemWithOptout()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $email->optOut              = true;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 08', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 08',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 08',
                                                                                'subject 08',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $personId                   = $contact->getClassId('Person');
            $activities                 = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                CampaignItemActivity::TYPE_SKIP,
                                                                                $campaignItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
        }

        /**
         * @depends testProcessDueCampaignItemWithOptout
         */
        public function testProcessDueCampaignItemWithReturnPathHeaders()
        {
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceReturnPath', 'bounce@zurmo.com');
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 09', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 09',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 09',
                                                                                'subject 09',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            CampaignItemsUtil::processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner, $emailMessage->owner);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertTrue(strpos($emailMessage->content->textContent, 'Dr. contact 09 contact 09son') !== false);
            $this->assertTrue(strpos($emailMessage->content->textContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '<b>contact 09son</b>, contact 09') !== false);
            $this->assertTrue(strpos($emailMessage->content->htmlContent, '/marketingLists/external/') !== false);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals($marketingList->fromAddress, $emailMessage->sender->fromAddress);
            $this->assertEquals($marketingList->fromName, $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact, $recipients[0]->personOrAccount);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'),
                                                'Return-Path' => 'bounce@zurmo.com');
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
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