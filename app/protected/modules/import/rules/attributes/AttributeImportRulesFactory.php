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
            $importRulesTypeClassName = $importRulesType . 'ImportRules';
            $attributeImportRulesType = $importRulesTypeClassName::getAttributeImportRulesType(
                                        $attributeIndexOrDerivedType);
            $modelClassName           = $importRulesTypeClassName::getModelClassNameByAttributeIndexOrDerivedType(
                                        $attributeIndexOrDerivedType);
            assert('$attributeImportRulesType !== null');
            $attributeImportRulesClassName = $attributeImportRulesType . 'AttributeImportRules';
            if(is_subclass_of($attributeImportRulesClassName, 'DerivedAttributeImportRules'))
            {
                return new $attributeImportRulesClassName(new $modelClassName(false));
            }
            return new $attributeImportRulesClassName(new $modelClassName(false), $attributeIndexOrDerivedType);
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
            assert('is_array($attributeIndicesOrDerivedTypes)');
            $collection   = array();
            foreach($attributeIndicesOrDerivedTypes as $attributeIndexOrDerivedAttributeType)
            {
                $collection[] = self::makeByImportRulesTypeAndAttributeIndexOrDerivedType($importRulesType,
                                $attributeIndexOrDerivedAttributeType);
            }
            return $collection;
        }


    }
?>