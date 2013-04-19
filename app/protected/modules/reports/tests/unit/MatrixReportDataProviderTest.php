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

    class MatrixReportDataProviderTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testResolveDisplayAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Maximum';
            $report->addDisplayAttribute($displayAttribute);
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'float';
            $groupBy->axis    = 'y';
            $report->addGroupBy($groupBy);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $displayAttributes = $reportDataProvider->resolveDisplayAttributes();
            $this->assertCount(2, $displayAttributes);
        }

        public function testResolveGroupBys()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'integer';
            $groupBy->axis = 'x';
            $groupBy2         = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy2->attributeIndexOrDerivedType = 'float';
            $groupBy2->axis   = 'y';
            $report->addGroupBy($groupBy);
            $report->addGroupBy($groupBy2);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $groupBys = $reportDataProvider->resolveGroupBys();
            $this->assertCount(2, $groupBys);
            $this->assertEquals('float',   $groupBys[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('integer', $groupBys[1]->getAttributeIndexOrDerivedType());
        }

        public function testGetXAxisGroupByDataValuesCount()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Maximum';
            $report->addDisplayAttribute($displayAttribute);
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'float';
            $groupBy->axis    = 'x';
            $report->addGroupBy($groupBy);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $count = $reportDataProvider->getXAxisGroupByDataValuesCount();
            $this->assertEquals(1, $count);
        }

        public function testGetYAxisGroupByDataValuesCount()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Maximum';
            $report->addDisplayAttribute($displayAttribute);
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'float';
            $groupBy->axis    = 'y';
            $report->addGroupBy($groupBy);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $count = $reportDataProvider->getYAxisGroupByDataValuesCount();
            $this->assertEquals(0, $count);
        }

        public function testMakeXAxisGroupingsForColumnNamesData()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Maximum';
            $report->addDisplayAttribute($displayAttribute);
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'float';
            $groupBy->axis    = 'x';
            $report->addGroupBy($groupBy);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $data               = $reportDataProvider->makeXAxisGroupingsForColumnNamesData();
            $this->assertEquals(array(), $data);
        }

        public function testMakeAxisCrossingColumnCountAndLeadingHeaderRowsData()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Maximum';
            $report->addDisplayAttribute($displayAttribute);
            $groupBy          = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'float';
            $groupBy->axis    = 'x';
            $report->addGroupBy($groupBy);
            $reportDataProvider = new MatrixReportDataProvider($report);
            $data               = $reportDataProvider->makeAxisCrossingColumnCountAndLeadingHeaderRowsData();
            $compareData        = array('rows' => array(), 'axisCrossingColumnCount' => 0);
            $this->assertEquals($compareData, $data);
        }
    }
?>