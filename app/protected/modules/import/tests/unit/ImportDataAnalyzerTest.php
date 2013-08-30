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

    class ImportDataAnalyzerTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            $jim = UserTestHelper::createBasicUser('jim');
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
            assert($saved);    // Not Coding Standard

            $values = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('ImportTestMultiDropDown');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            $values = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('ImportTestTagCloud');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            //Ensure the external system id column is present.
            $columnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            RedBeanColumnTypeOptimizer::
            externalIdColumn(User::getTableName('User'), $columnName);
            $userTableName = User::getTableName('User');
            R::exec("update " . $userTableName . " set $columnName = 'A' where id = {$super->id}");
            R::exec("update " . $userTableName . " set $columnName = 'B' where id = {$jim->id}");

            RedBeanColumnTypeOptimizer::
            externalIdColumn(ImportModelTestItem::getTableName('ImportModelTestItem'),   $columnName);
            RedBeanColumnTypeOptimizer::
            externalIdColumn(ImportModelTestItem2::getTableName('ImportModelTestItem2'), $columnName);
            RedBeanColumnTypeOptimizer::
            externalIdColumn(ImportModelTestItem3::getTableName('ImportModelTestItem3'), $columnName);
            RedBeanColumnTypeOptimizer::
            externalIdColumn(ImportModelTestItem4::getTableName('ImportModelTestItem4'), $columnName);
        }

        /**
         * This test was needed because of the wierd type casting issues with 0 and 1 and '1' and '0' as keys in an array.
         * '0' and '1' turn into integers which they shouldn't and this messes up the oneOf sql query builder. Additionally
         * on some versions of MySQL, 0,1 in a NOT IN, will evaluate true to 'abc' which it shouldn't.  As a result
         * the 0/1 boolean values have been removed from the BooleanSanitizerUtil::getAcceptableValues().
         */
        public function testBooleanAcceptableValuesMappingAndSqlOneOfString()
        {
            $string = SQLOperatorUtil::
                      resolveOperatorAndValueForOneOf('oneOf', BooleanSanitizerUtil::getAcceptableValues());
            $compareString = "IN('false','true','y','n','yes','no','0','1','')"; // Not Coding Standard
            $this->assertEquals($compareString, $string);
        }

        /**
         * @depends testBooleanAcceptableValuesMappingAndSqlOneOfString
         */
        public function testImportDataAnalysisResultsForMultiSelectWithNothingWrong()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest2.csv', $import->getTempTableName());
            $mappingData = array(
                'column_1' => array('attributeIndexOrDerivedType' => 'multiDropDown',      'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueMultiSelectDropDownModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $config       = array('pagination' => array('pageSize' => 2));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData, array('column_1'));
            $importDataAnalyzer->analyzePage();
            $customFieldsInstructionData = $importDataAnalyzer->getCustomFieldsInstructionData();
            $this->assertFalse($customFieldsInstructionData->hasDataByColumnName('column_1'));
            //Confirm analysis status and message
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[0]->analysisStatus);
            $this->assertNull($data[0]->serializedAnalysisMessages);
        }

        /**
         * It should not throw an exception even though the mappingRuleForm is missing.  This could happen if the
         * multi-select default value is unselected entirely
         * @depends testImportDataAnalysisResultsForMultiSelectWithNothingWrong
         */
        public function testImportDataAnalysisResultsForMultiSelectMissingMappingRuleForm()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest2.csv', $import->getTempTableName());
            $mappingData = array(
                'column_1' => array('attributeIndexOrDerivedType' => 'multiDropDown',      'type' => 'importColumn',
                                    'mappingRulesData' => array()),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $config       = array('pagination' => array('pageSize' => 2));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData, array('column_1'));
            $importDataAnalyzer->analyzePage();
            $customFieldsInstructionData = $importDataAnalyzer->getCustomFieldsInstructionData();
            $this->assertFalse($customFieldsInstructionData->hasDataByColumnName('column_1'));
            //Confirm analysis status and message
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[0]->analysisStatus);
            $this->assertNull($data[0]->serializedAnalysisMessages);
        }

        /**
         * @depends testImportDataAnalysisResultsForMultiSelectMissingMappingRuleForm
         */
        public function testImportDataAnalysisResults()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            R::exec("update " . $import->getTempTableName() . " set column_8 = " .
                     Yii::app()->user->userModel->id . " where id != 1 limit 4");

            $externalSystemIdColumnName = ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME;
            //Add test ImportModelTestItem models for use in this test.
            $importModelTestItemModel1 = ImportTestHelper::createImportModelTestItem('aaa', 'aba');
            $importModelTestItemModel2 = ImportTestHelper::createImportModelTestItem('ddw', 'daf');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_10 = " .
                     $importModelTestItemModel1->id . " where id != 1 limit 3");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem::getTableName('ImportModelTestItem')
            . " set $externalSystemIdColumnName = 'B' where id = {$importModelTestItemModel2->id}");

            //Add test ImportModelTestItem2 models for use in this test.
            $importModelTestItem2Model1 = ImportTestHelper::createImportModelTestItem2('aaa');
            $importModelTestItem2Model2 = ImportTestHelper::createImportModelTestItem2('bbb');
            $importModelTestItem2Model3 = ImportTestHelper::createImportModelTestItem2('ccc');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping.
            R::exec("update " . $import->getTempTableName() . " set column_14 = " .
                     $importModelTestItem2Model1->id . " where id != 1 limit 4");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem2::getTableName('ImportModelTestItem2')
            . " set $externalSystemIdColumnName = 'B' where id = {$importModelTestItem2Model2->id}");

            //Add test ImportModelTestItem3 models for use in this test.
            $importModelTestItem3Model1 = ImportTestHelper::createImportModelTestItem3('aaa');
            $importModelTestItem3Model2 = ImportTestHelper::createImportModelTestItem3('dd');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_17 = " .
                     $importModelTestItem3Model1->id . " where id != 1 limit 3");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem3::getTableName('ImportModelTestItem3')
            . " set $externalSystemIdColumnName = 'K' where id = {$importModelTestItem3Model2->id}");

            //Add test ImportModelTestItem4 models for use in this test.
            $importModelTestItem4Model1 = ImportTestHelper::createImportModelTestItem4('aaa');
            $importModelTestItem4Model2 = ImportTestHelper::createImportModelTestItem4('dd');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_12 = " .
                     $importModelTestItem4Model1->id . " where id != 1 limit 5");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem3::getTableName('ImportModelTestItem4')
            . " set $externalSystemIdColumnName = 'J' where id = {$importModelTestItem4Model2->id}");

            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_1' => array('attributeIndexOrDerivedType' => 'phone',          'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_2' => array('attributeIndexOrDerivedType' => 'float',          'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_3' => array('attributeIndexOrDerivedType' => 'boolean',        'type' => 'importColumn'),

                'column_4' => array('attributeIndexOrDerivedType' => 'date',           'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null),
                                        'ValueFormatMappingRuleForm'                =>
                                        array('format' => 'MM-dd-yyyy'))),

                'column_5' => array('attributeIndexOrDerivedType' => 'dateTime',       'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null),
                                        'ValueFormatMappingRuleForm' =>
                                        array('format' => 'MM-dd-yyyy hh:mm'))),

                'column_6' => array('attributeIndexOrDerivedType' => 'dropDown',      'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueDropDownModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_7' => array('attributeIndexOrDerivedType' => 'createdByUser', 'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                    'UserValueTypeModelAttributeMappingRuleForm' =>
                                        array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME))),

                'column_8' => array('attributeIndexOrDerivedType' => 'modifiedByUser', 'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                    'UserValueTypeModelAttributeMappingRuleForm' =>
                                        array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID))),

                'column_9' => array('attributeIndexOrDerivedType' => 'owner',           'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                    'DefaultModelNameIdMappingRuleForm' => array('defaultModelId' => null),
                                    'UserValueTypeModelAttributeMappingRuleForm' =>
                                        array('type' => UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID))),

                'column_10' => array('attributeIndexOrDerivedType' => 'id',             'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'IdValueTypeMappingRuleForm' =>
                                        array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_11' => array('attributeIndexOrDerivedType' => 'id',             'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'IdValueTypeMappingRuleForm' =>
                                        array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),

                'column_12' => array('attributeIndexOrDerivedType' => 'hasOneAlso',     'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultModelNameIdMappingRuleForm' => array('defaultModelId' => null),
                                        'IdValueTypeMappingRuleForm' => array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_13' => array('attributeIndexOrDerivedType' => 'hasOneAlso',     'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultModelNameIdMappingRuleForm' => array('defaultModelId' => null),
                                        'IdValueTypeMappingRuleForm' =>
                                        array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),

                'column_14'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueTypeMappingRuleForm' =>
                                          array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_15'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueTypeMappingRuleForm' =>
                                          array('type' => RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),

                'column_16'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueTypeMappingRuleForm' =>
                                          array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME))),

                'column_17'  => array('attributeIndexOrDerivedType' => 'ImportModelTestItem3Derived',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),

                'column_18'  => array('attributeIndexOrDerivedType' => 'ImportModelTestItem3Derived',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'IdValueTypeMappingRuleForm' =>
                                          array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),

                'column_19' => array('attributeIndexOrDerivedType' => 'url',            'type' => 'importColumn',
                                     'mappingRulesData' => array(
                                         'DefaultValueModelAttributeMappingRuleForm' =>
                                          array('defaultValue' => null))),

                'column_20' => array('attributeIndexOrDerivedType' => 'textArea', 'type' => 'importColumn'),

                'column_21' => array('attributeIndexOrDerivedType' => 'integer',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_22' => array('attributeIndexOrDerivedType' => 'currencyValue',  'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_23' => array('attributeIndexOrDerivedType' => 'FullName',       'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),

                'column_24' => array('attributeIndexOrDerivedType' => 'multiDropDown',      'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueMultiSelectDropDownModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                'column_25' => array('attributeIndexOrDerivedType' => 'tagCloud',      'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueMultiSelectDropDownModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                                     );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $config       = array('pagination' => array('pageSize' => 15));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData, array_keys($mappingData));
            $importDataAnalyzer->analyzePage();
            $data = $dataProvider->getData();
            $this->assertEquals(12, count($data));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[0]->analysisStatus);
            $compareData = array();
            $compareData['column_10'] = array('Is an existing record and will be updated.');
            $compareData['column_11'] = array('Was not found and will create a new record during import.');
            $compareData['column_13'] = array('Was not found and this row will be skipped during import.');
            $compareData['column_15'] = array('Was not found and this row will be skipped during import.');
            $compareData['column_16'] = array('Was not found and will create a new record during import.');
            $compareData['column_17'] = array('Is an existing record and will be updated.');
            $compareData['column_18'] = array('Was not found and this row will be skipped during import.');
            $this->assertEquals($compareData, unserialize($data[0]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[0]->analysisStatus);

            $compareData              = array();
            $compareData['column_1']  = array('Is too long. Maximum length is 14. This value will truncated upon import.');
            $compareData['column_10'] = array('Is an existing record and will be updated.');
            $compareData['column_11'] = array('Was not found and will create a new record during import.');
            $compareData['column_13'] = array('Was not found and this row will be skipped during import.');
            $compareData['column_15'] = array('Was not found and this row will be skipped during import.');
            $compareData['column_16'] = array('Was not found and will create a new record during import.');
            $compareData['column_17'] = array('Is an existing record and will be updated.');
            $compareData['column_18'] = array('Was not found and this row will be skipped during import.');
            $this->assertEquals($compareData, unserialize($data[1]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[1]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is too long. Maximum length is 64. This value will truncated upon import.';
            $compareData['column_1']   = array();
            $compareData['column_1'][] = 'Is too long. Maximum length is 14. This value will truncated upon import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Is an existing record and will be updated.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Is an existing record and will be updated.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[2]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[2]->analysisStatus);

            $compareData = array();
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[3]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[3]->analysisStatus);

            $compareData = array();
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[4]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[4]->analysisStatus);

            $compareData = array();
            $compareData['column_2']   = array();
            $compareData['column_2'][] = 'Is invalid.';
            $compareData['column_4']   = array();
            $compareData['column_4'][] = 'Is an invalid date format. This value will be skipped during import.';
            $compareData['column_6']   = array();
            $compareData['column_6'][] = 'notpresent is new. This value will be added upon import.';
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_9']   = array();
            $compareData['column_9'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Is an existing record and will be updated.';
            $this->assertEquals($compareData, unserialize($data[5]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[5]->analysisStatus);

            $compareData = array();
            $compareData['column_4']   = array();
            $compareData['column_4'][] = 'Is an invalid date format. This value will be skipped during import.';
            $compareData['column_5']   = array();
            $compareData['column_5'][] = 'Is an invalid date time format. This value will be skipped during import.';
            $compareData['column_6']   = array();
            $compareData['column_6'][] = 'neverpresent is new. This value will be added upon import.';
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Is an existing record and will be updated.';
            $compareData['column_24']   = array();
            $compareData['column_24'][] = 'Multi 5 is new. This value will be added upon import.';
            $compareData['column_25']   = array();
            $compareData['column_25'][] = 'Cloud 5 is new. This value will be added upon import.';
            $this->assertEquals($compareData, unserialize($data[6]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[6]->analysisStatus);

            $compareData = array();
            $compareData['column_2']   = array();
            $compareData['column_2'][] = 'Is invalid.';
            $compareData['column_7']   = array();
            $compareData['column_7'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_9']   = array();
            $compareData['column_9'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_21']   = array();
            $compareData['column_21'][] = 'Is invalid.';
            $compareData['column_22']   = array();
            $compareData['column_22'][] = 'Is invalid.';
            $compareData['column_24']   = array();
            $compareData['column_24'][] = 'Multi 4 is new. This value will be added upon import.';
            $compareData['column_25']   = array();
            $compareData['column_25'][] = 'Cloud 4 is new. This value will be added upon import.';
            $this->assertEquals($compareData, unserialize($data[7]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[7]->analysisStatus);

            $compareData = array();
            $compareData['column_3']   = array();
            $compareData['column_3'][] = 'Is an invalid check box value. This will be set to false upon import.';
            $compareData['column_5']   = array();
            $compareData['column_5'][] = 'Is an invalid date time format. This value will be skipped during import.';
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Is an existing record and will be updated.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_21']   = array();
            $compareData['column_21'][] = 'Is invalid.';
            $compareData['column_24']   = array();
            $compareData['column_24'][] = 'Multi 4 is new. This value will be added upon import.';
            $compareData['column_24'][] = 'Multi 5 is new. This value will be added upon import.';
            $compareData['column_25']   = array();
            $compareData['column_25'][] = 'Cloud 4 is new. This value will be added upon import.';
            $compareData['column_25'][] = 'Cloud 5 is new. This value will be added upon import.';
            $this->assertEquals($compareData, unserialize($data[8]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[8]->analysisStatus);

            $compareData = array();
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_15']   = array();
            $compareData['column_15'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Is an existing record and will be linked.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_19']   = array();
            $compareData['column_19'][] = 'Is an invalid URL. This value will be cleared during import.';
            $compareData['column_23']   = array();
            $compareData['column_23'][] = 'Is too long.';
            $this->assertEquals($compareData, unserialize($data[9]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[9]->analysisStatus);

            $compareData = array();
            $compareData['column_3']   = array();
            $compareData['column_3'][] = 'Is an invalid check box value. This will be set to false upon import.';
            $compareData['column_7']   = array();
            $compareData['column_7'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_8']   = array();
            $compareData['column_8'][] = 'Is an invalid user value. This value will be skipped during import.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $compareData['column_12']   = array();
            $compareData['column_12'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_13']   = array();
            $compareData['column_13'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_14']   = array();
            $compareData['column_14'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_16']   = array();
            $compareData['column_16'][] = 'Was not found and will create a new record during import.';
            $compareData['column_17']   = array();
            $compareData['column_17'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_18']   = array();
            $compareData['column_18'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[10]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[10]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is  required.';
            $compareData['column_9']   = array();
            $compareData['column_9'][] = 'Is  required.';
            $compareData['column_10']   = array();
            $compareData['column_10'][] = 'Was not found and this row will be skipped during import.';
            $compareData['column_11']   = array();
            $compareData['column_11'][] = 'Was not found and will create a new record during import.';
            $this->assertEquals($compareData, unserialize($data[11]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[11]->analysisStatus);

            $customFieldsInstructionData = $importDataAnalyzer->getCustomFieldsInstructionData();
            $this->assertTrue($customFieldsInstructionData->hasDataByColumnName('column_6'));
            $compareData = array(CustomFieldsInstructionData::ADD_MISSING_VALUES =>
                                 array( 'notpresent', 'neverpresent'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getDataByColumnName('column_6'));
            $compareData = array(CustomFieldsInstructionData::ADD_MISSING_VALUES =>
                                 array('Multi 5', 'Multi 4'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getDataByColumnName('column_24'));
            $compareData = array(CustomFieldsInstructionData::ADD_MISSING_VALUES =>
                                 array('Cloud 5', 'Cloud 4'));
            $this->assertEquals($compareData, $customFieldsInstructionData->getDataByColumnName('column_25'));
        }

        /**
         * @depends testImportDataAnalysisResults
         */
        public function testMinimumLengthsUsingBatchAnalyzers()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerMinLengthsTest.csv', $import->getTempTableName());
            $config       = array('pagination' => array('pageSize' => 10));
            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),

                'column_1' => array('attributeIndexOrDerivedType' => 'FullName',       'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());
            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData, array_keys($mappingData));
            $importDataAnalyzer->analyzePage();
            $data = $dataProvider->getData();

            $this->assertNull($data[0]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[0]->analysisStatus);

            $compareData = array();
            $compareData['column_1']   = array();
            $compareData['column_1'][] = 'Is too short.';
            $this->assertEquals($compareData, unserialize($data[1]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[1]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is too short. Minimum length is 3.';
            $this->assertEquals($compareData, unserialize($data[2]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[2]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is too short. Minimum length is 3.';
            $compareData['column_1']   = array();
            $compareData['column_1'][] = 'Is too short.';
            $this->assertEquals($compareData, unserialize($data[3]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[3]->analysisStatus);
        }
    }
?>
