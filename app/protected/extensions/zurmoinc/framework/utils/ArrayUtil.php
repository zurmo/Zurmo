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
     * Helper functionality for use in accessing and manipulating arrays.
     */
    class ArrayUtil
    {
        /**
         * Returns value of $array[$element] if $element is defined, otherwise if not defined will return null
         */
        public static function getArrayValue($array, $element)
        {
            if (isset($array[$element]))
            {
                return $array[$element];
            }
            return null;
        }

        public static function resolveArrayToLowerCase($array)
        {
            return unserialize(mb_strtolower(serialize($array)));
        }

        /**
         * Case insensitive version of @link http://www.php.net/manual/en/function.array-unique.php
         * @param array $array
         */
        public static function array_iunique($array)
        {
            return array_intersect_key($array, array_unique(array_map('strtolower', $array)));
        }
    }
?>
