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

    class ProductTemplatesDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH =
            'application.modules.products.controllers.filters.ProductCatalogRelatedModelsZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModulePluralLabel', $params);
            return array($title);
        }

        public static function getDetailsAndEditBreadcrumbLinks()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array(Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModulePluralLabel', $params) => array('default/list'));
        }

        public function filters()
        {
            $modelClassName             = $this->getModule()->getPrimaryModelName();
            $viewClassName              = $modelClassName . 'EditAndDetailsView';
            $zeroModelsYetViewClassName = 'ProductTemplatesZeroModelsYetView';
            $pageViewClassName          = 'ProductTemplatesPageView';
            //Need to remove the general access rights filter
            $filters = array_slice(parent::filters(), 1);
            $filters = array_merge(array(
                                        array(
                                            ZurmoBaseController::RIGHTS_FILTER_PATH .
                                            ' - modalList, selectFromRelatedList, details, autoCompleteAllProductCategoriesForMultiSelectAutoComplete', // Not Coding Standard
                                            'moduleClassName' => get_class($this->getModule()),
                                            'rightName' => ProductTemplatesModule::getAccessRight(),
                                        ),
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
                                   ), $filters
            );

            return $filters;
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'ProductTemplatesLink';
            $productTemplate                = new ProductTemplate(false);
            $searchForm                     = new ProductTemplatesSearchForm($productTemplate);
            $listAttributesSelector         = new ListAttributesSelector('ProductTemplatesListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                                $searchForm,
                                $pageSize,
                                null,
                                'ProductTemplatesSearchView'
                                );
            $breadcrumbLinks = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView  = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view       = new ProductTemplatesPageView($mixedView);
            }
            else
            {
                $mixedView  = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                                    'SecuredActionBarForProductsSearchAndListView',
                                    null, $activeActionElementType);
                $view       = new ProductTemplatesPageView(ProductDefaultViewUtil::
                                                               makeViewWithBreadcrumbsForCurrentUser(
                                                                    $this, $mixedView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            }
            echo $view->render();
        }

        public function actionDetails($id)
        {
            $productTemplate    = static::getModelAndCatchNotFoundAndDisplayError('ProductTemplate', intval($id));
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = StringUtil::getChoppedStringContent(strval($productTemplate), 25);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($productTemplate);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($productTemplate), 'ProductTemplatesModule'), $productTemplate);
            $detailsView        = new ProductTemplateDetailsView($this->getId(), $this->getModule()->getId(), $productTemplate);
            $view               = new ProductTemplatesPageView(ProductDefaultViewUtil::
                                                                makeViewWithBreadcrumbsForCurrentUser(
                                                                    $this, $detailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionCreate()
        {
            $breadcrumbLinks    = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[]  = Zurmo::t('ProductTemplatesModule', 'Create');
            $editAndDetailsView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new ProductTemplate()), 'Edit');
            $view               = new ProductTemplatesPageView(ProductDefaultViewUtil::
                                                                makeViewWithBreadcrumbsForCurrentUser(
                                                                    $this, $editAndDetailsView, $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $productTemplate   = ProductTemplate::getById(intval($id));
            $breadcrumbLinks   = static::getDetailsAndEditBreadcrumbLinks();
            $breadcrumbLinks[] = StringUtil::getChoppedStringContent(strval($productTemplate), 25);
            $view              = new ProductTemplatesPageView(ProductDefaultViewUtil::
                                                                 makeViewWithBreadcrumbsForCurrentUser($this,
                                                                 $this->makeEditAndDetailsView(
                                                                     $this->attemptToSaveModelFromPost(
                                                                         $productTemplate, $redirectUrl), 'Edit'), $breadcrumbLinks, 'ProductBreadCrumbView'));
            echo $view->render();
        }

        protected static function getZurmoControllerUtil()
        {
            return new ProductTemplateZurmoControllerUtil('productTemplateItems', 'ProductTemplateItemForm',
                                                            'ProductTemplateCategoriesForm');
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
            $pageSize               = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                        'massEditProgressPageSize');
            $productTemplate        = new ProductTemplate(false);
            $activeAttributes       = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider           = $this->getDataProviderByResolvingSelectAllFromGet(
                                                                                    new ProductTemplatesSearchForm($productTemplate),
                                                                                    $pageSize,
                                                                                    Yii::app()->user->userModel->id,
                                                                                    null,
                                                                                    'ProductTemplatesSearchView');
            $selectedRecordCount    = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productTemplate        = $this->processMassEdit(
                                                                $pageSize,
                                                                $activeAttributes,
                                                                $selectedRecordCount,
                                                                'ProductTemplatesPageView',
                                                                $productTemplate,
                                                                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                                $dataProvider
                                                            );
            $massEditView = $this->makeMassEditView(
                                                        $productTemplate,
                                                        $activeAttributes,
                                                        $selectedRecordCount,
                                                        ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural')
                                                    );
            $view = new ProductTemplatesPageView(ZurmoDefaultViewUtil::
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
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massEditProgressPageSize');
            $productTemplate    = new ProductTemplate(false);
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                                                                    new ProductTemplatesSearchForm($productTemplate),
                                                                                    $pageSize,
                                                                                    Yii::app()->user->userModel->id,
                                                                                    null,
                                                                                    'ProductTemplatesSearchView'
                                                                                );
            $this->processMassEditProgressSave(
                                                'ProductTemplate',
                                                $pageSize,
                                                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
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
            $title           = Zurmo::t('ProductTemplatesModule', 'Mass Delete ProductTemplatesModulePluralLabel', $params);
            $breadcrumbLinks = array(
                 $title,
            );
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                    'massDeleteProgressPageSize');
            $productTemplate    = new ProductTemplate(false);

            $activeAttributes   = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                                                                new ProductTemplatesSearchForm($productTemplate),
                                                                                $pageSize,
                                                                                Yii::app()->user->userModel->id,
                                                                                null,
                                                                                'ProductTemplatesSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $productTemplate     = $this->processMassDelete(
                                                            $pageSize,
                                                            $activeAttributes,
                                                            $selectedRecordCount,
                                                            'ProductTemplatesPageView',
                                                            $productTemplate,
                                                            Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModulePluralLabel', $params),
                                                            $dataProvider
                                                        );

            if ($productTemplate === false)
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule',
                'One of the ProductTemplatesModuleSingularLowerCaseLabel selected is  associated to products in the system hence could not be deleted', $params));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
            else
            {
                $massDeleteView = $this->makeMassDeleteView(
                    $productTemplate,
                    $activeAttributes,
                    $selectedRecordCount,
                    Zurmo::t('ProductTemplatesModule', 'ProductTemplatesModulePluralLabel', $params),
                    'ProductTemplatesMassDeleteView'
                );
                $view = new ProductTemplatesPageView(ZurmoDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this, $massDeleteView, $breadcrumbLinks, 'ProductBreadCrumbView'));
                echo $view->render();
            }
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
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $productTemplate    = new ProductTemplate(false);
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                                                                        new ProductTemplatesSearchForm($productTemplate),
                                                                                        $pageSize,
                                                                                        Yii::app()->user->userModel->id,
                                                                                        null,
                                                                                        'ProductTemplatesSearchView'
                                                                                    );
            $this->processMassDeleteProgress(
                                                'ProductTemplate',
                                                $pageSize,
                                                ProductTemplatesModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                $dataProvider
                                            );
        }

        public function actionModalList()
        {
            $modalListLinkProvider = new ProductTemplateSelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId'],
                                            $_GET['modalTransferInformation']['modalId']
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider);
        }

        public function actionDelete($id)
        {
            $productTemplate = ProductTemplate::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($productTemplate);
            //Check if product template has associated products
            if ($productTemplate->delete())
            {
                $this->redirect(array($this->getId() . '/index'));
            }
            else
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ProductTemplatesModule', 'The product template is associated to products in the system hence could not be deleted'));
                $this->redirect(Zurmo::app()->request->getUrlReferrer());
            }
        }

        protected static function getSearchFormClassName()
        {
            return 'ProductTemplatesSearchForm';
        }

        public function actionExport()
        {
            $this->export('ProductTemplatesSearchView');
        }

        public function actionAutoCompleteAllProductCategoriesForMultiSelectAutoComplete($term)
        {
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = null;
            $productCategories      = self::getProductCategoriesByPartialName($term, $pageSize, $adapterName);
            $autoCompleteResults    = array();
            foreach ($productCategories as $productCategory)
            {
                $autoCompleteResults[] = array(
                    'id'   => $productCategory->id,
                    'name' => self::renderHtmlContentLabelFromProductCategoryAndKeyword($productCategory, $term)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        public static function getProductCategoriesByPartialName($partialName, $pageSize, $stateMetadataAdapterClassName = null)
        {
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $joinTablesAdapter  = new RedBeanModelJoinTablesQueryAdapter('ProductCategory');
            $metadata           = array('clauses' => array(), 'structure' => '');
            if ($stateMetadataAdapterClassName != null)
            {
                $stateMetadataAdapter   = new $stateMetadataAdapterClassName($metadata);
                $metadata               = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                $metadata['structure']  = '(' . $metadata['structure'] . ')';
            }
            $where  = RedBeanModelDataProvider::makeWhere('ProductCategory', $metadata, $joinTablesAdapter);
            if ($where != null)
            {
                $where .= 'and';
            }
            $where .= self::getWherePartForPartialNameSearchByPartialName($partialName);
            return ProductCategory::getSubset($joinTablesAdapter, null, $pageSize, $where, "productcategory.name");
        }

        protected static function getWherePartForPartialNameSearchByPartialName($partialName)
        {
            assert('is_string($partialName)');
            return "   (productcategory.name  like '$partialName%') ";
        }

        public static function renderHtmlContentLabelFromProductCategoryAndKeyword($productCategory, $keyword)
        {
            assert('$productCategory instanceof ProductCategory && $productCategory->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if ($productCategory->name != null)
            {
                return strval($productCategory) . '&#160&#160<b>'. '</b>';
            }
            else
            {
                return strval($productCategory);
            }
        }

        /**
         * Override to provide a provide template specific label for the modal page title.
         * @see ZurmoModuleController->actionSelectFromRelatedList()
         */
        public function actionSelectFromRelatedList($portletId,
                                                    $uniqueLayoutId,
                                                    $relationAttributeName,
                                                    $relationModelId,
                                                    $relationModuleId,
                                                    $stateMetadataAdapterClassName = null)
        {
            $portlet               = Portlet::getById((int)$portletId);
            $modalListLinkProvider = new ProductTemplateSelectFromRelatedListModalListLinkProvider(
                                            $relationAttributeName,
                                            (int)$relationModelId,
                                            $relationModuleId,
                                            $portlet->getUniquePortletPageId(),
                                            $uniqueLayoutId,
                                            (int)$portlet->id,
                                            $this->getModule()->getId()
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, $stateMetadataAdapterClassName);
        }

        /**
         * Copies the product template
         * @param int $id
         */
        public function actionCopy($id)
        {
            $copyToProductTemplate = new ProductTemplate();
            $postVariableName      = get_class($copyToProductTemplate);
            if (!isset($_POST[$postVariableName]))
            {
                $productTemplate = ProductTemplate::getById((int)$id);
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($productTemplate);
                ProductZurmoCopyModelUtil::copy($productTemplate, $copyToProductTemplate);
            }
            $this->processEdit($copyToProductTemplate);
        }

        /**
         * Process the editing of product template
         * @param Product $productTemplate
         * @param string $redirectUrl
         */
        protected function processEdit(ProductTemplate $productTemplate, $redirectUrl = null)
        {
            $view = new ProductTemplatesPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($productTemplate, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        /**
         * Gets product template data for product
         * @param string $id
         */
        public function actionGetProductTemplateDataForProduct($id)
        {
            $getData = GetUtil::getData();
            $productTemplate    = static::getModelAndCatchNotFoundAndDisplayError('ProductTemplate', intval($id));

            $categoryOutput             = array();
            $productType                = $productTemplate->type;
            $productPriceFrequency      = $productTemplate->priceFrequency;
            $productSellPriceCurrency   = $productTemplate->sellPrice->currency->id;
            $productSellPriceValue      = $productTemplate->sellPrice->value;
            foreach ($productTemplate->productCategories as $category)
            {
                $categoryOutput[] = array( 'id' => $category->id, 'name' => $category->name);
            }
            $output = array('categoryOutput'           => $categoryOutput,
                            'productType'              => $productType,
                            'productPriceFrequency'    => $productPriceFrequency,
                            'productSellPriceCurrency' => $productSellPriceCurrency,
                            'productSellPriceValue'    => $productSellPriceValue,
                            'productName'              => $productTemplate->name,
                            'productDescription'       => $productTemplate->description
                           );

            echo CJSON::encode($output);
        }
    }
?>