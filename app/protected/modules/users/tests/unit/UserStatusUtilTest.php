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
     * Test class for testing UserStatus and UserStatusUtil classes
     */
    class UserStatusUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testMakeByUser()
        {
            $user       = UserTestHelper::createBasicUser('statusCheck');
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $userStatus = UserStatusUtil::makeByUser($user);
            $this->assertTrue($userStatus->isActive());
        }

        public function testMakeByPostData()
        {
            $userStatus = UserStatusUtil::makeByPostData(array('userStatus' => UserStatusUtil::ACTIVE));
            $this->assertTrue ($userStatus->isActive());
            $userStatus = UserStatusUtil::makeByPostData(array('userStatus' => UserStatusUtil::INACTIVE));
            $this->assertFalse($userStatus->isActive());
            $userStatus = UserStatusUtil::makeByPostData(array());
            $this->assertNull($userStatus);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testMakeByPostDataInvalidValue()
        {
            UserStatusUtil::makeByPostData(array('userStatus' => 'invalid'));
        }

        public function testRemoveIfExistsFromPostData()
        {
            $data          = array('abc' => 'def');
            $sanitizedData = UserStatusUtil::removeIfExistsFromPostData($data);
            $this->assertEquals($sanitizedData, $data);

            $sanitizedData = UserStatusUtil::removeIfExistsFromPostData(array('abc' => 'def', 'userStatus' => 'abc'));
            $this->assertEquals($sanitizedData, $data);
        }

        public function testResolveUserStatus()
        {
            $user       = UserTestHelper::createBasicUser('statusCheck2');
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            //Set the user to inactive.
            $userStatus = new UserStatus();
            $userStatus->setInactive();
            UserStatusUtil::resolveUserStatus($user, $userStatus);
            $userId     = $user->id;
            $user       = User::getById($userId);
            $this->assertEquals(UserStatusUtil::INACTIVE, UserStatusUtil::getSelectedValueByUser($user));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            //Now set the user back to active.
            $userStatus->setActive();
            UserStatusUtil::resolveUserStatus($user, $userStatus);
            $userId = $user->id;
            $user   = User::getById($userId);
            $this->assertEquals(UserStatusUtil::ACTIVE, UserStatusUtil::getSelectedValueByUser($user));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertTrue(Right::NONE == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }
    }
?>
