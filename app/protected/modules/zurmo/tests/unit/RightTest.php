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

    class RightTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createRoles();
            //Forget the cache, otherwise user/role/group information is not properly reflected in the cache.
            RedBeanModel::forgetAll();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testStringify()
        {
            $right = new Right();
            $right->moduleName = 'UsersModule';
            $right->type       = Right::ALLOW;
            $right->name       = UsersModule::RIGHT_CHANGE_USER_PASSWORDS;
            $this->assertEquals('Allow:Change User Passwords', strval($right));
            $right->type       = Right::DENY;
            $this->assertEquals('Deny:Change User Passwords', strval($right));
        }

        public function testInfiniteRecursionDoesntHappen()
        {
            // The problem was caused because MANY to MANY
            // relations infinitely trying to get each others'
            // errors.
            $bill = User::getByUsername('billy');
            $bill->validate(); // Ok.
            $bill->groups;
            $bill->validate(); // Did Boom! Not now though.
        }

        public function testSetRights()
        {
            $nerd       = User::getByUsername('billy');
            $salesman   = User::getByUsername('bobby');
            $salesStaff = Group::getByName('Sales Staff');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);

            // Save everyone so that the same one will be used by
            // the security classes - because it is cached.
            $this->assertTrue($everyone->save());

            $this->assertEquals(Right::DENY,  $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));

            $this->assertEquals(Right::NONE,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::NONE,  $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $salesman->setRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS);
            $this->assertTrue($salesman->save());
            $this->assertEquals(Right::ALLOW, $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::ALLOW, $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::NONE,  $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));

            $this->assertEquals(Right::DENY,  $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::ALLOW, $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));
            $this->assertEquals(Right::DENY,  $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_CHANGE_USER_PASSWORDS));

            $this->assertEquals(Right::NONE,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::NONE,  $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $salesStaff->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $this->assertTrue($salesStaff->save());
            $this->assertEquals(Right::ALLOW,  $salesman ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesStaff->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::NONE,  $everyone  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));

            $this->assertEquals(Right::DENY,  $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,  $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));

            $this->assertEquals(Right::ALLOW, $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $salesman->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE, Right::DENY);
            $this->assertTrue($salesman->save());
            $this->assertEquals(Right::DENY,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));

            $this->assertEquals(Right::DENY,  $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,  $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,  $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));

            $this->assertEquals(Right::NONE,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::NONE,  $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $everyone->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($everyone->save());
            $this->assertEquals(Right::ALLOW, $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $this->assertEquals(Right::ALLOW, $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $this->assertEquals(Right::ALLOW, $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $salesman->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $this->assertTrue($salesman->save());
            $this->assertEquals(Right::DENY,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $this->assertEquals(Right::ALLOW, $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $this->assertEquals(Right::DENY,  $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $salesman->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $this->assertTrue($salesman->save());
            $this->assertEquals(Right::ALLOW, $salesman  ->getActualRight         ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::NONE,  $salesman  ->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getInheritedActualRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $this->assertEquals(Right::ALLOW, $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }

        /**
         * @depends testSetRights
         */
        public function testRemoveRights()
        {
            $nerd       = User::getByUsername('billy');
            $salesman   = User::getByUsername('bobby');
            $salesStaff = Group::getByName('Sales Staff');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $salesStaff->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
            $this->assertTrue($salesStaff->save());
            $this->assertEquals(Right::DENY,   $nerd      ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,   $salesman  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,   $salesStaff->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));
            $this->assertEquals(Right::DENY,   $everyone  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE));

            $this->assertEquals(Right::ALLOW, $nerd      ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesman  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $salesStaff->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $everyone  ->getEffectiveRight      ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $nerd      ->forget();
            $salesman  ->forget();
            $salesStaff->forget();
            $everyone  ->forget();
            unset($nerd);
            unset($salesman);
            unset($salesStaff);
            unset($everyone);

            Right::removeAll();
            //Clear the cache since the method above removeAll calls directly to the database.
            RightsCache::forgetAll();

            $nerd       = User::getByUsername('billy');
            $salesman   = User::getByUsername('bobby');
            $salesStaff = Group::getByName('Sales Staff');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $this->assertEquals(Right::DENY,  $nerd      ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $salesman  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $salesStaff->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $everyone  ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }

        public function testRightsPropagationViaRoles()
        {
            $parentRole      = Role::getByName('Sales Manager');
            $childRole       = Role::getByName('Sales Person');
            $childChildRole  = Role::getByName('Junior Sales Person');

            $userInParentRole     = $parentRole    ->users[0];
            $userInChildRole      = $childRole     ->users[0];
            $userInChildChildRole = $childChildRole->users[0];

            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInChildRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInChildRole->save());
            $this->assertEquals(Right::ALLOW, $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInParentRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $this->assertTrue($userInParentRole->save());
            $this->assertEquals(Right::ALLOW, $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInParentRole->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInParentRole->save());
            $this->assertEquals(Right::ALLOW, $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInChildRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $this->assertTrue($userInChildRole->save());
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInParentRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInParentRole->save());
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInParentRole->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInParentRole->save());
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInChildRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInChildRole->save());
            $this->assertEquals(Right::ALLOW, $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInChildRole->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInChildRole->save());
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            $userInChildChildRole->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($userInChildChildRole->save());
            $this->assertEquals(Right::ALLOW, $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::ALLOW, $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));

            Right::removeAll();
            //Clear the cache since the method above removeAll calls directly to the database.
            RightsCache::forgetAll();

            $userInParentRoleId     = $userInParentRole    ->id;
            $userInChildRoleId      = $userInChildRole     ->id;
            $userInChildChildRoleId = $userInChildChildRole->id;
            RedBeanModel::forgetAll();
            unset($userInParentRole);
            unset($userInChildRole);
            unset($userInChildChildRole);

            $userInParentRole     = User::getById($userInParentRoleId);
            $userInChildRole      = User::getById($userInChildRoleId);
            $userInChildChildRole = User::getById($userInChildChildRoleId);

            $this->assertEquals(Right::DENY,  $userInParentRole    ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildRole     ->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
            $this->assertEquals(Right::DENY,  $userInChildChildRole->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }

        public function testRightsInVariousModules()
        {
            $nerd = User::getByUsername('billy');
            $nerd->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $nerd->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS, Right::ALLOW);
            $nerd->setRight('LeadsModule',    LeadsModule::RIGHT_ACCESS_LEADS,       Right::DENY);
            $nerd->setRight('UsersModule',    UsersModule::RIGHT_LOGIN_VIA_WEB,      Right::ALLOW);
            $nerd->setRight('UsersModule',    UsersModule::RIGHT_LOGIN_VIA_WEB_API,  Right::ALLOW);
            $this->assertTrue($nerd->save());

            $this->assertEquals(Right::ALLOW, $nerd->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));
            $this->assertEquals(Right::ALLOW, $nerd->getEffectiveRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS));
            $this->assertEquals(Right::DENY,  $nerd->getEffectiveRight('LeadsModule',    LeadsModule::RIGHT_ACCESS_LEADS));
            $this->assertEquals(Right::ALLOW, $nerd->getEffectiveRight('UsersModule',    UsersModule::RIGHT_LOGIN_VIA_WEB));
            $this->assertEquals(Right::ALLOW, $nerd->getEffectiveRight('UsersModule',    UsersModule::RIGHT_LOGIN_VIA_WEB_API));
        }
    }
?>
