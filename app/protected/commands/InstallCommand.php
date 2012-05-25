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
     * InstallCommand allows the installation to be run via the command line instead of the user interface.
     */
    class InstallCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc install <database-hostname> <database-name> <database-username> <database-password> <database-port> <superuser-password> [demodata] [load-magnitude]

    DESCRIPTION
      This command runs a silent install on the application, optional loading demo data if specified. This version
      assumes memcache is availble running at its standard host/post location.  Also assumes the system is ready
      for an installation as this install script does not check services like the install through the user interface.

    PARAMETERS
     * database-hostname  : The hostname, typically 'localhost'.
     * database-name      : The database name to installation the application on.
     * database-username  : The database user
     * database-password  : The database user password.
     * database-port      : The database port.
     * superuser-password : The password for the super administrator that is created.  The username will be 'super'

     Optional Parameters:
     * demodata: If you want demodata to load just specify 'demodata' otherwise leave blank.
     * load-magnitude: Conditional, used only if demodata parameter specified, and it specify load magnitude for demodata (demodata volume).

EOD;
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        set_time_limit('7200');
        if (!isset($args[0]) || !isset($args[1]) || !isset($args[2]) || !isset($args[3]) || !isset($args[4]) || !isset($args[5]))
        {
            $this->usageError('The database user, database name, password, host and port must be specified.');
        }
        if (isset($args[6]) && $args[6] != 'demodata')
        {
            $this->usageError('Invalid parameter specified.  If specified the 7th parameter should be \'demodata\'');
        }
        if (isset($args[7]) && (intval($args[7]) < 1))
        {
            $this->usageError('Invalid parameter specified.  If specified the 8th parameter should be integer and greater then 0');
        }
        if (Yii::app()->isApplicationInstalled())
        {
            $this->usageError('The installation is marked as being already completed.  Cannot run the installer.');
        }
        echo "\n";
        InstallUtil::runFromInstallCommand($args);
    }
}
?>