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
     * Component form for filter definitions
     */
    class FilterForReportForm extends ComponentForReportForm implements OperatorInterface
    {
        /**
         * True if the filter is to be available as a run time filter that can be changed when running the report
         * @var bool
         */
        public $availableAtRunTime = false;

        /**
         * If the filter attribute is a currency attribute, then this property should be populated
         * @var string
         */
        public $currencyIdForValue;

        /**
         * @var mixed
         */
        public $value;

        /**
         * Depending on the operator, if it is between for example, there will be 2 values.
         * @var mixed
         */
        public $secondValue;

        /**
         * owner__User for example uses this property to define the owner's name which can then be used in the user
         * interface
         * @var string
         */
        public $stringifiedModelForValue;

        /**
         * Some attributes like date and DateTime use valueType to define the type of filter instead of using the
         * operator.
         * @var string
         */
        public $valueType;

        /**
         * @var string
         */
        private $_operator;

        /**
         * @var array
         */
        private $_availableOperatorsType;

        /**
         * @return string component type
         */
        public static function getType()
        {
            return static::TYPE_FILTERS;
        }

        /**
         * @return array
         */
        public function attributeNames()
        {
            return array_merge(parent::attributeNames(), array('operator'));
        }

        /**
         * Reset availableOperatorsType cache whenever a new attribute is set
         * (non-PHPdoc)
         * @see ComponentForReportForm::__set()
         */
        public function __set($name, $value)
        {
            parent::__set($name, $value);
            if ($name == 'attributeIndexOrDerivedType')
            {
                $this->_availableOperatorsType = null;
            }
        }

        /**
         * @param $value
         * @throws NotSupportedException
         */
        public function setOperator($value)
        {
            if (!in_array($value, OperatorRules::availableTypes()) && $value != null)
            {
                throw new NotSupportedException('Invalid operator type ' . $value);
            }
            $this->_operator = $value;
        }

        /**
         * @return string
         */
        public function getOperator()
        {
            return $this->_operator;
        }

        /**
         * @return array
         */
        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('operator',                    'type', 'type' => 'string'),
                array('operator',                    'validateOperator'),
                array('value',                       'safe'),
                array('value',                       'validateValue'),
                array('secondValue',                 'safe'),
                array('secondValue',                 'validateSecondValue'),
                array('currencyIdForValue',          'safe'),
                array('stringifiedModelForValue',    'safe'),
                array('availableAtRunTime',          'boolean'),
                array('valueType',                   'type', 'type' => 'string'),
                array('valueType',                   'validateValueType'),
            ));
        }

        /**
         * @return bool
         */
        public function validateOperator()
        {
            if ($this->getAvailableOperatorsType() != null && $this->operator == null)
            {
                $this->addError('operator', Zurmo::t('ReportsModule', 'Operator cannot be blank.'));
                return  false;
            }
        }

        /**
         * @return bool
         */
        public function validateValue()
        {
            if ((in_array($this->operator, self::getOperatorsWhereValueIsRequired()) ||
               in_array($this->valueType, self::getValueTypesWhereValueIsRequired()) ||
               ($this->getValueElementType() == 'BooleanForWizardStaticDropDown' ||
               $this->getValueElementType()  == 'UserNameId' ||
               ($this->getValueElementType() == 'MixedDateTypesForReport' && $this->valueType == null))) &&
               $this->value == null)
            {
                $this->addError('value', Zurmo::t('ReportsModule', 'Value cannot be blank.'));
            }
            $passedValidation = true;
            $rules            = array();
            if (!is_array($this->value))
            {
                $this->resolveAndValidateValueData($rules, $passedValidation, 'value');
            }
            else
            {
                //Assume array has only string values
                foreach ($this->value as $subValue)
                {
                    if (!is_string($subValue))
                    {
                        $this->addError('value', Zurmo::t('ReportsModule', 'Value must be a string.'));
                        $passedValidation = false;
                    }
                }
            }
            return $passedValidation;
        }

        /**
         * When the operator type is Between the secondValue is required. Also if the valueType, which is used by
         * date/datetime attributes is set to Between than the secondValue is required.
         * @return bool
         * @throws NotSupportedException
         */
        public function validateSecondValue()
        {
            $passedValidation = true;
            $rules            = array();
            if (!is_array($this->secondValue))
            {
                if (in_array($this->operator, self::getOperatorsWhereSecondValueIsRequired()) ||
                   in_array($this->valueType, self::getValueTypesWhereSecondValueIsRequired()))
                {
                    $rules[] = array('secondValue', 'required');
                }
                $this->resolveAndValidateValueData($rules, $passedValidation, 'secondValue');
            }
            else
            {
                throw new NotSupportedException();
            }
            return $passedValidation;
        }

        /**
         * @return bool
         */
        public function validateValueType()
        {
            if ($this->getValueElementType() == 'MixedDateTypesForReport' && $this->valueType == null)
            {
                $this->addError('valueType', Zurmo::t('ReportsModule', 'Type cannot be blank.'));
                return false;
            }
        }

        /**
         * @return bool
         */
        public function hasAvailableOperatorsType()
        {
            if ($this->getAvailableOperatorsType() != null)
            {
                return true;
            }
            return false;
        }

        /**
         * @return array
         * @throws NotSupportedException if the attributeIndexOrDerivedType has not been populated yet
         */
        public function getOperatorValuesAndLabels()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $type = $this->getAvailableOperatorsType();
            $data = array();
            ModelAttributeToReportOperatorTypeUtil::resolveOperatorsToIncludeByType($data, $type);
            return $data;
        }

        /**
         * @return null|string
         * @throws NotSupportedException if the attributeIndexOrDerivedType has not been populated yet
         */
        public function getValueElementType()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            return $modelToReportAdapter->getFilterValueElementType($this->getResolvedAttribute());
        }

        /**
         * @return array
         * @throws NotSupportedException if the resolved attribute is invalid and not on the resolved model
         */
        public function getCustomFieldDataAndLabels()
        {
            $modelClassName       = $this->getResolvedAttributeModelClassName();
            $attribute            = $this->getResolvedAttribute();
            if ($modelClassName::isAnAttribute($attribute))
            {
                $model            = new $modelClassName(false);
                $dataAndLabels    = CustomFieldDataUtil::
                                    getDataIndexedByDataAndTranslatedLabelsByLanguage($model->{$attribute}->data, Yii::app()->language);
                return $dataAndLabels;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return array|null|string
         * @throws NotSupportedException if the attributeIndexOrDerivedType has not been populated yet
         */
        protected function getAvailableOperatorsType()
        {
            if ($this->attributeIndexOrDerivedType == null)
            {
                throw new NotSupportedException();
            }
            if ($this->_availableOperatorsType != null)
            {
                return $this->_availableOperatorsType;
            }
            $modelToReportAdapter          = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            $availableOperatorsType        = $modelToReportAdapter->getAvailableOperatorsType($this->getResolvedAttribute());
            $this->_availableOperatorsType = $availableOperatorsType;
            return $availableOperatorsType;
        }

        /**
         * @return array
         */
        protected static function getValueTypesWhereValueIsRequired()
        {
            return MixedDateTypesSearchFormAttributeMappingRules::getValueTypesWhereValueIsRequired();
        }

        /**
         * @return array
         */
        protected static function getValueTypesWhereSecondValueIsRequired()
        {
            return MixedDateTypesSearchFormAttributeMappingRules::getValueTypesWhereSecondValueIsRequired();
        }

        /**
         * @return array
         */
        protected static function getOperatorsWhereValueIsRequired()
        {
            return OperatorRules::getOperatorsWhereValueIsRequired();
        }

        /**
         * @return array
         */
        protected static function getOperatorsWhereSecondValueIsRequired()
        {
            return OperatorRules:: getOperatorsWhereSecondValueIsRequired();
        }

        /**
         * @param array $rules
         * @return CList
         * @throws CException
         */
        private function createValueValidatorsByRules(Array $rules)
        {
            $validators = new CList;
            foreach ($rules as $rule)
            {
                if (isset($rule[0], $rule[1]))
                {
                    $validators->add(CValidator::createValidator($rule[1], $this, $rule[0], array_slice($rule, 2)));
                }
                else
                {
                    throw new CException(Zurmo::t('ReportsModule', '{class} has an invalid validation rule. The rule must specify ' .
                        'attributes to be validated and the validator name.' ,
                        array('{class}' => get_class($this))));
                }
            }
            return $validators;
        }

        /**
         * @param array $rules
         * @param $passedValidation
         * @param $ruleAttributeName
         */
        private function resolveAndValidateValueData(Array $rules, & $passedValidation, $ruleAttributeName)
        {
            $modelToReportAdapter = $this->makeResolvedAttributeModelRelationsAndAttributesToReportAdapter();
            $rules                = array_merge($rules,
                $modelToReportAdapter->getFilterRulesByAttribute(
                    $this->getResolvedAttribute(), $ruleAttributeName));
            $validators           = $this->createValueValidatorsByRules($rules);
            foreach ($validators as $validator)
            {
                $validated = $validator->validate($this);
                if (!$validated)
                {
                    $passedValidation = false;
                }
            }
        }
    }
?>