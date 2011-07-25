<?php
    /**
     * Helper class to organize random data arrays for different models.
     */
    class RandomDataUtil
    {
        private static $randomData;

        /**
         * Given a module class name and model class name, return the random data array if it exists. Will cache
         * the random data array upon the first load.
         * @param string $moduleClassName
         * @param string $modelClassName
         */
        public static function getRandomDataByModuleAndModelClassNames($moduleClassName, $modelClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            if(!isset(self::$randomData[$modelClassName]))
            {
                $directoryName = $moduleClassName::getDirectoryName();
                $moduleName    = $moduleClassName::getPluralCamelCasedName();
                $filePath      = Yii::getPathOfAlias('application.modules.' . $directoryName . '.data.' .
                                 $modelClassName . 'RandomData') . '.php';
                if(file_exists($filePath))
                {
                    self::$randomData[$modelClassName] = require($filePath);
                }
            }
            return self::$randomData[$modelClassName];
        }

        /**
         * Given an array, randomly returns a value.
         * @param array $array
         */
        public static function getRandomValueFromArray($array)
        {
            assert('is_array($array)');
            return $array[array_rand($array)];
        }

        /**
         * Returns true/false randomly.
         */
        public static function getRandomBooleanValue()
        {
            $value  = mt_rand(0,1);
            if($value == 1)
            {
                return true;
            }
            return false;
        }

        /**
         * Returns  a randomly generated phone number
         */
        public static function makeRandomPhoneNumber()
        {
            return mt_rand(200,899) . '-' . mt_rand(200,899) . '-' . mt_rand(1000,9999);
        }
    }
?>