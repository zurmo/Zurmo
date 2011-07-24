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

    /**
     * This test is in the application instead of the framework so it can be tested when the database is frozen or
     * unfrozen.
     */
    class DemoDataUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            //Load default data first. This is required for the demo data to load correctly.
            $messageLogger   = new MessageLogger();
            DefaultDataUtil::load($messageLogger);
        }

        public function testLoad()
        {
            $this->assertEquals(1, Group::getCount());
            $this->assertEquals(0, Role::getCount());
            $this->assertEquals(0, Account::getCount());
            $this->assertEquals(0, Contact::getCount());
            $this->assertEquals(0, Opportunity::getCount());
            $this->assertEquals(0, Meeting::getCount());
            $this->assertEquals(0, Note::getCount());
            $this->assertEquals(0, Task::getCount());
            $this->assertEquals(1, User::getCount());
            $messageLogger   = new MessageLogger();
            DemoDataUtil::load($messageLogger, 3);
            $this->assertEquals(7, Group::getCount());
            $this->assertEquals(3, Role::getCount());
            $this->assertEquals(1, Account::getCount());
            $this->assertEquals(4, Contact::getCount());
            $this->assertEquals(2, Opportunity::getCount());
            $this->assertEquals(3, Meeting::getCount());
            $this->assertEquals(3, Note::getCount());
            $this->assertEquals(3, Task::getCount());
            $this->assertEquals(9, User::getCount());
        }
    }
?>