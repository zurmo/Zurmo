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
     * UpdateSchemaCommand allows the schema to be updated.  This is useful if you are developing
     * and make changes to metadata that affects the database schema.
     */
    class DatabaseCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
    USAGE
      zurmoc database <action> <filePath>

    DESCRIPTION
      This command is used to backup or restore the Zurmo database.
      Please set \$maintenanceMode = true in the perInstance.php config file before you start this process.

    PARAMETERS
     * action: action(possible options: "backup" or "restore")
     * filePath: path to file
                   a. For backup, filepath where the database backup will be stored
                   b. For restore, path to file from which database will be restored

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
                $messageStreamer->add('');

                if ($action == 'backup')
                {
                    $this->backupDatabase($filePath, $messageStreamer);
                }
                elseif ($action == 'restore')
                {
                    $this->restoreDatabase($filePath, $messageStreamer);
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
         */
        protected function backupDatabase($filePath, $messageStreamer)
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
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString);

            $result = DatabaseCompatibilityUtil::backupDatabase($databaseConnectionInfo['databaseType'],
                                                                $databaseConnectionInfo['databaseHost'],
                                                                Yii::app()->db->username,
                                                                Yii::app()->db->password,
                                                                $databaseConnectionInfo['databasePort'],
                                                                $databaseConnectionInfo['databaseName'],
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
         */
        protected function restoreDatabase($filePath, $messageStreamer)
        {
            $messageStreamer->add(Zurmo::t('Commands', 'Starting database restore process.'));
            $databaseConnectionInfo = RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString);

            $tables = DatabaseCompatibilityUtil::getAllTableNames();
            if (!empty($tables))
            {
                $messageStreamer->add(Zurmo::t('Commands', 'Database needs to be empty.'));
                $messageStreamer->add(Zurmo::t('Commands', 'Database is not restored.'));
                Yii::app()->end();
            }
            $result = DatabaseCompatibilityUtil::restoreDatabase($databaseConnectionInfo['databaseType'],
                                                                 $databaseConnectionInfo['databaseHost'],
                                                                 Yii::app()->db->username,
                                                                 Yii::app()->db->password,
                                                                 $databaseConnectionInfo['databasePort'],
                                                                 $databaseConnectionInfo['databaseName'],
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