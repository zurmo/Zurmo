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
     * Test MatrixReportToExportAdapter
     */
    class MatrixReportToExportAdapterTest extends ZurmoBaseTest
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
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');

            //for date summation
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute1->attributeIndexOrDerivedType = 'date__Maximum';
            $displayAttribute1->label                       = 'New Label For Date -(Max)';
            $this->assertTrue($displayAttribute1->columnAliasName == 'col0');
            $report->addDisplayAttribute($displayAttribute1);

            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute2->attributeIndexOrDerivedType = 'date__Minimum';
            $this->assertTrue($displayAttribute2->columnAliasName == 'col1');
            $report->addDisplayAttribute($displayAttribute2);

            //for dateTime summation
            $displayAttribute3 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute3->attributeIndexOrDerivedType = 'dateTime__Maximum';
            $this->assertTrue($displayAttribute3->columnAliasName == 'col2');
            $report->addDisplayAttribute($displayAttribute3);

            $displayAttribute4 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute4->attributeIndexOrDerivedType = 'dateTime__Minimum';
            $this->assertTrue($displayAttribute4->columnAliasName == 'col3');
            $report->addDisplayAttribute($displayAttribute4);

            //for createdDateTime summation
            $displayAttribute5 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute5->attributeIndexOrDerivedType = 'createdDateTime__Maximum';
            $this->assertTrue($displayAttribute5->columnAliasName == 'col4');
            $report->addDisplayAttribute($displayAttribute5);

            $displayAttribute6 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute6->attributeIndexOrDerivedType = 'createdDateTime__Minimum';
            $this->assertTrue($displayAttribute6->columnAliasName == 'col5');
            $report->addDisplayAttribute($displayAttribute6);

            //for modifiedDateTime summation
            $displayAttribute7 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute7->attributeIndexOrDerivedType = 'modifiedDateTime__Maximum';
            $this->assertTrue($displayAttribute7->columnAliasName == 'col6');
            $report->addDisplayAttribute($displayAttribute7);

            $displayAttribute8 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute8->attributeIndexOrDerivedType = 'modifiedDateTime__Minimum';
            $this->assertTrue($displayAttribute8->columnAliasName == 'col7');
            $report->addDisplayAttribute($displayAttribute8);

            //for float summation
            $displayAttribute9 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute9->attributeIndexOrDerivedType = 'float__Minimum';
            $this->assertTrue($displayAttribute9->columnAliasName == 'col8');
            $report->addDisplayAttribute($displayAttribute9);

            $displayAttribute10 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute10->attributeIndexOrDerivedType = 'float__Maximum';
            $this->assertTrue($displayAttribute10->columnAliasName == 'col9');
            $report->addDisplayAttribute($displayAttribute10);

            $displayAttribute11 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute11->attributeIndexOrDerivedType = 'float__Summation';
            $this->assertTrue($displayAttribute11->columnAliasName == 'col10');
            $report->addDisplayAttribute($displayAttribute11);

            $displayAttribute12 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute12->attributeIndexOrDerivedType = 'float__Average';
            $this->assertTrue($displayAttribute12->columnAliasName == 'col11');
            $report->addDisplayAttribute($displayAttribute12);

            //for integer summation
            $displayAttribute13 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute13->attributeIndexOrDerivedType = 'integer__Minimum';
            $this->assertTrue($displayAttribute13->columnAliasName == 'col12');
            $report->addDisplayAttribute($displayAttribute13);

            $displayAttribute14 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute14->attributeIndexOrDerivedType = 'integer__Maximum';
            $this->assertTrue($displayAttribute14->columnAliasName == 'col13');
            $report->addDisplayAttribute($displayAttribute14);

            $displayAttribute15 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute15->attributeIndexOrDerivedType = 'integer__Summation';
            $this->assertTrue($displayAttribute15->columnAliasName == 'col14');
            $report->addDisplayAttribute($displayAttribute15);

            $displayAttribute16 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute16->attributeIndexOrDerivedType = 'integer__Average';
            $this->assertTrue($displayAttribute16->columnAliasName == 'col15');
            $report->addDisplayAttribute($displayAttribute16);

            //for currency summation
            $displayAttribute17 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute17->attributeIndexOrDerivedType = 'currencyValue__Minimum';
            $this->assertTrue($displayAttribute17->columnAliasName == 'col16');
            $report->addDisplayAttribute($displayAttribute17);

            $displayAttribute18 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute18->attributeIndexOrDerivedType = 'currencyValue__Maximum';
            $this->assertTrue($displayAttribute18->columnAliasName == 'col17');
            $report->addDisplayAttribute($displayAttribute18);

            $displayAttribute19 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute19->attributeIndexOrDerivedType = 'currencyValue__Summation';
            $this->assertTrue($displayAttribute19->columnAliasName == 'col18');
            $report->addDisplayAttribute($displayAttribute19);

            $displayAttribute20 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute20->attributeIndexOrDerivedType = 'currencyValue__Average';
            $this->assertTrue($displayAttribute20->columnAliasName == 'col19');
            $report->addDisplayAttribute($displayAttribute20);

            $displayAttribute21 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_MATRIX);
            $displayAttribute21->attributeIndexOrDerivedType = 'Count';
            $this->assertTrue($displayAttribute21->columnAliasName == 'col20');
            $report->addDisplayAttribute($displayAttribute21);

            $groupBy           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'firstName';
            $report->addGroupBy($groupBy);

            $groupBy           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'lastName';
            $groupBy->axis = 'y';
            $report->addGroupBy($groupBy);

            $dataProvider       = new MatrixReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareRowData     = array(array('',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                             ),
                                        array('Last Name',
                                              'New Label For Date -(Max)',
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
                                              'Count',
                                             ),
                                        array('xLast',
                                              '2013-02-14',
                                              '2013-02-12',
                                              '2013-02-14 23:15:00',
                                              '2013-02-12 10:15:00',
                                              $reportModelTestItem2->createdDateTime,
                                              $reportModelTestItem1->createdDateTime,
                                              $reportModelTestItem2->modifiedDateTime,
                                              $reportModelTestItem1->modifiedDateTime,
                                              '10.5',
                                              '200.5',
                                              '211',
                                              '105.5',
                                              '10',
                                              '1010',
                                              '1020',
                                              '510.0000',
                                              '100',
                                              'Mixed Currency',
                                              '100',
                                              'Mixed Currency',
                                              '200',
                                              'Mixed Currency',
                                              '100',
                                              'Mixed Currency',
                                              '2'
                                            ),
                                       );
            $this->assertEmpty($adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());

            $groupBy           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'boolean';
            $report->addGroupBy($groupBy);

            $groupBy           = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'phone';
            $groupBy->axis = 'y';
            $report->addGroupBy($groupBy);

            $dataProvider       = new MatrixReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareRowData     = array(array('',
                                              '',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                              'xFirst',
                                             ),
                                        array('',
                                              '',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                              'Yes',
                                             ),
                                        array('Last Name',
                                              'Phone',
                                              'New Label For Date -(Max)',
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
                                              'Count',
                                             ),
                                        array('xLast',
                                              '7842151012',
                                              '2013-02-14',
                                              '2013-02-12',
                                              '2013-02-14 23:15:00',
                                              '2013-02-12 10:15:00',
                                              $reportModelTestItem2->createdDateTime,
                                              $reportModelTestItem1->createdDateTime,
                                              $reportModelTestItem2->modifiedDateTime,
                                              $reportModelTestItem1->modifiedDateTime,
                                              '10.5',
                                              '200.5',
                                              '211',
                                              '105.5',
                                              '10',
                                              '1010',
                                              '1020',
                                              '510.0000',
                                              '100',
                                              'Mixed Currency',
                                              '100',
                                              'Mixed Currency',
                                              '200',
                                              'Mixed Currency',
                                              '100',
                                              'Mixed Currency',
                                              '2'
                                            ),
                                       );
            $this->assertEmpty($adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
        }

        public function testGetLeadingHeadersDataFromMatrixReportDataProviderWithALinkableAttribute()
        {
            $reportModelTestItem2        = new ReportModelTestItem2();
            $reportModelTestItem2->name  = 'report name';
            $reportModelTestItem2->phone = '123456789';
            $this->assertTrue($reportModelTestItem2->save());

            $reportModelTestItem2        = new ReportModelTestItem2();
            $reportModelTestItem2->name  = 'report name';
            $reportModelTestItem2->phone = '987654321';
            $this->assertTrue($reportModelTestItem2->save());

            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTest2Module');
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                            Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $groupBy           = new GroupByForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                            Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'phone';
            $report->addGroupBy($groupBy);

            $groupBy           = new GroupByForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                            Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'name';
            $groupBy->axis = 'y';
            $report->addGroupBy($groupBy);

            $dataProvider       = new MatrixReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareRowData     = array(
                                    array(null, '123456789', '987654321'),
                                    array('Name', 'Count', 'Count'),
                                    array('report name', 1, 1)
                                  );
            $this->assertEmpty($adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());

            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('ReportsTest2Module');
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                Report::TYPE_MATRIX);
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $groupBy           = new GroupByForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'name';
            $report->addGroupBy($groupBy);

            $groupBy           = new GroupByForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'phone';
            $groupBy->axis = 'y';
            $report->addGroupBy($groupBy);

            $dataProvider       = new MatrixReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareRowData     = array(
                array(null, 'report name'),
                array('Phone', 'Count',),
                array('123456789', 1),
                array('987654321', 1)
            );
            $this->assertEmpty($adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
        }
    }
?>