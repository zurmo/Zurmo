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

    class ReportResultsRowDataTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
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

        public function testGetCurrencyValueAndDateAttributesOnOwnedModel()
        {
            $reportModelTestItem11            = new ReportModelTestItem11();
            $reportModelTestItem11->date      = '2002-12-12';
            $reportModelTestItem11b           = new ReportModelTestItem11();
            $reportModelTestItem11b->date     = '2002-12-13';
            $reportModelTestItem10            = new ReportModelTestItem10();
            $reportModelTestItem10->reportModelTestItem11->add($reportModelTestItem11);
            $reportModelTestItem10->reportModelTestItem11->add($reportModelTestItem11b);
            $this->assertTrue($reportModelTestItem10->save());
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem10',
                                    Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'reportModelTestItem11___date';
            $this->assertEquals('col0', $displayAttributeX->columnAliasName);

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItem11b, 'abc');

            $model = $reportResultsRowData->getModel('attribute0');
            $this->assertEquals('2002-12-13', $model->date);
        }

        public function testGetModel()
        {
            $reportModelTestItemX = new ReportModelTestItem();
            $reportModelTestItemX->firstName = 'xFirst';
            $reportModelTestItemX->lastName = 'xLast';
            $displayAttributeX    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'FullName';

            $reportModelTestItemY = new ReportModelTestItem();
            $reportModelTestItemY->firstName = 'yFirst';
            $reportModelTestItemY->lastName = 'yLast';
            $displayAttributeY    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeY->setModelAliasUsingTableAliasName('def');
            $displayAttributeY->attributeIndexOrDerivedType = 'FullName';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');
            $reportResultsRowData->addModelAndAlias($reportModelTestItemY, 'def');

            $model1 = $reportResultsRowData->getModel('attribute0');
            $this->assertEquals('xFirst xLast', strval($model1));
            $model2 = $reportResultsRowData->getModel('attribute1');
            $this->assertEquals('yFirst yLast', strval($model2));
        }

        public function testGettingAttributeForString()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->string = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'string';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someString', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeForOwnedString()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->primaryAddress->street1 = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'primaryAddress___street1';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someString', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeForLikeContactState()
        {
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->likeContactState = $reportModelTestItem7;
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'likeContactState';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');

            $this->assertEquals('someName', $reportResultsRowData->attribute0);
        }

        public function testGettingAttributeWhenMadeViaSelect()
        {
            $displayAttributeX = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttributeX->attributeIndexOrDerivedType = 'integer__Maximum';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $reportResultsRowData->addSelectedColumnNameAndValue('col5', 55);

            $this->assertEquals(55, $reportResultsRowData->col5);
        }

        public function testGetDataParamsForDrillDownAjaxCall()
        {
            $reportModelTestItem                       = new ReportModelTestItem();
            $reportModelTestItem->dropDown->value      = 'dropDownValue';
            $reportModelTestItem->currencyValue->value = 45.05;
            $reportModelTestItem->owner                = Yii::app()->user->userModel;

            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItem->likeContactState = $reportModelTestItem7;

            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'dropDown';
            $displayAttribute1->valueUsedAsDrillDownFilter = true;
            $displayAttribute1->setModelAliasUsingTableAliasName('abc');

            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'integer';

            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType = 'currencyValue';
            $displayAttribute3->valueUsedAsDrillDownFilter = true;
            $displayAttribute3->setModelAliasUsingTableAliasName('abc');

            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType = 'createdDateTime__Day';
            $displayAttribute4->valueUsedAsDrillDownFilter = true;

            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType = 'owner__User';
            $displayAttribute5->valueUsedAsDrillDownFilter = true;
            $displayAttribute5->setModelAliasUsingTableAliasName('abc');

            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType = 'likeContactState';
            $displayAttribute6->valueUsedAsDrillDownFilter = true;
            $displayAttribute6->setModelAliasUsingTableAliasName('abc');

            $reportResultsRowData = new ReportResultsRowData(array($displayAttribute1, $displayAttribute2,
                                                                   $displayAttribute3, $displayAttribute4,
                                                                   $displayAttribute5, $displayAttribute6), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItem, 'abc');
            $reportResultsRowData->addSelectedColumnNameAndValue('col3', 15);
            $data   = $reportResultsRowData->getDataParamsForDrillDownAjaxCall();
            $userId = Yii::app()->user->userModel->id;
            $this->assertEquals('dropDownValue',           $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('dropDown')]);
            $this->assertEquals(45.05,                     $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('currencyValue')]);
            $this->assertEquals(15,                        $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('createdDateTime__Day')]);
            $this->assertEquals($userId,                   $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('owner__User')]);
            $this->assertEquals($reportModelTestItem7->id, $data[ReportResultsRowData::resolveDataParamKeyForDrillDown('likeContactState')]);
        }

        public function testResolveDataParamKeyForDrillDown()
        {
            $this->assertEquals(ReportResultsRowData::DRILL_DOWN_GROUP_BY_VALUE_PREFIX . 'abc',
                                ReportResultsRowData::resolveDataParamKeyForDrillDown('abc'));
        }

        public function testWhenResolveValueFromModelHasNoModelAndReturnsProperDefaultModel()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->string = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'string';
            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX), 4);
            $this->assertNull($reportResultsRowData->attribute0);
        }

        public function testGetAttributeLabel()
        {
            $reportModelTestItemX         = new ReportModelTestItem();
            $reportModelTestItemX->string = 'someString';
            $displayAttributeX            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_SUMMATION);
            $displayAttributeX->setModelAliasUsingTableAliasName('abc');
            $displayAttributeX->attributeIndexOrDerivedType = 'string';
            $displayAttributeY = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 Report::TYPE_SUMMATION);
            $displayAttributeY->attributeIndexOrDerivedType = 'integer__Maximum';

            $reportResultsRowData = new ReportResultsRowData(array($displayAttributeX, $displayAttributeY), 4);
            $reportResultsRowData->addModelAndAlias($reportModelTestItemX, 'abc');
            $reportResultsRowData->addSelectedColumnNameAndValue('col1', 55);

            //Test a viaModel attribute
            $this->assertEquals('String', $reportResultsRowData->getAttributeLabel('attribute0'));

            //Test a viaSelect attriubte
            $this->assertEquals('Integer -(Max)', $reportResultsRowData->getAttributeLabel('col1'));
        }
    }
?>