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
     * Adapts join table information into query parts for a particular sql query. Automatically determines tables
     * aliases and will provide sql for the from, join, and where query parts for join information.
     */
    class RedBeanModelJoinTablesQueryAdapter
    {
        private $baseFromTableName;

        private $fromTablesAndAliases = array();

        private $leftTablesAndAliases = array();

        private $tableCounts = array();

        /**
         * Set to true if the select query needs to query distinct ids because of a join that produces multiple rows
         * with the same id.
         * @var boolean
         */
        private $selectDistinct = false;

        /**
         * @param @modelClassName - the main 'from part' table is this model's table. This is considered the base table
         */
        public function __construct($modelClassName)
        {
            assert('is_string($modelClassName)');
            $tableName               = RedBeanModel::getTableName($modelClassName);
            $quote                   = DatabaseCompatibilityUtil::getQuote();
            $this->baseFromTableName = $tableName;
            $this->addTableCount($tableName);
        }

        /**
         * Add a joining table by using a combination of an extra from table and a where clause.
         * @param $tableName - table to add in from clause.
         * @param $onTableJoinIdName - The joining id on the baseTable.
         */
        public function addFromTableAndGetAliasName($tableName, $onTableJoinIdName, $onTableAliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($onTableJoinIdName)');
            assert('$onTableAliasName == null || is_string($onTableAliasName)');
            if ($onTableAliasName == null)
            {
                $onTableAliasName = $this->baseFromTableName;
            }
            $tableAliasName = $tableName . $this->getAliasByTableName($tableName);
            $this->fromTablesAndAliases[] = array(
                'tableName'         => $tableName,
                'tableAliasName'    => $tableAliasName,
                'tableJoinIdName'   => 'id',
                'onTableAliasName'  => $onTableAliasName,
                'onTableJoinIdName' => $onTableJoinIdName,
            );
            $this->addTableCount($tableName);
            return $tableAliasName;
        }

        /**
         * Add a joining table by using a left join clause. If the table is already joined using the same
         * onTableAliasName and onTableJoinIdName, then this join will be skipped.
         * @param $tableName - table to add as a left join clause
         * @param $onTableJoinIdName - The joining id on the baseTable.
         */
        public function addLeftTableAndGetAliasName($tableName, $onTableJoinIdName,
                                                    $onTableAliasName = null, $tableJoinIdName = 'id',
                                                    $extraOnQueryPart = null)
        {
            assert('is_string($tableName)');
            assert('is_string($onTableJoinIdName)');
            assert('$onTableAliasName == null || is_string($onTableAliasName)');
            assert('$extraOnQueryPart == null || is_string($extraOnQueryPart)');
            if ($onTableAliasName == null)
            {
                $onTableAliasName = $this->baseFromTableName;
            }
            if (null !== $existingTableAliasName = $this->getAlreadyLeftJoinedTableAliasName(
                                            $tableName, $onTableAliasName, $onTableJoinIdName))
            {
                return $existingTableAliasName;
            }

            $tableAliasName = $tableName . $this->getAliasByTableName($tableName);
            $this->leftTablesAndAliases[] = array(
                'tableName'         => $tableName,
                'tableAliasName'    => $tableAliasName,
                'tableJoinIdName'   => $tableJoinIdName,
                'onTableAliasName'  => $onTableAliasName,
                'onTableJoinIdName' => $onTableJoinIdName,
                'extraOnQueryPart'  => $extraOnQueryPart,
            );
            $this->addTableCount($tableName);
            return $tableAliasName;
        }

        /**
         * Checks if the given tableName, onTableAliasName, and onTableJoinIdName is already left joined.
         * If it is, then it returns the tableAliasName for the existing left join.
         * @return - Existing tableAliasName or null.
         */
        protected function getAlreadyLeftJoinedTableAliasName($tableName, $onTableAliasName, $onTableJoinIdName)
        {
            foreach ($this->leftTablesAndAliases as $information)
            {
                if ( $information['tableName']         == $tableName &&
                    $information['onTableAliasName']  == $onTableAliasName &&
                    $information['onTableJoinIdName'] == $onTableJoinIdName)
                {
                    return $information['tableAliasName'];
                }
            }
            return null;
        }

        /**
         * @return - string SQL from part. This does not include the baseTable.
         */
        public function getJoinFromQueryPart()
        {
            if (count($this->fromTablesAndAliases) > 0)
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                $joinTableStrings = array();
                foreach ($this->fromTablesAndAliases as $information)
                {
                    //tbd if we should add quotes around the alias as well.
                    if ( $information['tableName'] == $information['tableAliasName'])
                    {
                        $joinTableStrings[] = $quote . $information['tableName'] . $quote;
                    }
                    else
                    {
                        $joinTableStrings[] = $quote . $information['tableName'] . $quote . ' ' . $information['tableAliasName'];
                    }
                }
                return implode(', ', $joinTableStrings);
            }
            return null;
        }

        /**
         * @return - string - left, inner, and right join clauses.
         */
        public function getJoinQueryPart()
        {
            if (count($this->leftTablesAndAliases) > 0)
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                $queryPart = null;
                foreach ($this->leftTablesAndAliases as $information)
                {
                    if ($queryPart != null)
                    {
                        $queryPart .= ' ';
                    }
                    if ($information['tableName'] == $information['tableAliasName'])
                    {
                        $queryPart .= 'left join ' . $quote . $information['tableName'] . $quote . ' ';
                    }
                    else
                    {
                        $queryPart .= 'left join ' . $quote . $information['tableName'] . $quote . ' ' . $information['tableAliasName'] . ' ';
                    }
                    $queryPart .= 'on ' . $quote . $information['tableAliasName'] . $quote . '.' . $quote . $information['tableJoinIdName']   . $quote .' = ';
                    $queryPart .= $quote . $information['onTableAliasName']       . $quote . '.' . $quote . $information['onTableJoinIdName'] . $quote;
                    $queryPart .= $information['extraOnQueryPart'];
                }
                return $queryPart . ' ';
            }
            return null;
        }

        /**
         * @return - string - SQL where part that is used by the join clauses.
         */
        public function getJoinWhereQueryPart()
        {
            if (count($this->fromTablesAndAliases) > 0)
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                $queryPart = null;
                foreach ($this->fromTablesAndAliases as $information)
                {
                    if ($queryPart != null)
                    {
                        $queryPart .= ' and ';
                    }
                    $queryPart .= $quote . $information['tableAliasName']   . $quote . '.' . $quote . $information['tableJoinIdName']   . $quote . ' = ';
                    $queryPart .= $quote . $information['onTableAliasName'] . $quote . '.' . $quote . $information['onTableJoinIdName'] . $quote;
                }
                return $queryPart;
            }
            return null;
        }

        /**
         * @return integer count of from tables. Does not include base table
         */
        public function getFromTableJoinCount()
        {
            return count($this->fromTablesAndAliases);
        }

        /**
         * @return integer count of left join tables.
         */
        public function getLeftTableJoinCount()
        {
            return count($this->leftTablesAndAliases);
        }

        /**
         * For testing purposes.
         */
        public function getFromTablesAndAliases()
        {
            return $this->fromTablesAndAliases;
        }

        /**
         * For testing purposes.
         */
        public function getLeftTablesAndAliases()
        {
            return $this->leftTablesAndAliases;
        }

        /**
         * Given a table name, is this table already in the list of tables to be joined in the from part of a query?
         */
        public function isTableInFromTables($tableName)
        {
            assert('is_string($tableName)');
            foreach ($this->fromTablesAndAliases as $information)
            {
                if ($information['tableName'] == $tableName)
                {
                    return true;
                }
            }
            return false;
        }

        public function getAliasByTableName($tableName)
        {
            if (isset($this->tableCounts[$tableName]))
            {
                $alias = $this->tableCounts[$tableName];
            }
            else
            {
                $alias = null;
            }
            return $alias;
        }

        private function addTableCount($tableName)
        {
            if (isset($this->tableCounts[$tableName]))
            {
                $this->tableCounts[$tableName] ++;
            }
            else
            {
                $this->tableCounts[$tableName] = 1;
            }
        }

        public function getSelectDistinct()
        {
            return $this->selectDistinct;
        }

        public function setSelectDistinctToTrue()
        {
            $this->selectDistinct = true;
        }
    }
?>
