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

    class StuckJobsNotificationRulesTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testGetUsers()
        {
            $billy = User::getByUsername('billy');
            $sally = User::getByUsername('sally');
            $rules = new StuckJobsNotificationRules();
            $this->assertEquals(1, count($rules->getUsers())); //super user

            //Now add billy and sally to allow rights to the JobManager
            $billy->setRight('JobsManagerModule', JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER);
            $this->assertTrue($billy->save());
            $sally->setRight('JobsManagerModule', JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER);
            $this->assertTrue($sally->save());

            $billy = User::getByUsername('billy');
            $this->assertEquals(Right::ALLOW,
                    $billy->getEffectiveRight('JobsManagerModule', JobsManagerModule::RIGHT_ACCESS_JOBSMANAGER));

            //Rules should still show 1 since the users are already loaded (isLoaded = true)
            $this->assertEquals(1, count($rules->getUsers()));

            //Instantiate a new rules object.
            $rules = new StuckJobsNotificationRules();
            $this->assertEquals(3, count($rules->getUsers()));
        }
    }
?>
