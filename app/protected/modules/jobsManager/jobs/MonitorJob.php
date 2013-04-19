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
     * A job for monitoring all other jobs and making sure they are functioning properly.
     */
    class MonitorJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('JobsManagerModule', 'Monitor Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'Monitor';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('JobsManagerModule', 'Every 5 minutes');
        }

        /**
         * @returns translated string to use when communicating that the monitor is stuck.
         */
        public static function getStuckStringContent()
        {
            return Zurmo::t('JobsManagerModule', 'The monitor job is stuck.');
        }

        public function run()
        {
            $jobsInProcess = static::getNonMonitorJobsInProcessModels();
            foreach ($jobsInProcess as $jobInProcess)
            {
                if (JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
                {
                    self::makeJobStuckNotification();
                }
            }
            $jobLogs = static::getNonMonitorJobLogsUnprocessed();
            foreach ($jobLogs as $jobLog)
            {
                if ($jobLog->status == JobLog::STATUS_COMPLETE_WITH_ERROR)
                {
                    $message                      = new NotificationMessage();
                    $message->htmlContent         = Zurmo::t('JobsManagerModule', 'Job completed with errors.');
                    $url                          = Yii::app()->createAbsoluteUrl('jobsManager/default/jobLogDetails/',
                                                                        array('id' => $jobLog->id));
                    $message->htmlContent        .= "<br/>" . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
                    $rules                        = new JobCompletedWithErrorsNotificationRules();
                    NotificationsUtil::submit($message, $rules);
                }
                $jobLog->isProcessed         = true;
                $jobLog->save();
            }
            return true;
        }

        protected static function getNonMonitorJobsInProcessModels()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => 'Monitor',
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('JobInProcess');
            $where = RedBeanModelDataProvider::makeWhere('JobInProcess', $searchAttributeData, $joinTablesAdapter);
            return JobInProcess::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        protected static function getNonMonitorJobLogsUnprocessed()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => 'Monitor',
                ),
                2 => array(
                    'attributeName'        => 'isProcessed',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('JobLog');
            $where = RedBeanModelDataProvider::makeWhere('JobLog', $searchAttributeData, $joinTablesAdapter);
            return JobLog::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        public static function makeJobStuckNotification()
        {
            $message                    = new NotificationMessage();
            $message->textContent       = Zurmo::t('JobsManagerModule', 'The system has detected there are jobs that are stuck.');
            $message->htmlContent       = Zurmo::t('JobsManagerModule', 'The system has detected there are jobs that are stuck.');
            $rules                      = new StuckJobsNotificationRules();
            NotificationsUtil::submit($message, $rules);
        }
    }
?>