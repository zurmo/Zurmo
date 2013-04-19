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
     * Data provider a report that is a matrix report
     */
    class MatrixReportDataProvider extends ReportDataProvider
    {
        const HEADER_COLUMN_ALIAS_SUFFIX = 'Header';

        public static $maximumGroupsCount = 400;

        /**
         * Resolved to include the groupBys as query only display attributes
         * @var null | array of DisplayAttributesForReportForms
         */
        private $resolvedDisplayAttributes;

        /**
         * Resolved groupBys in order of y-axis groupBys then x-axis groupBys
         * @var null | array of GroupBysForReportForms
         */
        private $resolvedGroupBys;

        /**
         * @var array
         */
        private $xAxisGroupByDataValues;

        /**
         * @var array
         */
        private $yAxisGroupByDataValues;

        /**
         * @param $index
         * @return string
         */
        public static function resolveColumnAliasName($index)
        {
            assert('is_int($index)');
            return DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX . $index;
        }

        /**
         * @param $columnAliasName
         * @return string
         */
        public static function resolveHeaderColumnAliasName($columnAliasName)
        {
            assert('is_int($columnAliasName) || is_string($columnAliasName)');
            return $columnAliasName . self::HEADER_COLUMN_ALIAS_SUFFIX;
        }

        /**
         * Override to
         * @param Report $report
         * @param array $config
         */
        public function __construct(Report $report, array $config = array())
        {
            parent::__construct($report, $config);
            $this->pagination = array('pageSize' => self::$maximumGroupsCount);
        }

        /**
         * @return int
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
            $rows                   = R::getAll($sql);
            return count($rows);
        }

        /**
         * @return int
         */
        public function calculateTotalGroupingsCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
            $rows                   = R::getAll($sql);
            if (count($rows) > 0)
            {
                return count($rows) * count($rows[0]);
            }
            return 0;
        }

        /**
         * @return array|null
         */
        public function resolveDisplayAttributes()
        {
            if ($this->resolvedDisplayAttributes == null)
            {
                $this->resolvedDisplayAttributes = array();
                foreach ($this->report->getDisplayAttributes() as $displayAttribute)
                {
                    $this->resolvedDisplayAttributes[] = $displayAttribute;
                }
                foreach ($this->resolveGroupBys() as $groupBy)
                {
                    $displayAttribute                                 = new DisplayAttributeForReportForm(
                                                                        $groupBy->getModuleClassName(),
                                                                        $groupBy->getModelClassName(),
                                                                        $this->report->getType());
                    $displayAttribute->attributeIndexOrDerivedType    = $groupBy->attributeIndexOrDerivedType;
                    $displayAttribute->queryOnly                      = true;
                    $displayAttribute->madeViaSelectInsteadOfViaModel = true;
                    $this->resolvedDisplayAttributes[]                = $displayAttribute;
                }
            }
            return $this->resolvedDisplayAttributes;
        }

        /**
         * @return array|null
         */
        public function resolveGroupBys()
        {
            if ($this->resolvedGroupBys != null)
            {
                return $this->resolvedGroupBys;
            }
            $this->resolvedGroupBys = array();
            foreach ($this->report->getGroupBys() as $groupBy)
            {
                if ($groupBy->axis == 'y')
                {
                    $this->resolvedGroupBys[] = $groupBy;
                }
            }
            foreach ($this->report->getGroupBys() as $groupBy)
            {
                if ($groupBy->axis == 'x')
                {
                    $this->resolvedGroupBys[] = $groupBy;
                }
            }
            return $this->resolvedGroupBys;
        }

        /**
         * @return int
         */
        public function getXAxisGroupByDataValuesCount()
        {
            $count = 1;
            foreach ($this->getXAxisGroupByDataValues() as $groupByValues)
            {
                $count = $count * count($groupByValues);
            }
            return $count;
        }

        /**
         * @return int
         */
        public function getYAxisGroupByDataValuesCount()
        {
            return count($this->getYAxisGroupByDataValues());
        }

        /**
         * Public for testing purposes
         * @return array
         */
        public function makeXAxisGroupingsForColumnNamesData()
        {
            $data                        = array();
            $xAxisGroupByDataValues      = $this->getXAxisGroupByDataValues();
            $xAxisGroupByDataValuesCount = count($xAxisGroupByDataValues);
            $attributeKey                = 0;
            $startingGroupBysIndex       = 0;
            $this->resolveXAxisGroupingsForColumnNames($data, array_values($xAxisGroupByDataValues), $attributeKey,
                                                       $xAxisGroupByDataValuesCount, $startingGroupBysIndex);
            return $data;
        }

        /**
         * @return array
         */
        public function makeAxisCrossingColumnCountAndLeadingHeaderRowsData()
        {
            $headerData    = array('rows' => array());
            $headerData['axisCrossingColumnCount'] = count($this->getYAxisGroupBys());
            $lastSpanCount = $this->getDisplayCalculationsCount();
            foreach (array_reverse($this->getXAxisGroupByDataValues()) as $attributeIndexOrDerivedType => $groupByValues)
            {
                $xAxisDisplayAttribute = $this->getDisplayAttributeByAttribute($attributeIndexOrDerivedType);
                foreach ($groupByValues as $key => $value)
                {
                    $groupByValues[$key] = $xAxisDisplayAttribute->resolveValueAsLabelForHeaderCell($value);
                }
                $headerData['rows'][]  = array('groupByValues' => $groupByValues, 'colSpan' => $lastSpanCount);
                $lastSpanCount = count($groupByValues) * $lastSpanCount;
            }
            $headerData['rows'] = array_reverse($headerData['rows']);
            return $headerData;
        }

        /**
         * @return array
         */
        public function getDisplayAttributesThatAreYAxisGroupBys()
        {
            $displayAttributes = array();
            foreach ($this->resolveDisplayAttributes() as $displayAttribute)
            {
                foreach ($this->getYAxisGroupBys() as $groupBy)
                {
                    if ($displayAttribute->attributeIndexOrDerivedType ==
                        $groupBy->attributeIndexOrDerivedType)
                    {
                        $displayAttributes[] = $displayAttribute;
                        break;
                    }
                }
            }
            return $displayAttributes;
        }

        /**
         * @param array $data
         * @param array $indexedXAxisGroupByDataValues
         * @param int $attributeKey
         * @param int $xAxisGroupBysCount
         * @param int $startingIndex
         */
        protected function resolveXAxisGroupingsForColumnNames(& $data, $indexedXAxisGroupByDataValues, & $attributeKey,
                                                               $xAxisGroupBysCount, $startingIndex)
        {
            assert('is_array($data)');
            assert('is_array($indexedXAxisGroupByDataValues)');
            assert('is_int($attributeKey)');
            assert('is_int($xAxisGroupBysCount)');
            assert('is_int($startingIndex)');
            if (isset($indexedXAxisGroupByDataValues[$startingIndex]))
            {
                foreach ($indexedXAxisGroupByDataValues[$startingIndex] as $value)
                {
                    $data[$value] = array();
                    if (($startingIndex + 1) == $xAxisGroupBysCount)
                    {
                        foreach ($this->resolveDisplayAttributes() as $displayAttribute)
                        {
                            if ($displayAttribute->queryOnly != true)
                            {
                                $data[$value][$displayAttribute->attributeIndexOrDerivedType] =
                                    static::resolveColumnAliasName($attributeKey);
                                $attributeKey++;
                            }
                        }
                    }
                    else
                    {
                        $this->resolveXAxisGroupingsForColumnNames($data[$value], $indexedXAxisGroupByDataValues,
                                                                   $attributeKey, $xAxisGroupBysCount, $startingIndex + 1);
                    }
                }
            }
        }

        /**
         * @param null | int $offset
         * @param null | int $limit
         * @return array
         */
        protected function runQueryAndGetResolveResultsData($offset, $limit)
        {
            assert('is_int($offset) || $offset == null');
            assert('is_int($limit) || $limit == null');
            $selectQueryAdapter                        = new RedBeanModelSelectQueryAdapter();
            $sql                                       = $this->makeSqlQueryForFetchingData($selectQueryAdapter,
                                                         $offset, $limit);
            $rows                                      = $this->getRowsData($sql);
            $resultsData                               = array();
            $idByOffset                                = 0;
            $calculationsCount                         = $this->getDisplayCalculationsCount();
            $xAxisGroupByDataValuesCount               = $this->getXAxisGroupByDataValuesCount() * $calculationsCount;
            $xAxisGroupingsColumnNamesData             = $this->makeXAxisGroupingsForColumnNamesData();
            $displayAttributesThatAreYAxisGroupBys     = $this->getDisplayAttributesThatAreYAxisGroupBys();
            $previousYAxisDisplayAttributesUniqueIndex = $this->resolveYAxisDisplayAttributesUniqueIndex(
                                                         $rows[0], $displayAttributesThatAreYAxisGroupBys);
            $resultsData[$idByOffset]                  = new ReportResultsRowData($this->resolveDisplayAttributes(), 0);
            $this->addDefaultColumnNamesAndValuesToReportResultsRowData($resultsData[$idByOffset],
                                                                        $xAxisGroupByDataValuesCount);
            foreach ($rows as $row)
            {
                $currentYAxisDisplayAttributesUniqueIndex = $this->resolveYAxisDisplayAttributesUniqueIndex(
                                                            $row, $displayAttributesThatAreYAxisGroupBys);
                if ($previousYAxisDisplayAttributesUniqueIndex != $currentYAxisDisplayAttributesUniqueIndex)
                {
                    $idByOffset++;
                    $resultsData[$idByOffset] = new ReportResultsRowData($this->resolveDisplayAttributes(), $idByOffset);
                    $this->addDefaultColumnNamesAndValuesToReportResultsRowData($resultsData[$idByOffset],
                                                                                $xAxisGroupByDataValuesCount);
                }
                $tempData = $xAxisGroupingsColumnNamesData;
                foreach ($this->resolveDisplayAttributes() as $displayAttribute)
                {
                    $value    = $row[$displayAttribute->columnAliasName];
                    if ($this->isDisplayAttributeAnXAxisGroupBy($displayAttribute))
                    {
                        $tempData = $tempData[$value];
                    }
                    elseif ($this->isDisplayAttributeAnYAxisGroupBy($displayAttribute))
                    {
                        $resolvedColumnAliasName = static::resolveHeaderColumnAliasName($displayAttribute->columnAliasName);
                        $resultsData[$idByOffset]->addSelectedColumnNameAndValue($resolvedColumnAliasName, $value);
                        $resultsData[$idByOffset]->addSelectedColumnNameAndLabel($resolvedColumnAliasName, $displayAttribute->resolveValueAsLabelForHeaderCell($value));
                    }
                }
                //At this point $tempData is at the final level, where the actual display calculations are located
                foreach ($this->resolveDisplayAttributes() as $displayAttribute)
                {
                    if (!$displayAttribute->queryOnly)
                    {
                        $value = $row[$displayAttribute->columnAliasName];
                        $columnAliasName = $tempData[$displayAttribute->attributeIndexOrDerivedType];
                        $resultsData[$idByOffset]->addSelectedColumnNameAndValue($columnAliasName, $value);
                    }
                }
                $previousYAxisDisplayAttributesUniqueIndex = $currentYAxisDisplayAttributesUniqueIndex;
            }
            $this->resolveRowSpansForResultsData($resultsData);
            return $resultsData;
        }

        /**
         * @param array $resultsData
         */
        protected function resolveRowSpansForResultsData(& $resultsData)
        {
            $previousLeadingUniqueIndexData = array();
            foreach ($resultsData as $dataKey => $reportResultsRowData)
            {
                $leadingUniqueIndexData = array();
                $leadingUniqueIndex     = null;
                foreach ($this->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
                {
                    $attributeName = static::resolveHeaderColumnAliasName($displayAttribute->columnAliasName);
                    if ($leadingUniqueIndex != null)
                    {
                        $leadingUniqueIndex .= FormModelUtil::DELIMITER;
                    }
                    $leadingUniqueIndex .= $reportResultsRowData->{$attributeName};
                    $leadingUniqueIndexData[$displayAttribute->columnAliasName] = $leadingUniqueIndex;
                    $rowSpan = 0;
                    if ($dataKey == 0 ||
                       $previousLeadingUniqueIndexData[$displayAttribute->columnAliasName] !=
                       $leadingUniqueIndexData[$displayAttribute->columnAliasName])
                    {
                        $rowSpan = 1;
                        for ($i = ($dataKey + 1); $i < count($resultsData); $i++)
                        {
                            $nextLeadingUniqueIndex     = null;
                            foreach ($this->getDisplayAttributesThatAreYAxisGroupBys() as $nextDisplayAttribute)
                            {
                                if ($nextLeadingUniqueIndex != null)
                                {
                                    $nextLeadingUniqueIndex .= FormModelUtil::DELIMITER;
                                }
                                $nextAttributeName = static::resolveHeaderColumnAliasName($nextDisplayAttribute->columnAliasName);
                                $nextLeadingUniqueIndex .= $resultsData[$i]->$nextAttributeName;
                                if ($nextDisplayAttribute->attributeIndexOrDerivedType == $displayAttribute->attributeIndexOrDerivedType)
                                {
                                    break;
                                }
                            }
                            if ($nextLeadingUniqueIndex == $leadingUniqueIndexData[$displayAttribute->columnAliasName])
                            {
                                $rowSpan++;
                            }
                            else
                            {
                                break;
                            }
                        }
                    }
                    $resultsData[$dataKey]->addSelectedColumnNameAndRowSpan(static::resolveHeaderColumnAliasName(
                        $displayAttribute->columnAliasName), $rowSpan);
                }
                $previousLeadingUniqueIndexData = $leadingUniqueIndexData;
            }
        }

        /**
         * @return int
         */
        protected function getDisplayCalculationsCount()
        {
            $count           = 0;
            foreach ($this->resolveDisplayAttributes() as $displayAttribute)
            {
                if (!$displayAttribute->queryOnly)
                {
                    $count++;
                }
            }
            return $count;
        }

        /**
         * @return array
         */
        protected function getXAxisGroupByDataValues()
        {
            if ($this->xAxisGroupByDataValues == null)
            {
                $this->xAxisGroupByDataValues = array();
                $selectQueryAdapter = new RedBeanModelSelectQueryAdapter();
                $sql                = $this->makeSqlQueryForFetchingData($selectQueryAdapter, null, null);
                $rows               = $this->getRowsData($sql);
                foreach ($rows as $row)
                {
                    foreach ($this->getDisplayAttributesThatAreXAxisGroupBys() as $displayAttribute)
                    {
                        if (!isset($this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]) ||
                            !in_array($row[$displayAttribute->columnAliasName],
                                $this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]))
                        {
                            $this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType][] =
                                $row[$displayAttribute->columnAliasName];
                        }
                    }
                }
            }
            //Sort for group bys correctly.
            foreach ($this->getDisplayAttributesThatAreXAxisGroupBys() as $displayAttribute)
            {
                if ($displayAttribute->getHeaderSortableType() == DisplayAttributeForReportForm::HEADER_SORTABLE_TYPE_ASORT)
                {
                    asort($this->xAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]);
                }
            }
            return $this->xAxisGroupByDataValues;
        }

        /**
         * @return array
         */
        protected function getYAxisGroupByDataValues()
        {
            if ($this->yAxisGroupByDataValues == null)
            {
                $this->yAxisGroupByDataValues = array();
                $selectQueryAdapter = new RedBeanModelSelectQueryAdapter();
                $sql                = $this->makeSqlQueryForFetchingData($selectQueryAdapter, null, null);
                $rows               = $this->getRowsData($sql);
                foreach ($rows as $row)
                {
                    foreach ($this->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
                    {
                        if (!isset($this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]) ||
                            in_array($row[$displayAttribute->columnAliasName],
                                     $this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType]))
                        {
                            $this->yAxisGroupByDataValues[$displayAttribute->attributeIndexOrDerivedType][] =
                                $row[$displayAttribute->columnAliasName];
                        }
                    }
                }
            }
            return $this->yAxisGroupByDataValues;
        }

        /**
         * @return bool|void
         * @throws NotSupportedException if the report type is not matrix
         */
        protected function isReportValidType()
        {
            if ($this->report->getType() != Report::TYPE_MATRIX)
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return array
         */
        protected function getXAxisGroupBys()
        {
            $xAxisGroupBys = array();
            foreach ($this->report->getGroupBys() as $groupBy)
            {
                if ($groupBy->axis == 'x')
                {
                    $xAxisGroupBys[] = $groupBy;
                }
            }
            return $xAxisGroupBys;
        }

        /**
         * @return array
         */
        protected function getDisplayAttributesThatAreXAxisGroupBys()
        {
            $displayAttributes = array();
            foreach ($this->resolveDisplayAttributes() as $displayAttribute)
            {
                foreach ($this->getXAxisGroupBys() as $xAxisGroupBy)
                {
                    if ($displayAttribute->attributeIndexOrDerivedType ==
                        $xAxisGroupBy->attributeIndexOrDerivedType)
                    {
                        $displayAttributes[] = $displayAttribute;
                        break;
                    }
                }
            }
            return $displayAttributes;
        }

        /**
         * @return array
         */
        protected function getYAxisGroupBys()
        {
            $yAxisGroupBys = array();
            foreach ($this->report->getGroupBys() as $groupBy)
            {
                if ($groupBy->axis == 'y')
                {
                    $yAxisGroupBys[] = $groupBy;
                }
            }
            return $yAxisGroupBys;
        }

        /**
         * @param $displayAttribute
         * @return bool
         */
        protected function isDisplayAttributeAnXAxisGroupBy($displayAttribute)
        {
            foreach ($this->getXAxisGroupBys() as $groupBy)
            {
                if ($displayAttribute->attributeIndexOrDerivedType ==
                    $groupBy->attributeIndexOrDerivedType)
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * @param $displayAttribute
         * @return bool
         */
        protected function isDisplayAttributeAnYAxisGroupBy($displayAttribute)
        {
            foreach ($this->getYAxisGroupBys() as $groupBy)
            {
                if ($displayAttribute->attributeIndexOrDerivedType ==
                    $groupBy->attributeIndexOrDerivedType)
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * @param ReportResultsRowData $reportResultsRowData
         * @param $totalCount
         */
        protected function addDefaultColumnNamesAndValuesToReportResultsRowData(ReportResultsRowData $reportResultsRowData, $totalCount)
        {
            for ($i = 0; $i < $totalCount; $i++)
            {
                $columnAliasName = DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX . $i;
                $value           = 0;
                $reportResultsRowData->addSelectedColumnNameAndValue($columnAliasName, $value);
            }
        }

        /**
         * @param array $rowData
         * @param array $displayAttributesThatAreYAxisGroupBys
         * @return null|string
         */
        protected function resolveYAxisDisplayAttributesUniqueIndex($rowData, $displayAttributesThatAreYAxisGroupBys)
        {
            $uniqueIndex = null;
            foreach ($displayAttributesThatAreYAxisGroupBys as $displayAttribute)
            {
                if ($uniqueIndex != null)
                {
                    $uniqueIndex .= FormModelUtil::DELIMITER;
                }
                $uniqueIndex .= $rowData[$displayAttribute->columnAliasName];
            }
            return $uniqueIndex;
        }

        /**
         * Only query on y-axis group bys to get a proper row count
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @return null|string
         */
        protected function makeGroupBysContentForCount(RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter)
        {
            $builder = new GroupBysReportQueryBuilder($joinTablesAdapter);
            return $builder->makeQueryContent($this->getYAxisGroupBys());
        }
    }
?>