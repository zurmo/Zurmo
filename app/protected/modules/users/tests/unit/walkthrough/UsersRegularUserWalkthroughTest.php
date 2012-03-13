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
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class UsersRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $aUser = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB);
            $aUser->save();
            $bUser = UserTestHelper::createBasicUser('bUser');
            $bUser->setRight('UsersModule', UsersModule::RIGHT_ACCESS_USERS);
            $bUser->save();
            $cUser = UserTestHelper::createBasicUser('cUser');
            $dUser = UserTestHelper::createBasicUser('dUser');
        }

        public function testRegularUserAllControllerActions()
        {
            $aUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
            $bUser = User::getByUsername('bUser');

            //Access to admin configuration should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('configuration');

            //Access to users list to modify users should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default');

            $this->setGetArray(array('id' => $bUser->id));
            //Access to view other users Audit Trail should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default/auditEventsModalList');

            //Access to edit other User and Role should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default/edit');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to allowed to view Audit Trail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            //Access to User Role edit link and control not available.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertFalse(strpos($content, 'User_role_SelectLink') !== false);
            $this->assertFalse(strpos($content, 'User_role_name') !== false);

            //Check if the user who has right access for users can access any users audit trail.
            $bUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('bUser');
            $this->setGetArray(array('id' => $bUser->id));
            //Access to audit Trail should not fail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            $this->setGetArray(array('id' => $aUser->id));
            //Access to other user audit Trail should not fail.
            $this->runControllerWithNoExceptionsAndGetContent('users/default/auditEventsModalList');

            //Now test all portlet controller actions
            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead
            //Now test peon with elevated permissions to models.
            //actionModalList
            //Autocomplete for User
        }

        /**
         * @depends testRegularUserAllControllerActions
         */
        public function testBulkWriteSecurityCheck()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser = User::getByUsername('aUser');
            $this->assertEquals(Right::DENY, $aUser->getEffectiveRight('UserModule', UsersModule::RIGHT_ACCESS_USERS));
            $this->assertEquals(Right::DENY, $aUser->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE));
            $aUser->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_WRITE);
            $this->assertTrue($aUser->save());

            //Confirm user cannot access the massEdit view even though he/she has bulk write access.
            Yii::app()->user->userModel = $aUser;
            $this->setGetArray(array('selectedIds' => '1,2,3', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithExitExceptionAndGetContent('users/default/massEdit');
            $this->assertFalse(strpos($content, 'You have tried to access a page you do not have access to') === false);
        }

        /**
         * @depends testBulkWriteSecurityCheck
         */
        public function testRegularUserAfterChangeOfUserName()
        {
            $cUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('cUser');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');

            $this->setGetArray(array('id' => $cUser->id));
            //Access to User to change the username.
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');

            $this->assertTrue(strpos($content, 'User_lastName') !== false);
            $this->assertTrue(strpos($content, 'User_username') !== false);

            $this->setGetArray(array('id' => $cUser->id));
            $this->setPostArray(array(
                'User'  => array('username' => 'zuser', 'firstName' => 'cUser', 'lastName' => 'cUserson'),
                'save' => 'Save'
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/edit');

            $kUser = $this->logoutCurrentUserLoginNewUserAndGetByUsername('kUser');
            $this->setGetArray(array('id' => $kUser->id));
            $this->runControllerWithNoExceptionsAndGetContent('users/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
        }
    }
?>