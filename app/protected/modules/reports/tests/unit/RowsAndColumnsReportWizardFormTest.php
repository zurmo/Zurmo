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

    /**
    * Test ReportWizardForm validation functions.
    */
    class RowsAndColumnsReportWizardFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testValidateFilters()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();

            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $filter->value                           = 'Zurmo';
            $rowsAndColumnsReportWizardForm->filters = array($filter);
            $rowsAndColumnsReportWizardForm->validateFilters();
            $this->assertFalse($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateFiltersForErrors()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();

            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $rowsAndColumnsReportWizardForm->filters = array($filter);
            $validated                               = $filter->validate();
            $this->assertFalse($validated);
            $errors                                  = $filter->getErrors();
            $compareErrors                           = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
        }

        public function testValidateFiltersStructure()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'createdDateTime';
            $filter->operator                        = OperatorRules::TYPE_BETWEEN;
            $filter->value                           = '2013-02-19 00:00';
            $filter->secondValue                     = '2013-02-20 00:00';
            $rowsAndColumnsReportWizardForm->filters = array($filter);
            $rowsAndColumnsReportWizardForm->filtersStructure  = '1';
            $rowsAndColumnsReportWizardForm->validateFiltersStructure();
            $this->assertFalse($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateFiltersStructureForError()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'createdDateTime';
            $filter->operator                        = OperatorRules::TYPE_BETWEEN;
            $filter->value                           = '2013-02-19 00:00';
            $filter->secondValue                     = '2013-02-20 00:00';
            $rowsAndColumnsReportWizardForm->filters = array($filter);
            $rowsAndColumnsReportWizardForm->filtersStructure  = '2';
            $rowsAndColumnsReportWizardForm->validateFiltersStructure();
            $errors                                  = $rowsAndColumnsReportWizardForm->getErrors();
            $compareErrors                           = array('filtersStructure'     => array('The structure is invalid. Please use only integers less than 2.'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateDisplayAttributes()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $reportModelTestItem                     = new ReportModelTestItem();
            $reportModelTestItem->date               = '2013-02-12';
            $displayAttribute                        = new DisplayAttributeForReportForm('ReportsTestModule',
                                                            'ReportModelTestItem', Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');
            $displayAttribute->attributeIndexOrDerivedType     = 'date';
            $rowsAndColumnsReportWizardForm->displayAttributes = array($displayAttribute);
            $rowsAndColumnsReportWizardForm->validateDisplayAttributes();
            $this->assertFalse($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateDisplayAttributesForError()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $rowsAndColumnsReportWizardForm->displayAttributes = array();
            $content = $rowsAndColumnsReportWizardForm->validateDisplayAttributes();
            $errors  = $rowsAndColumnsReportWizardForm->getErrors();
            $compareErrors                           = array('displayAttributes'     => array('At least one display column must be selected'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateOrderBys()
        {
            $rowsAndColumnsReportWizardForm          = new RowsAndColumnsReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                           = 'desc';
            $rowsAndColumnsReportWizardForm->orderBys = array($orderBy);
            $rowsAndColumnsReportWizardForm->validateOrderBys();
            $this->assertFalse($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateOrderBysForErrors()
        {
            $rowsAndColumnsReportWizardForm           = new RowsAndColumnsReportWizardForm();
            $orderBy                                  = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->attributeIndexOrDerivedType     = 'modifiedDateTime';
            $orderBy->order                           = 'desc1';
            $rowsAndColumnsReportWizardForm->orderBys = array($orderBy);
            $rowsAndColumnsReportWizardForm->validateOrderBys();
            $errors  = $orderBy->getErrors();
            $compareErrors                            = array('order'     => array('Order must be asc or desc.'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateSpotConversionCurrencyCode()
        {
           $rowsAndColumnsReportWizardForm                         = new RowsAndColumnsReportWizardForm();
           $rowsAndColumnsReportWizardForm->currencyConversionType = 2;
           $rowsAndColumnsReportWizardForm->spotConversionCurrencyCode = 'CAD';
           $rowsAndColumnsReportWizardForm->validateSpotConversionCurrencyCode();
           $this->assertFalse($rowsAndColumnsReportWizardForm->hasErrors());
        }

        public function testValidateSpotConversionCurrencyCodeForErrors()
        {
           $rowsAndColumnsReportWizardForm                         = new RowsAndColumnsReportWizardForm();
           $rowsAndColumnsReportWizardForm->currencyConversionType = 3;
           $rowsAndColumnsReportWizardForm->spotConversionCurrencyCode = null;
           $rowsAndColumnsReportWizardForm->validateSpotConversionCurrencyCode();
           $errors  = $rowsAndColumnsReportWizardForm->getErrors();
           $compareErrors                            = array('spotConversionCurrencyCode'     => array('Spot Currency cannot be blank.'));
           $this->assertEquals($compareErrors, $errors);
           $this->assertTrue($rowsAndColumnsReportWizardForm->hasErrors());
        }
    }
?>