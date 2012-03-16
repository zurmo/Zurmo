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
     * User Module
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class UsersSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $aUser = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $saved = $aUser->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $bUser = UserTestHelper::createBasicUser('bUser');
            $cUser = UserTestHelper::createBasicUser('cUser');
            $dUser = UserTestHelper::createBasicUser('dUser');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('users/default');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/create');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');

            //Access to admin configuration should be allowed.
            $this->runControllerWithNoExceptionsAndGetContent('configuration');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $aUser = User::getByUsername('auser');
            $bUser = User::getByUsername('buser');
            $cUser = User::getByUsername('cuser');
            $dUser = User::getByUsername('duser');
            $super = User::getByUsername('super');

            $this->setGetArray(array('id' => $super->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $bUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $super->id));
            //Access to User Role edit link and control available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertTrue(strpos($content, 'User_role_SelectLink') !== false);
            $this->assertTrue(strpos($content, 'User_role_name') !== false);

            $this->setGetArray(array('id' => $aUser->id));
            //Access to User Role edit link and control available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertTrue(strpos($content, 'User_role_SelectLink') !== false);
            $this->assertTrue(strpos($content, 'User_role_name') !== false);

            $users = User::getAll();
            $this->assertEquals(5, count($users));
            //Save user.
            $this->assertTrue($aUser->id > 0);
            $this->assertEquals('aUserson', $aUser->lastName);
            $this->assertEquals(null, $aUser->officePhone);
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('User' =>
                array('officePhone' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $aUser = User::getById($aUser->id);
            $this->assertEquals('456765421', $aUser->officePhone);
            $this->assertEquals('aUserson',  $aUser->lastName);
            //Test having a failed validation on the user during save.
            $this->setGetArray (array('id'      => $aUser->id));
            $this->setPostArray(array('User' => array('lastName' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            //LastName for aUser should still be aUserson.
            //Need to forget aUser, since it has lastName = '' from the setAttributes called in actionEdit.
            //Retrieve aUser and confirm the lastName is still aUserson.
            $aUser->forget();
            $aUser = User::getByUsername('auser');
            $this->assertEquals('aUserson', $aUser->lastName);

            //Load Model Detail View
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/details');

            //Load Model Security Detail View
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/securityDetails');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $users = User::getAll();
            $this->assertEquals(5, count($users));
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>5</strong>&#160;records selected for updating') === false);
            //save Model MassEdit for selected Ids
            //Test that the 4 contacts do not have the office phone number we are populating them with.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertNotEquals   ('7788', $user1->officePhone);
            $this->assertNotEquals   ('7788', $user2->officePhone);
            $this->assertNotEquals   ('7788', $user3->officePhone);
            $this->assertNotEquals   ('7788', $user4->officePhone);
            $this->setGetArray(array(
                'selectedIds'  => $aUser->id . ',' . $bUser->id, // Not Coding Standard
                'selectAll'    => '',
                'User_page'    => 1));
            $this->setPostArray(array(
                'User'      => array('officePhone' => '7788'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/massEdit');

            //Test that the 2 contacts have the new office phone number and the other contacts do not.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertEquals      ('7788', $user1->officePhone);
            $this->assertEquals      ('7788', $user2->officePhone);
            $this->assertNotEquals   ('7788', $user3->officePhone);
            $this->assertNotEquals   ('7788', $user4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll'    => '1',
                'User_page'    => 1));
            $this->setPostArray(array(
                'User'         => array('officePhone' => '1234'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/massEdit');
            //Test that all accounts have the new phone number.
            $user1 = User::getById($aUser->id);
            $user2 = User::getById($bUser->id);
            $user3 = User::getById($cUser->id);
            $user4 = User::getById($dUser->id);
            $this->assertEquals   ('1234', $user1->officePhone);
            $this->assertEquals   ('1234', $user2->officePhone);
            $this->assertEquals   ('1234', $user3->officePhone);
            $this->assertEquals   ('1234', $user4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('users/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3, 4 and 5.
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":40') === false);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":60') === false);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":80') === false);
            $this->setGetArray(array('selectAll' => '1', 'User_page' => 5));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":100') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for User
            $this->setGetArray(array('term' => 'auser'));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/modalList');

            //Change password view.
            $this->setGetArray(array('id' => $aUser->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/changePassword');

            //Failed change password validation
            $this->setPostArray(array('ajax' => 'edit-form',
                'UserPasswordForm' => array('newPassword' => '', 'newPassword_repeat' => '')));
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changePassword');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Successful change password validation
            $this->setPostArray(array('ajax' => 'edit-form',
                'UserPasswordForm' => array('newPassword' => 'aNewPassword', 'newPassword_repeat' => 'aNewPassword')));
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/changePassword');
            $this->assertEquals('[]', $content);

            //Successful saved password change.
            $this->setPostArray(array('save' => 'Save',
                'UserPasswordForm' => array('newPassword' => 'bNewPassword', 'newPassword_repeat' => 'bNewPassword')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/changePassword');
            //Login using new password successfully.
            $identity = new UserIdentity('auser', 'bNewPassword');
            $authenticated = $identity->authenticate();
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);

            //User Configuration UI. Change aUser configuration values.
            //First make sure settings are not what we are setting them too.
            $this->assertNotEquals(9, Yii::app()->pagination->getByUserAndType($aUser, 'listPageSize'));
            $this->assertNotEquals(4, Yii::app()->pagination->getByUserAndType($aUser, 'subListPageSize'));
            //Load up configuration page.
            $this->setGetArray(array('id' => $aUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will fail validation.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(
                        'listPageSize' => 0,
                        'subListPageSize' => 4,
                        )));

            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will pass validation.
            $this->setGetArray(array('id' => $aUser->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(  'listPageSize' => 9,
                        'subListPageSize' => 4,
                        )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit');
            $this->assertEquals('User configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            //Check to make sure user configuration is actually changed.
            $this->assertEquals(9, Yii::app()->pagination->getByUserAndType($aUser, 'listPageSize'));
            $this->assertEquals(4, Yii::app()->pagination->getByUserAndType($aUser, 'subListPageSize'));
            //Confirm current user has certain session values
            $this->assertNotEquals(7, Yii::app()->user->getState('listPageSize'));
            $this->assertNotEquals(4, Yii::app()->user->getState('subListPageSize'));

            //Change current user configuration values. (Yii::app()->user->userModel)
            //First make sure settings are not what we are setting them too.
            $this->assertNotEquals(7, Yii::app()->pagination->getForCurrentUserByType('listPageSize'));
            //Load up configuration page.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will fail validation.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array( 'listPageSize' => 0,
                        'subListPageSize' => 4,
                        )));

            $this->runControllerWithNoExceptionsAndGetContent('users/default/configurationEdit');
            //Post fake save that will pass validation.
            $this->setGetArray(array('id' => Yii::app()->user->userModel->id));
            $this->setPostArray(array('UserConfigurationForm' =>
                array(  'listPageSize' => 7,
                        'subListPageSize' => 4,
                        )));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit');
            $this->assertEquals('User configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            //Check to make sure user configuration is actually changed.
            $this->assertEquals(7, Yii::app()->pagination->getForCurrentUserByType('listPageSize'));
            //Check getState data. since it should be updated for current user.
            $this->assertEquals(7, Yii::app()->user->getState('listPageSize'));
            $this->assertEquals(4, Yii::app()->user->getState('subListPageSize'));
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserUserStatusActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $user       = UserTestHelper::createBasicUser('statusCheck');
            $userId     = $user->id;
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            //Change the user's status to inactive and confirm the changes in rights.
            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' => array('userStatus'  => UserStatusUtil::INACTIVE)));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');

            $userId     = $user->id;
            $user       = User::getById($userId);
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            //Now change the user's status back to active.
            $this->setGetArray(array('id' => $user->id));
            $this->setPostArray(array('User' => array('userStatus'  => UserStatusUtil::ACTIVE)));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');

            $userId     = $user->id;
            $user       = User::getById($userId);
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }

        /**
         * @depends testSuperUserUserStatusActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            //Nothing currently to test.
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDeleteAction()
        {
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setPostArray(array('UserPasswordForm' =>
                                array('firstName'          => 'Some',
                                      'lastName'           => 'Body',
                                      'username'           => 'somenewuser',
                                      'newPassword'        => 'myPassword123',
                                      'newPassword_repeat' => 'myPassword123',
                                      'officePhone'        => '456765421',
                                      'userStatus'         => 'Active')));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/create');

            $user = User::getByUsername('somenewuser');
            $this->assertEquals('Some', $user->firstName);
            $this->assertEquals('Body', $user->lastName);
        }
    }
?>