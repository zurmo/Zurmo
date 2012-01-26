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

    class PermissionsTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');

            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createAccounts();
            SecurityTestHelper::createRoles();

            $everyone = Group::getByName('Everyone');
            $saved = $everyone->save();
            assert('$saved'); // Not Coding Standard
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testStringify()
        {
            $permission = new Permission();
            $permission->permitable = User::getByUsername('bobby');
            $permission->type = Permission::ALLOW;
            $this->assertEquals('Bobby Bobbyson:Allow:-----', strval($permission));
            $permission->permissions = Permission::READ;
            $this->assertEquals('Bobby Bobbyson:Allow:R----', strval($permission));
            $permission->permissions |= Permission::WRITE;
            $this->assertEquals('Bobby Bobbyson:Allow:RW---', strval($permission));
            $permission->permissions ^= Permission::READ;
            $this->assertEquals('Bobby Bobbyson:Allow:-W---', strval($permission));
            $permission->permissions |= Permission::DELETE;
            $this->assertEquals('Bobby Bobbyson:Allow:-WD--', strval($permission));
            $permission->permissions |= Permission::CHANGE_PERMISSIONS;
            $this->assertEquals('Bobby Bobbyson:Allow:-WDP-', strval($permission));
            $permission->permissions |= Permission::CHANGE_OWNER;
            $this->assertEquals('Bobby Bobbyson:Allow:-WDPO', strval($permission));
            $permission->permissions &= ~Permission::DELETE;
            $this->assertEquals('Bobby Bobbyson:Allow:-W-PO', strval($permission));
            $permission->permissions |= Permission::READ;
            $this->assertEquals('Bobby Bobbyson:Allow:RW-PO', strval($permission));
            $permission->permissions &= ~Permission::WRITE;
            $this->assertEquals('Bobby Bobbyson:Allow:R--PO', strval($permission));
            $permission->type = Permission::DENY;
            $this->assertEquals('Bobby Bobbyson:Deny:R--PO',  strval($permission));

            $permission->permitable = Group::getByName('Sales Staff');
            $this->assertEquals('Sales Staff:Deny:R--PO', strval($permission));
        }

        public function testUserCanReadEmptyModelWithoutPermission()
        {
            $user = new User();
            $user->username     = 'atest';
            $user->firstName    = 'AAA';
            $user->lastName     = 'Tester';
            $saved = $user->save();
            $this->assertTrue($saved);
            $this->assertTrue($user->id > 0);
            $item       = NamedSecurableItem::getByName('AccountsModule');
            $this->assertEquals(Permission::NONE, $item->getEffectivePermissions($user));
            Yii::app()->user->userModel = User::getByUsername('atest');
            $account = new Account();
            $this->assertEquals(null, $account->name);
        }

        public function testSavePermission()
        {
            $account = new Account();
            $account->name = 'Yooples';
            $account->addPermissions(User::getByUserName('billy'), Permission::READ);
            $this->assertTrue($account->save());
        }

        public function testChangingPermissionsOnEmptyModelsWhenNamedSecuredItemPermissionsChange()
        {
            $super = User::getByUsername('super');
            $user = new User();
            $user->username     = 'ktest';
            $user->firstName    = 'KAAA';
            $user->lastName     = 'XXA';
            $saved = $user->save();
            $this->assertTrue($saved);
            $this->assertTrue($user->id > 0);
            $item       = NamedSecurableItem::getByName('AccountsModule');
            $this->assertEquals(Permission::NONE, $item->getEffectivePermissions($user));
            $account = new Account();  //loading defaults means the owner will be super. If you do not load defaults
                                       //then the users will have Permission::ALL because its id < 0.
                                       //@see OwnedSecurableItem::getEffectivePermissions
            $this->assertEquals(null, $account->name);
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($user));
            //switch current user to make sure, still the same.
            Yii::app()->user->userModel = $user;
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($user));
            Yii::app()->user->userModel = $super;
            $item->addPermissions($user, Permission::READ);
            $this->assertTrue($item->save());
            //now check that new user has permission to read on accounts in general.
            $permission = $account->getEffectivePermissions($user);
            $this->assertEquals(Permission::READ, $permission);
            $this->assertTrue(Permission::READ == ($permission & Permission::READ));
            Yii::app()->user->userModel = User::getByUsername('ktest');
            $permission = $account->getEffectivePermissions($user);
            $this->assertEquals(Permission::READ, $permission);
            $this->assertTrue(Permission::READ == ($permission & Permission::READ));
            Yii::app()->user->userModel = $super;
            $item->delete();
        }

        public function testOwnersImplicityFullPermissions()
        {
            // Bill is the account owner and has all permissions implicitly.
            $accounts = Account::getAll();
            $account  = $accounts[0];
            $owner    = $account->owner;
            $this->assertEquals(Permission::ALL,                           $account->getEffectivePermissions      ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($owner));
        }

        public function testOwnersImplicitFullPermissionsCannotBeDenied()
        {
            // Bill is the account owner and has all permissions implicitly.
            $accounts = Account::getAll();
            $account  = $accounts[0];
            $owner    = $account->owner;
            $this->assertEquals(Permission::ALL, $account->getEffectivePermissions($owner));

            $account->addPermissions($owner, Permission::READ, Permission::DENY);
            $this->assertEquals(Permission::ALL,                           $account->getEffectivePermissions      ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::READ), $account->getExplicitActualPermissions ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($owner));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::ALL,                           $account->getEffectivePermissions      ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($owner));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($owner));
        }

        public function testUserHasNoPermissionsWhenNoneGranted()
        {
            // Bobby is not the account owner and so has no permissions.
            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($user));
        }

        public function testUserHasPermissionsWhenGrantedExplicitly()
        {
            // Bobby is not the account owner, but is granted certain
            // permissions explicitly.
            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);

            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ,       Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->addPermissions($user, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getInheritedActualPermissions($user));
        }

        public function testUserDoesntHavePermissionsWhenDeniedExplicitly()
        {
            // Bobby is not the account owner, but is granted certain
            // permissions explicitly.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);

            $account->addPermissions($user, Permission::READ_WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->addPermissions($user, Permission::WRITE, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::WRITE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($user));
        }

        public function testUserHasPermissionsWhenGrantedViaEveryone()
        {
            // Bobby is not the account owner, but is a a member of Everyone
            // (implicitly), and Everyone is granted certain permissions
            // explicitly.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                     $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ,  Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::WRITE, Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),  $account->getInheritedActualPermissions($user));
        }

        public function testUserDoesntHavePermissionsWhenDeniedViaEveryone()
        {
            // Bobby is not the account owner, but is a a member of Everyone
            // (implicitly), and Everyone is granted certain permissions
            // explicitly.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $account->addPermissions($user, Permission::READ_WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::WRITE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($user));
        }

        public function testUserHasNoPermissionsWhenGrantedViaAGroupTheyAreNotAMemberOf()
        {
            // Bobby is not the account owner, he is a a member of Sales Staff,
            // but Support Staff is granted certain permissions explicitly,
            // so Bobby, not being a member of Support Staff, has no permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);
            $group    = Group::getByName('Support Staff');
            $this->assertFalse($group->contains($user));

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($user));
        }

        public function testUserHasPermissionsWhenGrantedViaAGroup()
        {
            // Bobby is not the account owner, but is a a member of Support Staff
            // and Support Staff is granted certain permissions explicitly.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Sales Staff');
            $this->assertTrue($group->contains($user));

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($user));
        }

        public function testUserDoesntHavePermissionsWhenDeniedViaAGroup()
        {
            // Bobby is not the account owner, but is a a member of Support Staff
            // and Support Staff is granted certain permissions explicitly.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Sales Staff');
            $this->assertTrue($group->contains($user));

            $account->addPermissions($user, Permission::READ_WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::WRITE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($user));
        }

        /**
         * @depends testUserHasPermissionsWhenGrantedViaAGroup
         */
        public function testUserHasPermissionsWhenGrantedViaNestedGroups()
        {
            // Bobby is not the account owner, but is a a member of Support Staff,
            // whic is a member of Dorks. Dorks is granted certain permissions
            // explicitly, so Bobby has those permissions via Dorks.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Dorks');
            $this->assertTrue($group->contains($user));

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($user));
        }

        public function testUserDoesntHavePermissionsWhenDeniedViaNestedGroups()
        {
            // Bobby is not the account owner, but is a a member of Support Staff,
            // whic is a member of Dorks. Dorks is granted certain permissions
            // explicitly, so Bobby has those permissions via Dorks.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Dorks');
            $this->assertTrue($group->contains($user));

            $account->addPermissions($user, Permission::READ_WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                           $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($user));

            $account->addPermissions($group, Permission::WRITE, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::WRITE), $account->getInheritedActualPermissions($user));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                 $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($user));
        }

        public function testEveryoneHasNoPermissionsWhenNoneGranted()
        {
            // Everyone has not been granted any permissions to the account
            // and so has no permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($group));
        }

        public function testEveryoneHasPermissionsWhenGrantedExplicity()
        {
            // Everyone is granted certain permissions to the account
            // explicitly and so has those permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group));
        }

        public function testEveryoneDoesntHavePermissionsWhenDeniedExplicity()
        {
            // Everyone is granted certain permissions to the account
            // explicitly and so has those permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $account->addPermissions($group, Permission::READ_WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                           $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE),  $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($group));

            $account->addPermissions($group, Permission::WRITE, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                 $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::WRITE), $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE),  $account->getInheritedActualPermissions($group));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                 $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),        $account->getInheritedActualPermissions($group));
        }

        public function testGroupHasNoPermissionsWhenNoneGranted()
        {
            // Sales Staff has not been granted any permissions to the account
            // and so has no permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName('Sales Staff');
            $this->assertEquals(Permission::NONE,                          $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE), $account->getInheritedActualPermissions($group));
        }

        public function testGroupHasPermissionsWhenGrantedExplicitly()
        {
            // Sales Staff is granted certain permissions to the account
            // explicitly and so has those permissions.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName('Sales Staff');

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getInheritedActualPermissions($group));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group));
        }

        public function testGroupHasPermissionsWhenGrantedViaEveryone()
        {
            // Everyone is granted certain permissions to the account
            // explicitly and so the group Sales Staff has those permissions
            // implicitly via Everyone.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group2   = Group::getByName('Sales Staff');

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getInheritedActualPermissions($group2));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getInheritedActualPermissions($group2));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group2));
        }

        public function testGroupHasPermissionsWhenGrantedViaAGroup()
        {
            // Dorks is granted certain permissions to the account
            // explicitly and, since Sales Staff is a member of Dorks,
            // the group Sales Staff has those permissions implicitly
            // via Dorks.
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $group    = Group::getByName('Dorks');
            $group2   = Group::getByName('Sales Staff');

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ,                                $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::READ, Permission::NONE),       $account->getInheritedActualPermissions($group2));

            $account->addPermissions($group, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE,                          $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $account->getInheritedActualPermissions($group2));

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getExplicitActualPermissions ($group2));
            $this->assertEquals(array(Permission::NONE, Permission::NONE),       $account->getInheritedActualPermissions($group2));
        }

        public function testPermissionsOnNamedSecurableItems()
        {
            $accounts = Account::getByName('Supermart');
            $this->assertEquals(1, count($accounts));
            $account  = $accounts[0];
            $owner    = $account->owner;
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($owner->id, $user->id);
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group    = Group::getByName('Sales Staff');

            $this->assertEquals(Permission::ALL,  $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($group));

            // Putting permissions on.
            $securableItem1 = new NamedSecurableItem();
            $securableItem1->name = 'Account';
            $securableItem1->addPermissions($everyone, Permission::READ);
            $securableItem1->addPermissions($user,     Permission::DELETE);
            $securableItem1->addPermissions($group,    Permission::WRITE);
            $this->assertTrue($securableItem1->save());

            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::READ_WRITE_DELETE, $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ,              $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ_WRITE,        $account->getEffectivePermissions($group));

            $securableItem2 = new NamedSecurableItem();
            $securableItem2->name = 'AccountsModule';
            $securableItem2->addPermissions($everyone, Permission::CHANGE_OWNER);
            $securableItem2->addPermissions($group,    Permission::DELETE);
            $this->assertTrue($securableItem2->save());

            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::READ_WRITE_DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ_WRITE_DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($group));

            $account->addPermissions($user,  Permission::CHANGE_OWNER);
            $account->addPermissions($group, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());

            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::WRITE | Permission::DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ  | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::WRITE | Permission::DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($group));

            // Taking permissions off.
            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::READ_WRITE_DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ_WRITE_DELETE | Permission::CHANGE_OWNER,
                                                               $account->getEffectivePermissions($group));

            $securableItem2->removeAllPermissions();
            $this->assertTrue($securableItem2->save());
            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::READ_WRITE_DELETE, $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ,              $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ_WRITE,        $account->getEffectivePermissions($group));

            $securableItem1->removeAllPermissions();
            $this->assertTrue($securableItem1->save());
            $this->assertEquals(Permission::ALL,               $account->getEffectivePermissions($owner));
            $this->assertEquals(Permission::NONE,              $account->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE,              $account->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::NONE,              $account->getEffectivePermissions($group));

            $securableItem2->delete();
            unset($securableItem2);

            $securableItem1->delete();
            unset($securableItem1);
        }

        public function testGettingWithPermissions()
        {
            $accounts = Account::getAll();
            $this->assertTrue(count($accounts) >= 2);
            $account1 = $accounts[0];
            $account2 = $accounts[1];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Sales Staff');
            $this->assertTrue($group->contains($user));

            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($group));

            $account1->addPermissions($user,     Permission::READ);
            $account1->addPermissions($group,    Permission::WRITE);
            $this->assertTrue($account1->save());
            ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($account1, $user);

            $account2->addPermissions($user,     Permission::WRITE);
            $account2->addPermissions($group,    Permission::CHANGE_OWNER);
            $this->assertTrue($account2->save());

            $this->assertEquals(Permission::READ  | Permission::WRITE,        $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::WRITE,                            $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::WRITE | Permission::CHANGE_OWNER, $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::CHANGE_OWNER,                     $account2->getEffectivePermissions($group));

            Yii::app()->user->userModel = $user;
            $models = Account::getAll();
            $this->assertEquals(1, count($models));
            $this->assertTrue($models[0]->isSame($account1));

            unset($account1);
            unset($account2);
            unset($user);
            unset($group);
            RedBeanModel::forgetAll();
            Permission::removeAll();
        }

        // The key to understanding the following tests is noting who
        // is the current user at each stage. They are simulating three
        // people using the application separately to fiddle with the
        // same account.

        public function testSecurityExceptions()
        {
            try
            {
                $superAdmin    = User::getByUsername('super');
                $originalOwner = User::getByUsername('betty');
                $buddy         = User::getByUsername('bernice');
                $pleb          = User::getByUsername('brian');

                Yii::app()->user->userModel = $superAdmin;
                $account = new Account();
                $account->name = 'Dooble & Co';
                $account->owner = $originalOwner;
                $this->assertTrue($account->save());

                // READ - owner can read, pleb can't.

                Yii::app()->user->userModel = $originalOwner;
                $this->assertEquals(Permission::ALL, $account->getEffectivePermissions());
                $this->assertEquals('Dooble & Co', $account->name);

                Yii::app()->user->userModel = $pleb;
                try
                {
                    $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $name = $account->name;
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($pleb));
                    $this->assertEquals(Permission::READ, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE, $e->effectivePermissions);
                }

                // WRITE - owner can write, pleb can't.

                Yii::app()->user->userModel = $originalOwner;
                $this->assertEquals(Permission::ALL, $account->getEffectivePermissions());
                $account->name = 'Booble & Sons';
                $this->assertTrue($account->save());
                $this->assertEquals('Booble & Sons', $account->name);

                Yii::app()->user->userModel = $pleb;
                try
                {
                    $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $account->name = 'Google & Mums';
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($pleb));
                    $this->assertEquals(Permission::WRITE, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE,  $e->effectivePermissions);
                }

                // PERMISSIONS - owner can give permissions to and remove
                // permissions from buddy, pleb can't change permissions.

                Yii::app()->user->userModel = $originalOwner;
                $this->assertEquals(Permission::ALL, $account->getEffectivePermissions());
                $account->addPermissions($buddy, Permission::READ);
                $this->assertTrue($account->save());

                Yii::app()->user->userModel = $buddy;
                $this->assertEquals(Permission::READ, $account->getEffectivePermissions());
                $this->assertEquals('Booble & Sons', $account->name);

                Yii::app()->user->userModel = $pleb;
                try
                {
                    $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $account->addPermissions($pleb, Permission::ALL);
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($pleb));
                    $this->assertEquals(Permission::CHANGE_PERMISSIONS, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE,               $e->effectivePermissions);
                }

                Yii::app()->user->userModel = $originalOwner;
                $account->removePermissions($buddy, Permission::READ, Permission::ALLOW_DENY);
                $this->assertTrue($account->save());

                // CHANGE_OWNER - owner gives the account to his buddy,
                // pleb can't change the owner.

                Yii::app()->user->userModel = $originalOwner;
                $this->assertEquals(Permission::ALL, $account->getEffectivePermissions());
                $account->owner = $buddy;
                $this->assertTrue($account->save());

                Yii::app()->user->userModel = $pleb;
                try
                {
                    $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $account->owner = $pleb;
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($pleb));
                    $this->assertEquals(Permission::CHANGE_OWNER, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE,         $e->effectivePermissions);
                }

                // DELETE - pleb can't delete, the original
                // owner can't either, the new owner deletes it.

                Yii::app()->user->userModel = $pleb;
                try
                {
                    $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $account->delete();
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($pleb));
                    $this->assertEquals(Permission::DELETE, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE,   $e->effectivePermissions);
                }

                Yii::app()->user->userModel = $originalOwner;
                try
                {
                   $this->assertEquals(Permission::NONE, $account->getEffectivePermissions());
                    $account->delete();
                    $this->fail();
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $this->assertTrue($e->user->isSame($originalOwner));
                    $this->assertEquals(Permission::DELETE, $e->requiredPermissions);
                    $this->assertEquals(Permission::NONE,   $e->effectivePermissions);
                }

                Yii::app()->user->userModel = $buddy;
                $account->delete();
                unset($account);

                unset($originalOwner);
                unset($buddy);
                unset($pleb);
                RedBeanModel::forgetAll();
                Permission::removeAll();
            }
            catch (AccessDeniedSecurityException $e)
            {
                echo 'Access denied security exception details - ';
                echo "current user: {$e->user}, ";
                echo 'required:'  . Permission::permissionsToString($e->requiredPermissions)  . ', ';
                echo 'effective:' . Permission::permissionsToString($e->effectivePermissions) . "\n";
                throw $e;
            }
        }

        public function testRemoveAllPermissions()
        {
            $accounts = Account::getAll();
            $this->assertTrue(count($accounts) >= 2);
            $account1 = $accounts[0];
            $account2 = $accounts[1];
            $user     = User::getByUsername('bobby');
            $group    = Group::getByName('Sales Staff');
            $this->assertTrue($group->contains($user));
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($group));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($everyone));

            $account1->addPermissions($user,     Permission::READ);
            $account1->addPermissions($group,    Permission::WRITE);
            $account1->addPermissions($everyone, Permission::DELETE);
            $this->assertTrue($account1->save());

            $account2->addPermissions($user,     Permission::WRITE);
            $account2->addPermissions($group,    Permission::CHANGE_OWNER);
            $account2->addPermissions($everyone, Permission::READ);
            $this->assertTrue($account2->save());

            $this->assertEquals(Permission::READ  | Permission::WRITE | Permission::DELETE,
                                                    $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::WRITE | Permission::DELETE, $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::DELETE, $account1->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ  | Permission::WRITE | Permission::CHANGE_OWNER,
                                                    $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ | Permission::CHANGE_OWNER,
                                                    $account2->getEffectivePermissions($group));
            $this->assertEquals(Permission::READ, $account2->getEffectivePermissions($everyone));

            $account1Id = $account1->id;
            $account2Id = $account2->id;
            $userId     = $user->id;
            $groupId    = $group->id;

            Permission::removeForPermitable($group);

            unset($account1);
            unset($account2);
            unset($user);
            unset($group);
            unset($everyone);
            RedBeanModel::forgetAll();

            $account1 = Account::getById($account1Id);
            $account2 = Account::getById($account2Id);
            $user     = User::getById($userId);
            $group    = Group::getById($groupId);
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $this->assertEquals(Permission::READ  | Permission::DELETE,
                                                    $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::DELETE, $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::DELETE, $account1->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::READ  | Permission::WRITE,
                                                    $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::READ,   $account2->getEffectivePermissions($group));
            $this->assertEquals(Permission::READ,   $account2->getEffectivePermissions($everyone));

            unset($account1);
            unset($account2);
            unset($user);
            unset($group);
            unset($everyone);
            RedBeanModel::forgetAll();

            Permission::removeAll();

            $account1 = Account::getById($account1Id);
            $account2 = Account::getById($account2Id);
            $user     = User::getById($userId);
            $group    = Group::getById($groupId);
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($group));
            $this->assertEquals(Permission::NONE, $account1->getEffectivePermissions($everyone));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($user));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($group));
            $this->assertEquals(Permission::NONE, $account2->getEffectivePermissions($everyone));
        }

        public function testPermissionsPropagationViaRoles()
        {
            $parentRole     = Role::getByName('Sales Manager');
            $childRole      = Role::getByName('Sales Person');
            $childChildRole = Role::getByName('Junior Sales Person');

            $userInParentRole     = $parentRole    ->users[0];
            $userInChildRole      = $childRole     ->users[0];
            $userInChildChildRole = $childChildRole->users[0];

            $accounts = Account::getAll();
            $account  = $accounts[0];

            $this->assertEquals(Permission::ALL,  $account->getEffectivePermissions($account->owner));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInParentRole, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->removePermissions($userInParentRole, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInChildRole, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInParentRole, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->removePermissions($userInParentRole, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->removePermissions($userInChildRole, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->removePermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInChildChildRole, Permission::WRITE);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ_WRITE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::WRITE,      $account->getEffectivePermissions($userInChildChildRole));

            $account->addPermissions($userInChildChildRole, Permission::READ, Permission::DENY);
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::READ_WRITE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::READ_WRITE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::WRITE,      $account->getEffectivePermissions($userInChildChildRole));

            Permission::removeAll();
            $accountId = $account->id;
            RedBeanModel::forgetAll();
            unset($account);

            $account = Account::getById($accountId);
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInParentRole));
            $this->assertEquals(Permission::NONE, $account->getEffectivePermissions($userInChildChildRole));
        }

        public function testDeletePermitableDeletesItsPermissions()
        {
            $user = UserTestHelper::createBasicUser('Toolman');

            $accounts = Account::getAll();
            $account  = $accounts[0];
            $account->permissions->removeAll();
            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());

            $this->assertEquals(1, count($account->permissions));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($user));

            $user->delete();

            $accountId = $account->id;
            unset($account);
            RedBeanModel::forgetAll();

            $account = Account::getById($accountId);
            $this->assertEquals(0, count($account->permissions));
        }

        public function testDeleteSecurableItemDeletesItsPermissions()
        {
            $user = User::getByUsername('billy');

            $account = new Account();
            $account->name = 'Waxamatronic';
            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());

            $this->assertEquals(1, count($account->permissions));
            $this->assertEquals(Permission::READ, $account->getEffectivePermissions($user));

            $account->delete();
            unset($account);

            $userId = $user->id;
            unset($user);
            RedBeanModel::forgetAll();

            $this->assertEquals(0, count(Permission::getAll()));
        }

        // The above performance test, for now at least, must be the last test
        // in the suite, because it is not finished and leaves things in a state
        // that may affect things adversely. It will eventually go elsewhere.
    }
?>
