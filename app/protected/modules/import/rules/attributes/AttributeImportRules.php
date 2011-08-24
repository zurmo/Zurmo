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
     * Base class for defining an attribute or derived attribute's import rules.
     */
    abstract class AttributeImportRules
    {
        protected $model;

        public function __construct($model)
        {
            assert('$model instanceof RedBeanModel');
            $this->model         = $model;
        }

        /**
         * @return string - If the class name is TestAttributeImportRules, then 'Test' will be returned.
         */
        public static function getType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('AttributeImportRules'));
            return $type;
        }

        public function getModelClassName()
        {
            return get_class($this->model);
        }

        /**
         * Since the attributes can be derived or real, this method provides a uniform api that can be called regardless
         * of whether the attribute is derived or not, and will produce the real model attribute names if available.
         */
        abstract public function getRealModelAttributeNames();

        public function getDisplayLabelByAttributeName($attributeName)
        {
            assert('$attributeName == null || is_string($attributeName)');
            return $this->model->getAttributeLabel($attributeName);
        }

        /**
         * Returns mapping rule form and the associated element to use.  Override to specify as many
         * pairings as needed.
         * @return array of MappingRuleForm/Element pairings.
         */
        public static function getModelAttributeMappingRuleFormTypesAndElementTypes($type)
        {
            assert('$type == "importColumn" || $type == "extraColumn"');

            $forAllData = static::getAllModelAttributeMappingRuleFormTypesAndElementTypes();
            if($type == 'extraColumn')
            {
                $typeBasedData  = static::getExtraColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes();
            }
            else
            {
                $typeBasedData  = static::getImportColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes();
            }
            return array_merge($forAllData, $typeBasedData);
        }

        /**
         * Returns mapping rule form and the associated element to use.  Override to specify as many
         * pairings as needed. This method is used for mapping rule form/element pairings that are available for
         * both types of columns.
         * @return array of MappingRuleForm/Element pairings.
         */
        protected static function getAllModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * Override to place mapping rule forms / elements that are only for mapping extra columns.
         */
        protected static function getExtraColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * Override to place mapping rule forms / elements that are only for mapping actual import columns.
         */
        protected static function getImportColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * @return array of sanitizer util names. The sanitizer utils in the array are in the order that they will
         * be processed during the import.
         */
        public static function getSanitizerUtilTypesInProcessingOrder()
        {
            return array();
        }

        /**
         * @return count of usable mapping rule form types. This count is different than for import columns because
         * some mapping rules only show for import columns and not extra columns.  This is the columnType in the
         * mapping data.
         */
        public static function getExtraColumnUsableCountOfModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return count(static::getAllModelAttributeMappingRuleFormTypesAndElementTypes()) +
                   count(static::getExtraColumnOnlyModelAttributeMappingRuleFormTypesAndElementTypes());
        }
    }
?>