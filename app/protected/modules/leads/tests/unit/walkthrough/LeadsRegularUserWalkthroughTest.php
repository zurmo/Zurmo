<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Leads Module Walkthrough.
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class LeadsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;
            //Setup test data owned by the super user.
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead',  $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead2', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead3', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead4', $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testRegularUserAllControllerActions()
        {
            //Now test all portlet controller actions

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead

            //Now test peon with elevated permissions to models.
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //Create lead owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $lead = LeadTestHelper::createLeadByNameForOwner('Lead', $super);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/massEdit');
            $this->setGetArray(array('selectAll' => '1', 'Lead_page' => 2));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/massEditProgressSave');

            //Autocomplete for lead should fail
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/autoComplete');

            //actionModalList should fail
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/modalList');

            //actionAuditEventsModalList should fail
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/auditEventsModalList');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            //Now test peon with elevated rights to tabs /other available rights
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to leads
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_CREATE_LEADS);
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_DELETE_LEADS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/create');

            //Test nobody can view an existing lead he owns.
            $lead = LeadTestHelper::createLeadByNameForOwner('leadOwnedByNobody', $nobody);
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test nobody can delete an existing lead he owns and it redirects to index.
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete',
                        Yii::app()->createUrl('leads/default/index'));

            //Autocomplete for Lead should not fail.
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/autoComplete');

            //actionModalList for Lead should not fail.
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/modalList');
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create lead owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $lead = LeadTestHelper::createLeadByNameForOwner('leadForElevationToModelTest', $super);

            //Test nobody, access to edit, details and delete should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $lead->addPermissions($nobody, Permission::READ);
            $this->assertTrue($lead->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Test nobody, access to edit and delete should fail.
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $lead->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead->save());

            //Now the nobody user should be able to access the edit view and still the details view
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test nobody, access to delete should fail.
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $lead->removePermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead->save());

            //Test nobody, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give nobody access to read, write and delete
            Yii::app()->user->userModel = $super;
            $lead->addPermissions($nobody, Permission::READ_WRITE_DELETE);
            $this->assertTrue($lead->save());

            //now nobody should be able to delete a lead
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete',
                        Yii::app()->createUrl('leads/default/index'));

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

            //create lead owned by super
            $lead2 = LeadTestHelper::createLeadByNameForOwner('leadsParentRolePermission', $super);

            //Test userInChildRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInParentRole, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $lead2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($lead2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Test userInChildRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Test userInParentRole, access to edit and delete should fail.
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $lead2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead2->save());

            //Test userInChildRole, access to edit and delete should not fail and also detaisl view must be accessible.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test userInChildRole, access to delete should fail.
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test userInParentRole, access to delete should fail.
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $lead2->removePermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead2->save());

            //Test userInChildRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInParentRole, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give userInChildRole access to read, write and delete
            Yii::app()->user->userModel = $super;
            $lead2->addPermissions($userInChildRole, Permission::READ_WRITE_DELETE);
            $this->assertTrue($lead2->save());

            //Test userInParentRole, access to delete should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $lead2->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete',
                        Yii::app()->createUrl('leads/default/index'));

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

            //Add access for the confused user to leads and creation of leads.
            $userInChildGroup->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $userInChildGroup->setRight('LeadsModule', LeadsModule::RIGHT_CREATE_LEADS);
            $userInChildGroup->setRight('LeadsModule', LeadsModule::RIGHT_DELETE_LEADS);
            $this->assertTrue($userInChildGroup->save());

            //create lead owned by super
            $lead3 = LeadTestHelper::createLeadByNameForOwner('leadsParentGroupPermission', $super);

            //Test userInParentGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInChildGroup, access to details, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $lead3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($lead3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Test userInParentGroup, access to delete should fail.
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInChildGroup, access to edit and details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Test userInChildGroup, access to edit and delete should fail.
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $lead3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead3->save());

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test userInParentGroup, access to delete should fail.
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');

            //Test userInChildGroup, access to delete should fail.
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $lead3->removePermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($lead3->save());

            //Test userInChildGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //Test userInParentGroup, access to detail, edit and delete should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/details');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/edit');
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('leads/default/delete');

            //give parentGroup access to read, write and delete
            Yii::app()->user->userModel = $super;
            $lead3->addPermissions($parentGroup, Permission::READ_WRITE_DELETE);
            $this->assertTrue($lead3->save());

            //Test userInChildGroup, access to delete should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $lead3->id));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete',
                        Yii::app()->createUrl('leads/default/index'));

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $parentGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');
            $parentGroup                = Group::getByName('AAA');

            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }
        //todo: test lead conversion.

        public function testUserHasNoAccessToAccountsAndTriesToConvertWhenAccountIsOptional()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $belina = UserTestHelper::createBasicUser('belina');
            $lead = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $belina);
            $belina->setRight   ('LeadsModule', LeadsModule::RIGHT_CONVERT_LEADS, Right::ALLOW);
            $belina->setRight   ('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $belina->setRight   ('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS, Right::ALLOW);
            $belina->setRight   ('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $this->assertTrue($belina->save());
            $this->assertEquals(Right::DENY, $belina->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $belina = $this->logoutCurrentUserLoginNewUserAndGetByUsername('belina');

            //Now check that when belina tries to convert a lead, it will automatically make it an account.
            $convertToAccountSetting = LeadsModule::getConvertToAccountSetting();
            $this->assertTrue($convertToAccountSetting == LeadsModule::CONVERT_NO_ACCOUNT ||
                              $convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED);

            $oldStateValue = $lead->state->name;
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert');

            $contact = Contact::getById($lead->id);
            $this->assertNotEquals($oldStateValue, $contact->state->name);
        }

        /**
         * @depends testUserHasNoAccessToAccountsAndTriesToConvertWhenAccountIsOptional
         */
        public function testUserCanAccessAccountsButCannotCreateAccountShowConvertAction()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $belina = User::getByUserName('belina');
            $lead   = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $belina);
            $belina->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $this->assertTrue($belina->save());
            $belina = $this->logoutCurrentUserLoginNewUserAndGetByUsername('belina');
            $convertToAccountSetting = LeadsModule::getConvertToAccountSetting();
            $this->assertEquals(Right::DENY, $belina->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS));

            //The convert view should load up normally, except the option to create an account will not be pressent.
            //This tests that the view does in fact come up.
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/convert');
        }

        /**
         * @depends testUserCanAccessAccountsButCannotCreateAccountShowConvertAction
         */
        public function testLeadConversionMisconfigurationScenarios()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $bubby = UserTestHelper::createBasicUser('bubby');
            $lead  = LeadTestHelper::createLeadbyNameForOwner('BelinaLead1', $bubby);
            $bubby->setRight   ('LeadsModule', LeadsModule::RIGHT_CONVERT_LEADS, Right::ALLOW);
            $bubby->setRight   ('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS, Right::ALLOW);
            $this->assertTrue($bubby->save());

            //Scenario #1 - User does not have access to contacts
            $this->assertEquals(Right::DENY, $bubby->getEffectiveRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS));
            $bubby = $this->logoutCurrentUserLoginNewUserAndGetByUsername('bubby');
            //View will not show up properly.
            $this->setGetArray (array('id' => $lead->id));
            $this->runControllerWithExitExceptionAndGetContent('leads/default/convert');

            //Scenario #2 - User cannot access accounts and an account is required for conversion
            $bubby->setRight   ('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS, Right::ALLOW);
            $bubby->setRight   ('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $this->assertTrue($bubby->save());
            $metadata = LeadsModule::getMetadata();
            $metadata['global']['convertToAccountSetting'] = LeadsModule::CONVERT_ACCOUNT_REQUIRED;
            LeadsModule::setMetadata($metadata);

            //At this point because the account is required, the view will not come up properly.
            $this->setGetArray (array('id' => $lead->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('leads/default/convert');
            $this->assertFalse(strpos($content, 'Conversion is set to require an account.  Currently you do not have access to the accounts module.') === false);
        }
    }
?>