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
     * Default controller for all workflow actions
      */
    class WorkflowsDefaultController extends ZurmoBaseController
    {
        const USER_EMAIL_TEMPLATE_ACCESS_FILTER_PATH =
            'application.modules.workflows.controllers.filters.UserCanAccessEmailTemplatesForWorkflowCheckControllerFilter';

        const ZERO_MODELS_CHECK_FILTER_PATH = 'application.modules.workflows.controllers.filters.WorkflowZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('WorkflowsModule', 'Workflows');
            return array($title);
        }

        public static function getManageOrderBreadcrumbLinks()
        {
            $title = Zurmo::t('WorkflowsModule', 'Ordering');
            return array($title);
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                   array(
                        self::getRightsFilterPath() . ' + selectType',
                        'moduleClassName' => 'WorkflowsModule',
                        'rightName' => WorkflowsModule::RIGHT_CREATE_WORKFLOWS,
                   ),
                   array(
                        static::USER_EMAIL_TEMPLATE_ACCESS_FILTER_PATH,
                        'controller' => $this,
                   ),
                   array(
                       static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                       'controller' => $this,
                       'activeActionElementType' => 'WorkflowsLink',
                       'breadcrumbLinks'         => static::getListBreadcrumbLinks(),
                   ),
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + manageOrder',
                        'controller' => $this,
                        'activeActionElementType' => 'WorkflowManageOrderLink',
                        'breadcrumbLinks'         => static::getManageOrderBreadcrumbLinks(),
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
            $activeActionElementType        = 'WorkflowsLink';
            $savedWorkflow                    = new SavedWorkflow(false);
            $searchForm                     = new WorkflowsSearchForm($savedWorkflow);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize, null,
                                              'WorkflowsSearchView');
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new WorkflowsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', null, $activeActionElementType);
                $view = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                              makeViewWithBreadcrumbsForCurrentUser(
                                              $this, $mixedView, $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $savedWorkflow = static::getModelAndCatchNotFoundAndDisplayError('SavedWorkflow', intval($id));
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($savedWorkflow);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($savedWorkflow), 'WorkflowsModule'), $savedWorkflow);
            $breadcrumbLinks         = array(strval($savedWorkflow));
            $workflow                = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            $workflowToWizardFormAdapter = new WorkflowToWizardFormAdapter($workflow);
            $form = $workflowToWizardFormAdapter->makeFormByType();
            $detailsView             = new WorkflowDetailsView($this->getId(), $this->getModule()->getId(), $form);
            $view                    = new  WorkflowsPageView(  ZurmoDefaultAdminViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                            $this,
                                            $detailsView,
                                            $breadcrumbLinks,
                                            'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionSelectType()
        {
            $breadcrumbLinks  = array(Zurmo::t('WorkflowsModule', 'Select Workflow Type'));
            $view             = new WorkflowsPageView(  ZurmoDefaultAdminViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser(
                                                        $this,
                                                        new WorkflowWizardTypesGridView(),
                                                        $breadcrumbLinks,
                                                        'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate($type = null)
        {
            if ($type == null)
            {
                $this->actionSelectType();
                Yii::app()->end(0, false);
            }
            $breadcrumbLinks = array(Zurmo::t('WorkflowsModule', 'Create'));
            assert('is_string($type)');
            $workflow         = new Workflow();
            $workflow->setType($type);
            $workflow->setIsActive(true);
            $wizardWizardView = WorkflowWizardViewFactory::makeViewFromWorkflow($workflow);
            $view             = new WorkflowsPageView(  ZurmoDefaultAdminViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser(
                                                        $this,
                                                        $wizardWizardView,
                                                        $breadcrumbLinks,
                                                        'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $savedWorkflow      = SavedWorkflow::getById((int)$id);
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            $breadcrumbLinks    = array(strval($savedWorkflow));
            $workflow           = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            $wizardWizardView = WorkflowWizardViewFactory::makeViewFromWorkflow($workflow);
            $view             = new WorkflowsPageView(  ZurmoDefaultAdminViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser(
                                                        $this,
                                                        $wizardWizardView,
                                                        $breadcrumbLinks,
                                                        'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionSave($type, $id = null)
        {
            $postData                  = PostUtil::getData();
            $savedWorkflow             = null;
            $workflow                  = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $workflow, $type, $id);

            $workflowToWizardFormAdapter = new WorkflowToWizardFormAdapter($workflow);
            $model                     =  $workflowToWizardFormAdapter->makeFormByType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            if ($savedWorkflow->id > 0)
            {
                ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            }
            if ($savedWorkflow->save())
            {
                echo CJSON::encode(array('id'             => $savedWorkflow->id,
                                         'redirectToList' => false));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $postData      = PostUtil::getData();
            $savedWorkflow = null;
            $workflow        = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $workflow, $type, $id);
            if ($nodeId != null)
            {
                $wizardToTreeAdapter = new WorkflowRelationsAndAttributesToTreeAdapter($workflow, $treeType);
                echo ZurmoTreeView::saveDataAsJson($wizardToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new WorkflowRelationsAndAttributesTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber,
                                                   $trackableStructurePosition = false, $id = null)
        {
            $postData                           = PostUtil::getData();
            $savedWorkflow                      = null;
            $workflow                           = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $workflow, $type, $id);
            $nodeIdWithoutTreeType              = WorkflowRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $workflow->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = WorkflowToWizardFormAdapter::getFormClassNameByType($workflow->getType());
            $model                              = ComponentForWorkflowFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $workflow->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = WorkflowRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new WorkflowAttributeToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForWorkflowComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content               = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionDelete($id)
        {
            $savedWorkflow = SavedWorkflow::GetById(intval($id));
            ControllerSecurityUtil::resolveCanCurrentUserAccessModule($savedWorkflow->moduleClassName);
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedWorkflow);
            $savedWorkflow->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionGetAvailableAttributesForTimeTrigger($type, $id = null)
        {
            $postData                           = PostUtil::getData();
            $savedWorkflow                      = null;
            $workflow                           = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $workflow, $type, $id);
            $moduleClassName                    = $workflow->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $dataAndLabels                      = WorkflowUtil::resolveDataAndLabelsForTimeTriggerAvailableAttributes(
                                                  $moduleClassName, $modelClassName, $workflow->getType());
            echo CJSON::encode($dataAndLabels);
        }

        public function actionAddOrChangeTimeTriggerAttribute($type, $attributeIndexOrDerivedType, $moduleClassName,
                                                              $id = null)
        {
            $componentType                      = TimeTriggerForWorkflowForm::getType();
            $postData                           = PostUtil::getData();
            //Special situation since this is coming form GET
            $postData['ByTimeWorkflowWizardForm']['moduleClassName'] = $moduleClassName;
            $savedWorkflow                      = null;
            $workflow                           = null;
            $this->resolveSavedWorkflowAndWorkflowByPostData($postData, $savedWorkflow, $workflow, $type, $id);
            $moduleClassName                    = $workflow->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly
            $wizardFormClassName                = WorkflowToWizardFormAdapter::getFormClassNameByType($workflow->getType());
            $model                              = ComponentForWorkflowFormFactory::makeByComponentType($moduleClassName,
                                                  $modelClassName, $workflow->getType(), $componentType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $model->attributeIndexOrDerivedType = $attributeIndexOrDerivedType;
            $inputPrefixData                    = array($wizardFormClassName, $componentType);
            $adapter                            = new WorkflowAttributeToElementAdapter($inputPrefixData, $model,
                                                  $form, $componentType);
            $view                               = new AttributeRowForWorkflowComponentView($adapter,
                                                  1, $inputPrefixData, $attributeIndexOrDerivedType,
                                                  false, true, $componentType);
            $content               = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionChangeActionType($moduleClassName, $type)
        {
            $content = ZurmoHtml::dropDownList(ActionsForWorkflowWizardView::ACTION_TYPE_RELATION_NAME,
                        null, ActionsForWorkflowWizardView::resolveTypeRelationDataAndLabels(
                            $moduleClassName, $moduleClassName::getPrimaryModelName(), $type));
            echo $content;
        }

        public function actionChangeActionTypeRelatedModel($moduleClassName, $type, $relation)
        {
            $content = ZurmoHtml::dropDownList(ActionsForWorkflowWizardView::ACTION_TYPE_RELATED_MODEL_RELATION_NAME,
                                               null,
                                               ActionsForWorkflowWizardView::resolveTypeRelatedModelRelationDataAndLabels(
                                               $moduleClassName, $moduleClassName::getPrimaryModelName(), $type, $relation));
            echo $content;
        }

        public function actionAddAction($moduleClassName, $type, $actionType, $rowNumber, $relation = null,
                                        $relatedModelRelation = null)
        {
            $form                        = new WizardActiveForm();
            $form->enableAjaxValidation  = true; //ensures error validation populates correctly
            $wizardFormClassName         = WorkflowToWizardFormAdapter::getFormClassNameByType($type);
            $model                       = ComponentForWorkflowFormFactory::makeByComponentType($moduleClassName,
                                           $moduleClassName::getPrimaryModelName(), $type,
                                           ComponentForWorkflowForm::TYPE_ACTIONS);
            $model->type                 = $actionType;
            $model->relation             = $relation;
            $model->relatedModelRelation = $relatedModelRelation;
            $inputPrefixData             = array($wizardFormClassName, ComponentForWorkflowForm::TYPE_ACTIONS,
                                           (int)$rowNumber);
            $view                        = new ActionRowForWorkflowComponentView($model, (int)$rowNumber,
                                           $inputPrefixData, $form);
            $content                     = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionAddEmailMessage($moduleClassName, $type, $rowNumber)
        {
            $form                        = new WizardActiveForm();
            $form->enableAjaxValidation  = true; //ensures error validation populates correctly
            $rowCounterInputId           = ComponentForWorkflowWizardView::
                                           resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES);
            $wizardFormClassName         = WorkflowToWizardFormAdapter::getFormClassNameByType($type);
            $model                       = ComponentForWorkflowFormFactory::makeByComponentType($moduleClassName,
                                           $moduleClassName::getPrimaryModelName(), $type,
                                           ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES);
            $inputPrefixData             = array($wizardFormClassName, ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES,
                                           (int)$rowNumber);
            $form->setInputPrefixData($inputPrefixData);
            $view                        = new EmailMessageRowForWorkflowComponentView($model, (int)$rowNumber,
                                           $inputPrefixData, $form,
                                           WorkflowToWizardFormAdapter::getFormClassNameByType($type),
                                           $rowCounterInputId);
            $content                     = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionAddEmailMessageRecipient($moduleClassName, $type, $recipientType, $rowNumber,
                                                       $recipientRowNumber)
        {
            $form                        = new WizardActiveForm();
            $form->enableAjaxValidation  = true; //ensures error validation populates correctly
            $wizardFormClassName         = WorkflowToWizardFormAdapter::getFormClassNameByType($type);
            $model                       = WorkflowEmailMessageRecipientFormFactory::make($recipientType,
                                           $moduleClassName::getPrimaryModelName(), $type);
            $inputPrefixData             = array($wizardFormClassName, ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES,
                                           (int)$rowNumber, EmailMessageForWorkflowForm::TYPE_EMAIL_MESSAGE_RECIPIENTS,
                                           $recipientRowNumber);
            $adapter                     = new WorkflowEmailMessageRecipientToElementAdapter($model, $form,
                                           $recipientType, $inputPrefixData);
            $view                        = new EmailMessageRecipientRowForWorkflowComponentView($adapter,
                                           (int)$recipientRowNumber, $inputPrefixData);
            $content                     = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionManageOrder()
        {
            $activeActionElementType = 'WorkflowManageOrderLink';
            $actionBarView           = new SecuredActionBarForWorkflowsSearchAndListView(
                                            $this->getId(),
                                            $this->getModule()->getId(), new SavedWorkflow(),
                                            'list-view',
                                            'sequence',
                                            false,
                                            $activeActionElementType);
            $gridView                = new GridView(2, 1);
            $gridView->setView($actionBarView, 0, 0);
            $gridView->setView(new WorkflowManageOrderView(), 1, 0);
            $breadcrumbLinks         = static::getManageOrderBreadcrumbLinks();
            $view                    = new WorkflowsPageView(  ZurmoDefaultAdminViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                            $this,
                                            $gridView,
                                            $breadcrumbLinks,
                                            'WorkflowBreadCrumbView'));
            echo $view->render();
        }

        public function actionLoadOrderByModule($moduleClassName)
        {
            $savedWorkflows = SavedWorkflow::getAllByModuleClassName($moduleClassName);
            if (count($savedWorkflows) == 0)
            {
                echo CJSON::encode(array('dataToOrder' => 'false'));
                Yii::app()->end(0, false);
            }
            $view = new WorkflowManageOrderListView($savedWorkflows);
            $content               = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo CJSON::encode(array('content' => $content, 'dataToOrder' => 'true'));
        }

        public function actionSaveOrder()
        {
            $savedWorkflowData = ArrayUtil::getArrayValue(PostUtil::getData(), 'SavedWorkflow');
            if ($savedWorkflowData != null && isset($savedWorkflowData['savedWorkflowIds']) &&
               count($savedWorkflowData['savedWorkflowIds']) > 0)
            {
                $order = 1;
                foreach ($savedWorkflowData['savedWorkflowIds'] as $savedWorkflowId)
                {
                    $savedWorkflow        = SavedWorkflow::getById((int)$savedWorkflowId);
                    $savedWorkflow->order = $order;
                    $saved = $savedWorkflow->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                    $order++;
                }
                $message = Zurmo::t('WorkflowsModule', 'Order saved successfully.');
                echo CJSON::encode(array('message' => $message, 'type' => 'message'));
            }
            else
            {
                echo CJSON::encode(array('message' => Zurmo::t('Core', 'There was an error processing your request'),
                                         'type' => 'error'));
            }
            Yii::app()->end(0, false);
        }

        protected function resolveCanCurrentUserAccessWorkflows()
        {
            if (!RightsUtil::doesUserHaveAllowByRightName('WorkflowsModule',
                                                            WorkflowsModule::RIGHT_CREATE_WORKFLOWS,
                                                            Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            return true;
        }

        protected function resolveMetadataBeforeMakingDataProvider(& $metadata)
        {
            $metadata = SavedWorkflowsUtil::resolveSearchAttributeDataByModuleClassNames($metadata,
                Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo());
        }

        protected function resolveSavedWorkflowAndWorkflowByPostData(Array $postData, & $savedWorkflow, & $workflow,
                                                                     $type, $id = null)
        {
            if ($id == null)
            {
                $this->resolveCanCurrentUserAccessWorkflows();
                $savedWorkflow               = new SavedWorkflow();
                $workflow                    = new Workflow();
                $workflow->setType($type);
            }
            else
            {
                $savedWorkflow              = SavedWorkflow::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedWorkflow);
                $workflow                   = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            }
            DataToWorkflowUtil::resolveWorkflowByWizardPostData($workflow, $postData,
                                    WorkflowToWizardFormAdapter::getFormClassNameByType($type));
        }

        protected function actionValidate($postData, WorkflowWizardForm $model)
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
    }
?>
