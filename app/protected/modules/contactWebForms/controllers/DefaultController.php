<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ContactWebFormsDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH =
                    'application.modules.contactWebForms.controllers.filters.ContactWebFormsZeroModelsCheckControllerFilter';

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
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                        'activeActionElementType' => 'ContactWebFormsListLink',
                        'breadcrumbLinks'         => static::getListBreadcrumbLinks(),
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
                $view      = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
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
                unset($_POST[$modelClassName]['serializedData']);
                $contactWebForm->serializedData = serialize($_POST['attributeIndexOrDerivedType']);
            }
            $contactWebForm->defaultOwner = Yii::app()->user->userModel;
            $contactWebForm->language     = Yii::app()->language;
            $titleBarAndEditView          = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($contactWebForm), 'Edit');
            $view                         = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView,
                                                $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id)
        {
            $contactWebForm  = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($contactWebForm);
            $modelClassName  = $this->getModule()->getPrimaryModelName();
            $breadCrumbTitle = Zurmo::t('ContactWebFormsModule', 'Edit Web Form');
            $breadcrumbLinks = array($breadCrumbTitle);
            if ($contactWebForm->language === null)
            {
                $contactWebForm->language = Yii::app()->language;
            }
            if (isset($_POST[$modelClassName]))
            {
                unset($_POST[$modelClassName]['serializedData']);
                $contactWebForm->serializedData = serialize($_POST['attributeIndexOrDerivedType']);
            }
            $titleBarAndEditView                = $this->makeEditAndDetailsView(
                                                  $this->attemptToSaveModelFromPost($contactWebForm), 'Edit');
            $view                               = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
                                                      makeViewWithBreadcrumbsForCurrentUser($this, $titleBarAndEditView,
                                                      $breadcrumbLinks, 'ContactWebFormsBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $contactWebForm         = static::getModelAndCatchNotFoundAndDisplayError('ContactWebForm', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($contactWebForm);
            $breadCrumbTitle        = $contactWebForm->name;
            $breadcrumbLinks        = array($breadCrumbTitle);
            $titleBarAndDetailsView = $this->makeEditAndDetailsView($contactWebForm, 'Details');
            $view                   = new ContactWebFormsPageView(ZurmoDefaultAdminViewUtil::
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