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
                                                         'firstRowIsHeaderRow',
                                                         'modelPermissions',
                                                         'mappingData');

        public static function makeFormByImport($import)
        {
            assert('$import instanceof Import');
            $form = new ImportWizardForm();
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
        public static function setFormByPostForStep1(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["importRulesType"])');
            if($importWizardForm->importRulesType != $postData["importRulesType"])
            {
                foreach(self::$importToFormAttributeMap as $attributeName)
                {
                    $importWizardForm->$attributeName = null;
                }
            }
            $importWizardForm->importRulesType = $postData['importRulesType'];
        }

        public static function setFormByPostForStep2(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["firstRowIsHeaderRow"])');
            $importWizardForm->setAttributes(array('firstRowIsHeaderRow' => $postData['firstRowIsHeaderRow']));
        }

        public static function setFormByPostForStep3(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["modelPermissions"])');
            $importWizardForm->setAttributes(array('modelPermissions' => $postData['modelPermissions']));
        }

        public static function setFormByPostForStep4(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["modelPermissions"])');
            //todo: this should populate the mappingData fully including the rules data from post.
        }


        /**
         * Given an array of file upload data, set the form from this.  Keep the existing importRulesType value
         * but clear out any other form attributes since with a new file uploaded, those other attribute values will
         * need to be redone.
         * @param object $importWizardForm
         * @param array $fileUploadData
         */
        public static function setFormByFileUploadDataAndTableName(& $importWizardForm, $fileUploadData, $tableName)
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
            $importWizardForm->fileUploadData = $fileUploadData;
            $importWizardForm->mappingData    = ImportMappingUtil::makeMappingDataByTableName($tableName);
        }

        /**
         * Based on the self::$importToFormAttributeMap, create an array of elements from the
         * import wizard form.  Serialize the array and set the import serializedData attribute.
         * @param object $importWizardForm
         * @param object $import
         */
        public static function setImportSerializedDataFromForm($importWizardForm, & $import)
        {
            $dataToSerialize = array();
            foreach(self::$importToFormAttributeMap as $attributeName)
            {
                $dataToSerialize[$attributeName] = $importWizardForm->$attributeName;
            }
            $import->serializedData = serialize($dataToSerialize);
        }
    }
?>