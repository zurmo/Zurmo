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

        public function testSomething()
        {
            $importRules  = ImportRulesUtil::makeImportRulesByType('ImportModelTestItem');
            $page         = 'some page value??';
            $config       = array('pagination' => array('pageSize' => 2));
            $dataProvider->getPagination()->setCurrentPage($page);
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $importResultsUtil = new ImportResultsUtil($import);
            ImportUtil::importByDataProvider($dataProvider, $importRules, $mappingData, $importResultsUtil);
            $importResulstUtil->processStatusAndMessagesForEachRow();
        }
    }
?>