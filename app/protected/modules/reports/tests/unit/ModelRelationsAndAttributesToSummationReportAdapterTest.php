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

    class ModelRelationsAndAttributesToSummationReportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + float';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new ReportModelTestItem());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        /**
         * Testing groupBy and displayAttributes together
         */
        public function testGetAttributesForOrderBys()
        {
            $model              = new ReportModelTestItem();
            $model2             = new ReportModelTestItem2();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'hasOne___name';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);

            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem2', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model2, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForOrderBys($report->getGroupBys(), $report->getDisplayAttributes(), $model, 'hasOne');
            $this->assertEquals(2, count($attributes));
            $this->assertTrue(isset($attributes['name']));
            $this->assertTrue(isset($attributes['Count']));
        }

        /**
         * @depends testGetAttributesForOrderBys
         */
        public function testGetAttributesForChartSeries()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForChartSeries($report->getGroupBys());
            $this->assertEquals(0, count($attributes));

            //Add a group by, but not as a display attribute
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $attributes         = $adapter->getAttributesForChartSeries($report->getGroupBys());
            $this->assertEquals(0, count($attributes));

            //Add a group by as a display attribute
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'dropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'dropDown';
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartSeries($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);

            //Add a second group by as a display attribute
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'radioDropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'radioDropDown';
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartSeries($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(2, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);

            //Add a third group by that is likeContactState as a display attribute
            $groupBy            = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'likeContactState';
            $report->setModuleClassName('ReportsTestModule');
            $report->addGroupBy($groupBy);
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'likeContactState';
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartSeries($report->getGroupBys(), $report->getDisplayAttributes());
            $this->assertEquals(3, count($attributes));
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
        }

        /**
         * @depends testGetAttributesForChartSeries
         */
        public function testGetAttributesForChartRange()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules();
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter            = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $attributes         = $adapter->getAttributesForChartRange($report->getDisplayAttributes());
            $this->assertEquals(0, count($attributes));

            //Add a display attribute that cannot be a range
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'dropDown';
            $report->setModuleClassName('ReportsTestModule');
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartRange($report->getDisplayAttributes());
            $this->assertEquals(0, count($attributes));

            //Add a display attribute that can be a range
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'float__Summation';
            $report->setModuleClassName('ReportsTestModule');
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartRange($report->getDisplayAttributes());
            $this->assertEquals(1, count($attributes));
            $compareData        = array('label' => 'Float -(Sum)');
            $this->assertEquals($compareData, $attributes['float__Summation']);

            //Add a second display attribute that can be a range
            $displayAttribute   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'float__Average';
            $report->setModuleClassName('ReportsTestModule');
            $report->addDisplayAttribute($displayAttribute);
            $attributes         = $adapter->getAttributesForChartRange($report->getDisplayAttributes());
            $this->assertEquals(2, count($attributes));
            $compareData        = array('label' => 'Float -(Sum)');
            $this->assertEquals($compareData, $attributes['float__Summation']);
            $compareData        = array('label' => 'Float -(Avg)');
            $this->assertEquals($compareData, $attributes['float__Average']);
        }

        /**
         * @depends testGetAttributesForChartRange
         */
        public function testIsAttributeIndexOrDerivedTypeADisplayCalculation()
        {
            $model   = new ReportModelTestItem();
            $rules   = new ReportsTestReportRules();
            $report  = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $this->assertFalse ($adapter->isAttributeIndexOrDerivedTypeADisplayCalculation('string'));
            $this->assertTrue  ($adapter->isAttributeIndexOrDerivedTypeADisplayCalculation('float__Summation'));
            $this->assertTrue  ($adapter->isAttributeIndexOrDerivedTypeADisplayCalculation(
                                ModelRelationsAndAttributesToSummableReportAdapter::DISPLAY_CALCULATION_COUNT));
        }

        /**
         * @depends testIsAttributeIndexOrDerivedTypeADisplayCalculation
         */
        public function testIsDisplayAttributeMadeViaSelect()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $this->assertFalse($adapter->isDisplayAttributeMadeViaSelect('date'));
            $this->assertFalse($adapter->isDisplayAttributeMadeViaSelect('phone'));

            $this->assertTrue($adapter->isDisplayAttributeMadeViaSelect('date__Day'));
            $this->assertTrue($adapter->isDisplayAttributeMadeViaSelect('integer__Maximum'));
        }

        /**
         * @depends testIsDisplayAttributeMadeViaSelect
         */
        public function testIsAttributeACalculationOrModifier()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $this->assertTrue ($adapter->isAttributeACalculationOrModifier('Count'));
            $this->assertFalse($adapter->isAttributeACalculationOrModifier('phone'));
            $this->assertTrue ($adapter->isAttributeACalculationOrModifier('date__Day'));
            $this->assertTrue ($adapter->isAttributeACalculationOrModifier('integer__Maximum'));
        }

        /**
         * @depends testIsAttributeACalculationOrModifier
         */
        public function testIsAttributeACalculatedGroupByModifier()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $this->assertFalse ($adapter->isAttributeACalculatedGroupByModifier('Count'));
            $this->assertFalse ($adapter->isAttributeACalculatedGroupByModifier('phone'));
            $this->assertTrue  ($adapter->isAttributeACalculatedGroupByModifier('date__Day'));
            $this->assertFalse ($adapter->isAttributeACalculatedGroupByModifier('integer__Maximum'));
        }

        /**
         * @depends testIsAttributeACalculatedGroupByModifier
         */
        public function testResolveRealAttributeName()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $this->assertEquals ('id',     ModelRelationsAndAttributesToSummationReportAdapter::resolveRealAttributeName('Count'));
            $this->assertEquals ('string', ModelRelationsAndAttributesToSummationReportAdapter::resolveRealAttributeName('string'));
            $this->assertEquals ('owner',  ModelRelationsAndAttributesToSummationReportAdapter::resolveRealAttributeName('owner__User'));
            $this->assertEquals ('owner',  ModelRelationsAndAttributesToSummationReportAdapter::resolveRealAttributeName(
                                           'ReportsTestModel__owner__Inferred'));
        }

        /**
         * @depends testResolveRealAttributeName
         */
        public function testGetCalculationOrModifierType()
        {
            $model              = new ReportModelTestItem();
            $rules              = new ReportsTestReportRules(); //ReportsTestModule rules
            $report             = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $adapter = new ModelRelationsAndAttributesToSummationReportAdapter($model, $rules, $report->getType());
            $this->assertEquals('Maximum',   $adapter->getCalculationOrModifierType('integer__Maximum'));
            $this->assertEquals('something', $adapter->getCalculationOrModifierType('something'));
        }

        /**
         * @depends testGetCalculationOrModifierType
         */
        public function testResolveDisplayAttributeTypeAndAddSelectClause()
        {
            //todo:
            //$this->fail();
        }
    }
?>
