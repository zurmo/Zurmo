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
     * Helper class with import wizard related functions.
     */
    class ImportWizardUtil
    {
        /**
         * Mapping array to map the serialized data elements from the import object back and forth from the
         * import wizard form.
         * @var array
         */
        private static $importToFormAttributeMap = array('importRulesType',
                                                         'fileUploadData',
                                                         'rowColumnDelimiter',
                                                         'rowColumnEnclosure',
                                                         'firstRowIsHeaderRow',
                                                         'mappingData',
                                                         'dataAnalyzerMessagesData');

        /**
         * Given an import object, make an ImportWizardForm, mapping the attributes from the import object into the
         * form.
         * @param object $import
         */
        public static function makeFormByImport($import)
        {
            assert('$import instanceof Import');
            $form     = new ImportWizardForm();
            $form->id = $import->id;
            if($import->serializedData != null)
            {
                $unserializedData = unserialize($import->serializedData);
                foreach(self::$importToFormAttributeMap as $attributeName)
                {
                    if(isset($unserializedData[$attributeName]))
                    {
                        $form->$attributeName = $unserializedData[$attributeName];
                    }
                }
                if(isset($unserializedData['explicitReadWriteModelPermissions']))
                {
                    $form->explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                               makeByMixedPermitablesData(
                                                               $unserializedData['explicitReadWriteModelPermissions']);
                }
                else
                {
                    $form->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
                }
            }
            return $form;
        }

        /**
         * Given a form and an array of post data, set the form for the step 1 process. If the importRulesType
         * is already set and the new value is different, all other form attribute values should be emptied since this
         * means the importRulesType is different and for the next steps, we can't use existing saved data.
         * @param object $importWizardForm
         * @param array $postData
         */
        public static function setFormByPostForStep1($importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["importRulesType"])');
            if($importWizardForm->importRulesType != $postData["importRulesType"])
            {
                foreach(self::$importToFormAttributeMap as $attributeName)
                {
                    $importWizardForm->$attributeName = null;
                }
                $importWizardForm->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            }
            $importWizardForm->importRulesType = $postData['importRulesType'];
        }

        /**
         * Step 2 is where the import file is uploaded and the user checks if the first column is a header row.
         * @param object $importWizardForm
         * @param array $postData
         */
        public static function setFormByPostForStep2($importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData)');
            assert('isset($postData["firstRowIsHeaderRow"])');
            assert('isset($postData["rowColumnDelimiter"])');
            assert('isset($postData["rowColumnEnclosure"])');
            $importWizardForm->setAttributes(array('firstRowIsHeaderRow' => $postData['firstRowIsHeaderRow'],
                                                   'rowColumnDelimiter'  => $postData['rowColumnDelimiter'],
                                                   'rowColumnEnclosure'  => $postData['rowColumnEnclosure']));
        }

        /**
         * Step 3 is where the explicit permissions are decided for the models that will be imported.
         * @param object $importWizardForm
         * @param array $postData
         */
        public static function setFormByPostForStep3($importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["explicitReadWriteModelPermissions"])');
            $importWizardForm->explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                                   makeByPostData(
                                                                   $postData['explicitReadWriteModelPermissions']);
        }

        /**
         * Step 4 is where the import mapping is done along with any mapping rules.
         * @param object $importWizardForm
         * @param array $postData
         */
        public static function setFormByPostForStep4($importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData)');
            $importWizardForm->mappingData = $postData;
        }


        /**
         * Given an array of file upload data, set the form from this.  Keep the existing importRulesType value
         * but clear out any other form attributes since with a new file uploaded, those other attribute values will
         * need to be redone.
         * @param object $importWizardForm
         * @param array $fileUploadData
         */
        public static function setFormByFileUploadDataAndTableName($importWizardForm, $fileUploadData, $tableName)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($fileUploadData)');
            assert('is_string($tableName)');
            foreach(self::$importToFormAttributeMap as $attributeName)
            {
                if($attributeName != 'importRulesType')
                {
                    $importWizardForm->$attributeName = null;
                }
            }
            $importWizardForm->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            $importWizardForm->fileUploadData                    = $fileUploadData;
            try
            {
                $importWizardForm->mappingData = ImportMappingUtil::makeMappingDataByTableName($tableName);
            }
            catch(NoRowsInTableException $e)
            {
                throw new FailedFileUploadException(Yii::t('Default', 'Import file has no rows to use.'));
            }
        }

        /**
         * Based on the self::$importToFormAttributeMap, create an array of elements from the
         * import wizard form.  Serialize the array and set the import serializedData attribute.
         * @param object $importWizardForm
         * @param object $import
         */
        public static function setImportSerializedDataFromForm($importWizardForm, $import)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('$import instanceof Import');
            $dataToSerialize = array();
            foreach(self::$importToFormAttributeMap as $attributeName)
            {
                $dataToSerialize[$attributeName] = $importWizardForm->$attributeName;
            }
            $dataToSerialize['explicitReadWriteModelPermissions'] =
                ExplicitReadWriteModelPermissionsUtil::
                makeMixedPermitablesDataByExplicitReadWriteModelPermissions(
                $importWizardForm->explicitReadWriteModelPermissions);
            $import->serializedData = serialize($dataToSerialize);
        }

        /**
         * Use this method to remove the existing temp table associated with this import model.  Will also remove
         * data from serializedData that is created after a file is normally attached to an import model. It will
         * leave the importRulesType in place since that is created prior to uploading a new file.
         * @param object $import model.
         */
        public static function clearFileAndRelatedDataFromImport($import)
        {
            assert('$import instanceof Import');
            $unserializedData                       = $import->serializedData;
            $newUnserializedData['importRulesType'] = $unserializedData['importRulesType'];
            if($import->save())
            {
                ImportDatabaseUtil::dropTableByTableName($import->getTempTableName());
                return true;
            }
            return false;
        }

        /**
         * Given an importWizardForm and an import object, ascertain whether there is a sufficient number of rows
         * to do an import. If there is a header row present, then the minimum row count must be 2, otherwise it only
         * has to be 1.
         * @param object $importWizardForm
         * @param object $import
         * @return boolean true/false
         */
        public static function importFileHasAtLeastOneImportRow($importWizardForm, $import)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('$import instanceof Import');
            $count = ImportDatabaseUtil::getCount($import->getTempTableName());
            $minimumRows = 1;
            if($importWizardForm->firstRowIsHeaderRow)
            {
                $minimumRows = 2;
            }
            if($count >= $minimumRows)
            {
                return true;
            }
            return false;
        }
    }
?>