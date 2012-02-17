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
     * JobManager command is used to run either the Monitor job or a normal job via a cron or Windows scheduled tasks.
     */
    class JobManagerCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc jobManager <username> <jobType> [runTimeInSeconds] [messageLoggerClassName]

    DESCRIPTION
      This command runs a specific job as specified by the jobType parameter. If you want to run the monitor job
      specify the jobType as 'Monitor'.

    PARAMETERS
     * username: username to log in as and run the job. Typically 'super'. Must be a super adminstrator.
     * jobType:  Type of job to run.

     Optional Parameters:
     * runTimeInSeconds: how many seconds to let this script run, if not specified will default to 5 minutes.
     * messageLoggerClassName: which messageLogger class to use. Defaults to MessageLogger
EOD;
    }

    /**
     * Execute the action.  Changes max run time to 5 minutes, pass the optional parameter
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        if (!isset($args[0]))
        {
            $this->usageError('A username must be specified.');
        }

        if (!isset($args[1]))
        {
            $this->usageError('A job type must be specified.');
        }
        try
        {
            Yii::app()->user->userModel = User::getByUsername($args[0]);
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$group->users->contains(Yii::app()->user->userModel))
            {
                $this->usageError('The username specified must be for a super administrator.');
            }
        }
        catch (NotFoundException $e)
        {
            $this->usageError('The specified username does not exist.');
        }
        if (!is_string($args[1]))
        {
            $this->usageError('The specified job type to run is invalid.');
        }
        else
        {
            $jobClassName = $args[1] . 'Job';
            if (!@class_exists($jobClassName))
            {
                $this->usageError('The specified job type to run does not exist.');
            }
        }
        if (isset($args[2]))
        {
            $timeLimit = (int)$args[2];
        }
        else
        {
            $timeLimit = 300;
        }
        if (isset($args[3]))
        {
            $messageLoggerClassName = $args[3];
        }
        else
        {
            $messageLoggerClassName = 'MessageLogger';
        }
        echo "\n";
        JobsManagerUtil::runFromJobManagerCommand($args[1], $timeLimit, $messageLoggerClassName);
    }
}
?>