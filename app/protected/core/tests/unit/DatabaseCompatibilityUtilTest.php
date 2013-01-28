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
        protected $testDatabaseHostname;
        protected $testDatabasePort = 3306;
        protected $testDatabaseName;
        protected $testDatabaseUsername;
        protected $testDatabasePassword;
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
            list(, $this->testDatabaseHostname, $this->testDatabasePort, $this->testDatabaseName) =
                array_values(RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString));
            $this->testDatabaseUsername          = Yii::app()->db->username;
            $this->testDatabasePassword          = Yii::app()->db->password;
            $this->superUserPassword = 'super';
            $this->databaseBackupTestFile = INSTANCE_ROOT . '/protected/runtime/databaseBackupTest.sql';
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
            if (is_file($this->databaseBackupTestFile))
            {
                unlink($this->databaseBackupTestFile);
            }
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

        public function testGetOperatorAndValueWherePart()
        {
            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('equals', 1);
            $compareQueryPart = "= 1";
            $this->assertEquals($compareQueryPart, $queryPart);

            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('equals', 'test@zumrmo.com');
            $compareQueryPart = "= 'test@zumrmo.com'";
            $this->assertEquals($compareQueryPart, $queryPart);

            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('greaterThan', 5);
            $compareQueryPart = "> 5";
            $this->assertEquals($compareQueryPart, $queryPart);

            $exceptionThrowed = false;
            try
            {
                $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('oneOf', 'aaa');
            }
            catch (NotSupportedException $e)
            {
                $exceptionThrowed = true;
            }
            $this->assertTrue($exceptionThrowed);

            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('startsWith', 'aaa');
            $compareQueryPart = "like 'aaa%'";
            $this->assertEquals($compareQueryPart, $queryPart);

            $exceptionThrowed = false;
            try
            {
                $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('startsWith', 5);
            }
            catch (NotSupportedException $e)
            {
                $exceptionThrowed = true;
            }
            $this->assertTrue($exceptionThrowed);

            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('oneOf', array(5, 6, 7));
            $compareQueryPart = "IN(5,6,7)"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);

            $queryPart = DatabaseCompatibilityUtil::getOperatorAndValueWherePart('oneOf', array('a', 'b', 'c'));
            $compareQueryPart = "IN('a','b','c')"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
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

        /**
        * @expectedException FailedAssertionException
        */
        public function testResolveToLowerForStringComparison()
        {
            $queryPart = DatabaseCompatibilityUtil::resolveToLowerForStringComparison('equals', 'test@zumrmo.com');
            $compareQueryPart = "= 'test@zumrmo.com'";
            $this->assertEquals($compareQueryPart, $queryPart);

            $queryPart = DatabaseCompatibilityUtil::resolveToLowerForStringComparison('greaterThan', '5');
            $compareQueryPart = "> '5'";
            $this->assertEquals($compareQueryPart, $queryPart);

            $queryPart = DatabaseCompatibilityUtil::resolveToLowerForStringComparison('greaterThan', 5);
        }

        public function testBulkInsert()
        {
            $model          = new TestDatabaseBulkInsertModel();
            $model->number  = 9999;
            $model->string  = 'adasd';
            $model->save();
            $model->delete();

            // Test with different quatations.
            $tableName      = TestDatabaseBulkInsertModel::getTableName('TestDatabaseBulkInsertModel');
            $columnNames    = array('number', 'string');
            $insertData     = array(
                array(999  , 'It\'s string with quatation.'),
                array(1000 , "It\`s string with quatation."),
                array(1001 , 'It\'s string with "quatation".')
            );
            DatabaseCompatibilityUtil::bulkInsert($tableName, $insertData, $columnNames, 3);

            $bulkInsertedRows      = R::getAll("select * from $tableName order by id");
            $this->assertEquals(count($bulkInsertedRows), 3);
            for ($i = 0; $i < 3; $i++)
            {
                $this->assertEquals($bulkInsertedRows[$i]['number'], $insertData[$i][0]);
                $this->assertEquals($bulkInsertedRows[$i]['string'], $insertData[$i][1]);
            }

            $models = TestDatabaseBulkInsertModel::getAll();
            if (count($models) > 0)
            {
                foreach ($models as $model)
                {
                    $model->delete();
                }
            }

            // Test when there are less rows of data then bulk quantity for one loop.
            $tableName      = TestDatabaseBulkInsertModel::getTableName('TestDatabaseBulkInsertModel');
            $columnNames    = array('number', 'string');
            $numberOfRows   = 50;
            $bulkQuantity   = 100;
            $insertData  = $this->createDumpDataForBulkInsert($numberOfRows);

            DatabaseCompatibilityUtil::bulkInsert($tableName, $insertData, $columnNames, $bulkQuantity);
            $bulkInsertedRows      = R::getAll("select * from $tableName order by id");
            $this->assertEquals(count($bulkInsertedRows), $numberOfRows);
            for ($i = 0; $i < $numberOfRows; $i++)
            {
                $this->assertEquals($bulkInsertedRows[$i]['number'], $insertData[$i][0]);
                $this->assertEquals($bulkInsertedRows[$i]['string'], $insertData[$i][1]);
            }

            $models = TestDatabaseBulkInsertModel::getAll();
            if (count($models) > 0)
            {
                foreach ($models as $model)
                {
                    $model->delete();
                }
            }

            // Test when there is much data, for multiple loops of bulk insert.
            $numberOfRows         = 520;
            $insertData  = $this->createDumpDataForBulkInsert($numberOfRows);
            $bulkQuantity         = 100;
            $importDataForOneLoop = array();
            foreach ($insertData as $row)
            {
                $importDataForOneLoop[] = $row;
                if (count($importDataForOneLoop) > $bulkQuantity)
                {
                    DatabaseCompatibilityUtil::bulkInsert($tableName,
                        $importDataForOneLoop,
                        $columnNames,
                        $bulkQuantity);
                    $importDataForOneLoop = array();
                }
            }
            $this->assertFalse(count($importDataForOneLoop) > $bulkQuantity);
            if (count($importDataForOneLoop) > 0)
            {
                DatabaseCompatibilityUtil::bulkInsert($tableName, $importDataForOneLoop, $columnNames, $bulkQuantity);
            }

            $bulkInsertedRows      = R::getAll("select * from $tableName order by id");
            $this->assertEquals(count($bulkInsertedRows), $numberOfRows);
            for ($i = 0; $i < $numberOfRows; $i++)
            {
                $this->assertEquals($bulkInsertedRows[$i]['number'], $insertData[$i][0]);
                $this->assertEquals($bulkInsertedRows[$i]['string'], $insertData[$i][1]);
            }
        }

        protected function createDumpDataForBulkInsert($number)
        {
            assert('is_numeric($number) && $number > 0'); // Not Coding Standard
            $data = array();
            for ($i = 0; $i < $number; $i++)
            {
                $data[$i] = array(
                    $i, $this->generateRandString(20)
                );
            }
            return $data;
        }

        protected function generateRandString($length)
        {
            $chars  = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
            $size   = strlen($chars);
            $str    = '';
            for ($i = 0; $i < $length; $i++)
            {
                $str .= $chars[rand(0, $size - 1)];
            }
            return $str;
        }

        public function testGetDatabaseVersion()
        {
            $databaseVersion = DatabaseCompatibilityUtil::getDatabaseVersion('mysql',
                                                                             $this->temporaryDatabaseHostname,
                                                                             $this->temporaryDatabaseUsername,
                                                                             $this->temporaryDatabasePassword,
                                                                             $this->temporaryDatabasePort);
            $this->assertTrue(strlen($databaseVersion) > 0);
        }

        public function testGetDatabaseMaxAllowedPacketsSizeRb()
        {
            $maxAllowedPacketSize = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSizeRb();
            $this->assertGreaterThan(0, $maxAllowedPacketSize);
        }

        public function testGetDatabaseMaxAllowedPacketsSize()
        {
            $maxAllowedPacketSize = DatabaseCompatibilityUtil::getDatabaseMaxAllowedPacketsSize('mysql',
                                                                                                $this->temporaryDatabaseHostname,
                                                                                                $this->temporaryDatabaseUsername,
                                                                                                $this->temporaryDatabasePassword,
                                                                                                $this->temporaryDatabasePort);
            $this->assertGreaterThan(0, $maxAllowedPacketSize);
        }

        public function testGetDatabaseMaxSpRecursionDepth()
        {
            $maxSpRecursionDepth = DatabaseCompatibilityUtil::getDatabaseMaxSpRecursionDepth('mysql',
                                                                                             $this->temporaryDatabaseHostname,
                                                                                             $this->temporaryDatabaseUsername,
                                                                                             $this->temporaryDatabasePassword,
                                                                                             $this->temporaryDatabasePort);
            $this->assertGreaterThan(0, $maxSpRecursionDepth);
        }

        public function testGetDatabaseThreadStackValue()
        {
            $threadStackValue = DatabaseCompatibilityUtil::getDatabaseThreadStackValue('mysql',
                                                                                       $this->temporaryDatabaseHostname,
                                                                                       $this->temporaryDatabaseUsername,
                                                                                       $this->temporaryDatabasePassword,
                                                                                       $this->temporaryDatabasePort);
            $this->assertGreaterThan(0, $threadStackValue);
        }

        public function testGetDatabaseOptimizerSearchDepthValue()
        {
            $dbOptimizerSearchDepthValue = DatabaseCompatibilityUtil::getDatabaseOptimizerSearchDepthValue('mysql',
                                                                                                           $this->temporaryDatabaseHostname,
                                                                                                           $this->temporaryDatabaseUsername,
                                                                                                           $this->temporaryDatabasePassword,
                                                                                                           $this->temporaryDatabasePort);
            $this->assertTrue($dbOptimizerSearchDepthValue !== false);
            $this->assertGreaterThanOrEqual(0, $dbOptimizerSearchDepthValue);
        }

        public function testGetDatabaseLogBinValue()
        {
            $databaseLogBinValue = DatabaseCompatibilityUtil::getDatabaseLogBinValue('mysql',
                                                                                     $this->temporaryDatabaseHostname,
                                                                                     $this->temporaryDatabaseUsername,
                                                                                     $this->temporaryDatabasePassword,
                                                                                     $this->temporaryDatabasePort);
            $this->assertTrue($databaseLogBinValue !== false);
            $this->assertTrue(is_string($databaseLogBinValue));
        }

        public function testGetDatabaseLogBinTrustFunctionCreatorsValue()
        {
            $logBinTrustValue = DatabaseCompatibilityUtil::getDatabaseLogBinTrustFunctionCreatorsValue('mysql',
                                                                                                       $this->temporaryDatabaseHostname,
                                                                                                       $this->temporaryDatabaseUsername,
                                                                                                       $this->temporaryDatabasePassword,
                                                                                                       $this->temporaryDatabasePort);
            $this->assertTrue($logBinTrustValue !== false);
            $this->assertTrue(is_string($logBinTrustValue));
        }

        public function testGetDatabaseDefaultCollation()
        {
            $dbDefaultCollation = DatabaseCompatibilityUtil::getDatabaseDefaultCollation('mysql',
                                                                                          $this->testDatabaseHostname,
                                                                                          $this->testDatabaseName,
                                                                                          $this->testDatabaseUsername,
                                                                                          $this->testDatabasePassword,
                                                                                          $this->testDatabasePort);
            $this->assertTrue(is_string($dbDefaultCollation));
            $this->assertTrue(strlen($dbDefaultCollation) > 0);
        }

        public function testIsDatabaseStrictMode()
        {
            $isDatabaseStrictMode = DatabaseCompatibilityUtil::isDatabaseStrictMode('mysql',
                                                                                    $this->temporaryDatabaseHostname,
                                                                                    $this->temporaryDatabaseUsername,
                                                                                    $this->temporaryDatabasePassword,
                                                                                    $this->temporaryDatabasePort);
            $this->assertTrue(is_bool($isDatabaseStrictMode));
        }

        public function testDatabaseConnection_mysql()
        {
            $this->assertTrue(DatabaseCompatibilityUtil::checkDatabaseConnection('mysql',
                                                                                $this->temporaryDatabaseHostname,
                                                                                $this->temporaryDatabaseUsername,
                                                                                $this->temporaryDatabasePassword,
                                                                                $this->temporaryDatabasePort));

            $compareData = array(1045, "Access denied for user");
            $result = DatabaseCompatibilityUtil::checkDatabaseConnection('mysql',
                                                                        $this->temporaryDatabaseHostname,
                                                                        $this->temporaryDatabaseUsername,
                                                                        'wrong',
                                                                        $this->temporaryDatabasePort);
            $result[1] = substr($result[1], 0, 22);
            $this->assertEquals($compareData, $result);
            $result = DatabaseCompatibilityUtil::checkDatabaseConnection('mysql',
                                                                        $this->temporaryDatabaseHostname,
                                                                        'nobody',
                                                                        'password',
                                                                        $this->temporaryDatabasePort);
            $result[1] = substr($result[1], 0, 22);
            $this->assertEquals($compareData, $result);
        }

        public function testCheckDatabaseExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->temporaryDatabaseUsername == 'root')
            {
                $this->assertTrue(DatabaseCompatibilityUtil::checkDatabaseExists('mysql',
                                                                                $this->testDatabaseHostname,
                                                                                $this->testDatabaseUsername,
                                                                                $this->testDatabasePassword,
                                                                                $this->testDatabasePort,
                                                                                $this->testDatabaseName));
                $this->assertEquals(array(1049, "Unknown database 'junk'"),
                DatabaseCompatibilityUtil::checkDatabaseExists('mysql',
                                                                $this->temporaryDatabaseHostname,
                                                                $this->temporaryDatabaseUsername,
                                                                $this->temporaryDatabasePassword,
                                                                $this->temporaryDatabasePort,
                                                                'junk'));
            }
        }

        public function testCheckDatabaseUserExists()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->temporaryDatabaseUsername == 'root')
            {
                $this->assertTrue (DatabaseCompatibilityUtil::checkDatabaseUserExists('mysql',
                                                                                        $this->temporaryDatabaseHostname,
                                                                                        $this->temporaryDatabaseUsername,
                                                                                        $this->temporaryDatabasePassword,
                                                                                        $this->temporaryDatabasePort,
                                                                                        $this->temporaryDatabaseUsername));
                $this->assertFalse(DatabaseCompatibilityUtil::checkDatabaseUserExists('mysql',
                                                                                        $this->temporaryDatabaseHostname,
                                                                                        $this->temporaryDatabaseUsername,
                                                                                        $this->temporaryDatabasePassword,
                                                                                        $this->temporaryDatabasePort,
                                                                                        'dude'));
            }
        }

        public function testCreateDatabase()
        {
            $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql',
                                                                        $this->temporaryDatabaseHostname,
                                                                        $this->temporaryDatabaseUsername,
                                                                        $this->temporaryDatabasePassword,
                                                                        $this->temporaryDatabasePort,
                                                                        $this->temporaryDatabaseName));
        }

        public function testCreateDatabaseUser()
        {
            // This test cannot run as saltdev. It is therefore skipped on the server.
            if ($this->temporaryDatabaseUsername == 'root')
            {
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql',
                                                                            $this->temporaryDatabaseHostname,
                                                                            $this->temporaryDatabaseUsername,
                                                                            $this->temporaryDatabasePassword,
                                                                            $this->temporaryDatabasePort,
                                                                            $this->temporaryDatabaseName));
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabaseUser('mysql',
                                                                                $this->temporaryDatabaseHostname,
                                                                                $this->temporaryDatabaseUsername,
                                                                                $this->temporaryDatabasePassword,
                                                                                $this->temporaryDatabasePort,
                                                                                $this->temporaryDatabaseName,
                                                                                'wacko',
                                                                                'wacked'));
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabaseUser('mysql',
                                                                                $this->temporaryDatabaseHostname,
                                                                                $this->temporaryDatabaseUsername,
                                                                                $this->temporaryDatabasePassword,
                                                                                $this->temporaryDatabasePort,
                                                                                $this->temporaryDatabaseName,
                                                                                'wacko',
                                                                                ''));
            }
        }

        public function testGetTableRowsCountTotal()
        {
            R::exec("create table temptesttable (temptable_id int(11) unsigned not null)");
            $tableRowsCountTotal = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            $this->assertGreaterThan(0, $tableRowsCountTotal);
        }

        /**
        * @expectedException NotSupportedException
        */
        public function testGetDatabaseDefaultPort()
        {
            $mysqlDatabaseDefaultPort = DatabaseCompatibilityUtil::getDatabaseDefaultPort('mysql');
            $this->assertEquals(3306, $mysqlDatabaseDefaultPort);

            $mysqlDatabaseDefaultPort = DatabaseCompatibilityUtil::getDatabaseDefaultPort('pgsql');
        }

        public function testDatabaseBackupAndRestore()
        {
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

                $this->assertTrue(DatabaseCompatibilityUtil::backupDatabase('mysql',
                                                                            $this->temporaryDatabaseHostname,
                                                                            $this->temporaryDatabaseUsername,
                                                                            $this->temporaryDatabasePassword,
                                                                            $this->temporaryDatabasePort,
                                                                            $this->temporaryDatabaseName,
                                                                            $this->databaseBackupTestFile));

                //Drop database, and restore it from backup.
                $this->assertTrue(DatabaseCompatibilityUtil::createDatabase('mysql',
                                                                            $this->temporaryDatabaseHostname,
                                                                            $this->temporaryDatabaseUsername,
                                                                            $this->temporaryDatabasePassword,
                                                                            $this->temporaryDatabasePort,
                                                                            $this->temporaryDatabaseName));
                $this->assertTrue(DatabaseCompatibilityUtil::restoreDatabase('mysql',
                                                                            $this->temporaryDatabaseHostname,
                                                                            $this->temporaryDatabaseUsername,
                                                                            $this->temporaryDatabasePassword,
                                                                            $this->temporaryDatabasePort,
                                                                            $this->temporaryDatabaseName,
                                                                            $this->databaseBackupTestFile));
                $connection = @mysql_connect($this->temporaryDatabaseHostname . ':' . $this->temporaryDatabasePort,
                                            $this->temporaryDatabaseUsername,
                                            $this->temporaryDatabasePassword);

                $this->assertTrue(is_resource($connection));

                @mysql_select_db($this->temporaryDatabaseName);
                $result = @mysql_query("SELECT count(*) from temptable");
                $totalRows = mysql_fetch_row($result);

                $result = @mysql_query("SELECT * from temptable");
                $rows1 = mysql_fetch_row($result);
                $rows2 = mysql_fetch_row($result);

                @mysql_close($connection);

                $this->assertEquals(2, $totalRows[0]);
                $this->assertEquals(5,  $rows1[0]);
                $this->assertEquals(10, $rows2[0]);
            }
        }

        public function testMapHintTypeIntoDatabaseColumnType()
        {
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('blob');
                $this->assertEquals('BLOB', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('longblob');
                $this->assertEquals('LONGBLOB', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('boolean');
                $this->assertEquals('TINYINT(1)', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('date');
                $this->assertEquals('DATE', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('datetime');
                $this->assertEquals('DATETIME', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('string');
                $this->assertEquals('VARCHAR(255)', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('text');
                $this->assertEquals('TEXT', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('longtext');
                $this->assertEquals('LONGTEXT', $databaseColumnType);

                $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('id');
                $this->assertEquals('INT(11) UNSIGNED', $databaseColumnType);

                try
                {
                    $databaseColumnType = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType('invalidType');
                    $this->fail();
                }
                catch (NotSupportedException $e)
                {
                    // Do nothing
                }
            }
        }
    }
?>
