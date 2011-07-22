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
     * The base View for a world clock
     */
    class WorldClockView extends ConfigurableMetadataView implements PortletViewInterface
    {
        protected $params;

        protected $uniqueLayoutId;

        protected $viewData;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            $this->viewData              = $viewData;
            $this->params                = $params;
            $this->uniqueLayoutId        = $uniqueLayoutId;
        }

        public function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("WorldClock");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.WorldClock', array(
                'id' => $this->uniqueLayoutId,
                'options' => array(
                    'uniqueLayoutId'      => $this->uniqueLayoutId,
                    'weatherMetric'       => $this->resolveViewAndMetadataValueByName('temperatureUnit'),
                    'weatherLocationCode' => $this->resolveViewAndMetadataValueByName('weatherLocationCode'),
                    'weatherUrl'          => Yii::app()->createUrl('worldClock/default/getweather'),
                    ),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['WorldClock'];
        }

        public static function getDefaultMetadata()
        {
            return array(
                'perUser' => array(
                    'title' => Yii::t('Default', 'World Clock'),
                    'temperatureUnit' => 'F',
                    'weatherLocationCode' => 'NAM|US|IL|CHICAGO',
                ),
                'global' => array(
                ),
            );
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
            $formModel = new WorldClockForm();
            if ($this->viewData!='')
            {
                $formModel->setAttributes($this->viewData);
            }
            else
            {
                $metadata = self::getMetadata();
                $formModel->setAttributes($metadata['perUser']);
            }
            return new WorldClockConfigView($formModel, $this->params);
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'WorldClock';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'WorldClockModule';
        }
    }
?>
