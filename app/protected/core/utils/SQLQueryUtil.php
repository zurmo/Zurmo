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