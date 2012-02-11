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
     * Helper class for managing the explicit setting of login rights against a user in the user interface.
     * @see UserStatus
     * @see DerivedUserStatusElement
     */
    class UserStatusUtil
    {
        const ACTIVE = 'Active';

        const INACTIVE = 'Inactive';

        /**
         * Given a User, make the UserStatus based on explict login rights on that user
         * @param User $user
         */
        public static function makeByUser(User $user)
        {
            $userStatus = new UserStatus();
            if ( Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB) &&
                Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE) &&
                Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API))
            {
                $userStatus->setInactive();
            }
            return $userStatus;
        }

        public static function getSelectedValueByUser(User $user)
        {
            $userStatus = self::makeByUser($user);
            if ($userStatus->isActive())
            {
                return self::ACTIVE;
            }
            else
            {
                return self::INACTIVE;
            }
        }

        /**
         * @param array $postData
         */
        public static function makeByPostData($postData)
        {
            assert('is_array($postData)');
            $userStatus = new UserStatus();
            if (!isset($postData['userStatus']))
            {
                return null;
            }
            elseif ($postData['userStatus'] == self::ACTIVE)
            {
                return $userStatus;
            }
            elseif ($postData['userStatus'] == self::INACTIVE)
            {
                $userStatus->setInactive();
                return $userStatus;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Unset the 'userStatus' element in a post data array if it exists.
         * @param array $postData
         * @return array of post data with the 'userStatus' removed.
         */
        public static function removeIfExistsFromPostData($postData)
        {
            assert('is_array($postData)');
            if (isset($postData['userStatus']))
            {
                unset($postData['userStatus']);
            }
            return $postData;
        }

        /**
         * Given a User and a UserStatus resolve the removal or addition of explicit deny rights for login.
         * @param User $user
         * @param UserStatus $userStatus
         */
        public static function resolveUserStatus(User $user, UserStatus $userStatus)
        {
            assert('$user->id > 0');
            if ($userStatus->isActive())
            {
                if ( Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB) ||
                    Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE) ||
                    Right::DENY == $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API))
                {
                    self::removeExplicitDenyRights($user);
                }
            }
            else
            {
                if ( Right::DENY != $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB) ||
                    Right::DENY != $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE) ||
                    Right::DENY != $user->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API))
                {
                    self::setExplicitDenyRights($user);
                }
            }
        }

        protected static function removeExplicitDenyRights(User $user)
        {
            assert('$user->id > 0');
            $user->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB,     Right::DENY);
            $user->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE,  Right::DENY);
            $user->removeRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $saved = $user->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        protected static function setExplicitDenyRights(User $user)
        {
            assert('$user->id > 0');
            $user->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB,     Right::DENY);
            $user->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE,  Right::DENY);
            $user->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API, Right::DENY);
            $saved = $user->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public static function getStatusArray()
        {
            $statusData = array(self::ACTIVE, self::INACTIVE);
            return array_combine($statusData, $statusData);
        }

        /**
         * Given two users, can the first $user edit the status on the $anotherUser.  This is important to check to keep
         * user's from deactivating themselves and deactivating administrators.
         * @param User $user
         * @param User $anotherUser
         * @return true/false
         */
        public static function canUserEditStatusOnAnotherUser(User $user, User $anotherUser)
        {
            assert('$user->id > 0');
            assert('$anotherUser->id > 0');
            if ($user->isSame($anotherUser))
            {
                return false;
            }
            if (Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME)->contains($anotherUser))
            {
                return false;
            }
            if (!RightsUtil::canUserAccessModule('UsersModule', $user))
            {
                return false;
            }
            return true;
        }
    }
?>