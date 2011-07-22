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
     * Sets, gets, and deletes configuration entries in the application's database by user.
     */
    class UserConfiguration
    {
        /**
         * Searches for a configuration entry for a particular user.
         * @param $user id
         * @param $moduleName A non-empty string identifying the module to which
         * the configuration entry belongs.
         * @param $key A non-empty string identifying the configuration entry.
         * @return A value of the type that was originally put in into the entry or null
         * if the entry is not found.
         */
        public static function find($userId, $moduleName, $key)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('$userId != null && is_int($userId)');
            assert('$moduleName != ""');
            assert('$key        != ""');
            $bean = UserConfiguration::findBean($userId, $moduleName, $key);
            if ($bean !== false)
            {
                return $bean->value;
            }
            return null;
        }

        /**
         * Gets a configuration entry for a particular user.
         * @param $user id
         * @param $moduleName A non-empty string identifying the module to which
         * the configuration entry belongs.
         * @param $key A non-empty string identifying the configuration entry.
         * @return A value of the type that was originally put in into the entry.
         */
        public static function get($userId, $moduleName, $key)
        {
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('$userId != null && is_int($userId)');
            assert('$moduleName != ""');
            assert('$key        != ""');
            return UserConfiguration::getBean($userId, $moduleName, $key)->value;
        }

        /**
         * Gets all of the configuration entries for a particular user.
         * @param $user id
         * @return An array of arrays, keyed first on the module names, and
         * then the entry keys.
         */
        public static function getAll($userId)
        {
            assert('$userId != null && is_int($userId)');
            $beans = R::find(UserConfiguration::getTableName(), "userId = $userId");
            $entries = array();
            foreach ($beans as $bean)
            {
                if (!isset($entries[$bean->moduleName]))
                {
                    $entries[$bean->moduleName] = array();
                }
                $entries[$bean->moduleName][$bean->key] = $bean->value;
            }
            return $entries;
        }

        /**
         * Gets all of the configuration entries for a module.
         * @param $user id
         * @return An array of arrays, keyed first on the module names, and
         * then the entry keys.
         */
        public static function getByModuleName($userId, $moduleName)
        {
            assert('$userId != null && is_int($userId)');
            assert('$moduleName != ""');
            $beans = R::find(UserConfiguration::getTableName(), "userId = $userId and moduleName = '$moduleName'");
            $moduleEntries = array();
            foreach ($beans as $bean)
            {
                if (!isset($moduleEntries[$bean->moduleName]))
                {
                    $moduleEntries[$bean->moduleName] = array();
                }
                $moduleEntries[$bean->moduleName][$bean->key] = $bean->value;
            }
            return $moduleEntries;
        }

        /**
         * Sets a configuration entry.
         * @param $user id
         * @param $moduleName A non-empty string identifying the module to which
         * the configuration entry belongs.
         * @param $key A non-empty string identifying the configuration entry.
         * @param $value The value to store, of whatever desired type.
         */
        public static function set($userId, $moduleName, $key, $value)
        {
            assert('$userId != null && is_int($userId)');
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('$moduleName != ""');
            assert('$key        != ""');
            try
            {
                $bean = UserConfiguration::getBean($userId, $moduleName, $key);
            }
            catch (NotFoundException $e)
            {
                $bean = R::dispense(UserConfiguration::getTableName());
                $bean->userId     = $userId;
                $bean->moduleName = $moduleName;
                $bean->key        = $key;
            }
            $bean->value  = $value;
            R::store($bean);
        }

        /**
         *Deletes a configuration entry.
         * @param $user id
         * @param $moduleName A non-empty string identifying the module to which
         * the configuration entry belongs.
         * @param $key A non-empty string identifying the configuration entry.
         */
        public static function delete($userId, $moduleName, $key)
        {
            assert('$userId != null && is_int($userId)');
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('$moduleName != ""');
            assert('$key        != ""');
            $bean = UserConfiguration::getBean($userId, $moduleName, $key);
            if (isset($bean))
            {
                R::trash($bean);
                unset($bean);
            }
        }

        /**
         * Gets the <a href="http://www.redbeanphp.com/">RedBean</a> bean
         * representing the user configuration entry.
         * @param $user id
         * @return A bean.
         */
        protected static function getBean($userId, $moduleName, $key)
        {
            $bean = UserConfiguration::findBean($userId, $moduleName, $key);
            if ($bean !== false)
            {
                return $bean;
            }
            throw new NotFoundException();
        }

        protected static function findBean($userId, $moduleName, $key)
        {
            assert('$userId != null && is_int($userId)');
            assert('is_string($moduleName)');
            assert('is_string($key)');
            assert('$moduleName != ""');
            assert('$key        != ""');
            $bean = R::findOne(UserConfiguration::getTableName(),
                               'userId = ? and moduleName = ? and ' .
                               DatabaseCompatibilityUtil::quoteString('key') . ' = ?',
                               array($userId, $moduleName, $key));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            return $bean;
        }

        /**
         * Gets the table name in which configuration entries are stored in
         * the database.
         */
        protected static function getTableName()
        {
            return 'userconfiguration';
        }
    }
?>
