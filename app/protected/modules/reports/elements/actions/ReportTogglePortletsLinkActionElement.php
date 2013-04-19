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
     * Class to render link to toggle portlets for a report grid view
     */
    class ReportTogglePortletsLinkActionElement extends LinkActionElement
    {
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
        public function render()
        {
            $content  = null;
            if ($this->hasRuntimeFilters())
            {
                $htmlOptions = array('onClick' => 'js:$(".RuntimeFiltersForPortletView").toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('ReportsModule', 'Filters'), Zurmo::t('ReportsModule', 'Filters'), array('class' => 'label-for-report-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('ReportsModule', 'Filters'), true, $htmlOptions) . $label;
            }
            if ($this->hasChart())
            {
                $htmlOptions = array('onClick' => 'js:$(".ReportChartForPortletView").toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('ReportsModule', 'Chart'), Zurmo::t('ReportsModule', 'Chart'), array('class' => 'label-for-report-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('ReportsModule', 'Chart'), true, $htmlOptions) . $label;
            }
            $htmlOptions = array('onClick' => 'js:$(".ReportResultsGridForPortletView").toggle();');
            $label       = ZurmoHtml::label(Zurmo::t('ReportsModule', 'Grid'), Zurmo::t('ReportsModule', 'Grid'), array('class' => 'label-for-report-widgets'));
            $content    .= ZurmoHtml::checkBox(Zurmo::t('ReportsModule', 'Grid'), true, $htmlOptions) . $label;
            $htmlOptions = array('onClick' => 'js:$(".ReportSQLForPortletView").toggle();');
            $label       = ZurmoHtml::label(Zurmo::t('ReportsModule', 'SQL'), Zurmo::t('ReportsModule', 'SQL'), array('class' => 'label-for-report-widgets'));
            $content    .= ZurmoHtml::checkBox(Zurmo::t('ReportsModule', 'SQL'), false, $htmlOptions) . $label;
            return ZurmoHtml::tag('div', $this->getHtmlOptions(), $content );
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('ReportsModule', 'Toggle Results');
        }

        /**
         * @return null
         */
        protected function getDefaultRoute()
        {
            return null;
        }

        /**
         * @return bool
         */
        protected function hasRuntimeFilters()
        {
            if (!isset($this->params['hasRuntimeFilters']))
            {
                return false;
            }
            return $this->params['hasRuntimeFilters'];
        }

        /**
         * @return bool
         */
        protected function hasChart()
        {
            if (!isset($this->params['hasChart']))
            {
                return false;
            }
            return $this->params['hasChart'];
        }
    }
?>