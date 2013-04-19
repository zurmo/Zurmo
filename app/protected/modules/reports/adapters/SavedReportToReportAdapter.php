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
     * Helper class to adapt a SavedReport object to a Report object
     */
    class SavedReportToReportAdapter
    {
        /**
         * @param $savedReport
         * @return Report
         */
        public static function makeReportBySavedReport($savedReport)
        {
            $report = new Report();
            if ($savedReport->id > 0)
            {
                $report->setId((int)$savedReport->id);
            }
            $report->setDescription($savedReport->description);
            $report->setModuleClassName($savedReport->moduleClassName);
            $report->setName($savedReport->name);
            $report->setOwner($savedReport->owner);
            $report->setType($savedReport->type);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($savedReport);
            $report->setExplicitReadWriteModelPermissions($explicitReadWriteModelPermissions);
            if ($savedReport->serializedData != null)
            {
                $unserializedData = unserialize($savedReport->serializedData);
                if (isset($unserializedData['filtersStructure']))
                {
                    $report->setFiltersStructure($unserializedData['filtersStructure']);
                }
                if (isset($unserializedData['currencyConversionType']))
                {
                    $report->setCurrencyConversionType((int)$unserializedData['currencyConversionType']);
                }
                if (isset($unserializedData['spotConversionCurrencyCode']))
                {
                    $report->setSpotConversionCurrencyCode($unserializedData['spotConversionCurrencyCode']);
                }

                self::makeComponentFormAndPopulateReportFromData(
                        $unserializedData[ComponentForReportForm::TYPE_FILTERS],   $report, 'Filter');
                self::makeComponentFormAndPopulateReportFromData(
                        $unserializedData[ComponentForReportForm::TYPE_ORDER_BYS], $report, 'OrderBy');
                self::makeComponentFormAndPopulateReportFromData(
                        $unserializedData[ComponentForReportForm::TYPE_GROUP_BYS], $report, 'GroupBy');
                self::makeComponentFormAndPopulateReportFromData(
                        $unserializedData[ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES],
                        $report, 'DisplayAttribute');
                self::makeComponentFormAndPopulateReportFromData(
                        $unserializedData[ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES],
                        $report, 'DrillDownDisplayAttribute');

                if (isset($unserializedData['chart']))
                {
                    $moduleClassName = $report->getModuleClassName();
                    $modelClassName  = $moduleClassName::getPrimaryModelName();
                    $adapter         = ModelRelationsAndAttributesToSummationReportAdapter::
                                       make($moduleClassName, $modelClassName, $report->getType());
                    $chart           = new ChartForReportForm(
                                            ReportUtil::makeDataAndLabelsForSeriesOrRange(
                                            $adapter->getAttributesForChartSeries($report->getGroupBys(),
                                                $report->getDisplayAttributes())),
                                            ReportUtil::makeDataAndLabelsForSeriesOrRange(
                                            $adapter->getAttributesForChartRange($report->getDisplayAttributes())));
                    $chart->setAttributes($unserializedData['chart']);
                    $report->setChart($chart);
                }
            }
            return $report;
        }

        /**
         * @param Report $report
         * @param SavedReport$savedReport
         */
        public static function resolveReportToSavedReport(Report $report, SavedReport $savedReport)
        {
            $savedReport->description     = $report->getDescription();
            $savedReport->moduleClassName = $report->getModuleClassName();
            $savedReport->name            = $report->getName();
            $savedReport->owner           = $report->getOwner();
            $savedReport->type            = $report->getType();

            $data = array();
            $data['filtersStructure']           = $report->getFiltersStructure();
            $data['currencyConversionType']     = $report->getCurrencyConversionType();
            $data['spotConversionCurrencyCode'] = $report->getSpotConversionCurrencyCode();
            $data[ComponentForReportForm::TYPE_FILTERS]                      =
                self::makeArrayFromComponentFormsAttributesData($report->getFilters());
            $data[ComponentForReportForm::TYPE_ORDER_BYS]                    =
                self::makeArrayFromComponentFormsAttributesData($report->getOrderBys());
            $data[ComponentForReportForm::TYPE_GROUP_BYS]                    =
                self::makeArrayFromComponentFormsAttributesData($report->getGroupBys());
            $data[ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES]           =
                self::makeArrayFromComponentFormsAttributesData($report->getDisplayAttributes());
            $data[ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES] =
                self::makeArrayFromComponentFormsAttributesData($report->getDrillDownDisplayAttributes());
            if ($report->getChart()->type != null)
            {
                $data['chart'] = self::makeArrayFromChartForReportFormAttributesData($report->getChart());
            }
            $savedReport->serializedData   = serialize($data);
        }

        /**
         * @param ChartForReportForm $chartForReportForm
         * @return array
         */
        protected static function makeArrayFromChartForReportFormAttributesData(ChartForReportForm $chartForReportForm)
        {
            $data = array();
            foreach ($chartForReportForm->getAttributes() as $attribute => $value)
            {
                $data[$attribute] = $value;
            }
            return $data;
        }

        /**
         * @param array $componentFormsData
         * @return array
         */
        protected static function makeArrayFromComponentFormsAttributesData(Array $componentFormsData)
        {
            $data = array();
            foreach ($componentFormsData as $key => $componentForm)
            {
                foreach ($componentForm->getAttributes() as $attribute => $value)
                {
                    $data[$key][$attribute] = $value;
                }
            }
            return $data;
        }

        /**
         * @param array $componentFormsData
         * @param Report $report
         * @param null|string $componentPrefix
         */
        protected static function makeComponentFormAndPopulateReportFromData($componentFormsData, Report $report, $componentPrefix)
        {
            $moduleClassName    = $report->getModuleClassName();
            $addMethodName      = 'add' . $componentPrefix;
            $componentClassName = $componentPrefix . 'ForReportForm';
            $rowKey             = 0;
            foreach ($componentFormsData as $componentFormData)
            {
                $component      = new $componentClassName($moduleClassName,
                                                          $moduleClassName::getPrimaryModelName(),
                                                          $report->getType(),
                                                          $rowKey);
                $component->setAttributes($componentFormData);
                $report->{$addMethodName}($component);
                $rowKey++;
            }
        }
    }
?>