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
     * Helper class for working with import data tables.
     */
    class ImportDatabaseUtil
    {
        /**
         * Given a file resource, convert the file into a database table based on the table name provided.
         * Assumes the file is a csv.
         * @param string $csvFilePath
         * @param string $tableName
         * @return true on success.
         */
        public static function makeDatabaseTableByFileHandleAndTableName($csvFilePath, $tableName, $delimiter = ',', // Not Coding Standard
                                                                         $enclosure = "'")
        {
            assert('is_string($tableName)');
            assert('$tableName == strtolower($tableName)');
            assert('$delimiter != null && is_string($delimiter)');
            assert('$enclosure != null && is_string($enclosure)');

            //Replace /r/n in file with /n.
            $fileContent = file_get_contents($csvFilePath);
            $fileContent = str_replace("\r\n" ,"\n", trim($fileContent));
            file_put_contents($csvFilePath, $fileContent);

            $fileHandle  = fopen($csvFilePath, 'r');
            assert('gettype($fileHandle) == "resource"');

            $sql = "drop table if exists $tableName";
            R::exec($sql);

            if ($fileHandle !== false)
            {
                $freezeWhenComplete = false;
                if (RedBeanDatabase::isFrozen())
                {
                    RedBeanDatabase::unfreeze();
                    $freezeWhenComplete = true;
                }

                $columns = array();
                $data = fgetcsv($fileHandle, 0, $delimiter, $enclosure);
                if (count($data) > 0 && $data !== false)
                {
                    $newBean = R::dispense($tableName);
                    foreach ($data as $columnId => $value)
                    {
                        $columnName = 'column_' . $columnId;
                        $newBean->{$columnName} = str_repeat(' ', 256);
                        $columns[] = $columnName;
                    }
                    $newBean->status             = null;
                    $newBean->serializedmessages = null;

                    R::store($newBean);
                    R::trash($newBean);
                    R::exec("TRUNCATE TABLE $tableName");

                    DatabaseCompatibilityUtil::loadDataFromFileIntoDatabase($csvFilePath, $tableName, $delimiter,
                                                                            $enclosure, $columns);
                }

                self::optimizeTable($tableName);
                if ($freezeWhenComplete)
                {
                    RedBeanDatabase::freeze();
                }
            }
            else
            {
                return false;
            }
            return true;
        }

        protected static function optimizeTable($tableName)
        {
            $bean         = R::dispense($tableName);
            $bean->status = '2147483647'; //Creates an integer todo: optimize to status SET
            $s            = chr(rand(ord('A'), ord('Z')));
            while (strlen($bean->serializedmessages) < '1024')
            {
                $bean->serializedmessages .= chr(rand(ord('a'), ord('z')));
            }
            R::store($bean);
            R::trash($bean);
        }

        /**
         * Drops a table by the given table name.
         * @param string $tableName
         */
        public static function dropTableByTableName($tableName)
        {
            assert('$tableName == strtolower($tableName)');
            R::exec("drop table if exists $tableName");
        }

        /**
         * Gets the count of how many columns there are in a table minus the initial 'id' column.
         * @param string $tableName
         * @return integer
         */
        public static function getColumnCountByTableName($tableName)
        {
            assert('is_string($tableName)');
            $firstRowData = self::getFirstRowByTableName($tableName);
            return count($firstRowData) - 1;
        }

        /**
         * Get the first row of a table.  if no rows exist, an NoRowsInTableException is thrown.
         * @param string $tableName
         */
        public static function getFirstRowByTableName($tableName)
        {
            assert('is_string($tableName)');
            $sql = 'select * from ' . $tableName;
            try
            {
                $data = R::getRow($sql);
            }
            catch (RedBean_Exception_SQL $e)
            {
                throw new NoRowsInTableException();
            }
            return $data;
        }

        /**
         * Given a table name, count, and offset get an array of beans.
         * @param string $tableName
         * @param integer $count
         * @param integer $offset
         * @return array of RedBean_OODBBean beans.
         */
        public static function getSubset($tableName, $where = null, $count = null, $offset = null)
        {
            assert('is_string($tableName)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$offset  === null || is_integer($count)   && $count   >= 1');
            $sql = 'select id from ' . $tableName;
            if ($where != null)
            {
                $sql .= ' where ' . $where;
            }
            if ($count !== null)
            {
                $sql .= " limit $count";
            }
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            $ids   = R::getCol($sql);
            return R::batch ($tableName, $ids);
        }

        /**
         * Get the row count in a given table.
         * @param string $tableName
         * @return integer
         */
        public static function getCount($tableName, $where = null)
        {
            $sql = 'select count(*) count from ' . $tableName;

            if ($where != null)
            {
                $sql .= ' where ' . $where;
            }
            $count = R::getCell($sql);
            if ($count === null)
            {
                $count = 0;
            }
            return $count;
        }

        /**
         * Update the row in the table with status and message information after the row is attempted or successfully
         * imported.
         * @param string         $tableName
         * @param integer        $id
         * @param integer        $status
         * @param string or null $serializedMessages
         */
        public static function updateRowAfterProcessing($tableName, $id, $status, $serializedMessages)
        {
            assert('is_string($tableName)');
            assert('is_int($id)');
            assert('is_int($status)');
            assert('is_string($serializedMessages) || $serializedMessages == null');

            $bean = R::findOne($tableName, "id = :id", array('id' => $id));
            if ($bean == null)
            {
                throw new NotFoundException();
            }
            $bean->status             = $status;
            $bean->serializedmessages = $serializedMessages;
            R::store($bean);
        }

        /**
         * For the temporary import tables, some of the columns are reserved and not used by any of the import data
         * coming from a csv.  This includes the id, status, and serializedMessages columns.
         * @return array of column names.
         */
        public static function getReservedColumnNames()
        {
            return array('id', 'status', 'serializedmessages');
        }
    }
?>