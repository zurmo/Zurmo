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

    /**
     * The portlet view for lead detail view
     */
    class LeadDetailsPortletView extends LeadEditAndDetailsView implements PortletViewInterface
    {
        protected $params;
        protected $viewData;
        protected $uniqueLayoutId;

        /**
         * @param array $viewData
         * @param array $params
         * @param string $uniqueLayoutId
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('$params["relationModel"] instanceof RedBeanModel || $params["relationModel"] instanceof ModelForm');
            assert('isset($params["portletId"])');
            assert('isset($params["redirectUrl"])');
            $this->modelClassName    = $this->getModelClassName();
            $this->viewData          = $viewData;
            $this->params            = $params;
            $this->uniqueLayoutId    = $uniqueLayoutId;
            $this->gridIdSuffix      = $uniqueLayoutId;
            $this->rowsAreSelectable = false;
            $this->gridId            = 'list-view';
            $this->controllerId      = $this->resolveControllerId();
            $this->moduleId          = $this->resolveModuleId();
            parent::__construct('Details', $this->controllerId, $this->moduleId, $params["relationModel"]);
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
            return 'LeadsModule';
        }

        /**
         * Override to add a description for the view to be shown when adding a portlet
         */
        public static function getPortletDescription()
        {
        }

        protected static function resolveMetadataClassNameToUse()
        {
            return 'LeadEditAndDetailsView';
        }

        /**
         * Controller Id for the link to models from rows in the grid view.
         */
        private function resolveControllerId()
        {
            return 'default';
        }

        /**
         * Module Id for the link to models from rows in the grid view.
         */
        private function resolveModuleId()
        {
            return 'leads';
        }

        public static function canUserConfigure()
        {
            return false;
        }

        protected function renderTitleContent()
        {
            return null;
        }

        public static function canUserRemove()
        {
            return false;
        }

        public static function getDesignerRulesType()
        {
            return 'DetailsPortletView';
        }

        /**
         * Override and return null so we can render the actionElementMenu in the portletHeaderContent
         * @return null
         */
        protected function resolveAndRenderActionElementMenu()
        {
            return null;
        }

        /**
         * @return null|string
         */
        public function renderPortletHeadContent()
        {
            return $this->renderWrapperAndActionElementMenu();
        }

        /**
         * Just show 'Details' because the business card view has the person's name
         * @return string
         */
        public function getTitle()
        {
            return Zurmo::t('Core', 'Details');
        }

        protected function resolveLeftContentForSlidingPanels($content)
        {
            $content  = $this->makeFirstSlidingPanelContent($content);
            $content .= $this->makeSecondSlidingPanelContent();
            return $content;
        }

        protected function makeFirstSlidingPanelContent($content)
        {
            return PersonSlidingPanelsUtil::makeFirstSlidingPanelContent((int)$this->params['portletId'], $content);
        }

        protected function makeSecondSlidingPanelContent()
        {
            $layout  = new PersonCardViewLayout($this->model);
            $content = $layout->renderContent();
            return PersonSlidingPanelsUtil::makeSecondSlidingPanelContent((int)$this->params['portletId'], $content);
        }

        protected function renderAfterDetailsTable()
        {
            return PersonSlidingPanelsUtil::renderToggleLinkContent((int)$this->params['portletId']);
        }
    }
?>
