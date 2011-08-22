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
     * Sanitizer for attributes that are derived model types. This means the specific relation is not on the model,
     * but typically this is a casted down version of the actual relation on the model.
     */
    abstract class ModelDerivedIdValueTypeSanitizerUtil extends IdValueTypeSanitizerUtil
    {
        protected static function getDerivedModelClassName()
        {
            throw new NotImplementedException();
        }

        public static function makeSqlAttributeValueDataAnalyzer($modelClassName, $attributeName)
        {
            throw new NotImplementedException();
        }

        public static function makeBatchAttributeValueDataAnalyzer($modelClassName, $attributeName)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            $batchAttributeValueDataAnalyzerType = static::getBatchAttributeValueDataAnalyzerType();
            if($batchAttributeValueDataAnalyzerType == null)
            {
                throw new NotSupportedException();
            }
            $batchAttributeValueDataAnalyzerClassName = $batchAttributeValueDataAnalyzerType .
                                                        'BatchAttributeValueDataAnalyzer';
            return new $batchAttributeValueDataAnalyzerClassName(static::getDerivedModelClassName(), 'id');
        }

        /**
         * Given a value that is either a zurmo id or an external system id, resolve that the
         * value is valid.  If the value is not valid then an InvalidValueToSanitizeException is thrown.
         * @param string $modelClassName
         * @param string $attributeName
         * @param mixed $value
         * @param array $mappingRuleData
         */
        public static function sanitizeValue($modelClassName, $attributeName, $value, $mappingRuleData)
        {
            assert('is_string($modelClassName)');
            assert('$attributeName == null');
            assert('$value != ""');
            assert('$mappingRuleData["type"] == IdValueTypeMappingRuleForm::ZURMO_MODEL_ID ||
                    $mappingRuleData["type"] == IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID');
            $derivedModelClassName = $this->getDerivedModelClassName();
            if($value == null)
            {
                return $value;
            }
            if($mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::ZURMO_MODEL_ID)
            {
                try
                {
                    return $derivedModelClassName::getById($value);
                }
                catch(NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The id specified did not match any existing records.'));
                }
            }
            elseif($mappingRuleData["type"] == UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_ID)
            {
                try
                {
                    return static::getModelByExternalSystemIdAndModelClassName($value, $derivedModelClassName);
                }
                catch(NotFoundException $e)
                {
                    throw new InvalidValueToSanitizeException(Yii::t('Default', 'The other id specified did not match any existing records.'));
                }
            }
        }
    }
?>