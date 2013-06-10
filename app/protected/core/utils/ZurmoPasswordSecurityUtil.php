<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for encrypting/decrypting passwords
     */
    class ZurmoPasswordSecurityUtil
    {
        /**
         * Encrypt value, using CSecurityManager::encrypt method
         * @param string $value
         * @param string $salt
         * @return string
         */
        public static function encrypt($value, $salt = ZURMO_PASSWORD_SALT)
        {
            if ($value == '' || $value == null)
            {
                return $value;
            }
            return base64_encode(Yii::app()->getSecurityManager()->encrypt($value, $salt));
        }

        /**
         * Decrypt value, using CSecurityManager::decrypt method
         * @param string $value
         * @param string $salt
         * @return mixed
         */
        public static function decrypt($value, $salt = ZURMO_PASSWORD_SALT)
        {
            if ($value == '' || $value == null)
            {
                return $value;
            }
            return Yii::app()->getSecurityManager()->decrypt(base64_decode($value), $salt);
        }

        /**
         * Generate zurmo password salt and write it to perInstance file.
         * @param $instanceRoot
         * @param string $perInstanceFilename
         * @return string
         */
        public static function setPasswordSaltAndWriteToPerInstanceFile($instanceRoot, $perInstanceFilename = 'perInstance.php')
        {
            assert('is_dir($instanceRoot)');

            if (!defined('ZURMO_PASSWORD_SALT') || ZURMO_PASSWORD_SALT == 'defaultValue')
            {
                $perInstanceConfigFile     = "$instanceRoot/protected/config/$perInstanceFilename";
                $contents = file_get_contents($perInstanceConfigFile);

                $passwordSalt = substr(md5(microtime() * mt_rand()), 0, 15);

                $contents = preg_replace('/define\(\'ZURMO_PASSWORD_SALT\', \'defaultValue\'\);/',
                    "define('ZURMO_PASSWORD_SALT', '$passwordSalt');",
                    $contents);

                file_put_contents($perInstanceConfigFile, $contents);
                return $passwordSalt;
            }
            return ZURMO_PASSWORD_SALT;
        }
    }
?>