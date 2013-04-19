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
     * Base class for working with report results in a grid
     */
    abstract class ReportResultsGridView extends View implements ListViewInterface
    {
        /**
         * @var string
         */
        protected $controllerId;

        /**
         * @var string
         */
        protected $moduleId;

        /**
         * @var ReportDataProvider
         */
        protected $dataProvider;

        /**
         * @var bool
         */
        protected $rowsAreExpandable = false;

        /**
         * Unique identifier of the list view widget. Allows for multiple list view
         * widgets on a single page.
         *  @var null|string
         */
        protected $gridId;

        /**
         * Additional unique identifier.
         * @see $gridId
         */
        protected $gridIdSuffix;

        /**
         * Array containing CGridViewPagerParams
         */
        protected $gridViewPagerParams = array();

        /**
         * @var null|string
         */
        protected $emptyText = null;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param ReportDataProvider $dataProvider
         * @param null|string $gridIdSuffix
         * @param array $gridViewPagerParams
         */
        public function __construct(
            $controllerId,
            $moduleId,
            ReportDataProvider $dataProvider,
            $gridIdSuffix = null,
            $gridViewPagerParams = array()
        )
        {
            assert('is_array($gridViewPagerParams)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->dataProvider           = $dataProvider;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridViewPagerParams    = $gridViewPagerParams;
            $this->gridId                 = 'report-results-grid-view';
        }

        /**
         * @return string
         */
        public function getGridViewId()
        {
            return $this->gridId . $this->gridIdSuffix;
        }

        /**
         * @param string $attributeString
         * @param string $attribute
         * @return string
         */
        public function getLinkString($attributeString, $attribute)
        {
            $string  = 'ZurmoHtml::link(';
            $string .=  $attributeString . ', ';
            $string .= 'ReportResultsGridUtil::makeUrlForLink("' . $attribute . '", $data)';
            $string .= ', array("target" => "new"))';
            return $string;
        }

        /**
         * @return string
         */
        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "{summary}\n{items}\n{pager}" . $preloader;
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
        protected static function getSummaryText()
        {
            return Zurmo::t('ReportsModule', '{count} result(s)');
        }

        /**
         * @return string
         */
        protected static function getSummaryCssClass()
        {
            return 'summary';
        }

        /**
         * @return string
         * @throws NotSupportedException if the data provider is not valid
         */
        protected function renderContent()
        {
            if (!$this->isDataProviderValid())
            {
                throw new NotSupportedException();
            }
            return $this->renderResultsGridContent();
        }

        /**
         * @return string
         */
        protected function renderResultsGridContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ReportResultsGridView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content  = $cClipWidget->getController()->clips['ReportResultsGridView'] . "\n";
            $content .= $this->renderScripts();
            return $content;
        }

        /**
         * @return string
         */
        protected function getGridViewWidgetPath()
        {
            return 'application.modules.reports.widgets.ReportResultsExtendedGridView';
        }

        /**
         * @return array
         */
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
                'dataProvider'         => $this->dataProvider,
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
                'enableSorting'        => false,
                'expandableRows'       => $this->rowsAreExpandable(),
                'leadingHeaders'       => $this->getLeadingHeaders(),
            );
        }

        /**
         * @return array
         */
        protected function getCGridViewPagerParams()
        {
            $defaultGridViewPagerParams = array(
                'firstPageLabel'   => '<span>first</span>',
                'prevPageLabel'    => '<span>previous</span>',
                'nextPageLabel'    => '<span>next</span>',
                'lastPageLabel'    => '<span>last</span>',
                'class'            => 'SimpleListLinkPager',
                'paginationParams' => GetUtil::getData(),
                'route'            => 'defaultPortlet/details',
            );
            return $this->resolveDefaultGridViewPagerParams($defaultGridViewPagerParams);
        }

        /**
         * @param $defaultGridViewPagerParams
         * @return array
         */
        protected function resolveDefaultGridViewPagerParams($defaultGridViewPagerParams)
        {
            if (empty($this->gridViewPagerParams))
            {
                return $defaultGridViewPagerParams;
            }
            else
            {
                return array_merge($defaultGridViewPagerParams, $this->gridViewPagerParams);
            }
        }

        /**
         * @return bool
         */
        protected function getShowTableOnEmpty()
        {
            return true;
        }

        /**
         * @return null|string
         */
        protected function getEmptyText()
        {
            return $this->emptyText;
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
                    'class'               => 'DrillDownColumn',
                    'id'                  => $this->gridId . $this->gridIdSuffix . '-rowDrillDown',
                    'htmlOptions'         => array('class' => 'hasDrillDownLink')
                );
                array_push($columns, $firstColumn);
            }
            foreach ($this->dataProvider->resolveDisplayAttributes() as $key => $displayAttribute)
            {
                if (!$displayAttribute->queryOnly)
                {
                    $columnClassName  = $this->resolveColumnClassNameForListViewColumnAdapter($displayAttribute);
                    $attributeName    = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                    $params           = $this->resolveParamsForColumnElement($displayAttribute);
                    $columnAdapter    = new $columnClassName($attributeName, $this, $params);
                    $column           = $columnAdapter->renderGridViewData();
                    $column['header'] = $displayAttribute->label;
                    if (!isset($column['class']))
                    {
                        $column['class'] = 'DataColumn';
                    }
                    array_push($columns, $column);
                }
            }
            return $columns;
        }

        /**
         * @param DisplayAttributeForReportForm $displayAttribute
         * @return string
         */
        protected function resolveColumnClassNameForListViewColumnAdapter(DisplayAttributeForReportForm $displayAttribute)
        {
            $displayElementType = $displayAttribute->getDisplayElementType();
            if (@class_exists($displayElementType . 'ForReportListViewColumnAdapter'))
            {
                return $displayElementType . 'ForReportListViewColumnAdapter';
            }
            else
            {
                return $displayElementType . 'ListViewColumnAdapter';
            }
        }

        /**
         * @param DisplayAttributeForReportForm $displayAttribute
         * @return array
         */
        protected function resolveParamsForColumnElement(DisplayAttributeForReportForm $displayAttribute)
        {
            $params  = array();
            if ($displayAttribute->isALinkableAttribute() == 'name')
            {
                $params['isLink'] = true;
            }
            elseif ($displayAttribute->isATypeOfCurrencyValue())
            {
                $params['currencyValueConversionType'] = $this->dataProvider->getReport()->getCurrencyConversionType();
                $params['spotConversionCurrencyCode']  = $this->dataProvider->getReport()->getSpotConversionCurrencyCode();
                $params['fromBaseToSpotRate']  = $this->dataProvider->getReport()->getFromBaseToSpotRate();
            }
            return $params;
        }

        /**
         * @return string
         */
        protected function getCGridViewBeforeAjaxUpdate()
        {
            return 'js:function(id, options) {makeSmallLoadingSpinner(true, "#"+id + " > .list-preloader"); }'; // Not Coding Standard
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

        protected function renderScripts()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/ListViewUtils.js');
        }

        /**
         * @return bool
         */
        protected function rowsAreExpandable()
        {
            if (count($this->dataProvider->getReport()->getDrillDownDisplayAttributes()) > 0)
            {
                return true;
            }
            return false;
        }

        /**
         * Override in child as neededs
         */
        protected function getLeadingHeaders()
        {
        }
    }
?>