<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Accounts Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class AccountsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount3', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount4', $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $accounts = Account::getAll();
            $this->assertEquals(4, count($accounts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superAccountId2 = self::getModelIdByModelNameAndName('Account', 'superAccount2');
            $superAccountId3 = self::getModelIdByModelNameAndName('Account', 'superAccount3');
            $superAccountId4 = self::getModelIdByModelNameAndName('Account', 'superAccount4');
            $this->setGetArray(array('id' => $superAccountId));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            //Save account.
            $superAccount = Account::getById($superAccountId);
            $this->assertEquals(null, $superAccount->officePhone);
            $this->setPostArray(array('Account' => array('officePhone' => '456765421')));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit',
                        Yii::app()->createUrl('accounts/default/details', array('id' => $superAccountId)));
            $superAccount = Account::getById($superAccountId);
            $this->assertEquals('456765421', $superAccount->officePhone);
            //Test having a failed validation on the account during save.
            $this->setGetArray (array('id'      => $superAccountId));
            $this->setPostArray(array('Account' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;records selected for updating') === false);

            //save Model MassEdit for selected Ids
            //Test that the 2 accounts do not have the office phone number we are populating them with.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertNotEquals('7788', $account1->officePhone);
            $this->assertNotEquals('7788', $account2->officePhone);
            $this->assertNotEquals('7788', $account3->officePhone);
            $this->assertNotEquals('7788', $account4->officePhone);
            $this->setGetArray(array(
                'selectedIds' => $superAccountId . ',' . $superAccountId2, // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('officePhone' => '7788'),
                'MassEdit' => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            //Test that the 2 accounts have the new office phone number and the other accounts do not.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertEquals   ('7788', $account1->officePhone);
            $this->assertEquals   ('7788', $account2->officePhone);
            $this->assertNotEquals('7788', $account3->officePhone);
            $this->assertNotEquals('7788', $account4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('officePhone' => '4455'),
                'MassEdit' => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            //Test that all accounts have the new phone number.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $this->assertEquals('4455', $account1->officePhone);
            $this->assertEquals('4455', $account2->officePhone);
            $this->assertEquals('4455', $account3->officePhone);
            $this->assertEquals('4455', $account4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('accounts/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":50') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":75') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":100') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for Account
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/auditEventsModalList');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId2 = self::getModelIdByModelNameAndName ('Account', 'superAccount2');

            //Save a layout change. Collapse all portlets in the Account Details View.
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'AccountDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (3, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['AccountDetailsAndRelationsViewLeftBottomView_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'AccountDetailsAndRelationsViewLeftBottomView_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 3 portlets.
            $this->assertEquals(3, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => 'AccountDetailsAndRelationsViewLeftBottomView',
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed and moved to the first column.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            'AccountDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (3, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            foreach ($portlets as $column => $columns)
            {
                foreach ($columns as $position => $positionPortlets)
                {
                    $this->assertEquals('1', $positionPortlets->collapsed);
                }
            }
            //Load Details View again to make sure everything is ok after the layout change.
            $this->setGetArray(array('id' => $superAccountId2));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
        }

        /**
         * @depends testSuperUserDefaultPortletControllerActions
         */
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId4 = self::getModelIdByModelNameAndName ('Account', 'superAccount4');

            //Delete an account.
            $this->setGetArray(array('id' => $superAccountId4));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete');
            $accounts = Account::getAll();
            $this->assertEquals(3, count($accounts));
            try
            {
                Account::getById($superAccountId4);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Create a new account.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                            'name'        => 'myNewAccount',
                                            'officePhone' => '456765421')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/create');
            $accounts = Account::getByName('myNewAccount');
            $this->assertEquals(1, count($accounts));
            $this->assertTrue  ($accounts[0]->id > 0);
            $compareRedirectUrl = Yii::app()->createUrl('accounts/default/details', array('id' => $accounts[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals('456765421', $accounts[0]->officePhone);
            $this->assertTrue  ($accounts[0]->owner == $super);
            $accounts = Account::getAll();
            $this->assertEquals(4, count($accounts));
        }
    }
?>