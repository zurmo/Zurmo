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
     * View used during the installation to show in the user interface what services are correctly or incorrectly
     * installed or missing. This is additional view that will eventually appear after user enter settings, and only
     * if there are some failures(for example if db not exist, or if db not in strict mode).
     * This is the forth step during the installation process.
     */
    class InstallAdditionalCheckServicesView extends View
    {
        private $controlerId;

        private $moduleId;

        private $checkResultsDisplayData;

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
            $content .= Yii::t('Default', 'Below you will find the results of the system check. If any required ' .
                                          'services are not setup correctly, you will need to make sure they are ' .
                                          'installed correctly before you can continue.');
            $content .= '<br/><br/>';
            $content .= Yii::t('Default', 'It is highly recommended that all optional services are installed and ' .
                                          'working before continuing.');
            $content .= '<br/><br/>';
            if (count($this->checkResultsDisplayData[$failedIndexId]) > 0)
            {
                if (count($this->checkResultsDisplayData[$failedIndexId][$requiredIndexId]) > 0)
                {
                    $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                            Yii::t('Default', 'Failed Required Services'),
                                            $this->checkResultsDisplayData[$failedIndexId][$requiredIndexId],
                                            Yii::t('Default', 'FAIL'));
                    $content .= '<br/><br/>';
                }
                if (count($this->checkResultsDisplayData[$failedIndexId][$optionalIndexId]) > 0)
                {
                    $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                            Yii::t('Default', 'Failed Optional Services'),
                                            $this->checkResultsDisplayData[$failedIndexId][$optionalIndexId],
                                            Yii::t('Default', 'FAIL'));
                    $content .= '<br/>';
                }
            }
            if (count($this->checkResultsDisplayData[$warningIndexId]) > 0)
            {
                $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                        Yii::t('Default', 'Service Status Partially Known'),
                                        $this->checkResultsDisplayData[$warningIndexId],
                                        Yii::t('Default', 'WARNING'));
                $content .= '<br/>';
            }

            if (count($this->checkResultsDisplayData[$passedIndexId]) > 0)
            {
                $content .= $this->renderServiceGroupDisplayByServiceDataAndCheckResult(
                                        Yii::t('Default', 'Correctly Installed Services'),
                                        $this->checkResultsDisplayData[$passedIndexId],
                                        Yii::t('Default', 'PASS'));
            }
            $content .= '<br/><br/>';
            $content .= CHtml::link(Yii::t('Default', 'Recheck System'), '#', array('onclick' => 'window.location.reload()'));
            $content .= '</td></tr></table>';
            $content .= '</div>';
            return $content;
        }

        protected function renderServiceGroupDisplayByServiceDataAndCheckResult($groupLabel, $groupData,
                                                                                $checkResultLabel)
        {
            assert('is_string($groupLabel) && $groupLabel != ""');
            assert('is_array($groupData)');
            assert('is_string($checkResultLabel) && $checkResultLabel != ""');
            $content  = '<table>' . "\n";
            $content .= '<colgroup><col/><col style="width:100px;" /></colgroup>' . "\n";
            $content .= '<tr><td>' . $groupLabel . '</td><td></td></tr>' . "\n";
            foreach ($groupData as $serviceDisplayData)
            {
                $content .= '<tr><td>' . Yii::app()->format->formatNtext($serviceDisplayData['message']) . '</td>' . "\n";
                $content .= '<td>' . $checkResultLabel . '</td></tr>' . "\n";
            }
            $content .= '</table>' . "\n";
            return $content;
        }
    }
?>
