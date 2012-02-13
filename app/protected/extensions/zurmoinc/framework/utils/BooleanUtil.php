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
     * Helper functionality to convert booleans.
     */
    class BooleanUtil
    {
        /**
         * Converts a variable to bool. If it
         * is a string 'true' is true.
         */
        public static function boolVal($var)
        {
            return  is_string($var) && strtolower($var) == 'true' ||
                   !is_string($var) && (bool)$var;
        }

        /**
         * Converts a variable to an integer true or false as 1 and 0.
         * If it is a string 'true' is true.
         */
        public static function boolIntVal($var)
        {
            return intval(self::boolVal($var));
        }

        /**
         * Converts a boolean to a string as 'true' or 'false'.
         */
        public static function boolToString($var)
        {
            assert('is_bool($var)');
            if ($var)
            {
                return 'true';
            }
            else
            {
                return 'false';
            }
        }

        /**
         * Converts an integer true or false (1 or 0) to a string as 'true' or 'false'.
         */
        public static function boolIntToString($var)
        {
            assert('is_int($var) && ($var == 1 || $var == 0)');
            return self::boolToString(self::boolVal($var));
        }
    }
?>
