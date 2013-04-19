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
     * Base class used for wrapping a view of a component used by the report detail view to show results.
     */
    abstract class ReportResultsComponentForPortletView extends ConfigurableMetadataView implements PortletViewInterface
    {
        /**
         * Portlet parameters passed in from the portlet.
         * @var array
         */
        protected $params;

        /**
         * @var string
         */
        protected $controllerId;

        /**
         * @var string
         */
        protected $moduleId;

        /**
         * @var string
         */
        protected $uniqueLayoutId;

        /**
         * @var array
         */
        protected $viewData;

        /**
         * @return bool
         */
        public static function canUserConfigure()
        {
            return false;
        }

        /**
         * What kind of PortletRules this view follows
         * @return string PortletRulesType
         */
        public static function getPortletRulesType()
        {
            return 'ModelDetails';
        }

        /**
         * The view's module class name.
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'ReportsModule';
        }

        /**
         * Some extra assertions are made to ensure this view is used in a way that it supports.
         * @param array $viewData
         * @param array $params
         * @param string $uniqueLayoutId
         */
        public function __construct($viewData, $params, $uniqueLayoutId)
        {
            assert('is_array($viewData) || $viewData == null');
            assert('isset($params["relationModuleId"]) && $params["relationModuleId"] == "reports"');
            assert('isset($params["relationModel"]) && get_class($params["relationModel"]) == "Report"');
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = $params['relationModuleId'];
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        /**
         * @return null
         */
        public function getTitle()
        {
            return null;
        }

        /**
         * @return string
         */
        protected function resolveAndGetPaginationRoute()
        {
            return 'defaultPortlet/myListDetails';
        }

        /**
         * @return array
         */
        protected function resolveAndGetPaginationParams()
        {
            return array_merge(GetUtil::getData(), array('portletId' => $this->params['portletId']));
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         * @return string
         */
        protected function getPortletDetailsUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/defaultPortlet/details',
                                                        array_merge(GetUtil::getData(), array(
                                                            'portletId'      => $this->params['portletId'],
                                                            'uniqueLayoutId' => $this->uniqueLayoutId,
                                                            'id'             => $this->params['relationModel']->getId(),
                                                            'runReport'      => true)));
        }

        /**
         * Url to go to after an action is completed. Typically returns user to either a model's detail view or
         * the home page dashboard.
         * @return string
         */
        protected function getNonAjaxRedirectUrl()
        {
            return Yii::app()->createUrl('/' . $this->moduleId . '/default/details',
                                                        array( 'id' => $this->params['relationModel']->getId()));
        }

        /**
         * @return string
         */
        protected function renderRefreshLink()
        {
            $containerId = get_class($this);
            return ZurmoHtml::ajaxLink('refresh', $this->getPortletDetailsUrl(), array(
                    'type'   => 'GET',
                    'beforeSend' => 'function ( xhr ) {jQuery("#' . $containerId .
                                    '").html("");makeLargeLoadingSpinner(true, "#' . $containerId . '");}',
                    'update' => '#' . get_class($this)),
                    array('id'        => 'refreshPortletLink-' . get_class($this),
                          'class'     => 'refreshPortletLink',
                          'style'     => "display:none;",
                          'live'      => true,
                          'namespace' => 'refresh'));
        }
    }
?>