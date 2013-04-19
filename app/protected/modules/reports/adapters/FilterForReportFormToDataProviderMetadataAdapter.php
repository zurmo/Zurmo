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
     * Helper class for adapting a FilterForReportForm into search metadata
     */
    class FilterForReportFormToDataProviderMetadataAdapter
    {
        /**
         * @var FilterForReportForm
         */
        protected $filter;

        /**
         * @var string
         */
        protected $structure;

        /**
         * @var int
         */
        protected $structureCount = 1;

        /**
         * @var array
         */
        protected $clauses = array();

        /**
         * @var ModelRelationsAndAttributesToReportAdapter
         */
        protected $modelRelationsAndAttributesToReportAdapter;

        /**
         * @param FilterForReportForm $filter
         */
        public function __construct(FilterForReportForm $filter)
        {
            $this->filter = $filter;
            $this->modelRelationsAndAttributesToReportAdapter = $this->makeModelRelationsAndAttributesToReportAdapter();
        }

        /**
         * @return array
         */
        public function getAdaptedMetadata()
        {
            $this->resetClausesAndStructure();
            $this->resolveClausesAndStructure();
            $adaptedMetadata              = array();
            $adaptedMetadata['clauses']   = $this->clauses;
            $adaptedMetadata['structure'] = $this->structure;
            if (count($adaptedMetadata['clauses']) > 1)
            {
                $adaptedMetadata['structure'] = '(' . $adaptedMetadata['structure'] . ')';
            }
            return $adaptedMetadata;
        }

        protected function resolveValueForOperator()
        {
            if ($this->filter->getOperator() == OperatorRules::TYPE_IS_NULL ||
               $this->filter->getOperator() == OperatorRules::TYPE_IS_NOT_NULL)
            {
                return null;
            }
            return $this->filter->value;
        }

        protected function resolveClausesAndStructure()
        {
            $attribute = $this->filter->getResolvedAttribute();
            //is Dynamically Derived Attributes? __User
            if ($this->modelRelationsAndAttributesToReportAdapter->isDynamicallyDerivedAttribute($attribute))
            {
                $this->resolveDynamicallyDerivedAttributeClauseAndStructure();
            }
            elseif ($this->modelRelationsAndAttributesToReportAdapter instanceof
                   ModelRelationsAndAttributesToSummableReportAdapter &&
                   $this->modelRelationsAndAttributesToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                          'operatorType'         => $this->filter->getOperator(),
                                          'value'                => $this->resolveValueForOperator(),
                                          'modifierType'         => $this->modelRelationsAndAttributesToReportAdapter->
                                                                    getCalculationOrModifierType($attribute));
                $this->structure  = '1';
            }
            //likeContactState, a variation of dropDown, or currencyValue
            elseif ($this->modelRelationsAndAttributesToReportAdapter->relationIsReportedAsAttribute($attribute))
            {
                $this->resolveRelationReportedAsAttributeClauseAndStructure();
            }
            else
            {
                $this->resolveNonRelationNonDerivedAttributeClauseAndStructure();
            }
        }

        protected function resolveDynamicallyDerivedAttributeClauseAndStructure()
        {
            $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                      'relatedAttributeName' => 'id',
                                      'operatorType'         => $this->filter->getOperator(),
                                      'value'                => $this->resolveValueForOperator());
            $this->structure  = '1';
        }

        protected function resolveRelationReportedAsAttributeClauseAndStructure()
        {
            if ($this->filter->getValueElementType() == 'MixedCurrencyValueTypes')
            {
                $this->resolveCurrencyValueAttributeClauseAndStructure();
            }
            elseif ($this->filter->getValueElementType() == 'StaticDropDownForReport')
            {
                $this->resolveDropDownVariantAttributeClauseAndStructure();
            }
            else
            {
                //handles likeContactState for example
                $this->resolveRelatedIdAttributeClauseAndStructure();
            }
        }

        protected function resolveNonRelationNonDerivedAttributeClauseAndStructure()
        {
            if ($this->filter->getValueElementType() == 'MixedDateTypesForReport')
            {
                $this->resolveDateAttributeClauseAndStructure();
            }
            elseif ($this->filter->getValueElementType() == 'MixedNumberTypes')
            {
                $this->resolveNumericAttributeClauseAndStructure();
            }
            elseif ($this->filter->getValueElementType() == 'BooleanForWizardStaticDropDown')
            {
                $this->resolveBooleanAttributeClauseAndStructure();
            }
            else
            {
                $this->resolveTextAttributeClauseAndStructure();
            }
        }

        protected function resolveDateAttributeClauseAndStructure()
        {
            $rulesClassName         = $this->getDateOrDateTimeRulesClassName();
            $value                  = array();
            $value['type']          = $this->filter->valueType;
            $value['firstDate']     = $this->filter->value;
            $value['secondDate']    = $this->filter->secondValue;
            $attributesAndRelations  = 'resolveEntireMappingByRules';
            $rulesClassName::resolveAttributesAndRelations('notUsed__notUsed', $attributesAndRelations, $value);
            $count = 1;
            foreach ($attributesAndRelations as $attributeAndRelation)
            {
                $this->clauses[$count] = array('attributeName' => $this->getRealAttributeName(),
                                                              'operatorType'  => $attributeAndRelation[2],
                                                              'value'         => $this->resolveForValueByRules($rulesClassName,
                                                              $attributeAndRelation, $value));
                if ($this->structure == null)
                {
                    $this->structure  = $count;
                }
                else
                {
                    $this->structure  .= ' and ' . $count;
                }
                $count++;
            }
        }

        /**
         * @return string
         * @throws NotSupportedException if the displayElementType is not Date or DateTime, which means it is invalid
         */
        protected function getDateOrDateTimeRulesClassName()
        {
            $displayElementType = $this->modelRelationsAndAttributesToReportAdapter->getDisplayElementType(
                                  $this->filter->getResolvedAttribute());
            if ($displayElementType == 'Date')
            {
                return 'MixedDateTypesSearchFormAttributeMappingRules';
            }
            elseif ($displayElementType == 'DateTime')
            {
                return 'MixedDateTimeTypesSearchFormAttributeMappingRules';
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Utilized to process date and dateTime clauses properly
         * @param string $rulesClassName
         * @param array $attributesAndRelations
         * @param mixed $value
         * @return mixed
         */
        protected function resolveForValueByRules($rulesClassName, $attributesAndRelations, $value)
        {
            if ($attributesAndRelations[3] == 'resolveValueByRules')
            {
                return $rulesClassName::resolveValueDataIntoUsableValue($value);
            }
            elseif ($attributesAndRelations[3] != null)
            {
                return $attributesAndRelations[3];
            }
        }

        protected function resolveNumericAttributeClauseAndStructure()
        {
            if ($this->filter->getOperator() == OperatorRules::TYPE_BETWEEN)
            {
                $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                          'operatorType'         => OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                                          'value'                => $this->resolveValueForOperator());
                $this->clauses[2] = array('attributeName'        => $this->getRealAttributeName(),
                                          'operatorType'         => OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                                          'value'                => $this->filter->secondValue);
                $this->structure  = '1 and 2';
            }
            else
            {
                $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                          'operatorType'         => $this->filter->getOperator(),
                                          'value'                => $this->resolveValueForOperator());
                $this->structure  = '1';
            }
        }

        protected function resolveCurrencyValueAttributeClauseAndStructure()
        {
            if ($this->filter->getOperator() == OperatorRules::TYPE_BETWEEN)
            {
                $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                          'relatedAttributeName' => 'value',
                                          'operatorType'         => OperatorRules::TYPE_GREATER_THAN_OR_EQUAL_TO,
                                          'value'                => $this->resolveValueForOperator());
                $this->clauses[2] = array('attributeName'        => $this->getRealAttributeName(),
                                          'relatedAttributeName' => 'value',
                                          'operatorType'         => OperatorRules::TYPE_LESS_THAN_OR_EQUAL_TO,
                                          'value'                => $this->filter->secondValue);
                $this->structure  = '1 and 2';
                $count            = 3;
            }
            else
            {
                $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                          'relatedAttributeName' => 'value',
                                          'operatorType'         => $this->filter->getOperator(),
                                          'value'                => $this->resolveValueForOperator());
                $this->structure  = '1';
                $count            = 2;
            }
            if ($this->filter->currencyIdForValue != null)
            {
                $this->clauses[$count] = array('attributeName'   => $this->getRealAttributeName(),
                                               'relatedModelData'     => array(
                                                    'attributeName' => 'currency',
                                                    'relatedAttributeName' => 'id',
                                                    'operatorType'         => OperatorRules::TYPE_EQUALS,
                                                    'value'                => $this->filter->currencyIdForValue));
                $this->structure  .= ' and ' . $count;
            }
        }

        protected function resolveDropDownVariantAttributeClauseAndStructure()
        {
            $relationClassName =    $this->modelRelationsAndAttributesToReportAdapter->getModel()->
                                    getRelationModelClassName($this->getRealAttributeName());

            if ($relationClassName == 'MultipleValuesCustomField' ||
               is_subclass_of ($relationClassName, 'MultipleValuesCustomField'))
            {
                $relatedAttributeName = 'values';
            }
            else
            {
                $relatedAttributeName = 'value';
            }
            $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                      'relatedAttributeName' => $relatedAttributeName,
                                      'operatorType'         => $this->filter->getOperator(),
                                      'value'                => $this->resolveValueForOperator());

            $this->structure  = '1';
        }

        protected function resolveTextAttributeClauseAndStructure()
        {
            $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                      'operatorType'         => $this->filter->getOperator(),
                                      'value'                => $this->resolveValueForOperator());
            $this->structure  = '1';
        }

        protected function resolveBooleanAttributeClauseAndStructure()
        {
            $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                      'operatorType'         => $this->filter->getOperator(),
                                      'value'                => (bool)$this->resolveValueForOperator());
            $this->structure  = '1';
        }

        protected function resolveRelatedIdAttributeClauseAndStructure()
        {
            $this->clauses[1] = array('attributeName'        => $this->getRealAttributeName(),
                                      'relatedAttributeName' => 'id',
                                      'operatorType'         => $this->filter->getOperator(),
                                      'value'                => $this->resolveValueForOperator());
            $this->structure  = '1';
        }

        /**
         * @return string
         */
        protected function getRealAttributeName()
        {
            return $this->modelRelationsAndAttributesToReportAdapter->resolveRealAttributeName(
                   $this->filter->getResolvedAttribute());
        }

        protected function resetClausesAndStructure()
        {
            $this->clauses    = array();
            $this->structure  = null;
        }

        /**
         * @return ModelRelationsAndAttributesToReportAdapter
         */
        protected function makeModelRelationsAndAttributesToReportAdapter()
        {
            return ModelRelationsAndAttributesToReportAdapter::make(
                $this->filter->getResolvedAttributeModuleClassName(),
                $this->filter->getResolvedAttributeModelClassName(),
                $this->filter->getReportType());
        }
    }