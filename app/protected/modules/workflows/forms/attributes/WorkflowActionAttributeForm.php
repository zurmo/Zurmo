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
     * Base class for working with action attributes.
     */
    abstract class WorkflowActionAttributeForm extends ConfigurableMetadataModel
    {
        const TYPE_STATIC      = 'Static';

        const TYPE_STATIC_NULL = 'StaticNull';

        abstract public function getValueElementType();

        abstract protected function makeTypeValuesAndLabels($isCreatingNewModel, $isRequired);

        /**
         * @var string Static for example, Can also be Dynamic as well as other types specified by children
         */
        public $type;

        /**
         * @var mixed
         */
        public $value;

        /**
         * owner__User for example uses this property to define the owner's name which can then be used in the user
         * interface
         * @var string
         */
        protected $stringifiedModelForValue;

        /**
         * @var boolean if the attribute should have a value whether static or dynamic. In the user interface this surfaces
         * as a checkbox next to each attribute in the workflow wizard
         */
        public $shouldSetValue;

        /**
         * Refers to the model that is associated with the action attribute. If your action attribut is on accounts, then
         * this is going to be the Account model class name. However this could also be a relation model class name
         * if the AttributeIndex is referencing a related attribute.
         * @var string
         */
        protected $modelClassName;

        /**
         * Mapped attribute name or related attribute name if the attributeIndex is for a relation attribute.
         * @see $modelClassName
         * @var string
         */
        protected $modelAttributeName;

        /**
         * An example could be Primary Address >> Street 1
         * @var string
         */
        protected $displayLabel;

        /**
         * @return string - If the class name is BooleanWorkflowActionAttributeForm,
         * then 'Boolean' will be returned.
         */
        public static function getFormType()
        {
            $type = get_called_class();
            $type = substr($type, 0, strlen($type) - strlen('WorkflowActionAttributeForm'));
            return $type;
        }

        /**
         * @param string $modelClassName
         * @param string $modelAttributeName
         */
        public function __construct($modelClassName, $modelAttributeName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($modelAttributeName)');
            $this->modelClassName     = $modelClassName;
            $this->modelAttributeName = $modelAttributeName;
        }

        /**
         * Method needed so the validation routines can properly interact with the alternateValue and properly
         * set the correct errors.
         * @return mixed
         */
        public function getAlternateValue()
        {
            return $this->value;
        }

        /**
         * @return string
         */
        public function getDisplayLabel()
        {
            return $this->displayLabel;
        }

        /**
         * @return string
         */
        public function getStringifiedModelForValue()
        {
            return $this->stringifiedModelForValue;
        }

        /**
         * @param $displayLabel
         */
        public function setDisplayLabel($displayLabel)
        {
            assert('is_string($displayLabel)');
            $this->displayLabel = $displayLabel;
        }

        /**
         * @return string
         */
        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        /**
         * @return string
         */
        public function getModelAttributeName()
        {
            return $this->modelAttributeName;
        }

        /**
         * Override to properly handle retrieving rule information from the model for the attribute name.
         */
        public function rules()
        {
            $rules = array_merge(parent::rules(), array(
                array('type',                     'type', 'type' => 'string'),
                array('type',                     'required'),
                array('value',                    'safe'),
                array('value',                    'validateValue'),
                array('shouldSetValue',           'boolean'),
            ));
            $applicableRules = ModelAttributeRulesToWorkflowActionAttributeUtil::
                getApplicableRulesByModelClassNameAndAttributeName(
                $this->modelClassName,
                $this->modelAttributeName,
                'value');
            return array_merge($rules, $applicableRules);
        }

        /**
         * @return array
         */
        public function attributeLabels()
        {
            return array('alternateValue' => Zurmo::t('Core', 'Value'));
        }

        /**
         * Value is required based on the type. Override in children as needed to add more scenarios.
         * @return bool
         */
        public function validateValue()
        {
            if ($this->type == self::TYPE_STATIC && empty($this->value) && $this->shouldSetValue)
            {
                $this->addError('value', Zurmo::t('WorkflowsModule', 'Value cannot be blank.'));
                return false;
            }
            return true;
        }

        /**
         * @param bool $isCreatingNewModel
         * @param bool $isRequired Is the attribute required or not. Some types are not available if the attribute is
         * required.
         * @return array
         */
        public function getTypeValuesAndLabels($isCreatingNewModel, $isRequired)
        {
            assert('is_bool($isCreatingNewModel)');
            assert('is_bool($isRequired)');
            return $this->makeTypeValuesAndLabels($isCreatingNewModel, $isRequired);
        }

        /**
         * Utilized to create or update model attribute values after a workflow's triggers are fired as true.
         * @param WorkflowActionProcessingModelAdapter $adapter
         * @param $attribute
         * @throws NotSupportedException
         */
        public function resolveValueAndSetToModel(WorkflowActionProcessingModelAdapter $adapter, $attribute)
        {
            assert('is_string($attribute)');
            if ($this->type == WorkflowActionAttributeForm::TYPE_STATIC)
            {
                $adapter->getModel()->{$attribute} = $this->value;
            }
            elseif ($this->type == WorkflowActionAttributeForm::TYPE_STATIC_NULL)
            {
                $adapter->getModel()->{$attribute} = null;
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>