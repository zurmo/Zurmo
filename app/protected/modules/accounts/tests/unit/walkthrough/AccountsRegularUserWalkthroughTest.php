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
            //Now test peon with elevated rights to tabs /other available rights
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Now test peon with elevated permissions to models.
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToModels
         */
        public function testRegularUserSwitchingOwnershipLosesAccessToAccount()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS));
            $confused->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $confused->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $this->assertTrue($confused->save());

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
            $selectedIds = $account1->id . ',' . $account2->id . ',' . $account3->id ;
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
                'Account'  => array('name' => '7799','owner' => array('id' => $super->id)),
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
            $this->assertEquals ('7799',         $account1->name);
            $this->assertEquals ('7799',         $account2->name);
            $this->assertEquals ('cannotUpdate', $account3->name);
            $this->assertEquals ($super,         $account1->owner);
            $this->assertEquals ($super,         $account2->owner);
            $this->assertEquals ($billy,         $account3->owner);

        }
    }
?>