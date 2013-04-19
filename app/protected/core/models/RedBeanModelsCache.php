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
     * Caches RedBean models. If caching is configured it the cached models
     * outlive requests. Either way the models are cached in php for the duration
     * of the current request. This allows multiple references to the same cached
     * model, whether it came out of the memcache or not, to reference the same
     * php object.
     */
    class RedBeanModelsCache extends ZurmoCache
    {
        const MAX_MODELS_CACHED_IN_MEMORY = 100;

        private static $modelIdentifiersToModels = array();

        public static $cacheType = 'M:';

        /**
         * Get a cached model.
         */
        public static function getModel($modelIdentifier)
        {
            assert('is_string($modelIdentifier)');
            assert('$modelIdentifier != ""');
            if (PHP_CACHING_ON && isset(self::$modelIdentifiersToModels[$modelIdentifier]))
            {
                return self::$modelIdentifiersToModels[$modelIdentifier];
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($modelIdentifier, self::$cacheType);
                $cachedData = Yii::app()->cache->get($prefix . $modelIdentifier);
                if ($cachedData === false)
                {
                    throw new NotFoundException();
                }
                $serializedModelAndChecksum = unserialize($cachedData);
                if (!is_array($serializedModelAndChecksum) || count($serializedModelAndChecksum) != 2)
                {
                    throw new NotFoundException();
                }
                list($serializedModel, $checksum) = $serializedModelAndChecksum;
                assert('$checksum == 0 || $checksum == crc32($serializedModel)');
                $model = unserialize($serializedModel);
                if ($model === null) // RedBeanModel objected to what was being unserialized.
                {
                    throw new NotFoundException();
                }
                assert('serialize($model) == $serializedModel');
                if (YII_DEBUG)
                {
                    if (crc32(serialize($model)) != $checksum)
                    {
                        throw new ChecksumMismatchException();
                    }
                }
                assert('$model instanceof RedBeanModel');
                self::$modelIdentifiersToModels[$modelIdentifier] = $model;
                return $model;
            }
            throw new NotFoundException();
        }

        /**
         * Cache a model maintaining the in memory model
         * cache to a limited size.
         */
        public static function cacheModel(RedBeanModel $model)
        {
            $modelIdentifier = $model->getModelIdentifier();
            if (PHP_CACHING_ON)
            {
                self::$modelIdentifiersToModels[$modelIdentifier] = $model;
                if (count(self::$modelIdentifiersToModels) > self::MAX_MODELS_CACHED_IN_MEMORY)
                {
                    self::$modelIdentifiersToModels = array_slice(self::$modelIdentifiersToModels,
                                                                  count(self::$modelIdentifiersToModels) -
                                                                    self::MAX_MODELS_CACHED_IN_MEMORY);
                }
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($modelIdentifier, self::$cacheType);

                $serializedModel = serialize($model);
                $checksum  = YII_DEBUG ? crc32($serializedModel) : 0;
                $cachedData = serialize(array($serializedModel, $checksum));
                Yii::app()->cache->set($prefix . $modelIdentifier, $cachedData);
            }
        }

        /**
         * Forget a cached model.
         */
        public static function forgetModel(RedBeanModel $model)
        {
            $modelIdentifier = $model->getModelIdentifier();
            if (PHP_CACHING_ON)
            {
                unset(self::$modelIdentifiersToModels[$modelIdentifier]);
            }
            if (MEMCACHE_ON && Yii::app()->cache !== null)
            {
                $prefix = self::getCachePrefix($modelIdentifier, self::$cacheType);
                Yii::app()->cache->delete($prefix . $modelIdentifier);
            }
        }

        /**
         * Forget all cached models.
         * @param $onlyForgetPhpCache is for testing only. It is for
         * artificially creating situations where memcache must be
         * accessed for testing memcache and RedBeanModel serialization.
         */
        public static function forgetAll($onlyForgetPhpCache = false)
        {
            if (PHP_CACHING_ON)
            {
                self::$modelIdentifiersToModels = array();
            }
            if (!$onlyForgetPhpCache && MEMCACHE_ON && Yii::app()->cache !== null)
            {
                self::incrementCacheIncrementValue(static::$cacheType);
                //@Yii::app()->cache->flush();
            }
        }

        /**
         * TODO: Only forget by model.
         * @param $modelClassName - string.
         */
        public static function forgetAllByModelType($modelClassName)
        {
            assert('is_string($modelClassName)');
            self::forgetAll();
        }

        /**
         * Used for testing purposes if you need to clear out just the php caching.
         */
        public static function forgetAllModelIdentifiersToModels()
        {
            self::$modelIdentifiersToModels = array();
        }
    }
?>
