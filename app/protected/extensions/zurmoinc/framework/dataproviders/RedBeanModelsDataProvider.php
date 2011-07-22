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
     * A data provider that returns models that are from one or more model classes.
     */
    class RedBeanModelsDataProvider extends CDataProvider
    {
        private $modelClassNamesAndSortAttributes;
        private $sortDescending;
        private $modelClassNamesAndSearchAttributeData;

        /**
         * @param string $id - unique identifier for this data collection.
         * @param array $modelClassNamesAndSortAttributes
         * @param boolean $sortDescending
         * @param array $modelClassNamesAndSearchAttributeData
         * @param array $config
         */
        public function __construct($id, array $modelClassNamesAndSortAttributes = null, $sortDescending = false,
                            array $modelClassNamesAndSearchAttributeData = null, array $config = array())
        {
            assert('is_string($id) && $id != ""');
            assert('$modelClassNamesAndSortAttributes === null || count($modelClassNamesAndSortAttributes) > 0');
            assert('$modelClassNamesAndSearchAttributeData === null || count($modelClassNamesAndSearchAttributeData) > 0');
            assert('is_bool($sortDescending)');
            $this->modelClassNamesAndSortAttributes         = $modelClassNamesAndSortAttributes;
            $this->sortDescending                           = $sortDescending;
            $this->modelClassNamesAndSearchAttributeData    = $modelClassNamesAndSearchAttributeData;
            $this->setId($id);
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
            $sort = new RedBeanSort();
            $sort->sortVar = $this->getId().'_sort';
            $this->setSort($sort);
        }

        /**
         * See the yii documentation.
         */
        protected function fetchData()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $pagination->setItemCount($this->getTotalItemCount());
                $offset = $pagination->getOffset();
                $limit  = $pagination->getLimit();
            }
            else
            {
                $offset = 0;
                $limit  = null;
            }

            if (count($this->modelClassNamesAndSearchAttributeData) == 0)
            {
                return null;
            }
            $unionSql = static::makeUnionSql($this->modelClassNamesAndSearchAttributeData,
                                             $this->modelClassNamesAndSortAttributes,
                                             $this->sortDescending, $offset, $limit);
                                             return $this->makeModelsBySql($unionSql);
        }

       /**
        * Public for testing purposes only.
        */
        public static function makeUnionSql(array $modelClassNamesAndSearchAttributeData,
                                            array $modelClassNamesAndSortAttributes = null, $sortDescending = false,
                                            $offset = null, $limit = null)
        {
            assert('$modelClassNamesAndSortAttributes === null ||
                    count($modelClassNamesAndSortAttributes) == count($modelClassNamesAndSearchAttributeData)');
            assert('is_bool($sortDescending)');
            $sqlStatementsToUnion = array();
            foreach ($modelClassNamesAndSearchAttributeData as $modelClassName => $searchAttributeData)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                $where             = ModelDataProviderUtil::makeWhere(  $modelClassName, $searchAttributeData,
                                                                        $joinTablesAdapter);
                $orderByColumnName = null;
                $tableName         = $modelClassName::getTableName($modelClassName);
                if ($modelClassNamesAndSortAttributes !== null)
                {
                    if (isset($modelClassNamesAndSortAttributes[$modelClassName]))
                    {
                        $orderByColumnName = RedBeanModelDataProvider::resolveSortAttributeColumnName($modelClassName,
                                                 $joinTablesAdapter, $modelClassNamesAndSortAttributes[$modelClassName]);
                    }
                    else
                    {
                        throw new notSupportedException();
                    }
                }
                $quotedExtraSelectColumnNameAndAliases       = array();
                $quotedExtraSelectColumnNameAndAliases["'" . $modelClassName . "'"] = 'modelClassName';
                if ($orderByColumnName != null)
                {
                    $quotedExtraSelectColumnNameAndAliases[$orderByColumnName] = 'orderByColumn';
                }
                $sqlStatementsToUnion[] = $modelClassName::makeSubsetOrCountSqlQuery($tableName,
                                                         $joinTablesAdapter, null, null, $where, null, false,
                                                         $joinTablesAdapter->getSelectDistinct(),
                                                         $quotedExtraSelectColumnNameAndAliases);
            }
            $orderBy = null;
            if ($modelClassNamesAndSortAttributes !== null)
            {
                $orderBy = 'orderByColumn';
                if ($sortDescending)
                {
                    $orderBy .= ' desc';
                }
            }
            return self::makeSubsetUnionSqlQuery($sqlStatementsToUnion, $offset, $limit, $orderBy);
        }

        /**
         * Given a unioned sql statement, make the models for the beans returned.  The modelClassName is a column
         * name that must be in the select part of the sql statement for each unioned select.
         * @param string $sql
         * @return array of models
         */
        protected function makeModelsBySql($sql)
        {
            assert('is_string($sql)');
            $models                  = array();
            $idsAndModelClassNames   = R::getAll($sql);

            foreach ($idsAndModelClassNames as $data)
            {
                $modelClassName = $data['modelClassName'];
                $tableName = $modelClassName::getTableName($modelClassName);
                $bean      = R::load($tableName, $data['id']);
                $models[]  = $modelClassName::makeModel($bean, $modelClassName);
            }
            return $models;
        }

        protected static function makeSubsetUnionSqlQuery($sqlStatementsToUnion, $offset = null, $count = null,
                                                          $orderBy = null)
        {
            assert('is_array($sqlStatementsToUnion) && count($sqlStatementsToUnion) > 0');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            $sql = null;
            foreach ($sqlStatementsToUnion as $sqlToBeUnioned)
            {
                if ($sql != null)
                {
                    $sql .= ' UNION ';
                }
                $sql .= '(' . $sqlToBeUnioned . ')';
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

        /**
         * @return CSort the sorting object. If this is false, it means the sorting is disabled.
         */
        public function getSort()
        {
            if (($sort = parent::getSort()) !== false)
            {
                $sort->modelClass = $this->modelClassName;
            }
            return $sort;
        }

        /**
         * This function is made public for unit testing. Calculates the total for each of the select statements
         * and adds them up.
         * @return integer - total count across select statements.
         */
        public function calculateTotalItemCount()
        {
            $totalCount = 0;
            foreach ($this->modelClassNamesAndSearchAttributeData as $modelClassName => $searchAttributeData)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                $where = ModelDataProviderUtil::makeWhere($modelClassName, $searchAttributeData, $joinTablesAdapter);
                $totalCount = $totalCount + RedBeanModel::getCount($joinTablesAdapter, $where, $modelClassName,
                                                                   $joinTablesAdapter->getSelectDistinct());
            }
            return $totalCount;
        }

        /**
         * See the yii documentation.
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $model)
            {
                $keys[] = $model->id;
            }
            return $keys;
        }
    }
?>
