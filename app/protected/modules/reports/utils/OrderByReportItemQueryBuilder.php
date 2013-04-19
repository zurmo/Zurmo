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
     * Create the query string part for the SQL order by components
     */
    class OrderByReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @return string
         */
        protected static function resolveSortColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                        $modelAttributeToDataProviderAdapter)
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
         * @param string $tableAliasName
         * @param string $resolvedSortColumnName
         * @param null | string $queryStringExtraPart
         * @return string
         */
        protected function resolveOrderByString($tableAliasName, $resolvedSortColumnName, $queryStringExtraPart)
        {
            if ($this->modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $this->modelToReportAdapter->isAttributeACalculationOrModifier($this->componentForm->getResolvedAttribute()))
            {
                return $this->modelToReportAdapter->resolveOrderByStringForCalculationOrModifier(
                       $this->componentForm->getResolvedAttribute(), $tableAliasName,
                       $resolvedSortColumnName, $queryStringExtraPart);
            }
            else
            {
                return ModelDataProviderUtil::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
            }
        }

        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         * @return string
         */
        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $content = $this->resolveSortAttributeContent($modelAttributeToDataProviderAdapter, $onTableAliasName);
            return $content . ' ' . $this->componentForm->order;
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         * @return string
         */
        protected function resolveSortAttributeContent(RedBeanModelAttributeToDataProviderAdapter
                                                       $modelAttributeToDataProviderAdapter,
                                                       $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder                = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
            $tableAliasName         = $builder->resolveJoins($onTableAliasName, ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
            $resolvedSortColumnName = self::resolveSortColumnName($modelAttributeToDataProviderAdapter);
            $queryStringExtraPart   = $this->getAttributeClauseQueryStringExtraPart($tableAliasName);
            return $this->resolveOrderByString($tableAliasName, $resolvedSortColumnName, $queryStringExtraPart);
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter |
         * RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if ($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculationOrModifier($attribute))
            {
                $relatedAttribute = static::resolveRelatedAttributeForMakingAdapter($modelToReportAdapter, $attribute);
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute), $relatedAttribute);
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
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
                $modelToReportAdapter->resolveRealAttributeName($attribute), 'lastName');
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
            $sortAttribute = $modelToReportAdapter->getRules()->
                             getSortAttributeForRelationReportedAsAttribute(
                             $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                $attribute, $sortAttribute);
        }
    }
?>