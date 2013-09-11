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

    class RightsFormUtilTest extends ZurmoBaseTest
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

        public function testModuleRightsUtilGetAllModuleRightsData()
        {
            $group = new Group();
            $group->name = 'viewGroup';
            $group->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $saved = $group->save();
            $this->assertTrue($saved);
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $compareData = array(
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => 'Sign in Via Web',
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => 'Sign in Via Mobile',
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => 'Sign in Via Web API',
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData['UsersModule'], $data['UsersModule']);
            $group->forget();
        }

        /**
         * @depends testModuleRightsUtilGetAllModuleRightsData
         */
        public function testRightsFormUtil()
        {
            $group = Group::getByName('viewGroup');
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group1->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $group1->save();
            $this->assertTrue($saved);
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $this->assertTrue(is_array($data));
            $form = RightsFormUtil::makeFormFromRightsData($data);
            $compareData = array(
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => 'Sign in Via Web',
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => 'Sign in Via Mobile',
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => 'Sign in Via Web API',
                        'explicit'    => null,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData['UsersModule'], $form->data['UsersModule']);
            $group->forget();
            $group1->forget();
        }

        /**
         * @depends testRightsFormUtil
         */
        public function testRightsFormUtilSetRightsFromPost()
        {
            $group = Group::getByName('viewGroup');
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $form = RightsFormUtil::makeFormFromRightsData($data);
            $compareData = array(
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => 'Sign in Via Web',
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => 'Sign in Via Mobile',
                        'explicit'    => Right::ALLOW,
                        'inherited'   => null,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => 'Sign in Via Web API',
                        'explicit'    => null,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $this->assertEquals($compareData['UsersModule'], $form->data['UsersModule']);
            $fakePost = array(
                'UsersModule__RIGHT_LOGIN_VIA_WEB_API' => strval(Right::ALLOW),
                'UsersModule__RIGHT_LOGIN_VIA_MOBILE'  => '',
                'UsersModule__RIGHT_LOGIN_VIA_WEB'     => strval(Right::DENY),

            );
            $fakePost = RightsFormUtil::typeCastPostData($fakePost);
            $saved = RightsFormUtil::setRightsFromCastedPost($fakePost, $group);
            $this->assertTrue($saved);
            $group->forget();
            $group = Group::getByName('viewGroup');
            $compareData = array(
                'UsersModule' => array(
                    'RIGHT_CHANGE_USER_PASSWORDS'   => array(
                        'displayName' => UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB'   => array(
                        'displayName' => 'Sign in Via Web',
                        'explicit'    => Right::DENY,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_MOBILE'   => array(
                        'displayName' => 'Sign in Via Mobile',
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_LOGIN_VIA_WEB_API'   => array(
                        'displayName' => 'Sign in Via Web API',
                        'explicit'    => Right::ALLOW,
                        'inherited'   => Right::ALLOW,
                        'effective'   => Right::ALLOW,
                    ),
                    'RIGHT_CREATE_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_CREATE_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                    'RIGHT_ACCESS_USERS'   => array(
                        'displayName' => UsersModule::RIGHT_ACCESS_USERS,
                        'explicit'    => null,
                        'inherited'   => null,
                        'effective'   => Right::DENY,
                    ),
                ),
            );
            $data = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $this->assertEquals($compareData['UsersModule'], $data['UsersModule']);
            $group->forget();
        }

        public function testGetDerivedAttributeNameFromTwoStrings()
        {
            $attributeName = FormModelUtil::getDerivedAttributeNameFromTwoStrings('x', 'y');
            $this->assertEquals('x__y', $attributeName);
        }

        /**
         * @depends testRightsFormUtilSetRightsFromPost
         */
        public function testGiveUserAccessToModule()
        {
            $user = User::getByUsername('billy');
            $this->assertFalse(RightsUtil::canUserAccessModule('AccountsModule', $user));
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $fakePost = array(
                'AccountsModule__RIGHT_ACCESS_ACCOUNTS' => strval(Right::ALLOW),
            );
            $fakePost = RightsFormUtil::typeCastPostData($fakePost);
            $saved = RightsFormUtil::setRightsFromCastedPost($fakePost, $group);
            $this->assertTrue($saved);
            $this->assertTrue(RightsUtil::canUserAccessModule('AccountsModule', $user));
        }
    }
?>
