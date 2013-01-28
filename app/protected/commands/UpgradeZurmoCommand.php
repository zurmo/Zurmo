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
     * UpdateZurmoCommand update Zurmo version.
     */
    class UpgradeZurmoCommand extends CConsoleCommand
    {
        protected $interactive = true;

        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc updgradeZurmo <username> <action> <doNotlAlterFiles> <interactive>

    DESCRIPTION
      This command runs a Zurmo upgrade.

    PARAMETERS
     * username: username to log in as and run the import processes. Typically 'super'.
                  This user must be a super administrator.
     * action: define upgrade phase(possible options: "runPart1" or "runPart2")
     * doNotlAlterFiles: Should files be altered or not. This should be set to 1 if you
                         already updated files using Mercurial
     * interactive: interactive or not
EOD;
        }

        /**
         * Execute the action.
         * @param array command line parameters specific for this command
         */
        public function run($args)
        {
            set_time_limit(0);
            if (!isset($args[0]))
            {
                $this->usageError('A username must be specified.');
            }
            try
            {
                Yii::app()->user->userModel = User::getByUsername($args[0]);
            }
            catch (NotFoundException $e)
            {
                $this->usageError('The specified username does not exist.');
            }
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (!$group->users->contains(Yii::app()->user->userModel))
            {
                $this->usageError('The specified user is not a super administrator.');
            }

            if (!isset($args[1]))
            {
                $this->usageError('You must specify an action.');
            }
            else
            {
                $upgradeStep = $args[1];
            }

            if (isset($args[2]))
            {
                $doNotlAlterFiles = $args[2];
            }
            else
            {
                $doNotlAlterFiles = 0;
            }

            if (isset($args[3]))
            {
                $this->interactive = $args[3];
            }

            try
            {
                $template        = "{message}\n";
                $messageStreamer = new MessageStreamer($template);
                $messageStreamer->setExtraRenderBytes(0);

                if ($upgradeStep == 'runPart1')
                {
                    $messageStreamer->add(Zurmo::t('Commands', 'Starting Zurmo upgrade process.'));
                    $this->runPart1($messageStreamer, $doNotlAlterFiles);
                    $messageStreamer->add(Zurmo::t('Commands', 'Zurmo upgrade phase 1 completed.'));
                    $messageStreamer->add(Zurmo::t('Commands', 'Please execute next command: "{command}" to complete upgrade process.',
                            array('{command}' => './zurmoc upgradeZurmo super runPart2')));
                }
                elseif ($upgradeStep == 'runPart2')
                {
                    if (UpgradeUtil::isUpgradeStateValid())
                    {
                        $messageStreamer->add(Zurmo::t('Commands', 'Starting Zurmo upgrade process - phase 2.'));
                        $this->runPart2($messageStreamer);
                        $messageStreamer->add(Zurmo::t('Commands', 'Zurmo upgrade completed.'));
                    }
                    else
                    {
                        $message = 'Upgrade state is older then one day, please run phase one of the upgrade process again.';
                        throw new NotSupportedException($message);
                    }
                }
                else
                {
                    $this->usageError('Invalid step/action. Valid values are "runPart1" and "runPart2".');
                }
            }
            catch (Exception $e)
            {
                $messageStreamer->add(Zurmo::t('Commands', 'An error occur during upgrade: {message}',
                                               array('{message}' => $e->getMessage())));
                UpgradeUtil::unsetUpgradeState();
            }
        }

        protected function runPart1($messageStreamer, $doNotlAlterFiles = false)
        {
            set_time_limit(3600);
            $messageStreamer->add(Zurmo::t('Commands', 'This is the Zurmo upgrade process. Please backup files/database before you continue.'));

            $message = Zurmo::t('Commands', 'Are you sure you want to upgrade Zurmo? [yes|no]');

            if ($this->interactive)
            {
                $confirm = $this->confirm($messageStreamer, $message);
            }
            else
            {
                $confirm = true;
            }

            if ($confirm)
            {
                UpgradeUtil::runPart1($messageStreamer, $doNotlAlterFiles);
            }
            else
            {
                $messageStreamer->add(Zurmo::t('Commands', 'Upgrade process halted.'));
            }
        }

        protected function runPart2($messageStreamer)
        {
            // Upgrade process can take much time, because upgrade schema script.
            // Set timeout for upgrade to 12 hours.
            set_time_limit(12 * 60 * 60);
            UpgradeUtil::runPart2($messageStreamer);
        }

        /**
         * Prompt user by Yes or No
         * @param string $message an optional message to show at prompting.
         * @param bool $printYesNo If is true shows " [yes|no] " at prompting
         * @return bool True if user respond Yes, otherwise, return False
         */
        public function confirm($messageStreamer, $message = null)
        {
            if ($message !== null)
            {
                $messageStreamer->add($message);
            }
            return !strncasecmp(trim(fgets(STDIN)), 'y', 1);
        }
    }
?>