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
    * Bug :Date custom field cannot be cleared in the view
    * Above Bug Resolved
    * Test to show that date custom field can be cleared in the view
    */
    class AccountsSuperUserCustomDateNullValueBugWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a account for testing
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
        }

        public function testSuperUserCustomDateNotRequiredFieldWalkthroughForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));
            //View creation screen, then create custom field for date custom field type.
            $this->createDateNotRequiredCustomFieldByModule ('AccountsModule', 'datenotreq');
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountEditAndDetailsView'));
            $layout = AccountsDesignerWalkthroughHelperUtil::getAccountEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
        }

        public function testCreateAnAccountUserAfterTheCustomFieldsArePlacedForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Set the date and datetime variable values here
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new account based on the custom fields.
            $this->resetGetArray();
            $this->setPostArray(array('Account' => array(
                                    'name'                                  => 'myNewAccount',
                                    'officePhone'                           => '259-784-2169',
                                    'industry'                              => array('value' => 'Automotive'),
                                    'officeFax'                             => '299-845-7863',
                                    'employees'                             => '930',
                                    'annualRevenue'                         => '474000000',
                                    'type'                                  => array('value' => 'Prospect'),
                                    'website'                               => 'http://www.Unnamed.com',
                                    'primaryEmail'                          => array('emailAddress' => 'info@myNewAccount.com',
                                                                                  'optOut' => '1',
                                                                                  'isInvalid' => '0'),
                                    'secondaryEmail'                        => array('emailAddress' => '',
                                                                                  'optOut' => '0',
                                                                                  'isInvalid' => '0'),
                                    'billingAddress'                        => array('street1' => '6466 South Madison Creek',
                                                                                  'street2' => '',
                                                                                  'city' => 'Chicago',
                                                                                  'state' => 'IL',
                                                                                  'postalCode' => '60652',
                                                                                  'country' => 'USA'),
                                    'shippingAddress'                       => array('street1' => '27054 West Michigan Lane',
                                                                                  'street2' => '',
                                                                                  'city' => 'Austin',
                                                                                  'state' => 'TX',
                                                                                  'postalCode' => '78759',
                                                                                  'country' => 'USA'),
                                    'description'                           => 'This is a Description',
                                    'explicitReadWriteModelPermissions'     => array('type' => null),
                                    'datenotreqCstm'                        => $date)));
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
            $this->assertEquals($account[0]->datenotreqCstm                 , $dateAssert);
        }

        public function testEditAnAccountUserAfterTheCustomDateFieldNullValueBugForAccountsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $baseCurrency   = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $account        = Account::getByName('myNewAccount');
            $accountId      = $account[0]->id;

            //Edit and save the account.
            $this->setGetArray(array('id' => $accountId));
            $this->setPostArray(array('Account' => array(
                                    'name'                                  => 'myNewAccount',
                                    'officePhone'                           => '259-784-2169',
                                    'industry'                              => array('value' => 'Automotive'),
                                    'officeFax'                             => '299-845-7863',
                                    'employees'                             => '930',
                                    'annualRevenue'                         => '474000000',
                                    'type'                                  => array('value' => 'Prospect'),
                                    'website'                               => 'http://www.Unnamed.com',
                                    'primaryEmail'                          => array('emailAddress' => 'info@myNewAccount.com',
                                                                                  'optOut' => '1',
                                                                                  'isInvalid' => '0'),
                                    'secondaryEmail'                        => array('emailAddress' => '',
                                                                                  'optOut' => '0',
                                                                                  'isInvalid' => '0'),
                                    'billingAddress'                        => array('street1' => '6466 South Madison Creek',
                                                                                  'street2' => '',
                                                                                  'city' => 'Chicago',
                                                                                  'state' => 'IL',
                                                                                  'postalCode' => '60652',
                                                                                  'country' => 'USA'),
                                    'shippingAddress'                       => array('street1' => '27054 West Michigan Lane',
                                                                                  'street2' => '',
                                                                                  'city' => 'Austin',
                                                                                  'state' => 'TX',
                                                                                  'postalCode' => '78759',
                                                                                  'country' => 'USA'),
                                    'description'                           => 'This is a Description',
                                    'explicitReadWriteModelPermissions'     => array('type' => null),
                                    'datenotreqCstm'                        => ''))); //setting null value
            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/edit');

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
            $this->assertEquals($account[0]->datenotreqCstm                 , null);
        }
    }
?>