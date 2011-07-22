<?php
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
    }
?>