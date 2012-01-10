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
            if (isset($_GET['id']))
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
                if ($importWizardForm->fileUploadData == null)
                {
                    $importWizardForm->addError('fileUploadData',
                    Yii::t('Default', 'A file must be uploaded in order to continue the import process.'));
                }
                elseif (!ImportWizardUtil::importFileHasAtLeastOneImportRow($importWizardForm, $import))
                {
                    if ($importWizardForm->firstRowIsHeaderRow)
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
                    $importRulesClassName  = $importWizardForm->importRulesType . 'ImportRules';
                    if (!is_subclass_of($importRulesClassName::getModelClassName(), 'SecurableItem'))
                    {
                        $nextStep = 'step4';
                    }
                    else
                    {
                        $nextStep = 'step3';
                    }
                    $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, $nextStep);
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
            $import                = Import::getById((int)$_GET['id']);
            $importWizardForm      = ImportWizardUtil::makeFormByImport($import);
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
            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $importWizardForm->setScenario('saveMappingData');
            $tempTableName        = $import->getTempTableName();
            $importRulesClassName = ImportRulesUtil::getImportRulesClassNameByType($importWizardForm->importRulesType);
            if (isset($_POST[get_class($importWizardForm)]))
            {
                $reIndexedPostData                          = ImportMappingUtil::
                                                              reIndexExtraColumnNamesByPostData(
                                                              $_POST[get_class($importWizardForm)]);
                $sanitizedPostData                          = ImportWizardFormPostUtil::
                                                              sanitizePostByTypeForSavingMappingData(
                                                              $importWizardForm->importRulesType, $reIndexedPostData);
                ImportWizardUtil::setFormByPostForStep4($importWizardForm, $sanitizedPostData);

                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
                $validated                                  = MappingRuleFormAndElementTypeUtil::
                                                              validateMappingRuleForms(
                                                              $mappingDataMappingRuleFormsAndElementTypes);
                if ($validated)
                {
                    //Still validate even if MappingRuleForms fails, so all errors are captured and returned.
                    $this->attemptToValidateImportWizardFormAndSave($importWizardForm, $import, 'step5');
                }
                else
                {
                    $importWizardForm->validate();
                    $importWizardForm->addError('mappingData', Yii::t('Default',
                                                'There are errors with some of your mapping rules. Please fix.'));
                }
            }
            else
            {
                $mappingDataMappingRuleFormsAndElementTypes = MappingRuleFormAndElementTypeUtil::
                                                              makeFormsAndElementTypesByMappingDataAndImportRulesType(
                                                              $importWizardForm->mappingData,
                                                              $importWizardForm->importRulesType);
            }
            $dataProvider                                   = $this->makeDataProviderForSampleRow($import,
                                                              (bool)$importWizardForm->firstRowIsHeaderRow);
            if ($importWizardForm->firstRowIsHeaderRow)
            {
                $headerRow = ImportDatabaseUtil::getFirstRowByTableName($import->getTempTableName());
                assert('$headerRow != null');
            }
            else
            {
                $headerRow = null;
            }
            $sampleData                                     = $dataProvider->getData();
            assert('count($sampleData) == 1');
            $sample                                         = current($sampleData);
            $pagerUrl                                       = Yii::app()->createUrl('import/default/sampleRow',
                                                              array('id' => $import->id));
            $pagerContent                                   = ImportDataProviderPagerUtil::
                                                              renderPagerAndHeaderTextContent($dataProvider, $pagerUrl);
            $mappingDataMetadata                            = ImportWizardMappingViewUtil::
                                                              resolveMappingDataForView($importWizardForm->mappingData,
                                                              $sample, $headerRow);
            $mappableAttributeIndicesAndDerivedTypes        = $importRulesClassName::
                                                              getMappableAttributeIndicesAndDerivedTypes();
            $importView                                     = new GridView(2, 1);
            $importView->setView(new TitleBarView(Yii::t('Default', 'Import Wizard: Step 4 of 6')), 0, 0);
            $importView->setView(new ImportWizardMappingView($this->getId(),
                                                             $this->getModule()->getId(),
                                                             $importWizardForm,
                                                             $pagerContent,
                                                             $mappingDataMetadata,
                                                             $mappingDataMappingRuleFormsAndElementTypes,
                                                             $mappableAttributeIndicesAndDerivedTypes,
                                                             $importRulesClassName::getRequiredAttributesLabelsData()),
                                                             1, 0);
            $view                                           = new ImportPageView($this, $importView);
            echo $view->render();
        }

        /**
         * Step 5. Analyze data in a sequential process.
         * @param integer id - Import model id
         * @param string $step
         */
        function actionStep5($id, $step = null)
        {
            if (isset($_GET['nextParams']))
            {
                $nextParams = $_GET['nextParams'];
            }
            else
            {
                $nextParams = null;
            }
            assert('$step == null || is_string($step)');
            assert('$nextParams == null || is_array($nextParams)');

            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $unserializedData     = unserialize($import->serializedData);
            $pageSize             = Yii::app()->pagination->resolveActiveForCurrentUserByType('importPageSize');
            $config               = array('pagination' => array('pageSize' => $pageSize));
            $dataProvider         = new ImportDataProvider($import->getTempTableName(),
                                                           (bool)$importWizardForm->firstRowIsHeaderRow,
                                                           $config);
            $sequentialProcess    = new ImportDataAnalysisSequentialProcess($import, $dataProvider);
            $sequentialProcess->run($step, $nextParams);
            $nextStep             = $sequentialProcess->getNextStep();
            $route                = $this->getModule()->getId() . '/' . $this->getId() . '/step5';
            if ($sequentialProcess->isComplete())
            {
                $columnNamesAndAttributeIndexOrDerivedTypeLabels = ImportMappingUtil::
                                                                  makeColumnNamesAndAttributeIndexOrDerivedTypeLabels(
                                                                  $unserializedData['mappingData'],
                                                                  $unserializedData['importRulesType']);
                $dataAnalysisCompleteView = new ImportWizardDataAnalysisCompleteView($this->getId(),
                                                $this->getModule()->getId(),
                                                $importWizardForm,
                                                $columnNamesAndAttributeIndexOrDerivedTypeLabels);

                $sequenceView = new ContainedViewCompleteSequentialProcessView($dataAnalysisCompleteView);
            }
            else
            {
                $sequenceView = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            }
            if ($step == null)
            {
                $gridView     = new GridView(2, 1);
                $titleBarView = new TitleBarView (Yii::t('Default', 'Import Wizard: Step 5 of 6'));
                $wrapperView  = new ImportSequentialProcessContainerView($sequenceView, $sequentialProcess->getAllStepsMessage());
                $gridView->setView($titleBarView, 0, 0);
                $gridView->setView($wrapperView, 1, 0);
                $view        = new ImportPageView($this, $gridView);
            }
            else
            {
                $view        = new AjaxPageView($sequenceView);
            }
            echo $view->render();
        }

        /**
         * Step 6. Sanitize and create/update models using a sequential process.
         * @param integer id - Import model id
         * @param string $step
         */
        function actionStep6($id, $step = null)
        {
            if (isset($_GET['nextParams']))
            {
                $nextParams = $_GET['nextParams'];
            }
            else
            {
                $nextParams = null;
            }
            assert('$step == null || is_string($step)');
            assert('$nextParams == null || is_array($nextParams)');
            $import               = Import::getById((int)$id);
            $importWizardForm     = ImportWizardUtil::makeFormByImport($import);
            $cs                   = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $importCompleteView = $this->makeImportCompleteView($import, $importWizardForm);
                $view               = new AjaxPageView($importCompleteView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $unserializedData     = unserialize($import->serializedData);
            $pageSize             = Yii::app()->pagination->resolveActiveForCurrentUserByType('importPageSize');
            $config               = array('pagination' => array('pageSize' => $pageSize));
            $dataProvider         = new ImportDataProvider($import->getTempTableName(),
                                                           (bool)$importWizardForm->firstRowIsHeaderRow,
                                                           $config);
            $sequentialProcess    = new ImportCreateUpdateModelsSequentialProcess($import, $dataProvider);
            $sequentialProcess->run($step, $nextParams);
            $nextStep             = $sequentialProcess->getNextStep();
            $route                = $this->getModule()->getId() . '/' . $this->getId() . '/step6';
            if ($sequentialProcess->isComplete())
            {
                $importCompleteView = $this->makeImportCompleteView($import, $importWizardForm, true);
                $sequenceView       = new ContainedViewCompleteSequentialProcessView($importCompleteView);
            }
            else
            {
                $sequenceView = SequentialProcessViewFactory::makeBySequentialProcess($sequentialProcess, $route);
            }
            if ($step == null)
            {
                $gridView     = new GridView(2, 1);
                $titleBarView = new TitleBarView (Yii::t('Default', 'Import Wizard: Step 6 of 6'));
                $wrapperView  = new ImportSequentialProcessContainerView($sequenceView, $sequentialProcess->getAllStepsMessage());
                $gridView->setView($titleBarView, 0, 0);
                $gridView->setView($wrapperView, 1, 0);
                $view        = new ImportPageView($this, $gridView);
            }
            else
            {
                $view        = new AjaxPageView($sequenceView);
            }
            echo $view->render();
        }

        protected function makeImportCompleteView(Import $import, ImportWizardForm $importWizardForm, $setCurrentPageToFirst = false)
        {
            $pageSize                 = Yii::app()->pagination->resolveActiveForCurrentUserByType('listPageSize');
            $config                   = array('pagination' => array('pageSize' => $pageSize));
            if ($setCurrentPageToFirst)
            {
                $config['pagination']['currentPage'] = 0;
            }
            $importErrorsDataProvider = new ImportDataProvider($import->getTempTableName(),
                                                               (bool)$importWizardForm->firstRowIsHeaderRow,
                                                               $config,
                                                               ImportRowDataResultsUtil::ERROR);
            $errorListView            = new ImportErrorsListView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            'NotUsed',
                                            $importErrorsDataProvider
                                            );
            $importCompleteView       = new ImportWizardCreateUpdateModelsCompleteView($this->getId(),
                                            $this->getModule()->getId(),
                                            $importWizardForm,
                                            (int)ImportRowDataResultsUtil::getCreatedCount($import->getTempTableName()),
                                            (int)ImportRowDataResultsUtil::getUpdatedCount($import->getTempTableName()),
                                            (int)ImportRowDataResultsUtil::getErrorCount($import->getTempTableName()),
                                            $errorListView);
            return $importCompleteView;
        }

        /**
         * Step 4 ajax process.  When you change the attribute dropdown, new mapping rule information is retrieved
         * and displayed in the user interface.
         */
        public function actionMappingRulesEdit($id, $attributeIndexOrDerivedType, $columnName, $columnType)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = ImportRulesUtil::
                                                       getImportRulesClassNameByType($importWizardForm->importRulesType);
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();

            $mappingFormLayoutUtil                   = ImportToMappingFormLayoutUtil::make(
                                                       get_class($importWizardForm),
                                                       new ZurmoActiveForm(),
                                                       $importWizardForm->importRulesType,
                                                       $mappableAttributeIndicesAndDerivedTypes);

            $content                                 = $mappingFormLayoutUtil->renderMappingRulesElements(
                                                       $columnName,
                                                       $attributeIndexOrDerivedType,
                                                       $importWizardForm->importRulesType,
                                                       $columnType,
                                                       array());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        /**
         * Step 4 ajax process.  When you click the 'Add Field' button in the user interface, this ajax action
         * is called and makes an extra row to display for mapping.
         */
        public function actionMappingAddExtraMappingRow($id, $columnCount)
        {
            $import                                  = Import::getById((int)$_GET['id']);
            $importWizardForm                        = ImportWizardUtil::makeFormByImport($import);
            $importRulesClassName                    = ImportRulesUtil::
                                                       getImportRulesClassNameByType($importWizardForm->importRulesType);
            $mappableAttributeIndicesAndDerivedTypes = $importRulesClassName::
                                                       getMappableAttributeIndicesAndDerivedTypes();
            $extraColumnName                         = ImportMappingUtil::makeExtraColumnNameByColumnCount(
                                                       (int)$columnCount);
            $mappingDataMetadata                     = ImportWizardMappingViewUtil::
                                                       makeExtraColumnMappingDataForViewByColumnName($extraColumnName);
            $extraColumnView                         = new ImportWizardMappingExtraColumnView(
                                                       $importWizardForm,
                                                       $mappingDataMetadata,
                                                       $mappableAttributeIndicesAndDerivedTypes);
            $view                                    = new AjaxPageView($extraColumnView);
            echo $view->render();
        }

        public function actionSampleRow($id)
        {
            $import              = Import::getById((int)$_GET['id']);
            $importWizardForm    = ImportWizardUtil::makeFormByImport($import);
            $dataProvider        = $this->makeDataProviderForSampleRow($import,
                                   (bool)$importWizardForm->firstRowIsHeaderRow);
            $data                = $dataProvider->getData();
            $renderedContentData = array();
            $pagerUrl            = Yii::app()->createUrl('import/default/sampleRow', array('id' => $import->id));
            $headerContent       = ImportDataProviderPagerUtil::renderPagerAndHeaderTextContent($dataProvider, $pagerUrl);
            $renderedContentData[MappingFormLayoutUtil::getSampleColumnHeaderId()] = $headerContent;
            foreach ($data as $sampleColumnData)
            {
                foreach ($sampleColumnData as $columnName => $value)
                {
                    if (!in_array($columnName, ImportDatabaseUtil::getReservedColumnNames()))
                    {
                        $renderedContentData[MappingFormLayoutUtil::
                        resolveSampleColumnIdByColumnName($columnName)] = MappingFormLayoutUtil::
                                                                          renderChoppedStringContent($value);
                    }
                }
            }
            echo CJSON::encode($renderedContentData);
            Yii::app()->end(0, false);
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
            $importWizardForm->setAttributes($_POST['ImportWizardForm']);
            if (!$importWizardForm->validate(array('rowColumnDelimiter')))
            {
                $fileUploadData = array('error' => Yii::t('Default', 'Error: Invalid delimiter'));
            }
            elseif (!$importWizardForm->validate(array('rowColumnDelimiter')))
            {
                $fileUploadData = array('error' => Yii::t('Default', 'Error: Invalid qualifier'));
            }
            else
            {
                try
                {
                    $uploadedFile = ImportUploadedFileUtil::getByNameCatchErrorAndEnsureFileIsACSV($filesVariableName);
                    assert('$uploadedFile instanceof CUploadedFile');
                    $fileHandle  = fopen($uploadedFile->getTempName(), 'r');
                    if ($fileHandle !== false)
                    {
                        $tempTableName = $import->getTempTableName();
                        try
                        {
                            $tableCreated = ImportDatabaseUtil::
                                            makeDatabaseTableByFileHandleAndTableName($fileHandle, $tempTableName,
                                                                                      $importWizardForm->rowColumnDelimiter,
                                                                                      $importWizardForm->rowColumnEnclosure);
                            if (!$tableCreated)
                            {
                                throw new FailedFileUploadException(Yii::t('Default', 'Failed to create temporary database table from CSV.'));
                            }
                        }
                        catch (BulkInsertFailedException $e)
                        {
                            throw new FailedFileUploadException($e->getMessage());
                        }

                        $fileUploadData = array(
                            'name' => $uploadedFile->getName(),
                            'type' => $uploadedFile->getType(),
                            'size' => $uploadedFile->getSize(),
                        );
                        ImportWizardUtil::setFormByFileUploadDataAndTableName($importWizardForm, $fileUploadData,
                                                                              $tempTableName);
                        ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                        if (!$import->save())
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
                catch (FailedFileUploadException $e)
                {
                    $fileUploadData = array('error' => Yii::t('Default', 'Error') . ' ' . $e->getMessage());
                    ImportWizardUtil::clearFileAndRelatedDataFromImport($import);
                }
            }
            echo CJSON::encode(array($fileUploadData));
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
            if ($importWizardForm->validate())
            {
                ImportWizardUtil::setImportSerializedDataFromForm($importWizardForm, $import);
                if ($import->save())
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

        protected function makeDataProviderForSampleRow($import, $firstRowIsHeaderRow)
        {
            assert('$import instanceof Import');
            assert('is_bool($firstRowIsHeaderRow)');
            $config = array('pagination' => array('pageSize' => 1));
            return    new ImportDataProvider($import->getTempTableName(), $firstRowIsHeaderRow, $config);
        }
    }
?>
