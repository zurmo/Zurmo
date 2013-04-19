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

        public static function getArrayValueWithExceptionIfNotFound($array, $element)
        {
            if (!array_key_exists($element, $array))
            {
                throw new NotSupportedException($element . " does not exist.");
            }
            else
            {
                return static::getArrayValue($array, $element);
            }
        }

        public static function resolveArrayToLowerCase($array)
        {
            return unserialize(TextUtil::strToLowerWithDefaultEncoding(serialize($array)));
        }

        /**
         * Case insensitive version of @link http://www.php.net/manual/en/function.array-unique.php
         * @param array $array
         */
        public static function array_iunique($array)
        {
            return array_intersect_key($array, array_unique(array_map('strtolower', $array)));
        }

        /**
         * Given an array, stringify the array values into content seperated by commas and return the content.
         * @param array $data
         */
        public static function stringify($data)
        {
            assert('is_array($data)');
            $s             = null;
            foreach ($data as $value)
            {
                if ($s != null)
                {
                    $s .= ', ';
                }
                $s .= $value;
            }
            return $s;
        }

        /**
         * Convert multi-dimenision array into flat(one dimension) array
         */
        public static function flatten($array)
        {
            $flatternArray = array();
            foreach ($array as $element)
            {
                if (is_array($element))
                {
                    $flatternArray = array_merge($flatternArray, self::flatten($element));
                }
                else
                {
                    $flatternArray[] = $element;
                }
            }
            return $flatternArray;
        }

        /**
         * Pass an array in to sort by an element's value.
         * @param Array $array
         * @param Mixed $subKey (integer or string)
         * @param string $sortFunctionName
         * @return sorted array or empty array if nothing to sort
         */
        public static function subValueSort($array, $subKey, $sortFunctionName)
        {
            assert('$sortFunctionName == "sort" || $sortFunctionName == "asort"');
            $newArray = array();
            foreach ($array as $key => $value)
            {
                $newArray[$key] = strtolower($value[$subKey]);
            }
            if (!empty($newArray))
            {
                $sortFunctionName($newArray);
                $finalArray = array();
                foreach ($newArray as $newKey => $unused)
                {
                    $finalArray[$newKey] = $array[$newKey];
                }
                return $finalArray;
            }
            return array();
        }

        public static function arrayUniqueRecursive($array)
        {
            $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

            foreach ($result as $key => $value)
            {
                if (is_array($value))
                {
                    $result[$key] = ArrayUtil::arrayUniqueRecursive($value);
                }
            }
            return $result;
        }
    }
?>
