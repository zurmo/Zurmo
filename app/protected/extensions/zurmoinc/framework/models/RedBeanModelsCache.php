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
     * Caches RedBean models. If caching is configured it the cached models
     * outlive requests. Either way the models are cached in php for the duration
     * of the current request. This allows multiple references to the same cached
     * model, whether it came out of the memcache or not, to reference the same
     * php object.
     */
    class RedBeanModelsCache
    {
        const MAX_MODELS_CACHED_IN_MEMORY = 100;

        private static $modelIdentifiersToModels = array();

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
                $cachedData = Yii::app()->cache->get('M:' . $modelIdentifier);
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
                assert('crc32(serialize($model)) == $checksum');
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
                $serializedModel = serialize($model);
                $checksum  = YII_DEBUG ? crc32($serializedModel) : 0;
                $cachedData = serialize(array($serializedModel, $checksum));
                Yii::app()->cache->set('M:' . $modelIdentifier, $cachedData);
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
                Yii::app()->cache->delete('M:' . $modelIdentifier, 0);
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
                Yii::app()->cache->flush();
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
    }
?>
