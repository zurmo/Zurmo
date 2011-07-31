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

        protected $attributeName;

        public function __construct($model, $attributeName = null)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($attributeName) || $attributeName == null');
            $this->model         = $model;
            $this->attributeName = $attributeName;
        }

        public function getModelClassName()
        {
            return get_class($this->model);
        }

        public function getDisplayLabel()
        {
            return $this->model->getAttributeLabel($this->attributeName);
        }

        public function getModelAttributeNames()
        {
            return array($this->attributeName);
        }

        /**
         * Returns mapping rule form and the associated element to use.  Override to specify as many
         * pairings as needed.
         * @return array of MappingRuleForm/Element pairings.
         */
        public static function getModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            return array();
        }

        /**
         * @return array of sanitizer util names.
         */
        public static function getSanitizerUtilNames()
        {
            return array();
        }
    }
?>