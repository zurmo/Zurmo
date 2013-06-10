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

    class MarketingListDefaultPortletControllerRegularUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $superUserMarketingListId;

        protected static $regularUserMarketingListId;

        protected static $superUserMemberId;

        protected static $regularUserMemberId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            // set up data owned by super
            Yii::app()->user->userModel = User::getByUsername('super');
            $account1       = AccountTestHelper::createAccountByNameForOwner('account1', Yii::app()->user->userModel);
            $contact1       = ContactTestHelper::createContactWithAccountByNameForOwner('contact1',
                                                                                Yii::app()->user->userModel, $account1);
            $contact2       = ContactTestHelper::createContactWithAccountByNameForOwner('contact2',
                                                                                Yii::app()->user->userModel, $account1);
            $contact3       = ContactTestHelper::createContactWithAccountByNameForOwner('contact3',
                                                                                Yii::app()->user->userModel, $account1);
            $marketingList1 = MarketingListTestHelper::createMarketingListByName('MarketingList1');
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList1, $contact1);
            MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList1, $contact2);
            $member1        = MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList1, $contact3);
            static::$superUserMarketingListId   = $marketingList1->id;
            static::$superUserMemberId          = $member1->id;

            // set up data owned by nobody
            Yii::app()->user->userModel = UserTestHelper::createBasicUser('nobody');
            $account2       = AccountTestHelper::createAccountByNameForOwner('account2', Yii::app()->user->userModel);
            $contact4       = ContactTestHelper::createContactWithAccountByNameForOwner('contact4',
                                                                                Yii::app()->user->userModel, $account2);
            $contact5       = ContactTestHelper::createContactWithAccountByNameForOwner('contact5',
                                                                                Yii::app()->user->userModel, $account2);
            $contact6       = ContactTestHelper::createContactWithAccountByNameForOwner('contact6',
                                                                                Yii::app()->user->userModel, $account2);
            $marketingList2 = MarketingListTestHelper::createMarketingListByName('MarketingList2');
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList2, $contact4);
            $member2        = MarketingListMemberTestHelper::createMarketingListMember(1, $marketingList2, $contact5);
            MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList2, $contact6);
            static::$regularUserMarketingListId = $marketingList2->id;
            static::$regularUserMemberId        = $member2->id;
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            Yii::app()->user->userModel = $this->user;
        }

        public function testRegularUserAllActionsWithNoMarketingListRight()
        {
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                    'marketingLists/defaultPortlet/toggleUnsubscribed');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                        'marketingLists/defaultPortlet/countMembers');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                    'marketingLists/defaultPortlet/subscribeContacts');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                                'marketingLists/defaultPortlet/delete');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
        }

        /**
         * Expected exception due to subscribeContacts with no access for contacts and leads.
         * @expectedException PartialRightsForReportSecurityException
         */
        public function testRegularUserActionsWithMarketingListRightButInsufficientPermission()
        {
            $this->user->setRight('MarketingListsModule', MarketingListsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->setGetArray(array('id' => static::$superUserMemberId));
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                    'marketingLists/defaultPortlet/toggleUnsubscribed');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $content    = $this->runControllerWithExitExceptionAndGetContent('marketingLists/defaultPortlet/delete');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $this->setGetArray(array('marketingListId' => static::$superUserMarketingListId));
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                        'marketingLists/defaultPortlet/countMembers');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $type                       = 'contact';
            $account                    = AccountTestHelper::createAccountByNameForOwner('account2', $this->user);
            $contact7                   = ContactTestHelper::createContactWithAccountByNameForOwner('contact7',
                                                                                                        $this->user,
                                                                                                        $account);
            $this->setGetArray(array(
                'marketingListId'       => static::$superUserMarketingListId,
                'id'                    => $contact7->id,
                'type'                  => $type,
            ));
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                    'marketingLists/defaultPortlet/subscribeContacts');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
            $type                       = 'report';
            $report                     = SavedReportTestHelper::makeSimpleContactRowsAndColumnsReport();
            $this->setGetArray(array(
                'marketingListId'       => static::$superUserMarketingListId,
                'id'                    => $report->id,
                'type'                  => $type,
            ));
            $content    = $this->runControllerWithExitExceptionAndGetContent(
                                                                    'marketingLists/defaultPortlet/subscribeContacts');
            $this->assertTrue(strpos($content, 'You have tried to access a page you do not have access to.') !== false);
        }

        public function testRegularUserActionsWithMarketingListRightAndRequiredPermissions()
        {
            $this->setGetArray(array('id' => static::$regularUserMemberId));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/toggleUnsubscribed', true);
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/delete', true);
            $this->setGetArray(array('marketingListId' => static::$regularUserMarketingListId));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/countMembers');
            $type                       = 'contact';
            $account                    = AccountTestHelper::createAccountByNameForOwner('account2', $this->user);
            $contact8                   = ContactTestHelper::createContactWithAccountByNameForOwner('contact8',
                                                                                                        $this->user,
                                                                                                        $account);
            $this->setGetArray(array(
                'marketingListId'       => static::$regularUserMarketingListId,
                'id'                    => $contact8->id,
                'type'                  => $type,
            ));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
            //$this->user->setRight('ReportsModule', ReportsModule::getAccessRight());
            $this->user->setRight('ContactsModule', ContactsModule::getAccessRight()); // or leads. Else PartialRightsForReportSecurityException
            $this->assertTrue($this->user->save());
            $type                       = 'report';
            $report                     = SavedReportTestHelper::makeSimpleContactRowsAndColumnsReport();
            $this->setGetArray(array(
                'marketingListId'       => static::$regularUserMarketingListId,
                'id'                    => $report->id,
                'type'                  => $type,
            ));
            $this->runControllerWithNoExceptionsAndGetContent('marketingLists/defaultPortlet/subscribeContacts');
        }
    }
?>