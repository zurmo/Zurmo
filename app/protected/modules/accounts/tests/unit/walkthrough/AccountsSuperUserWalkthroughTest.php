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
            AccountTestHelper::createAccountByNameForOwner('superAccount5', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount6', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount7', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount8', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount9', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount10', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount11', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount12', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount13', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount14', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount15', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount16', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount17', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount18', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount19', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount20', $super);
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
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $accounts = Account::getAll();
            $this->assertEquals(20, count($accounts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superAccountId2 = self::getModelIdByModelNameAndName('Account', 'superAccount2');
            $superAccountId3 = self::getModelIdByModelNameAndName('Account', 'superAccount3');
            $superAccountId4 = self::getModelIdByModelNameAndName('Account', 'superAccount4');
            $superAccountId5 = self::getModelIdByModelNameAndName('Account', 'superAccount5');
            $superAccountId6 = self::getModelIdByModelNameAndName('Account', 'superAccount6');
            $superAccountId7 = self::getModelIdByModelNameAndName('Account', 'superAccount7');
            $superAccountId8 = self::getModelIdByModelNameAndName('Account', 'superAccount8');
            $superAccountId9 = self::getModelIdByModelNameAndName ('Account', 'superAccount9');
            $superAccountId10 = self::getModelIdByModelNameAndName('Account', 'superAccount10');
            $superAccountId11 = self::getModelIdByModelNameAndName('Account', 'superAccount11');
            $superAccountId12 = self::getModelIdByModelNameAndName('Account', 'superAccount12');
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
            $this->assertFalse(strpos($content, '<strong>20</strong>&#160;records selected for updating') === false);

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
            $account5 = Account::getById($superAccountId5);
            $account6 = Account::getById($superAccountId6);
            $account7 = Account::getById($superAccountId7);
            $account8 = Account::getById($superAccountId8);
            $account9 = Account::getById($superAccountId9);
            $account10 = Account::getById($superAccountId10);
            $account11 = Account::getById($superAccountId11);
            $account12 = Account::getById($superAccountId12);
            $this->assertEquals   ('7788', $account1->officePhone);
            $this->assertEquals   ('7788', $account2->officePhone);
            $this->assertNotEquals('7788', $account3->officePhone);
            $this->assertNotEquals('7788', $account4->officePhone);
            $this->assertNotEquals('7788', $account5->officePhone);
            $this->assertNotEquals('7788', $account6->officePhone);
            $this->assertNotEquals('7788', $account7->officePhone);
            $this->assertNotEquals('7788', $account8->officePhone);
            $this->assertNotEquals('7788', $account9->officePhone);
            $this->assertNotEquals('7788', $account10->officePhone);
            $this->assertNotEquals('7788', $account11->officePhone);
            $this->assertNotEquals('7788', $account12->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('officePhone' => '4455'),
                'MassEdit' => array('officePhone' => 1)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);
            //Test that all accounts have the new phone number.
            $account1 = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $account4 = Account::getById($superAccountId4);
            $account5 = Account::getById($superAccountId5);
            $account6 = Account::getById($superAccountId6);
            $account7 = Account::getById($superAccountId7);
            $account8 = Account::getById($superAccountId8);
            $account9 = Account::getById($superAccountId9);
            $account10 = Account::getById($superAccountId10);
            $account11 = Account::getById($superAccountId11);
            $account12 = Account::getById($superAccountId12);
            $this->assertEquals('4455', $account1->officePhone);
            $this->assertEquals('4455', $account2->officePhone);
            $this->assertEquals('4455', $account3->officePhone);
            $this->assertEquals('4455', $account4->officePhone);
            $this->assertEquals('4455', $account5->officePhone);
            $this->assertEquals('4455', $account6->officePhone);
            $this->assertEquals('4455', $account7->officePhone);
            $this->assertEquals('4455', $account8->officePhone);
            $this->assertEquals('4455', $account9->officePhone);
            $this->assertEquals('4455', $account10->officePhone);
            $this->assertEquals('4455', $account11->officePhone);
            $this->assertEquals('4455', $account12->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('accounts/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":10') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":15') === false);
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":20') === false);
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
            $this->assertEquals (2, count($portlets[1])         );
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
            //There should have been a total of 2 portlets.
            $this->assertEquals(2, $portletCount);
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
            $this->assertEquals (2, count($portlets[1])         );
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
            $this->assertEquals(19, count($accounts));
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
            $this->assertEquals(20, count($accounts));
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testStickySearchActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            StickySearchUtil::clearDataByKey('AccountsSearchView');
            $value = StickySearchUtil::getDataByKey('AccountsSearchView');
            $this->assertNull($value);

            //Sort order desc
            $this->setGetArray(array('AccountsSearchForm' => array('anyMixedAttributes'                 => 'xyz',
                                                                   SearchForm::SELECTED_LIST_ATTRIBUTES => array('officePhone', 'name')),
                                     'Account_sort'       => 'officePhone.desc'));

            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/');
            $data = StickySearchUtil::getDataByKey('AccountsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributes'                 => 'xyz',
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('officePhone', 'name'),
                                 'sortAttribute'                      => 'officePhone',
                                 'sortDescending'                     => true
            );
            $this->assertEquals($compareData, $data);

            //Sort order asc
            $this->setGetArray(array('AccountsSearchForm' => array('anyMixedAttributes'                 => 'xyz',
                                                                   SearchForm::SELECTED_LIST_ATTRIBUTES => array('officePhone', 'name')),
                                     'Account_sort'       => 'officePhone'));

            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/');
            $data = StickySearchUtil::getDataByKey('AccountsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributes'                 => 'xyz',
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('officePhone', 'name'),
                                 'sortAttribute'                       => 'officePhone',
                                 'sortDescending'                      => '',
                                 'savedSearchId'                       => ''
            );
            $this->assertEquals($compareData, $data);

            $this->setGetArray(array('clearingSearch' => true));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default');
            $data = StickySearchUtil::getDataByKey('AccountsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('name', 'type', 'owner')
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @deletes selected accounts.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $accounts = Account::getAll();
            $this->assertEquals(20, count($accounts));

            $superAccountId2  = self::getModelIdByModelNameAndName('Account', 'superAccount2');
            $superAccountId3  = self::getModelIdByModelNameAndName('Account', 'superAccount3');
            $superAccountId5  = self::getModelIdByModelNameAndName('Account', 'superAccount5');
            $superAccountId6  = self::getModelIdByModelNameAndName('Account', 'superAccount6');
            $superAccountId7  = self::getModelIdByModelNameAndName('Account', 'superAccount7');
            $superAccountId8  = self::getModelIdByModelNameAndName('Account', 'superAccount8');
            $superAccountId9  = self::getModelIdByModelNameAndName('Account', 'superAccount9');
            $superAccountId10 = self::getModelIdByModelNameAndName('Account', 'superAccount10');
            $superAccountId11 = self::getModelIdByModelNameAndName('Account', 'superAccount11');
            $superAccountId12 = self::getModelIdByModelNameAndName('Account', 'superAccount12');
            $superAccountId13 = self::getModelIdByModelNameAndName('Account', 'superAccount13');
            $superAccountId14 = self::getModelIdByModelNameAndName('Account', 'superAccount14');
            $superAccountId15 = self::getModelIdByModelNameAndName('Account', 'superAccount15');
            $superAccountId16 = self::getModelIdByModelNameAndName('Account', 'superAccount16');
            $superAccountId17 = self::getModelIdByModelNameAndName('Account', 'superAccount17');
            $superAccountId18 = self::getModelIdByModelNameAndName('Account', 'superAccount18');
            $superAccountId19 = self::getModelIdByModelNameAndName('Account', 'superAccount19');
            $superAccountId20 = self::getModelIdByModelNameAndName('Account', 'superAccount20');

            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;Accounts selected for removal') === false);

            //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massDelete');
            $this->assertFalse(strpos($content, '<strong>20</strong>&#160;Accounts selected for removal') === false);
            //MassDelete for selected ids
            $account2 = Account::getById($superAccountId2);
            $account3 = Account::getById($superAccountId3);
            $this->setGetArray(array(
                'selectedIds' => $superAccountId2 . ',' . $superAccountId3, // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => '5'));
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massDelete');

            //MassDelete for selected Record Count
            $accounts = Account::getAll();
            $this->assertEquals(18, count($accounts));

            //MassDelete for selected ids for paged scenario
            $account13 = Account::getById($superAccountId13);
            $account14 = Account::getById($superAccountId14);
            $account15 = Account::getById($superAccountId15);
            $account16 = Account::getById($superAccountId16);
            $account17 = Account::getById($superAccountId17);
            $account18 = Account::getById($superAccountId18);
            $account19 = Account::getById($superAccountId19);
            $account20 = Account::getById($superAccountId20);

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds'  => $superAccountId13 . ',' . $superAccountId14 . ',' . // Not Coding Standard
                                  $superAccountId15 . ',' . $superAccountId16 . ',' . // Not Coding Standard
                                  $superAccountId17 . ',' . $superAccountId18 . ',' . // Not Coding Standard
                                  $superAccountId19 . ',' . $superAccountId20,        // Not Coding Standard
                'selectAll'    => '',
                'massDelete'   => '',
                'Account_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithExitExceptionAndGetContent('accounts/default/massDelete');

            //MassDelete for selected Record Count
            $accounts = Account::getAll();
            $this->assertEquals(13, count($accounts));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds' => $superAccountId13 . ',' . $superAccountId14 . ',' . // Not Coding Standard
                                 $superAccountId15 . ',' . $superAccountId16 . ',' . // Not Coding Standard
                                 $superAccountId17 . ',' . $superAccountId18 . ',' . // Not Coding Standard
                                 $superAccountId19 . ',' . $superAccountId20,        // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massDeleteProgress');

           //MassDelete for selected Record Count
            $accounts = Account::getAll();
            $this->assertEquals(10, count($accounts));
        }

         /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $accounts = Account::getAll();
            $this->assertEquals(10, count($accounts));

            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Account_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page1.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('accounts/default/massDelete');

            //check for previous mass delete progress
            $accounts = Account::getAll();
            $this->assertEquals(5, count($accounts));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Account_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massDeleteProgress');

            //calculating account's count
            $accounts = Account::getAll();
            $this->assertEquals(0, count($accounts));
        }
    }
?>
