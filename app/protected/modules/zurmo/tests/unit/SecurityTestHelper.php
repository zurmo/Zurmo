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

    class SecurityTestHelper
    {
        private static $usernamesToUserInfo = array(
                               // Title      Groups                         Role
            'billy'    => array('Mr',   array('Administrators', 'Nerds'),   null),
            'bobby'    => array('Mr',   array('Managers', 'Sales Staff'),   'Sales Manager'),
            'benny'    => array('Mr',   array(            'Sales Staff'),   'Sales Person'),
            'betty'    => array('Ms',   array(            'Sales Staff'),   'Junior Sales Person'),
            'bernice'  => array('Miss', array('Managers', 'Support Staff'), 'Support Manager'),
            'brian'    => array('Mr',   array(            'Support Staff'), 'Support Person')
        );

        private static $groupsNamesToGroupNames = array(
            'Dorks' => array('Sales Staff'),
        );

        private static $parentToChildRoleNames = array(
            'Sales Manager'   => 'Sales Person',
            'Sales Person'    => 'Junior Sales Person',
            'Support Manager' => 'Support Person',
        );

        public static function createSuperAdmin()
        {
            try
            {
                return User::getByUsername('super');
            }
            catch (NotFoundException $e)
            {
                $user = new User();
                $user->username           = 'super';
                $user->title->value       = 'Mr';
                $user->firstName          = 'Clark';
                $user->lastName           = 'Kent';
                $user->setPassword('super');
                $saved = $user->save();
                assert('$saved');

                $group = Group::getByName('Super Administrators');
                $group->users->add($user);
                $saved = $group->save();
                assert('$saved');

                return $user;
            }
        }

        public static function createUsers()
        {
            foreach (self::$usernamesToUserInfo as $username => $userInfo)
            {
                $user = new User();
                $user->username           = $username;
                $user->title->value       = $userInfo[0];
                $user->firstName          = ucfirst($username);
                $user->lastName           = ucfirst($username) . 'son';
                $user->setPassword($username);
                $saved = $user->save();
                assert('$saved');
            }
        }

        public static function createGroups()
        {
            $groupNames = array();
            foreach (self::$usernamesToUserInfo as $username => $userInfo)
            {
                $usersGroupNames = $userInfo[1];
                foreach ($usersGroupNames as $groupName)
                {
                    if (!in_array($groupName, $groupNames))
                    {
                        if (!isset($groupNames[$groupName]))
                        {
                            $groupNames[$groupName] = array();
                        }
                        $groupNames[$groupName][] = $username;
                    }
                }
            }
            foreach ($groupNames as $groupName => $usernames)
            {
                $group = new Group();
                $group->name = $groupName;
                foreach ($usernames as $username)
                {
                    $group->users->add(User::getByUsername($username));
                }
                assert('$group->users->count() == count($usernames)');
                $saved = $group->save();
                assert('$saved');
            }
            foreach (self::$groupsNamesToGroupNames as $groupName => $groupNames)
            {
                $group = new Group();
                $group->name = $groupName;
                foreach ($groupNames as $subGroupName)
                {
                    $subGroup = Group::getByName($subGroupName);
                    $group->groups->add($subGroup);
                }
                assert('$group->groups->count() == count($groupNames)');
                $saved = $group->save();
                assert('$saved');
            }
        }

        public static function createRoles()
        {
            foreach (self::$parentToChildRoleNames as $parentRoleName => $childRoleName)
            {
                if ($childRoleName !== null)
                {
                    $childRole = new Role();
                    $childRole->name = $childRoleName;
                    $childRole->validate();
                    $saved = $childRole->save();
                    assert('$saved');
                }
                try
                {
                    $parentRole = Role::getByName($parentRoleName);
                }
                catch (NotFoundException $e)
                {
                    $parentRole = new Role();
                }
                $parentRole->name = $parentRoleName;
                if ($childRoleName !== null)
                {
                    $parentRole->roles->add($childRole);
                }
                $saved = $parentRole->save();
                assert('$saved');
                $parentRole->forget();
                if ($childRoleName !== null)
                {
                    $childRole->forget();
                }
            }
            foreach (self::$usernamesToUserInfo as $username => $userInfo)
            {
                $roleName = $userInfo[2];
                if ($roleName !== null)
                {
                    assert('is_string($roleName)');
                    $role = Role::getByName($roleName);
                    $user = User::getByUsername($username);
                    $role->users->add($user);
                    $saved = $role->save();
                    assert('$saved');
                    $user->forget(); //do this so that if you retrieve the $user, $user->role will be known.
                }
            }
        }

        public static function createAccounts()
        {
            $user = User::getByUsername('billy');
            $accountNames = array('Supermart',
                                  'Microsoft',
                                  'Google');
            foreach ($accountNames as $accountName)
            {
                $account = new Account();
                $account->name  = $accountName;
                $account->owner = $user;
                $saved = $account->save();
                assert('$saved');
            }
        }
    }
?>
