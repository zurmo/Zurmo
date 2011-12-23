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
     * A view that displays a list of jobs available across the system including information
     * on last run, status, and actions that can be performed on a job.
     *
     */
    class JobsCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $monitorJobData;

        protected $jobsData = array();

        public function __construct($controllerId, $moduleId, $monitorJobData, $jobsData, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($monitorJobData)');
            assert('is_array($jobsData) && count($jobsData) > 0');
            assert('$messageBoxContent == null || is_string($messageBoxContent)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->monitorJobData         = $monitorJobData;
            $this->jobsData               = $jobsData;
            $this->messageBoxContent      = $messageBoxContent;
        }

        protected function renderContent()
        {
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array('id' => 'jobs-collection-form')
                                                            );
            $content .= $formStart;

            if ($this->messageBoxContent != null)
            {
                $content .= $this->messageBoxContent;
                $content .= '<br/>';
            }
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderViewToolBar();
            $content .= $clipWidget->renderEndWidget();
            $content .= '</div>';
            return $content;
        }

            /**
         * Render a form layout.
         * @param $form If the layout is editable, then pass a $form otherwise it can
         * be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout(ZurmoActiveForm $form)
        {
            $content  = '<div class="horizontal-line"></div>' . "\n";
            $content .= $this->renderMonitorJobLayout();
            $content .= '<br/>';
            $content .= '<h3>' . Yii::t('Default', 'Available Jobs') . '</h3>';
            $content .= $this->renderJobLayout($this->jobsData, Yii::t('Default', 'Job Name'));
            $content .= '<br/>';
            $content .= $this->renderSuggestedFrequencyContent();
            $content .= '<br/>';
            $content .= $this->renderHelpContent();
            return $content;
        }

        protected function renderMonitorJobLayout()
        {
            return $this->renderJobLayout(array('Monitor' => $this->monitorJobData),
                                          self::renderMonitorJobHeaderContent());
        }

        protected function renderJobLayout($jobsData, $jobLabelHeaderContent)
        {
            assert('is_array($jobsData)');
            assert('is_string($jobLabelHeaderContent)');
            $content  = '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:40%" /><col style="width:20%" /><col style="width:30%" />';
            $content .= '<col style="width:10%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . $jobLabelHeaderContent . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Last Completed Run') . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Status') . '</th>';
            $content .= '<th>&#160;</th>';
            $content .= '</tr>';
            foreach($jobsData as $type => $jobData)
            {
                $content .= '<tr>';
                $content .= '<td>' . $this->renderViewJobLogLinkContent($type);
                $content .=          '&#160;' . CHtml::encode($jobData['label']) . '</td>';
                $content .= '<td>' . CHtml::encode($jobData['lastCompletedRunContent']) . '</td>';
                $content .= '<td>' . CHtml::encode($jobData['statusContent']) . '</td>';
                $content .= '<td>' . $this->resolveActionContentByStatus($type, $jobData['status']) . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                ),
            );
            return $metadata;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected static function renderMonitorJobHeaderContent()
        {
            $title       = Yii::t('Default', 'The Monitor Job runs constantly making sure all jobs are running properly.');
            $content     = '<span id="active-monitor-job-tooltip" class="tooltip" title="' . $title . '">';
            $content    .= Yii::t('Default', 'What is the Monitor Job?') . '</span>';
            Yii::import('application.extensions.qtip.QTip');
            $qtip = new QTip();
            $qtip->addQTip("#active-monitor-job-tooltip");
            return $content;
        }

        protected function resolveActionContentByStatus($type, $status)
        {
            assert('is_string($type) && $type != ""');
            assert('is_int($status)');
            if($status == JobsToJobsCollectionViewUtil::STATUS_IN_PROCESS_STUCK)
            {
                $params = array('type' => $type);
                $route   = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/resetJob/', $params);
                $content = CHtml::link(Yii::t('Default', 'Reset'), $route);
                return $content;
            }
            return null;
        }

        protected function renderViewJobLogLinkContent($type)
        {
            assert('is_string($type) && $type != ""');
            $route = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/jobLogsModalList/',
                                           array('type' => $type));
            $label = Yii::t('Default', 'Job Log');
            return CHtml::ajaxLink($label, $route,
                array(
                    'onclick' => '$("#modalContainer").dialog("open"); return false;',
                    'update' => '#modalContainer',
                )
            );
        }

        protected function renderSuggestedFrequencyContent()
        {
            $content  = '<h3>' . Yii::t('Default', 'How often should I run each Job?') . '</h3>';
            $content .= '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:40%" /><col style="width:60%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . Yii::t('Default', 'Job Name') . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Recommmended Frequency') . '</th>';
            $content .= '</tr>';

            $content .= '<tr>';
            $content .= '<td>' . CHtml::encode($this->monitorJobData['label']) . '</td>';
            $content .= '<td>' . CHtml::encode($this->monitorJobData['recommendedFrequencyContent']) . '</td>';
            $content .= '</tr>';

            foreach($this->jobsData as $type => $jobData)
            {
                $content .= '<tr>';
                $content .= '<td>' . CHtml::encode($jobData['label']) . '</td>';
                $content .= '<td>' . CHtml::encode($jobData['recommendedFrequencyContent']) . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        protected static function renderHelpContent()
        {
            $clickHereLink = CHtml::link(Yii::t('Default', 'Click Here'), 'http://www.zurmo.org/links/jobsManagerHelp.php');
            $content  = '<h3>' . Yii::t('Default', 'How to Setup the Jobs to Run Automatically') . '</h3>';
            $content .= Yii::t('Default', '{ClickHereLink} for help on setting up a cron in Linux or a scheduled task in Windows',
                               array('{ClickHereLink}' => $clickHereLink));
            $content .= '<br/><br/>';
            return $content;
        }
    }
?>