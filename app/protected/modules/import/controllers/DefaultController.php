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
            if (isset($_GET['id']))
            {
                $import = Import::getById($_GET['id']);
            }
            else
            {
                $import = new Import();
            }
            $importWizardForm = ImportWizardUtil::makeFormByImport($import);
            if (isset($_POST[get_class($importStep1Form)]))
            {
                ImportWizardUtil::setFormByPostForStep1($importWizardForm, $_POST[get_class($importWizardForm)]);
                if ($importWizardForm->validate())
                {
                    ImportWizardUtil::setImportSerializedDataFromForm($import, $importWizardForm);
                    if ($import->save())
                    {
                        $this->redirect(array($this->getId() . '/step2', array('id' => $import->id)));
                    }
                    else
                    {
                        //todo: handle error.
                    }
                }
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard Step 1 of 6')), 0, 0);
            $importView->setView(new ImportWizardModuleImportRulesView($this->getId(),
                                                                       $this->getModule()->getId(),
                                                                       $importWizardForm,
                                                                       (int)$import->id), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 2. Upload the csv to import.
         */
        public function actionStep2($id)
        {
            $import           = Import::getById($_GET['id']);
            $importWizardForm = ImportWizardFormUtil::makeFormByImport($import);

            if (isset($_POST[get_class($importStep1Form)]))
            {
                ImportWizardUtil::setFormByPostForStep2($importWizardForm, $_POST[get_class($importWizardForm)]);
                if ($importWizardForm->validate())
                {
                    ImportWizardUtil::setImportSerializedDataFromForm($import, $importWizardForm);
                    if ($import->save())
                    {
                        $this->redirect(array($this->getId() . '/step3', array('id' => $import->id)));
                    }
                    else
                    {
                        //todo: handle error.
                    }
                }
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard Step 2 of 6')), 0, 0);
            $importView->setView(new ImportWizardUploadFileView($this->getId(),
                                                                $this->getModule()->getId(),
                                                                $importWizardForm,
                                                                (int)$import->id), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 3. Decide permissions for upload.
         */
        public function actionStep3($id)
        {
            $import           = Import::getById($_GET['id']);
            $importWizardForm = ImportWizardFormUtil::makeFormByImport($import);

            if (isset($_POST[get_class($importStep1Form)]))
            {
                ImportWizardUtil::setFormByPostForStep3($importWizardForm, $_POST[get_class($importWizardForm)]);
                if ($importWizardForm->validate())
                {
                    ImportWizardUtil::setImportSerializedDataFromForm($import, $importWizardForm);
                    if ($import->save())
                    {
                        $this->redirect(array($this->getId() . '/step4', array('id' => $import->id)));
                    }
                    else
                    {
                        //todo: handle error.
                    }
                }
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard Step 3 of 6')), 0, 0);
            $importView->setView(new ImportWizardModelPermissionsView($this->getId(),
                                                                      $this->getModule()->getId(),
                                                                      $importWizardForm,
                                                                      (int)$import->id), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 4. Import mapping
         */
        public function actionStep4($id)
        {
            $import           = Import::getById($_GET['id']);
            $importWizardForm = ImportWizardFormUtil::makeFormByImport($import);

            //todo: ajax validation for mapping? probably makes good sense?

            //todo: the adapter, data -> metadata stuff like GroupController -> EditModulePermissions
            //going to need to do tests i think first.... tests to show the metadata is as expected either new
            //or coming back here....

            if (isset($_POST[get_class($importStep1Form)]))
            {
                ImportWizardUtil::setFormByPostForStep4($importWizardForm, $_POST[get_class($importWizardForm)]);
                if ($importWizardForm->validate())
                {
                    ImportWizardUtil::setImportSerializedDataFromForm($import, $importWizardForm);
                    if ($import->save())
                    {
                        $this->redirect(array($this->getId() . '/step5', array('id' => $import->id)));
                    }
                    else
                    {
                        //todo: handle error.
                    }
                }
            }
            $importView = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard Step 4 of 6')), 0, 0);
            $importView->setView(new ImportWizardMappingView($this->getId(),
                                                             $this->getModule()->getId(),
                                                             $importWizardForm,
                                                             (int)$import->id), 1, 0);
            $view       = new ImportPageView($this, $importView);
            echo $view->render();
        }

        public function actionUpload($filesVariableName, $id)
        {
            assert('is_string($filesVariableName)');
            $import           = Import::getById($id);
            $importWizardForm = ImportWizardFormUtil::makeFormByImport($import);
            try {
                $uploadedFile = UploadedFileUtil::getByNameAndCatchError($filesVariableName);
                assert('$uploadedFile instanceof CUploadedFile');
                $fileHandle  = fopen($uploadedFile->getTempName(), 'r');
                if ($fileHandle !== false)
                {
                    $tableName = 'importtable' . $import->id;
                    if (!ImportDatabaseUtil::makeDatabaseTableByFileHandleAndTableName($fileHandle, $tableName))
                    {
                        throw new FailedFileUploadException(Yii::t('Default', 'Failed to create temporary database table from CSV.'));
                    }
                    $fileUploadData = array(
                        'name' => $uploadedFile->getName(),
                        'type' => $uploadedFile->getType(),
                        'size' => $uploadedFile->getSize(),
                    );
                    ImportWizardUtil::setFormByFileUploadDataAndTableName($importWizardForm, $fileUpload, $tableName);
                    ImportWizardUtil::setImportSerializedDataFromForm($import, $importWizardForm);
                    if (!$import->save())
                    {
                        throw new FailedFileUploadException(Yii::t('Default', 'Import model failed to save.'));
                    }
                }
                else
                {
                    throw new FailedFileUploadException(Yii::t('Default', 'Failed to open the uploaded file.'));
                }
                $fileUpload['humanReadableSize'] = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(
                                                   $fileUploadData['size']);
                $fileUploadData['id']            = $import->id;
            }
            catch(FailedFileUploadException $e)
            {
                $import->delete();
                $fileUpload = array('error' => Yii::t('Default', 'Error:') . ' ' . $e->getMessage());
            }
            echo CJSON::encode($fileUploadData);
            Yii::app()->end(0, false);
        }
    }
?>
