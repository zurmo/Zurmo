<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Default controller for all report actions
      */
    class ReportsDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        self::getRightsFilterPath() . ' + drillDownDetails',
                        'moduleClassName' => 'ReportsModule',
                        'rightName' => ReportsModule::RIGHT_ACCESS_REPORTS,
                   ),
                   array(
                        self::getRightsFilterPath() . ' + selectType',
                        'moduleClassName' => 'ReportsModule',
                        'rightName' => ReportsModule::RIGHT_CREATE_REPORTS,
                   ),
                   array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $savedReport                    = new SavedReport(false);
            $searchForm                     = new ReportsSearchForm($savedReport);
            $dataProvider                   = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ReportsSearchView'
            );
            $title           = Zurmo::t('ReportsModule', 'Reports');
            $breadcrumbLinks = array(
                 $title,
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new ReportsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForReportsSearchAndListView');
                $view = new ReportsPageView(ZurmoDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                            $this, $mixedView, $breadcrumbLinks, 'ReportBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $savedReport = static::getModelAndCatchNotFoundAndDisplayError('SavedReport', intval($id));
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($savedReport), 'ReportsModule'), $savedReport);
            $breadcrumbLinks         = array(strval($savedReport));
            $breadCrumbView          = new ReportBreadCrumbView($this->getId(), $this->getModule()->getId(), $breadcrumbLinks);
            $detailsAndRelationsView = $this->makeReportDetailsAndRelationsView($savedReport, Yii::app()->request->getRequestUri(),
                                                                                $breadCrumbView);
            $view = new ReportsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionSelectType()
        {
            $breadcrumbLinks  = array(Zurmo::t('ReportsModule', 'Select Report Type'));
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    new ReportWizardTypesGridView(),
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate($type = null)
        {
            if ($type == null)
            {
                $this->actionSelectType();
                Yii::app()->end(0, false);
            }
            $breadcrumbLinks = array(Zurmo::t('ReportsModule', 'Create'));
            assert('is_string($type)');
            $report           = new Report();
            $report->setType($type);
            $reportWizardView = ReportWizardViewFactory::makeViewFromReport($report);
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $reportWizardView,
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $savedReport      = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedReport);
            $breadcrumbLinks  = array(strval($savedReport));
            $report           = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $reportWizardView = ReportWizardViewFactory::makeViewFromReport($report);
            $view             = new ReportsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this,
                                                    $reportWizardView,
                                                    $breadcrumbLinks,
                                                    'ReportBreadCrumbView'));
            echo $view->render();
        }

        public function actionSave($type, $id = null)
        {
            $postData                  = PostUtil::getData();
            $savedReport               = null;
            $report                    = null;
            $this->resolveSavedReportAndReportByPostData($postData, $savedReport, $report, $type, $id);
            $reportToWizardFormAdapter = new ReportToWizardFormAdapter($report);
            $model                     =  $reportToWizardFormAdapter->makeFormByType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 resolveByPostDataAndModelThenMake($postData[get_class($model)], $savedReport);
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            if ($savedReport->id > 0)
            {
                ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            }
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedReport);
            if ($savedReport->save())
            {
                if ($explicitReadWriteModelPermissions != null)
                {
                    ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($savedReport,
                                                           $explicitReadWriteModelPermissions);
                }

                //i can do a safety check on perms, then do flash here, on the jscript we can go to list instead and this should come up...
                //make sure you add to list of things to test.

                $redirectToList = $this->resolveAfterSaveHasPermissionsProblem($savedReport,
                                                                                    $postData[get_class($model)]['name']);
                echo CJSON::encode(array('id'             => $savedReport->id,
                                         'redirectToList' => $redirectToList));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $postData    = PostUtil::getData();
            $savedReport = null;
            $report      = null;
            $this->resolveSavedReportAndReportByPostData($postData, $savedReport, $report, $type, $id);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new ReportRelationsAndAttributesToTreeAdapter($report, $treeType);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber, $trackableStructurePosition = false, $id = null)
        {
            $postData                           = PostUtil::getData();
            $savedReport                        = null;
            $report                             = null;
            $this->resolveSavedReportAndReportByPostData($postData, $savedReport, $report, $type, $id);
            $nodeIdWithoutTreeType              = ReportRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $report->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
            $model                              = ComponentForReportFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $report->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForReportComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content               = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionGetAvailableSeriesAndRangesForChart($type, $id = null)
        {
            $postData                           = PostUtil::getData();
            $savedReport                        = null;
            $report                             = null;
            $this->resolveSavedReportAndReportByPostData($postData, $savedReport, $report, $type, $id);
            $moduleClassName                    = $report->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $modelToReportAdapter               = ModelRelationsAndAttributesToReportAdapter::
                                                  make($moduleClassName, $modelClassName, $report->getType());
            if (!$modelToReportAdapter instanceof ModelRelationsAndAttributesToSummationReportAdapter)
            {
                throw new NotSupportedException();
            }
            $seriesAttributesData                       = $modelToReportAdapter->
                                                          getAttributesForChartSeries($report->getGroupBys(),
                                                          $report->getDisplayAttributes());
            $rangeAttributesData  =                       $modelToReportAdapter->
                                                          getAttributesForChartRange ($report->getDisplayAttributes());
            $dataAndLabels                              = array();
            $dataAndLabels['firstSeriesDataAndLabels']  = array('' => Zurmo::t('ReportsModule', '(None)'));
            $dataAndLabels['firstSeriesDataAndLabels']  = array_merge($dataAndLabels['firstSeriesDataAndLabels'],
                                                          ReportUtil::makeDataAndLabelsForSeriesOrRange($seriesAttributesData));
            $dataAndLabels['firstRangeDataAndLabels']   = array('' => Zurmo::t('ReportsModule', '(None)'));
            $dataAndLabels['firstRangeDataAndLabels']   = array_merge($dataAndLabels['firstRangeDataAndLabels'],
                                                          ReportUtil::makeDataAndLabelsForSeriesOrRange($rangeAttributesData));
            $dataAndLabels['secondSeriesDataAndLabels'] = array('' => Zurmo::t('ReportsModule', '(None)'));
            $dataAndLabels['secondSeriesDataAndLabels'] = array_merge($dataAndLabels['secondSeriesDataAndLabels'],
                                                          ReportUtil::makeDataAndLabelsForSeriesOrRange($seriesAttributesData));
            $dataAndLabels['secondRangeDataAndLabels']  = array('' => Zurmo::t('ReportsModule', '(None)'));
            $dataAndLabels['secondRangeDataAndLabels']  = array_merge($dataAndLabels['secondRangeDataAndLabels'],
                                                          ReportUtil::makeDataAndLabelsForSeriesOrRange($rangeAttributesData));
            echo CJSON::encode($dataAndLabels);
        }

        public function actionApplyRuntimeFilters($id)
        {
            $postData             = PostUtil::getData();
            $savedReport          = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            $report               = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $wizardFormClassName  = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
            if (!isset($postData[$wizardFormClassName]))
            {
                throw new NotSupportedException();
            }
            DataToReportUtil::resolveFilters($postData[$wizardFormClassName], $report);
            if (isset($postData['ajax']) && $postData['ajax'] == 'edit-form')
            {
                $adapter          = new ReportToWizardFormAdapter($report);
                $reportWizardForm = $adapter->makeFormByType();
                $reportWizardForm->setScenario(reportWizardForm::FILTERS_VALIDATION_SCENARIO);
                if (!$reportWizardForm->validate())
                {
                    $errorData = array();
                    foreach ($reportWizardForm->getErrors() as $attribute => $errors)
                    {
                            $errorData[ZurmoHtml::activeId($reportWizardForm, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
            }
            $filtersData          = ArrayUtil::getArrayValue($postData[$wizardFormClassName],
                                    ComponentForReportForm::TYPE_FILTERS);
            $sanitizedFiltersData = DataToReportUtil::sanitizeFiltersData($report->getModuleClassName(),
                                                                          $report->getType(),
                                                                          $filtersData);
            $stickyData           = array(ComponentForReportForm::TYPE_FILTERS => $sanitizedFiltersData);
            StickyReportUtil::setDataByKeyAndData($report->getId(), $stickyData);
        }

        public function actionResetRuntimeFilters($id)
        {
            $savedReport      = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            $report           = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            StickyReportUtil::clearDataByKey($report->getId());
        }

        public function actionDelete($id)
        {
            $savedReport = SavedReport::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedReport);
            $savedReport->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionDrillDownDetails($id, $rowId)
        {
            $savedReport  = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport, true);
            $report       = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $report->resolveGroupBysAsFilters(GetUtil::getData());
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'reportResultsSubListPageSize', get_class($this->getModule()));
            $dataProvider = ReportDataProviderFactory::makeForSummationDrillDown($report, $pageSize);
            $dataProvider->setRunReport(true);
            $view         = new SummationDrillDownReportResultsGridView('default', 'reports', $dataProvider, $rowId);
            $content = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionExport($id, $stickySearchKey = null)
        {
            assert('$stickySearchKey == null || is_string($stickySearchKey)');
            $savedReport                    = SavedReport::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedReport->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedReport);
            $report                         = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            $dataProvider                   = $this->getDataProviderForExport($report, (int)$stickySearchKey, false);
            $totalItems                     = intval($dataProvider->calculateTotalItemCount());
            $data                           = array();
            if ($totalItems > 0)
            {
                if ($totalItems <= ExportModule::$asynchronusThreshold)
                {
                    // Output csv file directly to user browser
                    if ($dataProvider)
                    {
                        $data1      = $dataProvider->getData();
                        $headerData = array();
                        foreach ($data1 as $reportResultsRowData)
                        {
                          $reportToExportAdapter  = new ReportToExportAdapter($reportResultsRowData, $report);
                          if (count($headerData) == 0)
                          {
                              $headerData = $reportToExportAdapter->getHeaderData();
                          }
                          $data[] = $reportToExportAdapter->getData();
                        }
                    }
                    // Output data
                    if (count($data))
                    {
                        $fileName = $this->getModule()->getName() . ".csv";
                        ExportItemToCsvFileUtil::export($data, $headerData, $fileName, true);
                    }
                    else
                    {
                        Yii::app()->user->setFlash('notification',
                            Zurmo::t('ZurmoModule', 'There is no data to export.')
                        );
                    }
                }
                else
                {
                    if ($dataProvider)
                    {
                        $serializedData = serialize($dataProvider);
                    }
                    // Create background job
                    $exportItem                  = new ExportItem();
                    $exportItem->isCompleted     = 0;
                    $exportItem->exportFileType  = 'csv';
                    $exportItem->exportFileName  = $this->getModule()->getName();
                    $exportItem->modelClassName  = 'SavedReport';
                    $exportItem->serializedData  = $serializedData;
                    $exportItem->save();
                    $exportItem->forget();
                    Yii::app()->user->setFlash('notification',
                        Zurmo::t('ZurmoModule', 'A large amount of data has been requested for export.  You will receive ' .
                        'a notification with the download link when the export is complete.')
                    );
                }
            }
            else
            {
                Yii::app()->user->setFlash('notification',
                    Zurmo::t('ZurmoModule', 'There is no data to export.')
                );
            }
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionAutoComplete($term, $moduleClassName, $type)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                        'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ReportAutoCompleteUtil::getByPartialName($term, $pageSize, $moduleClassName, $type);
            echo CJSON::encode($autoCompleteResults);
        }

        protected function resolveCanCurrentUserAccessReports()
        {
            if (!RightsUtil::doesUserHaveAllowByRightName('ReportsModule',
                                                            ReportsModule::RIGHT_CREATE_REPORTS,
                                                            Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            return true;
        }

        protected function resolveSavedReportAndReportByPostData(Array $postData, & $savedReport, & $report, $type, $id = null)
        {
            if ($id == null)
            {
                $this->resolveCanCurrentUserAccessReports();
                $savedReport               = new SavedReport();
                $report                    = new Report();
                $report->setType($type);
            }
            else
            {
                $savedReport                = SavedReport::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedReport);
                $report                     = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
            }
            DataToReportUtil::resolveReportByWizardPostData($report, $postData,
                                                            ReportToWizardFormAdapter::getFormClassNameByType($type));
        }

        protected function resolveAfterSaveHasPermissionsProblem(SavedReport $savedReport, $modelToStringValue)
        {
            assert('is_string($modelToStringValue)');
            if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($savedReport, Permission::READ))
            {
                return false;
            }
            else
            {
                $notificationContent = Zurmo::t(
                    'ReportsModule',
                    'You no longer have permissions to access {modelName}.',
                    array('{modelName}' => $modelToStringValue)
                );
                Yii::app()->user->setFlash('notification', $notificationContent);
                return true;
            }
        }

        protected function actionValidate($postData, ReportWizardForm $model)
        {
            if (isset($postData['validationScenario']) && $postData['validationScenario'] != null)
            {
                $model->setScenario($postData['validationScenario']);
            }
            else
            {
                throw new NotSupportedException();
            }
            $model->validate();
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        protected function makeReportDetailsAndRelationsView(SavedReport $savedReport, $redirectUrl,
                                                             ReportBreadCrumbView $breadCrumbView)
        {
            $reportDetailsAndRelationsView = ReportDetailsAndResultsViewFactory::makeView($savedReport, $this->getId(),
                $this->getModule()->getId(),
                $redirectUrl);
            $gridView = new GridView(2, 1);
            $gridView->setView($breadCrumbView, 0, 0);
            $gridView->setView($reportDetailsAndRelationsView, 1, 0);
            return $gridView;
        }

        protected function getDataProviderForExport(Report $report, $stickyKey, $runReport)
        {
            assert('is_string($stickyKey) || is_int($stickyKey)');
            assert('is_bool($runReport)');
            if (null != $stickyData = StickyReportUtil::getDataByKey($stickyKey))
            {
                StickyReportUtil::resolveStickyDataToReport($report, $stickyData);
            }
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'reportResultsListPageSize', get_class($this->getModule()));
            $dataProvider = ReportDataProviderFactory::makeByReport($report, $pageSize);
            if ($runReport)
            {
                $dataProvider->setRunReport($runReport);
            }
            return $dataProvider;
        }

        protected function resolveMetadataBeforeMakingDataProvider(& $metadata)
        {
            $metadata = SavedReportUtil::resolveSearchAttributeDataByModuleClassNames($metadata,
                Report::getReportableModulesClassNamesCurrentUserHasAccessTo());
        }
    }
?>
