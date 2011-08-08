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
            assert('$saved');

            //Ensure the external system id column is present.
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar100(User::getTableName('User'), 'externalSystemId');
            $userTableName = User::getTableName('User');
            R::exec("update " . $userTableName . " set externalSystemId = 'A' where id = {$super->id}");
            R::exec("update " . $userTableName . " set externalSystemId = 'B' where id = {$jim->id}");

            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar100(ImportModelTestItem::getTableName('ImportModelTestItem'),   'externalSystemId');
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar100(ImportModelTestItem2::getTableName('ImportModelTestItem2'), 'externalSystemId');
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar100(ImportModelTestItem3::getTableName('ImportModelTestItem3'), 'externalSystemId');
            RedBean_Plugin_Optimizer_ExternalSystemId::
            ensureColumnIsVarchar100(ImportModelTestItem4::getTableName('ImportModelTestItem4'), 'externalSystemId');
        }

        public function testImportDataAnalysisResults()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            R::exec("update " . $import->getTempTableName() . " set column_8 = " .
                     Yii::app()->user->userModel->id ." where id != 1 limit 4");


            //Add test ImportModelTestItem models for use in this test.
            $importModelTestItemModel1 = ImportTestHelper::createImportModelTestItem('aaa','aba');
            $importModelTestItemModel2 = ImportTestHelper::createImportModelTestItem('ddw','daf');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_10 = " .
                     $importModelTestItemModel1->id ." where id != 1 limit 3");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem::getTableName('ImportModelTestItem')
            . " set externalSystemId = 'B' where id = {$importModelTestItemModel2->id}");

            //Add test ImportModelTestItem2 models for use in this test.
            $importModelTestItem2Model1 = ImportTestHelper::createImportModelTestItem2('aaa');
            $importModelTestItem2Model2 = ImportTestHelper::createImportModelTestItem2('bbb');
            $importModelTestItem2Model3 = ImportTestHelper::createImportModelTestItem2('ccc');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping.
            R::exec("update " . $import->getTempTableName() . " set column_14 = " .
                     $importModelTestItem2Model1->id ." where id != 1 limit 4");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem2::getTableName('ImportModelTestItem2')
            . " set externalSystemId = 'B' where id = {$importModelTestItem2Model2->id}");

            //Add test ImportModelTestItem3 models for use in this test.
            $importModelTestItem3Model1 = ImportTestHelper::createImportModelTestItem3('aaa');
            $importModelTestItem3Model2 = ImportTestHelper::createImportModelTestItem3('dd');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_17 = " .
                     $importModelTestItem3Model1->id ." where id != 1 limit 3");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem3::getTableName('ImportModelTestItem3')
            . " set externalSystemId = 'K' where id = {$importModelTestItem3Model2->id}");

            //Add test ImportModelTestItem4 models for use in this test.
            $importModelTestItem4Model1 = ImportTestHelper::createImportModelTestItem4('aaa');
            $importModelTestItem4Model2 = ImportTestHelper::createImportModelTestItem4('dd');
            //Update for of the import rows to point to model 1.  This is for the ZURMO_MODEL_ID mapping rule form type value.
            R::exec("update " . $import->getTempTableName() . " set column_12 = " .
                     $importModelTestItem4Model1->id ." where id != 1 limit 5");
            //Update model2 to have an externalSystemId.
            R::exec("update " . ImportModelTestItem3::getTableName('ImportModelTestItem4')
            . " set externalSystemId = 'J' where id = {$importModelTestItem4Model2->id}");

            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',   	  'type' => 'importColumn'),
                'column_1' => array('attributeIndexOrDerivedType' => 'phone',    	  'type' => 'importColumn'),
                'column_2' => array('attributeIndexOrDerivedType' => 'float',    	  'type' => 'importColumn'),
                'column_3' => array('attributeIndexOrDerivedType' => 'boolean',  	  'type' => 'importColumn'),
                'column_4' => array('attributeIndexOrDerivedType' => 'date', 	 	  'type' => 'importColumn',
                                    'mappingRulesData' => array('ValueFormat' =>
                                    array('format' => 'MM-dd-yyyy'))),
                'column_5' => array('attributeIndexOrDerivedType' => 'dateTime', 	  'type' => 'importColumn',
                                    'mappingRulesData' => array('ValueFormat' =>
                                    array('format' => 'MM-dd-yyyy hh:mm'))),
                'column_6' => array('attributeIndexOrDerivedType' => 'dropDown',      'type' => 'importColumn'),
                'column_7' => array('attributeIndexOrDerivedType' => 'CreatedByUser', 'type' => 'importColumn',
                                    'mappingRulesData' => array('UserValueTypeModelAttribute' =>
                                    array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME))),
                'column_8' => array('attributeIndexOrDerivedType' => 'ModifiedByUser', 'type' => 'importColumn',
                                    'mappingRulesData' => array('UserValueTypeModelAttribute' =>
                                    array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID))),
                'column_9' => array('attributeIndexOrDerivedType' => 'owner', 		   'type' => 'importColumn',
                                    'mappingRulesData' => array('UserValueTypeModelAttribute' =>
                                    array('type' => UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID))),
                'column_10' => array('attributeIndexOrDerivedType' => 'id', 		   'type' => 'importColumn',
                                    'mappingRulesData' => array('IdValueType' =>
                                    array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_11' => array('attributeIndexOrDerivedType' => 'id', 		   'type' => 'importColumn',
                                    'mappingRulesData' => array('IdValueType' =>
                                    array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
                'column_12' => array('attributeIndexOrDerivedType' => 'hasOneAlso', 'type' => 'importColumn',
                                    'mappingRulesData' => array('IdValueType' =>
                                    array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_13' => array('attributeIndexOrDerivedType' => 'hasOneAlso', 'type' => 'importColumn',
                                    'mappingRulesData' => array('IdValueType' =>
                                    array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
                'column_14'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueType' =>
                                      array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_15'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueType' =>
                                      array('type' => RelatedModelValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
                'column_16'  => array('attributeIndexOrDerivedType' => 'hasOne',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array('RelatedModelValueType' =>
                                      array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_NAME))),
                'column_17'  => array('attributeIndexOrDerivedType' => 'ImportModelTestItem3Derived',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array('IdValueType' =>
                                      array('type' => IdValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_18'  => array('attributeIndexOrDerivedType' => 'ImportModelTestItem3Derived',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array('IdValueType' =>
                                      array('type' => IdValueTypeMappingRuleForm::EXTERNAL_SYSTEM_ID))),
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
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider);
            foreach($mappingData as $columnName => $columnMappingData)
            {
                $importDataAnalyzer->analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData);
            }
            $resultsData = $importDataAnalyzer->getResults();
            $compareData = array(
                'column_0' => array(
                    array('message'=> '1 value(s) are too large for this field. These values will be truncated to a length of 64 upon import.',
                          'sanitizerUtilType' => 'Truncate', 'moreAvailable' => false),
                ),
                'column_1' => array(
                    array('message'=> '2 value(s) are too large for this field. These values will be truncated to a length of 14 upon import.',
                           'sanitizerUtilType' => 'Truncate', 'moreAvailable' => false),
                ),
                'column_3' => array(
                    array('message'=> '2 value(s) have invalid check box values. These values will be set to false upon import.',
                           'sanitizerUtilType' => 'Boolean', 'moreAvailable' => false),
                ),
                'column_4' => array(
                    array('message'=> '2 value(s) have invalid date formats. These values will be cleared during import.',
                           'sanitizerUtilType' => 'Date', 'moreAvailable' => false),
                ),
                'column_5' => array(
                    array('message'=> '2 value(s) have invalid date time formats. These values will be cleared during import.',
                           'sanitizerUtilType' => 'DateTime', 'moreAvailable' => false),
                ),
                'column_6' => array(
                    array('message'=> '2 dropdown value(s) are missing from the field. These values will be added upon import.',
                           'sanitizerUtilType' => 'DropDown', 'moreAvailable' => false),
                ),
                'column_7' => array(
                    array('message'=> '2 username(s) specified were not found. These values will not be used during the import.',
                           'sanitizerUtilType' => 'UserValueType', 'moreAvailable' => false),
                ),
                'column_8' => array(
                    array('message'=> '1 zurmo user id(s) across 7 row(s) were not found. These values will not be used during the import.',
                           'sanitizerUtilType' => 'UserValueType', 'moreAvailable' => false),
                ),
                'column_9' => array(
                    array('message'=> '2 external system user id(s) specified were not found. These values will not be used during the import.',
                           'sanitizerUtilType' => 'UserValueType', 'moreAvailable' => false),
                ),
                'column_10' => array(
                    array('message'=> '3 record(s) will be updated and 9 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'SelfIdValueType', 'moreAvailable' => false),
                ),
                'column_11' => array(
                    array('message'=> '1 record(s) will be updated and 11 record(s) will be created during the import.',
                           'sanitizerUtilType' => 'SelfIdValueType', 'moreAvailable' => false),
                ),
                'column_12' => array(
                    array('message'=> '5 record(s) will be updated and 7 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'IdValueType', 'moreAvailable' => false),
                ),
                'column_13' => array(
                    array('message'=> '2 record(s) will be updated and 10 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'IdValueType', 'moreAvailable' => false),
                ),
                'column_14' => array(
                    array('message'=> '4 record(s) will be updated and 8 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'RelatedModelNameOrIdValueType', 'moreAvailable' => false),
                ),
                'column_15' => array(
                    array('message'=> '1 record(s) will be updated and 11 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'RelatedModelNameOrIdValueType', 'moreAvailable' => false),
                ),
                'column_16' => array(
                    array('message'=> '1 record(s) will be updated and 11 record(s) will be created during the import.',
                           'sanitizerUtilType' => 'RelatedModelNameOrIdValueType', 'moreAvailable' => false),
                ),
                'column_17' => array(
                    array('message'=> '3 record(s) will be updated and 9 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'ImportModelTestItem3ModelDerivedIdValueType', 'moreAvailable' => false),
                ),
                'column_18' => array(
                    array('message'=> '2 record(s) will be updated and 10 record(s) will be skipped during import.',
                           'sanitizerUtilType' => 'ImportModelTestItem3ModelDerivedIdValueType', 'moreAvailable' => false),
                ),
            );
            $this->assertEquals($compareData, $resultsData);
        }

        /**
         * @depends testImportDataAnalysisResults
         */
        public function testImportDataAnalysisUsingBatchAnalyzers()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');

            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            R::exec("update " . $import->getTempTableName() . " set column_8 = " .
                     Yii::app()->user->userModel->id ." where id != 1 limit 6");

            $config       = array('pagination' => array('pageSize' => 2));
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Test truncate sanitization by batch.
            $dataAnalyzer = new TruncateBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('phone'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_1');
            $compareMessage = '2 value(s) are too large for this field. These values will be truncated to a length of 14 upon import.';
            $this->assertEquals($compareMessage, $message);

            //Test boolean sanitization by batch.
            $dataAnalyzer = new BooleanBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('boolean'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_3');
            $compareMessage = '2 value(s) have invalid check box values. These values will be set to false upon import.';
            $this->assertEquals($compareMessage, $message);

            //Test date sanitization by batch.
            $dataAnalyzer = new DateBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('date'));
            $message      = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_4', 'ValueFormat',
                            array('format' => 'MM-dd-yyyy'));
            $compareMessage = '2 value(s) have invalid date formats. These values will be cleared during import.';
            $this->assertEquals($compareMessage, $message);

            //Test datetime sanitization by batch.
            $dataAnalyzer = new DateTimeBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('dateTime'));
            $message      = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_5', 'ValueFormat',
                            array('format' => 'MM-dd-yyyy hh:mm'));
            $compareMessage = '2 value(s) have invalid date time formats. These values will be cleared during import.';
            $this->assertEquals($compareMessage, $message);

            //Test dropdown sanitization by batch.
            $dataAnalyzer = new DropDownBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('dropDown'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_6');
            $compareMessage = '2 dropdown value(s) are missing from the field. These values will be added upon import.';
            $this->assertEquals($compareMessage, $message);

            //Test CreatedByUser sanitization by batch.
            $dataAnalyzer = new UserValueTypeBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('CreatedByUser'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_7', 'UserValueTypeModelAttribute',
                       array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME));
            $compareMessage = '2 value(s) have invalid user values. These values will not be used during the import.';
            $this->assertEquals($compareMessage, $message);

            //Test ModifiedByUser sanitization by batch.
            $dataAnalyzer = new UserValueTypeBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('ModifiedByUser'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_8', 'UserValueTypeModelAttribute',
                       array('type' => UserValueTypeModelAttributeMappingRuleForm::ZURMO_USER_ID));
            $compareMessage = '5 value(s) have invalid user values. These values will not be used during the import.';
            $this->assertEquals($compareMessage, $message);

            //Test owner sanitization by batch.
            $dataAnalyzer = new UserValueTypeBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('owner'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_9', 'UserValueTypeModelAttribute',
                       array('type' => UserValueTypeModelAttributeMappingRuleForm::EXTERNAL_SYSTEM_USER_ID));
            $compareMessage = '2 value(s) have invalid user values. These values will not be used during the import.';
            $this->assertEquals($compareMessage, $message);
        }
    }
?>
