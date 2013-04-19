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

    class ReportDataProviderToAmChartMakerAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testResolveFirstSeriesValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName(5);
            $this->assertEquals('FirstSeriesValue5', $value);
        }

        public function testResolveFirstSeriesDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(5);
            $this->assertEquals('FirstSeriesDisplayLabel5', $value);
        }

        public function testResolveFirstRangeDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName(5);
            $this->assertEquals('FirstRangeDisplayLabel5', $value);
        }

        public function testResolveFirstSeriesFormattedValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesFormattedValueName(5);
            $this->assertEquals('FirstSeriesFormattedValue5', $value);
        }

        public function testResolveSecondSeriesValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesValueName(5);
            $this->assertEquals('SecondSeriesValue5', $value);
        }

        public function testResolveSecondSeriesDisplayLabelName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName(5);
            $this->assertEquals('SecondSeriesDisplayLabel5', $value);
        }

        public function testResolveSecondSeriesFormattedValueName()
        {
            $value = ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesFormattedValueName(5);
            $this->assertEquals('SecondSeriesFormattedValue5', $value);
        }

        public function testGetType()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
           $this->assertEquals('Bar2D', $adapter->getType());
        }

        public function testGetDataNonStacked()
        {
            $data                       = array('redbluegreen');
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $this->assertEquals(array('redbluegreen'), $adapter->getData());
        }

        public function testGetDataStacked()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $data                       = array(1 =>
                                                array(ReportDataProviderToAmChartMakerAdapter::FIRST_SERIES_VALUE . 0
                                                => 500.42134),
                                                2 =>
                                                array(ReportDataProviderToAmChartMakerAdapter::SECOND_SERIES_VALUE . 0
                                                => 32),
            );
            $secondSeriesValueData      = array(0);
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'StackedBar3D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $displayAttribute           = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                          Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'float__Summation';
            $report->addDisplayAttribute($displayAttribute);
            $displayAttribute2          = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                          Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer__Summation';
            $report->addDisplayAttribute($displayAttribute2);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $compareData = array(1 => array('FirstSeriesValue0'           => 500.42134,
                                            'FirstSeriesFormattedValue0'  => 500.421),
                                 2 => array('SecondSeriesValue0'          => 32,
                                            'SecondSeriesFormattedValue0' => 32),
            );
            $this->assertEquals($compareData, $adapter->getData());
            //todo: more coverage needed. date, dateTime, string, and currency
        }

        public function testGetSecondSeriesValueCount()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                       $secondSeriesValueCount);
            $this->assertEquals(5, $adapter->getSecondSeriesValueCount());
        }

        public function testIsStackedFalse()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
            $this->assertFalse($adapter->isStacked());
        }

        public function testIsStackedTrue()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array();
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'StackedBar3D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                $secondSeriesValueCount);
            $this->assertTrue($adapter->isStacked());
        }

        public function testGetSecondSeriesDisplayLabelByKey()
        {
            $data                       = array();
            $secondSeriesValueData      = array();
            $secondSeriesDisplayLabels  = array('abc', 'def');
            $secondSeriesValueCount     = 5;
            $chart                      = new ChartForReportForm();
            $chart->type                = 'Bar2D';
            $chart->firstSeries         = 'dropDown';
            $chart->firstRange          = 'float__Summation';
            $chart->secondSeries        = 'radioDropDown';
            $chart->secondRange         = 'integer__Summation';
            $report                     = new Report();
            $report->setChart($chart);
            $adapter = new ReportDataProviderToAmChartMakerAdapter($report, $data, $secondSeriesValueData, $secondSeriesDisplayLabels,
                           $secondSeriesValueCount);
            $this->assertEquals('def', $adapter->getSecondSeriesDisplayLabelByKey(1));
        }
    }
?>