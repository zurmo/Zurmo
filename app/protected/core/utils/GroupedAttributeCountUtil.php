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
     * Helper class used to query models and group by specific attributes.
     */
    class GroupedAttributeCountUtil
    {
        /**
         * Query a model's table by attributeName to get the count of attribute values.
         * An example usage is if you want to know how many records have a certain contact state for all states.
         * @param $filterByAttributeName  - string identifying attribute that should be filtered on.
         * @param $filterByAttributeValue - string of value to filter the attribute by.
         * @return array of atributeValue / count pairings.
         */
        public static function getCountData(
            $modelClassName,
            $attributeName,
            $filterByAttributeName = null,
            $filterByAttributeValue = null)
        {
            assert('($filterByAttributeName == null && $filterByAttributeValue == null) ||
                        ($filterByAttributeName != null && $filterByAttributeValue != null)');
            $tableName          = RedBeanModel::getTableName($modelClassName);
            $joinTablesAdapter  = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $selectQueryAdapter = new RedBeanModelSelectQueryAdapter();
            if ($modelClassName::isRelation($attributeName) && $modelClassName::getRelationType($attributeName) == RedBeanModel::HAS_MANY)
            {
                assert('$attributeName == "values"'); //until we expand support on this method.
                $relationModelClassName = $modelClassName::getRelationModelClassName($attributeName);
                $attributeTableName     = RedBeanModel::getTableName($relationModelClassName);
                $columnName             = 'value';
                $relationTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                            $attributeTableName,
                                                                            'id',
                                                                            $tableName,
                                                                            $tableName . '_id');
            }
            else
            {
                $attributeTableName = $tableName;
                $columnName         = $modelClassName::getColumnNameByAttribute($attributeName);
            }

            $where = null;
            if ($filterByAttributeName != null)
            {
               $attributeModelClassName    = $modelClassName::resolveAttributeModelClassName($filterByAttributeName);
               $filterByAttributeTableName = RedBeanModel::getTableName($attributeModelClassName);
               $filterByColumnName         = $modelClassName::getColumnNameByAttribute($filterByAttributeName);
               $where = $filterByAttributeTableName . '.' . $filterByColumnName . '=' . $filterByAttributeValue;
               if ($filterByAttributeTableName != $tableName)
               {
                   $joinTablesAdapter->addFromTableAndGetAliasName($filterByAttributeTableName, $filterByAttributeTableName . '_id', $tableName);
               }
            }
            $where                     = $where;
            $selectDistinct            = false;
            $selectQueryAdapter->addClause($attributeTableName, $columnName, 'attribute');
            $selectQueryAdapter->addCountClause($tableName, 'id', 'count');
            $groupBy                   = static::getGroupBySqlPart($attributeTableName, $columnName);
            $sql                       = SQLQueryUtil::makeQuery($tableName, $selectQueryAdapter,
                                                                 $joinTablesAdapter, null, null, $where, null, $groupBy);
            return static::runQueryBySqlAndGetCountData($sql);
        }

        protected static function getGroupBySqlPart($tableName, $columnName)
        {
            assert('$tableName');
            assert('$columnName');
            $quote = DatabaseCompatibilityUtil::getQuote();
            return "{$quote}$tableName{$quote}.{$quote}$columnName{$quote}";
        }

        protected static function runQueryBySqlAndGetCountData($sql)
        {
            assert('is_string($sql)');
            $countData = array();
            $rows = R::getAll($sql);
            foreach ($rows as $row)
            {
                $countData[$row['attribute']] = $row['count'];
            }
            return $countData;
        }
    }
?>