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

    class DatabaseCommandTest extends ZurmoBaseTest
    {
        protected $temporaryDatabaseHostname;
        protected $temporaryDatabasePort = 3306;
        protected $temporaryDatabaseUsername;
        protected $temporaryDatabasePassword;
        protected $temporaryDatabaseName;
        protected $superUserPassword;
        protected $databaseBackupTestFile;

        public function __construct()
        {
            parent::__construct();
            list(, $this->temporaryDatabaseHostname, $this->temporaryDatabasePort, $this->temporaryDatabaseName) =
                array_values(RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->tempDb->connectionString));
            $this->temporaryDatabaseUsername = Yii::app()->tempDb->username;
            $this->temporaryDatabasePassword = Yii::app()->tempDb->password;
            $this->superUserPassword = 'super';
            $this->databaseBackupTestFile = INSTANCE_ROOT . '/protected/runtime/databaseBackupTest.sql';
        }

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $contents = file_get_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php');
            $contents = preg_replace('/\$maintenanceMode\s*=\s*false;/',
                                     '$maintenanceMode = true;',
                                     $contents);
            file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
        }

        public static function tearDownAfterClass()
        {
            $contents = file_get_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php');
            $contents = preg_replace('/\$maintenanceMode\s*=\s*true;/',
                                     '$maintenanceMode = false;',
                                     $contents);
            file_put_contents(INSTANCE_ROOT . '/protected/config/perInstanceTest.php', $contents);
            parent::tearDownAfterClass();
        }

        public function setup()
        {
            if (is_file($this->databaseBackupTestFile))
            {
                unlink($this->databaseBackupTestFile);
            }
        }

        public function tearDown()
        {
            if (is_file($this->databaseBackupTestFile))
            {
                unlink($this->databaseBackupTestFile);
            }
        }

        public function testBackupAndRestoreDatabase()
        {
            chdir(COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands');

            // Create new database (zurmo_temp).
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql',
                    $this->temporaryDatabaseHostname,
                    $this->temporaryDatabaseUsername,
                    $this->temporaryDatabasePassword,
                    $this->temporaryDatabasePort,
                    $this->temporaryDatabaseName));
                $connection = @mysql_connect($this->temporaryDatabaseHostname . ':' . $this->temporaryDatabasePort,
                    $this->temporaryDatabaseUsername,
                    $this->temporaryDatabasePassword);
                $this->assertTrue(is_resource($connection));

                @mysql_select_db($this->temporaryDatabaseName);
                @mysql_query("create table temptable (temptable_id int(11) unsigned not null)", $connection);
                @mysql_query("insert into temptable values ('5')", $connection);
                @mysql_query("insert into temptable values ('10')", $connection);
                $result = @mysql_query("SELECT count(*) from temptable");
                $totalRows = mysql_fetch_row($result);
                @mysql_close($connection);

                $this->assertEquals(2, $totalRows[0]);

                $command = "php zurmocTest.php database backup {$this->databaseBackupTestFile} mysql ";
                $command .= "{$this->temporaryDatabaseHostname} {$this->temporaryDatabaseName} ";
                $command .= "{$this->temporaryDatabasePort} {$this->temporaryDatabaseUsername} {$this->temporaryDatabasePassword}";

                if (!IS_WINNT)
                {
                    $command .= ' 2>&1';
                }

                exec($command, $output);
                sleep(2);

                $this->assertTrue(is_file($this->databaseBackupTestFile));

                //Drop database, and restore it from backup.
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql',
                    $this->temporaryDatabaseHostname,
                    $this->temporaryDatabaseUsername,
                    $this->temporaryDatabasePassword,
                    $this->temporaryDatabasePort,
                    $this->temporaryDatabaseName));

                // Ensure that database don't exist
                $connection = @mysql_connect($this->temporaryDatabaseHostname . ':' . $this->temporaryDatabasePort,
                    $this->temporaryDatabaseUsername,
                    $this->temporaryDatabasePassword);
                $this->assertTrue(is_resource($connection));

                @mysql_select_db($this->temporaryDatabaseName, $connection);
                $result = @mysql_query("SELECT count(*) from temptable", $connection);
                $this->assertFalse($result);

                // Now restore database
                $command = "php zurmocTest.php database restore {$this->databaseBackupTestFile} mysql ";
                $command .= "{$this->temporaryDatabaseHostname} {$this->temporaryDatabaseName} ";
                $command .= "{$this->temporaryDatabasePort} {$this->temporaryDatabaseUsername} {$this->temporaryDatabasePassword}";
                if (!IS_WINNT)
                {
                    $command .= ' 2>&1';
                }

                exec($command, $output);
                sleep(2);

                $connection = @mysql_connect($this->temporaryDatabaseHostname . ':' . $this->temporaryDatabasePort,
                    $this->temporaryDatabaseUsername,
                    $this->temporaryDatabasePassword);

                $this->assertTrue(is_resource($connection));

                $result = @mysql_select_db($this->temporaryDatabaseName, $connection);
                $this->assertTrue($result);

                $result = @mysql_query("SELECT count(*) from temptable", $connection);
                $this->assertTrue(is_resource($result));
                $totalRows = mysql_fetch_row($result);

                $result = @mysql_query("SELECT * from temptable", $connection);
                $rows1 = mysql_fetch_row($result);
                $rows2 = mysql_fetch_row($result);

                @mysql_close($connection);

                $this->assertEquals(2, $totalRows[0]);
                $this->assertEquals(5,  $rows1[0]);
                $this->assertEquals(10, $rows2[0]);
            }
        }
    }
?>
