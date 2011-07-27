<?php
    /**
     * Helper class for working with import data tables.
     */
    class ImportDatabaseUtil
    {
        public static function makeDatabaseTableByFileHandleAndTableName($fileHandle, $tableName)
        {
            assert('gettype($fileHandle) == "resource"');
            assert('is_string($tableName)');
            assert('$tableName == strtolower($tableName)');
            $freezeWhenComplete = false;
            if(RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freezeWhenComplete = true;
            }
            R::exec("drop table $tableName");
            while (($data = fgetcsv($fileHandle, 0, ',')) !== false)
            {
                $newBean = R::dispense($tableName);
                foreach($data as $columnId => $value)
                {
                    $columnName = 'column_' . $columnId;
                    $newBean->{$columnName} = $value;
                }
                R::store($newBean);
                unset($newBean);
            }
            if($freezeWhenComplete)
            {
                RedBeanDatabase::freeze();
            }
            return true;
        }

        public static function getColumnCountByTableName($tableName)
        {
            assert('is_string($tableName)');
            $firstRowData = self::getFirstRowByTableName($tableName);
            return count($firstRowData) - 1;
        }

        public static function getFirstRowByTableName($tableName)
        {
            assert('is_string($tableName)');
            $sql = 'select * from ' . $tableName;
            $data = R::getRow($sql);
            return $data;
        }

        public static function getRowsByTableNameAndCount($tableName, $count, $offset = null)
        {
            assert('is_string($tableName)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            $sql = 'select * from ' . $tableName . ' limit ' . $count;
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            $data = R::getAll($sql);
            return $data;
        }
    }
?>