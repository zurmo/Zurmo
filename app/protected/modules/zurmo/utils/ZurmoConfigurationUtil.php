<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper Utility to interface with the global and per user configuration settings on a module.
     */
    class ZurmoConfigurationUtil
    {
        /**
         * For the current user, retrieve a configuration value by module name and key.
         * @return configuration value of specified key
         */
        public static function getForCurrentUserByModuleName($moduleName, $key)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('Yii::app()->user->userModel == null || Yii::app()->user->userModel->id > 0');
            if (!Yii::app()->user->userModel instanceof User)
            {
                return null;
            }
            return ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel, $moduleName, $key);
        }

        /**
         * Retrieve a global configuration value by module name and key.
         * @return configuration value of specified key
         */
        public static function getByModuleName($moduleName, $key)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            if (!RedBeanDatabase::isSetup())
            {
                return null;
            }
            $metadata = $moduleName::getMetadata();
            if (isset($metadata['global']) && isset($metadata['global'][$key]))
            {
                return $metadata['global'][$key];
            }
            return null;
        }

        /**
         * For a specific user, retrieve a configuration value by module name and key.
         * @return configuration value of specified key
         */
        public static function getByUserAndModuleName($user, $moduleName, $key)
        {
            assert('$user instanceof User && $user->id > 0');
            assert('is_string($moduleName)');
            assert('is_string($key)');
            $metadata = $moduleName::getMetadata($user);
            if (isset($metadata['perUser']) && isset($metadata['perUser'][$key]))
            {
                return $metadata['perUser'][$key];
            }
            if (isset($metadata['global']) && isset($metadata['global'][$key]))
            {
                return $metadata['global'][$key];
            }
            return null;
        }

        /**
         * For the current user, set a configuration value by module name and key.
         */
        public static function setForCurrentUserByModuleName($moduleName, $key, $value)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('Yii::app()->user->userModel == null || Yii::app()->user->userModel->id > 0');
            if (!Yii::app()->user->userModel instanceof User)
            {
                return null;
            }
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel, $moduleName, $key, $value);
        }

        /**
         * Set a global configuration value by module name and key
         */
        public static function setByModuleName($moduleName, $key, $value)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            if (!RedBeanDatabase::isSetup())
            {
                return null;
            }
            $metadata = $moduleName::getMetadata();
            $metadata['global'][$key] = $value;
            $moduleName::setMetadata($metadata);
        }

        /**
         * For a specified user, set a configuration value by module name and key
         */
        public static function setByUserAndModuleName($user, $moduleName, $key, $value)
        {
            assert('$user instanceof User && $user->id > 0');
            assert('is_string($moduleName)');
            assert('is_string($key)');
            $metadata = $moduleName::getMetadata($user);
            $metadata['perUser'][$key] = $value;
            $moduleName::setMetadata($metadata, $user);
        }
    }
?>