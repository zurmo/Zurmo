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

    class EmailTemplatesDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForWorkflowZeroModelsCheckControllerFilter';

        const ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForMarketingZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('EmailTemplatesModule', 'Templates');
            return array($title);
        }

        public static function getDetailsAndEditForWorkflowBreadcrumbLinks()
        {
            return array(Zurmo::t('EmailTemplatesModule', 'Templates') =>
                         array('default/listForWorkflow'));
        }

        public static function getDetailsAndEditForMarketingBreadcrumbLinks()
        {
            return array(Zurmo::t('EmailTemplatesModule', 'Templates') =>
            array('default/listForMarketing'));
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH . ' + listForMarketing',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForMarketingLinkActionElement::getType(),
                        'breadcrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForMarketingStateMetadataAdapter'
                    ),
                    array(
                        static::ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH . ' + listForWorkflow',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForWorkflowLinkActionElement::getType(),
                        'breadcrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForWorkflowStateMetadataAdapter'
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionListForMarketing();
        }

        public function actionListForMarketing()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                            'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = EmailTemplatesForMarketingLinkActionElement::getType();
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                              'EmailTemplatesForMarketingStateMetadataAdapter',
                                              'EmailTemplatesSearchView');
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new EmailTemplatesPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForMarketingSearchAndListView', null, $activeActionElementType);
                $view      = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                             makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks, 'MarketingBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionListForWorkflow()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = EmailTemplatesForWorkflowLinkActionElement::getType();
            $emailTemplate                  = new EmailTemplate(false);
            $searchForm                     = new EmailTemplatesSearchForm($emailTemplate);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                              'EmailTemplatesForWorkflowStateMetadataAdapter',
                                              'EmailTemplatesSearchView');
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new EmailTemplatesPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', null, $activeActionElementType);
                $view      = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                             makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionCreate($type)
        {
            $type = (int)$type;
            $emailTemplate       = new EmailTemplate();
            $emailTemplate->type = $type;
            $editAndDetailsView  = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate), 'Edit');
            if ($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadcrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadcrumbLinks[]  = Zurmo::t('EmailTemplatesModule', 'Create');
                $view               = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->type == EmailTemplate::TYPE_CONTACT)
            {
                $emailTemplate->modelClassName = 'Contact';
                $breadcrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadcrumbLinks[]  = Zurmo::t('EmailTemplatesModule', 'Create');
                $view               = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($emailTemplate);

            $editAndDetailsView = $this->makeEditAndDetailsView($this->attemptToSaveModelFromPost($emailTemplate, $redirectUrl), 'Edit');
            if ($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadcrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view               = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->type == EmailTemplate::TYPE_CONTACT)
            {
                $breadcrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view               = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                                      makeViewWithBreadcrumbsForCurrentUser($this, $editAndDetailsView,
                                      $breadcrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($emailTemplate),
                                        'EmailTemplatesModule'), $emailTemplate);
            $detailsView              = new EmailTemplateEditAndDetailsView('Details', $this->getId(),
                                                                            $this->getModule()->getId(), $emailTemplate);

            if ($emailTemplate->type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadcrumbLinks          = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
                $breadcrumbLinks[]        = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view                     = new EmailTemplatesPageView(WorkflowDefaultAdminViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                            $breadcrumbLinks, 'WorkflowBreadCrumbView'));
            }
            elseif ($emailTemplate->type == EmailTemplate::TYPE_CONTACT)
            {
                $breadcrumbLinks          = static::getDetailsAndEditForMarketingBreadcrumbLinks();
                $breadcrumbLinks[]        = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
                $view                     = new EmailTemplatesPageView(MarketingDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this, $detailsView,
                                            $breadcrumbLinks, 'MarketingBreadCrumbView'));
            }
            else
            {
                throw new NotSupportedException();
            }
            echo $view->render();
        }

        protected static function getSearchFormClassName()
        {
            return 'EmailTemplatesSearchForm';
        }

        public function actionDelete($id)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($emailTemplate);
            $type          = $emailTemplate->type;
            $emailTemplate->delete();
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $this->redirect(array($this->getId() . '/listForWorkflow'));
            }
            elseif ($emailTemplate->type == EmailTemplate::TYPE_CONTACT)
            {
                $this->redirect(array($this->getId() . '/listForMarketing'));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionMergeTagGuide()
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, new MergeTagGuideView());
            echo $view->render();
        }
    }
?>
