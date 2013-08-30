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
     * Sanitizer for multi-select drop down attributes.
     */
    class MultiSelectDropDownSanitizerUtil extends DropDownSanitizerUtil
    {
        /**
         * @param RedBean_OODBBean $rowBean
         */
        public function analyzeByRow(RedBean_OODBBean $rowBean)
        {
            if ($rowBean->{$this->columnName} != null)
            {
                $customFieldData         = CustomFieldDataModelUtil::getDataByModelClassNameAndAttributeName(
                                           $this->modelClassName, $this->attributeName);
                $lowerCaseDropDownValues = ArrayUtil::resolveArrayToLowerCase(unserialize($customFieldData->serializedData));
                $customFieldValues       = MultiSelectDropDownSanitizerUtil::getCustomFieldValuesFromValueString(
                                           $rowBean->{$this->columnName});
                foreach ($customFieldValues as $aValue)
                {
                    if (!in_array(strtolower($aValue), $lowerCaseDropDownValues))
                    {
                        $label = Zurmo::t('ImportModule', '{value} is new. This value will be added upon import.',
                                          array('{value}' => $aValue));
                        $this->analysisMessages[]         = $label;
                        $this->missingCustomFieldValues[] = $aValue;
                    }
                }
            }
        }

        /**
         * Given a value, resolve that the value is a valid custom field data value. If the value does not exist yet,
         * check the import instructions data to determine how to handle the missing value.
         *
         * Example of customFieldsInstructionData
         * array(array(CustomFieldsInstructionData::ADD_MISSING_VALUES => array('neverPresent', 'notPresent'))
         *
         * @param mixed $value
         * @return sanitized value
         * @throws InvalidValueToSanitizeException
         */
        public function sanitizeValue($value)
        {
            assert('$this->mappingRuleData == null');
            $customFieldsInstructionData = $this->getCustomFieldsInstructionDataFromColumnMappingData();
            if (!isset($customFieldsInstructionData[CustomFieldsInstructionData::ADD_MISSING_VALUES]))
            {
                $customFieldsInstructionData[CustomFieldsInstructionData::ADD_MISSING_VALUES] = array();
            }
            if ($value == null)
            {
                return $value;
            }
            $customFieldValues                   = static::getCustomFieldValuesFromValueString($value);
            $customFieldData                     = CustomFieldDataModelUtil::
                                                   getDataByModelClassNameAndAttributeName(
                                                   $this->modelClassName, $this->attributeName);
            $dropDownValues                      = unserialize($customFieldData->serializedData);
            $lowerCaseDropDownValues             = ArrayUtil::resolveArrayToLowerCase($dropDownValues);
            $resolvedValuesToUse                 = array();
            foreach ($customFieldValues as $aValue)
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
                                                           $customFieldsInstructionData
                                                           [CustomFieldsInstructionData::ADD_MISSING_VALUES]);
                    if (in_array(mb_strtolower($aValue), $lowerCaseValuesToAdd))
                    {
                        $keyToAddAndUse                  = array_search(mb_strtolower($aValue), $lowerCaseValuesToAdd);
                        $resolvedValueToUse              = $customFieldsInstructionData
                                                           [CustomFieldsInstructionData::ADD_MISSING_VALUES][$keyToAddAndUse];
                        $unserializedData                = unserialize($customFieldData->serializedData);
                        $unserializedData[]              = $resolvedValueToUse;
                        $customFieldData->serializedData = serialize($unserializedData);
                        $saved                           = $customFieldData->save();
                        assert('$saved');
                    }
                    elseif (isset($customFieldsInstructionData[CustomFieldsInstructionData::MAP_MISSING_VALUES]))
                    {
                        $resolvedValueToUse = static::processMissingValueToMapAndGetResolvedValueToUse(
                                              $aValue, $generateMissingPickListError, $customFieldsInstructionData,
                                              $dropDownValues, $lowerCaseDropDownValues);
                    }
                    else
                    {
                         $generateMissingPickListError = true;
                    }
                    if ($generateMissingPickListError)
                    {
                        $message = 'Pick list value specified is missing from existing pick list and no valid instructions' .
                                   ' were provided on how to resolve this.';
                        throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', $message));
                    }
                    $resolvedValuesToUse[] = $resolvedValueToUse;
                }
            }
            return static::makeOwnedMultiSelectCustomField($resolvedValuesToUse, $customFieldData);
        }

        /**
         * @param string $value
         * @return array
         */
        public static function getCustomFieldValuesFromValueString($value)
        {
            assert('is_string($value)');
            $customFieldValues = explode(',', $value); // Not Coding Standard
            foreach ($customFieldValues as $key => $aValue)
            {
                if ($aValue == null || trim($aValue) == '')
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

        protected static function processMissingValueToMapAndGetResolvedValueToUse($aValue,
                                                                                   & $generateMissingPickListError,
                                                                                   array $customFieldsInstructionData,
                                                                                   array $dropDownValues,
                                                                                   array $lowerCaseDropDownValues)
        {
            assert('is_string($aValue)');
            assert('is_bool($generateMissingPickListError)');
            $lowerCaseMissingValuesToMap = ArrayUtil::resolveArrayToLowerCase($customFieldsInstructionData
                                           [CustomFieldsInstructionData::MAP_MISSING_VALUES]);
            if (isset($lowerCaseMissingValuesToMap[mb_strtolower($aValue)]))
            {
                $keyToUse           = array_search($lowerCaseMissingValuesToMap[mb_strtolower($aValue)],
                                                   $lowerCaseDropDownValues);
                if ($keyToUse === false)
                {
                    $message = 'Pick list value specified is missing from existing pick list, has a specified mapping value' .
                       ', but the mapping value is not a valid value.';
                    throw new InvalidValueToSanitizeException(Zurmo::t('ImportModule', $message));
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
            foreach ($resolvedValuesToUse as $resolvedValueToUse)
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