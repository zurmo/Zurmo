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
     * A helper class for assisting the data providers in building query parts for fetching data.
     *
     */
    class ModelDataProviderUtil
    {
        public static function resolveSortAttributeColumnName($modelClassName, &$joinTablesAdapter, $sortAttribute,
                                                              $sortRelatedAttribute = null)
        {
            $model = new $modelClassName(false);
            if ($model->isRelation($sortAttribute))
            {
                if ($sortRelatedAttribute == null)
                {
                    throw new NotSupportedException();
                }
                $attributeModelClassName    = self::resolveAttributeModelClassName($model, $sortAttribute);
                $attributeTableName         = RedBeanModel::getTableName($attributeModelClassName);
                $relationModelClassName     = $model->getRelationModelClassName($sortAttribute);
                //DOES NOT SUPPORT MANY_TO_MANY CURRENTLY
                $relationType               = $model->getRelationType($sortAttribute);
                assert('$relationType != RedBeanModel::MANY_MANY');
                $relationModel              = new $relationModelClassName(false);
                $relationAttributeModelClassName = self::resolveAttributeModelClassName($relationModel, $sortRelatedAttribute);
                $relationAttributeTableName = RedBeanModel::getTableName(self::resolveAttributeModelClassName($relationModel, $sortRelatedAttribute));
                $relationColumnName         = static::getColumnNameByAttribute($relationModel, $sortRelatedAttribute);
                $onTableAliasName           = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $attributeTableName,
                                                        $attributeModelClassName,
                                                        $modelClassName,
                                                        $joinTablesAdapter);
                $tableAliasName             = self::resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                                                $joinTablesAdapter, $relationType,
                                                static::getColumnNameByAttribute($model, $sortAttribute),
                                                $onTableAliasName,
                                                $relationModelClassName, $relationAttributeModelClassName,
                                                $relationAttributeTableName, $relationColumnName);
                $resolvedSortColumnName     = $relationColumnName;
            }
            else
            {
                $sortAttributeModelClassName = self::resolveAttributeModelClassName($model, $sortAttribute);
                $attributeTableName          = RedBeanModel::getTableName($sortAttributeModelClassName);
                $tableAliasName              = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $attributeTableName,
                                                        $sortAttributeModelClassName,
                                                        $modelClassName,
                                                        $joinTablesAdapter);
                $resolvedSortColumnName     = static::getColumnNameByAttribute($model, $sortAttribute);
            }
            $sort  = DatabaseCompatibilityUtil::quoteString($tableAliasName);
            $sort .= '.';
            $sort .= DatabaseCompatibilityUtil::quoteString($resolvedSortColumnName);
            return $sort;
        }

        /**
         * Override from RedBeanModelDataProvider to support multiple
         * where clauses for the same attribute and operatorTypes
         * @param metadata - array expected to have clauses and structure elements
         * @param $joinTablesAdapter
         * @see DataProviderMetadataAdapter
         * @return string
         */
        public static function makeWhere($modelClassName, array $metadata, &$joinTablesAdapter)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            if (empty($metadata))
            {
                return;
            }
            $where = array();
            $model = new $modelClassName(); // Used to interrogate it for its metadata.
            $modelTableName = RedBeanModel::getTableName($modelClassName);
            foreach ($metadata['clauses'] as $key => $clauseInformation)
            {
                if (!isset($clauseInformation['relatedAttributeName']))
                {
                    //example is owner, i think this would appear here.
                    self::buildJoinAndWhereForNonRelatedAttribute( $model, $clauseInformation,
                                                                    $key, $joinTablesAdapter, $where);
                }
                else
                {
                    if ($clauseInformation['relatedAttributeName'] == 'id')
                    {
                        self::buildJoinAndWhereForRelatedId(        $model, $clauseInformation,
                                                                    $key, $joinTablesAdapter, $where);
                    }
                    else
                    {
                        self::buildJoinAndWhereForRelatedAttribute( $model, $clauseInformation,
                                                                    $key, $joinTablesAdapter, $where);
                    }
                }
            }
            if (count($where)> 0)
            {
                return strtr(strtolower($metadata["structure"]), $where);
            }
            return;
        }

        /**
         * Given a non-related attribute on a model, build the join and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected static function buildJoinAndWhereForNonRelatedAttribute( $model,
                                                                    $clauseInformation,
                                                                    $whereKey,
                                                                    &$joinTablesAdapter,
                                                                    &$where)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_array($clauseInformation)');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            $attributeModelClassName = self::resolveAttributeModelClassName($model, $clauseInformation['attributeName']);
            $attributeTableName      = RedBeanModel::getTableName($attributeModelClassName);
            $columnName              = static::getColumnNameByAttribute(
                                            $model, $clauseInformation['attributeName']);
            $tableAliasName = self::resolveShouldAddFromTableAndGetAliasName(   $attributeTableName,
                                                                                $attributeModelClassName,
                                                                                get_class($model),
                                                                                $joinTablesAdapter);
            self::addWherePartByClauseInformation( $clauseInformation['operatorType'],
                                                    $clauseInformation['value'],
                                                    $where, $whereKey, $tableAliasName, $columnName);
        }

        /**
         * Given a related attribute on a model, build the jion and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected static function buildJoinAndWhereForRelatedAttribute(    $model, $clauseInformation, $whereKey,
                                                                            &$joinTablesAdapter, &$where)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_array($clauseInformation)');
            assert('$clauseInformation["relatedAttributeName"] != null');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            //assert('$model->getRelationType($clauseInformation["attributeName"]) != RedBeanModel::MANY_MANY');
            $attributeModelClassName         = self::resolveAttributeModelClassName($model, $clauseInformation['attributeName']);
            $attributeTableName              = RedBeanModel::getTableName($attributeModelClassName);
            $relationModelClassName          = $model->getRelationModelClassName($clauseInformation['attributeName']);
            $relationType                    = $model->getRelationType($clauseInformation['attributeName']);
            $relationModel                   = new $relationModelClassName();
            $relationAttributeModelClassName = self::resolveAttributeModelClassName(
                                                    $relationModel, $clauseInformation['relatedAttributeName']);
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $relationColumnName              = static::getColumnNameByAttribute(
                                                        $relationModel, $clauseInformation['relatedAttributeName']);
            $onTableAliasName                = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $attributeTableName,
                                                        $attributeModelClassName,
                                                        get_class($model),
                                                        $joinTablesAdapter);
            if ($relationType == RedBeanModel::MANY_MANY)
            {
                self::buildJoinAndWhereForManyToManyRelatedAttribute(   $model, $clauseInformation,
                                                                        $whereKey, $joinTablesAdapter, $where);
            }
            else
            {
                $relationAttributeTableAliasName = self::resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                $joinTablesAdapter,
                $relationType,
                static::getColumnNameByAttribute($model, $clauseInformation['attributeName']),
                                                 $onTableAliasName,
                                                 $relationModelClassName, $relationAttributeModelClassName,
                                                 $relationAttributeTableName, $relationColumnName);
                $relationWhere                   = array();
                self::addWherePartByClauseInformation($clauseInformation['operatorType'], $clauseInformation['value'],
                      $relationWhere, 1, $relationAttributeTableAliasName, $relationColumnName);
                $where[$whereKey] = strtr('1', $relationWhere);
            }
        }

        protected static function resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                                & $joinTablesAdapter, $relationType, $attributeColumnName, $onTableAliasName,
                                $relationModelClassName, $relationAttributeModelClassName, $relationAttributeTableName,
                                $relationColumnName)
        {
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_int($relationType)');
            assert('$relationType != RedBeanModel::MANY_MANY');
            assert('is_string($attributeColumnName)');
            assert('is_string($onTableAliasName)');
            assert('is_string($relationModelClassName)');
            assert('is_string($relationAttributeModelClassName)');
            assert('is_string($relationAttributeTableName)');
            assert('is_string($relationColumnName)');
            $relationTableName = RedBeanModel::getTableName($relationModelClassName);
            if ($relationType == RedBeanModel::HAS_MANY  ||
                $relationType == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                $onTableJoinIdName = 'id';
                $tableJoinIdName = $onTableAliasName . '_id';
                //HAS_MANY have the potetential to produce more than one row per model, so we need
                //to signal the query to be distinct.
                if ($relationType == RedBeanModel::HAS_MANY)
                {
                    $joinTablesAdapter->setSelectDistinctToTrue();
                }
            }
            else
            {
                $onTableJoinIdName = $attributeColumnName;
                $tableJoinIdName = 'id';
            }
            $relationTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                        $relationTableName,
                                                                        $onTableJoinIdName,
                                                                        $onTableAliasName,
                                                                        $tableJoinIdName);
            $relationAttributeTableAliasName = $relationTableAliasName;
            //the second left join check being performed is if you
            //are in a contact filtering on related account email as an example.
            if ($relationAttributeModelClassName != $relationModelClassName)
            {
                //Handling special scenario for casted down Person.  Todo: Automatically determine a
                //casted down scenario instead of specifically looking for Person.
                if ($relationAttributeModelClassName == 'Person')
                {
                    $onTableJoinIdName = "{$relationAttributeTableName}_id";
                }
                //An example of this if if you are searching on an account's industry value.  Industry is related from
                //account, but the value is actually on the parent class of OwnedCustomField which is CustomField.
                //Therefore the JoinId is going to be structured like this.
                elseif(get_parent_class($relationModelClassName) == $relationAttributeModelClassName)
                {
                    $onTableJoinIdName = "{$relationAttributeTableName}_id";
                }
                else
                {
                    $onTableJoinIdName = "{$relationColumnName}_{$relationAttributeTableName}_id";
                }
                $relationAttributeTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                            $relationAttributeTableName,
                                                            $onTableJoinIdName,
                                                            $relationTableAliasName);
            }
            return $relationAttributeTableAliasName;
        }

        /**
         * For both non related and related attributes, this method resolves whether a from join is needed.  This occurs
         * for example if a model attribute is castedUp. And that attribute is a relation that needs to be joined in
         * order to search.  Since that attribute is castedUp, the castedUp model needs to be from joined first.  This
         * also applies if the attribute is not a relation and just a member on the castedUp model. In that scenario,
         * the castedUp model also needs to be joined.
         *
         * This methhod assumes if the attribute is not on the base model, that it is casted up not down from it.
         */
        public static function resolveShouldAddFromTableAndGetAliasName( $attributeTableName,
                                                                            $attributeModelClassName,
                                                                            $modelClassName,
                                                                            &$joinTablesAdapter)
        {
            $tableAliasName = $attributeTableName;
            if ($modelClassName == 'User' && $attributeModelClassName == 'Person')
            {
                $personTableName = RedBeanModel::getTableName('Person');
                if (!$joinTablesAdapter->isTableInFromTables('person'))
                {
                    $joinTablesAdapter->addFromTableAndGetAliasName(
                                                            $personTableName,
                                                            "{$personTableName}_id",
                                                            RedBeanModel::getTableName('User'));
                }
            }
            elseif ($attributeModelClassName != $modelClassName)
            {
                while (get_parent_class($modelClassName) != $attributeModelClassName)
                {
                    $castedDownModelClassName = $modelClassName;
                    $modelClassName = get_parent_class($modelClassName);
                    $castedUpAttributeTableName = RedBeanModel::getTableName($modelClassName);
                    if (!$joinTablesAdapter->isTableInFromTables($castedUpAttributeTableName))
                    {
                        $joinTablesAdapter->addFromTableAndGetAliasName(
                                                                $castedUpAttributeTableName,
                                                                "{$castedUpAttributeTableName}_id",
                                                                RedBeanModel::getTableName($castedDownModelClassName));
                    }
                }
                if (!$joinTablesAdapter->isTableInFromTables($attributeTableName))
                {
                    $tableAliasName = $joinTablesAdapter->addFromTableAndGetAliasName(
                                                            $attributeTableName,
                                                            "{$attributeTableName}_id",
                                                            RedBeanModel::getTableName($modelClassName));
                }
            }
            return $tableAliasName;
        }

        /**
         * When the attributeName is 'id', this method determines if we need to join any tables or we can just
         * add where clauses on the column in the base table that corresponds to the id.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         *
         */
        protected static function buildJoinAndWhereForRelatedId(    $model, $clauseInformation, $whereKey,
                                                                    &$joinTablesAdapter, &$where)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_array($clauseInformation)');
            assert('$clauseInformation["relatedAttributeName"] == "id"');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            //Is the relation type HAS_ONE or HAS_MANY_BELONGS_TO
            $relationType = $model->getRelationType($clauseInformation['attributeName']);
            if ($relationType == RedBeanModel::HAS_ONE || $relationType == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                //Is the $clauseInformation['attributeName'] on the same model or a different model?
                $attributeModelClassName = self::resolveAttributeModelClassName($model, $clauseInformation['attributeName']);
                $attributeTableName      = RedBeanModel::getTableName($attributeModelClassName);
                $columnName              = static::getColumnNameByAttribute(
                                                $model, $clauseInformation['attributeName']);
                //If the attributeName is on a different model.
                $tableAliasName = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $attributeTableName,
                                                        $attributeModelClassName,
                                                        get_class($model),
                                                        $joinTablesAdapter);
                self::addWherePartByClauseInformation(  $clauseInformation['operatorType'],
                                                        $clauseInformation['value'],
                                                        $where, $whereKey, $tableAliasName, $columnName);
            }
            elseif ($relationType == RedBeanModel::MANY_MANY)
            {
                self::buildJoinAndWhereForManyToManyRelatedAttribute( $model, $clauseInformation,
                                                            $whereKey, $joinTablesAdapter, $where);
            }
            else
            {
                self::buildJoinAndWhereForRelatedAttribute( $model, $clauseInformation,
                                                            $whereKey, $joinTablesAdapter, $where);
            }
        }

        /**
         * Given a RedBeanModel::MANY_MANY related attribute on a model, build the join and where sql string information.
         * In this scenario with a many-to-many relation, you only need to join the joining table, since this method
         * currently only supports where the relatedAttributeName = 'id'.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected static function buildJoinAndWhereForManyToManyRelatedAttribute(    $model, $clauseInformation, $whereKey,
                                                                            &$joinTablesAdapter, &$where)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_array($clauseInformation)');
            assert('$clauseInformation["relatedAttributeName"] != null');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            assert('$model->getRelationType($clauseInformation["attributeName"]) == RedBeanModel::MANY_MANY');
            $attributeModelClassName         = self::resolveAttributeModelClassName($model, $clauseInformation['attributeName']);
            $attributeTableName              = RedBeanModel::getTableName($attributeModelClassName);
            $relationModelClassName          = $model->getRelationModelClassName($clauseInformation['attributeName']);
            $relationModel                   = new $relationModelClassName();
            $relationTableName               = RedBeanModel::getTableName($relationModelClassName);
            $relationAttributeModelClassName = self::resolveAttributeModelClassName(
                                                    $relationModel, $clauseInformation['relatedAttributeName']);
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $columnName                      = strtolower($clauseInformation['attributeName']);
            $relationColumnName              = static::getColumnNameByAttribute(
                                                        $relationModel, $clauseInformation['relatedAttributeName']);
            $onTableAliasName                = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $attributeTableName,
                                                        $attributeModelClassName,
                                                        get_class($model),
                                                        $joinTablesAdapter);
            $manyToManyTables                = array($relationTableName, $onTableAliasName);
            sort($manyToManyTables);
            $relationJoiningTableAliasName          = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                        implode('_', $manyToManyTables),
                                                        "id",
                                                        $onTableAliasName,
                                                        $attributeTableName . '_id');

            //if this is not the id column, then add an additional left join.
            if ($clauseInformation["relatedAttributeName"] != 'id')
            {
                $joinTablesAdapter->setSelectDistinctToTrue();
                $relationTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                            $relationTableName,
                                                                            $relationTableName . '_id',
                                                                            $relationJoiningTableAliasName,
                                                                            'id');
                $relationAttributeTableAliasName    = $relationTableAliasName;
                $whereClauseRelationColumnNameToUse = $relationColumnName;
            }
            else
            {
                $whereClauseRelationColumnNameToUse = $relationTableName . '_id';
                $relationAttributeTableAliasName = $relationJoiningTableAliasName;
            }

            $relationWhere                   = array();
            self::addWherePartByClauseInformation($clauseInformation['operatorType'], $clauseInformation['value'],
                        $relationWhere, 1, $relationAttributeTableAliasName, $whereClauseRelationColumnNameToUse);
            $where[$whereKey] = strtr('1', $relationWhere);
        }

        /**
         * Add a sql string to the where array base on the $operatorType, $value, $tableAliasName, and $columnName
         * parameters.  How the sql string is built depends on if the value is a string or not.
         * @see RedBeanModelDataProvider::makeWhere
         * @see buildJoinAndWhereForNonRelatedAttribute
         * @see buildJoinAndWhereForRelatedAttribute
         */
        protected static function addWherePartByClauseInformation(  $operatorType, $value, &$where,
                                                                    $whereKey, $tableAliasName, $columnName)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            $quote = DatabaseCompatibilityUtil::getQuote();
            if (is_string($value) || (is_array($value) && count($value) > 0) || $value !== null)
            {
                $where[$whereKey] = "($quote$tableAliasName$quote.$quote$columnName$quote " . // Not Coding Standard
                                DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType,
                                $value) . ")";
            }
        }

        /**
         * This method is needed to interpret when the attributeName is 'id'.  Since id is not an attribute
         * on the model, we manaully check for this and return the appropriate class name.
         */
        protected static function resolveAttributeModelClassName(RedBeanModel $model, $attributeName)
        {
            assert('is_string($attributeName)');
            if ($attributeName == 'id')
            {
                return get_class($model);
            }
            return $model->getAttributeModelClassName($attributeName);
        }

        public static function getColumnNameByAttribute(RedBeanModel $model, $attributeName)
        {
            if ($model->isRelation($attributeName))
            {
                $columnName = RedBeanModel::getForeignKeyName(get_class($model), $attributeName);
            }
            else
            {
                $columnName = strtolower($attributeName);
            }
            return $columnName;
        }
    }
?>