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

    class PolicyTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testStringify()
        {
            $policy = new Policy();
            $policy->moduleName = 'policy';
            $policy->name       = UsersModule::POLICY_PASSWORD_EXPIRY_DAYS;
            $policy->value      = 30;
            $this->assertEquals('Password Expiry Days = 30', strval($policy));

            $policy->name       = 'Some Other Policy';
            $policy->value      = 'Red';
            $this->assertEquals("Some Other Policy = 'Red'", strval($policy));
        }

        public function testSetPolicies()
        {
            $nerd       = User::getByUsername('billy');
            $salesman   = User::getByUsername('bobby');
            $salesStaff = Group::getByName('Sales Staff');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);

            // Save everyone so that the same one will be used by
            // the security classes - because it is cached.
            $this->assertTrue($everyone->save());

            $this->assertEquals(null, $nerd      ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(null, $nerd      ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $nerd      ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $nerd      ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $nerd->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 30);
            $this->assertTrue($nerd->save());
            $this->assertEquals(30,   $nerd      ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(30,   $nerd      ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $nerd      ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(30,   $nerd      ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(null, $salesman  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $salesStaff->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 10);
            $this->assertTrue($salesStaff->save());
            $this->assertEquals(10,   $salesman  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesman  ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(30,   $nerd      ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesman  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesStaff->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(10,   $salesman  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesman  ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $salesman->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 20);
            $this->assertTrue($salesman->save());
            $this->assertEquals(20,   $salesman  ->getActualPolicy         ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(20,   $salesman  ->getExplicitActualPolicy ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesman  ->getInheritedActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(30,   $nerd      ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(20,   $salesman  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesStaff->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getEffectivePolicy      ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
        }

        /**
         * @depends testSetPolicies
         */
        public function testRemovePolicies()
        {
            $nerd       = User::getByUsername('billy');
            $salesman   = User::getByUsername('bobby');
            $salesStaff = Group::getByName('Sales Staff');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);

            $salesStaff->removePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS);
            $this->assertTrue($salesStaff->save());
            $this->assertEquals(30,   $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(20,   $salesman  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $nerd->removePolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS);
            $this->assertTrue($nerd->save());
            $this->assertEquals(null, $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(20,   $salesman  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $everyone->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 69);
            $this->assertTrue($everyone->save());
            $this->assertEquals(69,   $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(20,   $salesman  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(69,   $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(69,   $everyone  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $salesman->removeAllPolicies();
            $this->assertTrue($salesman->save());
            PoliciesCache::forgetAll();
            $this->assertEquals(69,   $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(69,   $salesman  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(69,   $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(69,   $everyone  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $everyone->removeAllPolicies();
            $this->assertTrue($everyone->save());
            PoliciesCache::forgetAll();
            $this->assertEquals(null, $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesman  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $everyone  ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
        }

        /**
         * @depends testRemovePolicies
         */
        public function testDeleteAllPolicies()
        {
            $nerd       = User::getByUsername('billy');
            $salesStaff = Group::getByName('Sales Staff');

            $this->assertEquals(null, $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $this->assertEquals(null, $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $nerd->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 30);
            $this->assertTrue($nerd->save());
            $this->assertEquals(30,   $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            $salesStaff->setPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS, 10);
            $this->assertTrue($salesStaff->save());
            $this->assertEquals(30,   $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            Policy::removeAllForPermitable($nerd);
            PoliciesCache::forgetAll();

            unset($nerd);
            unset($salesStaff);
            RedBeanModel::forgetAll();

            $nerd       = User::getByUsername('billy');
            $salesStaff = Group::getByName('Sales Staff');

            $this->assertEquals(null, $nerd      ->getActualPolicy   ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(10,   $salesStaff->getActualPolicy   ('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));

            Policy::removeAll($nerd);
            PoliciesCache::forgetAll();

            unset($nerd);
            unset($salesStaff);
            RedBeanModel::forgetAll();

            $nerd       = User::getByUsername('billy');
            $salesStaff = Group::getByName('Sales Staff');

            $this->assertEquals(null, $nerd      ->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
            $this->assertEquals(null, $salesStaff->getActualPolicy('UsersModule', UsersModule::POLICY_PASSWORD_EXPIRY_DAYS));
        }

        public function testPolicyComparisons()
        {
            $this->assertEquals(Policy::NO,   UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                                                    array(Policy::NO, Policy::NO)));
            $this->assertEquals(Policy::YES,  UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                                                    array(Policy::YES, Policy::NO)));
            $this->assertEquals(Policy::YES,  UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                                                    array(Policy::NO, Policy::YES)));
            $this->assertEquals(Policy::YES,  UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS,
                                                    array(Policy::YES, Policy::YES)));

            $this->assertEquals(10, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                                                    array(10, 10)));
            $this->assertEquals(20, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                                                    array(10, 20)));
            $this->assertEquals(20, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH,
                                                    array(20, 10)));

            $this->assertEquals(10, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                                                    array(10, 10)));
            $this->assertEquals(20, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                                                    array(10, 20)));
            $this->assertEquals(20, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_MINIMUM_USERNAME_LENGTH,
                                                    array(20, 10)));

            $this->assertEquals(10, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                                                    array(10, 10)));
            $this->assertEquals(10, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                                                    array(10, 20)));
            $this->assertEquals(10, UsersModule::getStrongerPolicy(
                                                    UsersModule::POLICY_PASSWORD_EXPIRY_DAYS,
                                                    array(20, 10)));
        }
    }
?>
