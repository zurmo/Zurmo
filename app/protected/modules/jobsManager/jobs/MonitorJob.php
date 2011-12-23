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
     * A job for monitoring all other jobs and making sure they are functioning properly.
     */
    class MonitorJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Yii::t('Default', 'Monitor Job');
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
            return Yii::t('Default', 'Every 5 minutes');
        }

        /**
         * @returns translated string to use when communicating that the monitor is stuck.
         */
        public static function getStuckStringContent()
        {
            return Yii::t('Default', 'The monitor job is stuck.');
        }

        public function run()
        {
            $jobsInProcess = static::getNonMonitorJobsInProcessModels();
            foreach($jobsInProcess as $jobInProcess)
            {
                if(JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
                {
                    $message                    = new NotificationMessage();
                    $message->textContent       = Yii::t('Default', 'The system has detected there are jobs that are stuck.');
                    $rules                      = new StuckJobsNotificationRules();
                    NotificationsUtil::submit($message, $rules);
                }
            }
            $jobLogs = static::getNonMonitorJobLogsWithErrorStatus();
            foreach($jobLogs as $jobLog)
            {
                $message                     = new NotificationMessage();
                $message->textContent        = Yii::t('Default', 'Job completed with errors.');
                $rules                       = new JobCompletedWithErrorsNotificationRules();
                NotificationsUtil::submit($message, $rules);
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

        protected static function getNonMonitorJobLogsWithErrorStatus()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => 'Monitor',
                ),
                2 => array(
                    'attributeName'        => 'status',
                    'operatorType'         => 'equals',
                    'value'                => JobLog::STATUS_COMPLETE_WITH_ERROR,
                ),
                3 => array(
                    'attributeName'        => 'isProcessed',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2 and 3';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('JobLog');
            $where = RedBeanModelDataProvider::makeWhere('JobLog', $searchAttributeData, $joinTablesAdapter);
            return JobLog::getSubset($joinTablesAdapter, null, null, $where, null);
        }
    }

?>