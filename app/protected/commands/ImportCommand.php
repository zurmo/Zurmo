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
     * ImportCommand is used to run any manual or schedule import processes that are specific to your application.
     */
    class ImportCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc import <username> [importName] [messageInterval] [runTimeInSeconds]

    DESCRIPTION
      This command runs any import processes, specifically doing only the specified process if supplied. In a custom
      application, you can overwrite the CustomManagement class and add calls to various imports you would like.

    PARAMETERS
     * username: username to log in as and run the import processes. Typically 'super'.

     Optional Parameters:
     * importName: Name of import process to run
     * messageInterval: how many rows before a message output is displayed showing the progress.
     * runTimeInSeconds: how many seconds to let this script run, if not specified will default to 20 minutes.

EOD;
    }

    /**
     * Execute the action.  Changes max run time to 20 minutes, pass the optional parameter
     * @param array command line parameters specific for this command
     */
    public function run($args)
    {
        if (SHOW_QUERY_DATA)
        {
            $this->usageError('The $queryDataOn parameter must be off to run command line imports.');
        }
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

        if (isset($args[1]) && !is_string($args[1]))
        {
            $this->usageError('The specified process to run is invalid.');
        }

        echo "\n";
        ImportUtil::runFromImportCommand($args);
    }
}
?>