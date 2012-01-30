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
     * Helper functionality to convert POST/GET
     * search information into variables and arrays
     * that the RedBeanDataProvider will accept.
     */
    class SearchUtil
    {
        const ANY_MIXED_ATTRIBUTES_SCOPE_NAME = 'anyMixedAttributesScope';

        /**
         * Get the search attributes array by resolving the GET array
         * for the information. If the self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME is set, remove that
         * from the array since this is utilized only directly from the $_GET string.
         */
        public static function resolveSearchAttributesFromGetArray($getArrayName)
        {
            assert('is_string($getArrayName)');
            $searchAttributes = array();
            if (!empty($_GET[$getArrayName]))
            {
                $searchAttributes = SearchUtil::getSearchAttributesFromSearchArray($_GET[$getArrayName]);
                if(isset($searchAttributes[self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]) ||
                    key_exists(self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME, $searchAttributes))
                {
                    unset($searchAttributes[self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]);
                }
            }
            return $searchAttributes;
        }

        /**
         * From the get array, if the anyMixedAttributeScope variable is present, retrieve and set into the
         * $searchModel.  If the value is 'All', then set into the SearchModel a value of null since this
         * means there is no scoping.
         * @param object $searchModel
         * @param string $getArrayName
         */
        public static function resolveAnyMixedAttributesScopeForSearchModelFromGetArray($searchModel, $getArrayName)
        {
            assert('$searchModel instanceof RedBeanModel || $searchModel instanceof ModelForm');
            assert('is_string($getArrayName)');
            $searchAttributes  = array();
            if (!empty($_GET[$getArrayName]) && isset($_GET[$getArrayName][self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]))
            {
                assert('$searchModel instanceof SearchForm');
                if(!is_array($_GET[$getArrayName][self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]))
                {
                    $sanitizedAnyMixedAttributesScope = null;
                }
                elseif(count($_GET[$getArrayName][self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]) == 1 &&
                       $_GET[$getArrayName][self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME][0] == 'All')
                {
                    $sanitizedAnyMixedAttributesScope = null;
                }
                else
                {
                    $sanitizedAnyMixedAttributesScope = $_GET[$getArrayName][self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME];
                }
                $searchModel->setAnyMixedAttributesScope($sanitizedAnyMixedAttributesScope);
            }
        }

        /**
         * Get the sort attribute array by resolving the GET array
         * for the information.
         */
        public static function resolveSortAttributeFromGetArray($getArrayPrefixName)
        {
            $sortAttribute = null;
            if (!empty($_GET[$getArrayPrefixName . '_sort']))
            {
                $sortAttribute = SearchUtil::getSortAttributeFromSortString($_GET[$getArrayPrefixName . '_sort']);
            }
            return $sortAttribute;
        }

        /**
         * Get the sort descending array by resolving the GET array
         * for the information.
         */
        public static function resolveSortDescendingFromGetArray($getArrayPrefixName)
        {
            $sortDescending = false;
            if (!empty($_GET[$getArrayPrefixName . '_sort']))
            {
                $sortDescending = SearchUtil::isSortDescending($_GET[$getArrayPrefixName . '_sort']);
            }
            return $sortDescending;
        }

        /**
         * Convert incoming sort array into the sortAttribute part
         * Examples: 'name.desc'  'officeFax'
         */
        public static function getSortAttributeFromSortString($sortString)
        {
            $sortInformation = explode(".", $sortString);
            if ( count($sortInformation) == 2)
            {
                $sortAttribute = $sortInformation[0];
            }
            elseif ( count($sortInformation) == 1)
            {
                $sortAttribute = $sortInformation[0];
            }
            return $sortAttribute;
        }

        /**
         * Find out if the sort should be descending
         */
        public static function isSortDescending($sortString)
        {
            $sortInformation = explode(".", $sortString);
            if (count($sortInformation) == 2)
            {
                if ($sortInformation[1] == 'desc')
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * Convert search array into RedBeanDataProvider ready
         * array. Primary purpose is to set null any 'empty', but
         * set element in the array.
         */
        public static function getSearchAttributesFromSearchArray($searchArray)
        {
            array_walk_recursive($searchArray, 'SearchUtil::changeEmptyValueToNull');
            return $searchArray;
        }

        /**
         * if a value is empty, then change it to null
         * @see getSearchAttributesFromSearchArray
         */
        private static function changeEmptyValueToNull(&$value, $key)
        {
            if (empty($value) && $value !== '0')
            {
                $value = null;
            }
        }

        /**
         * Convert search array into a savable array of searchAttributes. If you want to resolve search attributes
         * to be used in the RedBeanDataProvider then use @see getSearchAttributesFromSearchArray
         * array. Primary purpose is to set null any 'empty', except for '0' values as '0' values mean that 'No' was
         * specfically specified for a boolean value for example.
         */
        public static function getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria($searchArray)
        {
            array_walk_recursive($searchArray, 'SearchUtil::changeEmptyValueToNullExceptNumeric');
            return $searchArray;
        }

        /**
         * if a value is empty, then change it to null, except 0 values or '0' which will retain its value.
         * @see getSearchAttributesFromSearchArrayForSavingExistingSearchCriteria
         */
        private static function changeEmptyValueToNullExceptNumeric(&$value, $key)
        {
            if (empty($value) && !is_numeric($value))
            {
                $value = null;
            }
        }

        public static function adaptSearchAttributesToSetInRedBeanModel($searchAttributes, $model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            $searchAttributesReadyToSetToModel = array();
            if ($model instanceof SearchForm)
            {
                $modelToUse =  $model->getModel();
            }
            else
            {
                $modelToUse =  $model;
            }
            foreach ($searchAttributes as $attributeName => $data)
            {
                if ($modelToUse->isAttribute($attributeName))
                {
                    $type = ModelAttributeToMixedTypeUtil::getType($modelToUse, $attributeName);
                    switch($type)
                    {
                        case 'CheckBox':

                            if (is_array($data) && isset($data['value']))
                            {
                                $data = $data['value'];
                            }
                            elseif (is_array($data) && $data['value'] == null)
                            {
                                $data = null;
                            }
                        default :
                            continue;
                    }
                }
                $searchAttributesReadyToSetToModel[$attributeName] = $data;
            }
            return $searchAttributesReadyToSetToModel;
        }
    }
?>