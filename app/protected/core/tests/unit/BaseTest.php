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

    class BaseTest extends PHPUnit_Framework_TestCase
    {
        public static $activateDefaultLanguages = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            global $freeze;
            if ($freeze)
            {
                $schemaFile = sys_get_temp_dir() . '/autobuilt.sql';
                $success = preg_match("/;dbname=([^;]+)/", Yii::app()->db->connectionString, $matches); // Not Coding Standard
                assert('$success == 1'); // Not Coding Standard
                $databaseName = $matches[1];
                if (file_exists($schemaFile) && filesize($schemaFile) > 0)
                {
                    system('mysql -u' . Yii::app()->db->username .
                                ' -p' . Yii::app()->db->password .
                                  ' ' . $databaseName            .
                           " < $schemaFile");
                }
            }
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password);
            assert('RedBeanDatabase::isSetup()'); // Not Coding Standard
            CustomFieldData::forgetAllPhpCache();
            GeneralCache::forgetAll();
            BeanModelCache::forgetAll();
            if ($freeze)
            {
                RedBeanDatabase::freeze();
                TestDatabaseUtil::deleteRowsFromAllTablesExceptLog();
            }
            else
            {
                TestDatabaseUtil::deleteAllTablesExceptLog();
            }
            Yii::app()->user->userModel = null;
            Yii::app()->user->clearStates(); //reset session.
            Yii::app()->language        = Yii::app()->getConfigLanguageValue();
            Yii::app()->timeZoneHelper->setTimeZone(Yii::app()->getConfigTimeZoneValue());
            Yii::app()->timeZoneHelper->load(); //resets timezone
            Yii::app()->languageHelper->flushModuleLabelTranslationParameters();
            if (static::$activateDefaultLanguages)
            {
                Yii::app()->languageHelper->load();
                Yii::app()->languageHelper->activateLanguagesForTesting();
                Yii::app()->languageHelper->importMessagesForTesting();
            }
        }

        public static function tearDownAfterClass()
        {
            if (static::$activateDefaultLanguages)
            {
                Yii::app()->languageHelper->deactivateLanguagesForTesting();
            }

            if (RedBeanDatabase::isFrozen())
            {
                TestDatabaseUtil::deleteRowsFromAllTablesExceptLog();
            }
            else
            {
                TestDatabaseUtil::deleteAllTablesExceptLog();
            }

            PermissionsCache::forgetAll();
            RedBeanModel::forgetAll();
            RedBeanDatabase::close();
            assert('!RedBeanDatabase::isSetup()'); // Not Coding Standard
            GeneralCache::forgetAll();
            BeanModelCache::forgetAll();
        }

        public static function resetAndPopulateFilesArrayByFilePathAndName($arrayName, $filePath, $fileName)
        {
            assert('is_string($arrayName) && $arrayName != ""'); // Not Coding Standard
            assert('is_string($filePath)  && $filePath  != ""'); // Not Coding Standard
            assert('is_string($fileName)  && $fileName  != ""'); // Not Coding Standard
            $_FILES = null;
            CUploadedFile::reset();
            $_FILES = array($arrayName => array('name'     => $fileName, 'type'  => ZurmoFileHelper::getMimeType($filePath),
                                                'tmp_name' => $filePath, 'error' => UPLOAD_ERR_OK,
                                                'size'     => filesize($filePath)));
        }

        public function setup()
        {
        }

        public function teardown()
        {
            Yii::app()->user->userModel = null;
        }

        protected function assertWithinTolerance($expected, $actual, $plusMinus)
        {
            if (abs($actual - $expected) > $plusMinus)
            {
                $this->fail("Actual $actual not within +/- $plusMinus of expected $expected.");
            }
        }

        protected function assertWithinPercentage($expected, $actual, $percentage)
        {
            $ratio = $actual/$expected;
            if ($ratio < (1 - $percentage/100) || $ratio > (1 + $percentage/100))
            {
                $this->fail("Actual $actual not within +/- $percentage% of expected $expected.");
            }
        }

        protected function isDebug()
        {
            return in_array('--debug', $_SERVER['argv']);
        }

        /**
         * Get the value of a property using reflection.
         *
         * @param object|string $class
         *     The object or classname to reflect. An object must be provided
         *     if accessing a non-static property.
         * @param string $propertyName The property to reflect.
         * @return mixed The value of the reflected property.
         */
        public static function getReflectedPropertyValue($object, $propertyName)
        {
            assert('is_object($object)'); // Not Coding Standard
            $reflectedClass = new ReflectionClass($object);
            $property       = $reflectedClass->getProperty($propertyName);
            $property->setAccessible(true);
            return $property->getValue($object);
        }
    }
?>
