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

    class SavedReportToReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
            DrillDownDisplayAttributeForReportForm::resetCount();
        }

        public function testResolveReportToSavedReport()
        {
            $billy       = User::getByUsername('billy');
            $report      = new Report();
            $report->setDescription    ('aDescription');
            $report->setModuleClassName('ReportsTestModule');
            $report->setName           ('myFirstReport');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $report->setOwner          ($billy);
            $report->setFiltersStructure('1 and 2 or 3');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_SPOT);
            $report->setSpotConversionCurrencyCode('CAD');

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->value                       = 'aValue';
            $filter->operator                    = 'equals';
            $filter->availableAtRunTime          = true;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $filter->value                       = 'aValue';
            $filter->secondValue                 = 'bValue';
            $filter->operator                    = 'between';
            $filter->currencyIdForValue          = '4';
            $filter->availableAtRunTime          = true;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'owner__User';
            $filter->value                       = 'aValue';
            $filter->stringifiedModelForValue    = 'someName';
            $filter->availableAtRunTime          = false;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'createdDateTime';
            $filter->value                       = 'aValue';
            $filter->secondValue                 = 'bValue';
            $filter->operator                    = null;
            $filter->currencyIdForValue          = null;
            $filter->availableAtRunTime          = true;
            $filter->valueType                   = 'Between';
            $report->addFilter($filter);

            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'lastName';
            $groupBy->axis                        = 'y';
            $report->addGroupBy($groupBy);

            $orderBy = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $orderBy->attributeIndexOrDerivedType = 'url';
            $orderBy->order                       = 'desc';
            $report->addOrderBy($orderBy);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('ReportsTestModule',
                                                                  'ReportModelTestItem', $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'firstName';
            $drillDownDisplayAttribute->label                       = 'someNewLabel';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $savedReport = new SavedReport();
            $this->assertNull($savedReport->serializedData);

            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);

            $this->assertEquals('ReportsTestModule',           $savedReport->moduleClassName);
            $this->assertEquals('myFirstReport',               $savedReport->name);
            $this->assertEquals('aDescription',                $savedReport->description);
            $this->assertEquals(Report::TYPE_ROWS_AND_COLUMNS, $savedReport->type);
            $this->assertEquals('1 and 2 or 3',                $report->getFiltersStructure());
            $this->assertTrue($savedReport->owner->isSame($billy));
            $compareData = array('Filters' => array(
                array(
                    'availableAtRunTime'           => true,
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'string',
                    'operator'                     => 'equals',
                ),
                array(
                    'availableAtRunTime'           => true,
                    'currencyIdForValue'           => '4',
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'currencyValue',
                    'operator'                     => 'between',
                ),
                array(
                    'availableAtRunTime'           => false,
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => 'someName',
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'owner__User',
                    'operator'                     => null,
                ),
                array(
                    'availableAtRunTime'           => true,
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => 'Between',
                    'attributeIndexOrDerivedType'  => 'createdDateTime',
                    'operator'                     => null,
                    'currencyIdForValue'           => null,
                ),
            ),
            'OrderBys' => array(
                array(
                    'order'                        => 'desc',
                    'attributeIndexOrDerivedType'  => 'url',
                )
            ),
            'GroupBys' => array(
                array(
                    'axis' => 'y',
                    'attributeIndexOrDerivedType' => 'lastName',
                ),
            ),
            'DisplayAttributes' => array(
                array(
                    'label'                          => 'someNewLabel',
                    'attributeIndexOrDerivedType'    => 'phone',
                    'columnAliasName'                => 'col0',
                    'queryOnly'                      => false,
                    'valueUsedAsDrillDownFilter'     => false,
                    'madeViaSelectInsteadOfViaModel' => false,
                )
            ),
            'DrillDownDisplayAttributes' => array(
                array(
                    'label'                          => 'someNewLabel',
                    'attributeIndexOrDerivedType'    => 'firstName',
                    'columnAliasName'                => 'col0',
                    'queryOnly'                      => false,
                    'valueUsedAsDrillDownFilter'     => false,
                    'madeViaSelectInsteadOfViaModel' => false,
                )
            ));
            $unserializedData = unserialize($savedReport->serializedData);
            $this->assertEquals($compareData['Filters'],                     $unserializedData['Filters']);
            $this->assertEquals($compareData['OrderBys'],                    $unserializedData['OrderBys']);
            $this->assertEquals($compareData['GroupBys'],                    $unserializedData['GroupBys']);
            $this->assertEquals($compareData['DisplayAttributes'],           $unserializedData['DisplayAttributes']);
            $this->assertEquals($compareData['DrillDownDisplayAttributes'],  $unserializedData['DrillDownDisplayAttributes']);
            $this->assertEquals('1 and 2 or 3',                              $unserializedData['filtersStructure']);
            $this->assertEquals(Report::CURRENCY_CONVERSION_TYPE_SPOT,       $unserializedData['currencyConversionType']);
            $this->assertEquals('CAD',                                       $unserializedData['spotConversionCurrencyCode']);
            $saved = $savedReport->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveReportToSavedReport
         */
        public function testMakeReportBySavedReport()
        {
            $billy                      = User::getByUsername('billy');
            $savedReports               = SavedReport::getAll();
            $this->assertEquals           (1, count($savedReports));
            $savedReport                = $savedReports[0];
            $report                     = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $filters                    = $report->getFilters();
            $groupBys                   = $report->getGroupBys();
            $orderBys                   = $report->getOrderBys();
            $displayAttributes          = $report->getDisplayAttributes();
            $drillDownDisplayAttributes = $report->getDrillDownDisplayAttributes();
            $this->assertEquals           ('ReportsTestModule',           $report->getModuleClassName());
            $this->assertEquals           ('myFirstReport',               $report->getName());
            $this->assertEquals           ('aDescription',                $report->getDescription());
            $this->assertEquals           (Report::TYPE_ROWS_AND_COLUMNS, $report->getType());
            $this->assertEquals           ('1 and 2 or 3',                $report->getFiltersStructure());
            $this->assertTrue             ($report->getOwner()->isSame($billy));
            $this->assertCount            (4, $filters);
            $this->assertCount            (1, $groupBys);
            $this->assertCount            (1, $orderBys);
            $this->assertCount            (1, $displayAttributes);
            $this->assertCount            (1, $drillDownDisplayAttributes);

            $this->assertEquals           (true,         $filters[0]->availableAtRunTime);
            $this->assertEquals           ('aValue',     $filters[0]->value);
            $this->assertEquals           ('string',     $filters[0]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[0]->currencyIdForValue);
            $this->assertNull             ($filters[0]->secondValue);
            $this->assertNull             ($filters[0]->stringifiedModelForValue);
            $this->assertNull             ($filters[0]->valueType);
            $this->assertEquals           ('equals',     $filters[0]->operator);

            $this->assertEquals           (true,             $filters[1]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $filters[1]->value);
            $this->assertEquals           ('currencyValue',  $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals           (4,                $filters[1]->currencyIdForValue);
            $this->assertEquals           ('bValue',         $filters[1]->secondValue);
            $this->assertNull             ($filters[1]->stringifiedModelForValue);
            $this->assertNull             ($filters[1]->valueType);
            $this->assertEquals           ('between',         $filters[1]->operator);

            $this->assertEquals           (false,            $filters[2]->availableAtRunTime);
            $this->assertEquals           ('aValue',         $filters[2]->value);
            $this->assertEquals           ('owner__User',    $filters[2]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[2]->currencyIdForValue);
            $this->assertNull             ($filters[2]->secondValue);
            $this->assertEquals           ('someName',       $filters[2]->stringifiedModelForValue);
            $this->assertNull             ($filters[2]->valueType);
            $this->assertNull             ($filters[2]->operator);

            $this->assertEquals           (true,               $filters[3]->availableAtRunTime);
            $this->assertEquals           ('aValue',           $filters[3]->value);
            $this->assertEquals           ('createdDateTime',  $filters[3]->attributeIndexOrDerivedType);
            $this->assertNull             ($filters[3]->currencyIdForValue);
            $this->assertEquals           ('bValue',           $filters[3]->secondValue);
            $this->assertNull             ($filters[3]->stringifiedModelForValue);
            $this->assertNull             ($filters[3]->operator);
            $this->assertEquals           ('Between',          $filters[3]->valueType);

            $this->assertEquals           ('url',              $orderBys[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('desc',             $orderBys[0]->order);

            $this->assertEquals           ('lastName',         $groupBys[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('y',                $groupBys[0]->axis);

            $this->assertEquals           ('phone',            $displayAttributes[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('someNewLabel',     $displayAttributes[0]->label);

            $this->assertEquals           ('firstName',        $drillDownDisplayAttributes[0]->attributeIndexOrDerivedType);
            $this->assertEquals           ('someNewLabel',     $drillDownDisplayAttributes[0]->label);
        }
    }
?>