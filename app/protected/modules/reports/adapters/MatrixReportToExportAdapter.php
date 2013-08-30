<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class used to convert MatrixReport models into arrays
     */
    class MatrixReportToExportAdapter extends ReportToExportAdapter
    {
        public function __construct(ReportDataProvider $dataProvider, Report $report)
        {
            $this->dataProvider     = $dataProvider;
            $this->report           = $report;
            $this->dataForExport    = $dataProvider->getData();
            $this->makeData();
        }

        protected function makeData()
        {
            $data                      = array();
            $this->headerData          = array();
            foreach ($this->dataForExport as $reportResultsRowData)
            {
                $line                      = array();
                $header                    = array();
                $temporaryHeader           = array(); //This is used because when resolving header for last columns we got strange results
                $key                       = $this->dataProvider->getXAxisGroupByDataValuesCount();
                $column                    = array();
                $extraLeadingHeaderColumns = 0;
                foreach ($this->dataProvider->getDisplayAttributesThatAreYAxisGroupBys() as $displayAttribute)
                {
                    $header[]           = $displayAttribute->label;
                    $className          = $this->resolveExportClassNameForReportToExportValueAdapter(
                                            $displayAttribute);
                    $attributeName      = MatrixReportDataProvider::resolveHeaderColumnAliasName(
                                            $displayAttribute->columnAliasName);
                    $params             = array();
                    $line[]             = $displayAttribute->resolveValueAsLabelForHeaderCell(
                                            $reportResultsRowData->$attributeName);
                }
                $leadingHeaders         = $this->dataProvider->makeAxisCrossingColumnCountAndLeadingHeaderRowsData();
                $rows                   = count($leadingHeaders['rows']);
                $matrixColumnCount      = $leadingHeaders['rows'][$rows - 1]['colSpan']; //This is the true columns count, the other are repeated for each grouping
                $attributeKey           = 0;
                for ($i = 0; $i < $this->dataProvider->getXAxisGroupByDataValuesCount(); $i++)
                {
                    foreach ($this->dataProvider->resolveDisplayAttributes() as $displayAttribute)
                    {
                        if (!$displayAttribute->queryOnly)
                        {
                            $params        = array();
                            $column        = MatrixReportDataProvider::resolveColumnAliasName($attributeKey);
                            $className     = $this->resolveExportClassNameForReportToExportValueAdapter(
                                                $displayAttribute);
                            $adapter       = new $className($reportResultsRowData, $column, $params);
                            $adapter->resolveData($line);
                            if ($attributeKey < $matrixColumnCount)
                            {
                                $oldHeaderCount = count($temporaryHeader);
                                $adapter->resolveHeaderData($temporaryHeader);
                                $adapter->resolveHeaderData($header);
                                $extraLeadingHeaderColumns += (count($temporaryHeader) - ($oldHeaderCount + 1));
                            }
                            elseif ($attributeKey % $matrixColumnCount == 0)
                            {
                                foreach ($temporaryHeader as $column)
                                {
                                    $header[] = $column;
                                }
                            }

                            $attributeKey++;
                        }
                    }
                }
                $data[]   = $line;
            }
            $leadingHeaderData      = $this->getLeadingHeadersDataFromMatrixReportDataProvider($extraLeadingHeaderColumns);
            $this->data = array_merge($leadingHeaderData, array_merge(array($header), $data));
        }

        protected function getLeadingHeadersDataFromMatrixReportDataProvider($extraLeadingHeaderColumns)
        {
            $leadingHeaders             = $this->dataProvider->makeAxisCrossingColumnCountAndLeadingHeaderRowsData();
            $previousGroupByValuesCount = 1;
            $headerData = array();
            for ($i = 0; $i < count($leadingHeaders['rows']); $i++)
            {
                $headerRow = array();
                for ($j = 0; $j < $leadingHeaders['axisCrossingColumnCount']; $j++)
                {
                    $headerRow[] = null;
                }
                for ($k = 0; $k < $previousGroupByValuesCount; $k++)
                {
                    foreach ($leadingHeaders['rows'][$i]['groupByValues'] as $value)
                    {
                        for ($l = 0; $l < $leadingHeaders['rows'][$i]['colSpan']; $l++)
                        {
                            $headerRow[] = $value;
                        }
                        if ($extraLeadingHeaderColumns > 0)
                        {
                            if ($i != (count($leadingHeaders['rows']) - 1))
                            {
                                $columnsToAdd = $extraLeadingHeaderColumns *
                                                $leadingHeaders['rows'][$i]['colSpan'] /
                                                $leadingHeaders['rows'][$i + 1]['colSpan'];
                            }
                            else
                            {
                                $columnsToAdd = $extraLeadingHeaderColumns;
                            }
                            for ($m = 0; $m < $columnsToAdd; $m++)
                            {
                                $headerRow[] = $value;
                            }
                        }
                    }
                }
                $previousGroupByValuesCount = count($leadingHeaders['rows'][$i]['groupByValues']);
                $headerData[] = $headerRow;
            }
            return $headerData;
        }
    }
?>