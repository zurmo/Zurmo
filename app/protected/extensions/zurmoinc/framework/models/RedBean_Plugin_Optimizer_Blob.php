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

    /**
     * A Blob only version of RedBean's existing RedBean_Plugin_Optimizer_Datetime.
     * It may seem ridiculous to derive this from RedBean_Plugin_Optimizer_Datetime,
     * but almost the entirety of the class is common to any kind of plugin optimizer
     * so there is no point in repeating it even though the blob optimizer should not
     * subclass something called datetime optimizer. If at some stage the commonality
     * in RedBean_Plugin_Optimizer_Datetime and RedBean_Plugin_Optimizer_Shrink are
     * refactored into a base class (RedBean_Plugin_Optimizer is already taken) this
     * should take advantage of that.
     */
    class RedBean_Plugin_Optimizer_Blob extends RedBean_Plugin_Optimizer_Datetime
    {
        public function optimize($blobType = 'blob')
        {
            assert('$blobType == "blob" || $blobType == "longblob"');
            try
            {
                $columnNamesToTypes = $this->writer->getColumns($this->table);
                if (array_key_exists($this->column, $columnNamesToTypes))
                {
                    $columnType = $columnNamesToTypes[$this->column];
                    if ($columnType != $blobType)
                    {
                        $this->adapter->exec("alter table {$this->table} change {$this->column} {$this->column} " . $blobType);
                    }
                }
                else
                {
                    $this->adapter->exec("alter table {$this->table} add {$this->column} " . $blobType);
                }
            }
            catch (RedBean_Exception_SQL $e)
            {
                //42S02 - Table does not exist.
                if (!in_array($e->getSQLState(), array('42S02')))
                {
                    throw $e;
                }
                else
                {
                    $this->writer->createTable($this->table);
                    $this->adapter->exec("alter table {$this->table} add {$this->column} " . $blobType);
                }
            }
        }
    }
?>
