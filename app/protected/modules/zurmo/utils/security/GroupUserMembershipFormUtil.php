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

    /**
     * Helper class to create a form
     * from a group's membership data. Also allows for
     * setting membership on a group from a form.
     */
    class GroupUserMembershipFormUtil
    {
        /**
         * @return a GroupUserMembershipForm
         */
        public static function makeFormFromGroup($group)
        {
            assert('$group instanceof Group');
            $form                        = new GroupUserMembershipForm();
            $userMembershipData          = GroupUserMembershipFormUtil::makeUserMembershipDataFromUsers(
                                                $group->users);
            $userNonMembershipData       = GroupUserMembershipFormUtil::makeNonUserMembershipDataFromUserMembershipData(
                                                $userMembershipData);
            $form->userMembershipData    = $userMembershipData;
            $form->userNonMembershipData = $userNonMembershipData;
            return $form;
        }

        /**
         * Takes post data and prepares it for setting the membership on the group.
         * Adds and removes users to group based on a form's userMembershipData
         * @return boolean. True if membership was set successfully.
         */
        public static function setMembershipFromForm($form, $group)
        {
            assert('$group instanceof Group');
            assert('$form instanceof GroupUserMembershipForm');
            $removedUsers = array();
            $addedUsers   = array();
            foreach ($group->users as $index => $user)
            {
                if (empty($form->userMembershipData[$user->id]))
                {
                    $group->users->removeByIndex($index);
                    $removedUsers[] = $user;
                }
            }
            $users = GroupUserMembershipFormUtil::makeUsersFromUserMembershipData($form->userMembershipData);
            foreach ($users as $user)
            {
                if (!$group->users->contains($user))
                {
                    $group->users->add($user);
                    $addedUsers[] = $user;
                }
            }
            $group->save();
            foreach ($removedUsers as $user)
            {
                ReadPermissionsOptimizationUtil::userRemovedFromGroup($group, $user);
            }
            foreach ($addedUsers as $user)
            {
                ReadPermissionsOptimizationUtil::userAddedToGroup($group, $user);
            }
            return true;
        }

        /**
         * Set the userMembershipData attribute on the GroupUserMembershipForm
         */
        public static function setFormFromCastedPost(GroupUserMembershipForm $membershipForm, array $postData)
        {
            $userMembershipData = array();
            if (!empty($postData['userMembershipData']))
            {
                foreach ($postData['userMembershipData'] as $userId)
                {
                    $user                        = User::getById($userId);
                    $userMembershipData[$userId] = strval($user);
                }
            }
            $membershipForm->userMembershipData = $userMembershipData;
            return $membershipForm;
        }

        protected static function makeUserMembershipDataFromUsers($users)
        {
            $data = array();
            foreach ($users as $user)
            {
                $data[$user->id] = strval($user);
            }
            return $data;
        }

        protected static function makeUsersFromUserMembershipData(array $userData)
        {
            $users = array();
            foreach ($userData as $id => $name)
            {
                $users[] = User::getById($id);
            }
            return $users;
        }

        protected static function makeNonUserMembershipDataFromUserMembershipData(array $userData)
        {
            $allUsers = User::getAll();
            $data     = array();
            foreach ($allUsers as $user)
            {
                if (empty($userData[$user->id]))
                {
                    $data[$user->id] = strval($user);
                }
            }
            return $data;
        }

        /**
         * Used to properly type cast incoming POST data
         */
        public static function typeCastPostData($postData)
        {
            assert('is_array($postData)');
            foreach ($postData['userMembershipData'] as $index => $userId)
            {
                $postData['userMembershipData'][$index] = intval($userId);
            }
            return $postData;
        }
    }
?>
