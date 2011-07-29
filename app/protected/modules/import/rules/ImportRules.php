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
     * Class to help the import module understand
     * how to parse and handle the import file it is importing based on what module(s) it is being imported into.
     */
    abstract class ImportRules
    {
        public static function getModelClassName()
        {
        }

        public static function getModuleClassNames()
        {
            $modelClassName = static::getModelClassName();
            assert('is_subclass_of($modelClassName, "Item")');
            $moduleClassName = $modelClassName::getModuleClassName();
            assert('$moduleClassName != null');
            return array($moduleClassName);
        }

        public static function getDisplayLabel()
        {
            $modelClassName  = static::getModelClassName();
            assert('is_subclass_of($modelClassName, "Item")');
            $moduleClassName = $modelClassName::getModuleClassName();
            assert('$moduleClassName != null');
            return $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
        }

        public static function getDerivedAttributeTypes()
        {
            return array('CreatedByUser',
                         'ModifiedByUser',
                         'CreatedDateTime',
                         'ModifiedDateTime');
        }

        public static function getNonImportableAttributeNames()
        {
            return array('createdByUser',
                         'modifiedByUser',
                         'createdDateTime',
                         'modifiedDateTime');
        }

        public static function getNonImportableAttributeImportRulesTypes()
        {
            return array();
        }

        public static function getMappableAttributeNamesAndDerivedTypes()
        {
            $mappableAttributeNamesAndDerivedTypes = array();
            $modelClassName                        = static::getModelClassName();
            $attributesCollection                  = static::getAttributesCollectionByModelClassName($modelClassName);
            $model                                 = new $modelClassName(false);

            foreach($attributesCollection as $attributeIndex => $attributeData)
            {
                if(!in_array($attributeData['attributeName'], static::getNonImportableAttributeNames()) &&
                    !in_array($attributeData['attributeImportRulesType'], static::getNonImportableAttributeImportRulesTypes()))
                {
                    $mappableAttributeNamesAndDerivedTypes[$attributeIndex] = $attributeData['attributeLabel'];
                }
            }
            foreach(static::getDerivedAttributeTypes() as $derivedType)
            {
                $attributeImportRulesClassName                       = $derivedType . 'AttributeImportRules';
                $attributeImportRules                                = new $attributeImportRulesClassName($model);
                $mappableAttributeNamesAndDerivedTypes[$derivedType] = $attributeImportRules->getDisplayLabel();
            }
            return $mappableAttributeNamesAndDerivedTypes;
        }

        protected static function getAttributesCollectionByModelClassName($modelClassName)
        {
            assert('$modelClassName != null && is_string($modelClassName)');
            $modelAttributesAdapter = new ModelAttributesImportMappingAdapter(new $modelClassName(false));
            return $modelAttributesAdapter->getAttributes();
        }

        public static function getModelClassNameByAttributeNameOrDerivedType($attributeNameOrDerivedType)
        {
            assert('is_string($attributeNameOrDerivedType)');
            return static::getModelClassName();
        }

        public static function getAttributeImportRulesType($attributeNameOrDerivedType)
        {
            assert('is_string($attributeNameOrDerivedType)');
            $modelClassName           = static::getModelClassName();
            $attributeImportRulesData = static::getAttributeNameOrDerivedTypeAndAttributeImportRuleTypes($modelClassName);
            if(isset($attributeImportRulesData[$attributeNameOrDerivedType]))
            {
                return $attributeImportRulesData[$attributeNameOrDerivedType];
            }
            throw new NotSupportedException();
        }

        protected static function getAttributeNameOrDerivedTypeAndAttributeImportRuleTypes($modelClassName)
        {
            assert('$modelClassName != null && is_string($modelClassName)');
            $attributesCollection = static::getAttributesCollectionByModelClassName($modelClassName);

            $attributeNameOrDerivedTypeAndRuleType = array();
            foreach($attributesCollection as $attributeIndex => $attributeData)
            {
                if(!in_array($attributeData['attributeName'], static::getNonImportableAttributeNames()) &&
                    !in_array($attributeData['attributeImportRulesType'], static::getNonImportableAttributeImportRulesTypes()))
                {
                    $attributeNameOrDerivedTypeAndRuleType[$attributeIndex] = $attributeData['attributeImportRulesType'];
                }
            }
            foreach(static::getDerivedAttributeTypes() as $derivedType)
            {
                $attributeNameOrDerivedTypeAndRuleType[$derivedType] = $derivedType;
            }
            return $attributeNameOrDerivedTypeAndRuleType;
        }
    }
?>