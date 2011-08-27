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
     * Sanitizer for boolean type attributes.
     */
    class BooleanSanitizerUtil extends SanitizerUtil
    {
        public static function getSqlAttributeValueDataAnalyzerType()
        {
            return 'Boolean';
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'Boolean';
        }

        /**
         * THE KEYS MUST MATCH THE KEYS IN getAcceptableValues() to have the correct mapping
         * This method and getAcceptableValues() to be split because of some type casting
         * issues in php where the keys were getting turned into integers instead of remaining as strings.
         * Lowercase array of mappable boolean values. These values if found in an import, will be converted correctly
         * to false/true. All other values are not valid.
         * @see getAcceptableValues()
         */
        public static function getAcceptableValuesResolvingValues()
        {
            return array(
                0 => false,
                1 => true,
                2 => true,
                3 => false,
                4 => true,
                5 => false,
                6 => false,
                7 => true,
                8 => false,
            );
        }

        /**
         * THE KEYS MUST MATCH THE KEYS IN getAcceptableValuesResolvingValues() to have the correct mapping
         * This method and getAcceptableValuesResolvingValue() needed to be split because of some type casting
         * issues in php where the keys were getting turned into integers instead of remaining as strings.
         * @see getAcceptableValuesResolvingValues()
         */
        public static function getAcceptableValues()
        {
            return array(
                0 => 'false',
                1 => 'true',
                2 => 'y',
                3 => 'n',
                4 => 'yes',
                5 => 'no',
                6 => '0',
                7 => '1',
                8 => '',
            );
        }

        public static function getLinkedMappingRuleType()
        {
            return 'DefaultValueModelAttribute';
        }

        /**
         * Given a value, attempt to convert the value to either true/false based on a mapping array of possible
         * boolean values.  If the value is not present, attemp to utilize the default value specified.
         * If the value presented is not a valid mapping value then a
         * InvalidValueToSanitizeException will be thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeName)');
            assert('$mappingRuleData["defaultValue"] == null || $mappingRuleData["defaultValue"] == "1" ||
                    $mappingRuleData["defaultValue"] == "0"');
            $acceptableValues = BooleanSanitizerUtil::getAcceptableValues();
            $acceptableValuesResolvingValues = BooleanSanitizerUtil::getAcceptableValuesResolvingValues();
            if ($value == null)
            {
                if ($mappingRuleData['defaultValue'] != null)
                {
                    $key = array_search($mappingRuleData['defaultValue'], $acceptableValues);
                    return $acceptableValuesResolvingValues[$mappingRuleData['defaultValue']];
                }
                else
                {
                    return $value;
                }
            }
            if (!in_array(strtolower($value), $acceptableValues))
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'Invalid check box format.'));
            }
            else
            {
                $key = array_search(strtolower($value), $acceptableValues);
                return $acceptableValuesResolvingValues[$key];
            }
        }
    }
?>