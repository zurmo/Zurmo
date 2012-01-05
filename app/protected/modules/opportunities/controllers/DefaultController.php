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

    class OpportunitiesDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $opportunity = new Opportunity(false);
            $searchForm = new OpportunitiesSearchForm($opportunity);
            $dataProvider = $this->makeSearchFilterListDataProvider(
                $searchForm,
                'Opportunity',
                'OpportunitiesFilteredList',
                $pageSize,
                Yii::app()->user->userModel->id
            );
            $searchFilterListView = $this->makeSearchFilterListView(
                $searchForm,
                'OpportunitiesFilteredList',
                $pageSize,
                OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural'),
                Yii::app()->user->userModel->id,
                $dataProvider
            );
            $view = new OpportunitiesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $searchFilterListView));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $opportunity = Opportunity::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($opportunity);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($opportunity), $opportunity);
            $detailsAndRelationsView = $this->makeDetailsAndRelationsView($opportunity, 'OpportunitiesModule',
                                                                          'OpportunityDetailsAndRelationsView',
                                                                          Yii::app()->request->getRequestUri());
            $view = new OpportunitiesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
            echo $view->render();
        }

        public function actionCreate()
        {
            $this->actionCreateByModel(new Opportunity());
        }

        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $opportunity = $this->resolveNewModelByRelationInformation( new Opportunity(),
                                                                                $relationAttributeName,
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            if ($relationAttributeName == 'contacts')
            {
                $relationContact = Contact::getById((int)$relationModelId);
                if ($relationContact->account->id > 0)
                {
                    $opportunity->account = $relationContact->account;
                }
            }
            $this->actionCreateByModel($opportunity, $redirectUrl);
        }

        protected function actionCreateByModel(Opportunity $opportunity, $redirectUrl = null)
        {
            $titleBarAndEditView = $this->makeTitleBarAndEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($opportunity, $redirectUrl), 'Edit');
            $view = new OpportunitiesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $opportunity = Opportunity::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($opportunity);
            $view = new OpportunitiesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this,
                                             $this->makeTitleBarAndEditAndDetailsView(
                                                        $this->attemptToSaveModelFromPost($opportunity, $redirectUrl),
                                                        'Edit')));
            echo $view->render();
        }

        /**
         * Action for displaying a mass edit form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to update is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassEditProgressView.
         * In the mass edit progress view, a javascript refresh will take place that will call a refresh
         * action, usually massEditProgressSave.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the updated records.
         * @see Controler->makeMassEditProgressView
         * @see Controller->processMassEdit
         * @see
         */
        public function actionMassEdit()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $opportunity = new Opportunity(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new OpportunitiesSearchForm($opportunity),
                'Opportunity',
                $pageSize,
                Yii::app()->user->userModel->id,
                'OpportunitiesFilteredList');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $opportunity = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'OpportunitiesPageView',
                $opportunity,
                OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $titleBarAndMassEditView = $this->makeTitleBarAndMassEditView(
                $opportunity,
                $activeAttributes,
                $selectedRecordCount,
                OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new OpportunitiesPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndMassEditView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass edit quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been updated and continues to be
         * called until the mass edit action is complete.  For example, if there are 20 records to update
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassEdit is called upon the initial form submission.
         */
        public function actionMassEditProgressSave()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $opportunity = new Opportunity(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new OpportunitiesSearchForm($opportunity),
                'Opportunity',
                $pageSize,
                Yii::app()->user->userModel->id,
                'OpportunitiesFilteredList'
            );
            $this->processMassEditProgressSave(
                'Opportunity',
                $pageSize,
                OpportunitiesModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                Yii::t('Default', 'OpportunitiesModuleSingularLabel Search',
                                                LabelUtil::getTranslationParamsForAllModules()));
        }

        public function actionDelete($id)
        {
            $opportunity = Opportunity::GetById(intval($id));
            $opportunity->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        /**
         * Override to provide an opportunity specific label for the modal page title.
         * @see ZurmoModuleController::actionSelectFromRelatedList()
         */
        public function actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $pageTitle = null,
                                                    $stateMetadataAdapterClassName = null)
        {
            $pageTitle = Yii::t('Default',
                                'OpportunitiesModuleSingularLabel Search',
                                 LabelUtil::getTranslationParamsForAllModules());
            parent::actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $pageTitle);
        }
    }
?>
