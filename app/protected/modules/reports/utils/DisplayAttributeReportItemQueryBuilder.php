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
     * Populate the RedBeanModelSelectQueryAdapter with the necessary columns or calculations to select
     */
    class DisplayAttributeReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        /**
         * @var RedBeanModelSelectQueryAdapter
         */
        protected $selectQueryAdapter;

        /**
         * @param ComponentForReportForm $componentForm
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         * @param null | string $currencyConversionType
         */
        public function __construct(ComponentForReportForm $componentForm,
                                    RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    ModelRelationsAndAttributesToReportAdapter $modelToReportAdapter,
                                    RedBeanModelSelectQueryAdapter $selectQueryAdapter,
                                    $currencyConversionType = null)
        {
            parent::__construct($componentForm, $joinTablesAdapter, $modelToReportAdapter, $currencyConversionType);
            $this->selectQueryAdapter = $selectQueryAdapter;
        }

        /**
         * @return bool
         */
        protected function isDisplayAttributeMadeViaSelect()
        {
            if ($this->componentForm->madeViaSelectInsteadOfViaModel)
            {
                return true;
            }
            if ($this->modelToReportAdapter->isDisplayAttributeMadeViaSelect($this->componentForm->getResolvedAttribute()))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         */
        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            $this->resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter, $onTableAliasName);
        }

        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         */
        protected function resolveDisplayAttributeColumnName($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder              = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            if ($this->shouldPrematurelyStopBuildingJoinsForAttribute($this->modelToReportAdapter, $modelAttributeToDataProviderAdapter))
            {
                $this->resolveDisplayAttributeForPrematurelyStoppingJoins($modelAttributeToDataProviderAdapter,
                                                                          $onTableAliasName);
            }
            else
            {
                $this->resolveDisplayAttributeForProcessingAllJoins(      $builder,
                                                                          $modelAttributeToDataProviderAdapter,
                                                                          $onTableAliasName);
            }
        }

        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         */
        protected function resolveDisplayAttributeForPrematurelyStoppingJoins($modelAttributeToDataProviderAdapter,
                                                                              $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if ($onTableAliasName == null)
            {
                $onTableAliasName     = $modelAttributeToDataProviderAdapter->getModelTableName();
            }
            $this->selectQueryAdapter->resolveIdClause($modelAttributeToDataProviderAdapter->getModelClassName(),
                                                       $onTableAliasName);
            $this->componentForm->setModelAliasUsingTableAliasName($onTableAliasName);
        }

        /**
         * @param ModelJoinBuilder $builder
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         * @throws NotSupportedException if the display attribute is made via select like SUM(integer) but the
         * adapter being used is not a summation adapter
         */
        protected function resolveDisplayAttributeForProcessingAllJoins(ModelJoinBuilder $builder,
                                                                        $modelAttributeToDataProviderAdapter,
                                                                        $onTableAliasName = null)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $tableAliasName                 = $builder->resolveJoins($onTableAliasName,
                                              ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            if ($this->isDisplayAttributeMadeViaSelect())
            {
                if (!$this->modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter)
                {
                    throw new NotSupportedException();
                }
                $this->modelToReportAdapter->resolveDisplayAttributeTypeAndAddSelectClause(
                                  $this->selectQueryAdapter,
                                  $this->componentForm->getResolvedAttribute(),
                                  $tableAliasName,
                                  $this->resolveColumnName($modelAttributeToDataProviderAdapter),
                                  $this->componentForm->columnAliasName,
                                  $this->getAttributeClauseQueryStringExtraPart($tableAliasName));
            }
            else
            {
                $tableAliasName = $this->resolvedTableAliasName($modelAttributeToDataProviderAdapter, $builder);
                $this->selectQueryAdapter->resolveIdClause(
                    $this->resolvedModelClassName($modelAttributeToDataProviderAdapter),
                    $tableAliasName);
                $this->componentForm->setModelAliasUsingTableAliasName($tableAliasName);
            }
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @return string
         */
        protected function resolveColumnName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if ($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getColumnName();
            }
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @return string
         */
        protected function resolvedModelClassName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter)
        {
            if ($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $modelAttributeToDataProviderAdapter->getRelationModelClassName();
            }
            else
            {
                return $modelAttributeToDataProviderAdapter->getModelClassName();
            }
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param ModelJoinBuilder $builder
         * @return string
         */
        protected function resolvedTableAliasName(RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter,
                                                  ModelJoinBuilder $builder)
        {
            if ($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $builder->getTableAliasNameForRelatedModel();
            }
            else
            {
                return $builder->getTableAliasNameForBaseModel();
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter|RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if ($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
               $modelToReportAdapter->isAttributeACalculationOrModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                $adapterClassName = get_class($modelToReportAdapter);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $adapterClassName::resolveRealAttributeName($attribute),
                    $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }

        /**
         * @param $modelToReportAdapter
         * @param $modelAttributeToDataProviderAdapter
         * @return bool
         */
        protected function shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          $modelAttributeToDataProviderAdapter)
        {
            assert('$modelAttributeToDataProviderAdapter instanceof RedBeanModelAttributeToDataProviderAdapter');
            if ($this->isDisplayAttributeMadeViaSelect())
            {
                return false;
            }
            if ($modelAttributeToDataProviderAdapter instanceof
               DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            elseif ($modelAttributeToDataProviderAdapter instanceof
                   InferredRedBeanModelAttributeToDataProviderAdapter)
            {
                return false;
            }
            //If casted up non-relation
            elseif ($modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() &&
               !$modelAttributeToDataProviderAdapter->isRelation())
            {
                return true;
            }
            //Owned relations such as Address or Email
            elseif ($modelAttributeToDataProviderAdapter->isOwnedRelation() &&
                   !$modelAttributeToDataProviderAdapter->isRelationTypeAHasManyVariant())
            {
                return true;
            }
            //likeContactState for example. It is not covered by ownedRelation above but should stop prematurely
            elseif ($modelToReportAdapter->relationIsReportedAsAttribute($modelAttributeToDataProviderAdapter->getAttribute()))
            {
                return true;
            }
            //if a User relation
            elseif ($modelAttributeToDataProviderAdapter->isRelation() &&
                   $modelAttributeToDataProviderAdapter->getRelationModelClassName() == 'User')
            {
                return true;
            }
            return parent::shouldPrematurelyStopBuildingJoinsForAttribute($modelToReportAdapter,
                                                                          $modelAttributeToDataProviderAdapter);
        }

        /**
         * @param $modelToReportAdapter
         * @param $modelAttributeToDataProviderAdapter
         * @param string $modelClassName
         * @param string $realAttributeName
         */
        protected function resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                          $modelClassName, $realAttributeName)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            if ($this->isDisplayAttributeMadeViaSelect())
            {
                return parent::resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                              $modelClassName, $realAttributeName);
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
            if ($this->componentForm->madeViaSelectInsteadOfViaModel)
            {
                $resolvedRelatedAttribute = $modelToReportAdapter->getRules()->
                    getGroupByRelatedAttributeForRelationReportedAsAttribute(
                    $modelToReportAdapter->getModel(), $attribute);
            }
            else
            {
                $resolvedRelatedAttribute = null;
            }
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $resolvedRelatedAttribute);
        }
    }
?>