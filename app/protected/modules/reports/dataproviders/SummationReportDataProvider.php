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
     * Data provider a report that is a summation report
     */
    class SummationReportDataProvider extends ReportDataProvider
    {
        /**
         * Resolved to include the groupBys as query only display attributes, and mark all display attributes that are
         * also groupBys as used by the drillDown.
         * @var null | array of DisplayAttributesForReportForms
         */
        private $resolvedDisplayAttributes;

        /**
         * @param Report $report
         * @param array $config
         */
        public function __construct(Report $report, array $config = array())
        {
            parent::__construct($report, $config);
        }

        /**
         * @return int
         */
        public function calculateTotalItemCount()
        {
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter();
            $sql                    = $this->makeSqlQueryForFetchingTotalItemCount($selectQueryAdapter);
            $rows                   = R::getAll($sql);
            $count                  = count($rows);
            return $count;
        }

        /**
         * @return ReportDataProviderToAmChartMakerAdapter
         */
        public function makeReportDataProviderToAmChartMakerAdapter()
        {
            if (ChartRules::isStacked($this->report->getChart()->type))
            {
                return $this->makeStackedReportDataProviderToAmChartMakerAdapter();
            }
            else
            {
                return $this->makeNonStackedReportDataProviderToAmChartMakerAdapter();
            }
        }

        /**
         * @return array|null
         */
        public function resolveDisplayAttributes()
        {
            if ($this->resolvedDisplayAttributes != null)
            {
                return $this->resolvedDisplayAttributes;
            }
            $this->resolvedDisplayAttributes = array();
            foreach ($this->report->getDisplayAttributes() as $displayAttribute)
            {
                $this->resolvedDisplayAttributes[] = $displayAttribute;
            }

            if (($this->report->getDrillDownDisplayAttributes()) > 0)
            {
                $this->resolveGroupBysThatAreNotYetDisplayAttributesAsDisplayAttributes();
            }
            return $this->resolvedDisplayAttributes;
        }

        /**
         * @return mixed
         */
        public function resolveFirstSeriesLabel()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->label;
                }
            }
        }

        /**
         * @return mixed
         */
        public function resolveFirstRangeLabel()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->label;
                }
            }
        }

        /**
         * @return bool|void
         * @throws NotSupportedException if the report is not valid for this data provider
         */
        protected function isReportValidType()
        {
            if ($this->report->getType() != Report::TYPE_SUMMATION)
            {
                throw new NotSupportedException();
            }
        }

        /**
         * @return array
         */
        protected function fetchChartData()
        {
            return $this->runQueryAndGetResolveResultsData(null, null);
        }

        /**
         * @return null | string
         */
        protected function resolveChartFirstSeriesAttributeNameForReportResultsRowData()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstSeries)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        /**
         * @return null | string
         */
        protected function resolveChartFirstRangeAttributeNameForReportResultsRowData()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->firstRange)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        /**
         * @return null | string
         */
        protected function resolveChartSecondSeriesAttributeNameForReportResultsRowData()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->secondSeries)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        /**
         * @return null | string
         */
        protected function resolveChartSecondRangeAttributeNameForReportResultsRowData()
        {
            foreach ($this->report->getDisplayAttributes() as $key => $displayAttribute)
            {
                if ($displayAttribute->attributeIndexOrDerivedType == $this->report->getChart()->secondRange)
                {
                    return $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                }
            }
        }

        /**
         * @return ReportDataProviderToAmChartMakerAdapter
         */
        protected function makeNonStackedReportDataProviderToAmChartMakerAdapter()
        {
            $resultsData              = $this->fetchChartData();
            $firstRangeAttributeName  = $this->resolveChartFirstRangeAttributeNameForReportResultsRowData();
            $firstSeriesDisplayAttributeKey  = $this->getDisplayAttributeKeyByAttribute($this->report->getChart()->firstSeries);
            $chartData                = array();
            foreach ($resultsData as $data)
            {
                $firstSeriesDataValue = $data->resolveRawValueByDisplayAttributeKey($firstSeriesDisplayAttributeKey);
                $chartData[] = array(ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName(1)
                                        => $data->$firstRangeAttributeName,
                                     ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(1)
                                        =>
                                     $this->getDisplayAttributeByAttribute($this->report->getChart()->firstSeries)->
                                     resolveValueAsLabelForHeaderCell($firstSeriesDataValue));
            }
            return new ReportDataProviderToAmChartMakerAdapter($this->report, $chartData);
        }

        /**
         * @return ReportDataProviderToAmChartMakerAdapter
         */
        protected function makeStackedReportDataProviderToAmChartMakerAdapter()
        {
            $resultsData                     = $this->fetchChartData();
            $firstRangeAttributeName         = $this->resolveChartFirstRangeAttributeNameForReportResultsRowData();
            $secondRangeAttributeName        = $this->resolveChartSecondRangeAttributeNameForReportResultsRowData();
            $chartData                       = array();
            $secondSeriesValueData           = array();
            $secondSeriesDisplayLabels       = array();
            $secondSeriesValueCount          = 1;
            $firstSeriesDisplayAttributeKey  = $this->getDisplayAttributeKeyByAttribute($this->report->getChart()->firstSeries);
            $secondSeriesDisplayAttributeKey = $this->getDisplayAttributeKeyByAttribute($this->report->getChart()->secondSeries);
            foreach ($resultsData as $data)
            {
                $firstSeriesDataValue             = $data->resolveRawValueByDisplayAttributeKey($firstSeriesDisplayAttributeKey);
                $chartData[$firstSeriesDataValue] = array(
                                                    ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(1) =>
                                                    $this->getDisplayAttributeByAttribute($this->report->getChart()->firstSeries)->
                                                    resolveValueAsLabelForHeaderCell($firstSeriesDataValue));
                $secondSeriesDataValue            = $data->resolveRawValueByDisplayAttributeKey($secondSeriesDisplayAttributeKey);
                if (!isset($secondSeriesValueData[$secondSeriesDataValue]))
                {
                    $secondSeriesValueData[$secondSeriesDataValue]      = $secondSeriesValueCount;
                    $secondSeriesDisplayLabels[$secondSeriesValueCount] = $this->getDisplayAttributeByAttribute(
                                                                          $this->report->getChart()->secondSeries)->
                                                                          resolveValueAsLabelForHeaderCell($secondSeriesDataValue);
                    $secondSeriesValueCount++;
                }
            }
            foreach ($resultsData as $data)
            {
                $firstSeriesDataValue  = $data->resolveRawValueByDisplayAttributeKey($firstSeriesDisplayAttributeKey);
                $secondSeriesDataValue = $data->resolveRawValueByDisplayAttributeKey($secondSeriesDisplayAttributeKey);
                $secondSeriesKey       = $secondSeriesValueData[$secondSeriesDataValue];
                if (!isset($chartData[$firstSeriesDataValue]
                          [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue]
                              [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)] = 0;
                }
                $chartData[$firstSeriesDataValue]
                          [ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($secondSeriesKey)] +=
                          $data->$firstRangeAttributeName;
                if (!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName($secondSeriesKey)] =
                        $this->getDisplayAttributeByAttribute($this->report->getChart()->firstRange)->label;
                }
                if (!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)] = 0;
                }
                $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName($secondSeriesKey)] += $data->$secondRangeAttributeName;
                if (!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)] =
                            $secondSeriesDisplayLabels[$secondSeriesKey];
                }
                if (!isset($chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)]))
                {
                    $chartData[$firstSeriesDataValue][ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($secondSeriesKey)] =
                        $this->getDisplayAttributeByAttribute($this->report->getChart()->secondRange)->label;
                }
            }
            return new ReportDataProviderToAmChartMakerAdapter($this->report, array_values($chartData),
                                                               $secondSeriesValueData, $secondSeriesDisplayLabels,
                                                               $secondSeriesValueCount - 1);
        }

        private function resolveGroupBysThatAreNotYetDisplayAttributesAsDisplayAttributes()
        {
            foreach ($this->resolveGroupBys() as $groupBy)
            {
                if (null === $index = $this->report->getDisplayAttributeIndex($groupBy->attributeIndexOrDerivedType))
                {
                    $displayAttribute                              = new DisplayAttributeForReportForm(
                                                                     $groupBy->getModuleClassName(),
                                                                     $groupBy->getModelClassName(),
                                                                     $this->report->getType());
                    $displayAttribute->attributeIndexOrDerivedType = $groupBy->attributeIndexOrDerivedType;
                    $displayAttribute->queryOnly                   = true;
                    $displayAttribute->valueUsedAsDrillDownFilter  = true;
                    $this->resolvedDisplayAttributes[]             = $displayAttribute;
                }
                else
                {
                    $this->resolvedDisplayAttributes[$index]->valueUsedAsDrillDownFilter = true;
                }
            }
        }
    }
?>