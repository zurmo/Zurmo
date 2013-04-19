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
     * A utility for getting information about jobs and putting into an array of data that is useful
     * for the JobsCollectionView.
     */
    class JobsToJobsCollectionViewUtil
    {
        /**
         * Indicates a job is not currently In Process
         */
        const STATUS_NOT_RUNNING         = 1;

        /**
         * Indicates a job is currently In Process and stuck based on it lasting longer than the
         * threshold.
         */
        const STATUS_IN_PROCESS_STUCK    = 2;

        /**
         * Indicates a job is currently In Process
         */
        const STATUS_IN_PROCESS          = 3;

        /**
         * @return array of data for the Monitor job.  Includes information such as the display label,
         * whether it is running or not, and the last completion time.
         */
        public static function getMonitorJobData()
        {
            return self::getJobDataByType('Monitor');
        }

        /**
         * @return array of data for jobs that are not the monitor job.  Includes information such as the display label,
         * whether it is running or not, and the last completion time.
         */
        public static function getNonMonitorJobsData()
        {
            $jobsData       = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $jobsClassNames = $module::getAllClassNamesByPathFolder('jobs');
                foreach ($jobsClassNames as $jobClassName)
                {
                    $classToEvaluate     = new ReflectionClass($jobClassName);
                    if (is_subclass_of($jobClassName, 'BaseJob') && !$classToEvaluate->isAbstract() &&
                        $jobClassName != 'MonitorJob')
                    {
                        $jobsData[$jobClassName::getType()] = self::getJobDataByType($jobClassName::getType());
                    }
                }
            }
            return $jobsData;
        }

        protected static function getJobDataByType($type)
        {
            assert('is_string($type) && $type != ""');
            $jobClassName                              = $type . 'Job';
            $lastCompletedJobLog                       = self::getLastCompletedJobLogByType($type);
            $jobInProcess                              = self::getIfJobIsInProcessOtherwiseReturnNullByType($type);
            $jobData = array();
            $jobData['label']                          = $jobClassName::getDisplayName();
            $jobData['lastCompletedRunEncodedContent'] = self::makeLastCompletedRunEncodedContentByJobLog($lastCompletedJobLog);
            $jobData['statusContent']                  = self::makeStatusContentByJobInProcess($jobInProcess);
            $jobData['status']                         = self::resolveStatusByJobInProcess($jobInProcess);
            $jobData['recommendedFrequencyContent']    = $jobClassName::getRecommendedRunFrequencyContent();
            return $jobData;
        }

        protected static function getIfJobIsInProcessOtherwiseReturnNullByType($type)
        {
            assert('is_string($type) && $type != ""');
            try
            {
                $jobInProcess = JobInProcess::getByType($type);
            }
            catch (NotFoundException $e)
            {
                $jobInProcess = null;
            }
            return $jobInProcess;
        }

        protected static function makeLastCompletedRunEncodedContentByJobLog($jobLog)
        {
            assert('$jobLog instanceof JobLog || $jobLog == null');
            if ($jobLog == null)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('JobsManagerModule', 'Never'), 'jobHasNeverRun');
            }
            if ($jobLog != null && $jobLog->status == JobLog::STATUS_COMPLETE_WITH_ERROR)
            {
                $content  = DateTimeUtil::
                           convertDbFormattedDateTimeToLocaleFormattedDisplay($jobLog->createdDateTime);
                $content .= ' ' . Zurmo::t('JobsManagerModule', '[with errors]');
                $content  = ZurmoHtml::wrapLabel($content, 'jobHasErrors');
            }
            else
            {
                $content = DateTimeUtil::
                           convertDbFormattedDateTimeToLocaleFormattedDisplay($jobLog->createdDateTime);
                $content  = ZurmoHtml::wrapLabel($content, 'jobRanSuccessfully');
            }
            return $content;
        }

        protected static function makeStatusContentByJobInProcess($jobInProcess)
        {
            assert('$jobInProcess instanceof JobInProcess || $jobInProcess == null');
            if ($jobInProcess != null && JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
            {
                return Zurmo::t('JobsManagerModule', 'In Process (Stuck)');
            }
            elseif ($jobInProcess != null)
            {
                $startedDateTimeContent = DateTimeUtil::
                                          convertDbFormattedDateTimeToLocaleFormattedDisplay($jobInProcess->createdDateTime);
                return Zurmo::t('JobsManagerModule', 'In Process [Started: {startedDateTime}]',
                       array('{startedDateTime}' => $startedDateTimeContent));
            }
            else
            {
                return Zurmo::t('JobsManagerModule', 'Not Running');
            }
        }

        protected static function resolveStatusByJobInProcess($jobInProcess)
        {
            assert('$jobInProcess instanceof JobInProcess || $jobInProcess == null');
            if ($jobInProcess != null && JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
            {
                return self::STATUS_IN_PROCESS_STUCK;
            }
            elseif ($jobInProcess != null)
            {
                return self::STATUS_IN_PROCESS;
            }
            else
            {
                return self::STATUS_NOT_RUNNING;
            }
        }

        protected static function getLastCompletedJobLogByType($type)
        {
            assert('is_string($type) && $type != ""');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('JobLog');
            $sort   = RedBeanModelDataProvider::
                      resolveSortAttributeColumnName('JobLog', $joinTablesAdapter, 'createdDateTime');
            $where  = RedBeanModelDataProvider::makeWhere('JobLog', $searchAttributeData, $joinTablesAdapter);
            $models = JobLog::getSubset($joinTablesAdapter, null, 1, $where, $sort . ' desc');
            if (count($models) > 1)
            {
                throw new NotSupportedException();
            }
            if (count($models) == 0)
            {
                return null;
            }
            return $models[0];
        }
    }
?>