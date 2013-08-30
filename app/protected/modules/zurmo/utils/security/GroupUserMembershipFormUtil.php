<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class to create a form
     * from a group's membership data. Also allows for
     * setting membership on a group from a form.
     */
    class GroupUserMembershipFormUtil
    {
        /**
         * Used to properly type cast incoming POST data
         */
        public static function typeCastPostData($postData)
        {
            assert('is_array($postData)');
            if (isset($postData['userMembershipData']))
            {
                foreach ($postData['userMembershipData'] as $index => $userId)
                {
                    $postData['userMembershipData'][$index] = intval($userId);
                }
            }
            return $postData;
        }

        /**
         * @param $group
         * @return GroupUserMembershipForm
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
         * @param GroupUserMembershipForm $form
         * @param Group $group
         * @return null|string $message. If message present than validation failed.
         */
        public static function validateMembershipChange(GroupUserMembershipForm $form, Group $group)
        {
            if ($group->name == Group::SUPER_ADMINISTRATORS_GROUP_NAME)
            {
                if (count($form->userMembershipData) == 0)
                {
                    return Zurmo::t('ZurmoModule', 'There must be at least one super administrator');
                }
                foreach ($group->users as $index => $user)
                {
                    if (empty($form->userMembershipData[$user->id]) && ($user->isRootUser))
                    {
                        return Zurmo::t('ZurmoModule', 'You cannot remove {user} from this group', array('{user}' => strval($user)));
                    }
                }
            }
        }

        /**
         * Takes post data and prepares it for setting the membership on the group.
         * Adds and removes users to group based on a form's userMembershipData
         * @param $form
         * @param $group
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
                if (empty($form->userMembershipData[$user->id]) && !$user->isSystemUser && !$user->isRootUser)
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
         * @param GroupUserMembershipForm $membershipForm
         * @param array $postData
         * @return GroupUserMembershipForm
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
                if (!$user->isSystemUser)
                {
                    $data[$user->id] = strval($user);
                }
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
                if (empty($userData[$user->id]) && !$user->isSystemUser)
                {
                    $data[$user->id] = strval($user);
                }
            }
            return $data;
        }
    }
?>
