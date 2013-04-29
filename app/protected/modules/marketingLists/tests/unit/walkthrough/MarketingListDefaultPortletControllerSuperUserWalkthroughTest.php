<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class MarketingListDefaultPortletControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account    = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $account2   = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            $contact1   = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            $contact2   = ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account2);
            $contact3   = ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
            $contact4   = ContactTestHelper::createContactWithAccountByNameForOwner('superContact4', $super, $account2);
            $contact5   = ContactTestHelper::createContactWithAccountByNameForOwner('superContact5', $super, $account);

            $marketingList1 = MarketingListTestHelper::createMarketingListByName('MarketingList1', 'MarketingList Description1');
            $marketingList2 = MarketingListTestHelper::createMarketingListByName('MarketingList2', 'MarketingList Description2');
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList1, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList1, $contact2);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList1, $contact3);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList1, $contact4);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList1, $contact5);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList2, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList2, $contact2);

            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        public function testDelete()
        {
            $marketingList          = MarketingListTestHelper::createMarketingListByName('MarketingList3', 'MarketingList Description3');
            $this->assertNotNull($marketingList);
            $contact                = RandomDataUtil::getRandomValueFromArray(Contact::getAll());
            $this->assertNotEmpty($contact);
            $marketingListMember    = MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList, $contact);
            $this->assertNotNull($marketingListMember);
            $id                     = $marketingListMember->id;
            $this->setGetArray(array('id' => $id));
            $content                = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/delete', true);
            $this->assertEmpty($content);
            $memberCount            = $marketingList->memberAlreadyExists($contact->id);
            $this->assertEquals(0, $memberCount);
        }

        public function testToggleUnsubscribed()
        {
            $marketingList              = MarketingListTestHelper::createMarketingListByName('MarketingList4',
                                                                                            'MarketingList Description4');
            $this->assertNotNull($marketingList);
            $contact                    = RandomDataUtil::getRandomValueFromArray(Contact::getAll());
            $this->assertNotEmpty($contact);
            $previousUnsubcribedValue   = 1;
            $marketingListMember        = MarketingListMemberTestHelper::createMarketingListMember($previousUnsubcribedValue,
                                                                                                    $marketingList,
                                                                                                    $contact);
            $marketingListMemberId      = $marketingListMember->id;
            $this->assertNotNull($marketingListMember);
            $this->setGetArray(array('id' => $marketingListMemberId));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent(
                                                                        'marketingLists/defaultPortlet/toggleUnsubscribed',
                                                                        true);
            $this->assertEmpty($content);
            $marketingListMember        = MarketingListMember::getById($marketingListMemberId);
            $newUnsubscribedValue       = $marketingListMember->unsubscribed;
            $this->assertNotEquals($previousUnsubcribedValue, $newUnsubscribedValue);
        }

        public function testCountMembers()
        {
            $marketingLists             = MarketingList::getByName('MarketingList1');
            $marketingListId            = $marketingLists[0]->id;
            $subscriberCount            = 3;
            $unsubscriberCount          = 2;
            $this->setGetArray(array('marketingListId' => $marketingListId));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/countMembers');
            $countArray                 = CJson::decode($content);
            $this->assertNotEmpty($countArray);
            $this->assertArrayHasKey('subscriberCount', $countArray);
            $this->assertArrayHasKey('unsubscriberCount', $countArray);
            $this->assertEquals($subscriberCount, $countArray['subscriberCount']);
            $this->assertEquals($unsubscriberCount, $countArray['unsubscriberCount']);
        }

        public function testSubscribeContactsForContactType()
        {
            $type                       = 'contact';
            $account                    = AccountTestHelper::createAccountByNameForOwner('superAccount3', $this->user);
            $contact                    = ContactTestHelper::createContactWithAccountByNameForOwner('superContact6',
                                                                                                    $this->user,
                                                                                                    $account);
            $contactId                  = $contact->id;
            $marketingList              = RandomDataUtil::getRandomValueFromArray(MarketingList::getAll());
            $marketingListId            = $marketingList->id;
            $this->setGetArray(array(
                                   'marketingListId'    => $marketingListId,
                                    'id'                => $contact->id,
                                    'type'              => $type,
                                ));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
            $contentArray               = CJson::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals('1 subscribed.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);

            $content                    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
            $contentArray               = CJson::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals('0 subscribed. 1 skipped.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);
        }

        public function testSubscribeContactsForReportType()
        {
            $type                       = 'report';
            $report                     = SavedReportTestHelper::makeSimpleContactRowsAndColumnsReport();
            $marketingList              = MarketingListTestHelper::createMarketingListByName('MarketingList5', 'MarketingList Description5');
            $marketingListId            = $marketingList->id;
            $contactCount               = Contact::getCount();
            $this->assertNotNull($report);
            $this->setGetArray(array(
                                    'marketingListId'    => $marketingListId,
                                    'id'                => $report->id,
                                    'type'              => $type,
                                ));
            $content                    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
            $contentArray               = CJson::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals($contactCount . ' subscribed.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);

            $content                    = $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
            $contentArray               = CJson::decode($content);
            $this->assertNotEmpty($contentArray);
            $this->assertArrayHasKey('type', $contentArray);
            $this->assertArrayHasKey('message', $contentArray);
            $this->assertEquals('0 subscribed. ' . $contactCount . ' skipped.', $contentArray['message']);
            $this->assertEquals('message', $contentArray['type']);
        }
    }
?>