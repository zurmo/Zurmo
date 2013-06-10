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

    /**
     * ContactWebForms Module Walkthrough.
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class ContactWebFormsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 1");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 2");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 3");
            ContactWebFormTestHelper::createContactWebFormByName("Web Form 4");
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //Create contact web form owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contactWebForm = ContactWebFormTestHelper::createContactWebFormByName('Web Form 5');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            //Now test peon with elevated rights to tabs /other available rights
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to contact web forms
            $nobody->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_ACCESS_CONTACT_WEB_FORMS);
            $nobody->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_CREATE_CONTACT_WEB_FORMS);
            $nobody->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_DELETE_CONTACT_WEB_FORMS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = $nobody;
            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/list');

            $this->assertFalse(strpos($content, 'Billy Corgan') === false);
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/create');
            //Test nobody can view an existing web forms he owns.
            $contactWebForm = ContactWebFormTestHelper::createContactWebFormByName('webFormOwnedByNobody', $nobody);

            //At this point the listview for web forms should show the search/list and not the helper screen.
            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/list');
            $this->assertTrue(strpos($content, 'Billy Corgan') === false);

            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create contact web form owned by user super.
            $super             = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contactWebForm    = ContactWebFormTestHelper::createContactWebFormByName('contactWebFormForElevationToModelTest', $super);

            //Test nobody, access to edit and details should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $contactWebForm->addPermissions($nobody, Permission::READ);
            $this->assertTrue($contactWebForm->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');

            //Test nobody, access to edit should fail.
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            $contactWebFormId  = $contactWebForm->id;
            $contactWebForm->forget();
            $contactWebForm    = ContactWebForm::getById($contactWebFormId);
            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $contactWebForm->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contactWebForm->save());

            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');

            $contactWebFormId  = $contactWebForm->id;
            $contactWebForm->forget();
            $contactWebForm    = ContactWebForm::getById($contactWebFormId);
            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $contactWebForm->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($contactWebForm->save());

            //Test nobody, access to detail should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

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

            //create web form owned by super

            $contactWebForm2 = ContactWebFormTestHelper::createContactWebFormByName('testingParentRolePermission', $super);

            //Test userInParentRole, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $contactWebForm2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($contactWebForm2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');

            $contactWebFormId  = $contactWebForm2->id;
            $contactWebForm2->forget();
            $contactWebForm2   = ContactWebForm::getById($contactWebFormId);

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $contactWebForm2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contactWebForm2->save());

            //Test userInChildRole, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');

            $contactWebFormId  = $contactWebForm2->id;
            $contactWebForm2->forget();
            $contactWebForm2   = ContactWebForm::getById($contactWebFormId);
            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $contactWebForm2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($contactWebForm2->save());

            //Test userInChildRole, access to detail should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            //Test userInParentRole, access to detail should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

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

            //Add access for the confused user to ContactWebForms and creation of ContactWebForms.
            $userInChildGroup->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_ACCESS_CONTACT_WEB_FORMS);
            $userInChildGroup->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_CREATE_CONTACT_WEB_FORMS);
            $this->assertTrue($userInChildGroup->save());

            //create web form owned by super
            $contactWebForm3 = ContactWebFormTestHelper::createContactWebFormByName('testingParentGroupPermission', $super);

            //Test userInParentGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            //Test userInChildGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $contactWebForm3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($contactWebForm3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/details');

            $contactWebFormId  = $contactWebForm3->id;
            $contactWebForm3->forget();
            $contactWebForm3   = ContactWebForm::getById($contactWebFormId);
            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $contactWebForm3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($contactWebForm3->save());

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default/edit');

            $contactWebFormId  = $contactWebForm3->id;
            $contactWebForm3->forget();
            $contactWebForm3   = ContactWebForm::getById($contactWebFormId);
            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $contactWebForm3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($contactWebForm3->save());

            //Test userInChildGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

            //Test userInParentGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/details');
            $this->setGetArray(array('id' => $contactWebForm3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contactWebForms/default/edit');

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

            //clear up the role relationships between users so not to effect next assertions
            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToModels
         */
        public function testRegularUserViewingContactWebFormWithoutAccessToAccount()
        {
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser              = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('ContactWebFormsModule', ContactWebFormsModule::RIGHT_ACCESS_CONTACT_WEB_FORMS);
            $this->assertTrue($aUser->save());
            $aUser              = User::getByUsername('aUser');
            $contactWebForm     = ContactWebFormTestHelper::createContactWebFormByName('contactWebFormOwnedByaUser', $aUser);
            $id                 = $contactWebForm->id;
            $contactWebForm->forget();
            unset($contactWebForm);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $content = $this->runControllerWithNoExceptionsAndGetContent('contactWebForms/default');
            $this->assertFalse(strpos($content, 'Fatal error: Method ContactWebForm::__toString() must not throw an exception') > 0);
        }
    }
?>