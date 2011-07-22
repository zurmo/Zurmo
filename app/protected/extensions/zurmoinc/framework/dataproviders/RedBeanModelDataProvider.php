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
     * A data provider that returns models.
     */
    class RedBeanModelDataProvider extends CDataProvider
    {
        private $modelClassName;
        private $sortAttribute;
        private $sortDescending;
        private $searchAttributeData;

        /**
         * @sortAttribute - Currently supports only non-related attributes.
         */
        public function __construct($modelClassName, $sortAttribute = null, $sortDescending = false, array $searchAttributeData = array(), array $config = array())
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$sortAttribute === null || is_string($sortAttribute) && $sortAttribute != ""');
            assert('is_bool($sortDescending)');
            $this->modelClassName               = $modelClassName;
            $this->sortAttribute                = $sortAttribute;
            $this->sortDescending               = $sortDescending;
            $this->searchAttributeData          = $searchAttributeData;
            $this->setId($this->modelClassName);
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
            $sort = new RedBeanSort($this->modelClassName);
            $sort->sortVar = $this->getId().'_sort';
            $this->setSort($sort);
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($this->modelClassName);
            $where = $this->makeWhere($this->modelClassName, $this->searchAttributeData, $joinTablesAdapter);
            $orderBy = null;
            if ($this->sortAttribute !== null)
            {
                $orderBy = self::resolveSortAttributeColumnName($this->modelClassName,
                                                                $joinTablesAdapter, $this->sortAttribute);
                if ($this->sortDescending)
                {
                    $orderBy .= ' desc';
                }
            }
            $modelClassName = $this->modelClassName;
            return $modelClassName::getSubset($joinTablesAdapter, $offset, $limit, $where, $orderBy,
                                              $this->modelClassName, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         *
         */
        public static function resolveSortAttributeColumnName($modelClassName, &$joinTablesAdapter, $sortAttribute)
        {
            assert('$sortAttribute === null || is_string($sortAttribute) && $sortAttribute != ""');
            $model = new $modelClassName(false);
            $sortRelatedAttribute = null;
            if ($model->isRelation($sortAttribute))
            {
                $relationType = $model->getRelationType($sortAttribute);
                //MANY_MANY not supported currently for sorting.
                assert('$relationType != RedBeanModel::MANY_MANY');
                $relationModelClassName = $model->getRelationModelClassName($sortAttribute);
                $sortRelatedAttribute   = self::getSortAttributeName($relationModelClassName);
            }
            return ModelDataProviderUtil::resolveSortAttributeColumnName($modelClassName, $joinTablesAdapter,
                        $sortAttribute, $sortRelatedAttribute);
        }

        /**
         * Each model has a sort attribute that is used to order the models if none is specified.
         */
        protected static function getSortAttributeName($modelClassName)
        {
            $metadata = $modelClassName::getMetadata();
            while (!isset($metadata[$modelClassName]['defaultSortAttribute']))
            {
                $modelClassName = get_parent_class($modelClassName);
                if ($modelClassName == 'RedBeanModel')
                {
                    //This means the sortAttribute value was not found.
                    throw new notImplementedException();
                }
            }
            assert('isset($metadata[$modelClassName]["defaultSortAttribute"])');
            return $metadata[$modelClassName]['defaultSortAttribute'];
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
         * Not for use by applications. Public for unit tests only.
         * Override from RedBeanModelDataProvider to support multiple
         * where clauses for the same attribute and operatorTypes
         * @param metadata - array expected to have clauses and structure elements
         * @param $joinTablesAdapter
         * @see DataProviderMetadataAdapter
         * @return string
         */
        public static function makeWhere($modelClassName, array $metadata, &$joinTablesAdapter)
        {
            return ModelDataProviderUtil::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
        }

        /**
         * See the yii documentation. This function is made public for unit testing.
         */
        public function calculateTotalItemCount()
        {
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($this->modelClassName);
            $where = $this->makeWhere($this->modelClassName, $this->searchAttributeData, $joinTablesAdapter);
            $modelClassName = $this->modelClassName;
            return $modelClassName::getCount($joinTablesAdapter, $where, $this->modelClassName, $joinTablesAdapter->getSelectDistinct());
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
