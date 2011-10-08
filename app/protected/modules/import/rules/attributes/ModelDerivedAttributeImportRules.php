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
     * Base class for a derived relation attribute. This would occur if the relation attribute is not specifically
     * defined on a model, but instead a casted up model is specifically defined.
     * @see DefaultModelNameIdDerivedAttributeMappingRuleForm
     */
    abstract class ModelDerivedAttributeImportRules extends DerivedAttributeImportRules
    {
        protected static function getAllModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array('DefaultModelNameIdDerivedAttribute' => 'ImportMappingRuleDefaultModelNameId',
                         'IdValueType'                        => 'ImportMappingModelIdValueTypeDropDown');
        }

        public function getDisplayLabel()
        {
            $name           = get_called_class();
            $modelClassName = substr($name, 0, strlen($name) - strlen('DerivedAttributeImportRules'));
            return $modelClassName::getModelLabelByTypeAndLanguage('Singular');
        }

        /**
         * This information regarding the correct attribute name on the model is not available. This information is
         * available via DerivedAttributeSupportedImportRules::getDerivedAttributeRealAttributeName();  Since we don't
         * have access to that information in this class, we don't know the import rule type, we cannot return anything.
         * Resolving what attribute to save the derived model to will need to be handled outside of this class.
         * @see DerivedAttributeSupportedImportRules::getRealModelAttributeNameForDerivedAttribute()
         */
        public function getRealModelAttributeNames()
        {
            return null;
        }

        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            throw new NotImplementedException();
        }

        public function resolveValueForImport($value, $columnMappingData, ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            $modelClassName        = $this->getModelClassName();
            $derivedModelClassName = static::getDerivedModelClassName();
            $sanitizedValue = ImportSanitizerUtil::
                              sanitizeValueBySanitizerTypes(static::getSanitizerUtilTypesInProcessingOrder(),
                                                            $modelClassName, null,
                                                            $value, $columnMappingData, $importSanitizeResultsUtil);
             if ($sanitizedValue == null &&
                $columnMappingData['mappingRulesData']
                                  ['DefaultModelNameIdDerivedAttributeMappingRuleForm']['defaultModelId'] != null)
             {
                $modelId               = $columnMappingData['mappingRulesData']
                                                           ['DefaultModelNameIdDerivedAttributeMappingRuleForm']
                                                           ['defaultModelId'];
                $sanitizedValue        = $derivedModelClassName::getById((int)$modelId);
             }
            return array(static::getDerivedAttributeName() => $sanitizedValue);
        }

        public static function getDerivedAttributeName()
        {
            return static::getDerivedModelClassName() . 'Derived';
        }

        public static function getDerivedModelClassName()
        {
            $class = get_called_class();
            $class = substr($class, 0, strlen($class) - strlen('DerivedAttributeImportRules'));
            return $class;
        }
    }
?>