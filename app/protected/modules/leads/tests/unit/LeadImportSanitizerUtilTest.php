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

    class LeadImportSanitizerUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
        }

        public function testSanitizeValueBySanitizerTypesForLeadStateTypeThatIsRequired()
        {
            $contactStates = ContactState::getAll();
            $this->assertEquals(6, count($contactStates));

            //Test a required contact state with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => null)));
            $sanitizerUtilTypes        = FirstStatesContactAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - The status is required.  Neither a value nor a default was specified.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required contact state with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => $contactStates[0]->id)));
            $sanitizerUtilTypes        = FirstStatesContactAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, $contactStates[1]->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($contactStates[1], $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required contact state with no value, and a default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultStateId' => $contactStates[0]->id)));
            $sanitizerUtilTypes        = FirstStatesContactAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals($contactStates[0], $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a required contact state with a value that is invalid
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = FirstStatesContactAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, 'somethingnotright',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - The status specified does not exist.';
            $this->assertEquals($compareMessage, $messages[0]);

            //Test a required contact state with a state that is for leads, not contacts.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('mappingRulesData' => array(
                                               'DefaultLeadStateIdMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = FirstStatesContactAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'Contact', null, $contactStates[5]->id,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertFalse($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(1, count($messages));
            $compareMessage = 'Contact - The status specified is invalid.';
            $this->assertEquals($compareMessage, $messages[0]);
        }
    }
?>