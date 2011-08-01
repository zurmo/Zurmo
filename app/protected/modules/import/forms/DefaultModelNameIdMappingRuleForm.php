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
     */
    class DefaultModelNameIdMappingRuleForm extends ModelAttributeMappingRuleForm
    {
        public    $defaultModelId;

        public    $defaultModelStringifiedName;

        protected $moduleIdOfDefaultModel;

        public function __construct($modelClassName, $modelAttributeName)
        {
            parent::__construct($modelClassName, $modelAttributeName);
            $model = new $modelClassName(false);
            assert('$model instanceof Item');
            assert('$model->isRelation($modelAttributeName)');
            $relationModelClassName       = $model->getRelationModelClassName($modelAttributeName);
            $defaultModuleClassName       = $relationModelClassName::getModuleClassName();
            $this->moduleIdOfDefaultModel = $defaultModuleClassName::getDirectoryName();
        }

        //todo: there is here because modelElement is lookign for it...
        public function getId()
        {
            return null;
        }

        public function rules()
        {
            if($this->getScenario() == 'extraColumn')
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
                                                         $requiredRuleIsApplicable);
                                                         return array_merge(parent::rules(), $defaultValueApplicableModelAttributeRules);
        }

        public function attributeLabels()
        {
            return array('defaultModelId'              => Yii::t('Default', 'Default Value'),
                         'defaultModelStringifiedName' => Yii::t('Default', 'Default Name'));
        }

        public static function getAttributeName()
        {
            return 'defaultModelId';
        }

        public function getDefaultModelName()
        {
            if($this->defaultModelName != null)
            {
                return $this->defaultModelName;
            }
            elseif($this->defaultModelId != null)
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
            $this->moduleIdOfDefaultModel;
        }
    }
?>