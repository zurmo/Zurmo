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

    class TasksImportDataAnalyzerTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;

            $accountTableName     = Account::getTableName('Account');
            $contactTableName     = Contact::getTableName('Contact');
            $opportunityTableName = Opportunity::getTableName('Opportunity');
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBeanColumnTypeOptimizer::
            externalIdColumn($accountTableName,     $columnName);
            RedBeanColumnTypeOptimizer::
            externalIdColumn($contactTableName,     $columnName);
            RedBeanColumnTypeOptimizer::
            externalIdColumn($opportunityTableName, $columnName);
        }

        public function testImportDataAnalysisResults()
        {
            $super                             = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $import                            = new Import();
            $serializedData['importRulesType'] = 'Tasks';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());

            $account1 = AccountTestHelper::createAccountByNameForOwner('account1', $super);
            $account2 = AccountTestHelper::createAccountByNameForOwner('account2', $super);
            $account3 = AccountTestHelper::createAccountByNameForOwner('account3', $super);

            $contact1 = ContactTestHelper::createContactByNameForOwner('contact1', $super);
            $contact2 = ContactTestHelper::createContactByNameForOwner('contact2', $super);
            $contact3 = ContactTestHelper::createContactByNameForOwner('contact3', $super);

            $opportunity1 = OpportunityTestHelper::createOpportunityByNameForOwner('opportunity1', $super);
            $opportunity2 = OpportunityTestHelper::createOpportunityByNameForOwner('opportunity2', $super);
            $opportunity3 = OpportunityTestHelper::createOpportunityByNameForOwner('opportunity3', $super);

            //Make models externally linked for testing.
            ImportTestHelper::updateModelsExternalId($account2,     'ACC');
            ImportTestHelper::updateModelsExternalId($contact2,     'CON');
            ImportTestHelper::updateModelsExternalId($opportunity2, 'OPP');

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.tasks.tests.unit.files'));
            R::exec("update " . $import->getTempTableName() . " set column_0 = " .
                    $account3->id . " where id != 1 limit 3");
            R::exec("update " . $import->getTempTableName() . " set column_2 = " .
                    $contact3->id . " where id != 1 limit 4");
            R::exec("update " . $import->getTempTableName() . " set column_4 = " .
                    $opportunity3->id . " where id != 1 limit 5");

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('AccountDerived',
                               IdValueTypeMappingRuleForm::ZURMO_MODEL_ID),
                'column_1'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('AccountDerived'),
                'column_2'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('ContactDerived',
                               IdValueTypeMappingRuleForm::ZURMO_MODEL_ID),
                'column_3'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('ContactDerived'),
                'column_4'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('OpportunityDerived',
                               IdValueTypeMappingRuleForm::ZURMO_MODEL_ID),
                'column_5'  => ImportMappingUtil::makeModelDerivedColumnMappingData ('OpportunityDerived'),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('Tasks');
            $config       = array('pagination' => array('pageSize' => 15));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData,
                                  array('column_0', 'column_1', 'column_2', 'column_3', 'column_4', 'column_5'));
            $importDataAnalyzer->analyzePage();
            $data = $dataProvider->getData();
            $this->assertEquals(10, count($data));

            $compareData = array();
            $compareData['column_0'][] = 'Is an existing record and will be updated.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Is an existing record and will be updated.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Is an existing record and will be updated.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[0]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[0]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Is an existing record and will be updated.';
            $compareData['column_1'][] = 'Is an existing record and will be updated.';
            $compareData['column_2'][] = 'Is an existing record and will be updated.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Is an existing record and will be updated.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[1]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[1]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Is an existing record and will be updated.';
            $compareData['column_1'][] = 'Is an existing record and will be updated.';
            $compareData['column_2'][] = 'Is an existing record and will be updated.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Is an existing record and will be updated.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[2]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[2]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Is an existing record and will be updated.';
            $compareData['column_2'][] = 'Is an existing record and will be updated.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Is an existing record and will be updated.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[3]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[3]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_3'][] = 'Is an existing record and will be updated.';
            $compareData['column_4'][] = 'Is an existing record and will be updated.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[4]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[4]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_3'][] = 'Is an existing record and will be updated.';
            $compareData['column_4'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[5]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[5]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_3'][] = 'Is an existing record and will be updated.';
            $compareData['column_4'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_5'][] = 'Is an existing record and will be updated.';
            $this->assertEquals($compareData, unserialize($data[6]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[6]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_5'][] = 'Is an existing record and will be updated.';
            $this->assertEquals($compareData, unserialize($data[7]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[7]->analysisStatus);

            $compareData = array();
            $compareData['column_0'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_1'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_2'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_3'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_4'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_5'][] = 'Is an existing record and will be updated.';
            $this->assertEquals($compareData, unserialize($data[8]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[8]->analysisStatus);

            //Will result with no problems since it is all blank.
            $this->assertFalse(unserialize($data[9]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[9]->analysisStatus);
        }
    }
?>
