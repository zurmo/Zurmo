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
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar($accountTableName,     'externalSystemId');
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar($contactTableName,     'externalSystemId');
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar($opportunityTableName, 'externalSystemId');
        }

        public function testImportDataAnalysisResults()
        {
            $super                             = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $import                            = new Import();
            $serializedData['importRulesType'] = 'Tasks';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());

            $accountTableName     = Account::getTableName('Account');
            $contactTableName     = Contact::getTableName('Contact');
            $opportunityTableName = Opportunity::getTableName('Opportunity');

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
            R::exec("update " . $accountTableName     . " set externalSystemId = 'ACC' where id = {$account2->id}");
            R::exec("update " . $contactTableName     . " set externalSystemId = 'CON' where id = {$contact2->id}");
            R::exec("update " . $opportunityTableName . " set externalSystemId = 'OPP' where id = {$opportunity2->id}");

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.tasks.tests.unit.files'));
            R::exec("update " . $import->getTempTableName() . " set column_0 = " .
                    $account3->id ." where id != 1 limit 3");
            R::exec("update " . $import->getTempTableName() . " set column_2 = " .
                    $contact3->id ." where id != 1 limit 4");
            R::exec("update " . $import->getTempTableName() . " set column_4 = " .
                    $opportunity3->id ." where id != 1 limit 5");



            $mappingData = array(
                'column_0'  => array('attributeIndexOrDerivedType' => 'AccountDerived',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_1'  => array('attributeIndexOrDerivedType' => 'AccountDerived',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
                'column_2'  => array('attributeIndexOrDerivedType' => 'ContactDerived',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_3'  => array('attributeIndexOrDerivedType' => 'ContactDerived',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
                'column_4'  => array('attributeIndexOrDerivedType' => 'OpportunityDerived',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_5'  => array('attributeIndexOrDerivedType' => 'OpportunityDerived',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('Tasks');
            $config       = array('pagination' => array('pageSize' => 2));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider);
            foreach($mappingData as $columnName => $columnMappingData)
            {
                $importDataAnalyzer->analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData);
            }
            $messagesData = $importDataAnalyzer->getMessagesData();
            $compareData = array(
                'column_0' => array(
                    array('message'=> '3 record(s) will be updated and 7 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'AccountDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_1' => array(
                    array('message'=> '3 record(s) will be updated and 7 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'AccountDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_2' => array(
                    array('message'=> '4 record(s) will be updated and 6 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'ContactDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_3' => array(
                    array('message'=> '3 record(s) will be updated and 7 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'ContactDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_4' => array(
                    array('message'=> '5 record(s) will be updated and 5 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'OpportunityDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_5' => array(
                    array('message'=> '3 record(s) will be updated and 7 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'OpportunityDerivedIdValueType', 'moreAvailable' => false),
                ),
            );
            $this->assertEquals($compareData, $messagesData);
            $importInstructionsData   = $importDataAnalyzer->getImportInstructionsData();
            $compareInstructionsData  = array();
            $this->assertEquals($compareInstructionsData, $importInstructionsData);
        }
    }
?>
