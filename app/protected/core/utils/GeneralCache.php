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
     * This is a general cache helper that utilizes both php caching and memcaching if available. Utilized for
     * caching requirements that are simple in/out of a serialized array or string of information.
     */
    class GeneralCache extends ZurmoCache
    {
        private static $cachedEntries = array();

        public static $cacheType = 'G:';

        /**
         * Get entry from php cache and/or memcache
         * @param string $identifier
         * @return mixed
         * @throws NotFoundException
         */
        public static function getEntry($identifier)
        {
            assert('is_string($identifier)');
            if (PHP_CACHING_ON)
            {
                if (isset(self::$cachedEntries[$identifier]))
                {
                    return self::$cachedEntries[$identifier];
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier, static::$cacheType);

                @$serializedData = Yii::app()->cache->get($prefix . $identifier);
                //echo "GET:" . $prefix . $identifier . "\n";
                if ($serializedData !== false)
                {
                    $unserializedData = unserialize($serializedData);
                    if (PHP_CACHING_ON)
                    {
                        self::$cachedEntries[$identifier] = $unserializedData;
                    }
                    return $unserializedData;
                }
            }
            throw new NotFoundException();
        }

        /**
         * Add entry to php cache and/or memcache
         * @param string $identifier
         * @param mixed $entry
         */
        public static function cacheEntry($identifier, $entry)
        {
            assert('is_string($identifier)');
            assert('is_string($entry) || is_array($entry) || is_numeric($entry) || is_object($entry) || $entry == null');
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries[$identifier] = $entry;
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier, static::$cacheType);
                @Yii::app()->cache->set($prefix . $identifier, serialize($entry));
            }
        }

        /**
         * Remove entry from php cache and/or memcache
         * @param string $identifier
         */
        public static function forgetEntry($identifier)
        {
            if (PHP_CACHING_ON)
            {
                if (isset(self::$cachedEntries[$identifier]))
                {
                    unset(self::$cachedEntries[$identifier]);
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($identifier, static::$cacheType);
                @Yii::app()->cache->delete($prefix . $identifier);
            }
        }

        /**
         * Remove all GeneralCache data from php cache and/or memcache
         * Please note that we are not using $Yii::app()->cache->forget function, because
         * this function remove all data from memcache, so it would remove memcache data
         * that are added by other application on same server(if there is only one instance of memcache on server)
         */
        public static function forgetAll()
        {
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries = array();
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                self::incrementCacheIncrementValue(static::$cacheType);
            }
        }
    }
?>
