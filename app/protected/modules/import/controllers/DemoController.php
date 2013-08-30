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

    Yii::import('application.modules.import.controllers.DefaultController', true);
    class ImportDemoController extends ImportDefaultController
    {
        public function actionCreateDemoImportForAnalysis($firstRowIsHeaderRow = true)
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $import                            = new Import();
            $serializedData['importRulesType'] = 'Accounts';
            $mappingData = array(
                'column_0' => array('attributeIndexOrDerivedType' => 'name', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_1' => array('attributeIndexOrDerivedType' => 'officePhone', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_2' => array('attributeIndexOrDerivedType' => 'officeFax', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_3' => array('attributeIndexOrDerivedType' => 'employees', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_4' => array('attributeIndexOrDerivedType' => 'annualRevenue', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_5' => array('attributeIndexOrDerivedType' => 'description', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_6' => array('attributeIndexOrDerivedType' => 'website', 'type' => 'importColumn',
                    'mappingRulesData' => array(
                        'DefaultValueModelAttributeMappingRuleForm' =>
                        array('defaultValue' => null))),
                'column_7' => array('attributeIndexOrDerivedType' => null, 'type' => 'importColumn',
                    'mappingRulesData' => array()),
            );
            $serializedData['mappingData']        = $mappingData;
            $serializedData['rowColumnDelimiter'] = ','; // Not Coding Standard
            $serializedData['rowColumnEnclosure'] = '"';
            $serializedData['firstRowIsHeaderRow'] = $firstRowIsHeaderRow;
            $import->serializedData               = serialize($serializedData);
            $saved = $import->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $this->createImportTempTable(8, $import->getTempTableName());

            //Make header row
            if ($firstRowIsHeaderRow)
            {
                $newBean = R::dispense($import->getTempTableName());
                $newBean->column_0 = 'Header #1';
                $newBean->column_1 = 'Header #2';
                $newBean->column_2 = 'Header #3';
                $newBean->column_3 = 'Header #4';
                $newBean->column_4 = 'Header #5';
                $newBean->column_5 = 'Header #6';
                $newBean->column_6 = 'Header #7';
                $newBean->column_7 = 'Header #8';
                R::store($newBean);
            }

            //Make data rows that are clean
            for ($i = 0; $i < 3; $i++)
            {
                $newBean = R::dispense($import->getTempTableName());
                $newBean->column_0 = 'aa1' . $i;
                $newBean->column_1 = 'aa2' . $i;
                $newBean->column_2 = 'aa3' . $i;
                $newBean->column_3 = 'aa4' . $i;
                $newBean->column_4 = 'aa5' . $i;
                $newBean->column_5 = 'aa6' . $i;
                $newBean->column_6 = 'aa7' . $i;
                $newBean->column_7 = 'aa8' . $i;
                $newBean->analysisStatus = ImportDataAnalyzer::STATUS_CLEAN;
                $analysisData = array();
                $analysisData['column_0']   = array();
                $analysisData['column_0'][] = 'a test message 1';
                $analysisData['column_0'][] = 'a test message 2';
                $analysisData['column_2']   = array();
                $analysisData['column_2'][] = 'a test message 1';
                $analysisData['column_2'][] = 'a test message 2';
                $newBean->serializedAnalysisMessages = serialize($analysisData);
                R::store($newBean);
            }

            //Make data rows that have a warning
            for ($i = 0; $i < 3; $i++)
            {
                $newBean = R::dispense($import->getTempTableName());
                $newBean->column_0 = 'ba1' . $i;
                $newBean->column_1 = 'ba2' . $i;
                $newBean->column_2 = 'ba3' . $i;
                $newBean->column_3 = 'ba4' . $i;
                $newBean->column_4 = 'ba5' . $i;
                $newBean->column_5 = 'ba6' . $i;
                $newBean->column_6 = 'ba7' . $i;
                $newBean->column_7 = 'ba8' . $i;
                $newBean->analysisStatus = ImportDataAnalyzer::STATUS_WARN;
                $analysisData = array();
                $analysisData['column_0']   = array();
                $analysisData['column_0'][] = 'a test message 1';
                $analysisData['column_0'][] = 'a test message 2';
                $analysisData['column_2']   = array();
                $analysisData['column_2'][] = 'a test message 1';
                $analysisData['column_2'][] = 'a test message 2';
                $newBean->serializedAnalysisMessages = serialize($analysisData);
                R::store($newBean);
            }

            //Make data rows that are skipped
            for ($i = 0; $i < 10; $i++)
            {
                $newBean = R::dispense($import->getTempTableName());
                $newBean->column_0 = 'ca1' . $i;
                $newBean->column_1 = 'ca2' . $i;
                $newBean->column_2 = 'ca3' . $i;
                $newBean->column_3 = 'ca4' . $i;
                $newBean->column_4 = 'ca5' . $i;
                $newBean->column_5 = 'ca6' . $i;
                $newBean->column_6 = 'ca7' . $i;
                $newBean->column_7 = 'ca8' . $i;
                $newBean->analysisStatus = ImportDataAnalyzer::STATUS_SKIP;
                $analysisData = array();
                $analysisData['column_0']   = array();
                $analysisData['column_0'][] = 'a test message 1';
                $analysisData['column_0'][] = 'a test message 2';
                $analysisData['column_2']   = array();
                $analysisData['column_2'][] = 'a test message 1';
                $analysisData['column_2'][] = 'a test message 2';
                $newBean->serializedAnalysisMessages = serialize($analysisData);
                R::store($newBean);
            }

            R::store($newBean);
            echo 'the import id is: ' . $import->id;
        }

        protected function createImportTempTable($columnCount, $tableName)
        {
            $freezeWhenComplete = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freezeWhenComplete = true;
            }
            $newBean = R::dispense($tableName);
            for ($i = 0; $i < $columnCount; $i++)
            {
                $columnName = 'column_' . $i;
                $newBean->{$columnName} = str_repeat(' ', 50);
                $columns[] = $columnName;
            }
            R::store($newBean);
            R::trash($newBean);
            R::wipe($tableName);
            ImportDatabaseUtil::optimizeTableNonImportColumns($tableName);
            R::wipe($tableName);
            if ($freezeWhenComplete)
            {
                RedBeanDatabase::freeze();
            }
        }
    }
?>
