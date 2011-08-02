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

    class ImportDefaultController extends Controller
    {
        public function filters()
        {
            $filters   = array();
            $filters[] = array(
                         ZurmoBaseController::RIGHTS_FILTER_PATH,
                         'moduleClassName' => 'ImportModule',
                         'rightName' => ImportModule::getAccessRight(),
            );
            return $filters;
        }

        public function actionIndex()
        {
            $this->actionStep1();
        }

        /**
         * Step 1. Select the module to import data into.
         */
        public function actionStep1()
        {
            $importWizardForm = new ImportWizardForm();
            if(isset($_GET['id']))
            {
                $import = Import::getById((int)$_GET['id']);
            }
            else
            {
                $import = new Import();
            }
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);
            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep1($importWizardForm, $_POST[get_class($importWizardForm)]);
                $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step2');
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard: Step 1 of 6')), 0, 0);
            $importView->setView(new ImportWizardImportRulesView($this->getId(),
                                                                       $this->getModule()->getId(),
                                                                       $importWizardForm), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 2. Upload the csv to import.
         */
        public function actionStep2($id)
        {
            $import           = Import::getById((int)$id);
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);

            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep2($importWizardForm, $_POST[get_class($importWizardForm)]);
                if($importWizardForm->fileUploadData == null)
                {
                    $importWizardForm->addError('fileUploadData',
                    Yii::t('Default', 'A file must be uploaded in order to continue the import process.'));
                }
                elseif(!ImportWizardUtil::importFileHasAtLeastOneImportRow($importWizardForm, $import))
                {
                    if($importWizardForm->firstRowIsHeaderRow)
                    {
                        $importWizardForm->addError('fileUploadData',
                        Yii::t('Default', 'The file that has been uploaded only has a header row and no additional rows to import.'));
                    }
                    else
                    {
                        $importWizardForm->addError('fileUploadData',
                        Yii::t('Default', 'A file must be uploaded with at least one row to import.'));
                    }
                }
                else
                {
                    $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step3');
                }
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard: Step 2 of 6')), 0, 0);
            $importView->setView(new ImportWizardUploadFileView($this->getId(),
                                                                $this->getModule()->getId(),
                                                                $importWizardForm), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 3. Decide permissions for upload.
         */
        public function actionStep3($id)
        {
            $import           = Import::getById((int)$_GET['id']);
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);

            if (isset($_POST[get_class($importWizardForm)]))
            {
                ImportWizardUtil::setFormByPostForStep3($importWizardForm, $_POST[get_class($importWizardForm)]);
                $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step4');
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard: Step 3 of 6')), 0, 0);
            $importView->setView(new ImportWizardSetModelPermissionsView($this->getId(),
                                                                         $this->getModule()->getId(),
                                                                         $importWizardForm), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 4. Import mapping
         */
        public function actionStep4($id)
        {
            $import               = Import::getById((int)$_GET['id']);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $importWizardForm->setScenario('saveMappingData');
            $tempTableName        = $import->getTempTableName();
            $importRulesClassName = $importWizardForm->importRulesType . 'ImportRules';
            if (isset($_POST[get_class($importWizardForm)]))
            {
                $reIndexedPostData = ImportMappingUtil::
                                     reIndexExtraColumnNamesByPostData($_POST[get_class($importWizardForm)]);
                ImportWizardUtil::setFormByPostForStep4($importWizardForm, $reIndexedPostData);

                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
                MappingRuleFormAndElementTypeUtil::validateMappingRuleForms($mappingDataMappingRuleFormsAndElementTypes);
                //still validate even if MappingRuleForms fails, so all errors are captured and returned.
                $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step5');
            }
            else
            {
                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
            }
            $mappingDataMetadata                            = ImportWizardMappingViewUtil::
                                                              resolveMappingDataForView($importWizardForm->mappingData,
                                                              $tempTableName,
                                                              (bool)$importWizardForm->firstRowIsHeaderRow);
            $mappableAttributeIndicesAndDerivedTypes        = $importRulesClassName::
                                                              getMappableAttributeIndicesAndDerivedTypes();
            $importView                                     = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard: Step 4 of 6')), 0, 0);
            $importView->setView(new ImportWizardMappingView($this->getId(),
                                                             $this->getModule()->getId(),
                                                             $importWizardForm,
                                                             $mappingDataMetadata,
                                                             $mappingDataMappingRuleFormsAndElementTypes,
                                                             $mappableAttributeIndicesAndDerivedTypes), 1, 0);
            $view                                           = new ImportPageView($this, $importView);
            echo $view->render();
        }

        public function actionMappingRulesEdit($id, $attributeIndexOrDerivedType, $columnName, $columnType)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = $importWizardForm->importRulesType . 'ImportRules';
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();
            $mappingFormLayoutUtil                   = new MappingFormLayoutUtil(
                                                       get_class($importWizardForm),
                                                       new ZurmoActiveForm(),
                                                       $mappableAttributeIndicesAndDerivedTypes);
            echo $mappingFormLayoutUtil->renderMappingRulesElements(
                                                       $columnName,
                                                       $attributeIndexOrDerivedType,
                                                       $importWizardForm->importRulesType,
                                                       $columnType,
                                                       array());
        }

        public function actionMappingAddExtraColumn($id, $columnCount)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = $importWizardForm->importRulesType . 'ImportRules';
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();
            $extraColumnName                         = ImportMappingUtil::makeExtraColumnNameByColumnCount(
                                                       (int)$columnCount);
            $mappingDataMetadata                     = ImportWizardMappingViewUtil::
                                                       getExtraColumnMappingDataForViewByColumnName($extraColumnName);
            $extraColumnView                         = new ImportWizardMappingExtraColumnView(
                                                       $importWizardForm,
                                                       $mappingDataMetadata,
                                                       $mappableAttributeIndicesAndDerivedTypes);
            $view                                    = new AjaxPageView($extraColumnView);
            echo $view->render();
        }

        /**
         * Ajax action called from user interface to upload an import file. If a file for this import model is
         * already uploaded, then this will overwrite it.
         * @param string $filesVariableName
         * @param string $id (should be integer, but php type casting doesn't work so well)
         */
        public function actionUploadFile($filesVariableName, $id)
        {
            assert('is_string($filesVariableName)');
            $import           = Import::getById((int)$id);
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);
            try {
                $uploadedFile = UploadedFileUtil::getByNameAndCatchError($filesVariableName);
                assert('$uploadedFile instanceof CUploadedFile');
                $fileHandle  = fopen($uploadedFile->getTempName(), 'r');
                if ($fileHandle !== false)
                {
                    $tempTableName = $import->getTempTableName();
                    if(!ImportDatabaseUtil::makeDatabaseTableByFileHandleAndTableName($fileHandle, $tempTableName))
                    {
                        throw new FailedFileUploadException(Yii::t('Default', 'Failed to create temporary database table from CSV.'));
                    }
                    $fileUploadData = array(
                        'name' => $uploadedFile->getName(),
                        'type' => $uploadedFile->getType(),
                        'size' => $uploadedFile->getSize(),
                    );
                    ImportWizardUtil::setFormByFileUploadDataAndTableName($importWizardForm, $fileUploadData,
                                                                          $tempTableName);
                    ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                    if(!$import->save())
                    {
                        throw new FailedFileUploadException(Yii::t('Default', 'Import model failed to save.'));
                    }
                }
                else
                {
                    throw new FailedFileUploadException(Yii::t('Default', 'Failed to open the uploaded file.'));
                }
                $fileUploadData['humanReadableSize'] = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(
                                                       $fileUploadData['size']);
                $fileUploadData['id']                = $import->id;
            }
            catch(FailedFileUploadException $e)
            {
                $fileUploadData = array('error' => Yii::t('Default', 'Error:') . ' ' . $e->getMessage());
                ImportWizardUtil::clearFileAndRelatedDataFromImport($import);
            }
            echo CJSON::encode($fileUploadData);
            Yii::app()->end(0, false);
        }

        /**
         * Ajax action to delete an import file that was uploaded.  Will drop the temporary table created for the import.
         * @param string $id
         */
        public function actionDeleteFile($id)
        {
            $import = Import::getById((int)$id);
            ImportWizardUtil::clearFileAndRelatedDataFromImport($import);
        }

        /**
         * Generic method that is used by all steps to validate and saved the ImportWizardForm and Import model.
         * @param object $importWizardForm
         * @param object $import
         * @param string $redirectAction
         */
        protected function attemptToValidateImportWizardFormAndSave($importWizardForm, $import, $redirectAction)
        {
            assert('$importWizardForm instanceof ImportWizardForm');
            assert('$import instanceof Import');
            assert('is_string($redirectAction)');
            if($importWizardForm->validate())
            {
                ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                if($import->save())
                {
                    $this->redirect(array($this->getId() . '/' . $redirectAction, 'id' => $import->id));
                    Yii::app()->end(0, false);
                }
                else
                {
                    $messageView = new ErrorView(Yii::t('Default', 'There was an error processing this import.'));
                    $view        = new ErrorPageView($messageView);
                    echo $view->render();
                    Yii::app()->end(0, false);
                }
            }
        }
    }
?>
