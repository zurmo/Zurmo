<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * A chart view for displaying data in a chart.
     *
     */
    abstract class ChartView extends ConfigurableMetadataView
    {
        protected $params;

        protected $uniqueLayoutId;

        protected $viewData;

        /**
         * @return string of chart data provider type.
         */
        abstract public function getChartDataProviderType();

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public static function canUserConfigure()
        {
            return true;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        public function getConfigurationView()
        {
            $formModel = new ChartForm();
            if ($this->viewData!='')
            {
                $formModel->setAttributes($this->viewData);
            }
            else
            {
                $metadata = self::getMetadata();
                $formModel->setAttributes($metadata['perUser']);
            }
            return new ChartConfigView($formModel, $this->params);
        }

        public function getChartParams()
        {
            if (in_array($this->resolveViewAndMetadataValueByName('type'), array('Pie2D', 'Pie3D', 'Donut2D')))
            {
                $showValues = true;
            }
            else
            {
                $showValues = false;
            }
            $params = array(
                'showValues' => $showValues,
            );
            return $params;
        }

        protected function resolveContentIfCurrentUserCanAccessChartByModule($moduleClassName, $modulePluralLabelName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modulePluralLabelName)');
            if (!RightsUtil::canUserAccessModule($moduleClassName, Yii::app()->user->userModel))
            {
                $msg  = 'You cannot view this chart because you do not have access ';
                $msg .= 'to the ' . $modulePluralLabelName . ' module.';
                return Yii::t('Default', $msg, LabelUtil::getTranslationParamsForAllModules());
            }
            return null;
        }
    }
?>
