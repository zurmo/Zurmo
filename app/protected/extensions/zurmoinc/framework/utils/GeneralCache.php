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
     * This is a general cache helper that utilizes both php caching and memcaching if available. Utilized for
     * caching requirements that are simple in/out of a serialized array or string of information.
     */
    class GeneralCache
    {
        private static $cachedEntries = array();

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
                $serializedData = Yii::app()->cache->get('G:' . $identifier);
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

        public static function cacheEntry($identifier, $entry)
        {
            assert('is_string($identifier)');
            assert('is_string($entry) || is_array($entry) || is_numeric($entry) || is_object($entry)');
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries[$identifier] = $entry;
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                Yii::app()->cache->set('G:' . $identifier, serialize($entry));
            }
        }

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
                Yii::app()->cache->delete('G:' . $identifier);
            }
        }

        public static function forgetAll()
        {
            if (PHP_CACHING_ON)
            {
                self::$cachedEntries = array();
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                Yii::app()->cache->flush();
            }
        }
    }
?>
