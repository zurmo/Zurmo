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
     * UpdateSchemaCommand allows the schema to be updated.  This is useful if you are developing
     * and make changes to metadata that affects the database schema.
     */
    class DatabaseCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc database <action> <filePath> <databaseType> <databaseHost> <databaseName> <databasePort> <databaseUsername> <databasePassword>

    DESCRIPTION
      This command is used to backup or restore the Zurmo database.
      Please set \$maintenanceMode = true in the perInstance.php config file before you start this process.

    PARAMETERS
     * action: action(possible options: "backup" or "restore")
     * filePath: path to file
                   a. For backup, filepath where the database backup will be stored
                   b. For restore, path to file from which database will be restored

     Optional Parameters(please note that if you want to use optional parameters, you must use all them):
     * databaseType, for example mysql, oracle...
     * databaseHost
     * databaseName
     * databasePort
     * databaseUsername
     * databasePassword

EOD;
    }

        /**
         * Execute the action.
         * @param array command line parameters specific for this command
         */
        public function run($args)
        {
            set_time_limit('3600');

            if (!isset($args[0]))
            {
                $this->usageError('You must specify an action.');
            }
            else
            {
                $action = $args[0];
            }

            if (!isset($args[1]))
            {
                $this->usageError('You must specify a path to the file.');
            }
            else
            {
                $filePath = $args[1];
            }

            if (count($args) != 2 && count($args) != 8)
            {
                $this->usageError('Invalid number of arguments.');
            }

            if (count($args) == 8)
            {
                $databaseType     = $args[2];
                $databaseHost     = $args[3];
                $databaseName     = $args[4];
                $databasePort     = $args[5];
                $databaseUsername = $args[6];
                $databasePassword = $args[7];
            }
            else
            {
                $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString);

                $databaseType     = $databaseConnectionInfo['databaseType'];
                $databaseHost     = $databaseConnectionInfo['databaseHost'];
                $databaseName     = $databaseConnectionInfo['databaseName'];
                $databasePort     = $databaseConnectionInfo['databasePort'];
                $databaseUsername = Yii::app()->db->username;
                $databasePassword = Yii::app()->db->password;
            }

            if (!Yii::app()->isApplicationInMaintenanceMode())
            {
                $this->usageError('Please set $maintenanceMode = true in the perInstance.php config file.');
            }

            if (!function_exists('exec'))
            {
                $this->usageError('exec() command is not available in PHP environment.');
            }

            try
            {
                $template        = "{message}\n";
                $messageStreamer = new MessageStreamer($template);
                $messageStreamer->setExtraRenderBytes(0);
                $messageStreamer->add(' ');

                if ($action == 'backup')
                {
                    $this->backupDatabase($filePath,
                                          $messageStreamer,
                                          $databaseType,
                                          $databaseHost,
                                          $databaseName,
                                          $databasePort,
                                          $databaseUsername,
                                          $databasePassword);
                }
                elseif ($action == 'restore')
                {
                    $this->restoreDatabase($filePath,
                                           $messageStreamer,
                                           $databaseType,
                                           $databaseHost,
                                           $databaseName,
                                           $databasePort,
                                           $databaseUsername,
                                           $databasePassword);
                }
                else
                {
                    $this->usageError('Invalid action. Valid values are "backup" and "restore".');
                }
            }
            catch (Exception $e)
            {
                $messageStreamer->add(
                                      Zurmo::t('Commands', 'An error occur during database backup/restore: {message}',
                                               array('{message}' => $e->getMessage()))
                                      );
            }
        }

        /**
         * Backup database
         * @param string $filePath
         * @param MessageStreamer $messageStreamer
         * @param $databaseType
         * @param $databaseHost
         * @param $databaseName
         * @param $databasePort
         * @param $databaseUsername
         * @param $databasePassword
         */
        protected function backupDatabase($filePath,
                                          $messageStreamer,
                                          $databaseType,
                                          $databaseHost,
                                          $databaseName,
                                          $databasePort,
                                          $databaseUsername,
                                          $databasePassword)
        {
            // If file already exist, ask user to confirm that want to overwrite it.
            if (file_exists($filePath))
            {
                $message = Zurmo::t('Commands', 'Backup file already exists. Are you sure you want to overwrite the existing file?.');
                if (!$this->confirm($message))
                {
                    $messageStreamer->add(Zurmo::t('Commands', 'Backup not completed.'));
                    $messageStreamer->add(Zurmo::t('Commands', 'Please delete existing file or enter new one, and start backup process again.'));
                    Yii::app()->end();
                }
            }

            $messageStreamer->add(Zurmo::t('Commands', 'Starting database backup process.'));

            $result = DatabaseCompatibilityUtil::backupDatabase($databaseType,
                                                                $databaseHost,
                                                                $databaseUsername,
                                                                $databasePassword,
                                                                $databasePort,
                                                                $databaseName,
                                                                $filePath);
            if ($result)
            {
                 $messageStreamer->add(Zurmo::t('Commands', 'Database backup completed.'));
            }
            else
            {
                $messageStreamer->add(Zurmo::t('Commands', 'There was an error during backup.'));
                // It is possible that empty file is created, so delete it.
                if (file_exists($filePath))
                {
                    $messageStreamer->add(Zurmo::t('Commands', 'Deleting backup file.'));
                    unlink($filePath);
                 }
                 $messageStreamer->add(Zurmo::t('Commands', 'Please backup database manually.'));
            }
        }

        /**
         * Restore database from backup file.
         * Database must be empty before restore starts.
         * @param string $filePath
         * @param MessageStreamer $messageStreamer
         * @param $databaseType
         * @param $databaseHost
         * @param $databaseName
         * @param $databasePort
         * @param $databaseUsername
         * @param $databasePassword
         */
        protected function restoreDatabase($filePath,
                                           $messageStreamer,
                                           $databaseType,
                                           $databaseHost,
                                           $databaseName,
                                           $databasePort,
                                           $databaseUsername,
                                           $databasePassword)
        {
            $messageStreamer->add(Zurmo::t('Commands', 'Starting database restore process.'));

            $result = DatabaseCompatibilityUtil::restoreDatabase($databaseType,
                                                                 $databaseHost,
                                                                 $databaseUsername,
                                                                 $databasePassword,
                                                                 $databasePort,
                                                                 $databaseName,
                                                                 $filePath);
            if ($result)
            {
                $messageStreamer->add(Zurmo::t('Commands', 'Database restored.'));
            }
            else
            {
                $messageStreamer->add(Zurmo::t('Commands', 'There was an error during restore.'));
                $messageStreamer->add(Zurmo::t('Commands', 'Please restore database manually.'));
            }
        }
    }
?>