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

    abstract class ProductsRelatedListView extends SecuredRelatedListView
    {
        /**
         * Form that has the information for how to display the latest products view.
         */
        protected $configurationForm = 'ProductsConfigurationForm';

        /**
         * The url to use as the redirect url when going to another action. This will return the user
         * to the correct page upon canceling or completing an action.
         * @var string
         */
        public $redirectUrl;

        /**
         * Unique identifier used to identify this view on the page.
         * @var string
         */
        protected $uniquePageId;

        protected $params;

        protected $showStageFilter = true;

        protected static $persistantProductPortletConfigs = array(
            'filteredByStage'
        );

        protected $relationModuleId;

        function __construct($viewData, $params, $uniqueLayoutId)
        {
            parent::__construct($viewData, $params, $uniqueLayoutId);
            $this->uniquePageId        = get_called_class();
            $productsConfigurationForm = $this->getConfigurationForm();
            $this->resolveProductsConfigFormFromRequest($productsConfigurationForm);
            $this->configurationForm   = $productsConfigurationForm;
            $this->relationModuleId    = $this->params['relationModuleId'];
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                    'perUser' => array(
                        'title' => "eval:Zurmo::t('ProductsModule', 'ProductsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                    ),
                    'global' => array(
                        'toolbar' => array(
                            'elements' => array(
                                array(  'type'            => 'CreateFromRelatedListLink',
                                        'routeModuleId'   => 'eval:$this->moduleId',
                                        'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()'),
                            ),
                        ),
                        'rowMenu' => array(
                            'elements' => array(
                                                    array('type'                      => 'EditLink'),
                                                    array('type'                      => 'RelatedDeleteLink'),
                                                    array('type'                      => 'RelatedUnlink',
                                                          'relationModelClassName'    => 'eval:get_class($this->params["relationModel"])',
                                                          'relationModelId'           => 'eval:$this->params["relationModel"]->id',
                                                          'relationModelRelationName' => 'products',
                                                          'userHasRelatedModelAccess' => 'eval:ActionSecurityUtil::canCurrentUserPerformAction( "Edit", $this->params["relationModel"])')
                            ),
                        ),
                        'derivedAttributeTypes' => array(),
                        'gridViewType' => RelatedListView::GRID_VIEW_TYPE_NORMAL,
                        'panels' => array(
                            array(
                                'rows' => array(
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'name', 'type' => 'Text'),
                                                ),
                                            ),
                                        )
                                    ),
                                    array('cells' =>
                                                array(
                                                    array(
                                                        'elements' => array(
                                                            array('attributeName' => 'quantity', 'type' => 'Text'),
                                                        ),
                                                    ),
                                                )
                                    ),
                                    array('cells' =>
                                        array(
                                            array(
                                                'elements' => array(
                                                    array('attributeName' => 'sellPrice', 'type' => 'CurrencyValue'),
                                                ),
                                            ),
                                        )
                                    ),
                                ),
                            ),
                        ),
                    ),
                );
             return $metadata;
        }

        public static function getModuleClassName()
        {
            return 'ProductsModule';
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "\n{items}\n{pager}\n<span class='products-portlet-totals'>{totalBarDetails}</span>" . $preloader;
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            return 'js:function(id, data)
                    {
                        processAjaxSuccessError(id, data);
                    }';
        }

        protected function getUniquePageId()
        {
            return null;
        }

        protected static function resolveAjaxOptionsForSelectList()
        {
            $title = Zurmo::t('ProductsModule', 'ProductsModuleSingularLabel Search',
                            LabelUtil::getTranslationParamsForAllModules());
            return ModalView::getAjaxOptionsForModalLink($title);
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         */
         protected function getCGridViewColumns()
         {
            $columns         = array();
            if ($this->rowsAreSelectable)
            {
                $firstColumn = $this->getCGridViewFirstColumn();
                array_push($columns, $firstColumn);
            }

            $metadata = $this->getResolvedMetadata();
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $columnInformation)
                        {
                            $columnClassName    = 'Product' . ucfirst($columnInformation['attributeName']) . 'RelatedListViewColumnAdapter';
                            $columnAdapter      = new $columnClassName($columnInformation['attributeName'], $this, array_slice($columnInformation, 1));
                            $column = $columnAdapter->renderGridViewData();
                            if (!isset($column['class']))
                            {
                                $column['class'] = 'DataColumn';
                            }
                            array_push($columns, $column);
                        }
                    }
                }
            }

            //Add total to the grid view
            $columnClassName    = 'ProductTotalRelatedListViewColumnAdapter';
            $columnAdapter      = new ProductTotalRelatedListViewColumnAdapter('total', $this, array());
            $column             = $columnAdapter->renderGridViewData();
            array_push($columns, $column);
            $menuColumn = $this->getGridViewMenuColumn();
            if ($menuColumn == null)
            {
                $lastColumn = $this->getCGridViewLastColumn();
                if (!empty($lastColumn))
                {
                    array_push($columns, $lastColumn);
                }
            }
            else
            {
                array_push($columns, $menuColumn);
            }
            return $columns;
        }

        protected function renderContent()
        {
            $content         = $this->renderConfigurationForm();
            $cClipWidget     = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content        .= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content    .= ZurmoHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
            }
            $content        .= $this->renderScripts();
            return $content;
        }

        public function getGridViewId()
        {
            return 'product-portlet-grid-view';
        }

        protected function getGridViewWidgetPath()
        {
            return 'application.modules.products.widgets.ProductPortletExtendedGridView';
        }

        protected function getCGridViewParams()
        {
            $columns = $this->getCGridViewColumns();
            assert('is_array($columns)');

            return array(
                'id' => $this->getGridViewId(),
                'htmlOptions' => array(
                    'class' => 'cgrid-view'
                ),
                'loadingCssClass'      => 'loading',
                'dataProvider'         => $this->getProductsDataProvider($this->configurationForm),
                'selectableRows'       => $this->getCGridViewSelectableRowsCount(),
                'pager'                => $this->getCGridViewPagerParams(),
                'beforeAjaxUpdate'     => $this->getCGridViewBeforeAjaxUpdate(),
                'afterAjaxUpdate'      => $this->getCGridViewAfterAjaxUpdate(),
                'columns'              => $columns,
                'nullDisplay'          => '&#160;',
                'pagerCssClass'        => static::getPagerCssClass(),
                'showTableOnEmpty'     => $this->getShowTableOnEmpty(),
                'emptyText'            => $this->getEmptyText(),
                'template'             => static::getGridTemplate(),
                'summaryText'          => static::getSummaryText(),
                'summaryCssClass'      => static::getSummaryCssClass(),
                'params'               => $this->params
            );
        }

        protected function getCGridViewPagerParams()
        {
            $defaultData = array('id' => $this->params["relationModel"]->id, 'stickyOffset' => 0);
            return array(
                    'firstPageLabel' => '<span>first</span>',
                    'prevPageLabel'  => '<span>previous</span>',
                    'nextPageLabel'  => '<span>next</span>',
                    'lastPageLabel'  => '<span>last</span>',
                    'class'          => 'SimpleListLinkPager',
                    'paginationParams' => array_merge($defaultData, array('portletId' => $this->params['portletId'])),
                    'route'         => 'defaultPortlet/details',
                );
        }

        public function renderPortletHeadContent()
        {
            return $this->renderWrapperAndActionElementMenu(Zurmo::t('Core', 'Options'));
        }

        protected function renderConfigurationForm()
        {
            $formName   = 'product-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $innerContent = null;
            if ($this->showStageFilter)
            {
                $element                   = new ProductStageFilterRadioElement($this->configurationForm,
                                                                                          'filteredByStage',
                                                                                          $form);
                $element->editableTemplate =  '<div id="ProductsConfigurationForm_filteredByStage_area">{content}</div>';
                $stageFilterContent        = $element->render();
                $innerContent             .= $stageFilterContent;
            }
            if ($innerContent != null)
            {
                $content .= '<div class="filter-portlet-model-bar">';
                $content .= $innerContent;
                $content .= '</div>' . "\n";
            }
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = $this->getPortletDetailsUrl(); // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'        =>  $urlScript,
                    'update'     => '#' . $this->uniqueLayoutId,
                    'beforeSend' => 'js:function(){$(this).makeSmallLoadingSpinner(true, "#' . $this->getGridViewId() . '"); $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                    'complete'   => 'js:function()
                    {
                                        $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
                                        $("#filter-portlet-model-bar-' . $this->uniquePageId . '").show();
                    }'
            ));
            Yii::app()->clientScript->registerScript($this->uniquePageId, "
            $('#ProductsConfigurationForm_filteredByStage_area').buttonset();
            $('#ProductsConfigurationForm_filteredByStage_area').change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }

        protected function getConfigurationForm()
        {
            return new ProductsConfigurationForm();
        }

        protected function resolveProductsConfigFormFromRequest(&$productsConfigurationForm)
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($productsConfigurationForm)]))
            {
                $productsConfigurationForm->setAttributes($_GET[get_class($productsConfigurationForm)]);
                $excludeFromRestore = $this->saveUserSettingsFromConfigForm($productsConfigurationForm);
            }
            $this->restoreUserSettingsToConfigFrom($productsConfigurationForm, $excludeFromRestore);
        }

        protected function saveUserSettingsFromConfigForm(&$productsConfigurationForm)
        {
            $savedConfigs = array();
            foreach (static::$persistantProductPortletConfigs as $persistantProductConfigItem)
            {
                if ($productsConfigurationForm->$persistantProductConfigItem !==
                    ProductsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey($this->params['portletId'],
                                                                                            $persistantProductConfigItem))
                {
                    ProductsPortletPersistentConfigUtil::setForCurrentUserByPortletIdAndKey($this->params['portletId'],
                                                            $persistantProductConfigItem,
                                                            $productsConfigurationForm->$persistantProductConfigItem
                                                        );
                    $savedConfigs[] = $persistantProductConfigItem;
                }
            }
            return $savedConfigs;
        }

        protected function restoreUserSettingsToConfigFrom(&$productsConfigurationForm, $excludeFromRestore)
        {
            foreach (static::$persistantProductPortletConfigs as $persistantProductConfigItem)
            {
                if (in_array($persistantProductConfigItem, $excludeFromRestore))
                {
                    continue;
                }
                $persistantProductConfigItemValue = ProductsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                                $this->params['portletId'],
                                                                                                $persistantProductConfigItem);
                if (isset($persistantProductConfigItemValue))
                {
                    $productsConfigurationForm->$persistantProductConfigItem = $persistantProductConfigItemValue;
                }
            }
            return $productsConfigurationForm;
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         */
        protected function getPortletDetailsUrl()
        {
            $redirectUrl = $this->params['redirectUrl'];
            $params = array_merge($_GET, array('portletId'       => $this->params['portletId'],
                                               'uniqueLayoutId'  => $this->uniqueLayoutId,
                                               'redirectUrl'    => $redirectUrl,
                                               'portletParams'   => array('relationModuleId' => $this->relationModuleId,
                                                                         'relationModelId' => $this->params['relationModel']->id)
                                               )
                                  );
            return Yii::app()->createUrl('/' . $this->relationModuleId . '/defaultPortlet/modalRefresh', $params);
        }

        protected function makeProductSearchAttributeData($form)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'][1] = array(
                                                        'attributeName'        => $this->getRelationAttributeName(),
                                                        'relatedAttributeName' => 'id',
                                                        'operatorType'         => 'equals',
                                                        'value'                => (int)$this->params['relationModel']->id,
                                                    );
            if ($form->filteredByStage != ProductsConfigurationForm::FILTERED_BY_ALL_STAGES)
            {
                $searchAttributeData['clauses'][2] = array(
                                                            'attributeName'        => 'stage',
                                                            'relatedAttributeName' => 'value',
                                                            'operatorType'         => 'equals',
                                                            'value'                => $form->filteredByStage,
                                                         );
                $searchAttributeData['structure'] = '1 and 2';
            }
            else
            {
                $searchAttributeData['structure'] = '1';
            }
            return $searchAttributeData;
        }

        protected function getProductsDataProvider($form)
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProviderBySearchAttributeData($this->makeProductSearchAttributeData($form));
            }
            return $this->dataProvider;
        }
    }
?>