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

    class ImportUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $jim = UserTestHelper::createBasicUser('jim');
        }

        public function testSetDataAnalyzerMessagesDataToImport()
        {
            $import = new Import();
            ImportUtil::setDataAnalyzerMessagesDataToImport($import, array('a' => 'b'));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals(array('a' => 'b'), $unserializedData['dataAnalyzerMessagesData']);

            //Test that setting it again wipes out the old value
            ImportUtil::setDataAnalyzerMessagesDataToImport($import, array('d' => 'e'));
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals(array('d' => 'e'), $unserializedData['dataAnalyzerMessagesData']);

            //Test that setting it with merge = true, merges with the existing value.
            ImportUtil::setDataAnalyzerMessagesDataToImport($import, array('k' => 'j'), true);
            $unserializedData = unserialize($import->serializedData);
            $this->assertEquals(array('d' => 'e', 'k' => 'j'), $unserializedData['dataAnalyzerMessagesData']);
        }

        public function testSimpleImportWithStringAndFullNameWhichAreRequiredAttributeOnImportTestModelItem()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            //Unfreeze since the test model is not part of the standard schema.
            $freezeWhenComplete = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freezeWhenComplete = true;
            }

            $testModels                        = ImportModelTestItem::getAll();
            $this->assertEquals(0, count($testModels));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'ImportModelTestItem';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());

            $this->assertEquals(13, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                'column_23' => array('attributeIndexOrDerivedType' => 'FullName',     'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                                        );

            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions());
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 10 models where created.
            $testModels = ImportModelTestItem::getAll();
            $this->assertEquals(10, count($testModels));
            $jim = User::getByUsername('jim');
            foreach ($testModels as $model)
            {
                $this->assertEquals(array(Permission::NONE, Permission::NONE), $model->getExplicitActualPermissions ($jim));
            }

            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(10, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(2, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(2, count($beansWithErrors));

            //Confirm the messages are as expected.
            $compareMessages = array(
                'ImportModelTestItem - Last name specified is too large.',
                'ImportModelTestItem - Last Name - Last Name cannot be blank.',
            );
            $this->assertEquals($compareMessages, unserialize(current($beansWithErrors)->serializedmessages));

            $compareMessages = array(
                'ImportModelTestItem - String This field is required and neither a value nor a default value was specified.',
                'ImportModelTestItem - A full name value is required but missing.',
                'ImportModelTestItem - Last Name - Last Name cannot be blank.',
                'ImportModelTestItem - String - String cannot be blank.',
            );
            $this->assertEquals($compareMessages, unserialize(next($beansWithErrors)->serializedmessages));

            //Clear out data in table
            R::exec("delete from " . ImportModelTestItem::getTableName('ImportModelTestItem'));

            //Re-freeze if needed.
            if ($freezeWhenComplete)
            {
                RedBeanDatabase::freeze();
            }
        }

        /**
         * @depends testSimpleImportWithStringAndFullNameWhichAreRequiredAttributeOnImportTestModelItem
         */
        public function testSettingExplicitReadWriteModelPermissionsDuringImport()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $testModels = ImportModelTestItem::getAll();
            $this->assertEquals(0, count($testModels));

            //Add a read only user for import. Then all models should be readable by jim in addition to super.
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $explicitReadWriteModelPermissions->addReadOnlyPermitable(User::getByUsername('jim'));

            //Unfreeze since the test model is not part of the standard schema.
            $freezeWhenComplete = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freezeWhenComplete = true;
            }

            $testModels                        = ImportModelTestItem::getAll();
            $this->assertEquals(0, count($testModels));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'ImportModelTestItem';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());

            $this->assertEquals(13, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',        'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'DefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                'column_23' => array('attributeIndexOrDerivedType' => 'FullName',     'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                        'FullNameDefaultValueModelAttributeMappingRuleForm' =>
                                        array('defaultValue' => null))),
                                        );

            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 3)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             $explicitReadWriteModelPermissions);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $testModels = ImportModelTestItem::getAll();
            $this->assertEquals(3, count($testModels));
            $jim = User::getByUsername('jim');
            foreach ($testModels as $model)
            {
                $this->assertEquals(array(Permission::READ, Permission::NONE), $model->getExplicitActualPermissions ($jim));
            }

            //Clear out data in table
            R::exec("delete from " . ImportModelTestItem::getTableName('ImportModelTestItem'));

            //Now test with read/write permissions being set.
            $explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $explicitReadWriteModelPermissions->addReadWritePermitable(User::getByUsername('jim'));
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             $explicitReadWriteModelPermissions);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $testModels = ImportModelTestItem::getAll();
            $this->assertEquals(3, count($testModels));
            $jim = User::getByUsername('jim');
            foreach ($testModels as $model)
            {
                $this->assertEquals(array(Permission::READ_WRITE, Permission::NONE), $model->getExplicitActualPermissions ($jim));
            }

            //Re-freeze if needed.
            if ($freezeWhenComplete)
            {
                RedBeanDatabase::freeze();
            }
        }
    }
?>