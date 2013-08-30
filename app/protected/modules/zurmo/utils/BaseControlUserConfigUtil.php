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
     * Class BaseControlUserConfigUtil
     * Helper class to extend when we want to get/set control user for some action or job using ZurmoConfigUtil
     */
    abstract class BaseControlUserConfigUtil
    {
        /**
         * Module name to which this config would belong, not necessarily the module name of translation inside this class
         */
        const CONFIG_MODULE_NAME       = 'ZurmoModule';

        /**
         * Config key for the control user we are querying
         */
        const CONFIG_KEY                = null;

        /**
         * When running a special action or a job an elevated user must be used in order to ensure the activities can
         * be processed properly.  if there is not a user specified, then a fall back of the first user that is a super
         * administrator will be returned
         * @param boolean $setOnMissing whether function set the fallback user inside config for future requests or not
         * @return User $user
         * @throws NotSupportedException if there is no user specified and there are no users in the super admin group
         * @throws MissingASuperAdministratorException if there are no super administrators available
         */
        public static function getUserToRunAs($setOnMissing = true)
        {
            $configModuleName   = static::getConfigModuleId();
            $configKey          = static::getConfigKey();
            $superGroup   = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (null != $userId = ZurmoConfigurationUtil::getByModuleName($configModuleName, $configKey))
            {
                try
                {
                    $user  = User::getById($userId);
                    if ($user->groups->contains($superGroup))
                    {
                        return $user;
                    }
                }
                catch (NotFoundException $e)
                {
                }
            }
            if ($superGroup->users->count() == 0)
            {
                throw new MissingASuperAdministratorException();
            }
            else
            {
                foreach ($superGroup->users as $user)
                {
                    if ($user->isSystemUser)
                    {
                        if ($setOnMissing)
                        {
                            ZurmoConfigurationUtil::setByModuleName($configModuleName, $configKey, $user->id);
                        }
                        return $user;
                    }
                }
                //Fallback if there is no system user for some reason.
                $user = $superGroup->users->offsetGet(0);
                if ($setOnMissing)
                {
                    ZurmoConfigurationUtil::setByModuleName($configModuleName, $configKey, $user->id);
                }
                return $user;
            }
        }

        /**
         * @see getUserToRunTrackActionAs
         * @param User $user
         * @throws NotSupportedException
         */
        public static function setUserToRunAs(User $user)
        {
            assert('$user->id > 0');
            $superGroup   = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$user->groups->contains($superGroup))
            {
                throw new NotSupportedException();
            }
            $configModuleName   = static::getConfigModuleId();
            $configKey          = static::getConfigKey();
            ZurmoConfigurationUtil::setByModuleName($configModuleName, $configKey, $user->id);
        }

        protected static function getConfigModuleId()
        {
            $configModuleName = static::CONFIG_MODULE_NAME;
            if (empty($configModuleName))
            {
                throw new NotSupportedException();
            }
            return $configModuleName;
        }

        protected static function getConfigKey()
        {
            $configKey  = static::CONFIG_KEY;
            if (empty($configKey))
            {
                throw new NotSupportedException();
            }
            return $configKey;
        }
    }
?>