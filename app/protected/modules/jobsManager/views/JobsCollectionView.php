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

        protected $jobsData;

        public function __construct($controllerId, $moduleId, $monitorJobData, $jobsData, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($monitorJobData)');
            assert('is_array($jobsData)');
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
            $content = $this->renderMonitorJobLayout();
            $content .= '<br/><br/>';
            $content .= 'need some sort of header that says regular jobs';
            //$content .= $this->renderNonMonitorJobsLayout();
            return $content;
            $content  = '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:30%" /><col style="width:70%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . $this->renderActiveHeaderContent() . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Language') . '</th>';
            $content .= '</tr>';

            //todo: detect if jobsData has 0 count and show a different message like no jobs detected

            foreach ($this->jobsData as $type => $jobData)
            {
                assert('is_string($jobData["label"])');
                assert('is_string($jobData["lastCompletedRunContent"])');
                assert('is_string($jobData["statusContent"])');
                assert('is_string($jobData["status"])');
                $route = $this->moduleId . '/' . $this->controllerId . '/delete/';
                $content .= '<tr>';
                $content .= '<td>x</td>';
                $content .= '<td>' . $this->jobData['label'] . '</td>';
                //todo: based on this: $jobData["status"], we should resolve to display action.
                //todo: also need to display modal link for logs.
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public function renderMonitorJobLayout()
        {
            $content  = '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:50%" /><col style="width:20%" /><col style="width:20%" />';
            $content .= '<col style="width:10%" />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>&#160;</th>';
            $content .= '<th>' . Yii::t('Default', 'Last Completed Run') . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Status') . '</th>';
            $content .= '<th>&#160;</th>';
            $content .= '</tr>';

            $content .= '<tr>';
            $content .= '<td>[chn-log] ' . CHtml::encode($this->monitorJobData['label']) . '</td>';
            $content .= '<td>' . CHtml::encode($this->monitorJobData['lastCompletedRunContent']) . '</td>';
            $content .= '<td>' . CHtml::encode($this->monitorJobData['statusContent']) . '</td>';
            $content .= '<td>' . self::resolveActionContentByStatus($this->monitorJobData['status']) . '</td>';
            $content .= '</tr>';

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

        protected static function renderActiveHeaderContent()
        {
            $title       = Yii::t('Default', 'Active languages can be used by users. The system language and any language in use by a user cannot be inactivated');
            $content     = Yii::t('Default', 'Active') . '&#160;';
            $content    .= '<span id="active-languages-tooltip" class="tooltip" title="' . $title . '">';
            $content    .= Yii::t('Default', 'What is this?') . '</span>';
            $qtip = new QTip();
            $qtip->addQTip("#active-languages-tooltip");
            return $content;
        }

        protected static function resolveActionContentByStatus($status)
        {
            assert('is_int($status)');
            if($status == JobsToJobsCollectionViewUtil::STATUS_IN_PROCESS_STUCK)
            {
                return 'todoclear';
            }
            return null;
        }
    }
?>