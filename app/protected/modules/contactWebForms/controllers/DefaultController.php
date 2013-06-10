<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class ContactWebFormsDefaultController extends ZurmoModuleController
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
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('ContactWebFormsModule', 'List');
            return array($title);
        }

        public function actionList()
        {
            $pageSize        = Yii::app()->pagination->resolveActiveForCurrentUserByType('listPageSize',
                                                                                          get_class($this->getModule()));
            $activeActionElementType = 'ContactWebFormsListLink';
            $contactWebForm  = new ContactWebForm(false);
            $searchForm      = new ContactWebFormsSearchForm($contactWebForm);
            $dataProvider    = $this->resolveSearchDataProvider($searchForm, $pageSize, null, 'ContactWebFormsSearchView');
            $breadcrumbLinks = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view      = new ContactWebFormsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForContactWebFormsSearchAndListView', null, $activeActionElementType);
                $view = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                              makeViewWithBreadcrumbsForCurrentUser(
                                              $this, $mixedView, $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionCreate()
        {
            $contactWebForm  = new ContactWebForm();
            $modelClassName  = $this->getModule()->getPrimaryModelName();
            $breadCrumbTitle = Zurmo::t('ContactWebFormsModule', 'Create Web Form');
            $breadcrumbLinks = array($breadCrumbTitle);
            if (isset($_POST[$modelClassName]))
            {
                $_POST[$modelClassName]['serializedData'] = serialize($_POST['attributeIndexOrDerivedType']);
            }
            else
            {
                $contactWebForm->defaultOwner = Yii::app()->user->userModel;
            }
            $titleBarAndEditView = $this->makeEditAndDetailsView(
                                          $this->attemptToSaveModelFromPost($contactWebForm), 'Edit');
            $view = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView,
                                                $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $contactWebForm = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($contactWebForm);
            $modelClassName = $this->getModule()->getPrimaryModelName();
            $breadCrumbTitle = Zurmo::t('ContactWebFormsModule', 'Edit Web Form');
            $breadcrumbLinks = array($breadCrumbTitle);
            if (isset($_POST[$modelClassName]))
            {
                $_POST[$modelClassName]['serializedData'] = serialize($_POST['attributeIndexOrDerivedType']);
            }
            $titleBarAndEditView = $this->makeEditAndDetailsView(
                                          $this->attemptToSaveModelFromPost($contactWebForm), 'Edit');
            $view = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView,
                                                $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $contactWebForm = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($contactWebForm);
            $breadCrumbTitle = $contactWebForm->name;
            $breadcrumbLinks = array($breadCrumbTitle);
            $titleBarAndDetailsView = $this->makeEditAndDetailsView($contactWebForm, 'Details');
            $view = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndDetailsView,
                                                $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $contactWebForm = ContactWebForm::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($contactWebForm);
            $contactWebForm->delete();
            $this->redirect(array($this->getId() . '/index'));
        }
    }
?>
