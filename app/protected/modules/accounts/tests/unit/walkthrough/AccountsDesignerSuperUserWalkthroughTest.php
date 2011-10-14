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
    * Designer Module Walkthrough of accounts.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    *   
    */
    class AccountsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();            
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSuperUserAccountDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Account Modules Menu.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for Account module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Account module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->setPostArray(array('save' => 'Save',
                'AccountsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Acc New Name',  AccountsModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Acc New Names', AccountsModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('acc new name',  AccountsModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('acc new names', AccountsModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsMassEditView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsSearchView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            
        }

        /**
         * @depends testSuperUserAccountDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('AccountsModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('AccountsModule', 'currency');
            $this->createDateCustomFieldByModule                ('AccountsModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('AccountsModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('AccountsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('AccountsModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('AccountsModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountsModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('AccountsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('AccountsModule', 'radio');
            $this->createTextCustomFieldByModule                ('AccountsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('AccountsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('AccountsModule', 'url');
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

            //Add all fields to AccountsSearchView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsSearchView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsModalSearchView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalSearchView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsSearchViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsRelatedListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsModalListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsModalListView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to AccountsMassEditView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsMassEditView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }
        
        
        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForAccountsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            //Load create, edit, and details views.
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');
            $this->setGetArray(array('id' => $superAccountId));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            //todo: test related list once the related list is available in a sub view.
        }
        
        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForAccountsModule
         */
        public function testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule()
        {
        
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            
            //Create a new account.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                            'name'        => 'myNewAccount',                                            
                                            'checkbox'=>'1',
                                            'currency'=>'45',
                                            'date'=>'',
                                            'datetime'=>'',
                                            'decimal'=>'123',
                                            'picklist'=>'a',
                                            'integer'=>'12',
                                            'multiselect'=>'gg',
                                            'phone' => '456765421',
                                            'radio'=>'e',
                                            'text'=>'This is a test Text',
                                            'textarea'=>'This is a test TextArea',
                                            'url'=>'http://wwww.abc.com',
                                            )));
                                            
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/create');
                      
            $account = Account::getByName('myNewAccount');
            $this->assertEquals(1, count($account));
            echo " MultiSelectDropDown :: ".$account[0]->MultiSelectDropDown;
        }
        
        
        /**
         * @depends testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterCreatingTheAccountUser()
        {
        
        
        
        }
        
        /**
         * @depends testCreateAccountUserAfterTheCustomFieldsPlacedForAccountsModule
         */
        public function testEditOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule()
        {
        
        
        
        }
        
        /**
         * @depends testEditOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterEditingTheAccountUser()
        {
        
        
        
        }
        
        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterEditingTheAccountUser
         */
        public function testDeleteOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule()
        {
        
        
        
        }
        
    }    
?>     