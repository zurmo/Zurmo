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
     * Base class for working with import temp table data
     */
    abstract class ImportTempTableListView extends ListView
    {
        const EXPANDABLE_ANALYSIS_CONTENT_TYPE = 'Analysis';

        const EXPANDABLE_IMPORT_RESULTS_CONTENT_TYPE = 'Import Results';

        /**
         * @var ImportDataProvider
         */
        protected $dataProvider;

        /**
         * @var bool
         */
        protected $rowsAreExpandable = false;

        /**
         * @var null|array
         */
        protected $mappingData;

        /**
         * @var string
         */
        protected $importRulesType;

        /**
         * @var ImportResultsConfigurationForm
         */
        protected $configurationForm;

        protected $importId;

        abstract protected function resolveSecondColumn();

        abstract protected function getDefaultRoute();

        abstract protected function getResultsFilterRadioElementClassName();

        public static function resolveAnalysisStatusLabel($data)
        {
            return ImportDataAnalyzer::getStatusLabelAndVisualIdentifierContentByType((int)$data->analysisStatus);
        }

        public static function resolveResultStatusLabel($data)
        {
            return ImportRowDataResultsUtil::getStatusLabelAndVisualIdentifierContentByType((int)$data->status);
        }

        /**
         * Override and implement in children classes
         * @throws NotImplementedException
         */
        protected static function getExpandableContentType()
        {
            throw new NotImplementedException();
        }

        protected static function resolveHeaderLabelByColumnNameAndLabel($columnName, $label)
        {
            if ($label == null)
            {
                $headerLabel = static::resolveColumnCountByName($columnName);
            }
            else
            {
                $headerLabel = $label;
            }
            return $headerLabel;
        }

        protected static function resolveColumnCountByName($columnName)
        {
            $columnNameParts = explode('_', $columnName);
            if (count($columnNameParts) != 2)
            {
                $columnNameCount = null;
            }
            else
            {
                $columnNameCount = $columnNameParts[1];
            }
            return  Zurmo::t('ImportModule', 'Column {n}', array('{n}' => $columnNameCount));
        }

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param ImportDataProvider $dataProvider
         * @param $mappingData
         * @param $importRulesType
         * @param ImportResultsConfigurationForm $configurationForm
         * @param array $importId
         * @param null $gridIdSuffix
         */
        public function __construct( $controllerId, $moduleId, ImportDataProvider $dataProvider, $mappingData, $importRulesType,
                                     ImportResultsConfigurationForm $configurationForm, ZurmoActiveForm $zurmoActiveForm, $importId, $gridIdSuffix = null)
        {
            parent::__construct($controllerId, $moduleId, 'NotUsed', $dataProvider, array(), false, $gridIdSuffix);
            $this->rowsAreSelectable = false;
            $this->mappingData       = $mappingData;
            $this->importRulesType   = $importRulesType;
            $this->configurationForm = $configurationForm;
            $this->zurmoActiveForm   = $zurmoActiveForm;
            $this->importId          = $importId;
            $this->gridId            = 'import-temp-table-list-view';
        }

        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content  = $this->renderConfigurationForm();
            $content .= $this->renderViewToolBar();
            $content .= $cClipWidget->getController()->clips['ListView'] . "\n";
            if ($this->rowsAreSelectable)
            {
                $content .= ZurmoHtml::hiddenField($this->gridId . $this->gridIdSuffix . '-selectedIds', implode(",", $this->selectedIds)) . "\n"; // Not Coding Standard
            }
            $content .= $this->renderScripts();
            return ZurmoHtml::tag('div', array('class' => 'left-column full-width'), $content);
        }

        public function getLinkString($attributeString, $attribute)
        {
            throw new NotSupportedException();
        }

        /**
         * @return string
         */
        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        /**
         * @return string
         */
        protected function getGridViewWidgetPath()
        {
            return 'application.modules.import.widgets.ImportTempTableExtendedGridView';
        }

        /**
         * @return array
         */
        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(), array('columnLabelsByName' => $this->resolveColumnLabelsByName(),
                                                                   'enableSorting'      => false,
                                                                   'expandableRows'     => $this->rowsAreExpandable()));
        }

        /**
         * @return array
         */
        protected function getCGridViewPagerParams()
        {
            return array(
                'firstPageLabel'   => '<span>first</span>',
                'prevPageLabel'    => '<span>previous</span>',
                'nextPageLabel'    => '<span>next</span>',
                'lastPageLabel'    => '<span>last</span>',
                'class'            => 'SimpleListLinkPager',
                'paginationParams' => GetUtil::getData(),
                'route'            => $this->getDefaultRoute(),
            );
        }

        protected function resolveColumnLabelsByName()
        {
            $columnLabelsByName = array();
            $headerRow = ImportDatabaseUtil::getFirstRowByTableName($this->dataProvider->getTableName());
            foreach ($headerRow as $columnName => $label)
            {
                if (!in_array($columnName, ImportDatabaseUtil::getReservedColumnNames()) &&
                    $this->mappingData[$columnName]['type'] == 'importColumn' &&
                    $this->mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
                {
                    if (!$this->dataProvider->hasHeaderRow())
                    {
                        $label = static::resolveColumnCountByName($columnName);
                    }
                    $columnLabelsByName[$columnName] = $label;
                }
            }
            return $columnLabelsByName;
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         * @return array
         */
        protected function getCGridViewColumns()
        {
            $columns = array();

            if ($this->rowsAreExpandable())
            {
                $firstColumn = array(
                    'class'                 => 'ImportDrillDownColumn',
                    'id'                    => $this->gridId . $this->gridIdSuffix . '-rowDrillDown',
                    'expandableContentType' => static::getExpandableContentType(),
                    'htmlOptions'           => array('class' => 'hasDrillDownLink')
                );
                array_push($columns, $firstColumn);
            }
            array_push($columns, $this->resolveSecondColumn());
            $headerRow = ImportDatabaseUtil::getFirstRowByTableName($this->dataProvider->getTableName());
            foreach ($headerRow as $columnName => $label)
            {
                if (!in_array($columnName, ImportDatabaseUtil::getReservedColumnNames()) &&
                    $this->mappingData[$columnName]['type'] == 'importColumn' &&
                    $this->mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
                {
                    if (!$this->dataProvider->hasHeaderRow())
                    {
                        $label = static::resolveColumnCountByName($columnName);
                    }
                    $params           = array();
                    $columnAdapter    = new BeanStringListViewColumnAdapter($columnName, $this, $params);
                    $column           = $columnAdapter->renderGridViewData();
                    $column['header'] = static::resolveHeaderColumnContent($columnName, $label);
                    $this->columnLabelsByName[$columnName] = $column['header'];
                    if (!isset($column['class']))
                    {
                        $column['class'] = 'DataColumn';
                    }
                    array_push($columns, $column);
                }
            }
            return $columns;
        }

        protected function resolveHeaderColumnContent($columnName, $label)
        {
            $content  = static::resolveHeaderLabelByColumnNameAndLabel($columnName, $label);
            if ($this->mappingData[$columnName]['attributeIndexOrDerivedType'] != null)
            {
                $attributeIndexOrDerivedType = $this->mappingData[$columnName]['attributeIndexOrDerivedType'];
                $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                        $this->importRulesType, $attributeIndexOrDerivedType);
                $content .= ZurmoHtml::tag('span', array('class' => 'icon-import-mapping'), '&darr;');
                $content .= $attributeImportRules->getDisplayLabel();
            }
            return $content;
        }

        /**
         * @return string
         */
        protected function getCGridViewBeforeAjaxUpdate()
        {
            return 'js:function(id, options) {$(this).makeSmallLoadingSpinner(true, "#"+id + " > .list-preloader"); }'; // Not Coding Standard
        }

        /**
         * Do not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        /**
         * @return bool
         */
        protected function rowsAreExpandable()
        {
            return true;
        }

        protected function getUniquePageId()
        {
            return get_called_class();
        }

        protected function renderConfigurationForm()
        {
            $content = $this->renderConfigurationFormLayout($this->zurmoActiveForm);
            $this->registerConfigurationFormLayoutScripts($this->zurmoActiveForm);
            return $content;
        }

        /**
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $elementClassName = $this->getResultsFilterRadioElementClassName();
            $content      = null;
            $content .= '<div class="horizontal-line filter-portlet-model-bar import-results-toolbar">';
            $element = new $elementClassName($this->configurationForm, 'filteredByStatus', $form);
            $element->editableTemplate =  '<div id="ImportResultsConfigurationForm_filteredByStatus_area">{content}</div>';
            $content .= $element->render();
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $url       = Yii::app()->createUrl($this->moduleId . '/' . $this->getDefaultRoute());
            $urlScript = 'js:$.param.querystring("' . $url . '", "' .
                         $this->dataProvider->getPagination()->pageVar . '=1&id=' . // Not Coding Standard
                         $this->importId . '&step=complete&ajax=' . $this->gridId . '&pageSize=' . $this->dataProvider->getPagination()->getPageSize() . '")'; // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'url'        =>  $urlScript,
                    'beforeSend' => 'js:function(){$(this).makeSmallLoadingSpinner(true, "#' .
                                    $this->getGridViewId() . '"); $("#' .
                                    $this->getUniquePageId() . '").find(".cgrid-view").addClass("loading");}',
                    'success'    => 'js:function(data)
                    {
                                    $("#' . $this->getUniquePageId() . '").replaceWith(data);
                    }',
            ));
            Yii::app()->clientScript->registerScript($this->getUniquePageId(), "
            $('#ImportResultsConfigurationForm_filteredByStatus_area').buttonset();
            $('#ImportResultsConfigurationForm_filteredByStatus_area').change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }
    }
?>