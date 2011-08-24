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
     * Helper class to make AttributeImportRules objects and collections.
     */
    class AttributeImportRulesFactory
    {
        /**
         * Cached array of data in order to avoid multiple newing up a model when it is just used to access basic
         * attribute or relation information.  Eventually some sort of static model utility will be available so
         * models don't have to be newed up to retrieve information like: isAttribute or getAttributeLabel.
         * @var array
         */
        private static $modelsByClassName = array();

        /**
         * Given an import rules type and an attribute index or derived type string, make an AttributeImportRules object.
         * @param string $importRulesType
         * @param string $attributeIndexOrDerivedType
         * @return object AttributeImportRules
         */
        public static function makeByImportRulesTypeAndAttributeIndexOrDerivedType($importRulesType,
                                                                                  $attributeIndexOrDerivedType)
        {
            assert('is_string($importRulesType)');
            assert('is_string($attributeIndexOrDerivedType)');
            $importRulesTypeClassName = ImportRulesUtil::getImportRulesClassNameByType($importRulesType);
            $attributeImportRulesType = $importRulesTypeClassName::getAttributeImportRulesType(
                                        $attributeIndexOrDerivedType);
            $modelClassName           = $importRulesTypeClassName::getModelClassNameByAttributeIndexOrDerivedType(
                                        $attributeIndexOrDerivedType);
            $attributeName            = self::resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                                        $modelClassName,
                                        $attributeIndexOrDerivedType);
            if(isset(self::$modelsByClassName[$modelClassName]))
            {
                $model = self::$modelsByClassName[$modelClassName];
            }
            else
            {
                $model                                    = new $modelClassName(false);
                self::$modelsByClassName[$modelClassName] = $model;
            }
            assert('$attributeImportRulesType !== null');
            $attributeImportRulesClassName = $attributeImportRulesType . 'AttributeImportRules';
            if(is_subclass_of($attributeImportRulesClassName, 'DerivedAttributeImportRules'))
            {
                return new $attributeImportRulesClassName($model);
            }
            return new $attributeImportRulesClassName($model, $attributeName);
        }

        /**
         * Given an import rules type and an array of atttribute indices or derived types, make a collection
         * of AttributeImportRules.
         * @param unknown_type $importRulesType
         * @param unknown_type $attributeIndicesOrDerivedTypes
         * @return array AttributeImportRules collection
         */
        public static function makeCollection($importRulesType, $attributeIndicesOrDerivedTypes)
        {
            assert('is_string($importRulesType)');
            assert('$attributeIndicesOrDerivedTypes == null || is_array($attributeIndicesOrDerivedTypes)');
            $collection   = array();
            if($attributeIndicesOrDerivedTypes != null)
            {
                foreach($attributeIndicesOrDerivedTypes as $attributeIndexOrDerivedAttributeType)
                {
                    $collection[$attributeIndexOrDerivedAttributeType] =
                                    self::makeByImportRulesTypeAndAttributeIndexOrDerivedType($importRulesType,
                                    $attributeIndexOrDerivedAttributeType);
                }
            }
            return $collection;
        }

        /**
         * Given an import rules type and attribute index or derived type, return the attribute import rules class name.
         * @param string $importRulesType
         * @param string $attributeIndexOrDerivedType
         */
        public static function getClassNameByImportRulesTypeAndAttributeIndexOrDerivedType($importRulesType,
                                                                                           $attributeIndexOrDerivedType)
        {
            assert('is_string($importRulesType)');
            assert('is_string($attributeIndexOrDerivedType)');
            $importRulesTypeClassName = ImportRulesUtil::getImportRulesClassNameByType($importRulesType);
            $attributeImportRulesType = $importRulesTypeClassName::
                                        getAttributeImportRulesType($attributeIndexOrDerivedType);
            assert('$attributeImportRulesType !== null');
            $attributeImportRulesClassName = $attributeImportRulesType . 'AttributeImportRules';
            return $attributeImportRulesClassName;
        }

        /**
         * This method will inspect the attributeIndexOrDerivedType looking for any attribute indexes that represent
         * two distinct variables. If it finds this, then it gets the correct attributeName to return.  The two
         * variables would be a relation attributeName and a relatedAttributeName.  It will return the
         * relatedAttributeName and update by reference the $modelClassName to the relation model class name.
         * @param string $modelClassName
         * @param string $attributeIndexOrDerivedType
         */
        public static function resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                               & $modelClassName, $attributeIndexOrDerivedType
        )
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeIndexOrDerivedType)');
            $relationNameAndAttributeName = explode(FormModelUtil::DELIMITER, $attributeIndexOrDerivedType);
            if(count($relationNameAndAttributeName) == 1)
            {
                return $attributeIndexOrDerivedType;
            }
            else
            {
                list($relationName, $attributeName) = $relationNameAndAttributeName;
            }
            if(isset(self::$modelsByClassName[$modelClassName]))
            {
                $model = self::$modelsByClassName[$modelClassName];
            }
            else
            {
                $model                                    = new $modelClassName(false);
                self::$modelsByClassName[$modelClassName] = $model;
            }
            $modelClassName = $model->getRelationModelClassName($relationName);
            assert('$modelClassName != null');
            return $attributeName;
        }

        /**
         * Given an attribute index or derived type, get the actual attribute name.  If the value is not split by
         * FormModelUtil::DELIMITER then the value passed in will be returned as is. Otherwise the left side of the
         * delimiter will be returned.
         * @param string $attributeIndexOrDerivedType
         */
        public static function getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType(
                               $attributeIndexOrDerivedType)
        {
            assert('is_string($attributeIndexOrDerivedType)');
                    $relationNameAndAttributeName = explode(FormModelUtil::DELIMITER, $attributeIndexOrDerivedType);
            if(count($relationNameAndAttributeName) == 1)
            {
                return $attributeIndexOrDerivedType;
            }
            else
            {
                list($relationName, $attributeName) = $relationNameAndAttributeName;
                return $relationName;
            }
        }
    }
?>