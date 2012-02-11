<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * A helper class for rendering view information from a JobLog model.
     */
    class JobLogViewUtil
    {
        public static function renderStatusAndMessageListContent(JobLog $jobLog)
        {
            if ($jobLog->status == JobLog::STATUS_COMPLETE_WITH_ERROR)
            {
                $content     = '<span id="active-nonmonitor-job-tooltip-' .
                               $jobLog->id . '" class="tooltip" title="' . CHtml::encode($jobLog->message) . '">';
                $content    .= Yii::t('Default', 'Completed with Errors') . '</span>';
                Yii::import('application.extensions.qtip.QTip');
                $options     = array('content' =>
                                        array('title' =>
                                            array('text'   => Yii::t('Default', 'Error Log'),
                                                  'button' => Yii::t('Default', 'Close'))
                                        ),
                                     'adjust' =>
                                        array('screen' => true),
                                     'position' =>
                                        array('corner' =>
                                            array('target' => 'bottomRight',
                                                  'tooltip' => 'topRight')),
                                     'style'  => array('width' => array('max' => 600)),
                                     // Begin Not Coding Standard
                                     'api' => array('beforeHide' => 'js:function (event, api) {
                                                                     if (event.originalEvent.type !== "click")
                                                                        return false;}')
                                    // End Not Coding Standard
                               );
                $qtip        = new QTip();
                $qtip->addQTip("#active-nonmonitor-job-tooltip-" . $jobLog->id, $options);
                return $content;
            }
            elseif ($jobLog->status == JobLog::STATUS_COMPLETE_WITHOUT_ERROR)
            {
                return Yii::t('Default', 'Completed');
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>