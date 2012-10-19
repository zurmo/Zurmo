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
     * Helper functionality to convert POST/GET
     * search information into variables and arrays
     * that the RedBeanDataProvider will accept.
     */
    class SearchUtil
    {
        /**
         * Get the search attributes array by resolving the GET array
         * for the information.  Remove any attributes from the array that are not searchable form attributes
         */
        public static function resolveSearchAttributesFromGetArray($getArrayName, $formModelClassName)
        {
            assert('is_string($getArrayName)');
            assert('is_string($formModelClassName) && is_subclass_of($formModelClassName, "SearchForm")');
            $searchAttributes = array();
            if (!empty($_GET[$getArrayName]))
            {
                $searchAttributes = SearchUtil::getSearchAttributesFromSearchArray($_GET[$getArrayName]);
                foreach ($formModelClassName::getNonSearchableAttributes() as $attribute)
                {
                    if (isset($searchAttributes[$attribute]) ||
                        key_exists($attribute, $searchAttributes))
                    {
                        unset($searchAttributes[$attribute]);
                    }
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
            if (!empty($_GET[$getArrayName]) && isset($_GET[$getArrayName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]))
            {
                assert('$searchModel instanceof SearchForm');
                if (!is_array($_GET[$getArrayName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]))
                {
                    $sanitizedAnyMixedAttributesScope = null;
                }
                elseif (count($_GET[$getArrayName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME]) == 1 &&
                       $_GET[$getArrayName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME][0] == 'All')
                {
                    $sanitizedAnyMixedAttributesScope = null;
                }
                else
                {
                    $sanitizedAnyMixedAttributesScope = $_GET[$getArrayName][SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME];
                }
                $searchModel->setAnyMixedAttributesScope($sanitizedAnyMixedAttributesScope);
            }
        }

        /**
         * From the get array, if the selectedListAttributes variable is present, retrieve and set into the
         * $searchModel.
         * @param object $searchModel
         * @param string $getArrayName
         */
        public static function resolveSelectedListAttributesForSearchModelFromGetArray($searchModel, $getArrayName)
        {
            assert('$searchModel instanceof RedBeanModel || $searchModel instanceof ModelForm');
            assert('is_string($getArrayName)');
            if ($searchModel->getListAttributesSelector() != null &&
                !empty($_GET[$getArrayName]) &&
                isset($_GET[$getArrayName][SearchForm::SELECTED_LIST_ATTRIBUTES]))
            {
                assert('$searchModel instanceof SearchForm');
                if (!is_array($_GET[$getArrayName][SearchForm::SELECTED_LIST_ATTRIBUTES]))
                {
                    $sanitizedListAttributes = null;
                }
                else
                {
                    $sanitizedListAttributes = $_GET[$getArrayName][SearchForm::SELECTED_LIST_ATTRIBUTES];
                }
                $searchModel->getListAttributesSelector()->setSelected($sanitizedListAttributes);
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
            assert('$searchArray != null');
            array_walk_recursive($searchArray, 'SearchUtil::changeEmptyValueToNull');
            self::changeEmptyArrayValuesToNull($searchArray);
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
         * if a value is an array, and the array has an element that is empty, remove it.
         * @see getSearchAttributesFromSearchArray
         */
        private static function changeEmptyArrayValuesToNull(& $searchArray)
        {
            $keysToUnset = array();
            foreach ($searchArray as $key => $value)
            {
                if (is_array($value) && isset($value['values']) && is_array($value['values']))
                {
                    foreach ($value['values'] as $subKey => $subValue)
                    {
                        if ($subValue == null)
                        {
                            unset($searchArray[$key]['values'][$subKey]);
                            $searchArray[$key]['values'] = array_values($searchArray[$key]['values']);
                        }
                    }
                    if (count($searchArray[$key]) == 1 && count($searchArray[$key]['values']) == 0)
                    {
                        $keysToUnset[] = $key;
                    }
                }
                if (is_array($value) && isset($value['value']) && is_array($value['value']))
                {
                    foreach ($value['value'] as $subKey => $subValue)
                    {
                        if ($subValue == null)
                        {
                            unset($searchArray[$key]['value'][$subKey]);
                            $searchArray[$key]['value'] = array_values($searchArray[$key]['value']);
                        }
                    }
                    if (count($searchArray[$key]) == 1 && count($searchArray[$key]['value']) == 0)
                    {
                        $keysToUnset[] = $key;
                    }
                }
                elseif (is_array($value))
                {
                    self::changeEmptyArrayValuesToNull($searchArray[$key]);
                }
            }
            foreach ($keysToUnset as $key)
            {
                unset($searchArray[$key]);
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
            self::changeEmptyArrayValuesToNull($searchArray);
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

        /**
         * @param string $getArrayName
         */
        public static function getDynamicSearchAttributesFromGetArray($getArrayName)
        {
            assert('is_string($getArrayName)');
            if (!empty($_GET[$getArrayName]) &&
                isset($_GET[$getArrayName][DynamicSearchForm::DYNAMIC_NAME]))
            {
                $dynamicSearchAttributes = SearchUtil::getSearchAttributesFromSearchArray($_GET[$getArrayName][DynamicSearchForm::DYNAMIC_NAME]);
                if (isset($dynamicSearchAttributes[DynamicSearchForm::DYNAMIC_STRUCTURE_NAME]))
                {
                    unset($dynamicSearchAttributes[DynamicSearchForm::DYNAMIC_STRUCTURE_NAME]);
                }
                foreach ($dynamicSearchAttributes as $key => $data)
                {
                    if (is_string($data) && $data == 'undefined')
                    {
                        unset($dynamicSearchAttributes[$key]);
                    }
                }
                return $dynamicSearchAttributes;
            }
        }

        /**
         * @param object DynamicSearchForm $searchModel
         * @param array $dynamicSearchAttributes
         */
        public static function sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel(DynamicSearchForm $searchModel,
                                                                                           $dynamicSearchAttributes)
        {
            assert('is_array($dynamicSearchAttributes)');
            $sanitizedDynamicSearchAttributes = array();
            foreach ($dynamicSearchAttributes as $key => $searchAttributeData)
            {
                $attributeIndexOrDerivedType = $searchAttributeData['attributeIndexOrDerivedType'];
                $structurePosition           = $searchAttributeData['structurePosition'];
                unset($searchAttributeData['attributeIndexOrDerivedType']);
                unset($searchAttributeData['structurePosition']);
                self::processDynamicSearchAttributesDataForSavingModelRecursively($searchModel, $searchAttributeData);
                $sanitizedDynamicSearchAttributes[$key] = $searchAttributeData;
                $sanitizedDynamicSearchAttributes[$key]['attributeIndexOrDerivedType'] = $attributeIndexOrDerivedType;
                $sanitizedDynamicSearchAttributes[$key]['structurePosition']           = $structurePosition;
            }
            return $sanitizedDynamicSearchAttributes;
        }

        protected static function processDynamicSearchAttributesDataForSavingModelRecursively($searchModel, & $searchAttributeData)
        {
            $processRecursively = false;
            foreach ($searchAttributeData as $attributeName => $attributeData)
            {
                if ( isset($attributeData['relatedModelData']) &&
                    is_array($attributeData) &&
                    $attributeData['relatedModelData'] == true)
                {
                    assert('count($attributeData) == 2');
                    $processRecursively = true;
                    break;
                }
            }
            if ($processRecursively)
            {
                $modelToUse      = self::resolveModelToUseByModelAndAttributeName($searchModel, $attributeName);
                self::processDynamicSearchAttributesDataForSavingModelRecursively($modelToUse,
                                                                                 $searchAttributeData[$attributeName]);
            }
            else
            {
                $searchAttributeData = GetUtil::sanitizePostByDesignerTypeForSavingModel($searchModel, $searchAttributeData);
            }
        }

        /**
         * Given a model and an attribute that is a relation, ascertain the correct model to use.  If a search form
         * model is available then use that otherwise use the appropriate related model.
         * @param object $model SearchForm or RedBeanModel
         * @param string $attributeName
         */
        public static function resolveModelToUseByModelAndAttributeName($model, $attributeName)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($attributeName)');
            $modelToUse      = SearchDataProviderMetadataAdapter::resolveAsRedBeanModel($model->$attributeName);
            $moduleClassName = $modelToUse->getModuleClassName();
            if ($moduleClassName != null)
            {
                $formClassName   = $moduleClassName::getGlobalSearchFormClassName();
                if ($formClassName != null)
                {
                    $modelToUse = new $formClassName($modelToUse);
                }
            }
            return $modelToUse;
        }

        /**
         * @param string $getArrayName
         */
        public static function getDynamicSearchStructureFromGetArray($getArrayName)
        {
            assert('is_string($getArrayName)');
            if (!empty($_GET[$getArrayName]) &&
                isset($_GET[$getArrayName][DynamicSearchForm::DYNAMIC_STRUCTURE_NAME]))
            {
                return $_GET[$getArrayName][DynamicSearchForm::DYNAMIC_STRUCTURE_NAME];
            }
        }
    }
?>