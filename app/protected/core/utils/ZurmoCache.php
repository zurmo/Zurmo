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
     * This is a base caching class, that contains code related to caching.
     * Memcache doesn't have option to remove data related just to one application or host,
     * and that is why we had to implement methods that would simulate this feature.
     * Please check this link for more details about this idea:
     * http://stackoverflow.com/questions/1202174/memcache-invalidate-entries-according-to-a-pattern
     */
    abstract class ZurmoCache
    {
        protected static $cacheIncrementValueVariableName = 'CacheIncrementValue';

        protected static $additionalStringForCachePrefix = '';

        /**
         * Get cache prefix, based on ZURMO_TOKEN, $cacheIncrementValue and cache type.
         * In case that we want to get just cache increment value, prefix will not contain increment value.
         * @param string $identifier
         * @return string
         */
        public static function getCachePrefix($identifier)
        {
            if (self::isIdentifierCacheIncrementValueName($identifier))
            {
                $prefix = ZURMO_TOKEN . '_' . static::$cacheType;
            }
            else
            {
                $cacheIncrementValue = self::getCacheIncrementValue(static::$cacheType);
                $prefix = ZURMO_TOKEN . '_' . $cacheIncrementValue . '_' . static::$cacheType;
            }

            if (self::getAdditionalStringForCachePrefix() != '')
            {
                $prefix = self::getAdditionalStringForCachePrefix() . '_' . $prefix;
            }

            return $prefix;
        }

        /**
         * Get curent increment value, based on $cacheType. Cache types can be:
         * "G:" - for GlobalCache
         * "M:" - for RedBeanModelsCache
         * "P:" - for PermissionCache
         * We need to distinct those cache types, because we should be able to forget only GlobalCache(increment
         * cache increment value), while other two cache types will contain valid data.
         * @param string $cacheType
         * @return int|mixed
         */
        protected static function getCacheIncrementValue($cacheType)
        {
            try
            {
                $cacheIncrementValue = GeneralCache::getEntry(self::$cacheIncrementValueVariableName . $cacheType);
            }
            catch (NotFoundException $e)
            {
                $cacheIncrementValue = 0;
                self::setCacheIncrementValue($cacheType, $cacheIncrementValue);
            }
            return $cacheIncrementValue;
        }

        /**
         * @param string $cacheType
         * @param mixed $value
         */
        protected static function setCacheIncrementValue($cacheType, $value)
        {
            GeneralCache::cacheEntry(self::$cacheIncrementValueVariableName . $cacheType, $value);
        }

        /**
         * Increment CacheIncrementValue
         * @param string $cacheType
         */
        protected static function incrementCacheIncrementValue($cacheType)
        {
            $currentCacheIncrementValue = self::getCacheIncrementValue($cacheType);
            $currentCacheIncrementValue++;
            self::setCacheIncrementValue($cacheType, $currentCacheIncrementValue);
        }

        /**
         * Check if identifier is same as self::$cacheIncrementValueVariableName.
         * @param $identifier
         * @return bool
         */
        protected static function isIdentifierCacheIncrementValueName($identifier)
        {
            if (strstr($identifier, self::$cacheIncrementValueVariableName) !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Set additional prefix
         * This is used to distinct memcache value for tests and for website, because test application and
         * website application use same ZURMO_TOKEN. This prefix is empty for web application,
         * and for tests it is set to "Test"
         * @param string $prefix
         */
        public static function setAdditionalStringForCachePrefix($prefix = '')
        {
            self::$additionalStringForCachePrefix = $prefix;
        }

        /**
         * Get additional prefix
         * @return string
         */
        public static function getAdditionalStringForCachePrefix()
        {
            return self::$additionalStringForCachePrefix;
        }
    }
?>
