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

    class DatabaseCompatibilityUtilTest extends BaseTest
    {
        protected $hostname;
        protected $rootUsername;
        protected $rootPassword;
        protected $databasePort = 3306;
        protected $existingDatabaseName;
        protected $temporaryDatabaseName;
        protected $superUserPassword;

        public function __construct()
        {
            parent::__construct();
            $matches = array();

            assert(preg_match("/host=([^;]+);(?:port=([^;]+);)?dbname=([^;]+)/", Yii::app()->db->connectionString, $matches) == 1); // Not Coding Standard
            if ($matches[2] != '')
            {
                $this->databasePort      = intval($matches[2]);
            }
            else
            {
                $databaseType = RedBeanDatabase::getDatabaseTypeFromDsnString(Yii::app()->db->connectionString);
                $this->databasePort = DatabaseCompatibilityUtil::getDatabaseDefaultPort($databaseType);
            }

            $this->hostname              = $matches[1];
            $this->rootUsername          = Yii::app()->db->username;
            $this->rootPassword          = Yii::app()->db->password;
            $this->existingDatabaseName  = $matches[3];
            $this->temporaryDatabaseName = "zurmo_wacky";
            if ($this->rootUsername == 'zurmo')
            {
                $this->rootUsername          = 'zurmoroot';
                $this->rootPassword          = 'somepass';
                $this->temporaryDatabaseName = 'zurmo_wacky';
            }
            $this->superUserPassword = 'super';
        }

        public function setup()
        {
            RedBeanDatabase::close();
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password,
                                   true);
        }

        public function tearDown()
        {
            RedBeanDatabase::close();
            RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                   Yii::app()->db->username,
                                   Yii::app()->db->password,
                                   true);
        }

        public function testCharLength()
        {
            $res = DatabaseCompatibilityUtil::charLength('tempColumn');
            $this->assertEquals('char_length(tempColumn)', $res);
        }

        public function testConcat()
        {
            $res = DatabaseCompatibilityUtil::concat(array('column1', 'column2'));
            $this->assertEquals('concat(column1, column2)', $res);
        }

        public function testDropTable()
        {
            R::exec("create table temptable (temptable_id int(11) unsigned not null)");
            DatabaseCompatibilityUtil::dropTable('temptable');
            $tables = DatabaseCompatibilityUtil::getAllTableNames();
            $this->assertFalse(in_array('temptable', $tables));
        }

        public function testGetAllTableNames()
        {
            R::exec("create table temptable (temptable_id int(11) unsigned not null)");
            $tables = DatabaseCompatibilityUtil::getAllTableNames();
            $this->assertTrue(in_array('temptable', $tables));
        }

        public function testGetDateFormat()
        {
            $this->assertEquals('yyyy-MM-dd', DatabaseCompatibilityUtil::getDateFormat());
        }

        public function testGetDateTimeFormat()
        {
            $this->assertEquals('yyyy-MM-dd HH:mm:ss', DatabaseCompatibilityUtil::getDateTimeFormat());
        }

        public function testGetMaxVarCharLength()
        {
            $this->assertEquals(255, DatabaseCompatibilityUtil::getMaxVarCharLength());
        }

        public function testLower()
        {
            $this->assertEquals('lower(tempColumn)', DatabaseCompatibilityUtil::lower('tempColumn'));
        }

        public function testGetQuote()
        {
            if (RedBeanDatabase::getDatabaseType() == 'pgsql')
            {
                $quoteCharacter = '"';
            }
            else
            {
                $quoteCharacter = '`';
            }
            $this->assertEquals($quoteCharacter, DatabaseCompatibilityUtil::getQuote());
        }

        public function testGetTrue()
        {
            if (RedBeanDatabase::getDatabaseType() == 'pgsql')
            {
                $trueValue = '1';
            }
            else
            {
                $trueValue = 'true';
            }
            $this->assertEquals($trueValue, DatabaseCompatibilityUtil::getTrue());
        }

        public function testLength()
        {
            $this->assertEquals('length(tempColumn)', DatabaseCompatibilityUtil::length('tempColumn'));
        }

        public function testQuoteString()
        {
            $string = 'tempColumn';
            if (RedBeanDatabase::getDatabaseType() == 'pgsql')
            {
                $quotedString = '"tempColumn"';
            }
            else
            {
                $quotedString = '`tempColumn`';
            }
            $this->assertEquals($quotedString, DatabaseCompatibilityUtil::quoteString($string));
        }

        public function testGetDatabaseMaxAllowedPacketsSizeRb()
        {
            $maxAllowedPacketSize = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSizeRb();
            $this->assertGreaterThan(0, $maxAllowedPacketSize);
        }

        public function testGetDatabaseMaxAllowedPacketsSize()
        {
            $maxAllowedPacketSize = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSize('mysql',
                                                                                                $this->hostname,
                                                                                                $this->rootUsername,
                                                                                                $this->rootPassword,
                                                                                                $this->databasePort);
            $this->assertGreaterThan(0, $maxAllowedPacketSize);
        }

        public function testGetDatabaseMaxSpRecursionDepth()
        {
            $maxSpRecursionDepth = DatabaseCompatibilityUtil::getDatabaseMaxSpRecursionDepth('mysql',
                                                                                             $this->hostname,
                                                                                             $this->rootUsername,
                                                                                             $this->rootPassword,
                                                                                             $this->databasePort);
            $this->assertGreaterThan(0, $maxSpRecursionDepth);
        }

        public function testGetDatabaseThreadStackValue()
        {
            $threadStackValue = DatabaseCompatibilityUtil::getDatabaseThreadStackValue('mysql',
                                                                                          $this->hostname,
                                                                                          $this->rootUsername,
                                                                                          $this->rootPassword,
                                                                                          $this->databasePort);
            $this->assertGreaterThan(0, $threadStackValue);
        }

        public function testGetDatabaseDefaultCollation()
        {
            $dbDefaultCollation = DatabaseCompatibilityUtil::getDatabaseDefaultCollation('mysql',
                                                                                          $this->hostname,
                                                                                          $this->existingDatabaseName,
                                                                                          $this->rootUsername,
                                                                                          $this->rootPassword,
                                                                                          $this->databasePort);
            $this->assertTrue(is_string($dbDefaultCollation));
            $this->assertTrue(strlen($dbDefaultCollation) > 0);
        }

        public function testIsDatabaseStrictMode()
        {
            $isDatabaseStrictMode = DatabaseCompatibilityUtil::isDatabaseStrictMode('mysql',
                                                                                    $this->hostname,
                                                                                    $this->rootUsername,
                                                                                    $this->rootPassword,
                                                                                    $this->databasePort);
            $this->assertTrue(is_bool($isDatabaseStrictMode));
        }

        public function testDatabaseConnection_mysql()
        {
            $this->assertTrue  (DatabaseCompatibilityUtil::checkDatabaseConnection('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort));
            $this->assertEquals(array(1045, "Access denied for user '{$this->rootUsername}'@'{$this->hostname}' (using password: YES)"),
                DatabaseCompatibilityUtil::checkDatabaseConnection('mysql', $this->hostname, $this->rootUsername,   'wrong', $this->databasePort));
            $this->assertEquals(array(1045, "Access denied for user 'nobody'@'{$this->hostname}' (using password: YES)"),
                DatabaseCompatibilityUtil::checkDatabaseConnection('mysql', $this->hostname, 'nobody', 'password', $this->databasePort));
        }

        public function testCheckDatabaseExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue  (DatabaseCompatibilityUtil::checkDatabaseExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->existingDatabaseName));
                $this->assertEquals(array(1049, "Unknown database 'junk'"),
                DatabaseCompatibilityUtil::checkDatabaseExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, 'junk'));
            }
        }

        public function testCheckDatabaseUserExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue (DatabaseCompatibilityUtil::checkDatabaseUserExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->rootUsername));
                $this->assertFalse(DatabaseCompatibilityUtil::checkDatabaseUserExists('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, 'dude'));
            }
        }

        public function testCreateDatabase()
        {
            $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->temporaryDatabaseName));
        }

        public function testCreateDatabaseUser()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->rootUsername == 'root')
            {
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabase    ('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->temporaryDatabaseName));
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabaseUser('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->temporaryDatabaseName, 'wacko', 'wacked'));
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabaseUser('mysql', $this->hostname, $this->rootUsername, $this->rootPassword, $this->databasePort, $this->temporaryDatabaseName, 'wacko', ''));
            }
        }

        public function testGetOperatorAndValueWherePartForNullOrEmpty()
        {
            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('isNull', null);
            $compareQueryPart = "IS NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('isNotNull', null);
            $compareQueryPart = "IS NOT NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('isEmpty', null);
            $compareQueryPart = "= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('isNotEmpty', null);
            $compareQueryPart = "!= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }
    }
?>
