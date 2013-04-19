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
     * Base class for constructing a ReportDataProvider
     */
    abstract class ReportDataProvider extends CDataProvider
    {
        /**
         * In each child class, this method can be used to determine if the report specified is valid for this
         * reportDataProvider
         * @return boolean
         */
        abstract protected function isReportValidType();

        /**
         * @var Report
         */
        protected $report;

        /**
         * Set to true if you want the data provider to get data.
         * @var bool
         */
        protected $runReport = false;

        /**
         * @var integer | null
         */
        protected $offset;

        /**
         * @var array
         */
        private $_rowsData;

        /**
         * @param Report $report
         * @param array $config
         */
        public function __construct(Report $report, array $config = array())
        {
            $this->report = $report;
            $this->isReportValidType();
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }

        /**
         * @param bool $runReport
         */
        public function setRunReport($runReport)
        {
            assert('is_bool($runReport)');
            $this->runReport = $runReport;
        }

        /**
         * @return Report
         */
        public function getReport()
        {
            return $this->report;
        }

        /**
         * @return array
         */
        public function resolveDisplayAttributes()
        {
            return $this->report->getDisplayAttributes();
        }

        /**
         * @return array
         */
        public function resolveGroupBys()
        {
            return $this->report->getGroupBys();
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         * @return int|string
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, true);
            $count = R::getCell($sql);
            if ($count === null || empty($count))
            {
                $count = 0;
            }
            return $count;
        }

        /**
         * @return string
         */
        public function makeTotalCountSqlQueryForDisplay()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            return $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, true);
        }

        /**
         * @return string
         */
        public function makeSqlQueryForDisplay()
        {
            $offset                 = $this->resolveOffset();
            $limit                  = $this->resolveLimit();
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            return $this->makeSqlQueryForFetchingData($selectQueryAdapter, $offset, $limit);
        }

        /**
         * Public for testing purposes only
         * @param $filters
         * @param $filtersStructure
         * @return array
         */
        public function resolveFiltersForReadPermissions(array $filters, & $filtersStructure)
        {
            $attributeIndexes     = $this->makeReadPermissionsAttributeIndexes($filters);
            $existingFiltersCount = count($filters);
            $structurePosition    = $existingFiltersCount + 1;
            $readStructure        = null;
            foreach ($attributeIndexes as $attributeIndexOrDerivedTypePrefix => $attributeOrDerivedAttributeTypes)
            {
                $structure = null;
                foreach ($attributeOrDerivedAttributeTypes as $attributeOrDerivedAttributeType)
                {
                    if ($structure != null)
                    {
                        $structure .= ' or ';
                    }
                    $structure .= $structurePosition;
                    $structurePosition++;
                    $filters[]  = $this->resolveFilterForReadPermissionAttributeIndex($attributeIndexOrDerivedTypePrefix,
                        $attributeOrDerivedAttributeType);
                }
                if ($structure != null)
                {
                    if ($readStructure != null)
                    {
                        $readStructure .= ' and ';
                    }
                    $readStructure .= '(' . $structure . ')';
                }
            }
            if ($readStructure != null)
            {
                if ($filtersStructure != null)
                {
                    $filtersStructure .= ' and (' . $readStructure . ')';
                }
                else
                {
                    $filtersStructure .= $readStructure;
                }
            }
            return $filters;
        }

        /**
         * Public for testing purposes only
         * @param $filters
         * @param $filtersStructure
         * @return array
         */
        public function resolveFiltersForVariableStates($filters, & $filtersStructure)
        {
            $attributeIndexes     = $this->makeVariableStatesAttributeIndexes($filters);
            $existingFiltersCount = count($filters);
            $structurePosition    = $existingFiltersCount + 1;
            $readStructure        = null;
            foreach ($attributeIndexes as $attributeIndexOrDerivedTypePrefix => $variableStateData)
            {
                $structure = $structurePosition;
                $structurePosition++;
                $filters[]  = $this->resolveFilterForVariableStateAttributeIndex($attributeIndexOrDerivedTypePrefix,
                    $variableStateData);
                if ($readStructure != null)
                {
                    $readStructure .= ' and ';
                }
                $readStructure .= $structure;
            }
            if ($readStructure != null)
            {
                if ($filtersStructure != null)
                {
                    $filtersStructure .= ' and (' . $readStructure . ')';
                }
                else
                {
                    $filtersStructure .= $readStructure;
                }
            }
            return $filters;
        }

        /**
         * @return array
         */
        protected function fetchData()
        {
            $offset = $this->resolveOffset();
            $limit  = $this->resolveLimit();
            if ($this->getTotalItemCount() == 0)
            {
                return array();
            }
            return $this->runQueryAndGetResolveResultsData($offset, $limit);
        }

        /**
         * @return int|null
         */
        protected function resolveOffset()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $totalItemCount = $this->getTotalItemCount();
                $pagination->setItemCount($totalItemCount);
                $offset = $pagination->getOffset();
            }
            else
            {
                $offset = null;
            }
            if ($this->offset != null)
            {
                $offset = $this->offset;
            }
            return $offset;
        }

        /**
         * @return int|null
         */
        protected function resolveLimit()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $totalItemCount = $this->getTotalItemCount();
                $pagination->setItemCount($totalItemCount);
                $limit  = $pagination->getLimit();
            }
            else
            {
                $limit  = null;
            }
            return $limit;
        }

        /**
         * @param $offset
         * @param $limit
         * @return array
         */
        protected function runQueryAndGetResolveResultsData($offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql          = $this->makeSqlQueryForFetchingData($selectQueryAdapter, $offset, $limit);
            $rows         = $this->getRowsData($sql);
            $resultsData  = array();
            $idByOffset   = self::resolveIdByOffset($offset);
            foreach ($rows as $key => $row)
            {
                $reportResultsRowData = new ReportResultsRowData($this->resolveDisplayAttributes(), $idByOffset);
                foreach ($selectQueryAdapter->getIdTableAliasesAndModelClassNames() as $tableAlias => $modelClassName)
                {
                    $idColumnName = $selectQueryAdapter->getIdColumNameByTableAlias($tableAlias);
                    $id           = (int)$row[$idColumnName];
                    if ($id != null)
                    {
                        $reportResultsRowData->addModelAndAlias($modelClassName::getById($id), $tableAlias);
                    }
                    unset($row[$idColumnName]);
                }
                foreach ($row as $columnName => $value)
                {
                    $reportResultsRowData->addSelectedColumnNameAndValue($columnName, $value);
                }
                $resultsData[$key] = $reportResultsRowData;
                $idByOffset++;
            }
            return $resultsData;
        }

        /**
         * @param $offset
         * @return int
         */
        protected static function resolveIdByOffset($offset)
        {
            assert('is_int($offset) || $offset == null');
            if ($offset == null)
            {
                return 0;
            }
            return $offset;
        }

        /**
         * @param $sql
         * @return array
         */
        protected function getRowsData($sql)
        {
            assert('is_string($sql)');
            if ($this->_rowsData == null)
            {
                $this->_rowsData = R::getAll($sql);
            }
            return $this->_rowsData;
        }

        /**
         * See the yii documentation.
         * @return array
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $data)
            {
                $keys[] = $data->getId();
            }
            return $keys;
        }

        /**
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         * @param $offset
         * @param $limit
         * @return string
         */
        protected function makeSqlQueryForFetchingData(RedBeanModelSelectQueryAdapter $selectQueryAdapter, $offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $moduleClassName        = $this->report->getModuleClassName();
            $modelClassName         = $moduleClassName::getPrimaryModelName();
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $this->makeDisplayAttributes($joinTablesAdapter, $selectQueryAdapter);
            $where                  = $this->makeFiltersContent($joinTablesAdapter);
            $orderBy                = $this->makeOrderBysContent($joinTablesAdapter);
            $groupBy                = $this->makeGroupBysContent($joinTablesAdapter);

            return                    SQLQueryUtil::makeQuery($modelClassName::getTableName($modelClassName),
                                      $selectQueryAdapter, $joinTablesAdapter, $offset, $limit, $where, $orderBy, $groupBy);
        }

        /**
         * @param $selectQueryAdapter
         * @param bool $selectJustCount
         * @return string
         */
        protected function makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter, $selectJustCount = false)
        {
            $moduleClassName        = $this->report->getModuleClassName();
            $modelClassName         = $moduleClassName::getPrimaryModelName();
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $this->makeDisplayAttributes($joinTablesAdapter, $selectQueryAdapter);
            $where                  = $this->makeFiltersContent($joinTablesAdapter);
            $orderBy                = $this->makeOrderBysContent($joinTablesAdapter);
            $groupBy                = $this->makeGroupBysContentForCount($joinTablesAdapter);
            //Make a fresh selectQueryAdapter that only has a count clause
            if ($selectJustCount)
            {
                //Currently this is always expected as false. If it is true, we need to add support for SpecificCountClauses
                //so we know which table/id the count is on.
                if ($selectQueryAdapter->isDistinct())
                {
                    throw new NotSupportedException();
                }
                $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter($selectQueryAdapter->isDistinct());
                $selectQueryAdapter->addNonSpecificCountClause();
            }
            return                    SQLQueryUtil::makeQuery($modelClassName::getTableName($modelClassName),
                                      $selectQueryAdapter, $joinTablesAdapter, null, null, $where, $orderBy, $groupBy);
        }

        /**
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param RedBeanModelSelectQueryAdapter $selectQueryAdapter
         */
        protected function makeDisplayAttributes(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                                 RedBeanModelSelectQueryAdapter $selectQueryAdapter)
        {
            $builder                = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter,
                                          $this->report->getCurrencyConversionType());
            $builder->makeQueryContent($this->resolveDisplayAttributes());
        }

        /**
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return null|string
         */
        protected function makeFiltersContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $filters          = $this->report->getFilters();
            $filtersStructure = $this->report->getFiltersStructure();
            $resolvedFilters  = $this->resolveFiltersForVariableStates($filters, $filtersStructure);
            $resolvedFilters  = $this->resolveFiltersForReadPermissions($resolvedFilters, $filtersStructure);
            $builder = new FiltersReportQueryBuilder($joinTablesAdapter, $filtersStructure);
            return $builder->makeQueryContent($resolvedFilters);
        }

        /**
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return null|string
         */
        protected function makeOrderBysContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $builder = new OrderBysReportQueryBuilder($joinTablesAdapter, $this->report->getCurrencyConversionType());
            return $builder->makeQueryContent($this->report->getOrderBys());
        }

        /**
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return null|string
         */
        protected function makeGroupBysContent(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $builder = new GroupBysReportQueryBuilder($joinTablesAdapter);
            return $builder->makeQueryContent($this->resolveGroupBys());
        }

        /**
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return null|string
         */
        protected function makeGroupBysContentForCount(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            return $this->makeGroupBysContent($joinTablesAdapter);
        }

        /**
         * @param $attributeIndexOrDerivedTypePrefix
         * @param $attributeOrDerivedAttributeType
         * @return FilterForReportForm
         * @throws NotSupportedException
         */
        protected function resolveFilterForReadPermissionAttributeIndex($attributeIndexOrDerivedTypePrefix, $attributeOrDerivedAttributeType)
        {
            assert('is_string($attributeIndexOrDerivedTypePrefix) || $attributeIndexOrDerivedTypePrefix == null');
            assert('is_string($attributeOrDerivedAttributeType)');
            $moduleClassName = $this->report->getModuleClassName();
            if ($attributeOrDerivedAttributeType == 'ReadOptimization')
            {
                $filter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                       $this->report->getType());
                $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix . $attributeOrDerivedAttributeType;
            }
            elseif ($attributeOrDerivedAttributeType == 'owner__User')
            {
                $filter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                                       $this->report->getType());
                $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix. $attributeOrDerivedAttributeType;
                $filter->operator                    = OperatorRules::TYPE_EQUALS;
                $filter->value                       = Yii::app()->user->userModel->id;
            }
            else
            {
                throw new NotSupportedException();
            }
            return $filter;
        }

        /**
         * @param array $filters
         * @return array
         */
        protected function makeReadPermissionsAttributeIndexes(array $filters)
        {
            $moduleClassName = $this->report->getModuleClassName();
            $attributeIndexes = array();
            ReadPermissionsForReportUtil::
                resolveAttributeIndexes($moduleClassName::getPrimaryModelName(), $attributeIndexes);
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveDisplayAttributes());
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $filters);
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->report->getOrderBys());
            ReadPermissionsForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveGroupBys());
            return $attributeIndexes;
        }

        /**
         * @param $attributeIndexOrDerivedTypePrefix
         * @param $variableStateData
         * @return FilterForReportForm
         */
        protected function resolveFilterForVariableStateAttributeIndex($attributeIndexOrDerivedTypePrefix, $variableStateData)
        {
            assert('is_string($attributeIndexOrDerivedTypePrefix) || $attributeIndexOrDerivedTypePrefix == null');
            assert('is_array($variableStateData) && count($variableStateData) == 2');
            $moduleClassName                     = $this->report->getModuleClassName();
            $filter                              = new FilterForReportForm($moduleClassName,
                                                   $moduleClassName::getPrimaryModelName(),
                                                   $this->report->getType());
            $filter->attributeIndexOrDerivedType = $attributeIndexOrDerivedTypePrefix. $variableStateData[0];
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = $variableStateData[1];
            return $filter;
        }

        /**
         * @param array $filters
         * @return array
         */
        protected function makeVariableStatesAttributeIndexes(array $filters)
        {
            $moduleClassName = $this->report->getModuleClassName();
            $attributeIndexes = array();
            VariableStatesForReportUtil::
                resolveAttributeIndexes($moduleClassName::getPrimaryModelName(), $attributeIndexes);
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveDisplayAttributes());
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $filters);
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->report->getOrderBys());
            VariableStatesForReportUtil::
                resolveAttributeIndexesByComponents($attributeIndexes, $this->resolveGroupBys());
            return $attributeIndexes;
        }

        /**
         * @param $attribute
         * @return mixed
         */
        protected function getDisplayAttributeByAttribute($attribute)
        {
            foreach ($this->resolveDisplayAttributes() as $displayAttribute)
            {
                if ($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $displayAttribute;
                }
            }
        }

        /**
         * @param $attribute
         * @return int|string
         */
        protected function getDisplayAttributeKeyByAttribute($attribute)
        {
            foreach ($this->resolveDisplayAttributes() as $key =>  $displayAttribute)
            {
                if ($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $key;
                }
            }
        }
    }
?>