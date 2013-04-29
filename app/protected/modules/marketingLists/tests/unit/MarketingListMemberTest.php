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
    class MarketingListMemberTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            MarketingListTestHelper::createMarketingListByName('test marketing List 01');
            MarketingListTestHelper::createMarketingListByName('test marketing List 02');

            //Setup test data owned by the super user.
            $account    = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $account2   = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account2);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetMarketingListMemberById()
        {
            $marketingList                      = RandomDataUtil::getRandomValueFromArray(MarketingList::getAll());
            $this->assertNotNull($marketingList);
            $contact                            = RandomDataUtil::getRandomValueFromArray(Contact::getAll());
            $this->assertNotNull($contact);
            $marketingListMember                = new MarketingListMember();
            $marketingListMember->unsubscribed  = 0;
            $marketingListMember->marketingList = $marketingList;
            $marketingListMember->contact       = $contact;
            $this->assertTrue($marketingListMember->unrestrictedSave());
            $id                                 = $marketingListMember->id;
            $this->assertTrue($id > 0);
            unset($marketingListMember);
            $marketingListMember                = MarketingListMember::getById($id);
            $this->assertEquals(0,              $marketingListMember->unsubscribed);
            $this->assertEquals($contact,       $marketingListMember->contact);
            $this->assertEquals($marketingList, $marketingListMember->marketingList);
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testGetLabel()
        {
            $marketingListMember = RandomDataUtil::getRandomValueFromArray(MarketingListMember::getAll());
            $this->assertNotEmpty($marketingListMember);
            $this->assertEquals(1, count($marketingListMember));
            $this->assertEquals('Marketing List Member',  $marketingListMember::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Marketing List Members', $marketingListMember::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testDeleteMarketingListMember()
        {
            MarketingListMemberTestHelper::createMarketingListMember();
            $marketingListMembers = MarketingListMember::getAll();
            $this->assertNotEmpty($marketingListMembers);
            $this->assertEquals(2, count($marketingListMembers));
            $marketingListMembers[0]->delete();
            $marketingListMembers = MarketingListMember::getAll();
            $this->assertNotEmpty($marketingListMembers);
            $this->assertEquals(1, count($marketingListMembers));
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testAddNewMemberSkipsDuplicate()
        {
            $marketingList                     = MarketingListTestHelper::createMarketingListByName('test marketing List 03');
            $this->assertNotNull($marketingList);
            $contact                            = RandomDataUtil::getRandomValueFromArray(Contact::getAll());
            $this->assertNotNull($contact);
            $added                              = $marketingList->addNewMember($contact->id, false, $contact);
            $this->assertTrue($added);
            $added                              = $marketingList->addNewMember($contact->id, false, $contact);
            $this->assertFalse($added);
            $memberCount                        = $marketingList->memberAlreadyExists($contact->id);
            $this->assertEquals(1, $memberCount);
            $marketingList                     = MarketingListTestHelper::createMarketingListByName('test marketing List 04');
            $this->assertNotNull($marketingList);
            $added                              = $marketingList->addNewMember($contact->id, false, $contact);
            $this->assertTrue($added);
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testGetCountByMarketingListIdAndUnsubscribed()
        {
            $marketingList                      = MarketingListTestHelper::createMarketingListByName('test marketing List 05');
            $this->assertNotNull($marketingList);
            $contacts                           = Contact::getAll();
            $this->assertNotEmpty($contacts);
            $unsubscribedCount                  = 0;
            $subscribedCount                    = 0;
            foreach ($contacts as $index => $contact)
            {
                $unsubcribed = ($index % 2);
                $member = MarketingListMemberTestHelper::fillMarketingListMember($unsubcribed, $marketingList, $contacts[0]);
                $this->assertTrue($member->unrestrictedSave());
                if ($unsubcribed)
                {
                    $unsubscribedCount++;
                }
                else
                {
                    $subscribedCount++;
                }
            }
            $calculatedSubscribedCount      = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($marketingList->id, 0);
            $calculatedUnsubscribedCount    = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($marketingList->id, 1);
            $this->assertEquals($subscribedCount, $calculatedSubscribedCount);
            $this->assertEquals($unsubscribedCount, $calculatedUnsubscribedCount);
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testBeforeSaveAutoresponderRegister()
        {
            $marketingList                  = MarketingListTestHelper::createMarketingListByName('test marketing List 06');
            $this->assertNotNull($marketingList);
            $autoresponderSubscribe         = AutoresponderTestHelper::createAutoresponder('autoresponder Subscribe',
                                                                                        'test autoresponder Subscribe',
                                                                                        'This is text content Subscribe',
                                                                                        'This is <b>html</b> content Subscribe',
                                                                                        10,
                                                                                        Autoresponder::OPERATION_SUBSCRIBE,
                                                                                        $marketingList
                                                                                    );
            $this->assertNotNull($autoresponderSubscribe);
            $autoresponderUnsubscribe       = AutoresponderTestHelper::createAutoresponder('autoresponder Unsubscribe',
                                                                                        'test autoresponder Unsubscribe',
                                                                                        'This is text content Unsubscribe',
                                                                                        'This is <b>html</b> content Unsubscribe',
                                                                                        20,
                                                                                        Autoresponder::OPERATION_UNSUBSCRIBE,
                                                                                        $marketingList
                                                                                    );
            $this->assertNotNull($autoresponderUnsubscribe);
            $member                         = MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList);
            $autoresponderItemsSubscribe    = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponderSubscribe->id);
            $this->assertNotEmpty($autoresponderItemsSubscribe);
            $this->assertCount(1, $autoresponderItemsSubscribe);
            $autoresponderItemsUnsubscribe    = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponderUnsubscribe->id);
            $this->assertEmpty($autoresponderItemsUnsubscribe);

            $member->unsubscribed               = 1;
            $saved = $member->unrestrictedSave();
            $this->assertTrue($saved);

            $autoresponderItemsSubscribe    = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponderSubscribe->id);
            $this->assertNotEmpty($autoresponderItemsSubscribe);
            $this->assertCount(1, $autoresponderItemsSubscribe);
            $autoresponderItemsUnsubscribe    = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                        AutoresponderItem::NOT_PROCESSED,
                                                                                        $autoresponderUnsubscribe->id);
            $this->assertNotEmpty($autoresponderItemsUnsubscribe);
            $this->assertCount(1, $autoresponderItemsUnsubscribe);
        }

        /**
         * @depends testCreateAndGetMarketingListMemberById
         */
        public function testBeforeDeleteAutoresponderRegister()
        {
            $marketingList                  = MarketingListTestHelper::createMarketingListByName('test marketing List 07');
            $this->assertNotNull($marketingList);
            $autoresponderRemove            = AutoresponderTestHelper::createAutoresponder('autoresponder Remove',
                                                                                            'test autoresponder Remove',
                                                                                            'This is text content Remove',
                                                                                            'This is <b>html</b> content Remove',
                                                                                            10,
                                                                                            Autoresponder::OPERATION_REMOVE,
                                                                                            $marketingList
                                                                                        );
            $this->assertNotNull($autoresponderRemove);

            $member                         = MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList);
            $saved = $member->delete();
            $this->assertTrue($saved);

            $autoresponderItemsRemove       = AutoresponderItem::getByProcessedAndAutoresponderId(
                                                                                            AutoresponderItem::NOT_PROCESSED,
                                                                                            $autoresponderRemove->id);
            $this->assertNotEmpty($autoresponderItemsRemove);
            $this->assertCount(1, $autoresponderItemsRemove);
        }
    }
?>