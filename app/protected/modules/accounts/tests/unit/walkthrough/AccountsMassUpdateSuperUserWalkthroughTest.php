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
    * Designer Module Walkthrough of accounts.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation, search, edit and delete of the account based on the custom fields.
    */
    class AccountsMassUpdateSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSuperUserCustomFieldsWalkthroughForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('AccountsModule', 'tagcloud');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to AccountEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountEditAndDetailsView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsMassEditView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsMassEditViewLayoutWithMultiSelectAndTagCloudFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForAccountsModule
         */
        public function testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Create First new account.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                    'name'                              => 'myFirstAccount',
                                    'multiselect'                       => array('values' => array('ff', 'rr')),
                                    'tagcloud'                          => array('values' => array('writing', 'gardening')),
                                    )));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/create');

            //Check the details if they are saved properly for the custom fields.
            $account = Account::getByName('myFirstAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name                           , 'myFirstAccount');
            $this->assertContains('ff'                                      , $account[0]->multiselect->values);
            $this->assertContains('rr'                                      , $account[0]->multiselect->values);
            $this->assertContains('writing'                                 , $account[0]->tagcloud->values);
            $this->assertContains('gardening'                               , $account[0]->tagcloud->values);
            unset($account);

            //Create First new account.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                    'name'                              => 'mySecondAccount',
                                    'multiselect'                       => array('values' => array('gg', 'hh')),
                                    'tagcloud'                          => array('values' => array('surfing', 'reading')),
                                    )));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/create');

            $account = Account::getByName('mySecondAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name                           , 'mySecondAccount');
            $this->assertContains('gg'                                      , $account[0]->multiselect->values);
            $this->assertContains('hh'                                      , $account[0]->multiselect->values);
            $this->assertContains('surfing'                                 , $account[0]->tagcloud->values);
            $this->assertContains('reading'                                 , $account[0]->tagcloud->values);
            unset($account);
        }

        /**
         * @depends testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule
         */
        public function testMassUpdateForMultiSelectFieldPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $account = Account::getByName('myFirstAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name, 'myFirstAccount');
            $this->assertContains('ff'           , $account[0]->multiselect->values);
            $this->assertContains('rr'           , $account[0]->multiselect->values);
            unset($account);

            $secondAccount = Account::getByName('mySecondAccount');
            $this->assertEquals(1, count($secondAccount));
            $this->assertEquals($secondAccount[0]->name, 'mySecondAccount');
            $this->assertContains('gg'           , $secondAccount[0]->multiselect->values);
            $this->assertContains('hh'           , $secondAccount[0]->multiselect->values);
            unset($secondAccount);

            $this->resetPostArray();
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => '1', 'selectedIds' => null, 'ajax' => null));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');

            $this->setPostArray(array('save'     => 'Save',
                                      'MassEdit' => array('multiselect' => '1'),
                                      'Account'  => array('multiselect' => array('values' => array('ff', 'rr')))
                                     )
                               );
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');

            $account = Account::getByName('myFirstAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name, 'myFirstAccount');
            $this->assertContains('ff'           , $account[0]->multiselect->values);
            $this->assertContains('rr'           , $account[0]->multiselect->values);

            $secondAccount = Account::getByName('mySecondAccount');
            $this->assertEquals(1, count($secondAccount));
            $this->assertEquals($secondAccount[0]->name, 'mySecondAccount');
            $this->assertContains('ff'           , $secondAccount[0]->multiselect->values);
            $this->assertContains('rr'           , $secondAccount[0]->multiselect->values);
        }

        /**
         * @depends testMassUpdateForMultiSelectFieldPlacedForAccountsModule
         */
        public function testMassUpdateForTagCloudFieldPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $account = Account::getByName('myFirstAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name, 'myFirstAccount');
            $this->assertContains('writing'      , $account[0]->tagcloud->values);
            $this->assertContains('gardening'    , $account[0]->tagcloud->values);
            unset($account);

            $secondAccount = Account::getByName('mySecondAccount');
            $this->assertEquals(1, count($secondAccount));
            $this->assertEquals($secondAccount[0]->name, 'mySecondAccount');
            $this->assertContains('surfing'      , $secondAccount[0]->tagcloud->values);
            $this->assertContains('reading'      , $secondAccount[0]->tagcloud->values);
            unset($secondAccount);

            $this->resetPostArray();
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => '1', 'selectedIds' => null, 'ajax' => null));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');

            $this->setPostArray(array('save'     => 'Save',
                                      'MassEdit' => array('tagcloud' => '1'),
                                      'Account'  => array('tagcloud' => array('values' => array('writing', 'gardening')))
                                     )
                               );
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');

            $account = Account::getByName('myFirstAccount');
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name, 'myFirstAccount');
            $this->assertContains('writing'      , $account[0]->tagcloud->values);
            $this->assertContains('gardening'    , $account[0]->tagcloud->values);

            $secondAccount = Account::getByName('mySecondAccount');
            $this->assertEquals(1, count($secondAccount));
            $this->assertEquals($secondAccount[0]->name, 'mySecondAccount');
            $this->assertContains('writing'      , $secondAccount[0]->tagcloud->values);
            $this->assertContains('gardening'    , $secondAccount[0]->tagcloud->values);
        }
    }
?>