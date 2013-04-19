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
     *
     */
    abstract class ReportItemQueryBuilder
    {
        /**
         * @var ComponentForReportForm
         */
        protected $componentForm;

        /**
         * @var ModelRelationsAndAttributesToReportAdapter
         */
        protected $modelToReportAdapter;

        /**
         * @var RedBeanModelJoinTablesQueryAdapter
         */
        protected $joinTablesAdapter;

        /**
         * @var null | integer
         */
        protected $currencyConversionType;

        /**
         * @param ComponentForReportForm $componentForm
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter
         * @param null | integer $currencyConversionType
         */
        public function __construct(ComponentForReportForm $componentForm,
                                    RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter,
                                    $currencyConversionType = null)
        {
            assert('is_int($currencyConversionType) || $currencyConversionType == null');
            $this->componentForm          = $componentForm;
            $this->joinTablesAdapter      = $joinTablesAdapter;
            $this->modelToReportAdapter   = $modelToReportAdapter;
            $this->currencyConversionType = $currencyConversionType;
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return null|string
         */
        protected static function resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if ($modelToReportAdapter->relationIsReportedAsAttribute(
                $modelToReportAdapter->resolveRealAttributeName($attribute)))
            {
                return 'value';
            }
            else
            {
                return null;
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return RedBeanModelAttributeToDataProviderAdapter
         */
        protected static function makeModelAttributeToDataProviderAdapterForDynamicallyDerivedAttribute(
            $modelToReportAdapter, $attribute)
        {
            return new RedBeanModelAttributeToDataProviderAdapter(
                $modelToReportAdapter->getModelClassName(),
                $modelToReportAdapter->resolveRealAttributeName($attribute), 'id');
        }

        /**
         * @return string
         * @throws NotSupportedException if the $attributeAndRelationData
         */
        public function resolveComponentAttributeStringContent()
        {
            $attributeAndRelationData = $this->componentForm->getAttributeAndRelationData();
            if (!is_array($attributeAndRelationData))
            {
                return $this->resolveComponentAttributeStringContentForNonNestedAttribute();
            }
            elseif (count($attributeAndRelationData) > 1)
            {
                return $this->resolveComponentAttributeStringContentForNestedAttribute();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
                           $modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                       $attribute);
        }

        /**
         * @return string
         */
        protected function resolveComponentAttributeStringContentForNonNestedAttribute()
        {
            $attribute                           = $this->componentForm->getAttributeAndRelationData();
            $modelAttributeToDataProviderAdapter = $this->makeModelAttributeToDataProviderAdapter(
                                                   $this->modelToReportAdapter, $attribute);
            return $this->resolveFinalContent($modelAttributeToDataProviderAdapter);
        }

        /**
         * @return string
         * @throws NotSupportedException
         */
        protected function resolveComponentAttributeStringContentForNestedAttribute()
        {
            $attributeAndRelationData = $this->componentForm->getAttributeAndRelationData();
            $count                    = 0;
            $moduleClassName          = $this->componentForm->getModuleClassName();
            $modelClassName           = $this->componentForm->getModelClassName();
            $onTableAliasName         = null;
            $startingModelClassName   = null;
            foreach ($attributeAndRelationData as $key => $relationOrAttribute)
            {
                $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::
                    make($moduleClassName, $modelClassName, $this->componentForm->getReportType());
                $modelAttributeToDataProviderAdapter  = $this->makeModelAttributeToDataProviderAdapter(
                                                        $modelToReportAdapter, $relationOrAttribute);
                if ($this->shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                    $modelAttributeToDataProviderAdapter))
                {
                    $attribute                            = 'id';
                    $modelAttributeToDataProviderAdapter  = $this->makeModelAttributeToDataProviderAdapter(
                                                            $modelToReportAdapter, $attribute);
                    break;
                }
                elseif ($modelToReportAdapter->isReportedOnAsARelation($relationOrAttribute))
                {
                    $modelClassName   = $modelToReportAdapter->getRelationModelClassName($relationOrAttribute);
                    $moduleClassName  = $modelToReportAdapter->getRelationModuleClassName($relationOrAttribute);
                    if ($modelToReportAdapter->isInferredRelation($relationOrAttribute) ||
                        $modelToReportAdapter->isDerivedRelationsViaCastedUpModelRelation($relationOrAttribute))
                    {
                        static::resolveCastingHintForAttribute($modelToReportAdapter,
                            $modelAttributeToDataProviderAdapter,
                            $modelClassName,
                            $modelToReportAdapter->resolveRealAttributeName(
                                $attributeAndRelationData[$key + 1]));
                    }
                    $modelAttributeToDataProviderAdapter->setCastingHintStartingModelClassName($startingModelClassName);
                    $builder                = new ModelJoinBuilder($modelAttributeToDataProviderAdapter,
                        $this->joinTablesAdapter);
                    $onTableAliasName       = $builder->resolveJoins($onTableAliasName,
                        ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                    $startingModelClassName = $modelAttributeToDataProviderAdapter->getCastingHintModelClassNameForAttribute();
                }
                else
                {
                    if ($count + 1 != count($attributeAndRelationData))
                    {
                        throw new NotSupportedException('The final element in array must be an attribute, not a relation');
                    }
                }
                $count++;
            }
            $modelAttributeToDataProviderAdapter->setCastingHintStartingModelClassName($startingModelClassName);
            return $this->resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName);
        }

        /**
         * @param $modelToReportAdapter
         * @param $attribute
         * @return DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter|
         *         InferredRedBeanModelAttributeToDataProviderAdapter|RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if ($modelToReportAdapter->isInferredRelation($attribute))
            {
                return new InferredRedBeanModelAttributeToDataProviderAdapter(
                           $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute),
                           $modelToReportAdapter->getRelationModelClassName($attribute),
                           $modelToReportAdapter->getRelationModuleClassName($attribute));
            }
            elseif ($modelToReportAdapter->isDerivedRelationsViaCastedUpModelRelation($attribute))
            {
                return new DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $attribute);
            }
            //Example is full name or calculated number
            elseif ($modelToReportAdapter->isDerivedAttribute($attribute))
            {
                //Derived attributes are assumed to be made via model so we only need the id of the model here.
                return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(), 'id');
            }
            //Example: createdUser__User
            elseif ($modelToReportAdapter->isDynamicallyDerivedAttribute($attribute))
            {
                return static::makeModelAttributeToDataProviderAdapterForDynamicallyDerivedAttribute(
                               $modelToReportAdapter, $attribute);
            }
            //Example: CustomField, CurrencyValue, OwnedCustomField, or likeContactState
            elseif ($modelToReportAdapter->relationIsReportedAsAttribute($attribute))
            {
                return $this->makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute(
                              $modelToReportAdapter, $attribute);
            }
            //Example: name or phone
            else
            {
                return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                           $attribute);
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param $modelAttributeToDataProviderAdapter
         * @param string $modelClassName
         * @param string $realAttributeName
         */
        protected function resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                          $modelClassName,
                                                          $realAttributeName)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            $hintAdapter        = new RedBeanModelAttributeToDataProviderAdapter($modelClassName, $realAttributeName);
            $hintModelClassName = $hintAdapter->getAttributeModelClassName();
            $modelAttributeToDataProviderAdapter->setCastingHintModelClassNameForAttribute($hintModelClassName);
        }

        /**
         * @param $modelToReportAdapter
         * @param $modelAttributeToDataProviderAdapter
         * @return bool
         */
        protected function shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          $modelAttributeToDataProviderAdapter)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            return false;
        }

        /**
         * @param string $tableAliasName
         * @return string
         */
        protected function getAttributeClauseQueryStringExtraPart($tableAliasName)
        {
            assert('is_string($tableAliasName)');
            if ($this->componentForm->isATypeOfCurrencyValue() &&
                ($this->currencyConversionType == Report::CURRENCY_CONVERSION_TYPE_BASE ||
                    $this->currencyConversionType == Report::CURRENCY_CONVERSION_TYPE_SPOT))
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                $currencyValue = new CurrencyValue();
                return " * {$quote}{$tableAliasName}{$quote}." .
                    "{$quote}" . $currencyValue->getColumnNameByAttribute('rateToBase') . "{$quote}";
            }
        }
    }
?>