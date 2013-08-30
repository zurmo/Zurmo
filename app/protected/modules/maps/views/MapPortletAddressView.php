<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class MapPortletAddressView extends View implements PortletViewInterface, RelatedPortletViewInterface
    {
        protected $params;

        protected $viewData;

        protected $uniqueLayoutId;

        protected $geoCodeQueryData;

        protected $containerIdSuffix;

        /**
         * @param array $viewData
         * @param array $params
         * @param array $uniqueLayoutId
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = $params['relationModuleId'];
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
            $this->controllerId   = $this->resolveControllerId();
            $this->moduleId       = $this->resolveModuleId();
            $this->geoCodeQueryData = $this->resolveAddressData();
            $this->containerIdSuffix = $uniqueLayoutId;
        }

        private function resolveControllerId()
        {
            return 'default';
        }

        private function resolveModuleId()
        {
            return 'maps';
        }

        public function getPortletParams()
        {
            return array();
        }

        public static function getPortletRulesType()
        {
            return 'Detail';
        }

        public static function getModuleClassName()
        {
            return 'MapsModule';
        }

        protected function resolveAddressData()
        {
            $modalMapAddressData = array('query'     => $this->params['relationModel']->primaryAddress,
                                         'latitude'  => '',
                                         'longitude' => '');
            return $modalMapAddressData;
        }

        public static function getMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('MapsModule', 'Google Map')",
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            $title  = Zurmo::t('MapsModule', 'Google Map');
            return $title;
        }

        public function renderContent()
        {
            if (!$this->shouldRenderMap())
            {
                $emptyLabel = Zurmo::t('ZurmoModule', 'No address found');
                return ZurmoHtml::tag('span', array('class' => 'empty'),
                            ZurmoHtml::tag('span', array('class' => 'icon-empty'), '') . $emptyLabel);
            }
            $mapCanvasContainerId = $this->getMapCanvasContainerId();
            $cClipWidget          = new CClipWidget();
            $cClipWidget->beginClip("Map");
            echo "<div id='" . $mapCanvasContainerId . "' class=\"mapCanvasPortlet\"></div>";
            Yii::app()->mappingHelper->renderMapContentForView($this->geoCodeQueryData, $mapCanvasContainerId);
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Map'];
        }

        public function renderPortletHeadContent()
        {
            return false;
        }

        public static function canUserConfigure()
        {
            return false;
        }

        /**
         * Override to add a description for the view to be shown when adding a portlet
         */
        public static function getPortletDescription()
        {
        }

        public static function hasRollupSwitch()
        {
            return false;
        }

        public static function getAllowedOnPortletViewClassNames()
        {
            return array();
        }

        public static function allowMultiplePlacement()
        {
            return false;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public function getMapCanvasContainerId()
        {
            return 'map-canvas' . $this->containerIdSuffix;
        }

        protected function shouldRenderMap()
        {
            if ($this->params['relationModel']->primaryAddress->makeAddress() == null)
            {
                return false;
            }
            return true;
        }
    }
?>