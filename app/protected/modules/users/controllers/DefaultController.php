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
        const EMAIL_CONFIGURATION_FILTER_PATH =
              'application.modules.emailMessages.controllers.filters.EmailConfigurationCheckControllerFilter';

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
                    ' - modalList, autoComplete, details, profile, edit, auditEventsModalList, changePassword, configurationEdit, emailConfiguration, securityDetails, ' .
                        'autoCompleteForMultiSelectAutoComplete, confirmTimeZone, changeAvatar',
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
            $filters[] = array(
                        self::EMAIL_CONFIGURATION_FILTER_PATH . ' + emailConfiguration',
                         'controller' => $this,
            );
            return $filters;
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $user                           = new User(false);
            $searchForm                     = new UsersSearchForm($user);
            $listAttributesSelector         = new ListAttributesSelector('UsersListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'UsersSearchView'
            );
            $title           = Zurmo::t('UsersModule', 'Users');
            $breadcrumbLinks = array(
                 $title,
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new UsersPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider, 'UsersActionBarForSearchAndListView');
                $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadcrumbLinks, 'UserBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionChangeAvatar($id)
        {
            if (Yii::app()->user->userModel->id == intval($id) ||
                RightsUtil::canUserAccessModule('UsersModule', Yii::app()->user->userModel))
            {
                $user                 = User::getById(intval($id));
                $userAvatarForm       = new UserAvatarForm($user);
                $this->attemptToValidateAjaxFromPost($userAvatarForm, 'UserAvatarForm');
                $viewForModal = new UserChangeAvatarView($this->getId(), $this->getModule()->getId(), $userAvatarForm);
                $this->attemptToSaveModelFromPost($userAvatarForm);
            }
            else
            {
                $viewForModal = new AccessFailureView();
            }

            $view = new ModalView($this, $viewForModal);
            Yii::app()->getClientScript()->setToAjaxMode();
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $user = User::getById(intval($id));
            $title           = Zurmo::t('UsersModule', 'Profile');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($user), 'UsersModule'), $user);
            $params = array(
                'controllerId'     => $this->getId(),
                'relationModuleId' => $this->getModule()->getId(),
                'relationModel'    => $user,
                'rankingData'      => GamePointUtil::getUserRankingData($user),
                'statisticsData'   => GameLevelUtil::getUserStatisticsData($user),
                'badgeData'        => GameBadge::getAllByPersonIndexedByType($user)
            );
            $detailsAndRelationsView = new UserDetailsAndRelationsView($this->getId(),
                                                                       $this->getModule()->getId(),
                                                                       $params);
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $detailsAndRelationsView, $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionAuditEventsModalList($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            parent::actionAuditEventsModalList($id);
        }

        public function actionCreate()
        {
            $title           = Zurmo::t('UsersModule', 'Create User');
            $breadcrumbLinks = array($title);
            $user             = new User();
            $user->language   = Yii::app()->language;
            $user->currency   = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $user->setScenario('createUser');
            $userPasswordForm = new UserPasswordForm($user);
            $userPasswordForm->setScenario('createUser');
            $this->attemptToValidateAjaxFromPost($userPasswordForm, 'UserPasswordForm');
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this,
                                             $this->makeTitleBarAndEditView(
                                                $this->attemptToSaveModelFromPost($userPasswordForm),
                                                    'UserCreateView'), $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            $user            = User::getById(intval($id));
            $user->setScenario('editUser');
            $title           = Zurmo::t('UsersModule', 'Details');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
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
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this,
                                             $this->makeTitleBarAndEditView(
                                                $this->attemptToSaveModelFromPost($user, $redirectUrlParams),
                                                    'UserActionBarAndEditView'), $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionChangePassword($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $title           = Zurmo::t('UsersModule', 'Change Password');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
            $user->setScenario('changePassword');
            $userPasswordForm = new UserPasswordForm($user);
            $userPasswordForm->setScenario('changePassword');
            $this->attemptToValidateAjaxFromPost($userPasswordForm, 'UserPasswordForm');
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this,
                                             $this->makeTitleBarAndEditView(
                                                $this->attemptToSaveModelFromPost($userPasswordForm),
                                                'UserActionBarAndChangePasswordView'), $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionConfirmTimeZone()
        {
            $confirmTimeZoneForm           = new UserTimeZoneConfirmationForm();
            $confirmTimeZoneForm->timeZone = Yii::app()->timeZoneHelper->getForCurrentUser();
            if (isset($_POST['UserTimeZoneConfirmationForm']))
            {
                $confirmTimeZoneForm->attributes = $_POST['UserTimeZoneConfirmationForm'];
                if ($confirmTimeZoneForm->validate())
                {
                    Yii::app()->user->userModel->timeZone = $confirmTimeZoneForm->timeZone;
                    if (Yii::app()->user->userModel->save())
                    {
                        Yii::app()->timeZoneHelper->confirmCurrentUsersTimeZone();
                        $this->redirect(Yii::app()->homeUrl);
                    }
                }
            }
            $title                         = Zurmo::t('UsersModule', 'Confirm your time zone');
            $timeZoneView                  = new UserTimeZoneConfirmationView($this->getId(),
                                                                 $this->getModule()->getId(),
                                                                 $confirmTimeZoneForm,
                                                                 $title);
            $view                          = new UsersPageView(ZurmoDefaultViewUtil::
                                                 makeStandardViewForCurrentUser($this, $timeZoneView));
            echo $view->render();
        }

        /**
         * Override to handle UserStatus processing.
         * @see ZurmoBaseController::attemptToSaveModelFromPost()
         */
        protected function attemptToSaveModelFromPost($model, $redirectUrlParams = null, $redirect = true)
        {
            assert('$model instanceof User || $model instanceof UserPasswordForm || $model instanceof UserAvatarForm');
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
                $controllerUtil     = new ZurmoControllerUtil();
                $model              = $controllerUtil->saveModelFromPost($sanitizedPostdata, $model,
                                                                           $savedSucessfully, $modelToStringValue);
                if ($savedSucessfully)
                {
                    if ($userStatus != null)
                    {
                        if ($model instanceof UserPasswordForm || $model instanceof UserAvatarForm)
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
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            null,
                            'UsersSearchView');
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
            $massEditView = $this->makeMassEditView(
                $user,
                $activeAttributes,
                $selectedRecordCount,
                UsersModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $massEditView));
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
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            null,
                           'UsersSearchView');
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
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider);
        }

        public function actionSecurityDetails($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $title           = Zurmo::t('UsersModule', 'Security Overview');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
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
            $securityDetailsView = new UserActionBarAndSecurityDetailsView(
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
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $securityDetailsView, $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionConfigurationEdit($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            $user = User::getById(intval($id));
            $title           = Zurmo::t('UsersModule', 'Configuration');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
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
                        Zurmo::t('UsersModule', 'User configuration saved successfully.')
                    );
                    $this->redirect(array($this->getId() . '/details', 'id' => $user->id));
                }
            }
            $titleBarAndEditView = new UserActionBarAndConfigurationEditView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $user,
                                    $configurationForm
            );
            $titleBarAndEditView->setCssClasses(array('AdministrativeArea'));
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView, $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        public function actionEmailConfiguration($id)
        {
            UserAccessUtil::resolveCanCurrentUserAccessAction(intval($id));
            $user  = User::getById(intval($id));
            $title = Zurmo::t('UsersModule', 'Email Configuration');
            $breadcrumbLinks = array(strval($user) => array('default/details',  'id' => $id), $title);
            $emailAccount = EmailAccount::resolveAndGetByUserAndName($user);
            $userEmailConfigurationForm = new UserEmailConfigurationForm($emailAccount);
            $userEmailConfigurationForm->emailSignatureHtmlContent = $user->getEmailSignature()->htmlContent;
            $postVariableName           = get_class($userEmailConfigurationForm);

            if (isset($_POST[$postVariableName]))
            {
                $userEmailConfigurationForm->setAttributes($_POST[$postVariableName]);
                if ($userEmailConfigurationForm->validate())
                {
                    $userEmailConfigurationForm->save();
                    Yii::app()->user->setFlash('notification',
                        Zurmo::t('UsersModule', 'User email configuration saved successfully.')
                    );
                    $this->redirect(array($this->getId() . '/details', 'id' => $user->id));
                }
            }
            $titleBarAndEditView = new UserActionBarAndEmailConfigurationEditView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $user,
                                    $userEmailConfigurationForm
            );
            $titleBarAndEditView->setCssClasses(array('AdministrativeArea'));
            $view = new UsersPageView(ZurmoDefaultAdminViewUtil::
                                         makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView, $breadcrumbLinks, 'UserBreadCrumbView'));
            echo $view->render();
        }

        protected static function getSearchFormClassName()
        {
            return 'UsersSearchForm';
        }

        public function actionExport()
        {
            $this->export('UsersSearchView');
        }

        /**
         * Given a partial name or e-mail address, search for all contacts regardless of contact state unless the
         * current user has security restrictions on some states.  If the adapter resolver returns false, then the
         * user does not have access to the Leads or Contacts module.
         * JSON encode the resulting array of contacts.
         */
        public function actionAutoCompleteForMultiSelectAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $users    = UserSearch::getUsersByPartialFullName($term, $pageSize);
            $autoCompleteResults  = array();
            foreach ($users as $user)
            {
                $autoCompleteResults[] = array(
                    'id'   => $user->getClassId('Item'),
                    'name' => strval($user)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }
    }
?>
