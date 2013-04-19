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
     * Base class used for wrapping a view of a report chart
     */
    class ReportChartView extends View
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
         * @var SummationReportDataProvider
         */
        protected $dataProvider;

        /**
         * @var string
         */
        protected $uniqueLayoutId;

        /**
         * @var int
         */
        protected static $maximumGroupsPerChart = 100;

        public static function setMaximumGroupsPerChart($value)
        {
            assert('is_int($value)');
            self::$maximumGroupsPerChart = $value;
        }

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param SummationReportDataProvider $dataProvider
         * @param string $uniqueLayoutId
         */
        public function __construct($controllerId, $moduleId, SummationReportDataProvider $dataProvider, $uniqueLayoutId)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($uniqueLayoutId)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->dataProvider           = $dataProvider;
            $this->uniqueLayoutId         = $uniqueLayoutId;
        }

        /**
         * @return string
         */
        public function renderContent()
        {
            if ($this->dataProvider->calculateTotalItemCount() > self::$maximumGroupsPerChart)
            {
                return $this->renderMaximumGroupsContent();
            }
            return $this->renderChartContent();
        }

        /**
         * @return string
         */
        protected function renderChartContent()
        {
            $reportDataProviderToAmChartMakerAdapter = $this->dataProvider->makeReportDataProviderToAmChartMakerAdapter();
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->categoryField    = ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesDisplayLabelName(1);
            $amChart->data             = $reportDataProviderToAmChartMakerAdapter->getData();
            $amChart->id               = $this->uniqueLayoutId;
            $amChart->type             = $reportDataProviderToAmChartMakerAdapter->getType();
            $amChart->xAxisName        = $this->dataProvider->resolveFirstSeriesLabel();
            $amChart->yAxisName        = $this->dataProvider->resolveFirstRangeLabel();
            $amChart->yAxisUnitContent = $this->resolveYAxisUnitContent();
            if ($reportDataProviderToAmChartMakerAdapter->isStacked())
            {
                for ($i = 1; $i < ($reportDataProviderToAmChartMakerAdapter->getSecondSeriesValueCount() + 1); $i++)
                {
                    $title       = $reportDataProviderToAmChartMakerAdapter->getSecondSeriesDisplayLabelByKey($i);
                    $balloonText = '"[[' . ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($i) .
                                   ']] - [[' . ReportDataProviderToAmChartMakerAdapter::resolveFirstRangeDisplayLabelName($i) .
                                   ']] : [[' . ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesFormattedValueName($i) .
                                   ']] - [[' . ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesDisplayLabelName($i) .
                                   ']] : [[' . ReportDataProviderToAmChartMakerAdapter::resolveSecondSeriesFormattedValueName($i) .
                                   ']] "';
                    $amChart->addSerialGraph(ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName($i), 'column',
                                             array('title' => '"' . CJavaScript::quote($title) . '"', 'balloonText' => $balloonText));
                }
            }
            else
            {
                $amChart->addSerialGraph(ReportDataProviderToAmChartMakerAdapter::resolveFirstSeriesValueName(1), 'column');
            }
            $scriptContent      = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->uniqueLayoutId, $scriptContent);
            $cClipWidget        = new CClipWidget();
            $cClipWidget->beginClip("Chart" . $this->uniqueLayoutId);
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $this->uniqueLayoutId));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart' . $this->uniqueLayoutId];
        }

        /**
         * @return string
         */
        protected function renderMaximumGroupsContent()
        {
            $content  = '<div class="general-issue-notice"><span class="icon-notice"></span><p>';
            $content .= Zurmo::t('ReportsModule', 'Your report has too many groups to plot. ' .
                                          'Please adjust the filters to reduce the number below {maximum}.',
                        array('{maximum}' => self::$maximumGroupsPerChart));
            $content .= '</p></div>';
            return $content;
        }

        /**
         * @return null
         * @throws NotSupportedException if the currency conversion type is invalid
         */
        protected function resolveYAxisUnitContent()
        {
            if ($this->dataProvider->getReport()->getCurrencyConversionType() ==
                Report::CURRENCY_CONVERSION_TYPE_ACTUAL)
            {
                return null;
            }
            elseif ($this->dataProvider->getReport()->getCurrencyConversionType() ==
                Report::CURRENCY_CONVERSION_TYPE_BASE)
            {
                //Assumes base conversion is done using sql math
                return Yii::app()->locale->getCurrencySymbol(Yii::app()->currencyHelper->getBaseCode());
            }
            elseif ($this->dataProvider->getReport()->getCurrencyConversionType() ==
                Report::CURRENCY_CONVERSION_TYPE_SPOT)
            {
                //Assumes base conversion is done using sql math
                return Yii::app()->locale->getCurrencySymbol(
                           $this->dataProvider->getReport()->getSpotConversionCurrencyCode());
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>