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

    class FilterForReportFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAndGetFilter()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $this->assertEquals('string', $filter->attributeAndRelationData);
            $this->assertEquals('string', $filter->attributeIndexOrDerivedType);
            $this->assertEquals('string', $filter->getResolvedAttribute());
            $this->assertEquals('String', $filter->getDisplayLabel());

            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasOne___name';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasOne', 'name'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasOne___name',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem',   $filter->getPenultimateModelClassName());
            $this->assertEquals('hasOne',                $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem2',  $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem2 >> Name', $filter->getDisplayLabel());

            //2 levels deeps
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasOne___hasMany3___name';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasOne', 'hasMany3', 'name'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasOne___hasMany3___name',          $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',              $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany3',                          $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem3',              $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem2 >> ReportModelTestItem3s >> Name', $filter->getDisplayLabel());
        }

        /**
         * @depends testSetAndGetFilter
         */
        public function testInferredRelationSetAndGet()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem5',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'ReportModelTestItem__reportItems__Inferred___phone';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '1234567890';
            $this->assertEquals(array('ReportModelTestItem__reportItems__Inferred', 'phone'),
                                                                    $filter->getAttributeAndRelationData());
            $this->assertEquals('ReportModelTestItem__reportItems__Inferred___phone',
                                                                    $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem5',             $filter->getPenultimateModelClassName());
            $this->assertEquals('ReportModelTestItem__reportItems__Inferred',
                                                                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',              $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> Phone',           $filter->getDisplayLabel());
        }

        /**
         * @depends testInferredRelationSetAndGet
         */
        public function testDerivedRelationSetAndGet()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'model5ViaItem___name';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = '1234567890';
            $this->assertEquals(array('model5ViaItem', 'name'),     $filter->getAttributeAndRelationData());
            $this->assertEquals('model5ViaItem___name',             $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem',              $filter->getPenultimateModelClassName());
            $this->assertEquals('model5ViaItem',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem5',             $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('ReportModelTestItem5s >> Name',    $filter->getDisplayLabel());
        }

        /**
         * @depends testDerivedRelationSetAndGet
         */
        public function testRelationReportedAsAttributeSetAndGet()
        {
            //test dropDown
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___dropDown';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'dropDown'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___dropDown',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',        $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',         $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> Drop Down', $filter->getDisplayLabel());

            //test currencyValue
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___currencyValue';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'currencyValue'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___currencyValue',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',        $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                    $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',         $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> Currency Value', $filter->getDisplayLabel());

            //test reportedAsAttribute
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___reportedAsAttribute';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'reportedAsAttribute'), $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___reportedAsAttribute',         $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                   $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                               $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                    $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> Reported As Attribute', $filter->getDisplayLabel());

            //test the likeContactState
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___likeContactState';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'likeContactState'),        $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___likeContactState',                $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                       $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                                   $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                        $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> A name for a state', $filter->getDisplayLabel());
        }

        /**
         * @depends testRelationReportedAsAttributeSetAndGet
         */
        public function testDynamicallyDerivedAttributeSetAndGet()
        {
            //test the likeContactState
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                                           Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'hasMany2___owner__User';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Jason';
            $this->assertEquals(array('hasMany2', 'owner__User'),      $filter->getAttributeAndRelationData());
            $this->assertEquals('hasMany2___owner__User',              $filter->attributeIndexOrDerivedType);
            $this->assertEquals('ReportModelTestItem2',                $filter->getPenultimateModelClassName());
            $this->assertEquals('hasMany2',                            $filter->getPenultimateRelation());
            $this->assertEquals('ReportModelTestItem',                 $filter->getResolvedAttributeModelClassName());
            $this->assertEquals('Reports Tests >> Owner',       $filter->getDisplayLabel());
        }

        /**
         * @depends testDynamicallyDerivedAttributeSetAndGet
         */
        public function testValidateTextAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors    = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = 'Jason';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test when not null is set as attribute, now the value is not required
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $filter->value                       = null;
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateTextAttribute
         */
        public function testValidateIntegerAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'integer';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be an integer
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be integer.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 456. should pass
            $filter->value                       = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check for between, but with strings
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = 'test';
            $filter->secondValue                 = 'test2';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'        => array('Value must be integer.'),
                                                         'secondValue'  => array('Second Value must be integer.'));
            $this->assertEquals($compareErrors, $errors);

            //Now check with integers. but missing the second value
            $filter->value                       = 345;
            $filter->secondValue                 = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('secondValue'     => array('Second Value must be integer.'));

            //now check for between with both filled in with integers
            $filter->value                       = 345;
            $filter->secondValue                 = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check when integers are strings, but should resolve as integerse
            $filter->value                       = '345';
            $filter->secondValue                 = '456';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateIntegerAttribute
         */
        public function testValidateDateAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'date';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'),
                                                         'valueType' => array('Type cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be a date
            $filter->valueType                   = 'On';
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be date.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 2011-05-05. should pass
            $filter->value                       = '2011-05-05';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check for between, but with strings
            $filter->valueType                    = 'Between';
            $filter->value                       = 'test';
            $filter->secondValue                 = 'test2';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'        => array('Value must be date.'),
                                                         'secondValue'  => array('Second Value must be date.'));
            $this->assertEquals($compareErrors, $errors);

            //Now check with dates. but missing the second value
            $filter->value                       = '2011-05-05';
            $filter->secondValue                 = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('secondValue'     => array('Second Value must be date.'));

            //now check for between with both filled in with integers
            $filter->value                       = '2011-05-05';
            $filter->secondValue                 = '2011-06-05';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateDateAttribute
         */
        public function testValidateDateTimeAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dateTime';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'),
                                                         'valueType' => array('Type cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //check Today
            $filter->valueType                   = 'Today';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //check Yesterday
            $filter->valueType                   = 'Yesterday';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //check Tomorrow
            $filter->valueType                   = 'Tomorrow';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //value is expected to be a date
            $filter->valueType                   = 'On';
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be date.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 2011-05-05. should pass
            $filter->value                       = '2011-05-05';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check for between, but with strings
            $filter->valueType                   = 'Between';
            $filter->value                       = 'test';
            $filter->secondValue                 = 'test2';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'        => array('Value must be date.'),
                                                         'secondValue'  => array('Second Value must be date.'));
            $this->assertEquals($compareErrors, $errors);

            //Now check with integers. but missing the second value
            $filter->value                       = '2011-05-05';
            $filter->secondValue                 = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('secondValue'     => array('Second Value must be date.'));

            //now check for between with both filled in with integers
            $filter->value                       = '2011-05-05';
            $filter->secondValue                 = '2011-06-05';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateDateTimeAttribute
         */
        public function testValidateBooleanAsDropDownAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'boolean';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be either 0 or 1
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be either 1 or 0.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = '1';
            $validated = $filter->validate();
            $this->assertTrue($validated);
            $filter->value                       = '0';
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateBooleanAsDropDownAttribute
         */
        public function testValidateDropDownAttribute()
        {
            //Test equals (non-array) and it is null
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'dropDown';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = OperatorRules::TYPE_EQUALS;

            //Test equals (non-array) and it is empty
            $filter->value                       = '';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test equals (non-array) and it is properly populated
            $filter->value                       = 'someValue';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test oneOf (array) and it is null
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is empty array
            $filter->value                       = array();
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is properly populated
            $filter->value                       = array('aFirstValue', 'aSecondValue');
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * Same as testing reportedAsAttribute
         * @depends testValidateDropDownAttribute
         */
        public function testValidateLikeContactStateAttribute()
        {
            //Test equals (non-array) and it is null
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'likeContactState';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = OperatorRules::TYPE_EQUALS;

            //Test equals (non-array) and it is empty
            $filter->value                       = '';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test equals (non-array) and it is properly populated
            $filter->value                       = 'someValue';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test oneOf (array) and it is null
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is empty array
            $filter->value                       = array();
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is properly populated
            $filter->value                       = array('aFirstValue', 'aSecondValue');
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * Will just assume that 'currencyId' passed is always ok and we are not trying to validate.
         * @depends testValidateDropDownAttribute
         */
        public function testValidateCurrencyValueAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'currencyValue';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be an currency
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be float.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 456. should pass
            $filter->value                       = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check for between, but with strings
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = 'test';
            $filter->secondValue                 = 'test2';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'        => array('Value must be float.'),
                                                         'secondValue'  => array('Second Value must be float.'));
            $this->assertEquals($compareErrors, $errors);

            //Now check with currency. but missing the second value
            $filter->value                       = 345;
            $filter->secondValue                 = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('secondValue'     => array('Second Value must be float.'));

            //now check for between with both filled in with currency
            $filter->value                       = 345.54;
            $filter->secondValue                 = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check when currency as currency. Should pass fine.
            $filter->value                       = '3450.87';
            $filter->secondValue                 = '456';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check when passing in a currencyIdForValue
            $filter->currencyIdForValue          = 4;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateCurrencyValueAttribute
         */
        public function testValidateDynamicallyDerivedOwnerAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                            Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'owner__User';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'),
                                                         'operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->value                       = '5';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->stringifiedModelForValue    = 'Jason';
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateDynamicallyDerivedOwnerAttribute
         */
        public function testValidateMultiSelectDropDownAttribute()
        {
            //Test equals (non-array) and it is null
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'multiDropDown';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = OperatorRules::TYPE_EQUALS;

            //Test equals (non-array) and it is empty
            $filter->value                       = '';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test equals (non-array) and it is properly populated
            $filter->value                       = 'someValue';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test oneOf (array) and it is null
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is empty array
            $filter->value                       = array();
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is properly populated
            $filter->value                       = array('aFirstValue', 'aSecondValue');
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateMultiSelectDropDownAttribute
         */
        public function testValidateTagCloudAttribute()
        {
            //Test equals (non-array) and it is null
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'tagCloud';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = OperatorRules::TYPE_EQUALS;

            //Test equals (non-array) and it is empty
            $filter->value                       = '';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test equals (non-array) and it is properly populated
            $filter->value                       = 'someValue';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test oneOf (array) and it is null
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is empty array
            $filter->value                       = array();
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is properly populated
            $filter->value                       = array('aFirstValue', 'aSecondValue');
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateTagCloudAttribute
         */
        public function testValidateRadioDropDownAttribute()
        {
            //Test equals (non-array) and it is null
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'radioDropDown';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $filter->operator                    = OperatorRules::TYPE_EQUALS;

            //Test equals (non-array) and it is empty
            $filter->value                       = '';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test equals (non-array) and it is properly populated
            $filter->value                       = 'someValue';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test oneOf (array) and it is null
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $filter->value                       = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is empty array
            $filter->value                       = array();
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //Test oneOf (array) and it is properly populated
            $filter->value                       = array('aFirstValue', 'aSecondValue');
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateRadioDropDownAttribute
         */
        public function testValidateTextAreaAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'textArea';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors    = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = 'Jason';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test when not null is set as attribute, now the value is not required
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $filter->value                       = null;
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateTextAreaAttribute
         */
        public function testValidateUrlAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'url';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors    = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = 'http://www.zurmo.com';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test when not null is set as attribute, now the value is not required
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $filter->value                       = null;
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidateUrlAttribute
         */
        public function testValidatePhoneAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'phone';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors    = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = 'Jason';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test when not null is set as attribute, now the value is not required
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $filter->value                       = null;
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidatePhoneAttribute
         */
        public function testValidateFloatAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'float';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            //value is expected to be an float
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value must be float.'));
            $this->assertEquals($compareErrors, $errors);

            //also check value as 456. should pass
            $filter->value                       = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check for between, but with strings
            $filter->operator                    = OperatorRules::TYPE_BETWEEN;
            $filter->value                       = 'test';
            $filter->secondValue                 = 'test2';
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'        => array('Value must be float.'),
                'secondValue'  => array('Second Value must be float.'));
            $this->assertEquals($compareErrors, $errors);

            //Now check with floats. but missing the second value
            $filter->value                       = 345;
            $filter->secondValue                 = null;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('secondValue'     => array('Second Value must be float.'));

            //now check for between with both filled in with floats
            $filter->value                       = 345;
            $filter->secondValue                 = 456;
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //now check when floats are strings, but should resolve as float
            $filter->value                       = '345';
            $filter->secondValue                 = '456';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidatePhoneAttribute
         */
        public function testValidateEmailAttribute()
        {
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $validated = $filter->validate();
            $this->assertFalse($validated);
            $errors    = $filter->getErrors();
            $compareErrors                       = array('operator'  => array('Operator cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $validated                           = $filter->validate();
            $this->assertFalse($validated);
            $errors                              = $filter->getErrors();
            $compareErrors                       = array('value'     => array('Value cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $filter->value                       = 'someone@zurmo.com';
            $validated                           = $filter->validate();
            $this->assertTrue($validated);

            //Test when not null is set as attribute, now the value is not required
            $filter->operator                    = OperatorRules::TYPE_IS_NOT_NULL;
            $filter->value                       = null;
            $validated = $filter->validate();
            $this->assertTrue($validated);
        }
    }
?>