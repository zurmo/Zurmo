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

    class RightsUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createRoles();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testDynamicStaticConstantRetrieval()
        {
           $value = constant('UsersModule::RIGHT_LOGIN_VIA_WEB_API');
           $this->assertEquals(UsersModule::RIGHT_LOGIN_VIA_WEB_API, $value);
           $module = 'UsersModule';
           $right  = 'RIGHT_LOGIN_VIA_WEB_API';
           $value = constant($module . '::' . $right);
           $this->assertEquals(UsersModule::RIGHT_LOGIN_VIA_WEB_API, $value);
        }

        public function testCanUserAccessModule()
        {
            $user = User::getByUsername('billy');
            $this->assertTrue(RightsUtil::canUserAccessModule('HomeModule', $user));
            $this->assertFalse(RightsUtil::canUserAccessModule('AccountsModule', $user));
            $user->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $saved = $user->save();
            $this->assertTrue($saved);
            $this->assertTrue(RightsUtil::canUserAccessModule('AccountsModule', $user));
        }

        /**
         * todo:
         * test the following:
         * set child allow
         * set parent deny
         * come in read only.
         * save
         * doesnt change anything with this. is this ok? i guess so
         */
    }
?>
