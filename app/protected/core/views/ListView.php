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

    /**
     * The base View for a module's list view.
     */
    abstract class ListView extends ModelView implements ListViewInterface
    {
        protected $controllerId;

        protected $moduleId;

        protected $dataProvider;

        /**
         * True/false to decide if each row in the list view widget
         * will have a checkbox.
         */
        protected $rowsAreSelectable = false;

        /**
         * Unique identifier of the list view widget. Allows for multiple list view
         * widgets on a single page.
         * @see $
         */
        protected $gridId;

        /**
         * Additional unique identifier.
         * @see $gridId
         */
        protected $gridIdSuffix;

        /**
         * Array of model ids. Each id is for a different row checked off
         */
        protected $selectedIds;

        /**
         * Array containing CGridViewPagerParams
         */
        protected $gridViewPagerParams = array();

        private $resolvedMetadata;

        protected $emptyText = null;

        private $listAttributesSelector;

        /**
         * Constructs a list view specifying the controller as
         * well as the model that will have its details displayed.isDisplayAttributeACalculationOrModifier
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $selectedIds,
            $gridIdSuffix = null,
            $gridViewPagerParams = array(),
            $listAttributesSelector = null
        )
        {
            assert('is_array($selectedIds)');
            assert('is_string($modelClassName)');
            assert('is_array($this->gridViewPagerParams)');
            assert('$listAttributesSelector == null || $listAttributesSelector instanceof ListAttributesSelector');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelClassName         = $modelClassName;
            $this->dataProvider           = $dataProvider;
            $this->rowsAreSelectable      = true;
            $this->selectedIds            = $selectedIds;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridViewPagerParams    = $gridViewPagerParams;
            $this->gridId                 = 'list-view';
            $this->listAttributesSelector = $listAttributesSelector;
        }

        /**
         * Renders content for a list view. Utilizes a CActiveDataprovider
         * and a CGridView widget.
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content = $this->renderViewToolBar();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content .= ZurmoHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
            }
            $content .= $this->renderScripts();
            return $content;
        }

        protected function getGridViewWidgetPath()
        {
            if (Yii::app()->userInterface->isMobile())
            {
                $widget = 'application.core.widgets.StackedExtendedGridView';
            }
            else
            {
                $widget = 'application.core.widgets.ExtendedGridView';
            }
            return $widget;
        }

        public function getRowsAreSelectable()
        {
            return $this->rowsAreSelectable;
        }

        public function setRowsAreSelectable($value)
        {
            $this->rowsAreSelectable = (boolean)$value;
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
                'dataProvider'         => $this->getDataProvider(),
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
            );
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "{summary}\n{items}\n{pager}" . $preloader;
        }

        protected static function getPagerCssClass()
        {
            return 'pager vertical';
        }

        protected static function getSummaryText()
        {
            return Zurmo::t('Core', '{count} result(s)');
        }

        protected static function getSummaryCssClass()
        {
            return 'summary';
        }

        protected function getCGridViewPagerParams()
        {
            $defaultGridViewPagerParams = array(
                        'prevPageLabel'    => '<span>previous</span>',
                        'nextPageLabel'    => '<span>next</span>',
                        'class'            => 'EndlessListLinkPager',
                        'paginationParams' => GetUtil::getData(),
                        'route'            => $this->getGridViewActionRoute($this->getListActionId(), $this->moduleId),
                    );
            if (empty($this->gridViewPagerParams))
            {
                return $defaultGridViewPagerParams;
            }
            else
            {
                return array_merge($defaultGridViewPagerParams, $this->gridViewPagerParams);
            }
        }

        protected function getShowTableOnEmpty()
        {
            return true;
        }

        protected function getEmptyText()
        {
            return $this->emptyText;
        }

        public function setEmptyText($text)
        {
            $this->emptyText = $text;
        }

        public function getGridViewId()
        {
            return $this->gridId . $this->gridIdSuffix;
        }

        protected function getCGridViewFirstColumn()
        {
            $checked = 'in_array($data->id, array(' . implode(',', $this->selectedIds) . '))'; // Not Coding Standard
            $checkBoxHtmlOptions = array();
            $firstColumn = array(
                    'class'               => 'CheckBoxColumn',
                    'checked'             => $checked,
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowSelector', // Always specify this as -rowSelector.
                    'checkBoxHtmlOptions' => $checkBoxHtmlOptions,
                );
            return $firstColumn;
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         */
         protected function getCGridViewColumns()
         {
            $columns = array();
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
                            $columnClassName = $columnInformation['type'] . 'ListViewColumnAdapter';
                            $columnAdapter  = new $columnClassName($columnInformation['attributeName'], $this, array_slice($columnInformation, 1));
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

        protected function resolveMetadata()
        {
            if ($this->listAttributesSelector != null)
            {
                return $this->listAttributesSelector->getResolvedMetadata();
            }
            return self::getMetadata();
        }

        protected function getResolvedMetadata()
        {
            if ($this->resolvedMetadata != null)
            {
                return $this->resolvedMetadata;
            }
            $this->resolvedMetadata = $this->resolveMetadata();
            return $this->resolvedMetadata;
        }

        protected function getCGridViewBeforeAjaxUpdate()
        {
            if ($this->rowsAreSelectable)
            {
                return 'js:function(id, options) { makeSmallLoadingSpinner(true, "#" + id); addListViewSelectedIdsToUrl(id, options); }';
            }
            else
            {
                return 'js:function(id, options) { makeSmallLoadingSpinner(true, "#" + id); }';
            }
        }

        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                        var $data = $(data);
                        jQuery.globalEval($data.filter("script").last().text());
                    }';
            // End Not Coding Standard
        }

        /**
         * Returns meta data for use in automatically generating the view.
         * The meta data is comprised of columns. The parameters match the
         * parameters used in CGridView. See link below for more information.
         * http://www.yiiframework.com/doc/api/1.1/CGridView/
         *
         * The below example is a simple listview with the 'id' and 'name' attributes
         * The 'name' column has a hyperlink to the detail view for that record.
         *
         * @code
            <?php
                $metadata = array(
                    array(
                        'class' => 'CDataColumn',
                        'name'  => 'id',
                    ),
                    array(
                        'class'           => 'CLinkColumn',
                        'header'          => Zurmo::t('Core', 'Name'),
                        'labelExpression' => '$data->name',
                        'urlExpression'   => 'Yii::app()->createUrl("/{$this->grid->getOwner()->getModule()->getId()}/{$this->grid->getOwner()->getId()}/details", array("id" => $data->id))',
                    )
                );
            ?>
         * @endcode
         */
        public static function getDefaultMetadata()
        {
            return array();
        }

        protected function getCGridViewSelectableRowsCount()
        {
            if ($this->rowsAreSelectable)
            {
                return 2;
            }
            else
            {
                return 0;
            }
        }

        protected function getCGridViewLastColumn()
        {
            $url  = 'Yii::app()->createUrl("' . $this->getGridViewActionRoute('edit');
            $url .= '", array("id" => $data->id))';
            return array(
                'class'           => 'ButtonColumn',
                'template'        => '{update}',
                'buttons' => array(
                    'update' => array(
                    'url' => $url,
                    'imageUrl'        => false,
                    'options'         => array('class' => 'pencil', 'title' => 'Update'),
                    'label'           => '!'
                    ),
                ),
            );
        }

        protected function getGridViewMenuColumn()
        {
            $metadata = $this::getMetadata();
            $content = null;
            if (isset($metadata['global']['rowMenu']) && is_array($metadata['global']['rowMenu']['elements']))
            {
                return array(
                    'class'           => 'RowMenuColumn',
                    'rowMenu'         => $metadata['global']['rowMenu'],
                    'listView'        => $this,
                    'redirectUrl'     => ArrayUtil::getArrayValue($this->params, 'redirectUrl'),
                    'modelClassName'  => $this->modelClassName
                );
            }
            return $content;
        }

        protected function getGridViewActionRoute($action, $moduleId = null)
        {
            if ($moduleId == null)
            {
                $moduleId = $this->moduleId;
            }
            return '/' . $moduleId . '/' . $this->controllerId . '/' . $action;
        }

        protected function getListActionId()
        {
            return 'list';
        }

        public function getLinkString($attributeString, $attribute)
        {
            $string  = 'ZurmoHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'Yii::app()->createUrl("' .
                        $this->getGridViewActionRoute('details') . '", array("id" => $data->id))';
            $string .= ')';
            return $string;
        }

        public function getRelatedLinkString($attributeString, $attributeName, $moduleId)
        {
            $string  = 'ListView::resolveRelatedListStringContent($data->' . $attributeName . '->id, ZurmoHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'Yii::app()->createUrl("' .
                        $this->getGridViewActionRoute('details', $moduleId) . '",
                        array("id" => $data->' . $attributeName . '->id))';
            $string .= '))';
            return $string;
        }

        public static function resolveRelatedListStringContent($modelId, $linkStringContent)
        {
            if ($modelId  > 0)
            {
                return $linkStringContent;
            }
        }

        public static function getDesignerRulesType()
        {
            return 'ListView';
        }

        /**
         * Module class name for models linked from rows in the grid view.
         */
        protected function getActionModuleClassName()
        {
            return get_class(Yii::app()->getModule($this->moduleId));
        }

        protected function getDataProvider()
        {
            return $this->dataProvider;
        }

        protected function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/ListViewUtils.js');
        }

        public function getModuleId()
        {
            return $this->moduleId;
        }

        public function getControllerId()
        {
            return $this->controllerId;
        }
    }
?>
