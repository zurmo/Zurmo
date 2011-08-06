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
            SecurityTestHelper::createSuperAdmin();
        }

        public function testImportDataAnalysisResults()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $import                            = new Import();
            $serializedData['importRulesType'] = 'ImportModelTestItem';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());
            ImportTestHelper::createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName());
            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'string',   'type' => 'importColumn'),
                'column_1' => array('attributeIndexOrDerivedType' => 'phone',    'type' => 'importColumn'),
                'column_2' => array('attributeIndexOrDerivedType' => 'float',    'type' => 'importColumn'),
                'column_3' => array('attributeIndexOrDerivedType' => 'boolean',  'type' => 'importColumn'),
                'column_4' => array('attributeIndexOrDerivedType' => 'date', 	 'type' => 'importColumn',
                                    'mappingRulesData' => array('ValueFormat' => array('format' => 'MM-dd-yyyy'))),
                'column_5' => array('attributeIndexOrDerivedType' => 'dateTime', 'type' => 'importColumn',
                                    'mappingRulesData' => array('ValueFormat' => array('format' => 'MM-dd-yyyy hh:mm'))),
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
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_4');
            $compareMessage = '2 value(s) have invalid check box values. These values will be set to false upon import.';
            $this->assertEquals($compareMessage, $message);

            //Test datetime sanitization by batch.
            $dataAnalyzer = new DateBatchAttributeValueDataAnalyzer('ImportModelTestItem', array('datetime'));
            $message = $dataAnalyzer->runAndGetMessage($dataProvider, 'column_5');
            $compareMessage = '2 value(s) have invalid check box values. These values will be set to false upon import.';
            $this->assertEquals($compareMessage, $message);
        }
    }
?>
