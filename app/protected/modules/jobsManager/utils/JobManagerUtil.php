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
    class JobManagerUtil
    {
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
            if($type == 'Monitor')
            {
                static::runMonitorJob();
            }
            else
            {
                static::runNonMonitorJob($type);
            }
            $messageStreamer->add(Yii::t('Default', 'Ending job type: {type}', array('{type}' => $type)));
        }

        public static function runMonitorJob()
        {
            try
            {
                $jobInProcess = JobInProcess::getByType('Monitor');
                if(static::isJobInProcessOverThreashold($runningJob, 'Monitor'))
                {
                    $message                    = new NotificationMessage();
                    $message->textContent       = MonitorJob::getStuckStringContent();
                    $rules                      = new StuckMonitorJobNotificationRules();
                    NotificationsUtil::submit($message, $rules);
                }
            }
            catch(NotFoundException $e)
            {
                $jobInProcess            = new JobInProcess();
                $jobInProcess->type    = 'Monitor';
                $jobInProcess->save();
                $startDateTime         = $jobInProcess->createdDateTime;
                $job                   = new MonitorJob();
                $job                   = new $jobClassName();
                $ranSuccessfully       = $job->run();
                $jobInProcess->delete();
                $jobLog                = new JobLog();
                $jobLog->type          = 'Monitor';
                $jobLog->startDateTime = $startDateTime;
                $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                if($ranSuccessfully)
                {
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
                }
                else
                {
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITH_ERROR;
                }
                $jobLog->isProcessed = false;
                $jobLog->save();
            }
        }

        public static function runNonMonitorJob($type)
        {
            assert('is_string($type) && $type != "Monitor"');
            try
            {
                $jobInProcess = JobInProcess::getByType($type);
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
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
                }
                else
                {
                    $jobLog->status        = JobLog::STATUS_COMPLETE_WITH_ERROR;
                    $jobLog->message       = $errorMessage;
                }
                $jobLog->isProcessed = false;
                $jobLog->save();
            }
        }

        public static function isJobInProcessOverThreashold($jobInProcess, $type)
        {
            $createdTimeStamp  = DateTimeUtil::convertDbFormatDateTimeToTimestamp($jobInProcess->createdDateTime);
            $nowTimeStamp      = time();
            $jobClassName      = $type . 'Job';
            $thresholdSeconds  = $jobClassName::getRunTimeThresholdInSeconds();
            if($nowTimeStamp - $createdTimeStamp > $thresholdSeconds)
            {
                return true;
            }
            return false;
        }
    }
?>