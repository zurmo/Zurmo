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

    // In general the regular permissions tests are the tests
    // for the optimization. These tests are to hit things
    // that those don't specifically test, or don't yet.
    class PermissionsOptimizationTest extends ZurmoBaseTest
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

            Permission::removeAll();
            PermissionsCache::forgetAll();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testPermissionsCachingBasics()
        {
            if (!SECURITY_OPTIMIZED)
            {
                return;
            }

            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);

            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());

            $securableItemId = $account->getClassId('SecurableItem');
            $permitableId    = $user   ->getClassId('Permitable');

            R::exec("call get_securableitem_cached_actual_permissions_for_permitable($securableItemId, $permitableId, @allow_permissions, @deny_permissions)");
            $allow_permissions = intval(R::getCell('select @allow_permissions'));
            $deny_permissions  = intval(R::getCell('select @deny_permissions'));
            $this->assertEquals(Permission::NONE, $allow_permissions);
            $this->assertEquals(Permission::NONE, $deny_permissions);

            ZurmoDatabaseCompatibilityUtil::callProcedureWithoutOuts("cache_securableitem_actual_permissions_for_permitable($securableItemId, $permitableId, 1, 0)");

            R::exec("call get_securableitem_cached_actual_permissions_for_permitable($securableItemId, $permitableId, @allow_permissions, @deny_permissions)");
            $allow_permissions = intval(R::getCell('select @allow_permissions'));
            $deny_permissions  = intval(R::getCell('select @deny_permissions'));
            $this->assertEquals(Permission::READ, $allow_permissions);
            $this->assertEquals(Permission::NONE, $deny_permissions);

            ZurmoDatabaseCompatibilityUtil::callProcedureWithoutOuts("clear_cache_securableitem_actual_permissions($securableItemId)");

            R::exec("call get_securableitem_cached_actual_permissions_for_permitable($securableItemId, $permitableId, @allow_permissions, @deny_permissions)");
            $allow_permissions = intval(R::getCell('select @allow_permissions'));
            $deny_permissions  = intval(R::getCell('select @deny_permissions'));
            $this->assertEquals(Permission::NONE, $allow_permissions);
            $this->assertEquals(Permission::NONE, $deny_permissions);

            $account->removeAllPermissions();
            $this->assertTrue($account->save());
            $this->assertEquals(Permission::NONE,                                $account->getEffectivePermissions      ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getExplicitActualPermissions ($user));
            $this->assertEquals(array(Permission::NONE,       Permission::NONE), $account->getInheritedActualPermissions($user));
        }

        public function testPermissionsCachingHitsAndMisses()
        {
            if (!SECURITY_OPTIMIZED)
            {
                return;
            }

            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);

            $this->setSomePermissions();

            $startTime = microtime(true);
            $permissions = $account->getEffectivePermissions($user);
            $endTime   = microtime(true);
            $firstTime = $endTime - $startTime;

            $startTime = microtime(true);
            $permissions = $account->getEffectivePermissions($user);
            $endTime   = microtime(true);
            $secondTime = $endTime - $startTime;

            // The false tells it to not forget the
            // db level cached permissions.
            PermissionsCache::forgetAll(false);

            $startTime = microtime(true);
            $permissions = $account->getEffectivePermissions($user);
            $endTime   = microtime(true);
            $thirdTime = $endTime - $startTime;

            // Will forget the db level cached permissions.
            PermissionsCache::forgetAll();

            $startTime = microtime(true);
            $permissions = $account->getEffectivePermissions($user);
            $endTime   = microtime(true);
            $fourthTime = $endTime - $startTime;

            // The first time is at least 10 times faster than
            // the second time because it will get it from the
            // php cached permissions.
            if ($secondTime > 0)
            {
                $this->assertGreaterThan(10, $firstTime / $secondTime);
            }

            // The first time is at least 2 times faster than
            // the third time even though the php level permissions
            // cache is cleared (or it's a different request)
            // because it will get it from the db cached permissions.
            if ($thirdTime > 0)
            {
                $this->assertGreaterThan(2, $firstTime / $thirdTime);
            }

            // The first time is at least 10 times faster than
            // the third time even though the php level permissions
            // cache is cleared (or it's a different request)
            // because it will get it from the db cached permissions.
            $this->assertWithinTolerance($firstTime, $fourthTime, 0.005);
        }

        public function testPermissionsCachingHitsAndMisses2()
        {
            if (!SECURITY_OPTIMIZED)
            {
                return;
            }

            // Like the test above by averaging over many loops.
            $loops = 100;

            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);

            $this->setSomePermissions();

            $firstTime = $secondTime = $thirdTime = $fourthTime = 0;

            for ($i = 0; $i < $loops; $i++)
            {
                $startTime = microtime(true);
                $permissions = $account->getEffectivePermissions($user);
                $endTime   = microtime(true);
                $firstTime += $endTime - $startTime;

                $startTime = microtime(true);
                $permissions = $account->getEffectivePermissions($user);
                $endTime   = microtime(true);
                $secondTime += $endTime - $startTime;

                // The false tells it to not forget the
                // db level cached permissions.
                PermissionsCache::forgetAll(false);

                $startTime = microtime(true);
                $permissions = $account->getEffectivePermissions($user);
                $endTime   = microtime(true);
                $thirdTime += $endTime - $startTime;

                // Will forget the db level cached permissions.
                PermissionsCache::forgetAll();

                $startTime = microtime(true);
                $permissions = $account->getEffectivePermissions($user);
                $endTime   = microtime(true);
                $fourthTime += $endTime - $startTime;

                // Will forget the db level cached permissions
                // to leave it clean for the next loop.
                PermissionsCache::forgetAll();
            }

            $firstTime  /= $loops;
            $secondTime /= $loops;
            $thirdTime  /= $loops;
            $fourthTime /= $loops;

            // The first time is at least 10 times faster than
            // the second time because it will get it from the
            // php cached permissions.
            if ($secondTime > 0)
            {
                $this->assertGreaterThan(10, $firstTime / $secondTime);
            }

            // The first time is at least 2 times faster than
            // the third time even though the php level permissions
            // cache is cleared (or it's a different request)
            // because it will get it from the db cached permissions.
            if ($thirdTime > 0)
            {
                $this->assertGreaterThan(2, $firstTime / $thirdTime);
            }

            // The first time is at least 10 times faster than
            // the third time even though the php level permissions
            // cache is cleared (or it's a different request)
            // because it will get it from the db cached permissions.
            $this->assertWithinTolerance($firstTime, $fourthTime, 0.005);

            Permission::removeAll();
        }

        protected function setSomePermissions()
        {
            if (!SECURITY_OPTIMIZED)
            {
                return;
            }

            $accounts = Account::getAll();
            $account  = $accounts[0];
            $user     = User::getByUsername('bobby');
            $this->assertNotEquals($account->owner->id, $user->id);
            $everyone = Group::getByName('Everyone');

            $account->addPermissions($user, Permission::READ);
            $account->addPermissions($user, Permission::WRITE, Permission::DENY);
            $account->addPermissions($everyone, Permission::CHANGE_OWNER);
            $this->assertTrue($account->save());

            try
            {
                $securableItem1 = NamedSecurableItem::getByName('Account');
            }
            catch (NotFoundException $e)
            {
                $securableItem1 = new NamedSecurableItem();
                $securableItem->name = 'Account';
            }

            $securableItem1->addPermissions($everyone, Permission::DELETE);
            $this->assertTrue($securableItem1->save());

            try
            {
                $securableItem2 = NamedSecurableItem::getByName('Account');
            }
            catch (NotFoundException $e)
            {
                $securableItem2 = new NamedSecurableItem();
                $securableItem->name = 'AccountsModule';
            }

            $securableItem2->addPermissions($everyone, Permission::CHANGE_PERMISSIONS);
            $this->assertTrue($securableItem2->save());
        }
    }
?>
