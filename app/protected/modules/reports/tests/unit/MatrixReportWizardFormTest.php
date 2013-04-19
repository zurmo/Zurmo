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
    class MatrixReportWizardFormTest extends ZurmoBaseTest
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
            $matrixReportWizardForm                  = new MatrixReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $filter->value                           = 'Zurmo';
            $matrixReportWizardForm->filters         = array($filter);
            $matrixReportWizardForm->validateFilters();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateFiltersForErrors()
        {
            $matrixReportWizardForm                  = new MatrixReportWizardForm();
            $filter                                  = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType     = 'string';
            $filter->operator                        = OperatorRules::TYPE_EQUALS;
            $matrixReportWizardForm->filters = array($filter);
            $validated                               = $filter->validate();
            $this->assertFalse($validated);
            $errors                                  = $filter->getErrors();
            $compareErrors                           = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
        }

        public function testValidateFiltersStructure()
        {
            $matrixReportWizardForm                       = new MatrixReportWizardForm();
            $filter                                       = new FilterForReportForm('ReportsTestModule',
                                                                'ReportModelTestItem', Report::TYPE_MATRIX);
            $filter->attributeIndexOrDerivedType          = 'createdDateTime';
            $filter->operator                             = OperatorRules::TYPE_BETWEEN;
            $filter->value                                = '2013-02-19 00:00';
            $filter->secondValue                          = '2013-02-20 00:00';
            $matrixReportWizardForm->filters              = array($filter);
            $matrixReportWizardForm->filtersStructure     = '1';
            $matrixReportWizardForm->validateFiltersStructure();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateFiltersStructureForError()
        {
            $matrixReportWizardForm                       = new MatrixReportWizardForm();
            $filter                                       = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType          = 'createdDateTime';
            $filter->operator                             = OperatorRules::TYPE_BETWEEN;
            $filter->value                                = '2013-02-19 00:00';
            $filter->secondValue                          = '2013-02-20 00:00';
            $matrixReportWizardForm->filters              = array($filter);
            $matrixReportWizardForm->filtersStructure     = '2';
            $matrixReportWizardForm->validateFiltersStructure();
            $errors                                       = $matrixReportWizardForm->getErrors();
            $compareErrors                                = array('filtersStructure'     => array('The structure is invalid. Please use only integers less than 2.'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }

        public function testValidateGroupBys()
        {
            $matrixReportWizardForm                = new MatrixReportWizardForm();
            $groupByX                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByX->attributeIndexOrDerivedType = 'string';
            $groupByX->axis                        = 'x';
            $this->assertEquals('x', $groupByX->axis);
            $groupByY                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByY->attributeIndexOrDerivedType = 'integer';
            $groupByY->axis                        = 'y';
            $this->assertEquals('y', $groupByY->axis);
            $matrixReportWizardForm->groupBys      = array($groupByX, $groupByY);
            $matrixReportWizardForm->validateGroupBys();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateGroupBysForErrors()
        {
            $matrixReportWizardForm                = new MatrixReportWizardForm();
            $groupByX                              = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $groupByX->attributeIndexOrDerivedType = 'string';
            $groupByX->axis                        = 'x';
            $this->assertEquals('x', $groupByX->axis);
            $matrixReportWizardForm->groupBys      = array($groupByX);
            $content = $matrixReportWizardForm->validateGroupBys();
            $errors  = $matrixReportWizardForm->getErrors();
            $compareErrors                         = array('groupBys'     => array('At least one x-axis and one y-axis grouping must be selected'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }

        public function testValidateDisplayAttributes()
        {
            $matrixReportWizardForm               = new MatrixReportWizardForm();
            $reportModelTestItem                  = new ReportModelTestItem();
            $reportModelTestItem->date            = '2013-02-12';
            $displayAttribute                     = new DisplayAttributeForReportForm('ReportsTestModule',
                                                        'ReportModelTestItem', Report::TYPE_MATRIX);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $matrixReportWizardForm->displayAttributes     = array($displayAttribute);
            $matrixReportWizardForm->validateDisplayAttributes();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateDisplayAttributesForError()
        {
            $matrixReportWizardForm                    = new MatrixReportWizardForm();
            $matrixReportWizardForm->displayAttributes = array();
            $matrixReportWizardForm->validateDisplayAttributes();
            $errors  = $matrixReportWizardForm->getErrors();
            $compareErrors                             = array('displayAttributes'     => array('At least one display column must be selected'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }

        public function testValidateOrderBys()
        {
            $matrixReportWizardForm                  = new MatrixReportWizardForm();
            $orderBy                                 = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_MATRIX);
            $orderBy->attributeIndexOrDerivedType    = 'modifiedDateTime';
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->order                          = 'desc';
            $matrixReportWizardForm->orderBys        = array($orderBy);
            $matrixReportWizardForm->validateOrderBys();
            $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateOrderBysForErrors()
        {
            $matrixReportWizardForm                   = new MatrixReportWizardForm();
            $orderBy                                  = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals('asc', $orderBy->order);
            $orderBy->attributeIndexOrDerivedType     = 'modifiedDateTime';
            $orderBy->order                           = null;
            $validated                                = $orderBy->validate();
            $this->assertFalse($validated);
            $errors = $orderBy->getErrors();
            $matrixReportWizardForm->orderBys         = array($orderBy);
            $matrixReportWizardForm->validateOrderBys();
            $errors  = $orderBy->getErrors();
            $compareErrors                            = array('order'     => array('Order cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $this->assertTrue($matrixReportWizardForm->hasErrors());
        }

        public function testValidateSpotConversionCurrencyCode()
        {
           $matrixReportWizardForm                             = new MatrixReportWizardForm();
           $matrixReportWizardForm->currencyConversionType     = 2;
           $matrixReportWizardForm->spotConversionCurrencyCode = 'CAD';
           $matrixReportWizardForm->validateSpotConversionCurrencyCode();
           $this->assertFalse($matrixReportWizardForm->hasErrors());
        }

        public function testValidateSpotConversionCurrencyCodeForErrors()
        {
           $matrixReportWizardForm                             = new MatrixReportWizardForm();
           $matrixReportWizardForm->currencyConversionType     = 3;
           $matrixReportWizardForm->spotConversionCurrencyCode = null;
           $matrixReportWizardForm->validateSpotConversionCurrencyCode();
           $errors  = $matrixReportWizardForm->getErrors();
           $compareErrors                                      = array('spotConversionCurrencyCode'     => array('Spot Currency cannot be blank.'));
           $this->assertEquals($compareErrors, $errors);
           $this->assertTrue($matrixReportWizardForm->hasErrors());
        }
    }
?>