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

    class SavedReportTestHelper
    {
        public static function makeSummationWithDrillDownReport()
        {
            $report                  = new Report();
            $report->setDescription    ('A test summation report with drill down description');
            $report->setModuleClassName('ReportsTestModule');
            $report->setName           ('A test summation report with drill down');
            $report->setType           (Report::TYPE_SUMMATION);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('1');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->value                       = '123';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $report->addFilter($filter);

            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'owner__User';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'owner__User';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'currencyValue__Summation';
            $report->addDisplayAttribute($displayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'string';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'hasOne___name';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'currencyValue';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'date';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $chart               = new ChartForReportForm();
            $chart->type         = 'Pie2D';
            $chart->firstSeries  = 'owner__User';
            $chart->firstRange   = 'currencyValue__Summation';
            $report->setChart($chart);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            $saved       = $savedReport->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $savedReport;
        }

        public static function makeSimpleContactRowsAndColumnsReport()
        {
            $report                  = new Report();
            $report->setDescription    ('A test contact report');
            $report->setModuleClassName('ContactsModule');
            $report->setName           ('A rows and columns report');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'lastName';
            $report->addDisplayAttribute($displayAttribute);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            $saved       = $savedReport->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $savedReport;
        }
    }
?>