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
     * A helper class for running normal jobs or the monitor job.
     */
    class JobsManagerUtil
    {
        /**
         * @see JobManagerCommand.  This method is called from the JobManagerCommand which is a commandline
         * tool to run jobs.  Based on the 'type' specified this method will call to run the monitor or a
         * regular non-monitor job.
         * @param string $type
         * @param timeLimit $timeLimit
         */
        public static function runFromJobManagerCommand($type, $timeLimit)
        {
            assert('is_string($type)');
            assert('is_int($timeLimit)');
            set_time_limit($timeLimit);
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageStreamer->add(Yii::t('Default', 'Script will run at most for {seconds} seconds.',
                                  array('{seconds}' => $timeLimit)));
            echo "\n";
            $messageStreamer->add(Yii::t('Default', 'Starting job type: {type}', array('{type}' => $type)));
            $messageLogger = new MessageLogger($messageStreamer);
            if($type == 'Monitor')
            {
                static::runMonitorJob($messageLogger);
            }
            else
            {
                static::runNonMonitorJob($type, $messageLogger);
            }
            $messageStreamer->add(Yii::t('Default', 'Ending job type: {type}', array('{type}' => $type)));
        }

        /**
         * Run the monitor job.
         */
        public static function runMonitorJob(MessageLogger $messageLogger)
        {
            try
            {
                $jobInProcess = JobInProcess::getByType('Monitor');
                $messageLogger->addInfoMessage("Existing monitor job detected");
                if(static::isJobInProcessOverThreashold($jobInProcess, 'Monitor'))
                {
                    $messageLogger->addInfoMessage("Existing monitor job is stuck");
                    $message                    = new NotificationMessage();
                    $message->textContent       = MonitorJob::getStuckStringContent();
                    $rules                      = new StuckMonitorJobNotificationRules();
                    NotificationsUtil::submit($message, $rules);
                }
            }
            catch(NotFoundException $e)
            {
                $jobInProcess          = new JobInProcess();
                $jobInProcess->type    = 'Monitor';
                $jobInProcess->save();
                $startDateTime         = $jobInProcess->createdDateTime;
                $job                   = new MonitorJob();
                $ranSuccessfully       = $job->run();
                $jobInProcess->delete();
                $jobLog                = new JobLog();
                $jobLog->type          = 'Monitor';
                $jobLog->startDateTime = $startDateTime;
                $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if($ranSuccessfully)
                {
                    $messageLogger->addInfoMessage("Monitor Job completed successfully");
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
                }
                else
                {
                    $messageLogger->addInfoMessage("Monitor Job completed with errors");
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITH_ERROR;
                }
                $jobLog->isProcessed = false;
                $jobLog->save();
            }
        }

        /**
         * Given a 'type' of job, run the job.  This is for non-monitor jobs only.
         * @param string $type
         */
        public static function runNonMonitorJob($type, MessageLogger $messageLogger)
        {
            assert('is_string($type) && $type != "Monitor"');
            try
            {
                $jobInProcess = JobInProcess::getByType($type);
                $messageLogger->addInfoMessage("Existing job detected");
            }
            catch(NotFoundException $e)
            {
                $jobInProcess            = new JobInProcess();
                $jobInProcess->type    = $type;
                $jobInProcess->save();
                $startDateTime         = $jobInProcess->createdDateTime;
                $jobClassName          = $type . 'Job';
                $job                   = new $jobClassName();
                $ranSuccessfully       = $job->run();
                $errorMessage          = $job->getErrorMessage();
                $jobInProcess->delete();
                $jobLog                = new JobLog();
                $jobLog->type          = $type;
                $jobLog->startDateTime = $startDateTime;
                $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if($ranSuccessfully)
                {
                    $messageLogger->addInfoMessage("Job completed successfully");
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
                }
                else
                {
                    $messageLogger->addInfoMessage("Job completed with errors");
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITH_ERROR;
                    $jobLog->message       = $errorMessage;
                }
                $jobLog->isProcessed = false;
                $jobLog->save();
            }
        }

        /**
         * Given a model of a jobInProcess and the 'type' of job, determine if the job has been running too
         * long.  Jobs have defined maximum run times that they are allowed to be in process.
         * @param JobInProcess $jobInProcess
         * @param string $type
         * @return true/false - true if the job is over the allowed amount of time to run for.
         */
        public static function isJobInProcessOverThreashold(JobInProcess $jobInProcess, $type)
        {
            assert('is_string($type) && $type != ""');

            $createdTimeStamp  = DateTimeUtil::convertDbFormatDateTimeToTimestamp($jobInProcess->createdDateTime);
            $nowTimeStamp      = time();
            $jobClassName      = $type . 'Job';
            $thresholdSeconds  = $jobClassName::getRunTimeThresholdInSeconds();
            if(($nowTimeStamp - $createdTimeStamp) > $thresholdSeconds)
            {
                return true;
            }
            return false;
        }
    }
?>