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

    class ZurmoGroupController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH,
                    'moduleClassName' => 'GroupsModule',
               ),
            );
        }

        public function resolveAndGetModuleId()
        {
            return 'groups';
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $treeView = new GroupsActionBarAndTreeListView(
                $this->getId(),
                $this->getModule()->getId(),
                Group::getAll('name')
            );
            $view             = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $treeView));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $group = Group::getById(intval($id));
            if (!$group->isEveryone && !$group->isSuperAdministrators)
            {
                $this->redirect(array($this->getId() . '/edit', 'id' => $id));
            }
            else
            {
                $this->redirect(array($this->getId() . '/editPolicies', 'id' => $id));
            }

        }

        public function actionCreate()
        {
            $titleBarAndCreateView = new GroupActionBarAndEditView($this->getId(), $this->getModule()->getId(),
                                                                   $this->attemptToSaveModelFromPost(new Group()));
            $view                  = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndCreateView));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $group               = Group::getById(intval($id));
            $this->resolveCanGroupBeEdited($group);
            $titleBarAndEditView = new GroupActionBarAndEditView($this->getId(),
                                                                 $this->getModule()->getId(),
                                                                 $this->attemptToSaveModelFromPost($group));
            $view                = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                       makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionModalList()
        {
            $groupsModalTreeView = new SelectParentGroupModalTreeListView(
                $this->getId(),
                $this->getModule()->getId(),
                $_GET['modalTransferInformation']['sourceModelId'],
                Group::getAll('name'),
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId']
            );
            $pageTitle           = Yii::t('Default', 'Select a Parent Group');
            $view                = new ModalView($this,
                                        $groupsModalTreeView,
                                        'modalContainer',
                                        $pageTitle);
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $group = Group::GetById(intval($id));
            $group->users->removeAll();
            $group->groups->removeAll();
            $group->save();
            $group->delete();
            unset($group);
            $this->redirect(array($this->getId() . '/index'));
        }

        public function actionEditUserMembership($id)
        {
            $group              = Group::getById(intval($id));
            $membershipForm     = GroupUserMembershipFormUtil::makeFormFromGroup($group);
            $postVariableName   = get_class($membershipForm);
            if (isset($_POST[$postVariableName]))
            {
                $castedPostData = GroupUserMembershipFormUtil::typeCastPostData($_POST[$postVariableName]);
                GroupUserMembershipFormUtil::setFormFromCastedPost($membershipForm, $castedPostData);
                if (GroupUserMembershipFormUtil::setMembershipFromForm($membershipForm, $group))
                {
                        Yii::app()->user->setFlash('notification',
                            Yii::t('Default', 'User Membership Saved Successfully.')
                        );
                        $this->redirect(array($this->getId() . '/details', 'id' => $group->id));
                        Yii::app()->end(0, false);
                }
            }
            $titleBarAndEditView = new GroupActionBarAndUserMembershipEditView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            $membershipForm,
                                            $group,
                                            $this->getModule()->getPluralCamelCasedName());
            $view                = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEditModulePermissions($id)
        {
            $group            = Group::getById(intval($id));
            $data             =  PermissionsUtil::getAllModulePermissionsDataByPermitable($group);
            $permissionsForm  = ModulePermissionsFormUtil::makeFormFromPermissionsData($data);
            $postVariableName = get_class($permissionsForm);
            if (isset($_POST[$postVariableName]))
            {
                $castedPostData     = ModulePermissionsFormUtil::typeCastPostData(
                                        $_POST[$postVariableName]);
                $readyToSetPostData = ModulePermissionsEditViewUtil::resolveWritePermissionsFromArray(
                                        $castedPostData);
                if (ModulePermissionsFormUtil::setPermissionsFromCastedPost($readyToSetPostData, $group))
                {
                        Yii::app()->user->setFlash('notification',
                            Yii::t('Default', 'Module Permissions Saved Successfully.')
                        );
                        $this->redirect(array($this->getId() . '/details', 'id' => $group->id));
                        Yii::app()->end(0, false);
                }
            }
            $permissionsData     = GroupModulePermissionsDataToEditViewAdapater::resolveData($data);
            $metadata            = ModulePermissionsEditViewUtil::resolveMetadataFromData(
                                        $permissionsData,
                                        ModulePermissionsEditAndDetailsView::getMetadata());
            $titleBarAndEditView = new GroupActionBarAndSecurityEditView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            $permissionsForm,
                                            $group,
                                            $this->getModule()->getPluralCamelCasedName(),
                                            $metadata,
                                            'ModulePermissionsEditAndDetailsView',
                                            'GroupModulePermissionsEditLink');
            $view                = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEditRights($id)
        {
            $group              = Group::getById(intval($id));
            $rightsData         = RightsUtil::getAllModuleRightsDataByPermitable($group);
            $rightsForm         = RightsFormUtil::makeFormFromRightsData($rightsData);
            $postVariableName   = get_class($rightsForm);
            if (isset($_POST[$postVariableName]))
            {
                $castedPostData = RightsFormUtil::typeCastPostData($_POST[$postVariableName]);
                if (RightsFormUtil::setRightsFromCastedPost($castedPostData, $group))
                {
                    PermissionsCache::forgetAll();
                    $group->forget();
                    $group      = Group::getById(intval($id));
                    Yii::app()->user->setFlash('notification', Yii::t('Default', 'Rights Saved Successfully.'));
                    $this->redirect(array($this->getId() . '/details', 'id' => $group->id));
                    Yii::app()->end(0, false);
                }
            }
            $metadata            = RightsEditViewUtil::resolveMetadataFromData(
                                            $rightsForm->data,
                                            RightsEditAndDetailsView::getMetadata());
            $titleBarAndEditView = new GroupActionBarAndSecurityEditView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            $rightsForm,
                                            $group,
                                            $this->getModule()->getPluralCamelCasedName(),
                                            $metadata,
                                            'RightsEditAndDetailsView',
                                            'GroupRightsEditLink');
            $view                = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEditPolicies($id)
        {
            $group              = Group::getById(intval($id));
            $data               = PoliciesUtil::getAllModulePoliciesDataByPermitable($group);
            $policiesForm       = PoliciesFormUtil::makeFormFromPoliciesData($data);
            $postVariableName   = get_class($policiesForm);
            if (isset($_POST[$postVariableName]))
            {
                $castedPostData = PoliciesFormUtil::typeCastPostData($_POST[$postVariableName]);
                $policiesForm   = PoliciesFormUtil::loadFormFromCastedPost($policiesForm, $castedPostData);
                if ($policiesForm->validate())
                {
                    if (PoliciesFormUtil::setPoliciesFromCastedPost($castedPostData, $group))
                    {
                        PermissionsCache::forgetAll();
                        Yii::app()->user->setFlash('notification',
                            Yii::t('Default', 'Policies Saved Successfully.')
                        );
                        $this->redirect(array($this->getId() . '/details', 'id' => $group->id));
                        Yii::app()->end(0, false);
                    }
                }
            }
            $metadata            = PoliciesEditViewUtil::resolveMetadataFromData(
                                        $policiesForm->data,
                                        PoliciesEditAndDetailsView::getMetadata());
            $titleBarAndEditView = new GroupActionBarAndSecurityEditView(
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        $policiesForm,
                                        $group,
                                        $this->getModule()->getPluralCamelCasedName(),
                                        $metadata,
                                        'PoliciesEditAndDetailsView',
                                        'GroupPoliciesEditLink');
            $view                = new GroupsPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        /**
         * Override to support special scenario of checking for
         * a reserved name.  Cannot use normal validate routine since
         * the _set is blocking the entry of a reserved name and _set is used
         * by setAttributes which comes before validate is called.
         */
        protected function attemptToSaveModelFromPost($model, $redirectUrlParams = null, $redirect = true)
        {
            assert('$redirectUrlParams == null || is_array($redirectUrlParams)');
            $postVariableName = get_class($model);
            if (isset($_POST[$postVariableName]))
            {
                if ($model->isNameNotAReservedName($_POST[$postVariableName]['name']))
                {
                    $model->setAttributes($_POST[$postVariableName]);
                    if ($model->save())
                    {
                        $this->redirectAfterSaveModel($model->id, $redirectUrlParams);
                    }
                }
            }
            return $model;
        }

        /**
         * Override to make sure the correct module label is used in the titlebar.
         * @see Controller::makeTitleBarAndEditAndDetailsView()
         */
        protected function makeTitleBarAndEditAndDetailsView($model, $renderType,
                                $titleBarAndEditViewClassName = 'TitleBarAndEditAndDetailsView')
        {
            assert('$model != null');
            assert('$renderType == "Edit"');
            assert('$titleBarAndEditViewClassName != null && is_string($titleBarAndEditViewClassName)');
            return new $titleBarAndEditViewClassName(
                $this->getId(),
                $this->getModule()->getId(),
                $model,
                GroupsModule::getPluralCamelCasedName(),
                $renderType
            );
        }

        protected function resolveCanGroupBeEdited($group)
        {
            if (!$group->isEveryone && !$group->isSuperAdministrators)
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