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
                $metadata        = self::getMetadata();
                $perUserMetadata = $metadata['perUser'];
                $this->resolveEvaluateSubString($perUserMetadata, null);
                $formModel->setAttributes($perUserMetadata);
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
                return Zurmo::t('ZurmoModule', $msg, LabelUtil::getTranslationParamsForAllModules());
            }
            return null;
        }
    }
?>
