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

    class ImportTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testImportColumnDataSanitizationAnalyzerResults()
        {
            $import = new Import();
            $import->modelImportRulesType = 'xx';
            $this->assertTrue($import->save());
            $this->createTempTableByFileNameAndTableName('importAnalyzerTest.csv',
                                                         ImportUtil::getDataTableNameByImport($import));
            $mappingData = array(
                'column_0' => array('attributeNameOrDerivedType' => 'xx'),
                'column_1' => array('attributeNameOrDerivedType' => 'xx'),
                'column_2' => array('attributeNameOrDerivedType' => 'xx'),
            );
            ImportUtil::SetMappingDataToImportAndSave($mappingData, $import);
            $modelImportRules = ImportUtil::makeModelImportRulesByImportModel($import);
            $dataProvider     = ImportDataProviderUtil::makeDataProviderByImportModel($import);
            $importColumnDataSanitizationAnalyzer = new ImportColumnDataSanitizationAnalyzer($modelImportRules, $dataProvider);
            foreach ($mappingData as $importColumnName => $columnMappingData)
            {
                $importColumnDataSanitizationAnalyzer->analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData);
            }
            $this->assertTrue($importColumnDataSanitizationAnalyzer->isThereAnyNonCleanData());
            $this->assertEquals(1, $importColumnDataSanitizationAnalyzer->getWarningMessagesCount());
            $this->assertEquals(1, $importColumnDataSanitizationAnalyzer->getRequiredFixesCount());
            $this->assertEquals(1, $importColumnDataSanitizationAnalyzer->getOptionalFixesCount());
            $nonCleanDataItems = $importColumnDataSanitizationAnalyzer->getNonCleanDataItems();
            $compareNonCleanDataItems = array();
            $this->assertEquals($compareNonCleanDataItems, $nonCleanDataItems);
//!!!  the goal of the test is to make sure this output completely matches the expectation.
                    //what is present?
                    //just some warning messages ( like truncate)
                    //something like dropdowns requiring a fix
                    //something like owner notice that removal will occur, or optional if empty owner then which owner to use?


            //Now test that the clean data information is correct
            $this->assertTrue($importColumnDataSanitizationAnalyzer->isThereCleanData());
            $cleanDataItems = $importColumnDataSanitizationAnalyzer->getCleanDataItems();
            $compareCleanDataItems = array();
            $this->assertEquals($compareCleanDataItems, $cleanDataItems);
        }

        /**
         * @depends testImportColumnDataSanitizationAnalyzerResults
         */
        public function testOptionalAndRequiredFixInputData()
        {
            //setting the settings for this by form.
            //todo: set form from serialized data, if you come back via prev button? can you come back here or is it too late?
            //todo: after u are done here is there like a final page to show you everything going down.
        }

        /**
         * @depends testOptionalAndRequiredFixInputData
         */
        public function testImportAndCreateModels()
        {
            //paging loop?

            //header vs. no header row?
        }

        /**
         * @depends testImportAndCreateModels
         */
        public function testUndoImport()
        {
            //test that you have joining import information in an import.
            //test undo import.
        }
    }
?>
