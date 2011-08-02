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
     * Base class of import rules that assist with importing data from an external system.  Extend this class to make
     * a set of ImportRules that is for a specific module or a combiniation of modules and/or models.
     */
    abstract class ImportRules
    {
        /**
         * @return string - If the class name is TestImportRules, then 'Test' will be returned.
         */
        public static function getType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('ImportRules'));
            return $type;
        }

        /**
         * Get model class name associated with the import rules.
         * @return string
         */
        public static function getModelClassName()
        {
        }

        /**
        * Get module class names associated with the import rules.
        * @return array
        */
        public static function getModuleClassNames()
        {
            $modelClassName = static::getModelClassName();
            assert('is_subclass_of($modelClassName, "Item")');
            $moduleClassName = $modelClassName::getModuleClassName();
            assert('$moduleClassName != null');
            return array($moduleClassName);
        }

        /**
         * Get the display label used to describe the import rules.
         * @return string
         */
        public static function getDisplayLabel()
        {
            $modelClassName  = static::getModelClassName();
            assert('is_subclass_of($modelClassName, "Item")');
            $moduleClassName = $modelClassName::getModuleClassName();
            assert('$moduleClassName != null');
            return $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
        }

        /**
         * Get the array of available derived attribute types that can be mapped when using these import rules.
         * @return array
         */
        public static function getDerivedAttributeTypes()
        {
            return array('CreatedByUser',
                         'ModifiedByUser',
                         'CreatedDateTime',
                         'ModifiedDateTime');
        }

        /**
         * Get the array of attributes that cannot be mapped when using these import rules.
         * @return array
         */
        public static function getNonImportableAttributeNames()
        {
            return array('createdByUser',
                         'modifiedByUser',
                         'createdDateTime',
                         'modifiedDateTime');
        }

        /**
         * Get the array of derived attributes that cannot be mappen when using these import rules.
         * @return array
         */
        public static function getNonImportableAttributeImportRulesTypes()
        {
            return array();
        }

        /**
         * Get mappable attribute indices and derived types in one array.  Mappable indices are strings of attribute
         * and related attribute information. For example, an attribute 'text' on a model would have an index of
         * 'text'. An attribute that is a related model 'relatedModel' with a related attribute 'string' would be
         * returned as 'relatedModel__string'. This array filters out any non-placeable derived types or attributes
         * before returning the array.
         * @return array
         */
        public static function getMappableAttributeIndicesAndDerivedTypes()
        {
            $mappableAttributeIndicesAndDerivedTypes = array();
            $modelClassName                          = static::getModelClassName();
            $attributesCollection                    = static::getAttributesCollectionByModelClassName($modelClassName);
            $model                                   = new $modelClassName(false);
            foreach($attributesCollection as $attributeIndex => $attributeData)
            {
                if(!in_array($attributeData['attributeName'], static::getNonImportableAttributeNames()) &&
                    !in_array($attributeData['attributeImportRulesType'], static::getNonImportableAttributeImportRulesTypes()))
                {
                    $mappableAttributeIndicesAndDerivedTypes[$attributeIndex] = $attributeData['attributeLabel'];
                }
            }
            foreach(static::getDerivedAttributeTypes() as $derivedType)
            {
                $attributeImportRulesClassName                         = $derivedType . 'AttributeImportRules';
                $attributeImportRules                                  = new $attributeImportRulesClassName($model);
                $mappableAttributeIndicesAndDerivedTypes[$derivedType] = $attributeImportRules->getDisplayLabel();
            }
            asort($mappableAttributeIndicesAndDerivedTypes);
            return $mappableAttributeIndicesAndDerivedTypes;
        }

        /**
         * Gets the attribute collection indexed by attribute indices for a particular model.
         * @param string $modelClassName
         * @see self::getMappableAttributeIndicesAndDerivedTypes()
         * @return array Attribute colleciton.
         */
        protected static function getAttributesCollectionByModelClassName($modelClassName)
        {
            assert('$modelClassName != null && is_string($modelClassName)');
            $modelAttributesAdapter = new ModelAttributesImportMappingAdapter(new $modelClassName(false));
            return $modelAttributesAdapter->getAttributes();
        }

        /**
         * Publically facing method to return the attribute collection based on the model class supported
         * by the import rules.
         * @see self::getAttributesCollectionByModelClassName($modelClassName)
         * @return array Attribute colleciton.
         */
        public static function getAttributesCollection()
        {
            return getAttributesCollectionByModelClassName(static::getModelClassName());
        }

        /**
         * Given an attribute index or derived type, return the model class name that it is from.
         * @param string $attributeIndexOrDerivedType
         */
        public static function getModelClassNameByAttributeIndexOrDerivedType($attributeIndexOrDerivedType)
        {
            assert('is_string($attributeIndexOrDerivedType)');
            return static::getModelClassName();
        }

        /**
         * Given an attribute index or derived type, return the class name of the appropriate attribute rules type.
         * @param string $attributeIndexOrDerivedType
         * @throws NotSupportedException
         * @return string - attribute rules type.
         */
        public static function getAttributeImportRulesType($attributeIndexOrDerivedType)
        {
            assert('is_string($attributeIndexOrDerivedType)');
            $modelClassName           = static::getModelClassName();
            $attributeImportRulesData = static::getAttributeIndexOrDerivedTypeAndAttributeImportRuleTypes($modelClassName);
            if(isset($attributeImportRulesData[$attributeIndexOrDerivedType]))
            {
                return $attributeImportRulesData[$attributeIndexOrDerivedType];
            }
            throw new NotSupportedException();
        }

        /**
         * Given a model class name, return an array of attribute indexes and derived attribute types as the array's
         * indexes, while using the attribute import rules type as the array values.
         * @param string $modelClassName
         */
        protected static function getAttributeIndexOrDerivedTypeAndAttributeImportRuleTypes($modelClassName)
        {
            assert('$modelClassName != null && is_string($modelClassName)');
            $attributesCollection = static::getAttributesCollectionByModelClassName($modelClassName);

            $attributeIndexOrDerivedTypeAndRuleType = array();
            foreach($attributesCollection as $attributeIndex => $attributeData)
            {
                if(!in_array($attributeData['attributeName'], static::getNonImportableAttributeNames()) &&
                    !in_array($attributeData['attributeImportRulesType'], static::getNonImportableAttributeImportRulesTypes()))
                {
                    $attributeIndexOrDerivedTypeAndRuleType[$attributeIndex] = $attributeData['attributeImportRulesType'];
                }
            }
            foreach(static::getDerivedAttributeTypes() as $derivedType)
            {
                $attributeIndexOrDerivedTypeAndRuleType[$derivedType] = $derivedType;
            }
            return $attributeIndexOrDerivedTypeAndRuleType;
        }

        /**
         *
         * For this set of import rules, get only the required attributes indexed by attribute index in an attribute
         * collection array. This will filter out any required attributes that are read only on their respective
         * models.
         * @return array
         */
        public static function getRequiredAttributesCollectionNotIncludingReadOnly()
        {
            $modelClassName                        = static::getModelClassName();
            $model                                 = new $modelClassName(false);
            $attributesCollection                  = static::getAttributesCollectionByModelClassName($modelClassName);
            $requireAttributesCollection           = array();
            foreach($attributesCollection as $attributeIndex => $attributeData)
            {
                if($attributeData['isRequired'] && !$model->isAttributeReadOnly($attributeData['attributeName']))
                {
                    $requireAttributesCollection[$attributeIndex] = $attributeData;
                }
            }
            return $requireAttributesCollection;
        }
    }
?>