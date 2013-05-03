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

    class LeadsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH . ' + convert, saveConvertedContact',
                        'moduleClassName' => 'LeadsModule',
                        'rightName' => LeadsModule::RIGHT_CONVERT_LEADS,
                   ),
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => 'LeadEditAndDetailsView',
                   ),
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller'                    => $this,
                        'stateMetadataAdapterClassName' => 'LeadsStateMetadataAdapter'
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'listPageSize', get_class($this->getModule()));
            $contact                        = new Contact(false);
            $searchForm                     = new LeadsSearchForm($contact);
            $listAttributesSelector         = new ListAttributesSelector('LeadsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                'LeadsStateMetadataAdapter',
                'LeadsSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new LeadsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider);
                $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $contact = static::getModelAndCatchNotFoundAndDisplayError('Contact', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($contact);
            if (!LeadsUtil::isStateALead($contact->state))
            {
                $urlParams = array('/contacts/' . $this->getId() . '/details', 'id' => $contact->id);
                $this->redirect($urlParams);
            }
            else
            {
                AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($contact), 'LeadsModule'), $contact);
                $breadCrumbView          = StickySearchUtil::resolveBreadCrumbViewForDetailsControllerAction($this, 'LeadsSearchView', $contact);
                $detailsAndRelationsView = $this->makeDetailsAndRelationsView($contact, 'LeadsModule',
                                                                              'LeadDetailsAndRelationsView',
                                                                              Yii::app()->request->getRequestUri(),
                                                                              $breadCrumbView);
                $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $detailsAndRelationsView));
                echo $view->render();
            }
        }

        public function actionCreate()
        {
            $titleBarAndEditView = $this->makeEditAndDetailsView(
                    $this->attemptToSaveModelFromPost(new Contact()), 'Edit',
                    'LeadTitleBarAndEditAndDetailsView'
            );
            $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                     makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $contact = Contact::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($contact);
            if (!LeadsUtil::isStateALead($contact->state))
            {
                $urlParams = array('/contacts/' . $this->getId() . '/edit', 'id' => $contact->id);
                $this->redirect($urlParams);
            }
            else
            {
                $this->processEdit($contact, $redirectUrl);
            }
        }

        public function actionCopy($id)
        {
            $copyToContact  = new Contact();
            $postVariableName   = get_class($copyToContact);
            if (!isset($_POST[$postVariableName]))
            {
                $contact        = Contact::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($contact);
                ZurmoCopyModelUtil::copy($contact, $copyToContact);
            }
            $this->processEdit($copyToContact);
        }

        protected function processEdit(Contact $contact, $redirectUrl = null)
        {
            $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                     makeStandardViewForCurrentUser($this,
                                         $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($contact, $redirectUrl), 'Edit',
                                                        'LeadTitleBarAndEditAndDetailsView')));
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
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $contact = new Contact(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new LeadsSearchForm($contact),
                $pageSize,
                Yii::app()->user->userModel->id,
                'LeadsStateMetadataAdapter',
                'LeadsSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $contact = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'LeadsPageView',
                $contact,
                Zurmo::t('LeadsModule', 'Leads'),
                $dataProvider
            );
            $massEditView = $this->makeMassEditView(
                $contact,
                $activeAttributes,
                $selectedRecordCount,
                Zurmo::t('LeadsModule', 'Leads')
            );
            $view = new LeadsPageView(ZurmoDefaultViewUtil::
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
            $contact = new Contact(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new LeadsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'LeadsStateMetadataAdapter',
                            'LeadsSearchView');
            $this->processMassEditProgressSave(
                'Contact',
                $pageSize,
                Zurmo::t('LeadsModule', 'Leads'),
                $dataProvider
            );
        }

        /**
         * Action for displaying a mass delete form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to delete is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassDeleteProgressView.
         * In the mass delete progress view, a javascript refresh will take place that will call a refresh
         * action, usually makeMassDeleteProgressView.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the delete records.
         * @see Controler->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $contact = new Contact(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new LeadsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'LeadsStateMetadataAdapter',
                            'LeadsSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $contact = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'LeadsPageView',
                $contact,
                Zurmo::t('LeadsModule', 'Leads'),
                $dataProvider
            );
            $massDeleteView = $this->makeMassDeleteView(
                $contact,
                $activeAttributes,
                $selectedRecordCount,
                Zurmo::t('LeadsModule', 'Leads')
            );
            $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been delted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $contact = new Contact(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                            new LeadsSearchForm($contact),
                            $pageSize,
                            Yii::app()->user->userModel->id,
                            'LeadsStateMetadataAdapter',
                            'LeadsSearchView');
            $this->processMassDeleteProgress(
                'Contact',
                $pageSize,
                Zurmo::t('LeadsModule', 'Leads'),
                $dataProvider
            );
        }

        public function actionConvert($id)
        {
            assert('!empty($id)');
            $contact                 = Contact::getById(intval($id));
            if (!LeadsUtil::isStateALead($contact->state))
            {
                $urlParams = array('/contacts/' . $this->getId() . '/details', 'id' => $contact->id);
                $this->redirect($urlParams);
            }
            $convertToAccountSetting = LeadsModule::getConvertToAccountSetting();
            $selectAccountForm       = new AccountSelectForm();
            $account                 = new Account();
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($contact);

            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', Yii::app()->user->userModel);
            $userCanAccessAccounts = RightsUtil::canUserAccessModule('AccountsModule', Yii::app()->user->userModel);
            $userCanCreateAccount  = RightsUtil::doesUserHaveAllowByRightName('AccountsModule',
                                     AccountsModule::RIGHT_CREATE_ACCOUNTS, Yii::app()->user->userModel);
            LeadsControllerSecurityUtil::
            resolveCanUserProperlyConvertLead($userCanAccessContacts, $userCanAccessAccounts, $convertToAccountSetting);
            if (isset($_POST['AccountSelectForm']))
            {
                $selectAccountForm->setAttributes($_POST['AccountSelectForm']);
                if ($selectAccountForm->validate())
                {
                    $account = Account::getById(intval($selectAccountForm->accountId));
                    $this->actionSaveConvertedContact($contact, $account);
                }
            }
            elseif (isset($_POST['Account']))
            {
                $account = LeadsUtil::attributesToAccountWithNoPostData($contact, $account, $_POST['Account']);
                $account->setAttributes($_POST['Account']);
                if ($account->save())
                {
                    $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($contact);
                    ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($account, $explicitReadWriteModelPermissions);
                    if (!$account->save())
                    {
                        throw new NotSupportedException();
                    }
                    $this->actionSaveConvertedContact($contact, $account);
                }
            }
            elseif (isset($_POST['AccountSkip']) ||
                $convertToAccountSetting == LeadsModule::CONVERT_NO_ACCOUNT ||
                ($convertToAccountSetting == LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED && !$userCanAccessAccounts)
                )
            {
                $this->actionSaveConvertedContact($contact);
            }
            else
            {
                $account = LeadsUtil::attributesToAccount($contact, $account);
            }
            $convertView = new LeadConvertView(
                $this->getId(),
                $this->getModule()->getId(),
                $contact->id,
                strval($contact),
                $selectAccountForm,
                $account,
                $convertToAccountSetting,
                $userCanCreateAccount
            );
            $view = new LeadsPageView(ZurmoDefaultViewUtil::
                                     makeStandardViewForCurrentUser($this, $convertView));
            echo $view->render();
        }

        protected function actionSaveConvertedContact($contact, $account = null)
        {
            $contact->account = $account;
            $contact->state   = ContactsUtil::getStartingState();
            if ($contact->save())
            {
                Yii::app()->user->setFlash('notification',
                    Zurmo::t('LeadsModule', 'LeadsModuleSingularLabel successfully converted.',
                                           LabelUtil::getTranslationParamsForAllModules())
                );
                $this->redirect(array('/contacts/default/details', 'id' => $contact->id));
            }
            Yii::app()->user->setFlash('notification',
                Zurmo::t('LeadsModule', 'LeadsModuleSingularLabel was not converted. An error occurred.')
            );
            $this->redirect(array('default/details', 'id' => $contact->id));
            Yii::app()->end(0, false);
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                'LeadsStateMetadataAdapter');
        }

        public function actionDelete($id)
        {
            $contact = Contact::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($contact);
            if (!LeadsUtil::isStateALead($contact->state))
            {
                $urlParams = array('/contacts/' . $this->getId() . '/delete', 'id' => $contact->id);
                $this->redirect($urlParams);
            }
            else
            {
                $contact->delete();
                $this->redirect(array($this->getId() . '/index'));
            }
        }

        /**
         * Override to always add contact state filter on search results.
         */
        public function actionAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ContactAutoCompleteUtil::getByPartialName($term, $pageSize, 'LeadsStateMetadataAdapter');
            echo CJSON::encode($autoCompleteResults);
        }

        protected function makeEditAndDetailsView($model, $renderType)
        {
            assert('$model != null');
            assert('$renderType == "Edit" || $renderType == "Details"');
            $editViewClassName = 'LeadEditAndDetailsView';
            return new $editViewClassName($renderType, $this->getId(), $this->getModule()->getId(), $model);
        }

        protected static function getSearchFormClassName()
        {
            return 'LeadsSearchForm';
        }

        public function actionExport()
        {
            $this->export('LeadsSearchView');
        }
    }
?>
