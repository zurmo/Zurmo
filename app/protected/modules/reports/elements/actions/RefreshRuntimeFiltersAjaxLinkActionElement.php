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
     * A class used to render a refresh ajax link for the report detail view
     */
    class RefreshRuntimeFiltersAjaxLinkActionElement extends AjaxLinkActionElement
    {
        /**
         * @param $controllerId
         * @param $moduleId
         * @param $modelId
         * @param array $params
         */
        public function __construct($controllerId, $moduleId, $modelId, $params = array())
        {
            $params['htmlOptions'] = array('id' => 'reset-runtime-filters', 'class'  => 'attachLoading z-button white-button');
            parent::__construct($controllerId, $moduleId, $modelId, $params);
        }

        /**
         * @return null
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Reset');
        }

        /**
         * @return mixed
         */
        protected function getDefaultRoute()
        {
            return Yii::app()->createUrl('reports/default/resetRuntimeFilters/',
                                         array('id' => $this->modelId )
            );
        }

        /**
         * @return string
         */
        protected function getLabel()
        {
            $content  = ZurmoHtml::tag('span', array('class' => 'z-spinner'), null);
            $content .= ZurmoHtml::tag('span', array('class' => 'z-icon'), null);
            $content .= ZurmoHtml::tag('span', array('class' => 'z-label'), $this->getDefaultLabel());
            return $content;
        }

        /**
         * @return array
         */
        protected function getAjaxOptions()
        {
            return array(
                    'beforeSend' => 'js:function()
                                    {
                                        makeOrRemoveLoadingSpinner(true, "#reset-runtime-filters");
                                        $("#reset-runtime-filters").addClass("attachLoadingTarget");
                                        $("#reset-runtime-filters").addClass("loading");
                                        $("#reset-runtime-filters").addClass("loading-ajax-submit");
                                    } ',
                    'success'    => 'js:function()
                                    {
                                        $("#RuntimeFiltersForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportResultsGridForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportChartForPortletView").find(".refreshPortletLink").click();
                                        $("#ReportSQLForPortletView").find(".refreshPortletLink").click();
                                    }');
        }
    }
?>