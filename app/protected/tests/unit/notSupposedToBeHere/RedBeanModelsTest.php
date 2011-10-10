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

    class RedBeanModelsTest extends BaseTest
    {
        const USERS = 5;

        private $usernames;

        public function setUp()
        {
            if (!isset($this->usernames))
            {
                $this->usernames = TestHelpers::makeUniqueRandomUsernames(RedBeanModelsTest::USERS);
            }
            foreach ($this->usernames as $username)
            {
                $user = new User();
                $user->username           = $username;
                $user->title->value       = 'Mr.';
                $user->firstName          = $username;
                $user->lastName           = $username;
                $user->setPassword(strtolower($username));
                $this->assertTrue($user->save());
            }
        }

        public function tearDown()
        {
            foreach ($this->usernames as $username)
            {
                $user = User::getByUsername($username);
                $user->delete();
            }
        }

        public function testCreateAndIterateLazyModels()
        {
            $users = new RedBeanModels('User');
            $this->assertEquals(count($this->usernames), $users->count());
            foreach ($users as $user)
            {
                $this->assertTrue(in_array($user->username, $this->usernames));
                $this->assertEquals($user->username, $user->firstName);
                $this->assertEquals($user->username, $user->lastName);
            }
        }
    }
?>
