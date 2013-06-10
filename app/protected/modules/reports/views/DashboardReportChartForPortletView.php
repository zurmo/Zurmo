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
     * A view for displaying report charts on the home page dashboard
     */
    class DashboardReportChartForPortletView extends ReportChartForPortletView
    {
        private $savedReport;

        private $warningMessage;

        private $savedReportHasBeenResolved = false;

        /**
         * What kind of PortletRules this view follows
         * @return string PortletRulesType
         */
        public static function getPortletRulesType()
        {
            return 'Chart';
        }

        public static function getDefaultMetadata()
        {
            return array(
                'perUser' => array(
                    'title'     => "eval:Zurmo::t('ReportsModule', 'Report Chart')",
                    'reportId'  => null,
                ),
                'global' => array(),
            );
        }

        public static function canUserConfigure()
        {
            return true;
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
            assert('isset($params["portletId"])');
            assert('is_string($uniqueLayoutId)');
            $this->moduleId       = 'reports';
            $this->viewData       = $viewData;
            $this->params         = $params;
            $this->uniqueLayoutId = $uniqueLayoutId;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            if ($this->getSavedReport() == null)
            {
                return Zurmo::t('ReportsModule', 'Report Chart');
            }

            return strval($this->getSavedReport());
        }

        public function getConfigurationView()
        {
            $formModel = new ReportSelectForm();
            if ($this->viewData != '')
            {
                $formModel->setAttributes($this->viewData);
            }
            else
            {
                $formModel->reportId = $this->getSavedReportId();
            }
            if ($this->getSavedReport() != null)
            {
                $formModel->reportName = strval($this->getSavedReport());
            }
            return new DashboardReportChartConfigView($formModel, $this->params);
        }

        public function renderContent()
        {
            if ($this->getSavedReportId() == null)
            {
                return $this->renderSelectAReportFirstContent();
            }
            elseif ($this->getSavedReport() == null)
            {
                return $this->renderWarningMessageContent();
            }
            $content = parent::renderContent();
            $this->renderScripts();
            return $content;
        }

        /**
         * @return string
         */
        protected function renderSelectAReportFirstContent()
        {
            $content  = '<div class="general-issue-notice"><span class="icon-notice"></span><p>';
            $content .= Zurmo::t('ReportsModule', 'Select a report with a chart');
            $content .= '</p></div>';
            return $content;
        }

        protected function renderWarningMessageContent()
        {
            $content  = '<div class="general-issue-notice"><span class="icon-notice"></span><p>';
            $content .= $this->warningMessage;
            $content .= '</p></div>';
            return $content;
        }

        protected function getSavedReportId()
        {
            return $this->resolveViewAndMetadataValueByName('reportId');
        }

        protected function renderScripts()
        {
            if (!isset($this->params['dataProvider']))
            {
                // Begin Not Coding Standard
                $script = "$(document).ready(function () {
                                $('#" . $this->getRefreshLinkContainerId() . "').find('.refreshPortletLink').click();
                       });";
                Yii::app()->clientScript->registerScript('loadReportResults-'. $this->uniqueLayoutId, $script);
                // End Not Coding Standard
            }
        }

        protected function getRefreshLinkContainerId()
        {
            return $this->getUniqueChartLayoutId();
        }

        protected function getUniqueChartLayoutId()
        {
            return $this->uniqueLayoutId . '-chart';
        }

        protected function getId()
        {
            return $this->getUniqueChartLayoutId();
        }

        protected function getSavedReport()
        {
            if ($this->savedReport == null && $this->getSavedReportId() != null && !$this->savedReportHasBeenResolved)
            {
                $this->resolveSavedReportAndWarningData();
            }
            return $this->savedReport;
        }

        private function resolveSavedReportAndWarningData()
        {
            try
            {
                $savedReport = SavedReport::getById((int)$this->getSavedReportId());

                if (!ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($savedReport, Permission::READ))
                {
                    $this->warningMessage = Zurmo::t('ReportsModule', 'You have tried to access a report you do not have access to');
                }
                else
                {
                    $report = SavedReportToReportAdapter::makeReportBySavedReport($savedReport);
                    if ($report->getChart()->type == null)
                    {
                        $this->warningMessage = Zurmo::t('ReportsModule', 'This report does not have a chart to display');
                    }
                    else
                    {
                        $this->savedReport = $savedReport;
                    }
                }
            }
            catch (NotFoundException $e)
            {
                $this->warningMessage = Zurmo::t('ReportsModule', 'You have tried to access a report that is no longer available');
            }
            catch (AccessDeniedSecurityException $e)
            {
                $this->warningMessage = Zurmo::t('ReportsModule', 'You have tried to access a report you do not have access to');
            }
            $this->savedReportHasBeenResolved = true;
        }
    }
?>