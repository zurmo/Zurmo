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
     * Sanitizer for drop down attributes.
     */
    class DropDownSanitizerUtil extends SanitizerUtil
    {
        /**
         * Variable used to indicate a drop down value is missing from zurmo and will need to be added during import.
         * @var string
         */
        const ADD_MISSING_VALUE = 'Add missing value';

        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'DropDown';
        }

        /**
         * Override to support instructions for drop downs. An example is if there is a missing drop down value,
         * information is provided in the instructions explainnig whether to add the missing drop down, delete the value,
         * or merge the value into an existing drop down.
         */
        public static function supportsSanitizingWithInstructions()
        {
            return true;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'DropDown';
        }

        /**
         * Example of importInstructionsData
         * array('DropDown' => array(DropDownSanitizerUtil::ADD_MISSING_VALUE => array('neverPresent', 'notPresent')))
         * @param unknown_type $modelClassName
         * @param unknown_type $attributeName
         * @param unknown_type $value
         * @param unknown_type $mappingRuleData
         * @param unknown_type $importInstructionsData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData,
                                             $importInstructionsData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$value != ""');
            assert('$mappingRuleData == null');
            assert('is_array($importInstructionsData["DropDown"][DropDownSanitizerUtil::ADD_MISSING_VALUE]');
            if($value == null)
            {
                return $value;
            }
            $customFieldData                     = CustomFieldDataModelUtil::
                                                   getDataByModelClassNameAndAttributeName(
                                                   $modelClassName, $attributeName);
            $dropDownValues                      = unserialize($customFieldData->serializedData);
            $lowerCaseDropDownValues             = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
            //does the value already exist in the custom field data
            if(in_array(lower($value), $lowerCaseDropDownValues))
            {
                $keyToUse                        = array_search(lower($value), $lowerCaseDropDownValues);
                $resolvedValueToUse              = $dropDownValues[$keyToUse];
            }
            //if the value does not already exist, then check the instructions data.
            $lowerCaseValuesToAdd                = ArrayUtil::resolveArrayToLowerCase(
                                                   $importInstructionsData['DropDown']
                                                   [DropDownSanitizerUtil::ADD_MISSING_VALUE]);
            if(in_array(lower($value), $lowerCaseValuesToAdd))
            {
                $keyToAddAndUse                  = array_search(lower($value), $lowerCaseValuesToAdd);
                $resolvedValueToUse              = $importInstructionsData['DropDown']
                                                   [DropDownSanitizerUtil::ADD_MISSING_VALUE][$keyToAddAndUse];
                $unserializedData                = unserialize($customFieldData->serializedData);
                $unserializedData[]              = $resolvedValueToUse;
                $customFieldData->serializedData = serialize($unserializedData);
                assert('$customFieldData->saved()');
            }
            else
            {
                throw new InvalidValueToSanitizeException();
            }
            try
            {
                $customField        = new CustomField();
                $customField->value = $resolvedValueToUse;
                $customField->data  = $customFieldData;
            }
            catch(NotSupportedException $e)
            {
                throw new InvalidValueToSanitizeException();
            }
            return $customField;
        }
    }
?>