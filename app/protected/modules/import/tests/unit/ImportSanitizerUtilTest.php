<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ImportSanitizerUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            $values = array(
                'Test1',
                'Test2',
                'Test3',
                'Sample',
                'Demo',
            );
            $customFieldData = CustomFieldData::getByName('ImportTestDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert('$saved');
        }

        public function testSanitizeValueBySanitizerTypesForBooleanTypeThatIsNotRequired()
        {
            //Test a non-required boolean with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required boolean with no value, but a valid default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '1')));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(true, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required boolean with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '0')));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', 'yes',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(true, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required boolean with a valid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', 'yes',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(true, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required boolean with a value that is not a valid mapped value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', 'blah',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null,    $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Boolean Invalid check box format.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required boolean with a value that is invalidly mapped and a specified default value. The specified
            //default value should be ignored in this scenario.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '1')));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', 'blah',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Boolean Invalid check box format.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required boolean with a valid mapped value of 'no' where it evaluates to false, and a default
            //value of '1'.  The default value should be ignored and the resulting sanitized value should be false.
            //Test a non-required boolean with a value that is invalidly mapped and a specified default value. The specified
            //default value should be ignored in this scenario.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '1')));
            $sanitizerUtilTypes        = CheckBoxAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'boolean', 'no',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(false, $sanitizedValue);
            $this->assertTrue($sanitizedValue !== null);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForDateTypeThatIsNotRequired()
        {
            //Test a non-required date with no value or a default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy')));
            $sanitizerUtilTypes        = DateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'date', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required date with no value but a default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy')));
            $sanitizerUtilTypes        = DateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'date', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('2010-05-04', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required date with a value and a default value.  The default value will be ignored.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy')));
            $sanitizerUtilTypes        = DateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'date', '02-20-2005',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(1108879200, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required date with an invalid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy')));
            $sanitizerUtilTypes        = DateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'date', '02-2005-06',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $this->assertEquals(0, count($messages));

            //Test a non-required date with an invalid value and a default value which will not be used since the
            //first sanitization of the date format will fail.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy')));
            $sanitizerUtilTypes        = DateAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'date', '02-2005-06',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForDateTimeTypeThatIsNotRequired()
        {
            //Test a non-required dateTime with no value or a default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy hh:mm')));
            $sanitizerUtilTypes        = DateTimeAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dateTime', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required dateTime with no value but a default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04 05:00'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy hh:mm')));
            $sanitizerUtilTypes        = DateTimeAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dateTime', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('2010-05-04 05:00', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required dateTime with a value and a default value.  The default value will be ignored.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04 00:00'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy hh:mm:ss')));
            $sanitizerUtilTypes        = DateTimeAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dateTime', '02-20-2005 04:22:00',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(1108894920, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required dateTime with an invalid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy hh:mm')));
            $sanitizerUtilTypes        = DateTimeAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dateTime', '02-2005-06',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $this->assertEquals(0, count($messages));

            //Test a non-required dateTime with an invalid value and a default value which will not be used since the
            //first sanitization of the datetime format will fail.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2010-05-04 00:00'),
                                               'ValueFormatMappingRuleForm'                =>
                                               array('format' => 'MM-dd-yyyy hh:mm')));
            $sanitizerUtilTypes        = DateTimeAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dateTime', '02-2005-06',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForDropDownTypeThatIsNotRequired()
        {
            $importInstructionsData = array('DropDown' => array(DropDownSanitizerUtil::ADD_MISSING_VALUE => array()));

            //Test a non-required dropDown with no value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)),
                                               'importInstructionsData' => $importInstructionsData);
            $sanitizerUtilTypes        = DropDownAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dropDown', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required dropDown with no value and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'Test1')),
                                               'importInstructionsData' => $importInstructionsData);
            $sanitizerUtilTypes        = DropDownAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dropDown', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('Test1', $sanitizedValue->value);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required dropDown with a valid value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'Test2')),
                                               'importInstructionsData' => $importInstructionsData);
            $sanitizerUtilTypes        = DropDownAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dropDown', 'Demo',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('Demo', $sanitizedValue->value);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with a missing value and a default value.  The default value should not
            //be picked up, it should be ignored.  On the first sanitization failure, sanitization will stop, this is
            //why the default value is not set.
            //Since there are no missing value instructions, the sanitization will result in an error message.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'Test3')),
                                               'importInstructionsData' => $importInstructionsData);
            $sanitizerUtilTypes        = DropDownAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dropDown', 'NotThere',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Drop Down Pick list value specified is missing from existing pick ' .
                              'list and no valid instructions were provided on how to resolve this.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Now use a value that is missing, but there are instructions to add it, and confirm it is added.
            $importInstructionsData = array('DropDown' =>
                                      array(DropDownSanitizerUtil::ADD_MISSING_VALUE => array('NewValue')));
            $customFieldData = CustomFieldData::getByName('ImportTestDropDown');
            $this->assertEquals(5, count(unserialize($customFieldData->serializedData)));
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'Test1')),
                                               'importInstructionsData' => $importInstructionsData);
            $sanitizerUtilTypes        = DropDownAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'dropDown', 'NewValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('NewValue', $sanitizedValue->value);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
            $customFieldData = CustomFieldData::getByName('ImportTestDropDown');
            $values = unserialize($customFieldData->serializedData);
            $this->assertEquals(6, count($values));
            $this->assertEquals('NewValue', $values[5]);
        }

        public function testSanitizeValueBySanitizerTypesForEmailTypeThatIsNotRequired()
        {
            //Test a non-required email with no value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = EmailAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Email', 'emailAddress', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with no value and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'a@a.com')));
            $sanitizerUtilTypes        = EmailAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Email', 'emailAddress', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('a@a.com', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with a valid value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'b@b.com')));
            $sanitizerUtilTypes        = EmailAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Email', 'emailAddress', 'c@c.com',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('c@c.com', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with an invalid value and a default value.  The default value should not
            //be picked up, it should be ignored.  On the first sanitization failure, sanitization will stop, this is
            //why the default value is not set.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'c@c.com')));
            $sanitizerUtilTypes        = EmailAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Email', 'emailAddress', 'abcxco@',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Email - Email Address Invalid email format.';
            $this->assertEquals($compareMessage, $messages[0]);
        }

        public function testSanitizeValueBySanitizerTypesForFullNameTypeThatIsRequired()
        {
            //Test a non-required FullName with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - A full name value is required but missing.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required FullName with no value, but a valid default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('something valid', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required FullName with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, 'aValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('aValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required FullName with a valid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, 'first last',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('first last', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required FullName with a value that is too long and no specified default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Last name specified is too large.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required FullName with a value that is too long and a specified default value. The specified
            //default value should be ignored in this scenario.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'willNotMatter')));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Last name specified is too large.';
            $this->assertEquals($compareMessage, $messages[0]);

            //A first name that is too large, but the last name is ok.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'willNotMatter')));
            $sanitizerUtilTypes        = FullNameAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85) . ' okLastName';
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - First name specified is too large.';
            $this->assertEquals($compareMessage, $messages[0]);
        }

        public function testSanitizeValueBySanitizerTypesForModelDerivedIdTypeThatNotIsRequired()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $importModelTestItem3Model1 = ImportTestHelper::createImportModelTestItem3('aaa');
            $importModelTestItem3Model2 = ImportTestHelper::createImportModelTestItem3('bbb');
            $importModelTestItem3Model3 = ImportTestHelper::createImportModelTestItem3('ccc');

            //Update the external system id.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar(ImportModelTestItem3::getTableName('ImportModelTestItem3'), $columnName);
            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            R::exec("update " . ImportModelTestItem3::getTableName('ImportModelTestItem3')
            . " set $externalSystemIdColumnName = 'Q' where id = {$importModelTestItem3Model3->id}");

            //Test a non-required related model with an invalid value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem3DerivedAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, 'qweqwe',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - ImportModelTestItem3 id specified did not match any existing records.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required related model with no value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem3DerivedAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a valid zurmo model id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem3DerivedAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null,
                                         $importModelTestItem3Model2->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem3Model2, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a valid external system id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem3DerivedAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', null, 'Q',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem3Model3, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForNumberTypesThatAreaNotRequired()
        {
            //Test a non-required decimal with no value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = DecimalAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required decimal with no value and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '45.65')));
            $sanitizerUtilTypes        = DecimalAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(45.65, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required decimal with a valid value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '45.65')));
            $sanitizerUtilTypes        = DecimalAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', '23.67',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('23.67', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
            //Now try with a correctly casted value.
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', 23.67,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(23.67, $sanitizedValue);
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
            //Now try an integer for a float. This should work ok.
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', 25,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(25, $sanitizedValue);
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required decimal with an invalid value and a default value.  The default value should not
            //be picked up, it should be ignored.  On the first sanitization failure, sanitization will stop, this is
            //why the default value is not set.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '45.65')));
            $sanitizerUtilTypes        = DecimalAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'float', 'abc',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Float Invalid number format.';
            $this->assertEquals($compareMessage, $messages[0]);

            ///////////////////////
            //Now test Integer
            //Test a non-required integer with no value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = IntegerAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required integer with no value and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '41')));
            $sanitizerUtilTypes        = IntegerAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(41, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required integer with a valid value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '2342')));
            $sanitizerUtilTypes        = IntegerAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', '34',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('34', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
            //Now try with a correctly casted value.
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', 654,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(654, $sanitizedValue);
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
            //Now try a float for an integer. This should work ok.
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', 25.54,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(25.54, $sanitizedValue);
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required integer with an invalid value and a default value.  The default value should not
            //be picked up, it should be ignored.  On the first sanitization failure, sanitization will stop, this is
            //why the default value is not set.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => '45')));
            $sanitizerUtilTypes        = IntegerAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'integer', 'abc',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Integer Invalid number format.';
            $this->assertEquals($compareMessage, $messages[0]);

        }

        public function testSanitizeValueBySanitizerTypesForRelatedModelIdTypeThatNotIsRequired()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $importModelTestItem2Model1 = ImportTestHelper::createImportModelTestItem2('aaa');
            $importModelTestItem2Model2 = ImportTestHelper::createImportModelTestItem2('bbb');
            $importModelTestItem2Model3 = ImportTestHelper::createImportModelTestItem2('ccc');

            //Update the external system id.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar(ImportModelTestItem2::getTableName('ImportModelTestItem2'), $columnName);
            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            R::exec("update " . ImportModelTestItem2::getTableName('ImportModelTestItem2')
            . " set $externalSystemIdColumnName = 'R' where id = {$importModelTestItem2Model3->id}");

            //Test a non-required related model with an invalid value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', 'qweqwe',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Has One The id specified did not match any existing records.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a non-required related model with no value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a valid zurmo model id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', $importModelTestItem2Model2->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem2Model2, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a valid external system id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', 'R',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem2Model3, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a valid model name
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', 'bbb',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem2Model2, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required related model with a model name for a new model.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'RelatedModelValueTypeMappingRuleForm' =>
                                               array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME)));
            $sanitizerUtilTypes        = ImportModelTestItem2AttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'hasOne', 'rrr',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('rrr', $sanitizedValue->name);
            $this->assertEquals('ImportModelTestItem2', get_class($sanitizedValue));
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForSelfIdTypeThatIsRequired()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $importModelTestItem1Model1 = ImportTestHelper::createImportModelTestItem('aaa', 'xxxx');
            $importModelTestItem1Model2 = ImportTestHelper::createImportModelTestItem('bbb', 'yyyy');
            $importModelTestItem1Model3 = ImportTestHelper::createImportModelTestItem('ccc', 'zzzz');

            //Update the external system id.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar(ImportModelTestItem::getTableName('ImportModelTestItem'), $columnName);
            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            R::exec("update " . ImportModelTestItem::getTableName('ImportModelTestItem')
            . " set $externalSystemIdColumnName = 'J' where id = {$importModelTestItem1Model3->id}");

            //Test the id attribute with an invalid value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = IdAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'id', 'xasdasd',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Id The id specified did not match any existing records.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test the id attribute with no value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = IdAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'id', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid zurmo model id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID)));
            $sanitizerUtilTypes        = IdAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'id', $importModelTestItem1Model2->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem1Model2->id, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid external system id
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'IdValueTypeMappingRuleForm' =>
                                               array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID)));
            $sanitizerUtilTypes        = IdAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'id', 'J',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($importModelTestItem1Model3->id, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForStringTypeThatIsRequired()
        {
            //Test a required string with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - String This field is required and neither a value' .
                              ' nor a default value was specified.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required string with no value, but a valid default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('something valid', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', 'aValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('aValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', 'bValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('bValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a value that is too long and no specified default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value, 0, 64), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a value that is too long and a specified default value. The specified
            //default value should be ignored in this scenario.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'willNotMatter')));
            $sanitizerUtilTypes        = TextAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'string', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value, 0, 64), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForStringTypeThatIsNotRequired()
        {
            //Test a non-required phone with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required phone with no value, but a valid default value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('something valid', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required phone with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', 'aValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('aValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required phone with a valid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', 'bValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('bValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required phone with a value that is too long and no specified default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value, 0, 14), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required phone with a value that is too long and a specified default value. The specified
            //default value should be ignored in this scenario.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'willNotMatter')));
            $sanitizerUtilTypes        = PhoneAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'phone', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value, 0, 14), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForTextAreaTypeThatIsNotRequired()
        {
            //Test a non-required textArea with no value
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array();
            $sanitizerUtilTypes        = TextAreaAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'textArea', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required textArea with a valid value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array();
            $sanitizerUtilTypes        = TextAreaAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'textArea', 'bValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('bValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required textArea with a value that is too long.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array();
            $sanitizerUtilTypes        = TextAreaAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(65070);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'textArea', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value, 0, 65000), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }

        public function testSanitizeValueBySanitizerTypesForUrlTypeThatIsNotRequired()
        {
            //Test a non-required email with no value and no default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = UrlAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'url', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with no value and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'abc.com')));
            $sanitizerUtilTypes        = UrlAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'url', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('abc.com', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with a valid value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'def.com')));
            $sanitizerUtilTypes        = UrlAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'url', 'gre.com',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('http://gre.com', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required email with an invalid value and a default value.  The default value should not
            //be picked up, it should be ignored.  On the first sanitization failure, sanitization will stop, this is
            //why the default value is not set.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'ggggga.com')));
            $sanitizerUtilTypes        = UrlAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'url', 'abcxco@',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(null, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Url Invalid url format.';
            $this->assertEquals($compareMessage, $messages[0]);
        }

        public function testSanitizeValueBySanitizerTypesForUserTypeThatIsRequired()
        {
            $billy = UserTestHelper::createBasicUser('billy');
            $jimmy = UserTestHelper::createBasicUser('jimmy');
            $sally = UserTestHelper::createBasicUser('sally');

            //Update the external system id.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBean_Plugin_Optimizer_ExternalSystemId::ensureColumnIsVarchar(User::getTableName('User'), $columnName);
            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            R::exec("update " . User::getTableName('User')
            . " set $externalSystemIdColumnName = 'K' where id = {$jimmy->id}");

            //Test a required user with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)));
            $sanitizerUtilTypes        = UserAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'owner', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'ImportModelTestItem - Owner This id is required and was not specified.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required string with no value, but a valid default value, a user id.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => $billy->id),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)));
            $sanitizerUtilTypes        = UserAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'owner', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($billy, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid user id.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID)));
            $sanitizerUtilTypes        = UserAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'owner', $billy->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($billy, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid external system user id.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID)));
            $sanitizerUtilTypes        = UserAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'owner', 'K',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($jimmy, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required string with a valid username.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)));
            $sanitizerUtilTypes        = UserAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'ImportModelTestItem', 'owner', 'sally',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($sally, $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }
    }
?>