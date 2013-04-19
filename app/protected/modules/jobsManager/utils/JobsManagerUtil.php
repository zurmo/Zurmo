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
        public static function runFromJobManagerCommand($type, $timeLimit, $messageLoggerClassName)
        {
            assert('is_string($type)');
            assert('is_int($timeLimit)');
            assert('is_string($messageLoggerClassName) && (
                    is_subclass_of($messageLoggerClassName, "MessageLogger") ||
                    $messageLoggerClassName == "MessageLogger")');
            set_time_limit($timeLimit);
            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageStreamer->add(Zurmo::t('JobsManagerModule', 'Script will run at most for {seconds} seconds.',
                                  array('{seconds}' => $timeLimit)));
            echo "\n";
            $messageStreamer->add(Zurmo::t('JobsManagerModule', '{dateTimeString} Starting job type: {type}',
                                  array('{type}' => $type,
                                         '{dateTimeString}' => static::getLocalizedDateTimeTimeZoneString())));
            $messageLogger = new $messageLoggerClassName($messageStreamer);
            if ($type == 'Monitor')
            {
                static::runMonitorJob($messageLogger);
            }
            else
            {
                static::runNonMonitorJob($type, $messageLogger);
            }
            $messageStreamer->add(Zurmo::t('JobsManagerModule', '{dateTimeString} Ending job type: {type}',
                                  array('{type}' => $type,
                                         '{dateTimeString}' => static::getLocalizedDateTimeTimeZoneString())));
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
                if (static::isJobInProcessOverThreashold($jobInProcess, 'Monitor'))
                {
                    $messageLogger->addInfoMessage("Existing monitor job is stuck");
                    self::makeMonitorStuckJobNotification();
                }
            }
            catch (NotFoundException $e)
            {
                $jobInProcess          = new JobInProcess();
                $jobInProcess->type    = 'Monitor';
                $jobInProcess->save();
                $startDateTime         = $jobInProcess->createdDateTime;
                $job                   = new MonitorJob();
                $job->setMessageLogger($messageLogger);
                $ranSuccessfully       = $job->run();
                $jobInProcess->delete();
                $jobLog                = new JobLog();
                $jobLog->type          = 'Monitor';
                $jobLog->startDateTime = $startDateTime;
                $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if ($ranSuccessfully)
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

        public static function makeMonitorStuckJobNotification()
        {
            $message                    = new NotificationMessage();
            $message->textContent       = MonitorJob::getStuckStringContent();
            $message->htmlContent       = MonitorJob::getStuckStringContent();
            $rules                      = new StuckMonitorJobNotificationRules();
            NotificationsUtil::submit($message, $rules);
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
            catch (NotFoundException $e)
            {
                $jobInProcess            = new JobInProcess();
                $jobInProcess->type    = $type;
                $jobInProcess->save();
                $startDateTime         = $jobInProcess->createdDateTime;
                $jobClassName          = $type . 'Job';
                $job                   = new $jobClassName();
                $job->setMessageLogger($messageLogger);
                $ranSuccessfully       = $job->run();
                $errorMessage          = $job->getErrorMessage();
                $jobInProcess->delete();
                $jobLog                = new JobLog();
                $jobLog->type          = $type;
                $jobLog->startDateTime = $startDateTime;
                $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if ($ranSuccessfully)
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
                $s = $jobLog->save();
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
            if (($nowTimeStamp - $createdTimeStamp) > $thresholdSeconds)
            {
                return true;
            }
            return false;
        }

        protected static function getLocalizedDateTimeTimeZoneString()
        {
            $content = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                                        DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
            $content .= ' ' . Yii::app()->user->userModel->timeZone;
            return $content;
        }
    }
?>