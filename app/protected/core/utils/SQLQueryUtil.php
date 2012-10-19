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
     * Helper class to provider SQL queries.
     */
    class SQLQueryUtil
    {
        public static function makeQuery($tableName,
                                         RedBeanModelSelectQueryAdapter     $selectQueryAdapter,
                                         RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                         $offset  = null,
                                         $count   = null,
                                         $where   = null,
                                         $orderBy = null,
                                         $groupBy = null)
        {
            assert('is_string($tableName) && $tableName != ""');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$where   === null || is_string ($where)   && $where   != ""');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            assert('$groupBy === null || is_string ($groupBy) && $groupBy != ""');
            $quote = DatabaseCompatibilityUtil::getQuote();
            $sql   = $selectQueryAdapter->getSelect();
            $sql  .= "from ";
            //Added ( ) around from tables to ensure precedence over joins.
            $joinFromPart   = $joinTablesAdapter->getJoinFromQueryPart();
            if ($joinFromPart !== null)
            {
                $sql .= "(";
                $sql .= "{$quote}$tableName{$quote}";
                $sql .= ", $joinFromPart) ";
            }
            else
            {
                $sql .= "{$quote}$tableName{$quote}";
                $sql .= ' ';
            }
            $sql           .= $joinTablesAdapter->getJoinQueryPart();
            $joinWherePart  = $joinTablesAdapter->getJoinWhereQueryPart();
            if ($where !== null)
            {
                $sql .= "where $where";
                if ($joinWherePart != null)
                {
                    $sql .= " and $joinWherePart";
                }
            }
            elseif ($joinWherePart != null)
            {
                $sql .= " where $joinWherePart";
            }
            if ($groupBy !== null)
            {
                $sql .= " group by $groupBy";
            }
            if ($orderBy !== null)
            {
                $sql .= " order by $orderBy";
            }
            if ($count !== null)
            {
                $sql .= " limit $count";
            }
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            return $sql;
        }
    }
?>