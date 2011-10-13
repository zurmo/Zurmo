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
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class AccountsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount',  Yii::app()->user->userModel);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', Yii::app()->user->userModel);
            AccountTestHelper::createAccountByNameForOwner('superAccount3', Yii::app()->user->userModel);
            AccountTestHelper::createAccountByNameForOwner('superAccount4', Yii::app()->user->userModel);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, Yii::app()->user->userModel);
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {           
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/massEdit');
            $this->setGetArray(array('selectAll' => '1', 'Account_page' => 2));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/massEditProgressSave');

            //Autocomplete for Account should fail
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/autoComplete');

            //actionModalList should fail
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/modalList');

            //actionAuditEventsModalList should fail
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/auditEventsModalList');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            
            //Now test peon with elevated rights to accounts
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_DELETE_ACCOUNTS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');

            //Test nobody can view an existing account he owns.
            $account = AccountTestHelper::createAccountByNameForOwner('accountOwnedByNobody', $nobody);
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test nobody can delete an existing account he owns and it redirects to index.
            $this->setGetArray(array('id' => $account->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/index'); // Not Coding Standard

            //Autocomplete for Account should not fail.
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/autoComplete');

            //actionModalList for Account should not fail.
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/modalList');
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create account owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $account = AccountTestHelper::createAccountByNameForOwner('testingAccountsForElevationToModelTest', $super);

            //Test nobody, access to edit, details and delete should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $account->addPermissions($nobody, Permission::READ);
            $this->assertTrue($account->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test nobody, access to edit and delete should fail.
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $account->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account->save());

            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test nobody, access to delete should fail.
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $account->removePermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account->save());

            //Test nobody, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give nobody access to read, write and delete
            Yii::app()->user->userModel = $super;
            $account->addPermissions($nobody, Permission::READ_WRITE_DELETE);
            $this->assertTrue($account->save());

            //Test nobody, access to delete should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $account->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/index'); // Not Coding Standard

            //create some roles
            Yii::app()->user->userModel = $super;
            $parentRole = new Role();
            $parentRole->name = 'AAA';
            $this->assertTrue($parentRole->save());

            $childRole = new Role();
            $childRole->name = 'BBB';
            $this->assertTrue($childRole->save());

            $userInParentRole = User::getByUsername('confused');
            $userInChildRole = User::getByUsername('nobody');

            $childRole->users->add($userInChildRole);
            $this->assertTrue($childRole->save());
            $parentRole->users->add($userInParentRole);
            $parentRole->roles->add($childRole);
            $this->assertTrue($parentRole->save());

            //create account owned by super
            $account2 = AccountTestHelper::createAccountByNameForOwner('testingAccountsParentRolePermission',$super);

            //Test userInChildRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInParentRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $account2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInChildRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInParentRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $account2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account2->save());

            //Test userInChildRole, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test userInChildRole, access to delete should fail.
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test userInParentRole, access to delete should fail.
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $account2->removePermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account2->save());

            //Test userInChildRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInParentRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give userInChildRole access to read, write and delete
            Yii::app()->user->userModel = $super;
            $account2->addPermissions($userInChildRole, Permission::READ_WRITE_DELETE);
            $this->assertTrue($account2->save());

            //Test userInParentRole, access to delete should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/index'); // Not Coding Standard

            //clear up the role relationships between users so not to effect next assertions
            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            //create some groups and assign users to groups
            Yii::app()->user->userModel = $super;
            $parentGroup = new Group();
            $parentGroup->name = 'AAA';
            $this->assertTrue($parentGroup->save());

            $childGroup = new Group();
            $childGroup->name = 'BBB';
            $this->assertTrue($childGroup->save());

            $userInChildGroup = User::getByUsername('confused');
            $userInParentGroup = User::getByUsername('nobody');

            $childGroup->users->add($userInChildGroup);
            $this->assertTrue($childGroup->save());
            $parentGroup->users->add($userInParentGroup);
            $parentGroup->groups->add($childGroup);
            $this->assertTrue($parentGroup->save());
            $parentGroup->forget();
            $childGroup->forget();
            $parentGroup = Group::getByName('AAA');
            $childGroup = Group::getByName('BBB');

            //Add access for the confused user to accounts and creation of accounts.
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_DELETE_ACCOUNTS);
            $this->assertTrue($userInChildGroup->save());

            //create account owned by super
            $account3 = AccountTestHelper::createAccountByNameForOwner('testingAccountsParentGroupPermission', $super);

            //Test userInParentGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInChildGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $account3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInParentGroup, access to edit and delete should fail.
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInChildGroup, access to edit and delete should fail.
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $account3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account3->save());

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test userInParentGroup, access to delete should fail.
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Test userInChildGroup, access to delete should fail.
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $account3->removePermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($account3->save());

            //Test userInChildGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //Test userInParentGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');
            $this->setGetArray(array('id' => $account3->id));			
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/edit');
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/delete');

            //give parentGroup access to read, write and delete
            Yii::app()->user->userModel = $super;
            $account3->addPermissions($parentGroup, Permission::READ_WRITE_DELETE);
            $this->assertTrue($account3->save());

            //Test userInChildGroup, access to delete should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/delete',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/index'); // Not Coding Standard

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');

            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToModels
         */
        public function testRegularUserSwitchingOwnershipLosesAccessToAccount()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            //$this->assertEquals(Right::DENY, $confused->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            //$this->assertEquals(Right::DENY, $confused->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS));
            //$confused->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            //$confused->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            //$this->assertTrue($confused->save());

            Yii::app()->user->userModel = $confused;
            $account = AccountTestHelper::createAccountByNameForOwner('Switcheroo', $confused);
            //User can get to edit ok.
            $this->setGetArray(array('id' => $account->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');

            //Save account, just name.
            $this->setPostArray(array('Account' => array('name' => 'Switcheroo Inc.')));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/details&id=' . $account->id); // Not Coding Standard

            //Now save account changing the owner, the redirect should go to the list view and provide a flash message.
            $this->setPostArray(array('Account' => array('owner' => array('id' => $super->id))));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/edit',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=accounts/default/index'); // Not Coding Standard
            ///Confirm flash message is set.
            $this->assertContains('You no longer have permissions to access Switcheroo Inc',
                                  Yii::app()->user->getFlash('notification'));
        }

        /**
         * @depends testRegularUserSwitchingOwnershipLosesAccessToAccount
         */
        public function testRegularUserBullkWriteWhereSomeItemsTheyDontHavePrivledgesToDoIt()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $billy = User::getByUsername('billy');
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE));
            $confused->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE);
            $this->assertTrue($confused->save());
            $account1 = AccountTestHelper::createAccountByNameForOwner('canUpdate', $confused);
            $account2 = AccountTestHelper::createAccountByNameForOwner('canUpdate2', $confused);
            $account3 = AccountTestHelper::createAccountByNameForOwner('cannotUpdate', $billy);
            $this->assertEquals($confused,  $account1->owner);
            $this->assertEquals($confused,  $account2->owner);
            $this->assertEquals($billy, $account3->owner);

            //Give confused user read access to $account3
            $this->assertNotEquals($account3->owner->id, $confused->id);
            $this->assertEquals(Permission::NONE, $account3->getEffectivePermissions      ($confused));
            $account3->addPermissions($confused, Permission::READ);
            $this->assertTrue($account3->save());
            $this->assertEquals(Permission::READ, $account3->getEffectivePermissions      ($confused));

            //Make confused user the current user.
            Yii::app()->user->userModel = $confused;

            //Load MassEdit view for the 3 accounts.
            $selectedIds = $account1->id . ',' . $account2->id . ',' . $account3->id ;    // Not Coding Standard
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>3</strong>&#160;records selected for updating') === false);

            //Test trying to change the owner to super and trying to change name which is required, but leaving it blank.
            //This will result in a validation error, but since since the owner has been selected as super, we want
            //to make sure there are no exceptions and the validation appears in the user interface correctly.
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('name' => '', 'owner' => array('id' => $super->id)),
                'MassEdit' => array('name' => 1, 'owner' => 1)
            ));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>3</strong>&#160;records selected for updating') === false);

            //Now set office phone to a real value, keep owner set at super, and try again. This time the mass update
            //should be successful except for account3 which the confused user does not have write access too.
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Account_page' => 1));
            $this->setPostArray(array(
                'Account'  => array('name' => '7799', 'owner' => array('id' => $super->id)),
                'MassEdit' => array('name' => 1, 'owner' => 1)
            ));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('accounts/default/massEdit');
            //Confirm the flash message shows the correct information that 1 failed.
            $this->assertContains('Successfully updated 2 records. 1 account skipped because you do not have sufficient permissions.',
                                  Yii::app()->user->getFlash('notification'));

            //Confirm updates are correct
            Yii::app()->user->userModel = $super;
            $account1 = Account::getById($account1->id);
            $account2 = Account::getById($account2->id);
            $account3 = Account::getById($account3->id);          
            
            $this->assertEquals ('7799',        $account1->name);
            $this->assertEquals ('7799',        $account2->name);
            $this->assertEquals ('cannotUpdate',$account3->name);
            $this->assertEquals ("".$super,     "".$account2->owner);
            $this->assertEquals ("".$super,     "".$account2->owner);
            $this->assertEquals ("".$billy,     "".$account3->owner);
        }
    }
?>
