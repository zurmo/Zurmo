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
     * Create the query string part for the SQL where part
     */
    class FilterReportItemQueryBuilder extends ReportItemQueryBuilder
    {
        /**
         * @var null | string
         */
        protected $filtersStructure;

        /**
         * @param $modelToReportAdapter
         * @param $modelAttributeToDataProviderAdapter
         * @param string $modelClassName
         * @param string $realAttributeName
         */
        public function resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                          $modelClassName,
                                                          $realAttributeName)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            if ($modelToReportAdapter->isAttributeReadOptimization($realAttributeName))
            {
                $hintAdapter        = new ReadOptimizationDerivedAttributeToDataProviderAdapter(
                                      $modelToReportAdapter->getModelClassName(), null);
                $hintModelClassName = $hintAdapter->getAttributeModelClassName();
                $modelAttributeToDataProviderAdapter->setCastingHintModelClassNameForAttribute($hintModelClassName);
            }
            else
            {
                return parent::resolveCastingHintForAttribute($modelToReportAdapter, $modelAttributeToDataProviderAdapter,
                                                              $modelClassName, $realAttributeName);
            }
        }

        /**
         * @param $modelAttributeToDataProviderAdapter
         * @param null | string $onTableAliasName
         * @return string
         */
        protected function resolveFinalContent($modelAttributeToDataProviderAdapter, $onTableAliasName = null)
        {
            if ($modelAttributeToDataProviderAdapter instanceof ReadOptimizationDerivedAttributeToDataProviderAdapter)
            {
                $builder        = new ReadOptimizationModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $this->joinTablesAdapter);
                $clausePosition = 1;
                $where          = array();
                $builder->resolveJoinsAndBuildWhere(null, null, $clausePosition, $where, $onTableAliasName);
                return $where[1];
            }
            else
            {
                $modelClassName  = $modelAttributeToDataProviderAdapter->getResolvedModelClassName();
                $metadataAdapter = new FilterForReportFormToDataProviderMetadataAdapter($this->componentForm);
                $attributeData   = $metadataAdapter->getAdaptedMetadata();
                //todo: right now in makeWhere we always set setDistinct to true when instantiating new ModelWhereAndJoinBuilder
                //todo: but when we are calling makeWhere from here we should not set that to true. or should it? TBD
                return ModelDataProviderUtil::makeWhere($modelClassName, $attributeData, $this->joinTablesAdapter,
                                                        $onTableAliasName);
            }
        }

        /**
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapterForRelationReportedAsAttribute($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            $sortAttribute = $modelToReportAdapter->getRules()->getSortAttributeForRelationReportedAsAttribute(
                             $modelToReportAdapter->getModel(), $attribute);
            return new RedBeanModelAttributeToDataProviderAdapter($modelToReportAdapter->getModelClassName(),
                             $attribute, $sortAttribute);
        }

        /**
         * In the event $modelToReportAdapter is summable and is a calculated group by modifier, unlike
         * DisplayAttributeReportItemQueryBuilder->makeModelAttributeToDataProviderAdapter, we do not need to
         * resolve the relatedAttribute when creating the RedBeanModelAttributeToDataProviderAdapter since currently
         * the attributes can only be date or dateTime.
         * @param $modelToReportAdapter
         * @param string $attribute
         * @return DerivedRelationViaCastedUpRedBeanModelAttributeToDataProviderAdapter |
         * ReadOptimizationDerivedAttributeToDataProviderAdapter | RedBeanModelAttributeToDataProviderAdapter
         */
        protected function makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute)
        {
            assert('$modelToReportAdapter instanceof ModelRelationsAndAttributesToReportAdapter');
            assert('is_string($attribute)');
            if ($modelToReportAdapter->isAttributeReadOptimization($attribute))
            {
                return new ReadOptimizationDerivedAttributeToDataProviderAdapter(
                           $modelToReportAdapter->getModelClassName(), null);
            }
            if ($modelToReportAdapter instanceof ModelRelationsAndAttributesToSummableReportAdapter &&
                $modelToReportAdapter->isAttributeACalculatedGroupByModifier($attribute))
            {
                return new RedBeanModelAttributeToDataProviderAdapter(
                    $modelToReportAdapter->getModelClassName(),
                    $modelToReportAdapter->resolveRealAttributeName($attribute));
            }
            return parent::makeModelAttributeToDataProviderAdapter($modelToReportAdapter, $attribute);
        }
    }
?>