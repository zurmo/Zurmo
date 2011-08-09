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

    class ImportDataAnalyzer
    {
        protected $importRules;

        protected $dataProvider;

        protected $messagesData = array();

        protected $importInstructionsData = array();

        public function __construct($importRules, $dataProvider)
        {
            assert('$importRules instanceof ImportRules');
            assert('$dataProvider instanceof AnalyzerSupportedDataProvider');
            $this->importRules  = $importRules;
            $this->dataProvider = $dataProvider;
        }

        public function analyzeByColumnNameAndColumnMappingData($columnName, $columnMappingData)
        {
            assert('is_string($columnMappingData["attributeIndexOrDerivedType"]) ||
                    $columnMappingData["attributeIndexOrDerivedType"] == null');
            if($columnMappingData['attributeIndexOrDerivedType'] == null)
            {
                return;
            }
            $attributeImportRules = AttributeImportRulesFactory::
                                    makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    $this->importRules->getType(),
                                    $columnMappingData['attributeIndexOrDerivedType']);
            $modelClassName       = $attributeImportRules->getModelClassName();
            $attributeNameOrNames = $attributeImportRules->getModelAttributeNames();
            if(null != $attributeValueSanitizerUtilTypes = $attributeImportRules->getSanitizerUtilTypes())
            {
                assert('is_array($attributeValueSanitizerUtilTypes)');
                foreach($attributeValueSanitizerUtilTypes as $attributeValueSanitizerUtilType)
                {
                    $attributeValueSanitizerUtilClassName = $attributeValueSanitizerUtilType . 'SanitizerUtil';
                    if($attributeValueSanitizerUtilClassName::supportsDataAnalysis())
                    {
                        if($attributeValueSanitizerUtilClassName::supportsSqlAttributeValuesDataAnalysis())
                        {
                            $sqlAttributeValuesDataAnalyzer = $attributeValueSanitizerUtilClassName::
                                                              makeSqlAttributeValueDataAnalyzer($modelClassName,
                                                                                                $attributeNameOrNames);
                            assert('$sqlAttributeValuesDataAnalyzer != null');
                            $this->resolveRun($columnName, $columnMappingData,
                                              $attributeValueSanitizerUtilClassName,
                                              $sqlAttributeValuesDataAnalyzer);
                            $messages       = $sqlAttributeValuesDataAnalyzer->getMessages();
                            if($messages != null)
                            {
                                foreach($messages as $message)
                                {
                                    $moreAvailable     = $sqlAttributeValuesDataAnalyzer::supportsAdditionalResultInformation();
                                    $sanitizerUtilType = $attributeValueSanitizerUtilClassName::getType();
                                    $this->addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable);
                                }
                            }
                            $instructionsData = $sqlAttributeValuesDataAnalyzer->getInstructionsData();
                            if($instructionsData != null)
                            {
                                $this->addInstructionDataByColumnName($columnName, $instructionsData, $sanitizerUtilType);
                            }
                        }
                        elseif($attributeValueSanitizerUtilClassName::supportsBatchAttributeValuesDataAnalysis())
                        {
                            $batchAttributeValuesDataAnalyzer = $attributeValueSanitizerUtilClassName::
                                                                makeBatchAttributeValueDataAnalyzer($modelClassName,
                                                                                                    $attributeNameOrNames);
                            assert('$batchAttributeValuesDataAnalyzer != null');
                            $this->resolveRun($columnName, $columnMappingData,
                                                           $attributeValueSanitizerUtilClassName,
                                                           $batchAttributeValuesDataAnalyzer);
                            $messages                    = $batchAttributeValuesDataAnalyzer->getMessages();
                            if($messages != null)
                            {
                                foreach($messages as $message)
                                {
                                    $moreAvailable     = $batchAttributeValuesDataAnalyzer::
                                                         supportsAdditionalResultInformation();
                                    $sanitizerUtilType = $attributeValueSanitizerUtilClassName::getType();
                                    $this->addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable);
                                }
                            }
                            $instructionsData = $batchAttributeValuesDataAnalyzer->getInstructionsData();
                            if($instructionsData != null)
                            {
                                $this->addInstructionDataByColumnName($columnName, $instructionsData, $sanitizerUtilType);
                            }
                        }
                        else
                        {
                            throw new notImplementedException();
                        }
                    }
                }
            }
        }

        protected function resolveRun($columnName, $columnMappingData,
                                                   $attributeValueSanitizerUtilClassName, $dataAnalyzer)
        {
            assert('is_string($columnName)');
            assert('is_array($columnMappingData)');
            assert('is_subclass_of($attributeValueSanitizerUtilClassName, "SanitizerUtil")');
            assert('$dataAnalyzer instanceof BatchAttributeValueDataAnalyzer ||
                    $dataAnalyzer instanceof SqlAttributeValueDataAnalyzer');
            $classToEvaluate = new ReflectionClass(get_class($dataAnalyzer));
            if($classToEvaluate->implementsInterface('LinkedToMappingRuleDataAnalyzerInterface'))
            {
                $mappingRuleType = $attributeValueSanitizerUtilClassName::getLinkedMappingRuleType();
                assert('$mappingRuleType != null');
                $mappingRuleData = $columnMappingData['mappingRulesData'][$mappingRuleType];
                assert('$mappingRuleData != null');
                $dataAnalyzer->runAndMakeMessages($this->dataProvider, $columnName, $mappingRuleType, $mappingRuleData);
            }
            else
            {
                $dataAnalyzer->runAndMakeMessages($this->dataProvider, $columnName);
            }
        }

        public function addMessageDataByColumnName($columnName, $message, $sanitizerUtilType, $moreAvailable)
        {
            assert('is_string($columnName)');
            assert('is_string($message)');
            assert('is_string($sanitizerUtilType)');
            assert('is_bool($moreAvailable)');
            $this->messagesData[$columnName][] = array('message'           => $message,
                                                  'sanitizerUtilType' => $sanitizerUtilType,
                                                  'moreAvailable'     => $moreAvailable);
        }

        public function addInstructionDataByColumnName($columnName, $instructionData, $sanitizerUtilType)
        {
            assert('is_string($columnName)');
            assert('is_string($instructionData) || is_array($instructionData)');
            assert('is_string($sanitizerUtilType)');
            $this->importInstructionsData[$columnName][$sanitizerUtilType] = $instructionData;
        }

        public function getMessagesData()
        {
            return $this->messagesData;
        }

        public function getImportInstructionsData()
        {
            return $this->importInstructionsData;
        }
    }
?>