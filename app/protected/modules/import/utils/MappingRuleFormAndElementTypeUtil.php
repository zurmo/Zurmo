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
     * Helper class for working with MappingRuleForms and Element types that are used with those forms.
     */
    class MappingRuleFormAndElementTypeUtil
    {
        /**
         * Given an array of AttributeImportRules an attribute index or derived type, make a MappingRuleForm
         * @param array $attributeImportRules
         * @param string $attributeIndexOrDerivedType
         */
        public static function makeCollectionByAttributeImportRules($attributeImportRules, $attributeIndexOrDerivedType, $columnType)
        {
            assert('$attributeImportRules instanceof AttributeImportRules');
            assert('is_string($attributeIndexOrDerivedType)');
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            $mappingRuleFormsAndElementTypes = array();
            $mappingRuleFormTypesAndElementTypes = $attributeImportRules::
                                                   getModelAttributeMappingRuleFormTypesAndElementTypes($columnType);
            foreach ($mappingRuleFormTypesAndElementTypes as $mappingRuleFormType => $elementType)
            {
                $mappingRuleFormClassName          = $mappingRuleFormType . 'MappingRuleForm';
                $modelClassName                    = $attributeImportRules->getModelClassName();
                if ($attributeImportRules instanceof NonDerivedAttributeImportRules)
                {
                    $attributeNameOrDerivedType = $attributeImportRules->getModelAttributeName();
                }
                elseif ($attributeImportRules instanceof DerivedAttributeImportRules)
                {
                    $attributeNameOrDerivedType = $attributeImportRules::getType();
                }
                else
                {
                    throw new NotSupportedException();
                }
                $mappingRuleForm                   = new $mappingRuleFormClassName(
                                                         $modelClassName,
                                                         $attributeNameOrDerivedType);
                $mappingRuleFormsAndElementTypes[] = array('elementType'     => $elementType,
                                                           'mappingRuleForm' => $mappingRuleForm);
            }
            return $mappingRuleFormsAndElementTypes;
        }

        /**
         * Given an array of mapping data and an import rules type, make an array of mapping rule forms
         * and element types.  This is indexed by the column name from the mapping data.  If the type of column
         * is 'extraColumn', then the mapping rules forms will be set with the scenario 'extraColumn'.
         * @param array $mappingData
         * @param string $importRulesType
         */
        public static function makeFormsAndElementTypesByMappingDataAndImportRulesType($mappingData, $importRulesType)
        {
            assert('is_array($mappingData)');
            assert('is_string($importRulesType)');
            $mappingRuleFormsAndElementTypes = array();
            foreach ($mappingData as $columnName => $mappingDataByColumn)
            {
                $mappingRuleFormsAndElementTypes[$columnName] = array();
                assert('$mappingDataByColumn["type"] == "importColumn" || $mappingDataByColumn["type"] == "extraColumn"');
                if ($mappingDataByColumn['attributeIndexOrDerivedType'] != null)
                {
                    $attributeImportRulesClassName = AttributeImportRulesFactory::
                                                     getClassNameByImportRulesTypeAndAttributeIndexOrDerivedType(
                                                     $importRulesType,
                                                     $mappingDataByColumn['attributeIndexOrDerivedType']);
                    foreach ($mappingDataByColumn["mappingRulesData"] as $mappingRuleFormClassName => $mappingRuleFormData)
                    {
                        $mappingRuleFormAndElementTypes = $attributeImportRulesClassName::
                                                          getModelAttributeMappingRuleFormTypesAndElementTypes(
                                                          $mappingDataByColumn["type"]);
                        $elementType                    = $mappingRuleFormAndElementTypes
                                                          [$mappingRuleFormClassName::getType()];
                        $mappingRuleForm                = static::makeForm($importRulesType,
                                                          $mappingDataByColumn['attributeIndexOrDerivedType'],
                                                          $mappingRuleFormClassName);
                        if ($mappingDataByColumn['type'] == "extraColumn")
                        {
                            $mappingRuleForm->setScenario('extraColumn');
                        }
                        $mappingRuleForm->setAttributes   ($mappingRuleFormData);
                        $mappingRuleFormsAndElementTypes[$columnName][]= array('mappingRuleForm' => $mappingRuleForm,
                                                                               'elementType'     => $elementType);
                    }
                }
            }
            return $mappingRuleFormsAndElementTypes;
        }

        /**
         * Make a mapping rule form object.
         * @param string $importRulesType
         * @param string $attributeIndexOrDerivedType
         * @param string $mappingRuleFormClassName
         */
        public static function makeForm($importRulesType, $attributeIndexOrDerivedType, $mappingRuleFormClassName)
        {
            assert('is_string($importRulesType)');
            assert('is_string($attributeIndexOrDerivedType)');
            assert('is_string($mappingRuleFormClassName)');
            $importRulesTypeClassName = ImportRulesUtil::getImportRulesClassNameByType($importRulesType);
            $modelClassName           = $importRulesTypeClassName::getModelClassNameByAttributeIndexOrDerivedType(
                                        $attributeIndexOrDerivedType);
            $attributeName            = AttributeImportRulesFactory::
                                        resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                                        $modelClassName,
                                        $attributeIndexOrDerivedType);
            $mappingRuleForm          = new $mappingRuleFormClassName($modelClassName, $attributeName);
            return $mappingRuleForm;
        }

        /**
         * Given an array of mappingDataMappingRuleForms data, validate each form.  If any form does not validate,
         * then return false.  The mappingDataMappingRuleForms array is organized by columnNames, then by the mapping
         * rules for each column.
         * @param array $mappingDataMappingRuleFormsAndElementTypes
         * @return true/false validation results.
         */
        public static function validateMappingRuleForms($mappingDataMappingRuleFormsData)
        {
            assert('is_array($mappingDataMappingRuleFormsData)');
            $anyValidatedFalse = false;
            foreach ($mappingDataMappingRuleFormsData as $notUsed => $mappingRuleFormsData)
            {
                foreach ($mappingRuleFormsData as $a => $mappingRuleFormData)
                {
                    assert('$mappingRuleFormData["mappingRuleForm"] instanceof MappingRuleForm');
                    if (!$mappingRuleFormData["mappingRuleForm"]->validate())
                    {
                        $anyValidatedFalse = true;
                    }
                }
            }
            return !$anyValidatedFalse;
        }
    }
?>