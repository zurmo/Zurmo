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

    class InstallCommandTest extends ZurmoBaseTest
    {
        protected $temporaryDatabaseHostname;
        protected $temporaryDatabasePort = 3306;
        protected $temporaryDatabaseUsername;
        protected $temporaryDatabasePassword;
        protected $temporaryDatabaseName;
        protected $superUserPassword;
        protected $databaseBackupTestFile;

        protected $originalPerInstanceConfiguration;
        protected $originalDebugConfiguration;

        public function setUp()
        {
            parent::setUp();
            $instanceRoot = INSTANCE_ROOT;
            $perInstanceConfigFile      = "$instanceRoot/protected/config/perInstanceTest.php";
            $debugConfigFile            = "$instanceRoot/protected/config/debugTest.php";
            if (is_file($perInstanceConfigFile))
            {
                $this->originalPerInstanceConfiguration = file_get_contents($perInstanceConfigFile);
                unlink($perInstanceConfigFile);
            }
            if (is_file($debugConfigFile))
            {
                $this->originalDebugConfiguration = file_get_contents($debugConfigFile);
                unlink($debugConfigFile);
            }
            $this->assertTrue(!is_file($perInstanceConfigFile));
            $this->assertTrue(!is_file($debugConfigFile));
        }

        public function tearDown()
        {
            RedBeanDatabase::close();
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                Yii::app()->db->username,
                Yii::app()->db->password,
                true);

            $instanceRoot = INSTANCE_ROOT;
            $perInstanceConfigFile      = "$instanceRoot/protected/config/perInstanceTest.php";
            $debugConfigFile            = "$instanceRoot/protected/config/debugTest.php";

            // Restore original config files.
            unlink($debugConfigFile);
            unlink($perInstanceConfigFile);
            file_put_contents($perInstanceConfigFile, $this->originalPerInstanceConfiguration);
            file_put_contents($debugConfigFile, $this->originalDebugConfiguration);
            chmod($perInstanceConfigFile, 0777);
            chmod($debugConfigFile, 0777);
            parent::tearDown();
        }

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

        public function testRun()
        {
            chdir(COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands');

            $command = "php zurmocTest.php install {$this->temporaryDatabaseHostname} {$this->temporaryDatabaseName} ";
            $command .= "{$this->temporaryDatabaseUsername} {$this->temporaryDatabasePassword} {$this->temporaryDatabasePort} ";
            $command .= "{$this->superUserPassword} 'http://sampleHost' 'app/index.php' demodata 1";

            if (!IS_WINNT)
            {
                $command .= ' 2>&1';
            }

            exec($command, $output);

            $instanceRoot = INSTANCE_ROOT;
            $perInstanceConfigFile      = "$instanceRoot/protected/config/perInstanceTest.php";
            $debugConfigFile            = "$instanceRoot/protected/config/debugTest.php";
            $perInstanceConfiguration = file_get_contents($perInstanceConfigFile);
            $debugConfiguration = file_get_contents($debugConfigFile);

            //Check if config files is updated.
            $this->assertRegExp('/\$connectionString = \'mysql:host='.
                    $this->temporaryDatabaseHostname . ';port=' . $this->temporaryDatabasePort .
                    ';dbname=' . $this->temporaryDatabaseName . '\';/', // Not Coding Standard
                $perInstanceConfiguration);
            $this->assertRegExp('/\$username         = \''.$this->temporaryDatabaseUsername.'\';/',  // Not Coding Standard
                $perInstanceConfiguration);
            $this->assertRegExp('/\$password         = \''.$this->temporaryDatabasePassword.'\';/',  // Not Coding Standard
                $perInstanceConfiguration);

            RedBeanDatabase::close();
            RedBeanDatabase::setup(Yii::app()->tempDb->connectionString,
                                   Yii::app()->tempDb->username,
                                   Yii::app()->tempDb->password,
                                   true);
            $count   = R::getRow('select count(*) count from _user');
            $this->assertEquals(9, $count['count']);
        }
    }
?>
