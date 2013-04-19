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
    require_once 'PHPUnit/Runner/Version.php';
    class PhpUnitServiceUtil
    {
        // Installed version must be equal or higher then phpUnitMinimumVersion
        public static $phpUnitMinimumVersion = '3.5';

        // Installed version must be less then phpUnitMaximumVersion
        public static $phpUnitMaximumVersion = '3.71';

        public static function checkVersion()
        {
            try
            {
                $actualVersion = PHPUnit_Runner_Version::id();

                if (version_compare($actualVersion, self::$phpUnitMinimumVersion) < 0)
                {
                    echo "\n Zurmo tests are not working with PHPUnit {$actualVersion} \n";
                    echo "PHPUnit version must be equal and higher then PHPUnit " . self::$phpUnitMinimumVersion . " and ";
                    echo "lower then " .self::$phpUnitMaximumVersion . "\n";
                    echo "Please upgrade your PHPUnit version \n\n";
                    exit;
                }

                if (version_compare($actualVersion, self::$phpUnitMaximumVersion) >= 0)
                {
                    echo "\n Zurmo tests are not working with PHPUnit {$actualVersion} \n";
                    echo "PHPUnit version must be equal and higher then PHPUnit " . self::$phpUnitMinimumVersion . " and ";
                    echo "lower then " .self::$phpUnitMaximumVersion . "\n";
                    echo "Please downgrade your PHPUnit version \n\n";
                    exit;
                }
                return;
            }
            catch (Exception $e)
            {
                echo "You must install PHPUnit, before running tests";
                exit;
            }
        }
    }
?>
