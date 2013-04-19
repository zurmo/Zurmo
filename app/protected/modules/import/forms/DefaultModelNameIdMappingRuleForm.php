<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Form for handling default values for relation type attributes.
     */
    class DefaultModelNameIdMappingRuleForm extends DefaultModelAttributeMappingRuleForm
    {
        /**
         * @var integer
         */
        public    $defaultModelId;

        /**
         * @var string
         */
        public    $defaultModelStringifiedName;

        /**
         * For the modelClassName associated with this form, get the model's module id.
         * @var string
         */
        protected $moduleIdOfDefaultModel;

        public function __construct($modelClassName, $modelAttributeName)
        {
            parent::__construct($modelClassName, $modelAttributeName);
            assert('is_subclass_of($modelClassName, "Item") || $modelClassName == "Item"');
            assert('$modelClassName::isRelation($modelAttributeName)');
            $relationModelClassName       = $modelClassName::getRelationModelClassName($modelAttributeName);
            $defaultModuleClassName       = $relationModelClassName::getModuleClassName();
            $this->moduleIdOfDefaultModel = $defaultModuleClassName::getDirectoryName();
        }

        /**
         * Override to add an extra rule for the defaultModelStringifiedName. This will ensure if there is an error
         * the message shows up properly in the user interface, since otherwise with the rule being on the
         * defaultModelId, it would not show up because it is a hidden input.
         * @see DefaultModelAttributeMappingRuleForm::rules()
         */
        public function rules()
        {
            if ($this->getScenario() == 'extraColumn')
            {
                $requiredRuleIsApplicable = true;
            }
            else
            {
                $requiredRuleIsApplicable = false;
            }
            $defaultValueApplicableModelAttributeRules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                                                         getApplicableRulesByModelClassNameAndAttributeName(
                                                         $this->modelClassName,
                                                         $this->modelAttributeName,
                                                         'defaultModelStringifiedName',
                                                         $requiredRuleIsApplicable);
                                                         return array_merge(parent::rules(), $defaultValueApplicableModelAttributeRules);
            return array_merge(parent::rules(), $defaultValueApplicableModelAttributeRules);
        }

        /**
         * This method is needed in the interface to work properly with the elements that use it.
         */
        public function getId()
        {
            return null;
        }

        public function attributeLabels()
        {
            return array('defaultModelId'              => Zurmo::t('ImportModule', 'Default Value'),
                         'defaultModelStringifiedName' => Zurmo::t('ImportModule', 'Default Name'));
        }

        public static function getAttributeName()
        {
            return 'defaultModelId';
        }

        /**
         * If needed get the stringified model name if the default model id is populated.
         */
        public function getDefaultModelName()
        {
            if ($this->defaultModelName != null)
            {
                return $this->defaultModelName;
            }
            elseif ($this->defaultModelId != null)
            {
                $modelClassName                    = $this->modelClassName;
                $this->defaultModelStringifiedName = strval($modelClassName::getById($this->defaultModelId));
                return $this->defaultModelStringifiedName;
            }
            else
            {
                return null;
            }
        }

        public function getModuleIdOfDefaultModel()
        {
            return $this->moduleIdOfDefaultModel;
        }
    }
?>