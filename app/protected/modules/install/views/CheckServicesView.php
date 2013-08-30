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
     * Base view for displaying check service results. Used by the installation and the diagnostics run after
     *  a system is installed
     */
    abstract class CheckServicesView extends View
    {
        protected $controllerId;

        protected $moduleId;

        protected $checkResultsDisplayData;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param array $checkResultsDisplayData
         */
        public function __construct($controllerId, $moduleId, $checkResultsDisplayData)
        {
            assert('is_string($controllerId) && $controllerId != ""');
            assert('is_string($moduleId) && $moduleId != ""');
            assert('is_array($checkResultsDisplayData)');
            $this->controllerId = $controllerId;
            $this->moduleId     = $moduleId;
            $this->checkResultsDisplayData = $checkResultsDisplayData;
        }

        protected function renderContent()
        {
            $failedIndexId   = CheckServicesUtil::CHECK_FAILED;
            $passedIndexId   = CheckServicesUtil::CHECK_PASSED;
            $warningIndexId  = CheckServicesUtil::CHECK_WARNING;
            $requiredIndexId = ServiceHelper::REQUIRED_SERVICE;
            $optionalIndexId = ServiceHelper::OPTIONAL_SERVICE;
            $content  = '<div class="MetadataView">';
            $content .= '<table>';
            $content .= '<tr><td>';
            $content .= $this->renderIntroductionContent();
            $content .= '<br/><br/>';
            if (count($this->checkResultsDisplayData[$failedIndexId]) > 0)
            {
                if (count($this->checkResultsDisplayData[$failedIndexId][$requiredIndexId]) > 0)
                {
                    $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                            Zurmo::t('InstallModule', 'Failed Required Services'),
                                            $this->checkResultsDisplayData[$failedIndexId][$requiredIndexId],
                                            '<span class="fail">' . Zurmo::t('InstallModule', 'FAIL') . '</span>');
                    $content .= '<br/><br/>';
                }
                if (count($this->checkResultsDisplayData[$failedIndexId][$optionalIndexId]) > 0)
                {
                    $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                            Zurmo::t('InstallModule', 'Failed Optional Services'),
                                            $this->checkResultsDisplayData[$failedIndexId][$optionalIndexId],
                                            '<span class="fail">' . Zurmo::t('InstallModule', 'FAIL') . '</span>');
                    $content .= '<br/>';
                }
            }
            if (count($this->checkResultsDisplayData[$warningIndexId]) > 0)
            {
                $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                        Zurmo::t('InstallModule', 'Service Status Partially Known'),
                                        $this->checkResultsDisplayData[$warningIndexId],
                                        '<span class="warning">' . Zurmo::t('InstallModule', 'WARNING') . '</span>');
                $content .= '<br/>';
            }

            if (count($this->checkResultsDisplayData[$passedIndexId]) > 0)
            {
                $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                        Zurmo::t('InstallModule', 'Correctly Installed Services'),
                                        $this->checkResultsDisplayData[$passedIndexId],
                                        '<span class="pass">' . Zurmo::t('InstallModule', 'PASS') . '</span>');
            }
            $content .= $this->renderActionBarContent();
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }

        /**
         * @param string $groupLabel
         * @param string $groupData
         * @param string $checkResultLabel
         * @return string
         */
        protected function renderServiceGroupDisplayByServiceDataAndCheckResult($groupLabel, $groupData,
                                                                                $checkResultLabel)
        {
            assert('is_string($groupLabel) && $groupLabel != ""');
            assert('is_array($groupData)');
            assert('is_string($checkResultLabel) && $checkResultLabel != ""');
            $content  = '<table>';
            $content .= '<colgroup><col/><col style="width:100px;" /></colgroup>';
            $content .= '<tr><td>' . $groupLabel . '</td><td></td></tr>';
            foreach ($groupData as $serviceDisplayData)
            {
                $content .= '<tr><td>' . Yii::app()->format->formatNtext($serviceDisplayData['message']) . '</td>';
                $content .= '<td>' . $checkResultLabel . '</td></tr>';
            }
            $content .= '</table>';
            return $content;
        }

        protected function renderIntroductionContent()
        {
        }

        protected function renderActionBarContent()
        {
        }
    }
?>
