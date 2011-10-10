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
     * Sanitizer for date time type attributes.
     */
    class DateTimeSanitizerUtil extends SanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'DateTime';
        }

        /**
         * @see DateTimeParser
         */
        public static function getAcceptableFormats()
        {
            return array(
                'yyyy-MM-dd hh:mm',
                'MM-dd-yyyy hh:mm',
                'dd-MM-yyyy hh:mm',
                'MM/dd/yyyy hh:mm'
            );
        }

        public static function getLinkedMappingRuleType()
        {
            return 'ValueFormat';
        }

        /**
         * Given a value, attempt to convert the value to a db datetime format based on the format provided.
         * If the value does not convert properly, meaning the value is not really in the format specified, then a
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
            assert('isset($mappingRuleData["format"])');
            if ($value == null)
            {
                return $value;
            }
            $sanitizedValue = CDateTimeParser::parse($value, $mappingRuleData['format']);
            if ($sanitizedValue === false || !is_int($sanitizedValue))
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'Invalid datetime format.'));
            }
            return DateTimeUtil::convertTimestampToDbFormatDateTime($sanitizedValue);
        }
    }
?>