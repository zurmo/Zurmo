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
     * Helper functionality for use in RedBeanModels and derived models.
     * These functions cater for specific databases other than MySQL,
     * then by default return results for for MySQL.
     */
    class DatabaseCompatibilityUtil
    {
        /**
         * Returns the database character length equivalent string function by a column name.
         * @param string $columnName
         */
        public static function charLength($columnName)
        {
            assert('is_string($columnName)');
            return 'char_length(' . $columnName . ')';
        }

        /**
         * Returns sql to concatentate the given strings for
         * the current database.
         */
        public static function concat(array $strings)
        {
            assert('AssertUtil::all($strings, "is_string")');
            if (in_array(RedBeanDatabase::getDatabaseType(), array('sqlite', 'pgsql')))
            {
                return implode(' || ', $strings);
            }
            else
            {
                return 'concat(' . implode(', ', $strings) . ')';
            }
        }

        /**
         * Drops the named table.
         */
        public static function dropTable($tableName)
        {
            assert('is_string($tableName) && $tableName != ""');
            R::exec("drop table $tableName;");
        }

        public static function escape($value)
        {
            return R::$adapter->escape($value);
        }

        /**
         * Returns an array of table names from the database.
         */
        public static function getAllTableNames()
        {
            assert('RedBeanDatabase::isSetup()');
            if (RedBeanDatabase::getDatabaseType() == 'sqlite')
            {
                return R::getCol('select name from sqlite_master where type = \'table\' order by name;');
            }
            elseif (RedBeanDatabase::getDatabaseType() == 'pgsql')
            {
                return R::getCol("
                    select relname from pg_catalog.pg_class
                         left join pg_catalog.pg_namespace n on n.oid = pg_catalog.pg_class.relnamespace
                    where pg_catalog.pg_class.relkind in ('r', '') and
                          n.nspname <> 'pg_catalog'               and
                          n.nspname <> 'information_schema'       and
                          n.nspname !~ '^pg_toast'                and
                          pg_catalog.pg_table_is_visible(pg_catalog.pg_class.oid)
                    order by lower(relname);
                ");
            }
            else
            {
                return R::getCol('show tables;');
            }
        }

        /**
         * Get the date format for the database in Unicode format.
         * http://www.unicode.org/reports/tr35/#Date_Format_Patterns
         * @return string
         */
        public static function getDateFormat()
        {
            return 'yyyy-MM-dd';
        }

        /**
         * Get the datetime format for the database in Unicode format.
         * http://www.unicode.org/reports/tr35/#Date_Format_Patterns
         * @return string
         */
        public static function getDateTimeFormat()
        {
            return 'yyyy-MM-dd HH:mm:ss';
        }

        /**
         * Returns the maximum varchar column type value.
         */
        public static function getMaxVarCharLength()
        {
            return 255;
        }

        /**
         * Returns the database string to lower equivalent string function by a column name and adds quotes
         * to it.
         * @param string $columnName
         */
        public static function lower($columnName)
        {
            assert('is_string($columnName)');
            return 'lower(' . $columnName . ')';
        }

        /**
         * Get the quote used for quoting table and column names.
         * for the current database.
         * Note: ' is always used for strings.
         */
        public static function getQuote()
        {
            assert('RedBeanDatabase::isSetup()');
            if (RedBeanDatabase::getDatabaseType() == 'pgsql')
            {
                return '"';
            }
            else
            {
                return '`';
            }
        }

        /**
         * Returns the value that represents true in sql for the
         * current database.
         */
        public static function getTrue()
        {
            assert('RedBeanDatabase::isSetup()');
            if (RedBeanDatabase::getDatabaseType() == 'sqlite')
            {
                return '1';
            }
            else
            {
                return 'true';
            }
        }

        /**
         * Returns the database length equivalent string function by a column name.
         * @param string $columnName
         */
        public static function length($columnName)
        {
            assert('is_string($columnName)');
            return 'length(' . $columnName . ')';
        }

        /**
         * Returns the given string quoted for the current
         * database.
         * Note: ' is always used for strings. Do not use this
         * function to quote strings in sql.
         */
        public static function quoteString($string)
        {
            assert('is_string($string)');
            $quote = self::getQuote();
            return "$quote$string$quote";
        }

        /**
         * Get max length for column name
         * @return int
         */
        public static function getDatabaseMaxColumnNameLength()
        {
            $maxColumnNameLength = 12;
            return $maxColumnNameLength;
        }

        /**
         * Get array of database reserved words
         * @throws NotSupportedException
         * @return multitype:string
         */
        public static function getDatabaseReserverWords()
        {
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                $reservedWords = array(
                    'ACCESSIBLE', 'ALTER', 'AS', 'BEFORE', 'BINARY', 'BY', 'CASE', 'CHARACTER', 'COLUMN', 'CONTINUE', 'CROSS',
                    'CURRENT_TIMESTAMP', 'DATABASE', 'DAY_MICROSECOND', 'DEC', 'DEFAULT', 'DESC', 'DISTINCT', 'DOUBLE', 'EACH',
                    'ENCLOSED', 'EXIT', 'FETCH', 'FLOAT8', 'FOREIGN', 'GRANT', 'HIGH_PRIORITY', 'HOUR_SECOND', 'IN', 'INNER',
                    'INSERT', 'INT2', 'INT8', 'INTO', 'JOIN', 'KILL', 'LEFT', 'LINEAR', 'LOCALTIME', 'LONG', 'LOOP', 'MATCH',
                    'MEDIUMTEXT', 'MINUTE_SECOND', 'NATURAL', 'NULL', 'OPTIMIZE', 'OR', 'OUTER', 'PRIMARY', 'RANGE', 'READ_WRITE', // Not Coding Standard
                    'REGEXP', 'REPEAT', 'RESTRICT', 'RIGHT', 'SCHEMAS', 'SENSITIVE', 'SHOW', 'SPECIFIC', 'SQLSTATE',
                    'SQL_CALC_FOUND_ROWS', 'STARTING', 'TERMINATED', 'TINYINT', 'TRAILING', 'UNDO', 'UNLOCK', 'USAGE',
                    'UTC_DATE', 'VALUES', 'VARCHARACTER', 'WHERE', 'WRITE', 'ZEROFILL', 'ALL', 'AND', 'ASENSITIVE', 'BIGINT',
                    'BOTH', 'CASCADE', 'CHAR', 'COLLATE', 'CONSTRAINT', 'COUNT', 'CREATE', 'CURRENT_TIME', 'CURSOR', 'DAY_HOUR',
                    'DAY_SECOND', 'DECLARE', 'DELETE', 'DETERMINISTIC', 'DIV', 'DUAL', 'ELSEIF', 'EXISTS', 'FALSE', 'FLOAT4', // Not Coding Standard
                    'FORCE', 'FULLTEXT', 'HAVING', 'HOUR_MINUTE', 'IGNORE', 'INFILE', 'INSENSITIVE', 'INT1', 'INT4', 'INTERVAL',
                    'ITERATE', 'KEYS', 'LEAVE', 'LIMIT', 'LOAD', 'LOCK', 'LONGTEXT', 'MASTER_SSL_VERIFY_SERVER_CERT', 'MEDIUMINT',
                    'MINUTE_MICROSECOND', 'MODIFIES', 'NO_WRITE_TO_BINLOG', 'ON', 'OPTIONALLY', 'OUT', 'PRECISION', 'PURGE',
                    'READS', 'REFERENCES', 'RENAME', 'REQUIRE', 'REVOKE', 'SCHEMA', 'SELECT', 'SET', 'SPATIAL', 'SQLEXCEPTION',
                    'SQL_BIG_RESULT', 'SSL', 'TABLE', 'TINYBLOB', 'TO', 'TRUE', 'UNIQUE', 'UPDATE', 'USING', 'UTC_TIMESTAMP', // Not Coding Standard
                    'VARCHAR', 'WHEN', 'WITH', 'YEAR_MONTH', 'ADD', 'ANALYZE', 'ASC', 'BETWEEN', 'BLOB', 'CALL', 'CHANGE', 'CHECK',
                    'CONDITION', 'CONVERT', 'CURRENT_DATE', 'CURRENT_USER', 'DATABASES', 'DAY_MINUTE', 'DECIMAL', 'DELAYED',
                    'DESCRIBE', 'DISTINCTROW', 'DROP', 'ELSE', 'ESCAPED', 'EXPLAIN', 'FLOAT', 'FOR', 'FROM', 'GROUP',
                    'HOUR_MICROSECOND', 'IF', 'INDEX', 'INOUT', 'INT', 'INT3', 'INTEGER', 'IS', 'KEY', 'LEADING', 'LIKE', 'LINES',
                    'LOCALTIMESTAMP', 'LONGBLOB', 'LOW_PRIORITY', 'MEDIUMBLOB', 'MIDDLEINT', 'MOD', 'NOT', 'NUMERIC', 'OPTION',
                    'ORDER', 'OUTFILE', 'PROCEDURE', 'READ', 'REAL', 'RELEASE', 'REPLACE', 'RETURN', 'RLIKE', 'SECOND_MICROSECOND',
                    'SEPARATOR', 'SMALLINT', 'SQL', 'SQLWARNING', 'SQL_SMALL_RESULT', 'STRAIGHT_JOIN', 'THEN', 'TINYTEXT',
                    'TRIGGER', 'UNION', 'UNSIGNED', 'USE', 'UTC_TIME', 'VARBINARY', 'VARYING', 'WHILE', 'XOR',
                    'GENERAL', 'IGNORE_SERVER_ID', 'MASTER_HEARTBEAT_PERIOD', 'MAXVALUE', 'RESIGNAL', 'SIGNAL', 'SLOW'
                );
            }
            else
            {
                throw new NotSupportedException();
            }
            return $reservedWords;
        }

        /**
         * Given an operator type and value, SQL is constructed. Example
         * return would be '>= 5'.
         * @return string
         */
        public static function getOperatorAndValueWherePart($operatorType, $value)
        {
            assert('is_string($operatorType)');
            if (!SQLOperatorUtil::isValidOperatorTypeByValue($operatorType, $value))
            {
                throw new NotSupportedException('value: ' . $value . ' operator type: ' . $operatorType);
            }
            if (is_string($value))
            {
                return self::resolveToLowerForStringComparison($operatorType, self::escape($value));
            }
            elseif (is_array($value) && count($value) > 0)
            {
                return SQLOperatorUtil::resolveOperatorAndValueForOneOf($operatorType, $value);
            }
            elseif ($value !== null)
            {
                return SQLOperatorUtil::getOperatorByType($operatorType) . " " . self::escape($value);
            }
            elseif ($value === null)
            {
                return SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty($operatorType);
            }
        }

        public static function resolveToLowerForStringComparison($operatorType, $value)
        {
            assert('is_string($operatorType)');
            assert('is_string($value)');
            if (RedBeanDatabase::getDatabaseType() != 'mysql')
            {
                //todo: for pgsql, need to use lower or ILIKE to make sure evaluation is not case sensitive
                throw new NotSupportedException();
            }
            return SQLOperatorUtil::getOperatorByType($operatorType) .
            " '" . SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType($operatorType) .
            $value .
            SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType($operatorType) . "'";
        }

        /**
         * Insert multiple columns into database.
         * Currently it supports only mysql database.
         * Limit write to 500 rows at once
         * @param string $tableName
         * @param array $rowsOfColumnValues
         * @param array $columnNames
         * @throws NotSupportedException
         */
        public static function bulkInsert($tableName, & $rowsOfColumnValues, & $columnNames, $bulkQuantity)
        {
            assert('is_string($tableName)');
            assert('is_array($rowsOfColumnValues)');
            assert('is_array($columnNames)');
            assert('is_int($bulkQuantity)');

            if (RedBeanDatabase::getDatabaseType() != 'mysql')
            {
                throw new NotSupportedException();
            }
            $counter = 0;
            foreach ($rowsOfColumnValues as $row)
            {
                if (count($row) == count($columnNames))
                {
                    if ($counter == 0)
                    {
                        $sql = "INSERT INTO " . self::quoteString($tableName) . "(" . implode(',', $columnNames) . ") VALUES "; // Not Coding Standard
                    }
                    if ($counter == $bulkQuantity)
                    {
                        $quotedRow = array_map(array('DatabaseCompatibilityUtil', 'escape'), $row);
                        $sql .= "('" . implode("','", $quotedRow). "')"; // Not Coding Standard
                        R::exec($sql);
                        $counter = 0;
                    }
                    else
                    {
                        $quotedRow = array_map(array('DatabaseCompatibilityUtil', 'escape'), $row);
                        $sql .= "('" . implode("','", $quotedRow). "'),"; // Not Coding Standard
                        $counter++;
                    }
                }
                else
                {
                    throw new BulkInsertFailedException(
                              Zurmo::t('Core', 'Bulk insert failed. There was a row with an incorrect column quantity.') .
                              ' ' .
                              Zurmo::t('Core', 'Row Counter: {rowNumber} Serialized row data: {serializedRowData}',
                              array('{rowNumber}' => $counter, '{serializedRowData}' => serialize($row))));
                }
            }
            if ($counter > 0)
            {
                $sql = trim($sql, ','); // Not Coding Standard
                R::exec($sql);
            }
        }

        /**
         * Get version number of database
         * @param string $databaseType
         * @param string $databaseHostname
         * @param string $databaseUsername
         * @param string $databasePassword
         * @param string $port
         * @throws NotSupportedException
         */
        public static function getDatabaseVersion($databaseType,
                                                  $databaseHostname,
                                                  $databaseUsername,
                                                  $databasePassword,
                                                  $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $PhpDriverVersion = phpversion('mysql');
                    if ($PhpDriverVersion !== null)
                    {
                        $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                        $result = @mysql_query("SELECT VERSION()");
                        $row    = @mysql_fetch_row($result);
                        if (is_resource($connection))
                        {
                            mysql_close($connection);
                        }
                        if (isset($row[0]))
                        {
                            return $row[0];
                        }
                    }
            }
            return false;
        }

        /**
         * Get database max alowed packet size.
         * @throws NotSupportedException
         */
        public static function getDatabaseMaxAllowedPacketsSizeRb()
        {
            if (RedBeanDatabase::getDatabaseType() != 'mysql')
            {
                throw new NotSupportedException();
            }

            $row = R::getRow("SHOW VARIABLES LIKE 'max_allowed_packet'");

            if (isset($row['Value']))
            {
                return $row['Value'];
            }
            else
            {
                return null;
            }
        }

        /**
        * Get database max alowed packet size.
         * @param string $databaseType
         * @param string $databaseHostname
         * @param string $databaseUsername
         * @param string $databasePassword
         * @param string $databasePort
         * @throws NotSupportedException
         * @return int|string error
         */
        public static function getDatabaseMaxAllowedPacketsSize($databaseType,
                                                                $databaseHostname,
                                                                $databaseUsername,
                                                                $databasePassword,
                                                                $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }

            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'max_allowed_packet'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
         * Get database max_sp_recursion_depth
         * @param string $databaseType
         * @param string $databaseHostname
         * @param string $databaseUsername
         * @param string $databasePassword
         * @param string $databasePort
         * @throws NotSupportedException
         */
        public static function getDatabaseMaxSpRecursionDepth($databaseType,
                                                              $databaseHostname,
                                                              $databaseUsername,
                                                              $databasePassword,
                                                              $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'max_sp_recursion_depth'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
        * Get database thread_stack
        * @param string $databaseType
        * @param string $databaseHostname
        * @param string $databaseUsername
        * @param string $databasePassword
        * @param string $databasePort
        * @throws NotSupportedException
        */
        public static function getDatabaseThreadStackValue($databaseType,
                                                           $databaseHostname,
                                                           $databaseUsername,
                                                           $databasePassword,
                                                           $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'thread_stack'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
        * Get database optimizer_search_depth
        * @param string $databaseType
        * @param string $databaseHostname
        * @param string $databaseUsername
        * @param string $databasePassword
        * @param string $databasePort
        * @throws NotSupportedException
        */
        public static function getDatabaseOptimizerSearchDepthValue($databaseType,
                                                                    $databaseHostname,
                                                                    $databaseUsername,
                                                                    $databasePassword,
                                                                    $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'optimizer_search_depth'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
        * Get database log_bin
        * @param string $databaseType
        * @param string $databaseHostname
        * @param string $databaseUsername
        * @param string $databasePassword
        * @param string $databasePort
        * @throws NotSupportedException
        */
        public static function getDatabaseLogBinValue($databaseType,
                                                      $databaseHostname,
                                                      $databaseUsername,
                                                      $databasePassword,
                                                      $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'log_bin'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
        * Get database log_bin_trust_function_creators
        * @param string $databaseType
        * @param string $databaseHostname
        * @param string $databaseUsername
        * @param string $databasePassword
        * @param string $databasePort
        * @throws NotSupportedException
        */
        public static function getDatabaseLogBinTrustFunctionCreatorsValue($databaseType,
                                                                           $databaseHostname,
                                                                           $databaseUsername,
                                                                           $databasePassword,
                                                                           $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'log_bin_trust_function_creators'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
         * Get database default collation
         * @param string $databaseType
         * @param string $databaseHostname
         * @param string $databaseName
         * @param string $databaseUsername
         * @param string $databasePassword
         * @param string $databasePort
         * @throws NotSupportedException
         * @return string|boolean
         */
        public static function getDatabaseDefaultCollation($databaseType,
                                                           $databaseHostname,
                                                           $databaseName,
                                                           $databaseUsername,
                                                           $databasePassword,
                                                           $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }

            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    @mysql_select_db($databaseName);
                    $result = @mysql_query("SHOW VARIABLES LIKE 'collation_database'");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[1]))
                    {
                        return $row[1];
                    }
            }
            return false;
        }

        /**
         * Check if database is in strict mode
         * @param string $databaseType
         * @param string $databaseHostname
         * @param string $databaseUsername
         * @param string $databasePassword
         * @param string $databasePort
         * @throws NotSupportedException
         * @return boolean
         */
        public static function isDatabaseStrictMode($databaseType,
                                                    $databaseHostname,
                                                    $databaseUsername,
                                                    $databasePassword,
                                                    $databasePort)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            switch ($databaseType)
            {
                case 'mysql':
                    $connection = @mysql_connect($databaseHostname . ':' . $databasePort, $databaseUsername, $databasePassword);
                    $result = @mysql_query("SELECT @@sql_mode;");
                    $row    = @mysql_fetch_row($result);
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    if (isset($row[0]))
                    {
                        if (strstr($row[0], 'STRICT_TRANS_TABLES') !== false)
                        {
                            $isStrict = true;
                        }
                        else
                        {
                            $isStrict = false;
                        }
                        return $isStrict;
                    }
            }
        }

        /**
         * Check if can connect to database
         * @param string $databaseType
         * @param string $host
         * @param string $rootUsername
         * @param string $rootPassword
         * @param string $port
         * @throws NotSupportedException
         * @return true|string $error
         */
        public static function checkDatabaseConnection($databaseType, $host, $rootUsername, $rootPassword, $port)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }

            assert('is_string($host)         && $host != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_int($port)            && $port != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host . ':' . $port, $rootUsername, $rootPassword)) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;
            }
        }

        /**
         * Check if database exist
         * @param string $databaseType
         * @param string $host
         * @param string $rootUsername
         * @param string $rootPassword
         * @param string $port
         * @param string $databaseName
         * @throws NotSupportedException
         * @returns true/false for if the named database exists.
         */
        public static function checkDatabaseExists($databaseType, $host, $rootUsername, $rootPassword, $port,
                                                   $databaseName)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_int($port)            && $port != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host . ':' . $port, $rootUsername, $rootPassword)) === false ||
                    @mysql_select_db($databaseName, $connection)         === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;
            }
        }

        /**
         * Check if database user exist
         * @param string $databaseType
         * @param string $host
         * @param string $rootUsername
         * @param string $rootPassword
         * @param string $port
         * @param string $username
         * @throws NotSupportedException
         * @returns true/false for if the named database user exists.
         */
        public static function checkDatabaseUserExists($databaseType, $host, $rootUsername, $rootPassword, $port, $username)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_int($port)            && $port != ""');
            assert('is_string($username)     && $username     != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result             = true;
                    $query              = "select count(*) from user where Host in ('%', '$host') and User ='$username'";
                    $connection         = @mysql_connect($host . ':' . $port, $rootUsername, $rootPassword);
                    $databaseConnection = @mysql_select_db('mysql', $connection);
                    $queryResult        = @mysql_query($query, $connection);
                    $row                = @mysql_fetch_row($queryResult);
                    if ($connection === false || $databaseConnection === false || $queryResult === false ||
                        $row === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    else
                    {
                        if ($row == null)
                        {
                            $result = array(mysql_errno(), mysql_error());
                        }
                        elseif (is_array($row) && count($row) == 1 && $row[0] == 0)
                        {
                            return false;
                        }
                        else
                        {
                            assert('is_array($row) && count($row) == 1 && $row[0] >= 1');
                            $result = $row[0] == 1;
                        }
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;
            }
        }

        ///////////////////////////////////////////////////////////////////////
        // Methods that modify things.
        // The aim is that when all of the checks above pass
        // these should be expected to succeed.
        ///////////////////////////////////////////////////////////////////////
        /**
         * Creates the named database, dropping it first if it already exists.
         * @param string $databaseType
         * @param string $host
         * @param string $rootUsername
         * @param string $rootPassword
         * @param string $port
         * @param string $databaseName
         * @throws NotSupportedException
         * @return boolean|string error
         */
        public static function createDatabase($databaseType, $host, $rootUsername, $rootPassword, $port, $databaseName)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_int($port)            && $port != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host . ':' . $port, $rootUsername, $rootPassword))                   === false ||
                    @mysql_query("drop   database if exists `$databaseName`", $connection) === false ||
                    @mysql_query("create database `$databaseName` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;", $connection) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;
            }
        }

        /**
         * Creates the named database user, dropping it first if it already exists.
         * Grants the user full access on the given database.
         * @param string $databaseType
         * @param string $host
         * @param string $rootUsername
         * @param string $rootPassword
         * @param string $port
         * @param string $databaseName
         * @param string $username
         * @param string $password
         * @throws NotSupportedException
         * @return boolean|string error
         */
        public static function createDatabaseUser($databaseType, $host, $rootUsername, $rootPassword, $port,
                                                  $databaseName, $username, $password)
        {
            if ($databaseType != 'mysql')
            {
                throw new NotSupportedException();
            }
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_int($port)            && $port != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host . ':' . $port, $rootUsername, $rootPassword))                               === false ||
                    // The === 666 is to execute this command ignoring whether it fails.
                    @mysql_query("drop user `$username`", $connection) === 666                                  ||
                    @mysql_query("grant all on `$databaseName`.* to `$username`",        $connection) === false ||
                    @mysql_query("set password for `$username` = password('$password')", $connection) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;
            }
        }

        public static function getTableRowsCountTotal()
        {
            if (RedBeanDatabase::getDatabaseType() != 'mysql')
            {
                throw new NotSupportedException();
            }
            $databaseName = RedBeanDatabase::getDatabaseNameFromDsnString(Yii::app()->db->connectionString);
            $sql       = "show tables";
            $totalCount = 0;
            $rows       = R::getAll($sql);
            $columnName = 'Tables_in_' . $databaseName;
            foreach ($rows as $row)
            {
                $tableName  = $row[$columnName];
                $tableSql   = "select count(*) count from " . $tableName;
                $row        = R::getRow($tableSql);
                $totalCount = $totalCount + $row['count'];
            }
            return $totalCount;
        }

        /**
         * Get port on which database is running by default, depending on database type
         * @param string $databaseType
         */
        public static function getDatabaseDefaultPort($databaseType = 'mysql')
        {
            if ($databaseType == 'mysql')
            {
                return 3306;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Baackup database schema and stored procedures.
         * @param string $databaseType
         * @param string $host
         * @param string $username
         * @param string $password
         * @param int $port
         * @param string $databaseName
         * @param string $backupFilePath
         * @throws NotSupportedException
         * @return boolean
         */
        public static function backupDatabase($databaseType = 'mysql',
                                            $host,
                                            $username,
                                            $password,
                                            $port,
                                            $databaseName,
                                            $backupFilePath)
        {
            assert('is_string($host)         && $host         != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($backupFilePath) && $backupFilePath != ""');

            if ($databaseType == 'mysql')
            {
                $result = exec("mysqldump --host=$host --user=$username --password=$password --port=$port --routines --add-drop-database $databaseName > $backupFilePath", $output, $returnVal);  // Not Coding Standard

                if ($returnVal !== 0)
                {
                    return false;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function restoreDatabase($databaseType = 'mysql',
                                               $host,
                                               $username,
                                               $password,
                                               $port,
                                               $databaseName,
                                               $restoreFilePath)
        {
            assert('is_string($host)         && $host         != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($restoreFilePath) && $restoreFilePath != ""');

            if ($databaseType == 'mysql')
            {
                $result = exec("mysql --host=$host --user=$username --password=$password --port=$port $databaseName < $restoreFilePath", $output, $returnVal); // Not Coding Standard
                if ($returnVal !== 0)
                {
                    return false;
                }
                else
                {
                    return true;
                }
                return $result;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Map hint type into database valid type
         * @param string $hintType
         * @throws NotSupportedException
         * @return string
         */
        public static function mapHintTypeIntoDatabaseColumnType($hintType, $length = null)
        {
            $databaseColumnType = '';
            if (RedBeanDatabase::getDatabaseType() == 'mysql')
            {
                if (isset($length) && $length > 0 && $length < 255)
                {
                    if ($hintType == 'string')
                    {
                        $databaseColumnType = "VARCHAR({$length})";
                    }
                }
                else
                {
                    switch ($hintType)
                    {
                        case 'blob':
                            $databaseColumnType = "BLOB";
                            break;
                        case 'longblob':
                            $databaseColumnType = "LONGBLOB";
                            break;
                        case 'boolean':
                            $databaseColumnType = "TINYINT(1)";
                            break;
                        case 'date':
                            $databaseColumnType = "DATE";
                            break;
                        case 'datetime':
                            $databaseColumnType = "DATETIME";
                            break;
                        case 'string':
                            $databaseColumnType = "VARCHAR(255)";
                            break;
                        case 'text':
                            $databaseColumnType = "TEXT";
                            break;
                        case 'longtext':
                            $databaseColumnType = "LONGTEXT";
                            break;
                        case 'id':
                            $databaseColumnType = "INT(11) UNSIGNED";
                            break;
                    }
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            if ($databaseColumnType == '')
            {
                throw new NotSupportedException();
            }
            return $databaseColumnType;
        }

        public function getCharLimits()
        {
        }

        public static function makeTimeZoneAdjustmentContent()
        {
            //todo: move into something that is a wrapper since we can't always know which user we should adjust timezone for.
            $timeZoneObject  = new DateTimeZone(Yii::app()->user->userModel->timeZone);
            $offsetInSeconds = $timeZoneObject->getOffset(new DateTime());
            if ($offsetInSeconds > 0)
            {
                $content = ' + ';
            }
            elseif ($offsetInSeconds < 0)
            {
                $content = ' - ';
            }
            else
            {
                return;
            }
            return $content . 'INTERVAL ' . abs($offsetInSeconds) . ' SECOND';
        }
    }
?>
