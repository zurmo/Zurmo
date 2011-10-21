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

    class AccountReadPermissionsOptimizationTest extends AccountReadPermissionsOptimizationBaseTest
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
            assert('self::getAccountMungeRowCount() == 0'); // Not Coding Standard
        }

        public function setup1()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $accounts = Account::getAll();
            assert('count($accounts) == 3'); // Not Coding Standard

            // The following is the test set up, given the
            // users, groups, and roles set up by SecurityTestHelper
            // the the additional setup below.

            // There is an account, owned by Benny, to which
            // Betty has been given explicit access, along
            // with anyone in Support Staff. The support staff
            // are Bernice and Brian. Benny is a Sales Person
            // so Bobby, the Sales Manager has access via roles.
            // Billy the admin guy has no access.

            $accounts[0]->owner = User::getByUsername('benny');
            $accounts[0]->addPermissions(User::getByUsername('betty'),         Permission::READ);
            $accounts[0]->addPermissions(Group::getByName   ('Support Staff'), Permission::READ);
            $saved = $accounts[0]->save();
            assert('count($saved)'); // Not Coding Standard
            ReadPermissionsOptimizationUtil::rebuild();
            $this->assertEquals(5, self::getAccountMungeRowCount());

            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        public function testOwnerOnlyNoMungeRowsGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getAll();
            $this->assertEquals(3, count($accounts));
            $account = new Account();
            $account->name = 'A lone account';
            $this->assertTrue($account->save());
            //This will test that even though there are no munge rows, the account we just created gets returned
            //correctly.
            $accounts = Account::getAll();

            $this->assertEquals(4, count($accounts));
            $this->assertEquals('A lone account', $accounts[3]->name);
            $accounts[3]->delete();
        }

        /**
         * @depends testOwnerOnlyNoMungeRowsGetAll
         */
        public function testGetAllAsUsersWithRead()
        {
            $this->setup1();

            $allAccounts = Account::getAll();
            $this->assertEquals(3, count($allAccounts));
            foreach (array(
                        'benny',   // Owner
                        'betty',   // Explicit
                        'bernice', // Via group
                        'bobby',   // Via role (Benny's manager)
                     ) as $username)
            {
                Yii::app()->user->userModel = User::getByUsername($username);
                $accounts = Account::getAll();
                $this->assertEquals(1, count($accounts));
                $this->assertTrue($accounts[0]->isSame($allAccounts[0]));
                $this->assertEquals(1, Account::getCount());
            }
            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        /**
         * @depends testGetAllAsUsersWithRead
         */
        public function testGetAllUserWithNoMungeAccessButIsOwnerOnSome()
        {
            Yii::app()->user->userModel = User::getByUsername('billy');

            $accounts = Account::getAll();
            $this->assertEquals(2, count($accounts));
            $this->assertEquals(2, Account::getCount());

            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        public function setup2()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            // The following sets up more, continuing from setup1.

            $accounts = Account::getAll();
            assert('count($accounts) == 3'); // Not Coding Standard

            $user = User::getByUsername('betty');

            for ($i = 1; $i < 3; $i++)
            {
                $accounts[$i]->addPermissions($user, Permission::READ);
                $saved = $accounts[$i]->save();
                assert('count($saved)'); // Not Coding Standard
            }

            ReadPermissionsOptimizationUtil::rebuild();
            $this->assertEquals(11, self::getAccountMungeRowCount());
        }

        /**
         * @depends testGetAllUserWithNoMungeAccessButIsOwnerOnSome
         */
        public function testGetAllAsUsersWithReadOnMoreThanOne()
        {
            $this->setup2();

            $allAccounts = Account::getAll();
            $this->assertEquals(3, count($allAccounts));

            Yii::app()->user->userModel = User::getByUsername('betty');

            $accounts = Account::getAll();
            $this->assertEquals(3, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[0]));
            $this->assertTrue($accounts[1]->isSame($allAccounts[1]));
            $this->assertTrue($accounts[2]->isSame($allAccounts[2]));

            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        /**
         * @depends testGetAllAsUsersWithReadOnMoreThanOne
         */
        public function testGetSubset()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $allAccounts = Account::getAll();

            Yii::app()->user->userModel = User::getByUsername('betty');

            $accounts = Account::getSubset(null, 0, 1);
            $this->assertEquals(1, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[0]));

            $accounts = Account::getSubset(null, 1, 1);
            $this->assertEquals(1, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[1]));

            $accounts = Account::getSubset(null, 2, 1);
            $this->assertEquals(1, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 0, 2);
            $this->assertEquals(2, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[0]));
            $this->assertTrue($accounts[1]->isSame($allAccounts[1]));

            $accounts = Account::getSubset(null, 1, 2);
            $this->assertEquals(2, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[1]));
            $this->assertTrue($accounts[1]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 2, 2);
            $this->assertEquals(1, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 0, 3);
            $this->assertEquals(3, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[0]));
            $this->assertTrue($accounts[1]->isSame($allAccounts[1]));
            $this->assertTrue($accounts[2]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 1, 3);
            $this->assertEquals(2, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[1]));
            $this->assertTrue($accounts[1]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 2, 3);
            $this->assertEquals(1, count($accounts));
            $this->assertTrue($accounts[0]->isSame($allAccounts[2]));

            $accounts = Account::getSubset(null, 3, 3);
            $this->assertEquals(0, count($accounts));

            $this->assertEquals(3, Account::getCount());

            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        /**
         * @depends testGetSubset
         */
        public function testOwnedSecurableItemCreated()
        {
            $mungeTableRowsBefore = self::getAccountMungeRowCount();

            $bobby = User::getByUsername('bobby');
            $benny = User::getByUsername('benny');

            // Benny is a sales person so his manager Bobby should have access
            // to things he creates.
            Yii::app()->user->userModel = $bobby;
            $bobbyBeforeAccounts = Account::getAll();

            Yii::app()->user->userModel = $benny;
            $bennyBeforeAccounts = Account::getAll();

            $account = new Account();
            $account->name = 'Doop de doop';
            $this->assertTrue($account->save());

            $this->assertEquals(array(Permission::ALL, Permission::NONE), $account->getActualPermissions   ($benny));
            $this->assertEquals(array(Permission::ALL, Permission::NONE), $account->getActualPermissions   ($bobby));

            $this->assertEquals(Permission::ALL,                          $account->getEffectivePermissions($benny));
            $this->assertEquals(Permission::ALL,                          $account->getEffectivePermissions($bobby));

            //Called in OwnedSecurableItem::afterSave();
            //ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($account);

            $bennyAfterAccounts = Account::getAll();

            Yii::app()->user->userModel = $bobby;
            $bobbyAfterAccounts = Account::getAll();

            $this->assertEquals(count($bennyBeforeAccounts) + 1, count($bennyAfterAccounts));
            $this->assertEquals(count($bobbyBeforeAccounts) + 1, count($bobbyAfterAccounts));

            $this->assertEquals($mungeTableRowsBefore + 1, self::getAccountMungeRowCount());

            $this->rebuildAndTestThatTheMungeDoesntChange();
        }

        public function testMemoryUsageCreatingModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            // Create some accounts to make sure memory is stable.
            for ($i = 0; $i < 20; $i++)
            {
                $account = self::createRandomAccount($i);
                $this->assertTrue($account->save(false));
            }
            $memoryBefore = memory_get_usage(true);
            // Create more while monitoring memory.
            for ($i = 20; $i < 220; $i++)
            {
                $account = self::createRandomAccount($i);
                $this->assertTrue($account->save(false));
                $memoryDuring = memory_get_usage(true);
                $this->assertWithinPercentage($memoryBefore, $memoryDuring, 10);
            }
            $memoryAfter  = memory_get_usage(true);
            $this->assertWithinPercentage($memoryBefore, $memoryAfter, 10);
        }

        public function testMemoryUsageGettingModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $memoryBefore = memory_get_usage(true);
            $count = Account::getCount();
            for ($i = 0; $i < $count; $i += 3)
            {
                $accounts = Account::getSubset(null, $i, 3);
                $memoryDuring = memory_get_usage(true);
                $this->assertWithinPercentage($memoryBefore, $memoryDuring, 10);
                foreach ($accounts as $account)
                {
                    unset($account);
                }
                $memoryDuring = memory_get_usage(true);
                $this->assertWithinPercentage($memoryBefore, $memoryDuring, 10);
            }
            $memoryAfter = memory_get_usage(true);
            $this->assertWithinPercentage($memoryBefore, $memoryAfter, 10);
        }

        public function testMemoryUsageNukingModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $memoryBefore = memory_get_usage(true);
            $count = Account::getCount();
            for ($i = 0; $i < $count; $i += 3)
            {
                $accounts = Account::getSubset(null, $i, 3);
                foreach ($accounts as $account)
                {
                    $account->delete();
                    unset($account);
                }
                $memoryDuring = memory_get_usage(true);
                $this->assertWithinPercentage($memoryBefore, $memoryDuring, 10);
            }
            $memoryAfter = memory_get_usage(true);
            $this->assertWithinPercentage($memoryBefore, $memoryAfter, 10);
        }

        public function disabled_testMemcachingAccountsDirectly()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $count = 50;
            $memcache = new Memcache();
            $memcache->connect('localhost', 11211);
            for ($i = 0; $i < $count; $i++)
            {
                $account = self::createRandomAccount($i);
                $this->assertTrue($account->save()); // So that it has everything it should,
                                                     // particularly its createdByUser.
                $this->assertTrue($memcache->set("M:$i", serialize($account)));
            }
            RedBeanModelsCache::forgetAll(true);
            RedBeansCache::forgetAll();
            Yii::app()->user->userModel = User::getByUsername('super');
            $memoryBefore = memory_get_usage(true);
            for ($i = 0; $i < $count; $i++)
            {
                $data = $memcache->get("M:$i");
                $this->assertTrue($data !== false);
                $account = unserialize($data);
                $this->assertTrue  ($account instanceof Account);
                $this->assertEquals("Account#$i",               $account->name);
                $this->assertEquals("http://www.account$i.com", $account->website);
                $this->assertTrue  ($account->owner instanceof User);
                $this->assertEquals('super', $account->owner->username);
                $this->assertTrue  ($account->owner          === Yii::app()->user->userModel);
                $this->assertTrue  ($account->createdByUser  === Yii::app()->user->userModel);
                $this->assertTrue  ($account->modifiedByUser === Yii::app()->user->userModel);
                unset($account);
            }
            $memoryAfter = memory_get_usage(true);
            $this->assertWithinPercentage($memoryBefore, $memoryAfter, 10);
        }

        public function disabled_testComparePhpAndSqlifiedMungeRebuilds()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            if ($this->isDebug())
            {
                echo "\nNuking existing accounts...\n";
            }
            $this->nukeExistingAccounts();
            // This is checked in to do a short range in
            // order to not be too slow.
            // Manipulate the numbers to test different ranges
            // if the php and optimized versions of doing a
            // munge rebuild seem to be giving different results.
            // The parameters are...
            //    - the number of accounts to create.
            //    - will it rebuild the munge after accounts? - leave as true.
            //    - after which account will it start rebuilding the munge.
            //    - step.
            // eg: 150, true, 100, 3 - create accounts, rebuilding the munge
            // after accounts 100, 103, 106, 109, etc, until 150 accounts
            // have been created.
            $this->createAccounts(150, true, 100, 3);
        }

        // This is not an automated test.
        // It must be disabled out for check in.
        // The easiest way is to rename it.
        //public function testAShootLoadOfAccounts() // Uncomment to run.
        public function disabled_testAShootLoadOfAccounts() // Uncomment to check in.
        {
            $freezeAfterFirst20 = false;

            $super = User::getByUsername('super');

            foreach (array(20, 50, 100, 200, 500, 1000, 10000, 100000, 200000) as $shootLoad)
            {
                echo "\nNuking existing accounts...\n";
                Yii::app()->user->userModel = $super;
                $this->nukeExistingAccounts();

                echo "Creating $shootLoad accounts...\n";
                echo " - Giving every 10th to Betty, giving Benny read\n";
                echo "   on overy 8th, and giving Sales Staff read on\n";
                echo "   every 12th.\n";
                list($time, $countThatBennyCanRead, $accountIdsThatBennyCanRead) = $this->createAccounts($shootLoad);
                echo 'Created accounts in ' . round($time, 1) . " seconds.\n";
                echo "Benny can read $countThatBennyCanRead of them.\n";
                echo 'The first few... ';
                for ($i = 0; $i < 10 && $i < count($accountIdsThatBennyCanRead); $i++)
                {
                    echo "{$accountIdsThatBennyCanRead[$i]}|";
                }
                echo "\n";

                $startTime = microtime(true);
                ReadPermissionsOptimizationUtil::rebuild(true);
                $endTime = microtime(true);
                if ($this->isDebug())
                {
                    echo 'Rebuilt the munge in php in ' . round($endTime - $startTime, 1) . ' seconds, ' . self::getAccountMungeRowCount() . " rows.\n";
                }
                $phpRows = R::getAll('select munge_id, securableitem_id, count from account_read order by munge_id, securableitem_id, count');

                // If $securityOptimized is false in debug.php the second one will just do the php again.
                $startTime = microtime(true);
                ReadPermissionsOptimizationUtil::rebuild();
                $endTime = microtime(true);
                if ($this->isDebug())
                {
                    echo 'Rebuilt the munge ' . (SECURITY_OPTIMIZED ? 'optimized' : 'in php') . ' in ' . round($endTime - $startTime, 1) . ' seconds, ' . self::getAccountMungeRowCount() . " rows.\n";
                }
                $otherRows = R::getAll('select munge_id, securableitem_id, count from account_read order by munge_id, securableitem_id, count');

                if (count(array_diff($phpRows, $otherRows)) > 0)
                {
                    echo "PHP & optimized munges don't match.\n";
                    echo "--------\n";
                    foreach ($phpRows as $row)
                    {
                        echo join(', ', array_values($row)) . "\n";
                    }
                    echo "--------\n";
                    foreach ($otherRows as $row)
                    {
                        echo join(', ', array_values($row)) . "\n";
                    }
                    echo "--------\n";
                }
                $this->assertEquals(count($phpRows), count($otherRows));
                $this->assertEquals($phpRows, $otherRows);

                Yii::app()->user->userModel = User::getByUsername('benny');
                $count    = Account::getCount();

                $startTime = microtime(true);
                $accounts = Account::getSubset(null, 0, 20);
                $endTime = microtime(true);
                echo 'As Benny retrieved 1 - ' . count($accounts) . " of $count in " . round($endTime - $startTime, 2) . " seconds.\n";
                unset($accounts);

                $offset = intval($count * 0.75);
                $startTime = microtime(true);
                $accounts = Account::getSubset(null, $offset, 20);
                $endTime = microtime(true);
                echo "As Benny retrieved $offset - " . ($offset + count($accounts)) . " of $count in " . round($endTime - $startTime, 3) . " seconds.\n";
                unset($accounts);

                echo "Done.\n";
                echo "\n-------------------------------\n";

                if ($freezeAfterFirst20 && !RedBeanDatabase::isFrozen())
                {
                    echo "Freezing database...\n";
                    RedBeanDatabase::freeze();
                }
            }
        }
    }
?>
