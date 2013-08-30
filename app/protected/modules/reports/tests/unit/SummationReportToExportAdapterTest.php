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
     * Test SummationReportToExportAdapter
     */
    class SummationReportToExportAdapterTest extends ZurmoBaseTest
    {
        public $freeze = false;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
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

        public function testGetDataWithNoRelationsSet()
        {
            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ReportTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            $this->assertTrue($saved);

            //for fullname attribute  (derived attribute)
            $reportModelTestItem1 = new ReportModelTestItem();
            $reportModelTestItem1->firstName = 'xFirst';
            $reportModelTestItem1->lastName = 'xLast';
            $reportModelTestItem1->boolean = true;
            $reportModelTestItem1->date = '2013-02-12';
            $reportModelTestItem1->dateTime = '2013-02-12 10:15:00';
            $reportModelTestItem1->float = 10.5;
            $reportModelTestItem1->integer = 10;
            $reportModelTestItem1->phone = '7842151012';
            $reportModelTestItem1->string = 'xString';
            $reportModelTestItem1->textArea = 'xtextAreatest';
            $reportModelTestItem1->url = 'http://www.test.com';
            $reportModelTestItem1->dropDown->value = $values[1];
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);
            $reportModelTestItem1->currencyValue   = $currencyValue;

            $reportModelTestItem1->primaryAddress->street1 = 'someString';
            $reportModelTestItem1->primaryEmail->emailAddress = "test@someString.com";

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem1->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem1->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $reportModelTestItem1->tagCloud->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $reportModelTestItem1->tagCloud->values->add($customFieldValue);
            $reportModelTestItem1->radioDropDown->value = $values[1];
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItem1->likeContactState = $reportModelTestItem7;
            $reportModelTestItem1->owner = Yii::app()->user->userModel;
            $saved               = $reportModelTestItem1->save();
            $this->assertTrue($saved);

            $reportModelTestItem2 = new ReportModelTestItem();
            $reportModelTestItem2->firstName = 'xFirst';
            $reportModelTestItem2->lastName = 'xLast';
            $reportModelTestItem2->boolean = true;
            $reportModelTestItem2->date = '2013-02-14';
            $reportModelTestItem2->dateTime = '2013-02-14 23:15:00';
            $reportModelTestItem2->float = 200.5;
            $reportModelTestItem2->integer = 1010;
            $reportModelTestItem2->phone = '7842151012';
            $reportModelTestItem2->string = 'xString';
            $reportModelTestItem2->textArea = 'xtextAreatest';
            $reportModelTestItem2->url = 'http://www.test.com';
            $reportModelTestItem2->dropDown->value = $values[1];
            $reportModelTestItem2->currencyValue   = $currencyValue;
            $reportModelTestItem2->primaryAddress->street1 = 'someString';
            $reportModelTestItem2->primaryEmail->emailAddress = "test@someString.com";
            $reportModelTestItem2->multiDropDown->values->add($customFieldValue);
            $reportModelTestItem2->tagCloud->values->add($customFieldValue);
            $reportModelTestItem2->radioDropDown->value = $values[1];
            $reportModelTestItem2->likeContactState = $reportModelTestItem7;
            $reportModelTestItem2->owner = Yii::app()->user->userModel;
            $saved               = $reportModelTestItem2->save();
            $this->assertTrue($saved);

            $report = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');

            //for date summation
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'date__Maximum';
            $displayAttribute1->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute1->columnAliasName == 'col0');
            $report->addDisplayAttribute($displayAttribute1);

            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->attributeIndexOrDerivedType = 'date__Minimum';
            $displayAttribute2->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute2->columnAliasName == 'col1');
            $report->addDisplayAttribute($displayAttribute2);

            //for dateTime summation
            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute3->attributeIndexOrDerivedType = 'dateTime__Maximum';
            $displayAttribute3->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute3->columnAliasName == 'col2');
            $report->addDisplayAttribute($displayAttribute3);

            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute4->attributeIndexOrDerivedType = 'dateTime__Minimum';
            $displayAttribute4->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute4->columnAliasName == 'col3');
            $report->addDisplayAttribute($displayAttribute4);

            //for createdDateTime summation
            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute5->attributeIndexOrDerivedType = 'createdDateTime__Maximum';
            $displayAttribute5->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute5->columnAliasName == 'col4');
            $report->addDisplayAttribute($displayAttribute5);

            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute6->attributeIndexOrDerivedType = 'createdDateTime__Minimum';
            $displayAttribute6->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute6->columnAliasName == 'col5');
            $report->addDisplayAttribute($displayAttribute6);

            //for modifiedDateTime summation
            $displayAttribute7 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute7->attributeIndexOrDerivedType = 'modifiedDateTime__Maximum';
            $displayAttribute7->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute7->columnAliasName == 'col6');
            $report->addDisplayAttribute($displayAttribute7);

            $displayAttribute8 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute8->attributeIndexOrDerivedType = 'modifiedDateTime__Minimum';
            $displayAttribute8->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute8->columnAliasName == 'col7');
            $report->addDisplayAttribute($displayAttribute8);

            //for float summation
            $displayAttribute9 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute9->attributeIndexOrDerivedType = 'float__Minimum';
            $displayAttribute9->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute9->columnAliasName == 'col8');
            $report->addDisplayAttribute($displayAttribute9);

            $displayAttribute10 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute10->attributeIndexOrDerivedType = 'float__Maximum';
            $displayAttribute10->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute10->columnAliasName == 'col9');
            $report->addDisplayAttribute($displayAttribute10);

            $displayAttribute11 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute11->attributeIndexOrDerivedType = 'float__Summation';
            $displayAttribute11->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute11->columnAliasName == 'col10');
            $report->addDisplayAttribute($displayAttribute11);

            $displayAttribute12 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute12->attributeIndexOrDerivedType = 'float__Average';
            $displayAttribute12->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute12->columnAliasName == 'col11');
            $report->addDisplayAttribute($displayAttribute12);

            //for integer summation
            $displayAttribute13 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute13->attributeIndexOrDerivedType = 'integer__Minimum';
            $displayAttribute13->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute13->columnAliasName == 'col12');
            $report->addDisplayAttribute($displayAttribute13);

            $displayAttribute14 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute14->attributeIndexOrDerivedType = 'integer__Maximum';
            $displayAttribute14->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute14->columnAliasName == 'col13');
            $report->addDisplayAttribute($displayAttribute14);

            $displayAttribute15 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute15->attributeIndexOrDerivedType = 'integer__Summation';
            $displayAttribute15->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute15->columnAliasName == 'col14');
            $report->addDisplayAttribute($displayAttribute15);

            $displayAttribute16 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute16->attributeIndexOrDerivedType = 'integer__Average';
            $displayAttribute16->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute16->columnAliasName == 'col15');
            $report->addDisplayAttribute($displayAttribute16);

            //for currency summation
            $displayAttribute17 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute17->attributeIndexOrDerivedType = 'currencyValue__Minimum';
            $displayAttribute17->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute17->columnAliasName == 'col16');
            $report->addDisplayAttribute($displayAttribute17);

            $displayAttribute18 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute18->attributeIndexOrDerivedType = 'currencyValue__Maximum';
            $displayAttribute18->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute18->columnAliasName == 'col17');
            $report->addDisplayAttribute($displayAttribute18);

            $displayAttribute19 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute19->attributeIndexOrDerivedType = 'currencyValue__Summation';
            $displayAttribute19->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute19->columnAliasName == 'col18');
            $report->addDisplayAttribute($displayAttribute19);

            $displayAttribute20 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute20->attributeIndexOrDerivedType = 'currencyValue__Average';
            $displayAttribute20->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute20->columnAliasName == 'col19');
            $report->addDisplayAttribute($displayAttribute20);

            $dataProvider       = new SummationReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('Date -(Max)',
                                        'Date -(Min)',
                                        'Date Time -(Max)',
                                        'Date Time -(Min)',
                                        'Created Date Time -(Max)',
                                        'Created Date Time -(Min)',
                                        'Modified Date Time -(Max)',
                                        'Modified Date Time -(Min)',
                                        'Float -(Min)',
                                        'Float -(Max)',
                                        'Float -(Sum)',
                                        'Float -(Avg)',
                                        'Integer -(Min)',
                                        'Integer -(Max)',
                                        'Integer -(Sum)',
                                        'Integer -(Avg)',
                                        'Currency Value -(Min)',
                                        'Currency Value -(Min) Currency',
                                        'Currency Value -(Max)',
                                        'Currency Value -(Max) Currency',
                                        'Currency Value -(Sum)',
                                        'Currency Value -(Sum) Currency',
                                        'Currency Value -(Avg)',
                                        'Currency Value -(Avg) Currency');
            $compareRowData     = array(array('2013-02-14',
                                              '2013-02-12',
                                              '2013-02-14 23:15:00',
                                              '2013-02-12 10:15:00',
                                              $reportModelTestItem2->createdDateTime,
                                              $reportModelTestItem1->createdDateTime,
                                              $reportModelTestItem2->modifiedDateTime,
                                              $reportModelTestItem1->modifiedDateTime,
                                              10.5, 200.5, 211, 105.5,
                                              10, 1010, 1020, 510,
                                              100, 'Mixed Currency',
                                              100, 'Mixed Currency',
                                              200, 'Mixed Currency',
                                              100, 'Mixed Currency'));
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());

            //With drill down
            $groupBy           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                Report::TYPE_SUMMATION);
            $groupBy->attributeIndexOrDerivedType = 'firstName';
            $report->addGroupBy($groupBy);

            $drillDownDisplayAttribute1 = new DrillDownDisplayAttributeForReportForm('ReportsTestModule',
                                                                'ReportModelTestItem', Report::TYPE_SUMMATION);
            $drillDownDisplayAttribute1->attributeIndexOrDerivedType = 'float';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute1);
            $drillDownDisplayAttribute2 = new DrillDownDisplayAttributeForReportForm('ReportsTestModule',
                                                                'ReportModelTestItem', Report::TYPE_SUMMATION);
            $drillDownDisplayAttribute2->attributeIndexOrDerivedType = 'integer';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute2);
            $dataProvider       = new SummationReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('Date -(Max)',
                                        'Date -(Min)',
                                        'Date Time -(Max)',
                                        'Date Time -(Min)',
                                        'Created Date Time -(Max)',
                                        'Created Date Time -(Min)',
                                        'Modified Date Time -(Max)',
                                        'Modified Date Time -(Min)',
                                        'Float -(Min)',
                                        'Float -(Max)',
                                        'Float -(Sum)',
                                        'Float -(Avg)',
                                        'Integer -(Min)',
                                        'Integer -(Max)',
                                        'Integer -(Sum)',
                                        'Integer -(Avg)',
                                        'Currency Value -(Min)',
                                        'Currency Value -(Min) Currency',
                                        'Currency Value -(Max)',
                                        'Currency Value -(Max) Currency',
                                        'Currency Value -(Sum)',
                                        'Currency Value -(Sum) Currency',
                                        'Currency Value -(Avg)',
                                        'Currency Value -(Avg) Currency',
                                        'First Name');
            $compareRowData     = array(array('2013-02-14',
                                              '2013-02-12',
                                              '2013-02-14 23:15:00',
                                              '2013-02-12 10:15:00',
                                              $reportModelTestItem2->createdDateTime,
                                              $reportModelTestItem1->createdDateTime,
                                              $reportModelTestItem2->modifiedDateTime,
                                              $reportModelTestItem1->modifiedDateTime,
                                              10.5, 200.5, 211, 105.5,
                                              10, 1010, 1020, 510,
                                              100, 'Mixed Currency',
                                              100, 'Mixed Currency',
                                              200, 'Mixed Currency',
                                              100, 'Mixed Currency',
                                              'xFirst'),
                                        array(null, 'Float', 'Integer'),
                                        array(null, '10.5', '10'),
                                        array(null, '200.5', '1010'));
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
        }
    }
?>