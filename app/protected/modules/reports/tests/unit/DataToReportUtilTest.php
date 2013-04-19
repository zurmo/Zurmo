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

    class DataToReportUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('bobby');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testRowsAndColumnsResolveReportByWizardPostData()
        {
            $bobby               = User::getByUserName('bobby');
            $wizardFormClassName = 'RowsAndColumnsReportWizardForm';
            $report              = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $data                     = array();
            $data['moduleClassName']  = 'ReportsTestModule';
            $data['description']      = 'a description';
            $data['name']             = 'name';
            $data['filtersStructure'] = '1 AND 2';
            $data['ownerId']          = $bobby->id;
            $data['currencyConversionType']     = Report::CURRENCY_CONVERSION_TYPE_SPOT;
            $data['spotConversionCurrencyCode'] = 'EUR';
            $data[ComponentForReportForm::TYPE_FILTERS][]   = array('attributeIndexOrDerivedType' => 'date',
                                                                    'valueType'                    => 'Between',
                                                                    'value'                       => '2/24/12',
                                                                    'secondValue'                 => '2/28/12');
            $data[ComponentForReportForm::TYPE_ORDER_BYS][] = array('attributeIndexOrDerivedType' => 'string',
                                                                    'order' => 'desc');
            $data[ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES][] = array('attributeIndexOrDerivedType' => 'phone',
                                                                             'label' => 'custom');
            DataToReportUtil::resolveReportByWizardPostData($report, array('RowsAndColumnsReportWizardForm' => $data),
                                                            $wizardFormClassName);
            $this->assertEquals('ReportsTestModule',                    $report->getModuleClassName());
            $this->assertEquals('a description',                        $report->getDescription());
            $this->assertEquals('name',                                 $report->getName());
            $this->assertEquals('1 AND 2',                              $report->getFiltersStructure());
            $this->assertEquals($bobby->id,                             $report->getOwner()->id);
            $this->assertEquals(Report::CURRENCY_CONVERSION_TYPE_SPOT,  $report->getCurrencyConversionType());
            $this->assertEquals('EUR',                                  $report->getSpotConversionCurrencyCode());

            $filters = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('date',       $filters[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('Between',    $filters[0]->valueType);
            $this->assertEquals('2012-02-24', $filters[0]->value);
            $this->assertEquals('2012-02-28', $filters[0]->secondValue);
            $orderBys = $report->getOrderBys();
            $this->assertCount(1, $orderBys);
            $this->assertEquals('string', $orderBys[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('desc',   $orderBys[0]->order);
            $displayAttributes = $report->getDisplayAttributes();
            $this->assertCount(1, $orderBys);
            $this->assertEquals('phone',  $displayAttributes[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('custom', $displayAttributes[0]->label);
        }

        public function testSummationResolveReportByWizardPostData()
        {
            $bobby               = User::getByUserName('bobby');
            $wizardFormClassName = 'SummationReportWizardForm';
            $report              = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $data                     = array();
            $data['moduleClassName']  = 'ReportsTestModule';
            $data['description']      = 'a description';
            $data['name']             = 'name';
            $data['filtersStructure'] = '1 AND 2';
            $data['ownerId']          = $bobby->id;
            $data['currencyConversionType']     = Report::CURRENCY_CONVERSION_TYPE_SPOT;
            $data['spotConversionCurrencyCode'] = 'EUR';
            $data[ComponentForReportForm::TYPE_FILTERS][]   = array('attributeIndexOrDerivedType' => 'date',
                                                                    'valueType'                    => 'Between',
                                                                    'value'                       => '2/24/12',
                                                                    'secondValue'                 => '2/28/12');
            $data[ComponentForReportForm::TYPE_ORDER_BYS][] = array('attributeIndexOrDerivedType' => 'string',
                                                                    'order' => 'desc');
            $data[ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES][] = array('attributeIndexOrDerivedType' => 'phone',
                                                                             'label' => 'custom');
            $data[ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES][] =
                                                                       array('attributeIndexOrDerivedType' => 'string',
                                                                             'label' => 'custom string');
            $data[ComponentForReportForm::TYPE_GROUP_BYS][] = array('attributeIndexOrDerivedType' => 'string',
                                                                    'axis' => 'y');
            $data['ChartForReportForm'] = array('type'        => ChartRules::TYPE_BAR_2D,
                                                'firstSeries' => 'string',
                                                'firstRange'  => 'integer__Maximum');
            DataToReportUtil::resolveReportByWizardPostData($report, array('SummationReportWizardForm' => $data),
                                                            $wizardFormClassName);
            $this->assertEquals('ReportsTestModule',                    $report->getModuleClassName());
            $this->assertEquals('a description',                        $report->getDescription());
            $this->assertEquals('name',                                 $report->getName());
            $this->assertEquals('1 AND 2',                              $report->getFiltersStructure());
            $this->assertEquals($bobby->id,                             $report->getOwner()->id);
            $this->assertEquals(Report::CURRENCY_CONVERSION_TYPE_SPOT,  $report->getCurrencyConversionType());
            $this->assertEquals('EUR',                                  $report->getSpotConversionCurrencyCode());

            $filters = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('date',       $filters[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('Between',    $filters[0]->valueType);
            $this->assertEquals('2012-02-24', $filters[0]->value);
            $this->assertEquals('2012-02-28', $filters[0]->secondValue);
            $orderBys = $report->getOrderBys();
            $this->assertCount(1, $orderBys);
            $this->assertEquals('string', $orderBys[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('desc',   $orderBys[0]->order);
            $displayAttributes = $report->getDisplayAttributes();
            $this->assertCount(1, $displayAttributes);
            $this->assertEquals('phone',  $displayAttributes[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('custom', $displayAttributes[0]->label);
            $groupBys = $report->getGroupBys();
            $this->assertCount(1, $groupBys);
            $this->assertEquals('string', $groupBys[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('y',   $groupBys[0]->axis);
            $drillDownDisplayAttributes = $report->getDrillDownDisplayAttributes();
            $this->assertCount(1, $drillDownDisplayAttributes);
            $this->assertEquals('string',        $drillDownDisplayAttributes[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('custom string', $drillDownDisplayAttributes[0]->label);
            $chart = $report->getChart();
            $this->assertEquals(ChartRules::TYPE_BAR_2D, $chart->type);
            $this->assertEquals('string', $chart->firstSeries);
            $this->assertEquals('integer__Maximum', $chart->firstRange);
        }

        public function testResolveFiltersAndDateConvertsProperlyToDbFormat()
        {
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $data   = array();
            $data[ComponentForReportForm::TYPE_FILTERS][] = array('attributeIndexOrDerivedType' => 'date',
                                                                  'valueType'                   => 'Between',
                                                                  'value'                       => '2/24/12',
                                                                  'secondValue'                 => '2/28/12');

            DataToReportUtil::resolveFilters($data, $report);
            $filters = $report->getFilters();
            $this->assertCount(1, $filters);
            $this->assertEquals('Between', $filters[0]->valueType);
            $this->assertEquals('2012-02-24', $filters[0]->value);
            $this->assertEquals('2012-02-28', $filters[0]->secondValue);
        }

        public function testSanitizeFiltersData()
        {
            //test specifically for date/dateTime conversion from local to db format.
            $filtersData         = array();
            $filtersData[0]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12');
            $filtersData[1]      = array('attributeIndexOrDerivedType' => 'dateTime', 'value' => '2/25/12');
            $filtersData[2]      = array('attributeIndexOrDerivedType' => 'date',     'value' => '2/24/12',
                                         'secondValue'                 => '2/28/12');
            $sanitizedFilterData = DataToReportUtil::sanitizeFiltersData('ReportsTestModule',
                                                                         Report::TYPE_ROWS_AND_COLUMNS, $filtersData);
            $this->assertEquals('2012-02-24', $sanitizedFilterData[0]['value']);
            $this->assertEquals('2012-02-25', $sanitizedFilterData[1]['value']);
            $this->assertEquals('2012-02-24', $sanitizedFilterData[2]['value']);
            $this->assertEquals('2012-02-28', $sanitizedFilterData[2]['secondValue']);
        }
    }
?>