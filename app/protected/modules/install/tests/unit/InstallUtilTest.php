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

    class InstallUtilTest extends BaseTest
    {
        protected $hostname;
        protected $rootUsername;
        protected $rootPassword;
        protected $existingDatabaseName;
        protected $temporaryDatabaseName;

        public function __construct()
        {
            parent::__construct();
            $matches = array();
            assert(preg_match("/host=([^;]+);dbname=([^;]+)/", Yii::app()->db->connectionString, $matches) == 1); // Not Coding Standard
            $this->hostname              = $matches[1];
            $this->rootUsername          = Yii::app()->db->username;
            $this->rootPassword          = Yii::app()->db->password;
            $this->existingDatabaseName  = $matches[2];
            $this->temporaryDatabaseName = "zurmo_wacky";
            if ($this->rootUsername == 'zurmo')
            {
                $this->rootUsername          = 'zurmoroot';
                $this->rootPassword          = 'somepass';
                $this->temporaryDatabaseName = 'zurmo_wacky';
            }
        }

        public function setup()
        {
            RedBeanDatabase::close();
        }

        public function tearDown()
        {
            RedBeanDatabase::close();
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password,
                                   true);
        }

        public function testWebServer()
        {
            $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.2.16 (Debian) Server Blaa Blaa Blaa';
            InstallUtil::checkWebServer(array('apache' => '10.0.0'), $expectedVersion);
            $this->assertFalse (InstallUtil::checkWebServer(array('apache' => '3.0.0'),  $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkWebServer(array('apache' => '2.2.16'), $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkWebServer(array('apache' => '2.2.0'),  $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertFalse (InstallUtil::checkWebServer(array('iis'    => '5.0.0'),  $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $_SERVER['SERVER_SOFTWARE'] = 'Apache';
            $this->assertFalse (InstallUtil::checkWebServer(array('apache' => '1.0.0'),  $actualVersion));
        }

        public function testCheckPhp()
        {
            $expectedVersion = PHP_VERSION;
            $this->assertFalse (InstallUtil::checkPhp('6.0.0',     $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertFalse (InstallUtil::checkPhp('5.8.0',     $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkPhp(PHP_VERSION, $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkPhp('4.4.1',     $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
        }

        public function testCheckPhpTimezoneSetting()
        {
            $oldValue = ini_get('date.timezone');
            ini_set('date.timezone', '');
            $this->assertFalse(InstallUtil::checkPhpTimezoneSetting());
            ini_set('date.timezone', 'EST');
            $this->assertTrue (InstallUtil::checkPhpTimezoneSetting());
            ini_set('date.timezone', $oldValue);
        }

        public function testCheckPhpMaxMemorySetting()
        {
            $oldValue = ini_get('memory_limit');
            ini_set('memory_limit', '64M');
            $this->assertFalse  (InstallUtil::checkPhpMaxMemorySetting(1024 * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(64   * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(12   * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting( 1   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            ini_set('memory_limit', '64m');
            $this->assertFalse  (                        InstallUtil::checkPhpMaxMemorySetting(1024 * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(64   * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(12   * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting( 1   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024,        $actualMemoryLimitBytes);
            //causing actual exhausting of memory during tests.
            /*
            ini_set('memory_limit', '64K');
            $this->assertFalse (64 * 1024,               InstallUtil::checkPhpMaxMemorySetting(1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024,               $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(64   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024,               $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(12   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024,               $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting( 1   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024,               $actualMemoryLimitBytes);
            */
            ini_set('memory_limit', '64G');
            $this->assertFalse(                          InstallUtil::checkPhpMaxMemorySetting(1024 * 1024 * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024 * 1024, $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(64   * 1024 * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024 * 1024, $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting(12   * 1024 * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024 * 1024, $actualMemoryLimitBytes);
            $this->assertTrue  (                         InstallUtil::checkPhpMaxMemorySetting( 1   * 1024, $actualMemoryLimitBytes));
            $this->assertEquals(64 * 1024 * 1024 * 1024, $actualMemoryLimitBytes);
            ini_set('memory_limit', $oldValue);
        }

        public function testCheckDatabase_mysql()
        {
            InstallUtil::checkDatabase('mysql', '10.5.5', $expectedVersion);
            $this->assertFalse (InstallUtil::checkDatabase('mysql', '7.0.0  ',        $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkDatabase('mysql', $expectedVersion, $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue  (InstallUtil::checkDatabase('mysql', '5.0.0',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
        }

        public function testCheckAPC()
        {
            InstallUtil::checkAPC('10.1.3', $expectedVersion);
            $this->assertFalse(InstallUtil::checkAPC('5.1.3',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            if (phpversion('apc') !== false)
            {
                $this->assertTrue (InstallUtil::checkAPC($expectedVersion, $actualVersion));
                $this->assertEquals($expectedVersion, $actualVersion);
                $this->assertTrue (InstallUtil::checkAPC('2.0.5',          $actualVersion));
                $this->assertEquals($expectedVersion, $actualVersion);
            }
        }

        public function testCheckTidy()
        {
            InstallUtil::checkTidy('10.1.3', $expectedVersion);
            $this->assertFalse(InstallUtil::checkTidy('2.1.3',        $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkTidy($actualVersion, $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkTidy('1.9.7',        $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
        }

        public function testCheckYii()
        {
            InstallUtil::checkYii('10.1.8', $expectedVersion);
            $this->assertFalse(InstallUtil::checkYii('3.1.8',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkYii($expectedVersion, $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkYii('1.1.6',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
        }

        public function testCheckRedBean()
        {
            InstallUtil::checkRedBean('10.1.3', $expectedVersion);
            $this->assertFalse(InstallUtil::checkRedBean('2.1.3',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkRedBean($expectedVersion, $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
            $this->assertTrue (InstallUtil::checkRedBean('1.2.9',          $actualVersion));
            $this->assertEquals($expectedVersion, $actualVersion);
        }

        public function testCheckRedBeanPatched()
        {
            $this->assertTrue(InstallUtil::checkRedBeanPatched());
        }

        public function testIsMbStringInstalled()
        {
            $this->assertTrue(InstallUtil::isMbStringInstalled());
        }

        public function testIsFileUploadsOn()
        {
            $this->assertTrue(InstallUtil::isFileUploadsOn());
        }

        /**
         * Setting the upload_max_filesize doesn't seem to do anything.
         */
        public function testCheckPhpUploadSizeSetting()
        {
            $this->assertFalse  (InstallUtil::checkPhpUploadSizeSetting(1024 * 1024 * 1024, $actualUploadLimitBytes));
            $this->assertTrue  (InstallUtil::checkPhpUploadSizeSetting(1 * 1024 * 1024, $actualUploadLimitBytes));
        }

        /**
         * Setting the post_max_size doesn't seem to do anything.
         */
        public function testCheckPhpPostSizeSetting()
        {
            $this->assertFalse (InstallUtil::checkPhpPostSizeSetting(1024 * 1024 * 1024, $actualPostLimitBytes));
            $this->assertTrue  (InstallUtil::checkPhpPostSizeSetting(1 * 1024 * 1024, $actualPostLimitBytes));
        }

        /**
         * Simple test to confirm the check doesnt break.
         */
        public function testGetDatabaseMaxAllowedPacketsSize()
        {
            $minimumRequireBytes = 1;
            $actualBytes         = null;
            $this->assertNotNull(InstallUtil::getDatabaseMaxAllowedPacketsSize('mysql', 1, $actualBytes));
        }

        public function testCheckMemcacheConnection()
        {
            $this->assertTrue  (InstallUtil::checkMemcacheConnection('127.0.0.1', 11211));
            $this->assertTrue  (InstallUtil::checkMemcacheConnection('localhost', 11211));
            $results = InstallUtil::checkMemcacheConnection('10.3.3.3',  11211);
            $this->assertTrue(  110 == $results[0] ||
                                10060 == $results[0]);
            $results = InstallUtil::checkMemcacheConnection('localhost', 12345);
            $this->assertTrue(  111 == $results[0] ||
                                10061 == $results[0]);
        }

        public function testDatabaseConnection_mysql()
        {
            $this->assertTrue  (InstallUtil::checkDatabaseConnection('mysql', $this->hostname, $this->rootUsername, $this->rootPassword));
            $this->assertEquals(array(1045, "Access denied for user '{$this->rootUsername}'@'{$this->hostname}' (using password: YES)"),
                                InstallUtil::checkDatabaseConnection('mysql', $this->hostname, $this->rootUsername,   'wrong'));
            $this->assertEquals(array(1045, "Access denied for user 'nobody'@'{$this->hostname}' (using password: YES)"),
                                InstallUtil::checkDatabaseConnection('mysql', $this->hostname, 'nobody', 'password'));
        }

        /**
         * @depends testDatabaseConnection_mysql
         */
        public function testCheckDatabaseExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue  (InstallUtil::checkDatabaseExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->existingDatabaseName));
                $this->assertEquals(array(1049, "Unknown database 'junk'"),
                                    InstallUtil::checkDatabaseExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, 'junk'));
            }
        }

        /**
         * @depends testCheckDatabaseExists
         */
        public function testCheckDatabaseUserExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue (InstallUtil::checkDatabaseUserExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->rootUsername));
                $this->assertFalse(InstallUtil::checkDatabaseUserExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, 'dude'));
            }
        }

        /**
         * @depends testCheckDatabaseUserExists
         */
        public function testCreateDatabase()
        {
            $this->assertTrue(InstallUtil::createDatabase('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName));
        }

        /**
         * @depends testCreateDatabase
         */
        public function testCreateDatabaseUser()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue(InstallUtil::createDatabase    ('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName));
                $this->assertTrue(InstallUtil::createDatabaseUser('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName, 'wacko', 'wacked'));
                $this->assertTrue(InstallUtil::createDatabaseUser('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName, 'wacko', ''));
            }
        }

        /**
         * @depends testCreateDatabaseUser
         */
        public function testConnectToDatabaseCreateSuperUserBuildDatabaseAndFreeze()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue(InstallUtil::createDatabase    ('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName));
                $this->assertTrue(InstallUtil::createDatabaseUser('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->temporaryDatabaseName, 'wacko', 'wacked'));
                InstallUtil::connectToDatabase('mysql', $this->hostname, 'wacky', $this->rootUsername, $this->rootPassword);
                Yii::app()->user->userModel = InstallUtil::createSuperUser('super', 'super');
                $messageLogger = new MessageLogger();
                InstallUtil::autoBuildDatabase($messageLogger);
                $this->assertFalse($messageLogger->isErrorMessagePresent());
                ReadPermissionsOptimizationUtil::rebuild();
                InstallUtil::freezeDatabase();
                $tableNames = R::getCol('show tables');
                $this->assertEquals(array(
                                        '_group',
                                        '_group__user',
                                        '_right',
                                        '_user',
                                        'account',
                                        'account_read',
                                        'accountsfilteredlist',
                                        'activity',
                                        'activity_item',
                                        'actual_permissions_cache',
                                        'address',
                                        'auditevent',
                                        'contact',
                                        'contact_opportunity',
                                        'contact_read',
                                        'contactsfilteredlist',
                                        'contactstate',
                                        'currency',
                                        'currencyvalue',
                                        'customfield',
                                        'customfielddata',
                                        'customfieldsmodel',
                                        'dashboard',
                                        'email',
                                        'filecontent',
                                        'filemodel',
                                        'filteredlist',
                                        'globalmetadata',
                                        'item',
                                        'leadsfilteredlist',
                                        'log',
                                        'mashableactivity',
                                        'meeting',
                                        'meeting_read',
                                        'namedsecurableitem',
                                        'note',
                                        'note_read',
                                        'opportunitiesfilteredlist',
                                        'opportunity',
                                        'opportunity_read',
                                        'ownedcustomfield',
                                        'ownedmodel',
                                        'ownedsecurableitem',
                                        'permission',
                                        'permitable',
                                        'person',
                                        'perusermetadata',
                                        'policy',
                                        'portlet',
                                        'role',
                                        'securableitem',
                                        'task',
                                        'task_read',
                                    ),
                                    $tableNames);
            }
        }

        public function testWriteConfiguration()
        {
            $instanceRoot = '.';

            $perInstanceConfigFileDist = "$instanceRoot/protected/config/perInstanceDIST.php";
            $perInstanceConfigFile     = "$instanceRoot/protected/config/perInstance.php";
            copy($perInstanceConfigFileDist, $perInstanceConfigFile);

            $debugConfigFileDist = "$instanceRoot/protected/config/debugDIST.php";
            $debugConfigFile     = "$instanceRoot/protected/config/debug.php";
            copy($debugConfigFileDist, $debugConfigFile);

            $this->assertRegExp   ('/\$debugOn = true;/',
                                   $originalDebugConfiguration);
            $this->assertRegExp   ('/\$forceNoFreeze = true;/',
                                   $originalDebugConfiguration);
            $originalPerInstanceConfiguration = file_get_contents($perInstanceConfigFile);
            try
            {
                InstallUtil::writeConfiguration($instanceRoot,
                                                'mysql', 'databases.r-us.com', 'wacky', 'wacko', 'wacked',
                                                'memcache.jason.com', 5432,
                                                'es');
                $debugConfiguration       = file_get_contents($debugConfigFile);
                $perInstanceConfiguration = file_get_contents($perInstanceConfigFile);
                $this->assertRegExp   ('/\$debugOn = false;/',
                                       $debugConfiguration);
                $this->assertRegExp   ('/\$forceNoFreeze = false;/',
                                       $debugConfiguration);
                $this->assertRegExp   ('/\$language         = \'es\';/',
                                       $perInstanceConfiguration);
                $this->assertRegExp   ('/\$connectionString = \'mysql:host=databases.r-us.com;dbname=wacky\';/', // Not Coding Standard
                                       $perInstanceConfiguration);
                $this->assertRegExp   ('/\$username         = \'wacko\';/',
                                       $perInstanceConfiguration);
                $this->assertRegExp   ('/\$password         = \'wacked\';/',
                                       $perInstanceConfiguration);
                $this->assertRegExp   ('/\'host\'   => \'memcache.jason.com\',\n' .            // Not Coding Standard
                                       '                                \'port\'   => 5432,/', // Not Coding Standard
                                       $perInstanceConfiguration);
                $this->assertNotRegExp('/\/\/ REMOVE THE REMAINDER/',
                                       $perInstanceConfiguration);
            }
            catch (Exception $e)
            {
                if (isset($debugConfiguration))
                {
                    echo $debugConfiguration;
                }
                if (isset($perInstanceConfiguration))
                {
                    echo $perInstanceConfiguration;
                }
            }
            // finally
            // {
                unlink($debugConfigFile);
                unlink($perInstanceConfigFile);
            // }
            if (isset($e)) // This bizarre looking $e stuff is because php thinks 'finally is not useful'.
            {
                throw $e;
            }
        }
    }
?>
