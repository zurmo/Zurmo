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
            $countData  = array();
            $model      = new $modelClassName();
            $columnName = ModelDataProviderUtil::getColumnNameByAttribute($model, $attributeName);
            $tableName  = RedBeanModel::getTableName($modelClassName);
            $where      = '';
            if ($filterByAttributeName != null)
            {
               $filterByColumnName = ModelDataProviderUtil::getColumnNameByAttribute($model, $filterByAttributeName);
               $where = 'where ' . $filterByColumnName . '=' . $filterByAttributeValue;
            }
            $sql  = 'select count(*) count, ' . $columnName . ' attribute ' .
                    'from ' . $tableName . ' '                              .
                    $where . ' '                                            .
                    'group by ' . $columnName . ' ';
            $rows = R::getAll($sql);
            foreach ($rows as $row)
            {
                $countData[$row['attribute']] = $row['count'];

            }
            return $countData;
        }
    }
?>