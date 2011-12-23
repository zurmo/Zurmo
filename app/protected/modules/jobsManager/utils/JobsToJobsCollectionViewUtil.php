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

    class JobsToJobsCollectionViewUtil
    {
        const STATUS_NOT_RUNNING         = 1;

        const STATUS_IN_PROCESS_STUCK    = 2;

        const STATUS_IN_PROCESS          = 3;

        public static function getNonMonitorJobsData()
        {
            $jobsData       = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $jobsClassNames = $module::getAllClassNamesByPathFolder('rules');
                foreach ($jobsClassNames as $jobClassName)
                {
                    $classToEvaluate     = new ReflectionClass($jobClassName);
                    if (is_subclass_of($jobClassName, 'BaseJob') && !$classToEvaluate->isAbstract() &&
                        $jobClassName != 'MonitorJob')
                    {

                        $jobsData[$jobClassName::getType()] =
                            array('label' => $jobClassName::getDisplayName(),
                                  'lastCompletedRunContent' => 'x',
                                  'statusContent'			=> 'y',
                                  'status'					=> 'z',
                         );
                    }
                }
            }
            return $jobsData;
        }

        public static function getMonitorJobData()
        {
            return self::getJobDataByType('Monitor');

        }

        protected static function getJobDataByType($type)
        {
            assert('is_string($type) && $type != ""');
            $lastCompletedJobLog                = self::getLastCompletedJobLogByType($type);
            $jobInProcess                       = self::getIfJobIsInProcessOtherwiseReturnNullByType($type);
            $jobData = array();
            $jobData['label']                   = MonitorJob::getDisplayName();
            $jobData['lastCompletedRunContent'] = self::makeLastCompletedRunContentByJobLog($lastCompletedJobLog);
            $jobData['statusContent']			= self::makeStatusContentByJobLog($lastCompletedJobLog, $jobInProcess);
            $jobData['status']					= self::resolveStatusByJobLog($lastCompletedJobLog, $jobInProcess);
            return $jobData;
        }

        protected static function getIfJobIsInProcessOtherwiseReturnNullByType($type)
        {
            assert('is_string($type) && $type != ""');
            try
            {
                $jobInProcess = JobInProcess::getByType($type);
            }
            catch(NotFoundException $e)
            {
                $jobInProcess = null;
            }
            return $jobInProcess;
        }

        protected static function makeLastCompletedRunContentByJobLog($jobLog)
        {
            assert('$jobLog instanceof JobLog || $jobLog == null');
            if($jobLog == null)
            {
                return Yii::t('Default', 'Never');
            }
            $content = DateTimeUtil::
                           convertDbFormattedDateTimeToLocaleFormattedDisplay($jobLog->createdDateTime);
            if($jobLog != null && $jobLog->status == JobLog::STATUS_COMPLETE_WITH_ERROR)
            {
                $content .= '&#160;' . Yii::t('Default', '[with errors]');
            }
            return $content;
        }

        protected static function makeStatusContentByJobLog($jobLog, $jobInProcess)
        {
            assert('$jobLog instanceof JobLog || $jobLog == null');
            assert('$jobInProcess instanceof JobInProcess || $jobInProcess == null');
            if($jobInProcess != null && JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
            {
                return Yii::t('Default', 'In Process (Stuck)');
            }
            elseif($jobInProcess != null)
            {
                $startedDateTimeContent = DateTimeUtil::
                                          convertDbFormattedDateTimeToLocaleFormattedDisplay($jobLog->createdDateTime);
                return Yii::t('Default', 'In Process [Started: {startedDateTime}]',
                       array('{startedDateTime}' => $startedDateTimeContent));
            }
            else
            {
                return Yii::t('Default', 'Not Running');
            }
        }

        protected static function resolveStatusByJobLog($jobLog, $jobInProcess)
        {
            assert('$jobLog instanceof JobLog || $jobLog == null');
            assert('$jobInProcess instanceof JobInProcess || $jobInProcess == null');
            if($jobInProcess != null && JobsManagerUtil::isJobInProcessOverThreashold($jobInProcess, $jobInProcess->type))
            {
                return self::STATUS_IN_PROCESS_STUCK;
            }
            elseif($jobInProcess != null)
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
            if(count($models) > 1)
            {
                throw new NotSupportedException();
            }
            if(count($models) == 0)
            {
                return null;
            }
            return $models[0];
        }
    }
?>