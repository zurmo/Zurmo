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

    class GroupBenchmarkTest extends BaseTest
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
    }
?>
