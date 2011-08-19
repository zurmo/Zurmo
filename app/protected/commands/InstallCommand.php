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
     * MessageCommand extracts messages to be translated from source files.
     * The extracted messages are saved as PHP message source files
     * under the specified directory.
     */
    class InstallCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc install <database-hostname> <database-name> <database-username> <database-password> <superuser-password> [demodata]

    DESCRIPTION
      This command runs a silent install on the application, optional loading demo data if specified. This version
      assumes memcache is availble running at its standard host/post location.  Also assumes the system is ready
      for an installation as this install script does not check services like the install through the user interface.

    PARAMETERS
     * database-hostname  : The hostname, typically 'localhost'.
     * database-name      : The database name to installation the application on.
     * database-username  : The database user
     * database-password  : The database user password.
     * superuser-password : The password for the super administrator that is created.  The username will be 'super'

     Optional Parameters:
     * demodata: If you want demodata to load just specify 'demodata' otherwise leave blank.

EOD;
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2]) || !isset($args[3]) || !isset($args[4]))
        {
            $this->usageError('The database user, password, and host must be specified.');
        }
        if (isset($args[5]) && $args[5] != 'demodata')
        {
            $this->usageError('Invalid parameter specified.  If specified the 6th parameter should be \'demodata\'');
        }
        if (Yii::app()->isApplicationInstalled())
        {
            $this->usageError('The installation is marked as being already completed.  Cannot run the installer.');
        }
        echo "\n";
        $form            = new InstallSettingsForm();
        $template        = "{message}\n";
        $messageStreamer = new MessageStreamer($template);
        $messageStreamer->setExtraRenderBytes(0);
        $messageStreamer->add(Yii::t('Default', 'Connecting to Database.'));

        $form->databaseHostname  = $args[0];
        $form->databaseName      = $args[1];
        $form->databaseUsername  = $args[2];
        $form->databasePassword  = $args[3];
        $form->superUserPassword = $args[4];

        $messageStreamer = new MessageStreamer($template);
        $messageStreamer->setExtraRenderBytes(0);
        InstallUtil::runInstallation($form, $messageStreamer);
        if (isset($args[5]))
        {
            $messageStreamer->add(Yii::t('Default', 'Starting to load demo data.'));
            $messageLogger = new MessageLogger($messageStreamer);
            DemoDataUtil::load($messageLogger, 3);
            $messageStreamer->add(Yii::t('Default', 'Finished loading demo data.'));
        }
        $messageStreamer->add(Yii::t('Default', 'Locking Installation.'));
        InstallUtil::writeInstallComplete(INSTANCE_ROOT);
        $messageStreamer->add(Yii::t('Default', 'Installation Complete.'));
    }
}
?>