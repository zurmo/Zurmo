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
     * Test RowsAndColumnsReportToExportAdapter
     */
    class RowsAndColumnsReportToExportAdapterTest extends ZurmoBaseTest
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

            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');

            //for fullname attribute  (derived attribute)
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->firstName = 'xFirst';
            $reportModelTestItem->lastName = 'xLast';
            $displayAttribute1   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('model1');
            $displayAttribute1->attributeIndexOrDerivedType = 'FullName';
            $displayAttribute1->label = 'Name';
            $report->addDisplayAttribute($displayAttribute1);

            //for boolean attribute
            $reportModelTestItem->boolean = true;
            $displayAttribute2   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('model1');
            $displayAttribute2->attributeIndexOrDerivedType = 'boolean';
            $report->addDisplayAttribute($displayAttribute2);

            //for date attribute
            $reportModelTestItem->date = '2013-02-12';
            $displayAttribute3   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->setModelAliasUsingTableAliasName('model1');
            $displayAttribute3->attributeIndexOrDerivedType = 'date';
            $report->addDisplayAttribute($displayAttribute3);

            //for datetime attribute
            $reportModelTestItem->dateTime = '2013-02-12 10:15:00';
            $displayAttribute4   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute4->setModelAliasUsingTableAliasName('model1');
            $displayAttribute4->attributeIndexOrDerivedType = 'dateTime';
            $report->addDisplayAttribute($displayAttribute4);

            //for float attribute
            $reportModelTestItem->float = 10.5;
            $displayAttribute5   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute5->setModelAliasUsingTableAliasName('model1');
            $displayAttribute5->attributeIndexOrDerivedType = 'float';
            $report->addDisplayAttribute($displayAttribute5);

            //for integer attribute
            $reportModelTestItem->integer = 10;
            $displayAttribute6   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute6->setModelAliasUsingTableAliasName('model1');
            $displayAttribute6->attributeIndexOrDerivedType = 'integer';
            $report->addDisplayAttribute($displayAttribute6);

            //for phone attribute
            $reportModelTestItem->phone = '7842151012';
            $displayAttribute7   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute7->setModelAliasUsingTableAliasName('model1');
            $displayAttribute7->attributeIndexOrDerivedType = 'phone';
            $report->addDisplayAttribute($displayAttribute7);

            //for string attribute
            $reportModelTestItem->string = 'xString';
            $displayAttribute8   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute8->setModelAliasUsingTableAliasName('model1');
            $displayAttribute8->attributeIndexOrDerivedType = 'string';
            $report->addDisplayAttribute($displayAttribute8);

            //for textArea attribute
            $reportModelTestItem->textArea = 'xtextAreatest';
            $displayAttribute9   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute9->setModelAliasUsingTableAliasName('model1');
            $displayAttribute9->attributeIndexOrDerivedType = 'textArea';
            $report->addDisplayAttribute($displayAttribute9);

            //for url attribute
            $reportModelTestItem->url = 'http://www.test.com';
            $displayAttribute10   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute10->setModelAliasUsingTableAliasName('model1');
            $displayAttribute10->attributeIndexOrDerivedType = 'url';
            $report->addDisplayAttribute($displayAttribute10);

            //for dropdown attribute
            $reportModelTestItem->dropDown->value = $values[1];
            $displayAttribute11   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute11->setModelAliasUsingTableAliasName('model1');
            $displayAttribute11->attributeIndexOrDerivedType = 'dropDown';
            $report->addDisplayAttribute($displayAttribute11);

            //for currency attribute
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $reportModelTestItem->currencyValue   = $currencyValue;
            $displayAttribute12   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute12->setModelAliasUsingTableAliasName('model1');
            $displayAttribute12->attributeIndexOrDerivedType = 'currencyValue';
            $report->addDisplayAttribute($displayAttribute12);

            //for primaryAddress attribute
            $reportModelTestItem->primaryAddress->street1 = 'someString';
            $displayAttribute13   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute13->setModelAliasUsingTableAliasName('model1');
            $displayAttribute13->attributeIndexOrDerivedType = 'primaryAddress___street1';
            $report->addDisplayAttribute($displayAttribute13);

            //for primaryEmail attribute
            $reportModelTestItem->primaryEmail->emailAddress = "test@someString.com";
            $displayAttribute14   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute14->setModelAliasUsingTableAliasName('model1');
            $displayAttribute14->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $report->addDisplayAttribute($displayAttribute14);

            //for multiDropDown attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $displayAttribute15   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute15->setModelAliasUsingTableAliasName('model1');
            $displayAttribute15->attributeIndexOrDerivedType = 'multiDropDown';
            $report->addDisplayAttribute($displayAttribute15);

            //for tagCloud attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $displayAttribute16   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute16->setModelAliasUsingTableAliasName('model1');
            $displayAttribute16->attributeIndexOrDerivedType = 'tagCloud';
            $report->addDisplayAttribute($displayAttribute16);

            //for radioDropDown attribute
            $reportModelTestItem->radioDropDown->value = $values[1];
            $displayAttribute17   = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute17->setModelAliasUsingTableAliasName('model1');
            $displayAttribute17->attributeIndexOrDerivedType = 'radioDropDown';
            $report->addDisplayAttribute($displayAttribute17);

            //for likeContactState
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItem->likeContactState = $reportModelTestItem7;
            $displayAttribute18            = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute18->setModelAliasUsingTableAliasName('model1');
            $displayAttribute18->attributeIndexOrDerivedType = 'likeContactState';
            $report->addDisplayAttribute($displayAttribute18);

            //for dynamic user attribute
            $reportModelTestItem->owner = Yii::app()->user->userModel;
            $displayAttribute19    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute19->setModelAliasUsingTableAliasName('model1');
            $displayAttribute19->attributeIndexOrDerivedType = 'owner__User';
            $report->addDisplayAttribute($displayAttribute19);

            $saved              = $reportModelTestItem->save();
            $this->assertTrue($saved);
            $dataProvider       = new RowsAndColumnsReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array( 'Name', 'Boolean', 'Date', 'Date Time', 'Float',
                                         'Integer', 'Phone', 'String', 'Text Area', 'Url', 'Drop Down',
                                         'Currency Value', 'Currency Value Currency', 'Primary Address >> Street 1',
                                         'Primary Email >> Email Address', 'Multi Drop Down',
                                         'Tag Cloud', 'Radio Drop Down', 'A name for a state', 'Owner');
            $compareRowData     = array(array( 'xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15:00',
                                         10.5, 10, '7842151012', 'xString', 'xtextAreatest',
                                         'http://www.test.com', 'Test2', '100.00', 'USD', 'someString', 'test@someString.com',
                                         'Multi 1,Multi 2', 'Cloud 2,Cloud 3', 'Test2', 'someName', 'super')); // Not Coding Standard
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
            $reportModelTestItem->delete();
        }

        public function testExportRelationAttributes()
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
            assert('$saved'); // Not Coding Standard

            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTest2Module');
            $report->setFiltersStructure('');

            //for fullname attribute
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->firstName = 'xFirst';
            $reportModelTestItem->lastName = 'xLast';
            $displayAttribute1    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute1->attributeIndexOrDerivedType = 'hasMany2___FullName';
            $displayAttribute1->label = 'Name';
            $report->addDisplayAttribute($displayAttribute1);

            //for boolean attribute
            $reportModelTestItem->boolean = true;
            $displayAttribute2    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute2->attributeIndexOrDerivedType = 'hasMany2___boolean';
            $report->addDisplayAttribute($displayAttribute2);

            //for date attribute
            $reportModelTestItem->date = '2013-02-12';
            $displayAttribute3    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute3->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute3->attributeIndexOrDerivedType = 'hasMany2___date';
            $report->addDisplayAttribute($displayAttribute3);

            //for datetime attribute
            $reportModelTestItem->dateTime = '2013-02-12 10:15:00';
            $displayAttribute4    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute4->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute4->attributeIndexOrDerivedType = 'hasMany2___dateTime';
            $report->addDisplayAttribute($displayAttribute4);

            //for float attribute
            $reportModelTestItem->float = 10.5;
            $displayAttribute5    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute5->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute5->attributeIndexOrDerivedType = 'hasMany2___float';
            $report->addDisplayAttribute($displayAttribute5);

            //for integer attribute
            $reportModelTestItem->integer = 10;
            $displayAttribute6    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute6->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute6->attributeIndexOrDerivedType = 'hasMany2___integer';
            $report->addDisplayAttribute($displayAttribute6);

            //for phone attribute
            $reportModelTestItem->phone = '7842151012';
            $displayAttribute7    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute7->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute7->attributeIndexOrDerivedType = 'hasMany2___phone';
            $report->addDisplayAttribute($displayAttribute7);

            //for string attribute
            $reportModelTestItem->string = 'xString';
            $displayAttribute8    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute8->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute8->attributeIndexOrDerivedType = 'hasMany2___string';
            $report->addDisplayAttribute($displayAttribute8);

            //for textArea attribute
            $reportModelTestItem->textArea = 'xtextAreatest';
            $displayAttribute9    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute9->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute9->attributeIndexOrDerivedType = 'hasMany2___textArea';
            $report->addDisplayAttribute($displayAttribute9);

            //for url attribute
            $reportModelTestItem->url = 'http://www.test.com';
            $displayAttribute10    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute10->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute10->attributeIndexOrDerivedType = 'hasMany2___url';
            $report->addDisplayAttribute($displayAttribute10);

            //for dropdown attribute
            $reportModelTestItem->dropDown->value = $values[1];
            $displayAttribute11    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute11->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute11->attributeIndexOrDerivedType = 'hasMany2___dropDown';
            $report->addDisplayAttribute($displayAttribute11);

            //for currency attribute
            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $reportModelTestItem->currencyValue   = $currencyValue;
            $displayAttribute12    = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute12->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute12->attributeIndexOrDerivedType = 'hasMany2___currencyValue';
            $report->addDisplayAttribute($displayAttribute12);

            //for primaryAddress attribute
            $reportModelTestItem->primaryAddress->street1 = 'someString';
            $displayAttribute13   = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute13->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute13->attributeIndexOrDerivedType = 'hasMany2___primaryAddress___street1';
            $report->addDisplayAttribute($displayAttribute13);

            //for primaryEmail attribute
            $reportModelTestItem->primaryEmail->emailAddress = "test@someString.com";
            $displayAttribute14   = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute14->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute14->attributeIndexOrDerivedType = 'hasMany2___primaryEmail___emailAddress';
            $report->addDisplayAttribute($displayAttribute14);

            //for multiDropDown attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 2';
            $reportModelTestItem->multiDropDown->values->add($customFieldValue);
            $displayAttribute15   = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute15->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute15->attributeIndexOrDerivedType = 'hasMany2___multiDropDown';
            $report->addDisplayAttribute($displayAttribute15);

            //for tagCloud attribute
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $reportModelTestItem->tagCloud->values->add($customFieldValue);
            $displayAttribute16   = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute16->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute16->attributeIndexOrDerivedType = 'hasMany2___tagCloud';
            $report->addDisplayAttribute($displayAttribute16);

            //for radioDropDown attribute
            $reportModelTestItem->radioDropDown->value = $values[1];
            $displayAttribute17   = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute17->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute17->attributeIndexOrDerivedType = 'hasMany2___radioDropDown';
            $report->addDisplayAttribute($displayAttribute17);

            //for likeContactState
            $reportModelTestItem7         = new ReportModelTestItem7;
            $reportModelTestItem7->name   = 'someName';
            $reportModelTestItem->likeContactState = $reportModelTestItem7;
            $displayAttribute18            = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute18->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute18->attributeIndexOrDerivedType = 'hasMany2___likeContactState';
            $report->addDisplayAttribute($displayAttribute18);

            //for dynamic user attribute
            $reportModelTestItem->owner   = Yii::app()->user->userModel;
            $displayAttribute19           = new DisplayAttributeForReportForm('ReportsTest2Module', 'ReportModelTestItem2',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute19->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute19->attributeIndexOrDerivedType = 'hasMany2___owner__User';
            $report->addDisplayAttribute($displayAttribute19);

            $reportModelTestItem2            = new ReportModelTestItem2();
            $reportModelTestItem2->owner     = Yii::app()->user->userModel;
            $reportModelTestItem2->hasMany2->add($reportModelTestItem);
            $this->assertTrue($reportModelTestItem2->save());

            $dataProvider       = new RowsAndColumnsReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('Name',
                                        'Reports Tests >> Boolean',
                                        'Reports Tests >> Date',
                                        'Reports Tests >> Date Time',
                                        'Reports Tests >> Float',
                                        'Reports Tests >> Integer',
                                        'Reports Tests >> Phone',
                                        'Reports Tests >> String',
                                        'Reports Tests >> Text Area',
                                        'Reports Tests >> Url',
                                        'Reports Tests >> Drop Down',
                                        'Reports Tests >> Currency Value',
                                        'Reports Tests >> Currency Value Currency',
                                        'Reports Tests >> Primary Address >> Street 1',
                                        'Reports Tests >> Primary Email >> Email Address',
                                        'Reports Tests >> Multi Drop Down',
                                        'Reports Tests >> Tag Cloud',
                                        'Reports Tests >> Radio Drop Down',
                                        'Reports Tests >> A name for a state',
                                        'Reports Tests >> Owner');
            $compareRowData     = array(array('xFirst xLast', 1, '2013-02-12', '2013-02-12 10:15:00',
                                        10.5, 10, '7842151012', 'xString', 'xtextAreatest',
                                        'http://www.test.com', 'Test2', '100.00', 'USD', 'someString', 'test@someString.com',
                                        'Multi 1,Multi 2', 'Cloud 2,Cloud 3', 'Test2', 'someName', 'super')); // Not Coding Standard
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());

            //for MANY-MANY Relationship
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');

            //for name attribute
            $reportModelTestItem3 = new ReportModelTestItem3();
            $reportModelTestItem3->name = 'xFirst';
            $displayAttribute1    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('relatedModel1');
            $displayAttribute1->attributeIndexOrDerivedType = 'hasOne___hasMany3___name';
            $report->addDisplayAttribute($displayAttribute1);

            //for somethingOn3 attribute
            $reportModelTestItem3->somethingOn3 = 'somethingOn3';
            $displayAttribute2    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('relatedModel1');
            $displayAttribute2->attributeIndexOrDerivedType = 'hasOne___hasMany3___somethingOn3';
            $report->addDisplayAttribute($displayAttribute2);

            $reportModelTestItem3->owner     = Yii::app()->user->userModel;
            $reportModelTestItem3->hasMany2->add($reportModelTestItem2);
            $this->assertTrue($reportModelTestItem3->save());

            $dataProvider       = new RowsAndColumnsReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('ReportModelTestItem2 >> ReportModelTestItem3s >> Name',
                                        'ReportModelTestItem2 >> ReportModelTestItem3s >> Something On 3');
            $compareRowData     = array(array('xFirst', 'somethingOn3'));
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
            $reportModelTestItem->delete();
            $reportModelTestItem2->delete();
            $reportModelTestItem3->delete();
        }

        public function testViaSelectAndViaModelTogether()
        {
            $reportModelTestItem = new ReportModelTestItem();
            $reportModelTestItem->string = 'string';
            $reportModelTestItem->lastName = 'lastName';
            $reportModelTestItem->integer = 9000;
            $reportModelTestItem->boolean = true;
            $this->assertTrue($reportModelTestItem->save());

            $report              = new Report();
            $report->setType(Report::TYPE_SUMMATION);
            $report->setModuleClassName('ReportsTestModule');
            $report->setFiltersStructure('');

            //viaSelect attribute
            $displayAttribute1 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute1->attributeIndexOrDerivedType = 'integer__Minimum';
            $displayAttribute1->madeViaSelectInsteadOfViaModel = true;
            $this->assertTrue($displayAttribute1->columnAliasName == 'col0');
            $report->addDisplayAttribute($displayAttribute1);

            //viaModel attribute
            $reportModelTestItem->boolean = true;
            $displayAttribute2    = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                     Report::TYPE_SUMMATION);
            $displayAttribute2->setModelAliasUsingTableAliasName('model1');
            $displayAttribute2->attributeIndexOrDerivedType = 'boolean';
            $report->addDisplayAttribute($displayAttribute2);

            $dataProvider       = new SummationReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('Integer -(Min)', 'Boolean');
            $compareRowData     = array(array(9000, true));
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
            $reportModelTestItem->delete();
        }
    }
?>