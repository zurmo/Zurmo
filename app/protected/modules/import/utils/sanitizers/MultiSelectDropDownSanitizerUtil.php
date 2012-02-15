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
     * Sanitizer for multi-select drop down attributes.
     */
    class MultiSelectDropDownSanitizerUtil extends DropDownSanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'MultiSelectDropDown';
        }

        /**
         * Given a value, resolve that the value is a valid custom field data value. If the value does not exist yet,
         * check the import instructions data to determine how to handle the missing value.
         *
         * Example of importInstructionsData
         * array('MultiSelectDropDown' => array(DropDownSanitizerUtil::ADD_MISSING_VALUE => array('neverPresent', 'notPresent')))
         *
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         * @param array $importInstructionsData
         */
        public static function sanitizeValueWithInstructions($modelClassName, $attributeName, $value, $mappingRuleData,
                                             $importInstructionsData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$mappingRuleData == null');
            assert('is_array($importInstructionsData["MultiSelectDropDown"][DropDownSanitizerUtil::ADD_MISSING_VALUE])');
            if ($value == null)
            {
                return $value;
            }
            $customFieldValues                   = static::getCustomFieldValuesFromValueString($value);
            $customFieldData                     = CustomFieldDataModelUtil::
                                                   getDataByModelClassNameAndAttributeName(
                                                   $modelClassName, $attributeName);
            $dropDownValues                      = unserialize($customFieldData->serializedData);
            $lowerCaseDropDownValues             = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
            $resolvedValuesToUse                 = array();
            foreach($customFieldValues as $aValue)
            {
                $generateMissingPickListError = false;
                //does the value already exist in the custom field data
                if (in_array(mb_strtolower($aValue), $lowerCaseDropDownValues))
                {
                    $keyToUse                        = array_search(mb_strtolower($aValue), $lowerCaseDropDownValues);
                    $resolvedValuesToUse[]           = $dropDownValues[$keyToUse];
                }
                else
                {
                    //if the value does not already exist, then check the instructions data.
                    $lowerCaseValuesToAdd                = ArrayUtil::resolveArrayToLowerCase(
                                                           $importInstructionsData['MultiSelectDropDown']
                                                           [DropDownSanitizerUtil::ADD_MISSING_VALUE]);
                    if (in_array(mb_strtolower($aValue), $lowerCaseValuesToAdd))
                    {
                        $keyToAddAndUse                  = array_search(mb_strtolower($aValue), $lowerCaseValuesToAdd);
                        $resolvedValueToUse              = $importInstructionsData['MultiSelectDropDown']
                                                           [DropDownSanitizerUtil::ADD_MISSING_VALUE][$keyToAddAndUse];
                        $unserializedData                = unserialize($customFieldData->serializedData);
                        $unserializedData[]              = $resolvedValueToUse;
                        $customFieldData->serializedData = serialize($unserializedData);
                        $saved                           = $customFieldData->save();
                        assert('$saved');
                    }
                    elseif (isset($importInstructionsData['MultiSelectDropDown'][DropDownSanitizerUtil::MAP_MISSING_VALUES]))
                    {
                        $resolvedValueToUse = static::processMissingValueToMapAndGetResolvedValueToUse(
                                              $aValue, $generateMissingPickListError, $importInstructionsData);
                    }
                    else
                    {
                         $generateMissingPickListError = true;
                    }
                    if ($generateMissingPickListError)
                    {
                        $message = 'Pick list value specified is missing from existing pick list and no valid instructions' .
                                   ' were provided on how to resolve this.';
                        throw new InvalidValueToSanitizeException(Yii::t('Default', $message));
                    }
                    $resolvedValuesToUse[] = $resolvedValueToUse;
                }
            }
            return static::makeOwnedMultiSelectCustomField($resolvedValuesToUse, $customFieldData);
        }

        public static function getCustomFieldValuesFromValueString($value)
        {
            assert('is_string($value)');
            $customFieldValues = explode(',', $value);
            foreach($customFieldValues as $key => $aValue)
            {

                if($aValue == null || trim($aValue) == '')
                {
                    unset($customFieldValues[$key]);
                }
                else
                {
                    $customFieldValues[$key] = trim($aValue);
                }
            }
            return $customFieldValues;
        }

        protected static function processMissingValueToMapAndGetResolvedValueToUse($aValue, & $generateMissingPickListError, $importInstructionsData)
        {
            assert('is_string($aValue)');
            assert('is_bool($generateMissingPickListError)');
            assert('is_array($importInstructionsData)');
            $lowerCaseMissingValuesToMap = ArrayUtil::resolveArrayToLowerCase(
                                               $importInstructionsData['MultiSelectDropDown']
                                               [DropDownSanitizerUtil::MAP_MISSING_VALUES]);
            if (isset($lowerCaseMissingValuesToMap[mb_strtolower($aValue)]))
            {
                $keyToUse           = array_search($lowerCaseMissingValuesToMap[mb_strtolower($aValue)],
                                                   $lowerCaseDropDownValues);
                if ($keyToUse === false)
                {
                    $message = 'Pick list value specified is missing from existing pick list, has a specified mapping value' .
                       ', but the mapping value is not a valid value.';
                    throw new InvalidValueToSanitizeException(Yii::t('Default', $message));
                }
                else
                {
                    return $dropDownValues[$keyToUse];
                }
            }
            else
            {
                $generateMissingPickListError = true;
            }
        }

        protected static function makeOwnedMultiSelectCustomField($resolvedValuesToUse, $customFieldData)
        {
            assert('is_array($resolvedValuesToUse)');
            assert('$customFieldData instanceof CustomFieldData');
            $customField        = new OwnedMultipleValuesCustomField();
            foreach($resolvedValuesToUse as $resolvedValueToUse)
            {
                $customFieldValue = new CustomFieldValue();
                $customFieldValue->value = $resolvedValueToUse;
                $customField->values->add($customFieldValue);
            }
            $customField->data  = $customFieldData;
            return $customField;
        }
    }
?>