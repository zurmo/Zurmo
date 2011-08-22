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

    class ImportToMappingFormLayoutUtil
    {
        public static function make($modelClassName, $form, $importRulesType, $mappableAttributeIndicesAndDerivedTypes)
        {
            assert('is_string($modelClassName)');
            assert('$form instanceof ZurmoActiveForm');
            assert('is_string($importRulesType)');
            assert('is_array($mappableAttributeIndicesAndDerivedTypes)');
            $mappableAttributeIndicesAndDerivedTypesForImportColumns = self::resolveMappableAttributeIndicesAndDerivedTypesByColumnType(
                                  $mappableAttributeIndicesAndDerivedTypes, 'importColumn', $importRulesType);
            $mappableAttributeIndicesAndDerivedTypesForExtraColumns = self::resolveMappableAttributeIndicesAndDerivedTypesByColumnType(
                                  $mappableAttributeIndicesAndDerivedTypes,'extraColumn', $importRulesType);
            return new MappingFormLayoutUtil($modelClassName, $form,
                                             $mappableAttributeIndicesAndDerivedTypesForImportColumns,
                                             $mappableAttributeIndicesAndDerivedTypesForExtraColumns);
        }

        /**
         *
         * Enter description here ...
         * @param array $mappableAttributeIndicesAndDerivedTypes
         * @param string $columnType
         */
        protected static function resolveMappableAttributeIndicesAndDerivedTypesByColumnType(
                                  $mappableAttributeIndicesAndDerivedTypes, $columnType, $importRulesType)
        {
            assert('is_array($mappableAttributeIndicesAndDerivedTypes)');
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            assert('is_string($importRulesType)');
            if($columnType == 'importColumn')
            {
                return $mappableAttributeIndicesAndDerivedTypes;
            }
            $attributeImportRules = AttributeImportRulesFactory::
                                    makeCollection($importRulesType, array_keys($mappableAttributeIndicesAndDerivedTypes));
            $sanitizedMappableAttributeIndicesAndDerivedTypes = array();
            foreach($mappableAttributeIndicesAndDerivedTypes as $attributeIndicesAndDerivedType => $label)
            {
                if($attributeImportRules[$attributeIndicesAndDerivedType]->
                   getExtraColumnUsableCountOfModelAttributeMappingRuleFormTypesAndElementTypes() > 0)
                {
                    $sanitizedMappableAttributeIndicesAndDerivedTypes[$attributeIndicesAndDerivedType] = $label;
                }
            }
            return $sanitizedMappableAttributeIndicesAndDerivedTypes;
        }
    }
?>