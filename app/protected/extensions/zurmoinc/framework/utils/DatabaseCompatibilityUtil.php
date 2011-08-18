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
     * Helper functionality for use in RedBeanModels and derived models.
     * These functions cater for specific databases other than MySQL,
     * then by default return results for for MySQL.
     */
    class DatabaseCompatibilityUtil
    {
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
         * Given an operator type and value, SQL is constructed. Example
         * return would be '>= 5'.
         * @return string
         */
        public static function getOperatorAndValueWherePart($operatorType, $value)
        {
            assert('is_string($operatorType)');
            if (!SQLOperatorUtil::isValidOperatorTypeByValue($operatorType, $value))
            {
                throw new NotSupportedException();
            }
            if (is_string($value))
            {
                return SQLOperatorUtil::getOperatorByType($operatorType) .
                " lower('" . SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType($operatorType) .
                $value . SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType($operatorType) . "')";
            }
            elseif (is_array($value) && count($value) > 0)
            {
                return SQLOperatorUtil::resolveOperatorAndValueForOneOf($operatorType, $value);
            }
            elseif ($value !== null)
            {
                return SQLOperatorUtil::getOperatorByType($operatorType) . " " . $value;
            }
        }
    }
?>
