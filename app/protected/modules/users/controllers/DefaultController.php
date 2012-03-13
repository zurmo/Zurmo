<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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

    class UsersDefaultController extends ZurmoModuleController
    {
        /**
         * Override to exclude modalSearchList and autoComplete
         * since these are available to all users regardless
         * of the access right on the users module.
         * Excludes details, edit, changePassword, and securityDetails
         * because these actions are checked using the
         * resolveCanCurrentUserAccessAction method.
         */
        public function filters()
        {
            $filters = array();
            $filters[] = array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH .
                    ' - modalList, autoComplete, details, profile, edit, auditEventsModalList, changePassword, configurationEdit, securityDetails',
                    'moduleClassName' => 'UsersModule',
                    'rightName' => UsersModule::getAccessRight(),
            );
            $filters[] = array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + create',
                    'moduleClassName' => 'UsersModule',
                    'rightName' => UsersModule::getCreateRight(),
            );
            $filters[] = array(
                ZurmoBaseController::RIGHTS_FILTER_PATH . ' + massEdit, massEditProgressSave',
                'moduleClassName' => 'ZurmoModule',
                'rightName' => ZurmoModule::RIGHT_BULK_WRITE,
            );
            return $filters;
        }

        public function actionList()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $searchFilterListView = $this->makeSearchAndListView(
                new UsersSearchForm(new User(false)),
                new User(false),
                'SearchAndListView',
                'Users',
                $pageSize,
                Yii::app()->user->userModel->id
            );
            $view = new UsersPageView($this, $searchFilterListView);
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, strval($user), $user);
            $params = array(
                'controllerId'     => $this->getId(),
                'relationModuleId' => $this->getModule()->getId(),
                'relationModel'    => $user,
            );
            $detailsAndRelationsView = new UserDetailsAndRelationsView($this->getId(),
                                                                       $this->getModule()->getId(),
                                                                       $user, $params);
            $view = new UsersPageView($this, $detailsAndRelationsView);
            echo $view->render();
        }

        public function actionAuditEventsModalList($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            parent::actionAuditEventsModalList($id);
        }

        public function actionCreate()
        {
            $user             = new User();
            $user->language   = Yii::app()->language;
            $user->currency   = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $user->setScenario('createUser');
            $userPasswordForm = new UserPasswordForm($user);
            $userPasswordForm->setScenario('createUser');
            $this->attemptToValidateAjaxFromPost($userPasswordForm, 'UserPasswordForm');
            $view = new UsersPageView($this,
                $this->makeTitleBarAndEditView(
                    $this->attemptToSaveModelFromPost($userPasswordForm),
                    'UserTitleBarAndCreateView'
                )
            );
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $this->attemptToValidateAjaxFromPost($user, 'User');
            if ($user == Yii::app()->user->userModel)
            {
                if (isset($_POST['User']) &&
                   !empty($_POST['User']['language']) &&
                   $_POST['User']['language'] != $user->language)
               {
                   $lang = $_POST['User']['language'];
               }
               else
               {
                   $lang = null;
               }
                $redirectUrlParams = array($this->getId() . '/details', 'id' => $user->id, 'lang' => $lang);
            }
            else
            {
                $redirectUrlParams = null;
            }
            $view = new UsersPageView($this,
                $this->makeTitleBarAndEditView(
                    $this->attemptToSaveModelFromPost($user, $redirectUrlParams),
                    'TitleBarAndEditView'
                )
            );
            echo $view->render();
        }

        public function actionChangePassword($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $user->setScenario('changePassword');
            $userPasswordForm = new UserPasswordForm($user);
            $userPasswordForm->setScenario('changePassword');
            $this->attemptToValidateAjaxFromPost($userPasswordForm, 'UserPasswordForm');
            $view = new UsersPageView($this,
                $this->makeTitleBarAndEditView(
                    $this->attemptToSaveModelFromPost($userPasswordForm),
                    'UserTitleBarAndChangePasswordView'
                )
            );
            echo $view->render();
        }

        /**
         * Override to handle UserStatus processing.
         * @see ZurmoBaseController::attemptToSaveModelFromPost()
         */
        protected function attemptToSaveModelFromPost($model, $redirectUrlParams = null, $redirect = true)
        {
            assert('$model instanceof User || $model instanceof UserPasswordForm');
            assert('$redirectUrlParams == null || is_array($redirectUrlParams) || is_string($redirectUrlParams)');
            $postVariableName   = get_class($model);
            if (isset($_POST[$postVariableName]))
            {
                $postData = $_POST[$postVariableName];
                if (isset($_POST[$postVariableName]['userStatus']))
                {
                    $userStatus        = UserStatusUtil::makeByPostData($_POST[$postVariableName]);
                    $sanitizedPostdata = UserStatusUtil::removeIfExistsFromPostData($postData);
                }
                else
                {
                    $userStatus        = null;
                    $sanitizedPostdata = $postData;
                }
                $savedSucessfully   = false;
                $modelToStringValue = null;
                $oldUsername        = $model->username;
                $model              = ZurmoControllerUtil::saveModelFromPost($sanitizedPostdata, $model,
                                                                           $savedSucessfully, $modelToStringValue);
                if ($savedSucessfully)
                {
                    if ($userStatus != null)
                    {
                        if ($model instanceof UserPasswordForm)
                        {
                            UserStatusUtil::resolveUserStatus($model->getModel(), $userStatus);
                        }
                        else
                        {
                            UserStatusUtil::resolveUserStatus($model, $userStatus);
                        }
                    }
                    if ($model->id == Yii::app()->user->userModel->id &&
                        $model->username != $oldUsername)
                    {
                        //If the logged in user changes their username, a logout must occur to properly to properly
                        //restart the session.
                        Yii::app()->getSession()->destroy();
                        $this->redirect(Yii::app()->homeUrl);
                    }
                    $this->actionAfterSuccessfulModelSave($model, $modelToStringValue, $redirectUrlParams);
                }
            }
            return $model;
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
            $user = new User(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new UsersSearchForm($user),
                'User',
                $pageSize,
                Yii::app()->user->userModel->id);
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $user = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'UsersPageView',
                $user,
                UsersModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $titleBarAndMassEditView = $this->makeTitleBarAndMassEditView(
                $user,
                $activeAttributes,
                $selectedRecordCount,
                UsersModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new UsersPageView($this, $titleBarAndMassEditView);
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
            $user = new User(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new UsersSearchForm($user),
                'User',
                $pageSize,
                Yii::app()->user->userModel->id
            );
            $this->processMassEditProgressSave(
                'User',
                $pageSize,
                UsersModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        public function actionProfile()
        {
            $this->actionDetails(Yii::app()->user->userModel->id);
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                                      Yii::t('Default', 'User Search'));
        }

        public function actionSecurityDetails($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $modulePermissionsData =  PermissionsUtil::getAllModulePermissionsDataByPermitable($user);
            $modulePermissionsForm = ModulePermissionsFormUtil::makeFormFromPermissionsData($modulePermissionsData);
            $viewReadyModulePermissionsData = GroupModulePermissionsDataToEditViewAdapater::resolveData($modulePermissionsData);
            $modulePermissionsViewMetadata = ModulePermissionsActualDetailsViewUtil::resolveMetadataFromData(
                $viewReadyModulePermissionsData,
                ModulePermissionsEditAndDetailsView::getMetadata());
            $rightsData =  RightsUtil::getAllModuleRightsDataByPermitable($user);
            $rightsForm = RightsFormUtil::makeFormFromRightsData($rightsData);
            $rightsViewMetadata = RightsEffectiveDetailsViewUtil::resolveMetadataFromData(
                $rightsData,
                RightsEditAndDetailsView::getMetadata());
            $policiesData =  PoliciesUtil::getAllModulePoliciesDataByPermitable($user);
            $policiesForm = PoliciesFormUtil::makeFormFromPoliciesData($policiesData);
            $policiesViewMetadata = PoliciesEffectiveDetailsViewUtil::resolveMetadataFromData(
                $policiesData,
                PoliciesEditAndDetailsView::getMetadata());
            $groupMembershipAdapter = new UserGroupMembershipToViewAdapter($user);
            $groupMembershipViewData = $groupMembershipAdapter->getViewData();
            $securityDetailsView = new UserTitleBarAndSecurityDetailsView(
                $this->getId(),
                $this->getModule()->getId(),
                $user,
                $modulePermissionsForm,
                $rightsForm,
                $policiesForm,
                $modulePermissionsViewMetadata,
                $rightsViewMetadata,
                $policiesViewMetadata,
                $groupMembershipViewData
            );
            $view = new UsersPageView($this, $securityDetailsView);
            echo $view->render();
        }

        public function actionConfigurationEdit($id)
        {
            $this->resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $configurationForm = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($user);
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    if ($user->id != Yii::app()->user->userModel->id)
                    {
                        UserConfigurationFormAdapter::setConfigurationFromForm($configurationForm, $user);
                    }
                    else
                    {
                        UserConfigurationFormAdapter::setConfigurationFromFormForCurrentUser($configurationForm);
                    }
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'User configuration saved successfully.')
                    );
                    $this->redirect(array($this->getId() . '/index'));
                }
            }
            $titleBarAndEditView = new UserTitleBarAndConfigurationEditView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $user,
                                    $configurationForm
            );
            $view = new UsersPageView($this, $titleBarAndEditView);
            echo $view->render();
        }

        protected function resolveCanCurrentUserAccessAction($userId)
        {
            if (Yii::app()->user->userModel->id == $userId ||
                RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                return;
            }
            $messageView = new AccessFailureView();
            $view = new AccessFailurePageView($messageView);
            echo $view->render();
            Yii::app()->end(0, false);
        }
    }
?>
