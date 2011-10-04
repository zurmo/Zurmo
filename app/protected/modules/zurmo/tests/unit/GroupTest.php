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

    class GroupTest extends BaseTest
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

        public function testSaveAndLoadGroup()
        {
            $u = array();
            for ($i = 0; $i < 5; $i++)
            {
                $user = new User();
                $user->setScenario('createUser');
                $user->username     = "uuuuu$i";
                $user->title->value = 'Mr.';
                $user->firstName    = "Uuuuuu{$i}";
                $user->lastName     = "Uuuuuu{$i}son";
                $user->setPassword("uuuuu$i");
                $this->assertTrue($user->save());
                $u[] = $user;
            }

            $a = new Group();
            $a->name = 'AAA';
            $this->assertTrue($a->save());
            $this->assertEquals(0, $a->users ->count());
            $this->assertEquals(0, $a->groups->count());

            $b = new Group();
            $b->name = 'BBB';
            $this->assertTrue($b->save());
            $this->assertEquals(0, $b->users ->count());
            $this->assertEquals(0, $b->groups->count());

            $a->users ->add($u[0]);
            $a->groups->add($b);
            $this->assertTrue($a->save());
            $this->assertEquals(1, $a->users ->count());

            $b->forget();
            unset($b);
            $a->forget();
            unset($a);
        }

        /**
         * @depends testSaveAndLoadGroup
         */
        public function testReadGroupsOnUser()
        {
            $a = Group::getByName('AAA');
            $this->assertEquals('AAA', $a->name);
            $this->assertEquals(1,     $a->users->count());
            $this->assertEquals(1,     $a->groups->count());
            $b = Group::getByName('BBB');
            $this->assertEquals('BBB', $b->name);
            $this->assertEquals(0,     $b->users->count());
            $this->assertEquals(0,     $b->groups->count());

            $user = User::getByUsername('uuuuu0');
            $this->assertTrue($user->id > 0);
            $this->assertEquals(1, $user->groups->count());
            $b->users->add($user);
            $this->assertTrue($b->save());
            $user->forget();

            $user = User::getByUsername('uuuuu0');
            $this->assertEquals(2, $user->groups->count());
            for ($i = 0; $i < $user->groups->count(); $i++)
            {
                $this->assertNotNull($user->groups[$i]->name);
            }
            $this->assertEquals(2, $user->groups->count());
            $b->users->removeByIndex(0);
            $this->assertTrue($b->save());
            $a->forget();
            unset($a);
            $b->forget();
            unset($b);
        }

        /**
         * @depends testSaveAndLoadGroup
         */
        public function testGroupsWithParentGroup()
        {
            $a = Group::getByName('AAA');
            $aId = $a->id;
            $group = new Group();
            $group->name = 'Child';
            $group->group = $a;
            $saved = $group->save();
            $this->assertTrue($saved);
            $group->forget();
            unset($group);

            $group = Group::getByName('Child');
            $this->assertEquals('Child', $group->name);
            $this->assertEquals($aId, $group->group->id);
            unset($group);

            unset($a);
            RedBeanModel::forgetAll();

            $a     = Group::getByName('AAA');
            $group = Group::getByName('Child');
            $a->groups->remove($group);
            $this->assertTrue($a->save());
        }

        /**
         * @depends testSaveAndLoadGroup
         */
        public function testGroupsContainingGroupsAndContains()
        {
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();

            $u = array();
            for ($i = 0; $i < 5; $i++)
            {
                $u[$i] = User::getByUsername("uuuuu$i");
            }

            $a = Group::getByName('AAA');
            $b = Group::getByName('BBB');

            $c = new Group();
            $c->name = 'CCC';
            $this->assertTrue($c->save());
            $this->assertEquals(0, $c->users ->count());
            $this->assertEquals(0, $c->groups->count());

            $d = new Group();
            $d->name = 'DDD';
            $this->assertTrue($d->save());
            $this->assertEquals(0, $d->users ->count());
            $this->assertEquals(0, $d->groups->count());

            $b->users->add($u[1]);
            $b->users->add($u[2]);
            $b->groups->add($c);
            $b->groups->add($d);
            $this->assertTrue($b->save());

            $c->users->add($u[3]);
            $this->assertTrue($c->save());

            $d->users->add($u[4]);
            $this->assertTrue($d->save());

            unset($a);
            unset($b);
            unset($c);
            unset($d);

            RedBeanModel::forgetAll();

            $a = Group::getByName('AAA');
            $b = Group::getByName('BBB');
            $c = Group::getByName('CCC');
            $d = Group::getByName('DDD');

            $this->assertEquals(1, $a->users->count());
            $this->assertEquals(2, $b->users->count());
            $this->assertEquals(1, $c->users->count());
            $this->assertEquals(1, $d->users->count());

            $this->assertEquals(1, $a->groups->count());
            $this->assertEquals(2, $b->groups->count());
            $this->assertEquals(0, $c->groups->count());
            $this->assertEquals(0, $d->groups->count());

            $this->assertTrue($a->contains($u[0]));
            $this->assertTrue($a->contains($u[1]));
            $this->assertTrue($a->contains($u[2]));
            $this->assertTrue($a->contains($u[3]));
            $this->assertTrue($a->contains($u[4]));
            $this->assertTrue($a->contains($b));
            $this->assertTrue($a->contains($c));
            $this->assertTrue($a->contains($d));

            $this->assertTrue(self::fastContainsUserByGroupName('AAA', $u[0]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('AAA', $u[1]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('AAA', $u[2]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('AAA', $u[3]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('AAA', $u[4]->id));

            $this->assertTrue($b->contains($u[1]));
            $this->assertTrue($b->contains($u[2]));
            $this->assertTrue($b->contains($u[3]));
            $this->assertTrue($b->contains($u[4]));
            $this->assertTrue($b->contains($c));
            $this->assertTrue($b->contains($d));

            $this->assertTrue(self::fastContainsUserByGroupName('BBB', $u[1]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('BBB', $u[2]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('BBB', $u[3]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('BBB', $u[4]->id));

            $this->assertTrue($c->contains($u[3]));
            $this->assertTrue($d->contains($u[4]));

            $this->assertTrue(self::fastContainsUserByGroupName('CCC', $u[3]->id));
            $this->assertTrue(self::fastContainsUserByGroupName('DDD', $u[4]->id));

            $this->assertFalse($b->contains($u[0]));

            $this->assertFalse(self::fastContainsUserByGroupName('BBB', $u[0]->id));

            $this->assertFalse($c->contains($u[0]));
            $this->assertFalse($c->contains($u[1]));
            $this->assertFalse($c->contains($u[2]));
            $this->assertFalse($c->contains($u[4]));

            $this->assertFalse(self::fastContainsUserByGroupName('CCC', $u[0]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('CCC', $u[1]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('CCC', $u[2]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('CCC', $u[4]->id));

            $this->assertFalse($d->contains($u[0]));
            $this->assertFalse($d->contains($u[1]));
            $this->assertFalse($d->contains($u[2]));
            $this->assertFalse($d->contains($u[3]));

            $this->assertFalse(self::fastContainsUserByGroupName('DDD', $u[0]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('DDD', $u[1]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('DDD', $u[2]->id));
            $this->assertFalse(self::fastContainsUserByGroupName('DDD', $u[3]->id));

            $this->assertFalse($b->contains($a));
            $this->assertFalse($c->contains($a));
            $this->assertFalse($d->contains($a));
            $this->assertFalse($c->contains($a));
            $this->assertFalse($c->contains($b));
            $this->assertFalse($d->contains($a));
            $this->assertFalse($d->contains($b));

            $a->forget();
            $b->forget();
            $c->forget();
            $d->forget();
            unset($a);
            unset($b);
            unset($c);
            unset($d);
        }

        protected static function fastContainsUserByGroupName($groupName, $userId)
        {
            // Optimizations work on the database,
            // anything not saved will not work.
            assert('$userId > 0'); // Not Coding Standard
            assert('is_string($groupName) && $groupName != ""'); // Not Coding Standard
            assert('is_int($userId) && $userId > 0'); // Not Coding Standard
            return intval(ZurmoDatabaseCompatibilityUtil::
                            callFunction("named_group_contains_user('$groupName', $userId)")) == 1;
        }

        protected static function fastContainsUserByGroupId($groupId, $userId)
        {
            assert('is_int($groupId) && $groupId > 0'); // Not Coding Standard
            assert('is_int($userId)  && $userId  > 0'); // Not Coding Standard
            return R::getCell("select group_contains_user($groupId, $userId);") == 1;
        }

        /**
         * @depends testGroupsContainingGroupsAndContains
         */
        public function testPerformanceOfFastContainsUserByGroupName()
        {
            $runs = 10;

            $u = array();
            for ($i = 0; $i < 5; $i++)
            {
                $u[$i] = User::getByUsername("uuuuu$i");
            }

            $startTime = microtime(true);
            for ($i = 0; $i < $runs; $i++)
            {
                RedBeanModel::forgetAll();
                $a = Group::getByName('AAA');

                $this->assertTrue($a->contains($u[0]));
                $this->assertTrue($a->contains($u[1]));
                $this->assertTrue($a->contains($u[2]));
                $this->assertTrue($a->contains($u[3]));
                $this->assertTrue($a->contains($u[4]));
            }
            $endTime = microtime(true);
            $totalTimeSlow = $endTime - $startTime;

            $userId0 = User::getByUsername("uuuuu0")->id;
            $userId1 = User::getByUsername("uuuuu1")->id;
            $userId2 = User::getByUsername("uuuuu2")->id;
            $userId3 = User::getByUsername("uuuuu3")->id;
            $userId4 = User::getByUsername("uuuuu4")->id;

            $startTime = microtime(true);
            for ($i = 0; $i < $runs; $i++)
            {
                $this->assertTrue(self::fastContainsUserByGroupName('AAA', $userId0));
                $this->assertTrue(self::fastContainsUserByGroupName('AAA', $userId1));
                $this->assertTrue(self::fastContainsUserByGroupName('AAA', $userId2));
                $this->assertTrue(self::fastContainsUserByGroupName('AAA', $userId3));
                $this->assertTrue(self::fastContainsUserByGroupName('AAA', $userId4));
            }
            $endTime = microtime(true);
            $totalTimeOptimized = $endTime - $startTime;

            $ratio = $totalTimeSlow / $totalTimeOptimized;

            $expectedMinimumRatio = 20;
            $this->assertGreaterThan($expectedMinimumRatio, $ratio);
        }

        /**
         * @depends testGroupsContainingGroupsAndContains
         */
        public function testPerformanceOfFastContainsUserByGroupId()
        {
            $runs = 10;

            $u = array();
            for ($i = 0; $i < 5; $i++)
            {
                $u[$i] = User::getByUsername("uuuuu$i");
            }

            $startTime = microtime(true);
            for ($i = 0; $i < $runs; $i++)
            {
                RedBeanModel::forgetAll();
                $a = Group::getByName('AAA');

                $this->assertTrue($a->contains($u[0]));
                $this->assertTrue($a->contains($u[1]));
                $this->assertTrue($a->contains($u[2]));
                $this->assertTrue($a->contains($u[3]));
                $this->assertTrue($a->contains($u[4]));
            }
            $endTime = microtime(true);
            $totalTimeSlow = $endTime - $startTime;

            $groupId = Group::getByName('AAA')->id;
            $userId0 = User::getByUsername("uuuuu0")->id;
            $userId1 = User::getByUsername("uuuuu1")->id;
            $userId2 = User::getByUsername("uuuuu2")->id;
            $userId3 = User::getByUsername("uuuuu3")->id;
            $userId4 = User::getByUsername("uuuuu4")->id;

            $startTime = microtime(true);
            for ($i = 0; $i < $runs; $i++)
            {
                $this->assertTrue(self::fastContainsUserByGroupId($groupId, $userId0));
                $this->assertTrue(self::fastContainsUserByGroupId($groupId, $userId1));
                $this->assertTrue(self::fastContainsUserByGroupId($groupId, $userId2));
                $this->assertTrue(self::fastContainsUserByGroupId($groupId, $userId3));
                $this->assertTrue(self::fastContainsUserByGroupId($groupId, $userId4));
            }
            $endTime = microtime(true);
            $totalTimeOptimized = $endTime - $startTime;

            $ratio = $totalTimeSlow / $totalTimeOptimized;

            $expectedMinimumRatio = 20;
            $this->assertGreaterThan($expectedMinimumRatio, $ratio);
        }

        /**
         * @depends testGroupsContainingGroupsAndContains
         */
        public function testCreatingSavingLoadingRemovingFromSavingAndLoadingGroups()
        {
            $e = new Group();
            $e->name = 'EEE';
            $this->assertTrue($e->save());

            $f = new Group();
            $f->name = 'FFF';
            $this->assertTrue($f->save());

            $b = Group::getByName('BBB');
            $this->assertEquals(2,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);

            $b = Group::getByName('BBB');
            $b->groups->add($e);
            $b->groups->add($f);
            $b->forget();
            unset($b); // Not saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(2,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);

            $b = Group::getByName('BBB');
            $b->groups->add($e);
            $b->groups->add($f);
            $this->assertTrue($b->save());
            unset($b); // Saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(4,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);
            $this->assertEquals('EEE', $b->groups[2]->name);
            $this->assertEquals('FFF', $b->groups[3]->name);

            $b->groups->removeByIndex(2);
            $this->assertEquals(3,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);
            $this->assertEquals('FFF', $b->groups[2]->name);
            $b->forget();
            unset($b); // Not saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(4,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);
            $this->assertEquals('EEE', $b->groups[2]->name);
            $this->assertEquals('FFF', $b->groups[3]->name);

            $b->groups->removeByIndex(2);
            $this->assertTrue($b->save());
            $this->assertEquals(3,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('DDD', $b->groups[1]->name);
            $this->assertEquals('FFF', $b->groups[2]->name);
            unset($b); //Saved.

            $b = Group::getByName('BBB');
            $b->groups->removeByIndex(1);
            $this->assertTrue($b->save()); // Removes DDD.

            $b->groups->add(Group::getByName('DDD')); // Readds it.
            $this->assertTrue($b->save());
            unset($b); //Saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(3,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('FFF', $b->groups[1]->name);
            $this->assertEquals('DDD', $b->groups[2]->name);

            $b = Group::getByName('BBB');
            $b->groups->removeByIndex(2); // Removes DDD.
            $b->groups->add(Group::getByName('DDD')); // Readds it.
            $this->assertTrue($b->save());
            unset($b); //Saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(3,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('FFF', $b->groups[1]->name);
            $this->assertEquals('DDD', $b->groups[2]->name);

            $b = Group::getByName('BBB');
            $d = Group::getByName('DDD');
            $b->groups->removeByIndex(2); // Removes DDD.
            $b->groups->add($d);          // Readds it.
            $b->groups->removeByIndex(2); // Removes it.
            $b->groups->add($d);          // Readds it.
            $b->groups->removeByIndex(2); // Removes it.
            $b->groups->add($d);          // Readds it.
            $b->groups->removeByIndex(2); // Removes DDD and leaves it removed.
            $this->assertTrue($b->save());
            unset($b); //Saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(2,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('FFF', $b->groups[1]->name);

            $b = Group::getByName('BBB');
            $f = Group::getByName('FFF');
            $b->groups->removeByIndex(1); // Removes FFF.
            $b->groups->add($f);          // Readds it.
            $b->groups->removeByIndex(1); // Removes it.
            $b->groups->add($f);          // Readds it.
            $b->groups->removeByIndex(1); // Removes it.
            $b->groups->add($f);          // Readds it.
            $b->groups->removeByIndex(1); // Removes it.
            $b->groups->add($f);          // Readds it and leaves it added.
            $this->assertTrue($b->save());
            unset($b); //Saved.

            $b = Group::getByName('BBB');
            $this->assertEquals(2,   $b->groups->count());
            $this->assertEquals('CCC', $b->groups[0]->name);
            $this->assertEquals('FFF', $b->groups[1]->name);
        }

        public function testEveryoneOnlyCanExistOnce()
        {
            $groupCountBefore = count(Group::getAll());
            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($group1->save());
            $groups = Group::getAll();
            $this->assertEquals($groupCountBefore + 1, count($groups));
            $group2 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals($groupCountBefore + 1, count($groups));
            $this->assertEquals($group1->id, $group2->id);
        }

        public function testSuperAdministratorsOnlyCanExistOnce()
        {
            $groupCountBefore = count(Group::getAll());
            $group1 = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $this->assertTrue($group1->save());
            $this->assertEquals($groupCountBefore, Group::getCount());
            $group2 = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $this->assertEquals($groupCountBefore, Group::getCount());
            $this->assertEquals($group1->id, $group2->id);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testEveryoneOnlyCannotBeDeleted()
        {
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertFalse($group->isDeletable());
            $group->delete();
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testSuperAdministratorsCannotBeDeleted()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $this->assertFalse($group->isDeletable());
            $group->delete();
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotSetAGroupsNameToEveryone()
        {
            $group = new Group();
            $group->name = Group::EVERYONE_GROUP_NAME;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotSetAGroupsNameToSuperAdministrators()
        {
            $group = new Group();
            $group->name = Group::SUPER_ADMINISTRATORS_GROUP_NAME;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotChangeTheEveryoneGroupsName()
        {
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group->name = 'Something';
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotChangeTheSuperAdministratorsGroupsName()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $group->name = 'Something';
        }

        public function testCanGetUsersFromTheEveryoneGroup()
        {
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertFalse($group->canModifyMemberships());
            $group->users;
        }

        public function testCannotAddUsersToTheEveryoneGroup()
        {
            SecurityTestHelper::createUsers();
            $users = User::getAll();
            $user = $users[0];
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertFalse($group->canModifyMemberships());
        }

        public function testCanGetGroupsFromTheEveryoneGroupButItIsEmptyArray()
        {
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertFalse($group->canModifyMemberships());
            $this->assertEquals(array(), $group->groups);
        }

        public function testCannotModifyGroupMembershipForTheTheEveryoneGroup()
        {
            SecurityTestHelper::createGroups();
            $groups = Group::getAll();
            $group = $groups[0];
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertFalse($everyone->canModifyMemberships());
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotSetEveryonesParentGroup()
        {
            $groups = Group::getAll();
            $group = $groups[0];
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyone->group = $group;
        }

        public function testGetEveryonesParentGroupReturnsNull()
        {
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertEquals(null, $everyone->group);
        }

        /**
         * @depends testCannotModifyGroupMembershipForTheTheEveryoneGroup
         */
        public function testEveryoneImplicitlyContainsAllGroups()
        {
            $everyone = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $groups = Group::getAll();
            foreach ($groups as $group)
            {
                $this->assertTrue($everyone->contains($group));
            }
        }

        /**
         * @depends testCannotAddUsersToTheEveryoneGroup
         */
        public function testEveryoneImplicitlyContainsAllUsers()
        {
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $users = User::getAll();
            foreach ($users as $user)
            {
                $this->assertTrue($group->contains($user));
            }
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCannotGetRightsFromTheSuperAdministratorsGroup()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $this->assertFalse($group->canModifyRights());
            $groups = $group->rights;
        }

        /**
         * @depends testCannotModifyGroupMembershipForTheTheEveryoneGroup
         * @expectedException NotSupportedException
         */
        public function testCannotSetRightsOnTheSuperAdministratorsGroup()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $this->assertFalse($group->canModifyRights());
            $group->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
        }

        /**
         * @depends testCannotModifyGroupMembershipForTheTheEveryoneGroup
         * @expectedException NotSupportedException
         */
        public function testCannotRemoveRightsOnTheSuperAdministratorsGroup()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $group->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE);
        }

        /**
         * @depends testCannotModifyGroupMembershipForTheTheEveryoneGroup
         * @expectedException NotSupportedException
         */
        public function testCannotRemoveAllRightsOnTheSuperAdministratorsGroup()
        {
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            $group->removeAllRights();
        }

        /**
         * @depends testCannotModifyGroupMembershipForTheTheEveryoneGroup
         */
        public function testSuperAdministratorsImplicitlyHasAllRights()
        {
            // Well, we're not testing all rights, but a representative sample.

            $rights = array(UsersModule::RIGHT_CHANGE_USER_PASSWORDS,
                            UsersModule::RIGHT_LOGIN_VIA_MOBILE,
                            UsersModule::RIGHT_LOGIN_VIA_WEB,
                            UsersModule::RIGHT_LOGIN_VIA_WEB_API);

            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            foreach ($rights as $right)
            {
                $this->assertEquals(Right::ALLOW, $group->getEffectiveRight('UsersModule', $right));
            }

            // And make sure that no-one else has the rights to ensure
            // the super administrators doesn't have them all just because
            // of a side effect of some bug.
            $group = Group::getByName(Group::EVERYONE_GROUP_NAME);
            foreach ($rights as $right)
            {
                $this->assertEquals(Right::DENY, $group->getEffectiveRight('UsersModule', $right));
            }

            $group = Group::getByName('AAA');
            foreach ($rights as $right)
            {
                $this->assertEquals(Right::DENY, $group->getEffectiveRight('UsersModule', $right));
            }
        }

        public function testSetParentOfGroup_ieBelongsToSideOfRelation()
        {
            $group1 = new Group();
            $group1->name = 'Monotremes';
            $this->assertTrue($group1->save());

            $group2 = new Group();
            $group2->name = 'Platypuses';
            $this->assertTrue($group2->save());

            // Test from the many side.

            $group1->groups->add($group2);
            $this->assertTrue ($group1->save());
            $this->assertTrue ($group1->contains($group2));

            unset($group1);
            unset($group2);

            RedBeanModel::forgetAll();

            $group1 = Group::getByName('Monotremes');
            $this->assertEquals(1, count($group1->groups));

            $group2 = Group::getByName('Platypuses');
            $this->assertTrue ($group1->contains($group2));
            $this->assertTrue ($group2->group->isSame($group1));

            $group1->groups->remove($group2);
            $this->assertTrue ($group2->save());
            $this->assertFalse($group1->contains($group2));

            $group2->group = $group1;
            $this->assertTrue ($group2->save());

            $group1->forget();
            $group2->forget();

            // Test from the belongs to side.

            $group1 = Group::getByName('Monotremes');
            $this->assertEquals(1, count($group1->groups));

            $group2 = Group::getByName('Platypuses');
            $this->assertTrue ($group2->group->isSame($group1));
            $this->assertTrue ($group1->contains($group2));
        }

        public function testEveryOneGroupShouldHaveNoParentAfterSave()
        {
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyone->group === null);
            $everyone->save();
            $this->assertTrue($everyone->group === null);
        }

        public function testAccessingUsersGroupsAfterGroupIsDeleted()
        {
            $user = UserTestHelper::createBasicUser('Dood1');

            $group = new Group();
            $group->name = 'Doods';
            $group->users->add($user);
            $this->assertTrue($group->save());

            $this->assertEquals(1, count($user->groups));
            $this->assertEquals('Doods', $user->groups[0]->name);

            $group->delete();
            unset($group);

            // The user object in memory doesn't
            // know yet that the group was deleted.
            $this->assertEquals(1, count($user->groups));

            // But in using the app it would be a later
            // request that would be getting the user
            // object anew.
            $user->forget();
            unset($user);
            $user = User::getByUsername('dood1');

            // Which shows the group having been deleted.
            $this->assertEquals(0, count($user->groups));
        }
    }
?>
