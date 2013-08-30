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

    class ProductTemplatesCategoryController extends ZurmoModuleController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH =
            'application.modules.products.controllers.filters.ProductCatalogRelatedModelsZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('ProductTemplatesModule', 'Categories');
            return array($title);
        }

        public static function getDetailsAndEditBreadcrumbLinks()
        {
            return array(Zurmo::t('ProductTemplatesModule', 'Categories') => array('category/list'));
        }

        public function filters()
        {
            $modelClassName             = 'ProductCategory';
            $viewClassName              = $modelClassName . 'EditAndDetailsView';
            $zeroModelsYetViewClassName = 'ProductCategoriesZeroModelsYetView';
            $pageViewClassName          = 'ProductCategoriesPageView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller'                 => $this,
                        'zeroModelsYetViewClassName' => $zeroModelsYetViewClassName,
                        'modelClassName'             => $modelClassName,
                        'pageViewClassName'          => $pageViewClassName,
                        'defaultViewUtilClassName'   => 'ProductDefaultViewUtil',
                        'activeActionElementType'    => 'ProductTemplatesLink',
                        'breadcrumbLinks'            => static::getListBreadcrumbLinks()
                   ),
               )
            );
        }

        protected function getModelName()
        {
            return 'ProductCategory';
        }

        public function actionList()
        {
            $activeActionElementType        = 'ProductCategoriesLink';
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            $introView                      = new ProductsIntroView('ProductsModule');
            $actionBarAndTreeView           = new ProductCategoriesActionBarAndTreeListView(
                                                                                        $this->getId(),
                                                                                        $this->getModule()->getId(),
                                                                                        ProductCategory::getAll('name'),
                                                                                        $activeActionElementType,
                                                                                        $introView
                                               );
            $view                           = new ProductCategoriesPageView(ProductDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                    $this, $actionBarAndTreeView,
                                                        $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $productCategory    = static::getModelAndCatchNotFoundAndDisplayError('ProductCategory', intval($id));
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($productCategory), 25);
            $detailsView        = new ProductCategoryDetailsView($this->getId(), $this->getModule()->getId(), $productCategory);
            $view               = new ProductCategoriesPageView(ProductDefaultViewUtil::
                                    makeViewWithBreadcrumbsForCurrentUser(
                                        $this, $detailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate()
        {
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = Zurmo::t('ProductTemplatesModule', 'Create');
            $productCategory        = new ProductCategory();
            $productCatalog         = ProductCatalog::resolveAndGetByName(ProductCatalog::DEFAULT_NAME);
            if (!empty($productCatalog))
            {
                $productCategory->productCatalogs->add($productCatalog);
            }
            $editAndDetailsView     = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($productCategory), 'Edit');
            $view                   = new ProductCategoriesPageView(ProductDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser(
                                                $this, $editAndDetailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $productCategory    = ProductCategory::getById(intval($id));
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($productCategory), 25);
            $view                   = new ProductCategoriesPageView(ProductDefaultViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this,
                                                $this->makeEditAndDetailsView(
                                                    $this->attemptToSaveModelFromPost(
                                                        $productCategory, $redirectUrl), 'Edit'), $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        //selecting
        public function actionModalParentList()
        {
            echo $this->renderModalList(
                'SelectParentCategoryModalTreeListView', Zurmo::t('ProductTemplatesModule', 'Select a Parent Category'));
        }

        public function actionModalList()
        {
            echo $this->renderModalList(
                'ProductCategoriesModalTreeListView', Zurmo::t('ProductTemplatesModule', 'Select a category'));
        }

        protected function renderModalList($modalViewName, $pageTitle)
        {
            $rolesModalTreeView = new $modalViewName(
                                                        $this->getId(),
                                                        $this->getModule()->getId(),
                                                        $_GET['modalTransferInformation']['sourceModelId'],
                                                        ProductCategory::getAll('name'),
                                                        $_GET['modalTransferInformation']['sourceIdFieldId'],
                                                        $_GET['modalTransferInformation']['sourceNameFieldId'],
                                                        $_GET['modalTransferInformation']['modalId']
                                                    );
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, $rolesModalTreeView);
            return $view->render();
        }

        public function actionDelete($id)
        {
            $productCategory = ProductCategory::GetById(intval($id));
            $isDeleted = $productCategory->delete();
            if ($isDeleted)
            {
                $this->redirect(array($this->getId() . '/index'));
            }
            else
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule', 'The product category is associated to product templates or has child categories in the system hence could not be deleted'));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
        }
    }
?>