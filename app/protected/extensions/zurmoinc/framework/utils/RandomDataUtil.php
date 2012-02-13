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
            if (!isset(self::$randomData[$modelClassName]))
            {
                $directoryName = $moduleClassName::getDirectoryName();
                $moduleName    = $moduleClassName::getPluralCamelCasedName();
                $filePath      = Yii::getPathOfAlias('application.modules.' . $directoryName . '.data.' .
                                 $modelClassName . 'RandomData') . '.php';
                if (file_exists($filePath))
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
            $value  = mt_rand(0, 1);
            if ($value == 1)
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
            return mt_rand(200, 899) . '-' . mt_rand(200, 899) . '-' . mt_rand(1000, 9999);
        }
    }
?>