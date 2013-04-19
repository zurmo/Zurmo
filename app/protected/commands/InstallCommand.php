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
     * InstallCommand allows the installation to be run via the command line instead of the user interface.
     */
    class InstallCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc install <database-hostname> <database-name> <database-username> <database-password> <database-port> <superuser-password> [hostInfo] [scriptUrl] [demodata] [load-magnitude]

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
     * hostInfo           : Set hostInfo in perInstance.php file.
     * scriptUrl          : Set scriptUrl in perInstance.php file.

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
            $this->usageError('The database user, database name, password, host, port and super user password must be specified.');
        }
        if (!isset($args[6]) || !isset($args[7]))
        {
            $this->usageError('the hostInfo and scriptUrl parameters must be specified.');
        }
        if (isset($args[8]) && $args[8] != 'demodata')
        {
            $this->usageError('Invalid parameter specified.  If specified the 9th parameter should be \'demodata\'');
        }
        if (isset($args[9]) && (intval($args[9]) < 1))
        {
            $this->usageError('Invalid parameter specified.  If specified the 10th parameter should be integer and greater then 0');
        }
        if (Yii::app()->isApplicationInstalled())
        {
            $this->usageError('The installation is marked as being already completed.  Cannot run the installer.');
        }
        echo "\n";
        InstallUtil::runFromInstallCommand($args, true);
    }
}
?>