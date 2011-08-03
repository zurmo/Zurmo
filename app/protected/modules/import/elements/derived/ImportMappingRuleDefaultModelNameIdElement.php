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
     * Element used in the import mapping process. This is used specifically for relation attributes where
     * a model modal popup/auto-complete input are needed. Unlike other overrides of NameIdElement, this class
     * is not tied to a specific model and can be used with any model class.
     */
    class ImportMappingRuleDefaultModelNameIdElement extends NameIdElement
    {
        protected $idAttributeId = 'defaultModelId';

        protected $nameAttributeName = 'defaultModelStringifiedName';

        /**
         * Override to ensure the model is the correct type of model. Also nullifying the attribute since
         * it will not be used by this element.  Setting the null to a string version of 'null' which is how the
         * view metadata normally sends this value in.
         */
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$model instanceof DefaultModelNameIdMappingRuleForm ||
                    $model instanceof DefaultModelNameIdDerivedAttributeMappingRuleForm');
            parent::__construct($model, $attribute, $form, $params);
        }

        /**
         * Override to get the correct module Id.  In the typical use of NameIdElement, the module id is statically
         * defined on the overrides, but since this element is used in a dynamic way and not attached to any particular
         * model, this override is necessary.
         * @see ModelElement::resolveModuleId()
         */
        protected function resolveModuleId()
        {
            return $this->model->getModuleIdOfDefaultModel();
        }

        public static function getModuleId()
        {
            throw new NotSupportedException();
        }

        public static function getModelAttributeNames()
        {
            throw new NotSupportedException();
        }
    }
?>