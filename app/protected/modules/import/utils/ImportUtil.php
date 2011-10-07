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
            if ($merge && isset($serializedData['dataAnalyzerMessagesData']))
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

        /**
         * Given a data provider, call getData and for each row, attempt to import the data.
         * @param object $dataProvider
         * @param object $importRules
         * @param array $mappingData
         * @param object $importResultsUtil
         */
        public static function importByDataProvider(ImportDataProvider $dataProvider,
                                                    ImportRules $importRules,
                                                    $mappingData,
                                                    ImportResultsUtil $importResultsUtil,
                                                    ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $data = $dataProvider->getData();
            foreach ($data as $rowBean)
            {
                assert('$rowBean->id != null');
                $importRowDataResultsUtil = new ImportRowDataResultsUtil((int)$rowBean->id);
                //todo: eventually handle security exceptions in a more graceful way instead of relying on a try/catch
                //but explicity checking for security rights/permissions.
                try
                {
                    static::importByImportRulesRowData($importRules, $rowBean, $mappingData,
                                                       $importRowDataResultsUtil, $explicitReadWriteModelPermissions);
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $importRowDataResultsUtil->addMessage(Yii::t('Default', 'You do not have permission to update this record and/or its related record.'));
                    $importRowDataResultsUtil->setStatusToError();
                }
                $importResultsUtil->addRowDataResults($importRowDataResultsUtil);
            }
        }

        /**
         * Given a row of data, resolve each value of the row for import and either create or update an existing model.
         * @param object $importRules
         * @param array $rowData
         * @param array $mappingData
         * @param object $importRowDataResultsUtil
         */
        public static function importByImportRulesRowData(ImportRules $importRules,
                                                          $rowBean,
                                                          $mappingData,
                                                          ImportRowDataResultsUtil $importRowDataResultsUtil,
                                                          ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            assert('$rowBean instanceof RedBean_OODBBean');
            assert('is_array($mappingData)');
            $makeNewModel              = true;
            $modelClassName            = $importRules->getModelClassName();
            $externalSystemId          = null;
            $importSanitizeResultsUtil = new ImportSanitizeResultsUtil();
            $afterSaveActionsData      = array();

            //Process the 'id' column first if available.
            if (false !== $idColumnName = static::getIdColumnNameByMappingData($mappingData))
            {
                $columnMappingData = $mappingData[$idColumnName];
                $attributeImportRules = AttributeImportRulesFactory::
                                        makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                        $importRules::getType(), $columnMappingData['attributeIndexOrDerivedType']);
                $valueReadyToSanitize = static::
                                        resolveValueToSanitizeByValueAndColumnType($rowBean->$idColumnName,
                                                                                   $columnMappingData['columnType']);
                $attributeValueData   = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                             $columnMappingData,
                                                                                             $importSanitizeResultsUtil);
                assert('count($attributeValueData) == 0 || count($attributeValueData) == 1');
                if ($attributeValueData['id'] != null)
                {
                    $model        = $modelClassName::getById($attributeValueData['id']);
                    $makeNewModel = false;
                }
                elseif ($attributeValueData[ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME] != null)
                {
                    $externalSystemId = $attributeValueData
                                        [ExternalSystemIdUtil::EXTERNAL_SYSTEM_ID_COLUMN_NAME];
                }
            }
            if ($makeNewModel)
            {
                $model = new $modelClassName();
                $model->setScenario('importModel');
            }

            //Process the rest of the mapped colummns.
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                assert('$columnMappingData["type"] == "importColumn" ||
                        $columnMappingData["type"] == "extraColumn"');
                if ($columnMappingData['attributeIndexOrDerivedType'] != null)
                {
                    $attributeImportRules = AttributeImportRulesFactory::
                                            makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                            $importRules::getType(), $columnMappingData['attributeIndexOrDerivedType']);
                    $valueReadyToSanitize = static::
                                            resolveValueToSanitizeByValueAndColumnType($rowBean->$columnName,
                                                                                       $columnMappingData['type']);

                    if ($attributeImportRules instanceof NonDerivedAttributeImportRules &&
                       $attributeImportRules->getModelClassName() != $modelClassName)
                    {
                        static::resolveModelForAttributeIndexWithMultipleNonDerivedAttributes($model,
                                                                                              $attributeImportRules,
                                                                                              $valueReadyToSanitize,
                                                                                              $columnMappingData,
                                                                                              $importSanitizeResultsUtil);
                    }
                    elseif ($attributeImportRules instanceof DerivedAttributeSupportedImportRules)
                    {
                        static::resolveModelForModelDerivedAttribute(                      $model,
                                                                                           $importRules::getType(),
                                                                                           $attributeImportRules,
                                                                                           $valueReadyToSanitize,
                                                                                           $columnMappingData,
                                                                                           $importSanitizeResultsUtil);
                    }
                    elseif ($attributeImportRules instanceof AfterSaveSupportedDerivedAttributeImportRules)
                    {
                    //aftersave tasks.
//$array[] = array(AttributeImportRules, attributeValuesData
                    }
                    else
                    {
                        static::
                        resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute($model,
                                                                                           $attributeImportRules,
                                                                                           $valueReadyToSanitize,
                                                                                           $columnMappingData,
                                                                                           $importSanitizeResultsUtil);
                    }
                }
            }

            $validated = $model->validate();
            if ($validated && $importSanitizeResultsUtil->shouldSaveModel())
            {
                $saved = $model->save();
                if ($saved)
                {
                    static::processAfterSaveActions($afterSaveActionsData, $model);
                    if ($externalSystemId!= null)
                    {
                        ExternalSystemIdUtil::updateByModel($model, $externalSystemId);
                    }
                    $importRowDataResultsUtil->addMessage(Yii::t('Default', 'Record saved correctly.'));
                    if ($makeNewModel)
                    {
                        try
                        {
                            ExplicitReadWriteModelPermissionsUtil::
                            resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                            $importRowDataResultsUtil->setStatusToCreated();
                        }
                        catch (AccessDeniedSecurityException $e)
                        {
                            $importRowDataResultsUtil->addMessage('The record saved, but you do not have permissions '.
                            'to set the security the way you did. As a result this record has been removed.');
                            $importRowDataResultsUtil->setStatusToError();
                        }
                    }
                    else
                    {
                        $importRowDataResultsUtil->setStatusToUpdated();
                    }
                }
                else
                {
                    $importRowDataResultsUtil->addMessage('The record failed to save. Reason unknown.');
                    $importRowDataResultsUtil->setStatusToError();
                }
            }
            else
            {
                if (!$importSanitizeResultsUtil->shouldSaveModel())
                {
                    $importRowDataResultsUtil->addMessages($importSanitizeResultsUtil->getMessages());
                }
                $messages = RedBeanModelErrorsToMessagesUtil::makeMessagesByModel($model);
                $importRowDataResultsUtil->addMessages($messages);
                $importRowDataResultsUtil->setStatusToError();
            }
        }

        protected static function processAfterSaveActions($afterSaveActionsData, RedBeanModel $model)
        {
            assert('is_array($afterSaveActionsData');
            foreach($afterSaveActionsData as $attributeImportRules => $attributeValueData)
            {
                $attributeImportRules::someMethodAndPass($model, $attributeValueData);
            }
        }

        protected static function getIdColumnNameByMappingData($mappingData)
        {
            assert('is_array($mappingData)');
            $idColumnName = null;
            $valueFound   = false;
            foreach ($mappingData as $columnName => $columnMappingData)
            {
                if ($columnMappingData['attributeIndexOrDerivedType'] == 'id')
                {
                    if ($valueFound || $columnMappingData['type'] != 'importColumn')
                    {
                        throw new NotSupportedException();
                    }
                    $idColumnName = $columnName;
                    $valueFound   = true;
                }
            }
            if ($idColumnName != null)
            {
                return $idColumnName;
            }
            return false;
        }

        protected static function resolveModelForAttributeIndexWithMultipleNonDerivedAttributes(
                                  RedBeanModel $model,
                                  AttributeImportRules $attributeImportRules,
                                  $valueReadyToSanitize,
                                  $columnMappingData,
                                  ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('is_array($columnMappingData)');
            if ($attributeImportRules->getModelClassName() == null)
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
            if ($model->$attributeName == null)
            {
                $model->$attributeName = new $relationModelClassName();
            }
            elseif (!$model->$attributeName instanceof $relationModelClassName)
            {
                throw new NotSupportedException();
            }
            foreach ($attributeValueData as $relationAttributeName => $value)
            {
                assert('$model->$attributeName->isAttribute($relationAttributeName)');
                static::resolveReadOnlyAndSetValueToAttribute($model->$attributeName, $relationAttributeName, $value);
            }
        }

        protected static function resolveModelForAttributeIndexWithSingleAttributeOrDerivedAttribute(
                                  RedBeanModel $model,
                                  AttributeImportRules $attributeImportRules,
                                  $valueReadyToSanitize,
                                  $columnMappingData,
                                  ImportSanitizeResultsUtil $importSanitizeResultsUtil)
        {
            assert('!$attributeImportRules instanceof DerivedAttributeSupportedImportRules');
            assert('is_array($columnMappingData)');
            $attributeValueData   = $attributeImportRules->resolveValueForImport($valueReadyToSanitize,
                                                                                 $columnMappingData,
                                                                                 $importSanitizeResultsUtil);
            foreach ($attributeValueData as $attributeName => $value)
            {
                assert('$model->isAttribute($attributeName)');
                static::resolveReadOnlyAndSetValueToAttribute($model, $attributeName, $value);
            }
        }

        protected static function resolveModelForModelDerivedAttribute(
                                  RedBeanModel $model,
                                  $importRulesType,
                                  AttributeImportRules $attributeImportRules,
                                  $valueReadyToSanitize,
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
            if ($attributeValueData[$attributeImportRules::getDerivedAttributeName()] != null)
            {
                $actualAttributeName = $importRulesType::getActualModelAttributeNameForDerivedAttribute();
                $actualModel         = $attributeValueData[$attributeImportRules::getDerivedAttributeName()];
                if (!$model->$actualAttributeName->contains($actualModel))
                {
                    $model->$actualAttributeName->add($actualModel);
                }
            }
        }

        protected static function resolveReadOnlyAndSetValueToAttribute(RedBeanModel $model, $attributeName, $value)
        {
            assert('is_string($attributeName)');
            if (!$model->isAttributeReadOnly($attributeName) || ($model->isAttributeReadOnly($attributeName) && // Not Coding Standard
                $model->isAllowedToSetReadOnlyAttribute($attributeName)))
            {
                $model->$attributeName = $value;
            }
        }

        protected static function resolveValueToSanitizeByValueAndColumnType($value, $columnType)
        {
            assert('$columnType == "importColumn" || $columnType == "extraColumn"');
            if ($columnType == 'importColumn')
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
            if ($value == '' || $value == null)
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