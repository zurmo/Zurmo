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
     * A date only version of RedBean's existing RedBean_Plugin_Optimizer_Datetime.
     * Copied from rb.php, it retains its RedBean coding standard.
     */
    class RedBean_Plugin_Optimizer_Date extends RedBean_Plugin_Optimizer_Datetime
    {
        public function optimize() {                                                                                    // Not Coding Standard
            if (!$this->matchesDate($this->value)) return true;                                                         // Not Coding Standard
                                                                                                                        // Not Coding Standard
            $type = $this->writer->scanType($this->value);                                                              // Not Coding Standard
                                                                                                                        // Not Coding Standard
            $fields = $this->writer->getColumns($this->table);                                                          // Not Coding Standard
                                                                                                                        // Not Coding Standard
            if (!in_array($this->column,array_keys($fields))) return false;                                             // Not Coding Standard
                                                                                                                        // Not Coding Standard
            $typeInField = $this->writer->code($fields[$this->column]);                                                 // Not Coding Standard
                                                                                                                        // Not Coding Standard
            if ($typeInField!="date") {                                                                                 // Not Coding Standard
                if ($this->matchesDate($this->value)) {                                                                 // Not Coding Standard
                                                                                                                        // Not Coding Standard
                    $cnt = (int) $this->adapter->getCell("select count(*) as n from {$this->table} where ".             // Not Coding Standard
                              "{$this->column} regexp '[0-9]{4}-[0-1][0-9]-[0-3][0-9]' " .                              // Not Coding Standard
                              "OR {$this->column} IS NULL");                                                            // Not Coding Standard
                    $total = (int) $this->adapter->getCell("SELECT count(*) FROM ".$this->writer->noKW($this->table));  // Not Coding Standard
                                                                                                                        // Not Coding Standard
                    if ($total===$cnt) {                                                                                // Not Coding Standard
                        $this->adapter->exec("ALTER TABLE ".$this->writer->noKW($this->table)." change ".               // Not Coding Standard
                                $this->writer->noKW($this->column)." ".$this->writer->noKW($this->column).              // Not Coding Standard
                                " date ");                                                                              // Not Coding Standard
                    }                                                                                                   // Not Coding Standard
                                                                                                                        // Not Coding Standard
                    return false;                                                                                       // Not Coding Standard
                }                                                                                                       // Not Coding Standard
                                                                                                                        // Not Coding Standard
                return true;                                                                                            // Not Coding Standard
            }                                                                                                           // Not Coding Standard
            else {                                                                                                      // Not Coding Standard
                                                                                                                        // Not Coding Standard
                return false;                                                                                           // Not Coding Standard
            }                                                                                                           // Not Coding Standard
        }

        public function matchesDate($value) {                                                                           // Not Coding Standard
            $pattern = "/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$/";                                                    // Not Coding Standard
            return (boolean) (preg_match($pattern, $value));                                                            // Not Coding Standard
        }
    }
?>
