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

    class ProductsDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH =
            'application.modules.products.controllers.filters.ProductCatalogRelatedModelsZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('ProductsModule', 'ProductsModulePluralLabel', $params);
            return array($title);
        }

        public function filters()
        {
            $modelClassName             = $this->getModule()->getPrimaryModelName();
            $viewClassName              = $modelClassName . 'EditAndDetailsView';
            $zeroModelsYetViewClassName = 'ProductsZeroModelsYetView';
            $pageViewClassName          = 'ProductsPageView';
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
                        'activeActionElementType'    => 'ProductsLink',
                        'breadcrumbLinks'            => static::getListBreadcrumbLinks()
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'ProductsLink';
            $product                        = new Product(false);
            $searchForm                     = new ProductsSearchForm($product);
            $listAttributesSelector         = new ListAttributesSelector('ProductsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider                   = $this->resolveSearchDataProvider(
                                                    $searchForm,
                                                    $pageSize,
                                                    null,
                                                    'ProductsSearchView'
                                                );
            $breadcrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView  = $this->makeListView(
                            $searchForm,
                            $dataProvider
                        );
                $view       = new ProductsPageView($mixedView);
            }
            else
            {
                $mixedView  = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                                   'SecuredActionBarForProductsSearchAndListView',
                                    null, $activeActionElementType);
                $view       = new ProductsPageView(ZurmoDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                        $this, $mixedView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $product            = static::getModelAndCatchNotFoundAndDisplayError('Product', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($product);
            $breadcrumbLinks = array(StringUtil::getChoppedStringContent(strval($product), 25));
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($product), 'ProductsModule'), $product);
            $detailsView        = new ProductEditAndDetailsView('Details', $this->getId(), $this->getModule()->getId(), $product);
            $view               = new ProductsPageView(ProductDefaultViewUtil::
                                                         makeViewWithBreadcrumbsForCurrentUser(
                                                            $this, $detailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate()
        {
            $params                 = LabelUtil::getTranslationParamsForAllModules();
            $title                  = Zurmo::t('ProductsModule', 'Create ProductsModuleSingularLabel', $params);
            $breadcrumbLinks        = array($title);
            $editAndDetailsView     = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new Product()), 'Edit');
            $view                   = new ProductsPageView(ProductDefaultViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser(
                                                    $this, $editAndDetailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $product         = Product::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($product);
            $breadcrumbLinks = array(StringUtil::getChoppedStringContent(strval($product), 25));
            $view            = new ProductsPageView(ProductDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this,
                                                            $this->makeEditAndDetailsView(
                                                                $this->attemptToSaveModelFromPost(
                                                                    $product, $redirectUrl), 'Edit'), $breadcrumbLinks, 'ProductBreadCrumbView'                                                   ));
            echo $view->render();
        }

        protected static function getZurmoControllerUtil()
        {
            return new ProductZurmoControllerUtil('productItems', 'ProductItemForm',
                                                            'ProductCategoriesForm');
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
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massEditProgressPageSize');
            $product            = new Product(false);
            $activeAttributes   = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new ProductsSearchForm($product),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'ProductsSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $product             = $this->processMassEdit(
                                        $pageSize,
                                        $activeAttributes,
                                        $selectedRecordCount,
                                        'ProductsPageView',
                                        $product,
                                        ProductsModule::getModuleLabelByTypeAndLanguage('Plural'),
                                        $dataProvider
                                    );
            $massEditView       = $this->makeMassEditView(
                                        $product,
                                        $activeAttributes,
                                        $selectedRecordCount,
                                        ProductsModule::getModuleLabelByTypeAndLanguage('Plural')
                                       );
            $view               = new ProductsPageView(ZurmoDefaultViewUtil::
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
            $pageSize       = Yii::app()->pagination->resolveActiveForCurrentUserByType('massEditProgressPageSize');
            $product        = new Product(false);
            $dataProvider   = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new ProductsSearchForm($product),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'ProductsSearchView'
                                        );
            $this->processMassEditProgressSave(
                        'Product',
                        $pageSize,
                        ProductsModule::getModuleLabelByTypeAndLanguage('Plural'),
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
         * @see Controller->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $params          = LabelUtil::getTranslationParamsForAllModules();
            $title           = Zurmo::t('ProductTemplatesModule', 'Mass Delete ProductsModulePluralLabel', $params);
            $breadcrumbLinks = array(
                 $title,
            );
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $product            = new Product(false);

            $activeAttributes   = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new ProductsSearchForm($product),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'ProductsSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $product             = $this->processMassDelete(
                                                            $pageSize,
                                                            $activeAttributes,
                                                            $selectedRecordCount,
                                                            'ProductsPageView',
                                                            $product,
                                                            ProductsModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                            $dataProvider
                                                          );
            $massDeleteView     = $this->makeMassDeleteView(
                                                             $product,
                                                             $activeAttributes,
                                                             $selectedRecordCount,
                                                             ProductsModule::getModuleLabelByTypeAndLanguage('Plural')
                                                            );
            $view               = new ProductsPageView(ZurmoDefaultViewUtil::
                                                            makeViewWithBreadcrumbsForCurrentUser($this, $massDeleteView, $breadcrumbLinks, 'ProductBreadCrumbView'));
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
            $pageSize       = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $product        = new Product(false);
            $dataProvider   = $this->getDataProviderByResolvingSelectAllFromGet(
                                          new ProductsSearchForm($product),
                                          $pageSize,
                                          Yii::app()->user->userModel->id,
                                          null,
                                          'ProductsSearchView'
                                        );
            $this->processMassDeleteProgress(
                                                'Product',
                                                $pageSize,
                                                ProductsModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                $dataProvider
                                             );
        }

        public function actionDelete($id)
        {
            $product = Product::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($product);
            $product->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        protected static function getSearchFormClassName()
        {
            return 'ProductsSearchForm';
        }

        public function actionExport()
        {
            $this->export('ProductsSearchView');
        }

        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $product = $this->resolveNewModelByRelationInformation( new Product(),
                                                                                $relationAttributeName,
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            $this->actionCreateByModel($product, $redirectUrl);
        }

        protected function actionCreateByModel(Product $product, $redirectUrl = null)
        {
            $titleBarAndEditView    = $this->makeEditAndDetailsView(
                                                $this->attemptToSaveModelFromPost($product, $redirectUrl), 'Edit');
            $view                   = new ProductsPageView(ZurmoDefaultViewUtil::
                                                                makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionUpdate($attribute)
        {
            $id         = Yii::app()->request->getParam('item');
            $value      = Yii::app()->request->getParam('value');
            assert('$id != null && $id != ""');
            assert('$value != null && $value != ""');
            $id         = intval($id);
            $product    = Product::getById($id);
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($product);
            switch($attribute)
            {
                case 'quantity'     :   $value      = intval($value);
                                        $product->quantity     = $value;
                                        break;
                case 'sellPrice'    :   $value      = floatval($value);
                                        $product->sellPrice->value = $value;
                                        break;
            }
            $product->save();
        }

        /**
         * Create product from product template when user select a product
         * template while adding a product in product portlet view
         * @param string $relationModuleId
         * @param int $portletId
         * @param string $uniqueLayoutId
         * @param int $id
         * @param int $relationModelId
         * @param string $relationAttributeName
         * @param string $relationModelClassName
         */
        public function actionCreateProductFromProductTemplate($relationModuleId, $portletId, $uniqueLayoutId, $id,
                                $relationModelId, $relationAttributeName, $relationModelClassName = null, $redirect = true)
        {
            if ($relationModelClassName == null)
            {
                $relationModelClassName = Yii::app()->getModule($relationModuleId)->getPrimaryModelName();
            }
            $productTemplate            = static::getModelAndCatchNotFoundAndDisplayError('ProductTemplate', intval($id));
            $product                    = new Product();
            $product->name              = $productTemplate->name;
            $product->description       = $productTemplate->description;
            $product->quantity          = 1;
            $product->stage->value      = Product::OPEN_STAGE;
            $product->productTemplate   = $productTemplate;
            $sellPrice                  = new CurrencyValue();
            $sellPrice->value           = $productTemplate->sellPrice->value;
            $sellPrice->currency        = $productTemplate->sellPrice->currency;
            $product->priceFrequency    = $productTemplate->priceFrequency;
            $product->sellPrice         = $sellPrice;
            $product->type              = $productTemplate->type;

            foreach ($productTemplate->productCategories as $productCategory)
            {
                $product->productCategories->add($productCategory);
            }

            $relationModel                      = $relationModelClassName::getById((int)$relationModelId);
            $product->$relationAttributeName    = $relationModel;
            $product->save();
            ZurmoControllerUtil::updatePermissionsWithDefaultForModelByCurrentUser($product);

            if ((bool)$redirect)
            {
                $isViewLocked = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
                $redirectUrl  = Yii::app()->createUrl('/' . $relationModuleId . '/default/details', array('id' => $relationModelId));
                $this->redirect(array('/' . $relationModuleId . '/defaultPortlet/modalRefresh',
                                        'portletId'            => $portletId,
                                        'uniqueLayoutId'       => $uniqueLayoutId,
                                        'redirectUrl'          => $redirectUrl,
                                        'portletParams'        => array(  'relationModuleId' => $relationModuleId,
                                                                          'relationModelId'  => $relationModelId),
                                        'portletsAreRemovable' => !$isViewLocked));
            }
        }

        /**
         * Copies the product
         * @param int $id
         */
        public function actionCopy($id)
        {
            $copyToProduct      = new Product();
            $postVariableName   = get_class($copyToProduct);
            if (!isset($_POST[$postVariableName]))
            {
                $product        = Product::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($product);
                ProductZurmoCopyModelUtil::copy($product, $copyToProduct);
            }
            $this->processEdit($copyToProduct);
        }

        /**
         * Process the editing of product
         * @param Product $product
         * @param string $redirectUrl
         */
        protected function processEdit(Product $product, $redirectUrl = null)
        {
            $view = new ProductsPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($product, $redirectUrl), 'Edit')));
            echo $view->render();
        }
    }
?>