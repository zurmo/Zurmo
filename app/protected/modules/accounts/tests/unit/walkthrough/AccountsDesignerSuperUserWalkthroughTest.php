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
    class AccountsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
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
            $this->createDependentDropDownCustomFieldByModule   ('AccountsModule', 'countrypicklist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountsModule', 'statepicklist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountsModule', 'citypicklist');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('AccountsModule', 'tagcloud');
            $this->createCalculatedNumberCustomFieldByModule    ('AccountsModule', 'calculatednumber');
            $this->createDropDownDependencyCustomFieldByModule  ('AccountsModule', 'dropdowndependency');
            $this->createIntegerCustomFieldByModule             ('AccountsModule', 'integer');
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

            //Set the date and datetime variable values here
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new account based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                    'name'                              => 'myNewAccount',
                                    'officePhone'                       => '259-784-2169',
                                    'industry'                          => array('value' => 'Automotive'),
                                    'officeFax'                         => '299-845-7863',
                                    'employees'                         => '930',
                                    'annualRevenue'                     => '474000000',
                                    'type'                              => array('value' => 'Prospect'),
                                    'website'                           => 'http://www.Unnamed.com',
                                    'primaryEmail'                      => array('emailAddress' => 'info@myNewAccount.com',
                                                                                  'optOut' => '1',
                                                                                  'isInvalid' => '0'),
                                    'secondaryEmail'                    => array('emailAddress' => '',
                                                                                  'optOut' => '0',
                                                                                  'isInvalid' => '0'),
                                    'billingAddress'                    => array('street1' => '6466 South Madison Creek',
                                                                                  'street2' => '',
                                                                                  'city' => 'Chicago',
                                                                                  'state' => 'IL',
                                                                                  'postalCode' => '60652',
                                                                                  'country' => 'USA'),
                                    'shippingAddress'                   => array('street1' => '27054 West Michigan Lane',
                                                                                  'street2' => '',
                                                                                  'city' => 'Austin',
                                                                                  'state' => 'TX',
                                                                                  'postalCode' => '78759',
                                                                                  'country' => 'USA'),
                                    'description'                       => 'This is a Description',
                                    'explicitReadWriteModelPermissions' => array('type' => null),
                                    'checkbox'                          => '1',
                                    'currency'                          => array('value'    => 45,
                                                                                 'currency' => array('id' =>
                                                                                 $baseCurrency->id)),
                                    'date'                              => $date,
                                    'datetime'                          => $datetime,
                                    'decimal'                           => '123',
                                    'picklist'                          => array('value'  => 'a'),
                                    'multiselect'                       => array('values' => array('ff', 'rr')),
                                    'tagcloud'                          => array('values' => array('writing', 'gardening')),
                                    'countrypicklist'                   => array('value'  => 'bbbb'),
                                    'statepicklist'                     => array('value'  => 'bbb1'),
                                    'citypicklist'                      => array('value'  => 'bb1'),
                                    'integer'                           => '12',
                                    'phone'                             => '259-784-2169',
                                    'radio'                             => array('value' => 'd'),
                                    'text'                              => 'This is a test Text',
                                    'textarea'                          => 'This is a test TextArea',
                                    'url'                               => 'http://wwww.abc.com')));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/create');

            //Check the details if they are saved properly for the custom fields.
            $account = Account::getByName('myNewAccount');
            //Retrieve the permission for the account.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($account[0]->id));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name                           , 'myNewAccount');
            $this->assertEquals($account[0]->officePhone                    , '259-784-2169');
            $this->assertEquals($account[0]->industry->value                , 'Automotive');
            $this->assertEquals($account[0]->officeFax                      , '299-845-7863');
            $this->assertEquals($account[0]->employees                      , '930');
            $this->assertEquals($account[0]->annualRevenue                  , '474000000');
            $this->assertEquals($account[0]->type->value                    , 'Prospect');
            $this->assertEquals($account[0]->website                        , 'http://www.Unnamed.com');
            $this->assertEquals($account[0]->primaryEmail->emailAddress     , 'info@myNewAccount.com');
            $this->assertEquals($account[0]->primaryEmail->optOut           , '1');
            $this->assertEquals($account[0]->primaryEmail->isInvalid        , '0');
            $this->assertEquals($account[0]->secondaryEmail->emailAddress   , '');
            $this->assertEquals($account[0]->secondaryEmail->optOut         , '0');
            $this->assertEquals($account[0]->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($account[0]->billingAddress->street1        , '6466 South Madison Creek');
            $this->assertEquals($account[0]->billingAddress->street2        , '');
            $this->assertEquals($account[0]->billingAddress->city           , 'Chicago');
            $this->assertEquals($account[0]->billingAddress->state          , 'IL');
            $this->assertEquals($account[0]->billingAddress->postalCode     , '60652');
            $this->assertEquals($account[0]->billingAddress->country        , 'USA');
            $this->assertEquals($account[0]->shippingAddress->street1       , '27054 West Michigan Lane');
            $this->assertEquals($account[0]->shippingAddress->street2       , '');
            $this->assertEquals($account[0]->shippingAddress->city          , 'Austin');
            $this->assertEquals($account[0]->shippingAddress->state         , 'TX');
            $this->assertEquals($account[0]->shippingAddress->postalCode    , '78759');
            $this->assertEquals($account[0]->shippingAddress->country       , 'USA');
            $this->assertEquals($account[0]->description                    , 'This is a Description');
            $this->assertEquals(0                                           , count($readWritePermitables));
            $this->assertEquals(0                                           , count($readOnlyPermitables));
            $this->assertEquals($account[0]->checkbox                       , '1');
            $this->assertEquals($account[0]->currency->value                , 45);
            $this->assertEquals($account[0]->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($account[0]->date                           , $dateAssert);
            $this->assertEquals($account[0]->datetime                       , $datetimeAssert);
            $this->assertEquals($account[0]->decimal                        , '123');
            $this->assertEquals($account[0]->picklist->value                , 'a');
            $this->assertEquals($account[0]->integer                        , 12);
            $this->assertEquals($account[0]->phone                          , '259-784-2169');
            $this->assertEquals($account[0]->radio->value                   , 'd');
            $this->assertEquals($account[0]->text                           , 'This is a test Text');
            $this->assertEquals($account[0]->textarea                       , 'This is a test TextArea');
            $this->assertEquals($account[0]->url                            , 'http://wwww.abc.com');
            $this->assertEquals($account[0]->countrypicklist->value         , 'bbbb');
            $this->assertEquals($account[0]->statepicklist->value           , 'bbb1');
            $this->assertEquals($account[0]->citypicklist->value            , 'bb1');
            $this->assertContains('ff'                                      , $account[0]->multiselect->values);
            $this->assertContains('rr'                                      , $account[0]->multiselect->values);
            $this->assertContains('writing'                                 , $account[0]->tagcloud->values);
            $this->assertContains('gardening'                               , $account[0]->tagcloud->values);
            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Account');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $account[0]);
            $this->assertEquals(474000930                                   , $testCalculatedValue);
        }

        /**
         * @depends testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterCreatingTheAccountUser()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created account using the customfields.
            $this->resetPostArray();
            $this->setGetArray(array('AccountsSearchForm' => array(
                                        'name'                  => 'myNewAccount',
                                        'officePhone'           => '259-784-2169',
                                        'type'                  => array('value'  =>  'Prospect'),
                                        'officeFax'             => '299-845-7863',
                                        'employees'             => '930',
                                        'website'               => 'http://www.Unnamed.com',
                                        'annualRevenue'         => '474000000',
                                        'anyCity'               => 'Austin',
                                        'anyState'              => 'TX',
                                        'anyStreet'             => '27054 West Michigan Lane',
                                        'anyPostalCode'         => '78759',
                                        'anyCountry'            => 'USA',
                                        'anyEmail'              => 'info@myNewAccount.com',
                                        'anyOptOutEmail'        => array('value' => '1'),
                                        'anyInvalidEmail'       => array('value' => ''),
                                        'ownedItemsOnly'        => '1',
                                        'industry'              => array('value' => 'Automotive'),
                                        'decimal'               => '123',
                                        'integer'               => '12',
                                        'phone'                 => '259-784-2169',
                                        'text'                  => 'This is a test Text',
                                        'textarea'              => 'This is a test TextArea',
                                        'url'                   => 'http://wwww.abc.com',
                                        'checkbox'              => array('value'  => '1'),
                                        'currency'              => array('value'  => 45),
                                        'picklist'              => array('value'  => 'a'),
                                        'multiselect'           => array('values' => array('ff', 'rr')),
                                        'tagcloud'              => array('values' => array('writing', 'gardening')),
                                        'countrypicklist'       => array('value'  => 'bbbb'),
                                        'statepicklist'         => array('value'  => 'bbb1'),
                                        'citypicklist'          => array('value'  => 'bb1'),
                                        'radio'                 => array('value'  => 'd'),
                                        'date__Date'            => array('type'   => 'Today'),
                                        'datetime__DateTime'    => array('type'   => 'Today')),
                                     'ajax' =>  'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default');

            //Check if the account name exists after the search is performed on the basis of the
            //custom fields added to the accounts module.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "myNewAccount") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterCreatingTheAccountUser
         */
        public function testEditOfTheAccountUserForTheTagCloudFieldAfterRemovingAllTagsPlacedForAccountsModule()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Get the account id from the recently created account.
            $accountId      = self::getModelIdByModelNameAndName('Account', 'myNewAccount');

            //Edit and save the account.
            $this->setGetArray(array('id' => $accountId));
            $this->setPostArray(array('Account' => array(
                            'name'                              => 'myEditAccount',
                            'officePhone'                       => '259-734-2169',
                            'industry'                          => array('value' => 'Energy'),
                            'officeFax'                         => '299-825-7863',
                            'employees'                         => '630',
                            'annualRevenue'                     => '472000000',
                            'type'                              => array('value' => 'Customer'),
                            'website'                           => 'http://www.UnnamedEdit.com',
                            'primaryEmail'                      => array('emailAddress' => 'info@myEditAccount.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => '',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'billingAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'shippingAddress'                   => array('street1' => '8519 East Franklin Center',
                                                                         'street2' => '',
                                                                         'city' => 'Chicago',
                                                                         'state' => 'IL',
                                                                         'postalCode' => '60652',
                                                                         'country' => 'USA'),
                            'description'                       => 'This is a Edit Description',
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'checkbox'                          => '0',
                            'currency'                          => array('value'   => 40,
                                                                          'currency' => array(
                                                                          'id' => $baseCurrency->id)),
                            'decimal'                           => '12',
                            'picklist'                          => array('value'  => 'b'),
                            'multiselect'                       => array('values' =>  array('gg', 'hh')),
                            'tagcloud'                          => array('values' =>  array()),
                            'countrypicklist'                   => array('value'  => 'aaaa'),
                            'statepicklist'                     => array('value'  => 'aaa1'),
                            'citypicklist'                      => array('value'  => 'ab1'),
                            'integer'                           => '11',
                            'phone'                             => '259-784-2069',
                            'radio'                             => array('value' => 'e'),
                            'text'                              => 'This is a test Edit Text',
                            'textarea'                          => 'This is a test Edit TextArea',
                            'url'                               => 'http://wwww.abc-edit.com'),
                            'save' => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $account = Account::getByName('myEditAccount');

            //Retrieve the permission of the account
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($account[0]->id));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name                           , 'myEditAccount');
            $this->assertEquals($account[0]->officePhone                    , '259-734-2169');
            $this->assertEquals($account[0]->industry->value                , 'Energy');
            $this->assertEquals($account[0]->officeFax                      , '299-825-7863');
            $this->assertEquals($account[0]->employees                      , '630');
            $this->assertEquals($account[0]->annualRevenue                  , '472000000');
            $this->assertEquals($account[0]->type->value                    , 'Customer');
            $this->assertEquals($account[0]->website                        , 'http://www.UnnamedEdit.com');
            $this->assertEquals($account[0]->primaryEmail->emailAddress     , 'info@myEditAccount.com');
            $this->assertEquals($account[0]->primaryEmail->optOut           , '0');
            $this->assertEquals($account[0]->primaryEmail->isInvalid        , '0');
            $this->assertEquals($account[0]->secondaryEmail->emailAddress   , '');
            $this->assertEquals($account[0]->secondaryEmail->optOut         , '0');
            $this->assertEquals($account[0]->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($account[0]->billingAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($account[0]->billingAddress->street2        , '');
            $this->assertEquals($account[0]->billingAddress->city           , 'San Jose');
            $this->assertEquals($account[0]->billingAddress->state          , 'CA');
            $this->assertEquals($account[0]->billingAddress->postalCode     , '95131');
            $this->assertEquals($account[0]->billingAddress->country        , 'USA');
            $this->assertEquals($account[0]->shippingAddress->street1       , '8519 East Franklin Center');
            $this->assertEquals($account[0]->shippingAddress->street2       , '');
            $this->assertEquals($account[0]->shippingAddress->city          , 'Chicago');
            $this->assertEquals($account[0]->shippingAddress->state         , 'IL');
            $this->assertEquals($account[0]->shippingAddress->postalCode    , '60652');
            $this->assertEquals($account[0]->shippingAddress->country       , 'USA');
            $this->assertEquals($account[0]->description                    , 'This is a Edit Description');
            $this->assertEquals(1                                           , count($readWritePermitables));
            $this->assertEquals(0                                           , count($readOnlyPermitables));
            $this->assertEquals($account[0]->checkbox                       , '0');
            $this->assertEquals($account[0]->currency->value                ,  40);
            $this->assertEquals($account[0]->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($account[0]->date                           , $dateAssert);
            $this->assertEquals($account[0]->datetime                       , $datetimeAssert);
            $this->assertEquals($account[0]->decimal                        , '12');
            $this->assertEquals($account[0]->picklist->value                , 'b');
            $this->assertEquals($account[0]->integer                        ,  11);
            $this->assertEquals($account[0]->phone                          , '259-784-2069');
            $this->assertEquals($account[0]->radio->value                   , 'e');
            $this->assertEquals($account[0]->text                           , 'This is a test Edit Text');
            $this->assertEquals($account[0]->textarea                       , 'This is a test Edit TextArea');
            $this->assertEquals($account[0]->url                            , 'http://wwww.abc-edit.com');
            $this->assertEquals($account[0]->countrypicklist->value         , 'aaaa');
            $this->assertEquals($account[0]->statepicklist->value           , 'aaa1');
            $this->assertEquals($account[0]->citypicklist->value            , 'ab1');
            $this->assertContains('gg'                                      , $account[0]->multiselect->values);
            $this->assertContains('hh'                                      , $account[0]->multiselect->values);
            $this->assertNotContains('reading'                              , $account[0]->tagcloud->values);
            $this->assertNotContains('writing'                              , $account[0]->tagcloud->values);
            $this->assertNotContains('surfing'                              , $account[0]->tagcloud->values);
            $this->assertNotContains('gardening'                            , $account[0]->tagcloud->values);

            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Account');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $account[0]);
            $this->assertEquals(472000630                                   , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheAccountUserForTheTagCloudFieldAfterRemovingAllTagsPlacedForAccountsModule
         */
        public function testEditOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Get the account id from the recently created account.
            $account        = Account::getByName('myEditAccount');
            $accountId      = $account[0]->id;

            //Edit and save the account.
            $this->setGetArray(array('id' => $accountId));
            $this->setPostArray(array('Account' => array(
                            'name'                              => 'myEditAccount',
                            'officePhone'                       => '259-734-2169',
                            'industry'                          => array('value' => 'Energy'),
                            'officeFax'                         => '299-825-7863',
                            'employees'                         => '630',
                            'annualRevenue'                     => '472000000',
                            'type'                              => array('value' => 'Customer'),
                            'website'                           => 'http://www.UnnamedEdit.com',
                            'primaryEmail'                      => array('emailAddress' => 'info@myEditAccount.com',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'secondaryEmail'                    => array('emailAddress' => '',
                                                                         'optOut' => '0',
                                                                         'isInvalid' => '0'),
                            'billingAddress'                    => array('street1' => '26378 South Arlington Ave',
                                                                         'street2' => '',
                                                                         'city' => 'San Jose',
                                                                         'state' => 'CA',
                                                                         'postalCode' => '95131',
                                                                         'country' => 'USA'),
                            'shippingAddress'                   => array('street1' => '8519 East Franklin Center',
                                                                         'street2' => '',
                                                                         'city' => 'Chicago',
                                                                         'state' => 'IL',
                                                                         'postalCode' => '60652',
                                                                         'country' => 'USA'),
                            'description'                       => 'This is a Edit Description',
                            'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                            'date'                              => $date,
                            'datetime'                          => $datetime,
                            'checkbox'                          => '0',
                            'currency'                          => array('value'   => 40,
                                                                          'currency' => array(
                                                                          'id' => $baseCurrency->id)),
                            'decimal'                           => '12',
                            'picklist'                          => array('value'  => 'b'),
                            'multiselect'                       => array('values' =>  array('gg', 'hh')),
                            'tagcloud'                          => array('values' =>  array('reading', 'surfing')),
                            'countrypicklist'                   => array('value'  => 'aaaa'),
                            'statepicklist'                     => array('value'  => 'aaa1'),
                            'citypicklist'                      => array('value'  => 'ab1'),
                            'integer'                           => '11',
                            'phone'                             => '259-784-2069',
                            'radio'                             => array('value' => 'e'),
                            'text'                              => 'This is a test Edit Text',
                            'textarea'                          => 'This is a test Edit TextArea',
                            'url'                               => 'http://wwww.abc-edit.com'),
                            'save' => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/edit');

            //Check the details if they are saved properly for the custom fields after the edit.
            $account = Account::getByName('myEditAccount');

            //Retrieve the permission of the account
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Account::getById($account[0]->id));
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables  = $explicitReadWriteModelPermissions->getReadOnlyPermitables();
            $this->assertEquals(1, count($account));
            $this->assertEquals($account[0]->name                           , 'myEditAccount');
            $this->assertEquals($account[0]->officePhone                    , '259-734-2169');
            $this->assertEquals($account[0]->industry->value                , 'Energy');
            $this->assertEquals($account[0]->officeFax                      , '299-825-7863');
            $this->assertEquals($account[0]->employees                      , '630');
            $this->assertEquals($account[0]->annualRevenue                  , '472000000');
            $this->assertEquals($account[0]->type->value                    , 'Customer');
            $this->assertEquals($account[0]->website                        , 'http://www.UnnamedEdit.com');
            $this->assertEquals($account[0]->primaryEmail->emailAddress     , 'info@myEditAccount.com');
            $this->assertEquals($account[0]->primaryEmail->optOut           , '0');
            $this->assertEquals($account[0]->primaryEmail->isInvalid        , '0');
            $this->assertEquals($account[0]->secondaryEmail->emailAddress   , '');
            $this->assertEquals($account[0]->secondaryEmail->optOut         , '0');
            $this->assertEquals($account[0]->secondaryEmail->isInvalid      , '0');
            $this->assertEquals($account[0]->billingAddress->street1        , '26378 South Arlington Ave');
            $this->assertEquals($account[0]->billingAddress->street2        , '');
            $this->assertEquals($account[0]->billingAddress->city           , 'San Jose');
            $this->assertEquals($account[0]->billingAddress->state          , 'CA');
            $this->assertEquals($account[0]->billingAddress->postalCode     , '95131');
            $this->assertEquals($account[0]->billingAddress->country        , 'USA');
            $this->assertEquals($account[0]->shippingAddress->street1       , '8519 East Franklin Center');
            $this->assertEquals($account[0]->shippingAddress->street2       , '');
            $this->assertEquals($account[0]->shippingAddress->city          , 'Chicago');
            $this->assertEquals($account[0]->shippingAddress->state         , 'IL');
            $this->assertEquals($account[0]->shippingAddress->postalCode    , '60652');
            $this->assertEquals($account[0]->shippingAddress->country       , 'USA');
            $this->assertEquals($account[0]->description                    , 'This is a Edit Description');
            $this->assertEquals(1                                           , count($readWritePermitables));
            $this->assertEquals(0                                           , count($readOnlyPermitables));
            $this->assertEquals($account[0]->checkbox                       , '0');
            $this->assertEquals($account[0]->currency->value                ,  40);
            $this->assertEquals($account[0]->currency->currency->id         , $baseCurrency->id);
            $this->assertEquals($account[0]->date                           , $dateAssert);
            $this->assertEquals($account[0]->datetime                       , $datetimeAssert);
            $this->assertEquals($account[0]->decimal                        , '12');
            $this->assertEquals($account[0]->picklist->value                , 'b');
            $this->assertEquals($account[0]->integer                        ,  11);
            $this->assertEquals($account[0]->phone                          , '259-784-2069');
            $this->assertEquals($account[0]->radio->value                   , 'e');
            $this->assertEquals($account[0]->text                           , 'This is a test Edit Text');
            $this->assertEquals($account[0]->textarea                       , 'This is a test Edit TextArea');
            $this->assertEquals($account[0]->url                            , 'http://wwww.abc-edit.com');
            $this->assertEquals($account[0]->countrypicklist->value         , 'aaaa');
            $this->assertEquals($account[0]->statepicklist->value           , 'aaa1');
            $this->assertEquals($account[0]->citypicklist->value            , 'ab1');
            $this->assertContains('gg'                                      , $account[0]->multiselect->values);
            $this->assertContains('hh'                                      , $account[0]->multiselect->values);
            $this->assertContains('reading'                                 , $account[0]->tagcloud->values);
            $this->assertContains('surfing'                                 , $account[0]->tagcloud->values);

            $metadata            = CalculatedDerivedAttributeMetadata::
                                   getByNameAndModelClassName('calculatednumber', 'Account');
            $testCalculatedValue = CalculatedNumberUtil::calculateByFormulaAndModel($metadata->getFormula(), $account[0]);
            $this->assertEquals(472000630                                   , $testCalculatedValue);
        }

        /**
         * @depends testEditOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterEditingTheAccountUser()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created account using the customfield.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'AccountsSearchForm' => AccountsDesignerWalkthroughHelperUtil::fetchAccountsSearchFormGetData(),
                        'ajax'               => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default');

            //Assert that the edit account exits after the edit and is diaplayed on the search page.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "myEditAccount") > 0);
        }

        /**
         * @depends testEditOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleWithMultiSelectValueSetToNull()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created account using the customfield.
            $this->resetPostArray();
            $this->setGetArray(array(
                        'AccountsSearchForm' => AccountsDesignerWalkthroughHelperUtil::fetchAccountsSearchFormGetDataWithMultiSelectValueSetToNull(),
                        'ajax'               => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default');

            //Assert that the edit account exits after the edit and is diaplayed on the search page.
            $this->assertTrue(strpos($content, "Displaying 1-1 of 1 result(s).") > 0);
            $this->assertTrue(strpos($content, "myEditAccount") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterEditingTheAccountUser
         */
        public function testDeleteOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Get the account id from the recently edited account.
            $accountId = self::getModelIdByModelNameAndName('Account', 'myEditAccount');

            //Set the account id so as to delete the account.
            $this->setGetArray(array('id' => $accountId));
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/delete');

            //Check whether the account is deleted.
            $account = Account::getByName('myEditAccount');
            $this->assertEquals(0, count($account));
        }

        /**
         * @depends testDeleteOfTheAccountUserForTheCustomFieldsPlacedForAccountsModule
         */
        public function testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterDeletingTheAccount()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a created account using the customfield.
            $this->resetGetArray();
            $this->setGetArray(array(
                        'AccountsSearchForm' => AccountsDesignerWalkthroughHelperUtil::fetchAccountsSearchFormGetData(),
                        'ajax'               => 'list-view')
            );
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default');

            //Assert that the edit account does not exits after the search.
            $this->assertTrue(strpos($content, "No results found.") > 0);
            $this->assertFalse(strpos($content, "26378 South Arlington Ave") > 0);
        }

        /**
         * @depends testWhetherSearchWorksForTheCustomFieldsPlacedForAccountsModuleAfterDeletingTheAccount
         */
        public function testTypeAheadWorksForTheTagCloudFieldPlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'rea'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "reading") > 0);
        }

        /**
         * @depends testTypeAheadWorksForTheTagCloudFieldPlacedForAccountsModule
         */
        public function testLabelLocalizationForTheTagCloudFieldPlacedForAccountsModule()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $languageHelper = new ZurmoLanguageHelper();
            $languageHelper->load();
            $this->assertEquals('en', $languageHelper->getForCurrentUser());
            Yii::app()->user->userModel->language = 'fr';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $languageHelper->setActive('fr');
            $this->assertEquals('fr', Yii::app()->user->getState('language'));

            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Search a list item by typing in tag cloud attribute.
            $this->resetPostArray();
            $this->setGetArray(array('name' => 'tagcloud',
                                     'term' => 'surf'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/default/autoCompleteCustomFieldData');

            //Check if the returned content contains the expected vlaue
            $this->assertTrue(strpos($content, "surfing fr") > 0);
        }
    }
?>