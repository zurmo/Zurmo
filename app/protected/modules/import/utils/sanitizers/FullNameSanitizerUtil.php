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
     * Sanitizer for full name attributes.
     */
    class FullNameSanitizerUtil extends SanitizerUtil
    {
        public static function supportsSqlAttributeValuesDataAnalysis()
        {
            return false;
        }

        public static function getBatchAttributeValueDataAnalyzerType()
        {
            return 'FullName';
        }

        /**
         * If a full name is invalid, then skip the entire row during import.
         */
        public static function shouldNotSaveModelOnSanitizingValueFailure()
        {
            return true;
        }

        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            assert('$value != ""');
            assert('$mappingRuleData == null');
            if($value == null)
            {
                return $value;
            }
            list($firstName, $lastName) = explode(' ', trim($value));
            if($firstName == null)
            {
                $lastName  = $firstName;
                $firstName = null;
            }
            if($lastName == null)
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'The full name specified did not resolve to have a last name, which is required.'));
            }
            $model              = new $modelClassName(false);
            $firstNameMaxLength = StringValidatorHelper::getMaxLengthByModelAndAttributeName($model, 'firstName');
            $lastNameMaxLength  = StringValidatorHelper::getMaxLengthByModelAndAttributeName($model, 'lastName');
            if(strlen($lastName) > $lastNameMaxLength)
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'Last name specified is too large.'));
            }
            if($firstName != null && strlen($firstName) > $firstNameMaxLength)
            {
                throw new InvalidValueToSanitizeException(Yii::t('Default', 'First name specified is too large.'));
            }
        }
    }
?>