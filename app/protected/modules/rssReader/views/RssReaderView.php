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
     * A rss reader view for displaying an Rss feed
     *
     */
    class RssReaderView extends ConfigurableMetadataView implements PortletViewInterface
    {
        protected $params;

        protected $uniqueLayoutId;

        protected $viewData;

        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["moduleId"])');
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("RssReader");
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.RssReader', array(
                'id'  => $this->uniqueLayoutId,
                'url' => $this->resolveViewAndMetadataValueByName('url'),
                'limit' => 3,
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['RssReader'];
        }

        public static function getDefaultMetadata()
        {
            return array(
                'perUser' => array(
                    'title' => "eval:Yii::t('Default', 'Zurmo News')",
                    'url'   => 'http://www.zurmo.org/feed',
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
            $formModel = new RssReaderForm();
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
            return new RssReaderConfigView($formModel, $this->params);
        }

        /**
         * What kind of PortletRules this view follows
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'RssReader';
        }

        /**
         * The view's module class name.
         */
        public static function getModuleClassName()
        {
            return 'RssReaderModule';
        }
    }
?>
