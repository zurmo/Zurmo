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

    class ImportWizardUtil
    {
        private static $importToFormAttributeMap = array('modelImportRulesType',
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
         * Given a form and an array of post data, set the form for the step 1 process. If the modelImportRulesType
         * is already set and the new value is different, all other form attribute values should be emptied since this
         * means the modelImportRulesType is different and for the next steps, we can't use existing saved data.
         * @param object $importWizardForm
         * @param array $postData
         */
        public static function setFormByPostForStep1(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["modelImportRulesType"])');
            if($importWizardForm->modelImportRulesType != null &&
               $importWizardForm->modelImportRulesType != $postData["modelImportRulesType"])
            {
                foreach(self::$importToFormAttributeMap as $attributeName)
                {
                    $importWizardForm->$attributeName = null;
                }
            }
            $importWizardForm->modelImportRulesType = $postData['modelImportRulesType'];
        }

        public static function setFormByPostForStep2(& $importWizardForm, $postData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($postData) && isset($postData["firstRowIsHeaderRow"])');
            $importWizardForm->setAttributes(array('firstRowIsHeaderRow' => $postData['firstRowIsHeaderRow']));
        }

        /**
         * Given an array of file upload data, set the form from this.  Keep the existing modelImportRulesType value
         * but clear out any other form attributes since with a new file uploaded, those other attribute values will
         * need to be redone.
         * @param object $importWizardForm
         * @param array $fileUploadData
         */
        public static function setFormByFileUploadData(& $importWizardForm, $fileUploadData)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('is_array($fileUploadData)');
            foreach(self::$importToFormAttributeMap as $attributeName)
            {
                $importWizardForm->$attributeName = null;
            }
            $importWizardForm->fileUploadData = $fileUploadData;
        }

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