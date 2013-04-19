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

    class DisplayAttributeForReportFormTest extends ZurmoBaseTest
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

            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $labels = array('fr' => array('Test1 fr', 'Test2 fr', 'Test3 fr', 'Sample fr', 'Demo fr'),
                            'de' => array('Test1 de', 'Test2 de', 'Test3 de', 'Sample de', 'Demo de'),
            );
            $customFieldData = CustomFieldData::getByName('ReportTestDropDown');
            $customFieldData->serializedData   = serialize($values);
            $customFieldData->serializedLabels = serialize($labels);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard
            ContactsModule::loadStartingData();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            ModelRelationsAndAttributesToSummableReportAdapter::forgetAll();
            ModelRelationsAndAttributesToRowsAndColumnsReportAdapter::forgetAll();
            ModelRelationsAndAttributesToMatrixReportAdapter::forgetAll();
        }

        public function testGetDisplayLabelForCalculations()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'float__Summation';
            $this->assertEquals('Float -(Sum)',    $displayAttribute->label);
            $this->assertEquals('Float -(Sum)',    $displayAttribute->getDisplayLabel());
        }

        public function testSetAndGetDisplayAttribute()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $this->assertNull($displayAttribute->label);
            $displayAttribute->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('String',    $displayAttribute->label);
            $displayAttribute->label                       = 'someLabel';
            $this->assertEquals('string',    $displayAttribute->attributeAndRelationData);
            $this->assertEquals('string',    $displayAttribute->attributeIndexOrDerivedType);
            $this->assertEquals('string',    $displayAttribute->getResolvedAttribute());
            $this->assertEquals('String',    $displayAttribute->getDisplayLabel());
            $this->assertEquals('someLabel', $displayAttribute->label);
            $validated = $displayAttribute->validate();
            $this->assertTrue($validated);

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                                  Report::TYPE_SUMMATION);
            $displayAttribute->label             = null;
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $displayAttribute->label             = '';
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $displayAttribute->label             = 'test';
            $displayAttribute->setAttributes(array('label' => ''));
            $validated                           = $displayAttribute->validate();
            $this->assertFalse($validated);
            $errors                              = $displayAttribute->getErrors();
            $compareErrors                       = array('label'     => array('Label cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
        }

        public function testGetDisplayElementTypeForRowsAndColumnsReport()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType = 'calculated';
            $this->assertEquals('CalculatedNumber',          $displayAttribute->getDisplayElementType());
        }

        public function testGetDisplayElementTypeForSummationReport()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'boolean';
            $this->assertEquals('CheckBox',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'date';
            $this->assertEquals('Date',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime';
            $this->assertEquals('DateTime',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'float';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'integer';
            $this->assertEquals('Integer',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $this->assertEquals('Phone',                     $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'textArea';
            $this->assertEquals('TextArea',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'url';
            $this->assertEquals('Url',                       $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'currencyValue';
            $this->assertEquals('CurrencyValue',             $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dropDown';
            $this->assertEquals('DropDown',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'radioDropDown';
            $this->assertEquals('RadioDropDown',             $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'multiDropDown';
            $this->assertEquals('MultiSelectDropDown',       $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'tagCloud';
            $this->assertEquals('TagCloud',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $this->assertEquals('Email',                     $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'FullName';
            $this->assertEquals('FullName',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'likeContactState';
            $this->assertEquals('ContactState',              $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'calculated';
            $this->assertEquals('CalculatedNumber',          $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'createdByUser__User';
            $this->assertEquals('User',                      $displayAttribute->getDisplayElementType());
        }

        public function testGetDisplayElementTypeDisplayCalculationsAndGroupModifiers()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'integer__Minimum';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'float__Minimum';
            $this->assertEquals('Decimal',                   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'date__Minimum';
            $this->assertEquals('Date',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Minimum';
            $this->assertEquals('DateTime',                  $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'currencyValue__Minimum';
            $this->assertEquals('CalculatedCurrencyValue',   $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Day';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Week';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Month';
            $this->assertEquals('GroupByModifierMonth',      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Quarter';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
            $displayAttribute->attributeIndexOrDerivedType = 'dateTime__Year';
            $this->assertEquals('Text',                      $displayAttribute->getDisplayElementType());
        }

        public function testResolveValueAsLabelForHeaderCellForADropDown()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                    Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'dropDown';
            $this->assertEquals('Sample', $displayAttribute->resolveValueAsLabelForHeaderCell('Sample'));
            //Test translating to a different language
            $oldLanguage = Yii::app()->language;
            Yii::app()->language = 'de';
            $this->assertEquals('Sample de', $displayAttribute->resolveValueAsLabelForHeaderCell('Sample'));
            Yii::app()->language = $oldLanguage;
        }

        public function testResolveValueAsLabelForHeaderCellForACheckBox()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'boolean';
            $this->assertEquals('Yes', $displayAttribute->resolveValueAsLabelForHeaderCell(true));
            //false will evaluate as '', so it wont show No, not sure if this is right
            $this->assertEquals('No',  $displayAttribute->resolveValueAsLabelForHeaderCell('0'));
            $this->assertEquals('',    $displayAttribute->resolveValueAsLabelForHeaderCell(''));
        }

        public function testResolveValueAsLabelForHeaderCellForADynamicUser()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'owner__User';
            $this->assertEquals('Clark Kent', $displayAttribute->resolveValueAsLabelForHeaderCell(Yii::app()->user->userModel->id));
        }

        public function testResolveValueAsLabelForHeaderCellForAString()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('', $displayAttribute->resolveValueAsLabelForHeaderCell(null));
        }

        public function testResolveValueAsLabelForHeaderCellForAnAttributeLikeContactState()
        {
            $contactStates = ContactState::getAll();
            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'state';
            $this->assertEquals('Recycled', $contactStates[2]->name);
            $this->assertEquals('Recycled', $displayAttribute->resolveValueAsLabelForHeaderCell($contactStates[2]->id));
        }

        public function testGetDisplayLabel()
        {
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_SUMMATION);
            $displayAttribute->attributeIndexOrDerivedType = 'string';
            $this->assertEquals('String', $displayAttribute->getDisplayLabel());
            $displayAttribute->attributeIndexOrDerivedType = 'hasOne___name';
            $this->assertEquals('ReportModelTestItem2 >> Name', $displayAttribute->getDisplayLabel());
            $displayAttribute->attributeIndexOrDerivedType = 'hasMany___name';
            $this->assertEquals('ReportModelTestItem3s >> Name', $displayAttribute->getDisplayLabel());
            $displayAttribute->attributeIndexOrDerivedType = 'primaryAddress___street1';
            $this->assertEquals('Primary Address >> Street 1', $displayAttribute->getDisplayLabel());
        }
    }
?>