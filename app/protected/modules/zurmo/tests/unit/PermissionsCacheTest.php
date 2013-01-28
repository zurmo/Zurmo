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

    class PermissionsCacheTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
        }

        public function testCachingCombinedPermissions()
        {
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $account = new Account();
                $account->name = 'Yooples';
                $this->assertTrue($account->save());

                $super = User::getByUsername('super');
                $combinedPermissions = 5;

                PermissionsCache::cacheCombinedPermissions($account, $super, $combinedPermissions);
                $combinedPermissionsFromCache = PermissionsCache::getCombinedPermissions($account, $super);
                $this->assertEquals($combinedPermissions, $combinedPermissionsFromCache);
/*
                PermissionsCache::forgetAll();

                $account = new Account();
                $account->name = 'Yooples2';
                $this->assertTrue($account->save());
                $super = User::getByUsername('super');
                $combinedPermissions = 5;

                PermissionsCache::cacheCombinedPermissions($account, $super, $combinedPermissions);
                $combinedPermissionsFromCache = PermissionsCache::getCombinedPermissions($account, $super);
                $this->assertEquals($combinedPermissions, $combinedPermissionsFromCache);
exit;
*/
            }
        }

        public function testCachingNamedSecurableItemActualPermissions()
        {
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                Yii::app()->user->userModel = User::getByUsername('super');
                $super = User::getByUsername('super');
                $namedSecurableItem       = 'AccountsModule';
                $item       = NamedSecurableItem::getByName('AccountsModule');
                $actualPermissions = $item->getActualPermissions();

                PermissionsCache::cacheNamedSecurableItemActualPermissions($namedSecurableItem, $super, $actualPermissions);
                $actualPermissionsFromCache = PermissionsCache::getNamedSecurableItemActualPermissions($namedSecurableItem, $super);
                $this->assertEquals($actualPermissions, $actualPermissionsFromCache);
            }
        }

        public function testForgetSecurableItem()
        {
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $super = User::getByUsername('super');
                Yii::app()->user->userModel = $super;

                $account = new Account();
                $account->name = 'Ocean Inc.';
                $this->assertTrue($account->save());

                $combinedPermissions = 5;

                PermissionsCache::cacheCombinedPermissions($account, $super, $combinedPermissions);
                $combinedPermissionsFromCache = PermissionsCache::getCombinedPermissions($account, $super);
                $this->assertEquals($combinedPermissions, $combinedPermissionsFromCache);

                PermissionsCache::forgetSecurableItem($account);
                try
                {
                    PermissionsCache::getCombinedPermissions($account, $super);
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }
            }
        }

        public function testForgetAll()
        {
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $super = User::getByUsername('super');
                Yii::app()->user->userModel = $super;

                $account = new Account();
                $account->name = 'Ocean Inc2.';
                $this->assertTrue($account->save());
                $combinedPermissions = 5;

                // Set some GeneralCache, which should stay in cache after cleanup
                GeneralCache::cacheEntry('somethingForTesting', 34);
                $value = GeneralCache::getEntry('somethingForTesting');
                $this->assertEquals(34, $value);

                PermissionsCache::cacheCombinedPermissions($account, $super, $combinedPermissions);
                $combinedPermissionsFromCache = PermissionsCache::getCombinedPermissions($account, $super);
                $this->assertEquals($combinedPermissions, $combinedPermissionsFromCache);

                PermissionsCache::forgetAll();
                try
                {
                    PermissionsCache::getCombinedPermissions($account, $super);
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    // Data from generalCache should still be in cache
                    $value = GeneralCache::getEntry('somethingForTesting');
                    $this->assertEquals(34, $value);
                }
            }
            // To-Do: Add test for forgetAll with $forgetDbLevelCache = true. It could be added to testForgetAll() function.
            // To-Do: Add test for forgetSecurableItem with $forgetDbLevelCache = true. . It could be added to testForgetSecurableItem() function.
        }
    }
?>
