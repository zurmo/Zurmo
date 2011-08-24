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

    class UserImportSanitizerUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSanitizeValueBySanitizerTypesForPasswordTypeThatIsNotRequired()
        {
            //Test a non-required password with no value or default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = PasswordAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'User', 'hash', null,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertNull($sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required password with a valid value, and a default value. The valid value should come through.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => 'something valid')));
            $sanitizerUtilTypes        = PasswordAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'User', 'hash', 'aValue',
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals('aValue', $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));

            //Test a non-required password with a value that is too long and no specified default value.
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $columnMappingData         = array('type' => 'importColumn', 'mappingRulesData' => array(
                                               'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                               array('defaultValue' => null)));
            $sanitizerUtilTypes        = PasswordAttributeImportRules::getSanitizerUtilTypesInProcessingOrder();
            $value                     = self::getStringByLength(85);
            $sanitizedValue            = ImportSanitizerUtil::
                                         sanitizeValueBySanitizerTypes(
                                         $sanitizerUtilTypes, 'User', 'hash', $value,
                                         $columnMappingData, $importSanitizeResultsUtil);
            $this->assertEquals(substr($value,0, 32), $sanitizedValue);
            $this->assertTrue($importSanitizeResultsUtil->shouldSaveModel());
            $messages = $importSanitizeResultsUtil->getMessages();
            $this->assertEquals(0, count($messages));
        }
    }
?>