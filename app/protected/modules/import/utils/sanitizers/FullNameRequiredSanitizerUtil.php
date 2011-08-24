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
     * Sanitizer for processing when a full name is required.  The part of the full name that is required is the
     * last name.  When the full name splits, if it is missing the last name part, then the entire full name value
     * is considered missing.
     */
    class FullNameRequiredSanitizerUtil extends RequiredSanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function supportsBatchAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getLinkedMappingRuleType()
        {
            return 'FullNameDefaultValueModelAttribute';
        }

        /**
         * Resolves that the value is not null or the value is null and a valid default value is available for the full
         * name. If not, then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            assert('$mappingRuleData["defaultValue"] == null || is_string($mappingRuleData["defaultValue"])');
            if($value == null)
            {
                if($mappingRuleData['defaultValue'] != '')
                {
                    $value = $mappingRuleData['defaultValue'];
                }
                else
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'A full name value is required but missing.'));
                }
            }
            return $value;
        }
    }
?>