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

    /**
     *
     */
    class ImportDataAnalysisSequentialProcess extends SequentialProcess
    {
        protected $import;

        protected $mappingData;

        protected $importRules;

        protected $dataProvider;

        protected $sanitizableColumnNames;

        public function __construct(Import $import, $dataProvider)
        {
            assert('$dataProvider instanceof AnalyzerSupportedDataProvider');
            $unserializedData             = unserialize($import->serializedData);
            $this->import                 = $import;
            $this->mappingData            = $unserializedData['mappingData'];
            $this->importRules            = ImportRulesUtil::makeImportRulesByType($unserializedData['importRulesType']);
            $this->dataProvider           = $dataProvider;
            $this->sanitizableColumnNames = $this->resolveSanitizableColumnNames($this->mappingData);
        }

        public function getAllStepsMessage()
        {
            return Yii::t('Default', 'Analyzing the import data...');
        }

        protected function steps()
        {
            return array('processColumns');
        }

        protected function stepMessages()
        {
            return array('processColumns' => Yii::t('Default', 'Processing'));
        }

        protected function processColumns($params)
        {
            $completionPosition = 1;
            if(!isset($params["columnNameToProcess"]))
            {
                $params["columnNameToProcess"] = $this->getNextMappedColumnName($this->mappingData);
            }
            else
            {
                assert('is_string($params["columnNameToProcess"])');
            }
            $completionPosition = array_search($params["columnNameToProcess"], $this->sanitizableColumnNames) + 1;
            if($completionPosition != count($this->sanitizableColumnNames))
            {
                $completionPosition ++;
            }
            $this->subSequenceCompletionPercentage = ($completionPosition / count($this->sanitizableColumnNames)) * 100;
            //Run data analyzer
            if($this->mappingData[$params["columnNameToProcess"]]['attributeIndexOrDerivedType'] == null)
            {
                throw new NotSupportedException();
            }
            $importDataAnalyzer              = new ImportDataAnalyzer($this->importRules, $this->dataProvider);
            $importDataAnalyzer->analyzeByColumnNameAndColumnMappingData($params["columnNameToProcess"],
                                                                         $this->mappingData[$params["columnNameToProcess"]]);
            $messagesData                    = $importDataAnalyzer->getMessagesData();
            $importInstructionsData          = $importDataAnalyzer->getImportInstructionsData();
            $unserializedData                = unserialize($this->import->serializedData);
            $unserializedData['mappingData'] = ImportMappingUtil::
                                               resolveImportInstructionsDataIntoMappingData(
                                               $this->mappingData, $importInstructionsData);
            $this->import->serializedData    = serialize($unserializedData);
            ImportUtil::setDataAnalyzerMessagesDataToImport($this->import, $messagesData, true);
            $saved = $this->import->save();
            assert('$saved');
            $nextColumnName = $this->getNextMappedColumnName($this->mappingData, $params['columnNameToProcess']);
            if($nextColumnName == null)
            {
                $this->nextStep    = null;
                $this->nextMessage = null;
                $this->complete    = true;
                return null;
            }
            else
            {
                $params['columnNameToProcess'] = $nextColumnName;
                $this->nextStep = 'processColumns';
                $this->setNextMessageByStep($this->nextStep);
                $attributeImportRules = AttributeImportRulesFactory::
                                        makeByImportRulesTypeAndAttributeIndexOrDerivedType($this->importRules->getType(),
                                        $this->mappingData[$params["columnNameToProcess"]]['attributeIndexOrDerivedType']);
                $this->nextMessage .= ' ' . $attributeImportRules->getDisplayLabel();
                return $params;
            }
        }

        protected function getNextMappedColumnName($mappingData, $currentColumnName = null)
        {
            assert('is_array($mappingData)');
            assert('$currentColumnName == null || is_string($currentColumnName)');
            if($currentColumnName == null)
            {
                $currentIndexPassed = true;
            }
            else
            {
                $currentIndexPassed = false;
            }
            foreach($mappingData as $columnName => $notUsed)
            {
                if($currentIndexPassed && $mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
                {
                    return $columnName;
                }
                if(!$currentIndexPassed && $columnName == $currentColumnName)
                {
                    $currentIndexPassed = true;
                }
            }
            return null;
        }

        protected function resolveSanitizableColumnNames($mappingData)
        {
            assert('is_array($mappingData)');
            $sanitizableColumnNames = array();
            foreach($mappingData as $columnName => $notUsed)
            {
                if($mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
                {
                    $sanitizableColumnNames[] = $columnName;
                }
            }
            return $sanitizableColumnNames;
        }
    }
?>