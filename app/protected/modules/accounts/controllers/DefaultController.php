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

    class AccountsDefaultController extends ZurmoModuleController
    {
        public function actionList()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $account = new Account(false);
            $searchForm = new AccountsSearchForm($account);
            $dataProvider = $this->makeSearchFilterListDataProvider(
                $searchForm,
                'Account',
                'AccountsFilteredList',
                $pageSize,
                Yii::app()->user->userModel->id
            );
            $searchFilterListView = $this->makeSearchFilterListView(
                $searchForm,
                'AccountsFilteredList',
                $pageSize,
                AccountsModule::getModuleLabelByTypeAndLanguage('Plural'),
                Yii::app()->user->userModel->id,
                $dataProvider
            );
            $view = new AccountsPageView($this, $searchFilterListView);
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $account = Account::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($account);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, null, $account);
            $detailsAndRelationsView = $this->makeDetailsAndRelationsView($account, 'AccountsModule',
                                                                          'AccountDetailsAndRelationsView',
                                                                          Yii::app()->request->getRequestUri());
            $view = new AccountsPageView($this, $detailsAndRelationsView);
            echo $view->render();
        }

        public function actionCreate()
        {
            $titleBarAndEditView = $this->makeTitleBarAndEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new Account()), 'Edit');
            $view = new AccountsPageView($this, $titleBarAndEditView);
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $account = Account::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($account);
            $view = new AccountsPageView($this,
                $this->makeTitleBarAndEditAndDetailsView(
                            $this->attemptToSaveModelFromPost($account, $redirectUrl), 'Edit')
            );
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
            $account = new Account(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new AccountsSearchForm($account),
                'Account',
                $pageSize,
                Yii::app()->user->userModel->id,
                'AccountsFilteredList');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $account = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'AccountsPageView',
                $account,
                AccountsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $titleBarAndMassEditView = $this->makeTitleBarAndMassEditView(
                $account,
                $activeAttributes,
                $selectedRecordCount,
                AccountsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new AccountsPageView($this, $titleBarAndMassEditView);
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
            $account = new Account(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new AccountsSearchForm($account),
                'Account',
                $pageSize,
                Yii::app()->user->userModel->id,
                'AccountsFilteredList'
            );
            $this->processMassEditProgressSave(
                'Account',
                $pageSize,
                AccountsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::renderModalSearchList($this, $modalListLinkProvider,
                                                Yii::t('Default', 'AccountsModuleSingularLabel Search',
                                                LabelUtil::getTranslationParamsForAllModules()));
        }

        public function actionDelete($id)
        {
            $account = Account::GetById(intval($id));
            $account->delete();
            $this->redirect(array($this->getId() . '/index'));
        }
    }
?>
