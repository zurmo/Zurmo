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
            $bUser = UserTestHelper::createBasicUser('bUser');
            $cUser = UserTestHelper::createBasicUser('cUser');
            $dUser = UserTestHelper::createBasicUser('dUser');
        }

        public function testRegularUserAllControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $this->runControllerWithNoExceptionsAndGetContent('users/default/profile');
            $aUser = User::getByUsername('aUser');

            //Access to admin configuration should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('configuration');

            //Access to users list to modify users should fail.
            $this->runControllerShouldResultInAccessFailureAndGetContent('users/default');

            $this->setGetArray(array('id' => $aUser->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('users/default/edit');
            $this->assertFalse(strpos($content, 'User_role_SelectLink') !== false);
            $this->assertFalse(strpos($content, 'User_role_name') !== false);

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
    }
?>