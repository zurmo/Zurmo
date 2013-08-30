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

    class ContactsImportDataAnalyzerTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
            ContactsModule::loadStartingData();
        }

        public function testImportDataAnalysisResults()
        {
            $super                             = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $import                            = new Import();
            $serializedData['importRulesType'] = 'Contacts';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(),
                                                  Yii::getPathOfAlias('application.modules.contacts.tests.unit.files'));
            $mappingData = array(
                'column_0'  => array('attributeIndexOrDerivedType' => 'ContactState',
                                         'type' => 'importColumn',
                                         'mappingRulesData' => array(
                                         'DefaultContactStateIdMappingRuleForm' => array('defaultStateId' => null))),
            );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('Contacts');
            $config       = array('pagination' => array('pageSize' => 15));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData, array('column_0'));
            $importDataAnalyzer->analyzePage();
            $data = $dataProvider->getData();
            $this->assertEquals(13, count($data));

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is invalid.';
            $this->assertEquals($compareData, unserialize($data[0]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[0]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is invalid.';
            $this->assertEquals($compareData, unserialize($data[1]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[1]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is invalid.';
            $this->assertEquals($compareData, unserialize($data[2]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[2]->analysisStatus);

            $this->assertNull($data[3]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[3]->analysisStatus);

            $this->assertNull($data[4]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[4]->analysisStatus);

            $this->assertNull($data[5]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[5]->analysisStatus);

            $this->assertNull($data[6]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[6]->analysisStatus);

            $this->assertNull($data[7]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[7]->analysisStatus);

            $this->assertNull($data[8]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[8]->analysisStatus);

            $this->assertNull($data[9]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[9]->analysisStatus);

            $this->assertNull($data[10]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[10]->analysisStatus);

            $this->assertNull($data[11]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[11]->analysisStatus);

            $this->assertNull($data[12]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[12]->analysisStatus);
        }
    }
?>
