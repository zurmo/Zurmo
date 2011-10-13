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
     * Adapts select information into query parts for a particular sql query. Automatically determines count,
     * distinct, sums, columns and aliases.
     */
    class RedBeanModelSelectQueryAdapter
    {
        /**
         * Array of select clauses
         * @var array
         */
        private $clauses;

        /**
         * Set to true if the select query needs to be distinct
         * with the same id.
         * @var boolean
         */
        private $distinct = false;

        /**
         * Count of select clauses
         * @var integer
         */
        private $clausesCount = 0;

        private $countClausePresent = false;

        public function __construct($distinct = false)
        {
            $this->distinct = $distinct;
        }

        protected function increaseClausesCountByOne()
        {
            $this->clausesCount++;
        }

        public function isDistinct()
        {
            return $this->distinct;
        }

        public function getClausesCount()
        {
            return $this->clausesCount;
        }

        public function getClauses()
        {
            return $this->clauses;
        }

        public function getSelect()
        {
            if ($this->getClausesCount() == 0)
            {
                throw new NotSupportedException();
            }
            $selectQuery = 'select ';
            if ($this->distinct && !$this->countClausePresent)
            {
                $selectQuery .= 'distinct ';
            }
            foreach ($this->clauses as $clauseCount => $clause)
            {
                $selectQuery .= $clause;
                if ($this->getClausesCount() > 1 && ($clauseCount + 1) < $this->getClausesCount())
                {
                    $selectQuery .= ','; // Not Coding Standard
                }
                $selectQuery .= ' ';
            }
            return $selectQuery;
        }

        public function addCountClause($tableName, $columnName = 'id', $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $distinctPart = null;
            if ($this->distinct)
            {
                $distinctPart = 'distinct ';
            }
            $clause = "count({$distinctPart}{$quote}$tableName{$quote}.{$quote}$columnName{$quote})";
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            $this->clauses[]          = $clause;
            $this->countClausePresent = true;
            $this->increaseClausesCountByOne();
        }

        public function addClause($tableName, $columnName, $aliasName = null)
        {
            assert('is_string($tableName)');
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $quote  = DatabaseCompatibilityUtil::getQuote();
            $clause = "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            $this->clauses[] = $clause;
            $this->increaseClausesCountByOne();
        }

        public function addClauseWithColumnNameOnlyAndNoEnclosure($columnName, $aliasName = null)
        {
            assert('is_string($columnName)');
            assert('is_string($aliasName) || $aliasName == null');
            $clause = "$columnName";
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            $this->clauses[] = $clause;
            $this->increaseClausesCountByOne();
        }

        public function addSummationClause($summationQueryPart, $aliasName = null)
        {
            assert('is_string($summationQueryPart)');
            assert('is_string($aliasName) || $aliasName == null');
            $clause = "sum({$summationQueryPart})";
            if ($aliasName != null)
            {
                $clause .= " $aliasName";
            }
            $this->clauses[] = $clause;
            $this->increaseClausesCountByOne();
        }
    }
?>
