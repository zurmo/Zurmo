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
     * Helper class for working with import.
     */
    class ImportUtil
    {
        /**
         * @param object $import
         * @param array $messagesData
         * @param boolean $merge - if true, then merge the $messagesData with existing data, otherwise overwrite
         * existing data.
         */
        public static function setDataAnalyzerMessagesDataToImport($import, $messagesData, $merge = false)
        {
            assert('$import instanceof Import');
            assert('is_array($messagesData) || $messagesData == null');
            $serializedData = unserialize($import->serializedData);
            if($merge && isset($serializedData['dataAnalyzerMessagesData']))
            {
                $serializedData['dataAnalyzerMessagesData'] = array_merge($serializedData['dataAnalyzerMessagesData'],
                                                                          $messagesData);
            }
            else
            {
                $serializedData['dataAnalyzerMessagesData'] = $messagesData;
            }
            $import->serializedData = serialize($serializedData);
        }


        public static function importByDataProvider(ImportDataProvider $dataProvider, ImportRules $importRules,
                                                    $mappingData, ImportResultsUtil $importResultsUtil)
        {
            $data = $dataProvider->getData();
            foreach($data as $rowData)
            {
                assert('$rowData["id"] != null');
                $importRowDataResultsUtil = new ImportRowDataResultsUtil($rowData['id']);
                $this->importByImportRulesRowData($importRules, $rowData, $mappingData, $importRowDataResultsUtil);
                $importResultsUtil->addRowDataResults($importRowDataResultsUtil);
            }
        }


        public static function importByImportRulesRowData(ImportRules $importRules, $rowData, $mappingData,
                                                          ImportRowDataResultsUtil $importRowDataResultsUtil)
        {
            assert('is_array($rowData)');
            assert('is_array($mappingData)');
            $makeNewModel     = true;
            $modelClassName   = $importRules->getModelClassName();
            $externalSystemId = null;
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();

            //Process the 'id' column first if available.
            if(false !== $idColumnName = static::getIdColumnNameByMappingData($mappingData))
            {
                $columnMappingData = $mappingData[$idColumnName];
                $attributeImportRules = AttributeImportRulesFactory::
                                        makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                        $importRules::getType(), $columnMappingData['attributeIndexOrDerivedType']);
                $valueReadyToSanitize = static::
                                        resolveValueToSanitizeByRowDataAndColumnType($rowData,
                                                                                     $columnMappingData['columnType']);
                $attributeValueData   = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                             $columnMappingData,
                                                                                             $importSanitizeResultsUtil);
                assert('count($attributeValueData) == 0 || count($attributeValueData) == 1');
                if($attributeValueData['id'] != null)
                {
                    $model        = $modelClassName::getById($attributeValueData['id']);
                    $makeNewModel = false;
                }
                elseif($attributeValueData[ExternalSystemIdSuppportedSanitizerUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME] != null)
                {
                    $externalSystemId = $attributeValueData
                                        [ExternalSystemIdSuppportedSanitizerUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME];
                }
            }
            if($makeNewModel)
            {
                $model = new $modelClassName(false);
                $model->setScenario('importModel');
            }

            //Process the rest of the mapped colummns.
            foreach($mappingData as $columnName => $columnMappingData)
            {
                assert('$columnMappingData["columnType"] == "importColumn" ||
                        $columnMappingData["columnType"] == "extraColumn"');
                if($columnMappingData['attributeIndexOrDerivedType'] != null)
                {
                    $attributeImportRules = AttributeImportRulesFactory::
                                            makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $importRules::getType(), $columnMappingData['attributeIndexOrDerivedType']);
                    $valueReadyToSanitize = static::
                                            resolveValueToSanitizeByRowDataAndColumnType($rowData,
                                                                                         $columnMappingData['columnType']);

                    if($attributeImportRules instanceof NonDerivedAttributeImportRules &&
                       $attributeImportRules->getModelClassName() != $modelClassName)
                    {
                        static::resolveModelForAttributeIndexWithMultipleNonDerivedAttributes($model,
                                                                                              $attributeImportRules,
                                                                                              $columnMappingData,
                                                                                              $importSanitizeResultsUtil);
                    }
                    elseif($attributeImportRules instanceof DerivedAttributeSupportedImportRules)
                    {
                        static::resolveModelForModelDerivedAttribute(                      $model,
                                                                                           $importRules::getType(),
                                                                                           $attributeImportRules,
                                                                                           $columnMappingData,
                                                                                           $importSanitizeResultsUtil);
                    }
                    else
                    {
                        static::
                        resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute($model,
                                                                                           $attributeImportRules,
                                                                                           $columnMappingData,
                                                                                           $importSanitizeResultsUtil);
                    }
                }
            }

            $validated = $model->validate();
            if($validated && $importSanitizeResultsUtil->shouldSaveModel())
            {
                $saved = $model->save();
                if($saved)
                {
                    if($externalSystemId!= null)
                    {
                        ExternalSystemIdUtil::updateByModel($model);
                    }
                    $importRowDataResultsUtil->addMessage(Yii::t('Default', 'AccountsModuleSingularLabel saved correctly.'));
                    if($makeNewModel)
                    {
                        $importRowDataResultsUtil->setStatusToCreated();
                    }
                    else
                    {
                        $importRowDataResultsUtil->setStatusToUpdated();
                    }
                }
                else
                {
                    $importRowDataResultsUtil->addMessage('something to indicate failure');
                    $importRowDataResultsUtil->setStatusToError();
                }
            }
            else
            {
                if(!$importSanitizeResultsUtil->shouldSaveModel())
                {
                    $importRowDataResultsUtil->addMessages($importSanitizeResultsUtil->getMessages());
                }
                $messages = RedBeanModelErrorsToMessagesUtil::makeMessagesByErrors($model->getErrors());
                $importRowDataResultsUtil->addMessages($messages);
                $importRowDataResultsUtil->setStatusToError();
            }
        }

        protected static function getIdColumnNameByMappingData($mappingData)
        {
            assert('is_array($mappingData)');
            $idColumnName = null;
            $valueFound   = false;
            foreach($mappingData as $columnName => $columnMappingData)
            {
                if($columnMappingData['attributeIndexOrDerivedType'] == 'id')
                {
                    if($valueFound || $columnMappingData['columnType'] != 'importColumn')
                    {
                        throw new NotSupportedException();
                    }
                    $idColumnName = $columnName;
                    $valueFound   = true;
                }
            }
            if($idColumnName != null)
            {
                return $idColumnName;
            }
            return false;
        }


        protected static function resolveModelForAttributeIndexWithMultipleNonDerivedAttributes(
                                  RedBeanModel $model,
                                  AttributeImportRules $attributeImportRules,
                                  $columnMappingData,
                                  ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('is_array($columnMappingData)');
            if($attributeImportRules->getModelClassName() == null)
            {
                throw new NotSupportedException();
            }
            $attributeValueData     = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                 $columnMappingData,
                                                                                 $importSanitizeResultsUtil);
            $attributeName          = AttributeImportRulesFactory::
                                      getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType(
                                      $columnMappingData['attributeIndexOrDerivedType']);
            $relationModelClassName = $attributeImportRules->getModelClassName();
            if($model->$attributeName == null)
            {
                $model->$attributeName = new $relationModelClassName();
            }
            elseif(!$model->$attributeName instanceof $relationModelClassName)
            {
                throw new NotSupportedException();
            }
            foreach($attributeValueData as $relationAttributeName => $value)
            {
                assert('$model->$attributeName->isAttribute($relationAttributeName)');
                static::resolveReadOnlyAndSetValueToAttribute($model->$attributeName, $relationAttributeName, $value);
            }
        }

        protected static function resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute(
                                  RedBeanModel $model,
                                  AttributeImportRules $attributeImportRules,
                                  $columnMappingData,
                                  ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('!$attributeImportRules instanceof DerivedAttributeSupportedImportRules');
            assert('is_array($columnMappingData)');
            $attributeValueData   = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                             $columnMappingData,
                                                                                             $importSanitizeResultsUtil);
            foreach($attributeValueData as $attributeName => $value)
            {
                assert('$model->isAttribute($attributeName)');
                static::resolveReadOnlyAndSetValueToAttribute($model, $attributeName, $value);
            }
        }

        protected static function resolveModelForModelDerivedAttribute(
                                  RedBeanModel $model,
                                  $importRulesType,
                                  AttributeImportRules $attributeImportRules,
                                  $columnMappingData,
                                  ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('is_string($importRulesType)');
            assert('$attributeImportRules instanceof DerivedAttributeSupportedImportRules');
            assert('is_array($columnMappingData)');
            $attributeValueData   = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                             $columnMappingData,
                                                                                             $importSanitizeResultsUtil);
            assert('count($attributeValueData) == 1');
            assert('isset($attributeValueData["getDerivedAttributeName()"])');
            if($attributeValueData[$attributeImportRules::getDerivedAttributeName()] != null)
            {
                $actualAttributeName = $importRulesType::getActualModelAttributeNameForDerivedAttribute();
                $actualModel         = $attributeValueData[$attributeImportRules::getDerivedAttributeName()];
                if(!$model->$actualAttributeName->contains($actualModel))
                {
                    $model->$actualAttributeName->add($actualModel);
                }
            }
        }

        protected static function resolveReadOnlyAndSetValueToAttribute(RedBeanModel $model, $attributeName, $value)
        {
            assert('is_string($attributeName)');
            if(!$model->isAttributeReadOnly() || ($model->isAttributeReadOnly()
                   && $model->isAllowedToSetReadOnlyAttribute($attributeName)))
            {
                $model->$attributeName = $value;
            }
        }

        protected static function resolveValueToSanitizeByRowDataAndColumnType($rowData, $columnName, $columnType)
        {
            assert('is_array($rowData)');
            assert('is_string($columnName)');
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            if($columnType == 'importColumn')
            {
                return static::resolveValueToSanitize($value);
            }
            else
            {
                return null;
            }
        }

        protected static function resolveValueToSanitize($value)
        {
            if($value == '' || $value == null)
            {
               return null;
            }
            else
            {
                return trim($value);
            }
        }
    }
?>