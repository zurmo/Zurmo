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
     * Import rules for any attributes that are id. This would be referencing the model id.
     */
    class IdAttributeImportRules extends NonDerivedAttributeImportRules
    {
        public function __construct($model, $attributeName)
        {
            parent::__construct($model, $attributeName);
            assert('$attributeName == "id"');
        }

        protected static function getImportColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array('IdValueType'   => 'ImportMappingModelIdValueTypeDropDown');
        }

        public function getDisplayLabel()
        {
            return $model::getModelLabelByTypeAndLanguage('Singular') . ' ' .Yii::t('Default', 'Id');
        }

        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            return array('SelfIdValueType');
        }

        public function resolveValueForImport($value, $columnMappingData, & $shouldSaveModel)
        {
            assert('is_array($columnMappingData)');
            assert('is_bool($shouldSaveModel)');
            $sanitizerUtilTypes = static::getSanitizerUtilTypesInProcessingOrder();
            if(count($sanitizerUtilTypes) == 1 && $sanitizerUtilTypes[0] == 'SelfIdValueType')
            {
                $modelClassName =$this->getModelClassName();
                try
                {
                    $value  = ImportSanitizerUtil::
                              sanitizeValueBySanitizerTypes(static::getSanitizerUtilTypesInProcessingOrder(),
                                                            $this->getModelClassName(),
                                                            $this->getModelAttributeName(),
                                                            $value,
                                                            $columnMappingData,
                                                            $shouldSaveModel);
                    return array($this->getModelAttributeName() => $value);
                }
                catch (ExternalSystemIdNotFoundException $e)
                {
                    return array(ExternalSystemIdSuppportedSanitizerUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME => $value);
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>