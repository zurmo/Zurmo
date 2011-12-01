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
        public static function resolveSortAttributeColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                              $modelAttributeToDataProviderAdapter,
                                                              RedBeanModelJoinTablesQueryAdapter
                                                              $joinTablesAdapter)
        {
            if($modelAttributeToDataProviderAdapter->isRelation())
            {
                if(!$modelAttributeToDataProviderAdapter->hasRelatedAttribute())
                {
                    throw new NotSupportedException();
                }
                assert('$modelAttributeToDataProviderAdapter->getRelationType() != RedBeanModel::MANY_MANY');
                $onTableAliasName           = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $modelAttributeToDataProviderAdapter,
                                                        $joinTablesAdapter);
                $tableAliasName             = self::resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                                                        $modelAttributeToDataProviderAdapter,
                                                        $joinTablesAdapter,
                                                        $onTableAliasName);
                $resolvedSortColumnName     = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                $tableAliasName             = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $modelAttributeToDataProviderAdapter,
                                                        $joinTablesAdapter);
                $resolvedSortColumnName     = $modelAttributeToDataProviderAdapter->getColumnName();
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
            foreach ($metadata['clauses'] as $key => $clauseInformation)
            {
                if (isset($clauseInformation['concatedAttributeNames']))
                {
                    if($clauseInformation['relatedAttributeName'] != null)
                    {
                        throw new NotSupportedException();
                    }
                    self::buildJoinAndWhereForNonRelatedConcatedAttributes(
                        $modelClassName,
                        $clauseInformation['concatedAttributeNames'],
                        $clauseInformation['operatorType'],
                        $clauseInformation['value'],
                        $key,
                        $joinTablesAdapter,
                        $where);
                }
                elseif (!isset($clauseInformation['relatedAttributeName']))
                {
                    $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                                   $modelClassName,
                                                                   $clauseInformation['attributeName']);
                    self::buildJoinAndWhereForNonRelatedAttribute($modelAttributeToDataProviderAdapter,
                                                                  $clauseInformation['operatorType'],
                                                                  $clauseInformation['value'],
                                                                  $key,
                                                                  $joinTablesAdapter,
                                                                  $where);
                }
                else
                {
                    $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                                   $modelClassName,
                                                                   $clauseInformation['attributeName'],
                                                                   $clauseInformation["relatedAttributeName"]);
                    if ($clauseInformation['relatedAttributeName'] == 'id')
                    {
                        self::buildJoinAndWhereForRelatedId(       $modelAttributeToDataProviderAdapter,
                                                                   $clauseInformation['operatorType'],
                                                                   $clauseInformation['value'],
                                                                   $key,
                                                                   $joinTablesAdapter,
                                                                   $where);
                    }
                    else
                    {
                        self::buildJoinAndWhereForRelatedAttribute($modelAttributeToDataProviderAdapter,
                                                                   $clauseInformation['operatorType'],
                                                                   $clauseInformation['value'],
                                                                   $key,
                                                                   $joinTablesAdapter,
                                                                   $where);
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
        protected static function buildJoinAndWhereForNonRelatedAttribute(RedBeanModelAttributeToDataProviderAdapter
                                                                          $modelAttributeToDataProviderAdapter,
                                                                          $operatorType,
                                                                          $value,
                                                                          $whereKey,
                                                                          $joinTablesAdapter,
                                                                          &$where)
        {
            assert('is_string($operatorType)');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            $tableAliasName = self::resolveShouldAddFromTableAndGetAliasName($modelAttributeToDataProviderAdapter,
                                                                             $joinTablesAdapter);
            self::addWherePartByClauseInformation($operatorType, $value,
                                                  $where, $whereKey, $tableAliasName,
                                                  $modelAttributeToDataProviderAdapter->getColumnName());
        }

        /**
         * Given a non-related array of attributes on a model, build the join and where sql string information. These
         * attributes will be concated together.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected static function buildJoinAndWhereForNonRelatedConcatedAttributes( $modelClassName,
                                                                                    $concatedAttributeNames,
                                                                                    $operatorType,
                                                                                    $value,
                                                                                    $whereKey,
                                                                                    $joinTablesAdapter,
                                                                                    &$where)
        {
            assert('is_string($modelClassName)');
            assert('is_string($operatorType)');
            assert('is_array($concatedAttributeNames) && count($concatedAttributeNames) == 2');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            $tableAliasAndColumnNames = array();

            foreach ($concatedAttributeNames as $attributeName)
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                           get_class($model), $attributeName);
                $tableAliasName                      = self::resolveShouldAddFromTableAndGetAliasName(
                                                           $modelAttributeToDataProviderAdapter, $joinTablesAdapter);
                $tableAliasAndColumnNames[]          = array($tableAliasName,
                                                             $modelAttributeToDataProviderAdapter->getColumnName());
            }
            self::addWherePartByClauseInformationForConcatedAttributes( $operatorType,
                                                    $value,
                                                    $where, $whereKey, $tableAliasAndColumnNames);
        }

        /**
         * Given a related attribute on a model, build the jion and where sql string information.
         * @see RedBeanModelDataProvider::makeWhere
         * @see addWherePartByClauseInformation
         */
        protected static function buildJoinAndWhereForRelatedAttribute(RedBeanModelAttributeToDataProviderAdapter
                                                                       $modelAttributeToDataProviderAdapter,
                                                                       $operatorType, $value, $whereKey,
                                                                       $joinTablesAdapter, &$where)
        {
            assert('is_string($operatorType)');
            assert('$modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            $onTableAliasName = self::resolveShouldAddFromTableAndGetAliasName($modelAttributeToDataProviderAdapter,
                                                                               $joinTablesAdapter);
            if ($modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                self::buildJoinAndWhereForManyToManyRelatedAttribute($modelAttributeToDataProviderAdapter,
                                                                     $operatorType,
                                                                     $value,
                                                                     $whereKey,
                                                                     $joinTablesAdapter,
                                                                     $where);
            }
            else
            {
                $relationAttributeTableAliasName     = self::resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                                                                $modelAttributeToDataProviderAdapter,
                                                                $joinTablesAdapter,
                                                                $onTableAliasName);
                $relationWhere = array();
                if($modelAttributeToDataProviderAdapter->isRelatedAttributeRelation() &&
                   $modelAttributeToDataProviderAdapter->getRelatedAttributeRelationType() == RedBeanModel::HAS_MANY)
                {
                   static::
                   buildWhereForRelatedAttributeThatIsItselfAHasManyRelation($modelAttributeToDataProviderAdapter,
                                                                             $joinTablesAdapter,
                                                                             $relationAttributeTableAliasName,
                                                                             $operatorType,
                                                                             $value,
                                                                             $relationWhere,
                                                                             1);
                }
                else
                {
                    self::addWherePartByClauseInformation($operatorType,
                                                          $value,
                                                          $relationWhere,
                                                          1,
                                                          $relationAttributeTableAliasName,
                                                          $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName());
                }
                $where[$whereKey] = strtr('1', $relationWhere);
            }
        }

        protected static function buildWhereForRelatedAttributeThatIsItselfAHasManyRelation(RedBeanModelAttributeToDataProviderAdapter
                                                                                            $modelAttributeToDataProviderAdapter,
                                                                                            $joinTablesAdapter,
                                                                                            $relationAttributeTableAliasName,
                                                                                            $operatorType,
                                                                                            $value,
                                                                                            & $where,
                                                                                            $whereKey
                                                                                            )
        {
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_string($relationAttributeTableAliasName)');
            assert('is_string($operatorType)');
            assert('is_array($value) && count($value) > 0');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            $relationAttributeName           = $modelAttributeToDataProviderAdapter->getRelatedAttribute();
            $relationAttributeModelClassName = $modelAttributeToDataProviderAdapter-> getRelatedAttributeRelationModelClassName();
            if($relationAttributeModelClassName != 'CustomFieldValue')
            {
                //Until we can add a third parameter to the search adapter metadata, we have to assume we are only doing
                //this for CustomFieldValue searches. Below we have $joinColumnName, since we don't have any other way
                //of ascertaining this information for now.
                throw new NotSupportedException();
            }
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $tableAliasName                  = $relationAttributeTableName;
            $joinColumnName                  = 'value';
            $relationColumnName              = RedBeanModel::getTableName($modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName()) . "_id";
            $quote                           = DatabaseCompatibilityUtil::getQuote();
            $where[$whereKey]   = "(1 = (select 1 from $quote$relationAttributeTableName$quote $tableAliasName " . // Not Coding Standard
                                  "where $quote$tableAliasName$quote.$quote$relationColumnName$quote = " . // Not Coding Standard
                                  "$quote$relationAttributeTableAliasName$quote.id " . // Not Coding Standard
                                  "and $quote$tableAliasName$quote.$quote$joinColumnName$quote " . // Not Coding Standard
                                  DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . "))";
        }

        protected static function resolveJoinsForRelatedAttributeAndGetRelationAttributeTableAliasName(
                                  RedBeanModelAttributeToDataProviderAdapter
                                  $modelAttributeToDataProviderAdapter,
                                  RedBeanModelJoinTablesQueryAdapter
                                  $joinTablesAdapter,
                                  $onTableAliasName)
        {
            assert('$modelAttributeToDataProviderAdapter->getRelationType() != RedBeanModel::MANY_MANY');
            assert('is_string($onTableAliasName)');
            if ($modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY  ||
                $modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                $onTableJoinIdName  = 'id';
                $tableJoinIdName    = $onTableAliasName . '_id';
                //HAS_MANY have the potential to produce more than one row per model, so we need
                //to signal the query to be distinct.
                if ($modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY)
                {
                    $joinTablesAdapter->setSelectDistinctToTrue();
                }
            }
            else
            {
                $onTableJoinIdName  = $modelAttributeToDataProviderAdapter->getColumnName();
                $tableJoinIdName    = 'id';
            }
            $relationTableAliasName          = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                               $modelAttributeToDataProviderAdapter->getRelationTableName(),
                                               $onTableJoinIdName,
                                               $onTableAliasName,
                                               $tableJoinIdName);
            $relationAttributeTableAliasName = $relationTableAliasName;
            //the second left join check being performed is if you
            //are in a contact filtering on related account email as an example.
            if ($modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName() !=
                $modelAttributeToDataProviderAdapter->getRelationModelClassName())
            {
                $relationAttributeTableName  = $modelAttributeToDataProviderAdapter->getRelatedAttributeTableName();
                //Handling special scenario for casted down Person.  Todo: Automatically determine a
                //casted down scenario instead of specifically looking for Person.
                if ($modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName() == 'Person')
                {
                    $onTableJoinIdName = "{$relationAttributeTableName}_id";
                }
                //An example of this if if you are searching on an account's industry value.  Industry is related from
                //account, but the value is actually on the parent class of OwnedCustomField which is CustomField.
                //Therefore the JoinId is going to be structured like this.
                elseif (get_parent_class($modelAttributeToDataProviderAdapter->getRelationModelClassName()) ==
                        $modelAttributeToDataProviderAdapter->getRelatedAttributeModelClassName())
                {
                    $onTableJoinIdName = "{$relationAttributeTableName}_id";
                }
                else
                {
                    $onTableJoinIdName = "{$modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName()}" .
                                         "_{$relationAttributeTableName}_id";
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
        public static function resolveShouldAddFromTableAndGetAliasName(RedBeanModelAttributeToDataProviderAdapter
                                                                        $modelAttributeToDataProviderAdapter,
                                                                        RedBeanModelJoinTablesQueryAdapter
                                                                        $joinTablesAdapter)
        {
            $attributeTableName = $modelAttributeToDataProviderAdapter->getAttributeTableName();
            $tableAliasName     = $attributeTableName;
            if ($modelAttributeToDataProviderAdapter->getModelClassName() == 'User' &&
                $modelAttributeToDataProviderAdapter->getAttributeModelClassName() == 'Person')
            {
                $modelTableName      = $modelAttributeToDataProviderAdapter->getModelTableName();
                if (!$joinTablesAdapter->isTableInFromTables('person'))
                {
                    $personTableName = $attributeTableName;

                    $joinTablesAdapter->addFromTableAndGetAliasName($personTableName, "{$personTableName}_id",
                                                                    $modelTableName);
                }
            }
            elseif ($modelAttributeToDataProviderAdapter->getAttributeModelClassName() !=
                    $modelAttributeToDataProviderAdapter->getModelClassName())
            {
                $modelClassName = $modelAttributeToDataProviderAdapter->getModelClassName();
                while (get_parent_class($modelClassName) !=
                       $modelAttributeToDataProviderAdapter->getAttributeModelClassName())
                {
                    $castedDownModelClassName   = $modelClassName;
                    $modelClassName             = get_parent_class($modelClassName);
                    $castedUpAttributeTableName = $modelClassName::getTableName($modelClassName);
                    if (!$joinTablesAdapter->isTableInFromTables($castedUpAttributeTableName))
                    {
                        $joinTablesAdapter->addFromTableAndGetAliasName(
                                                                $castedUpAttributeTableName,
                                                                "{$castedUpAttributeTableName}_id",
                                                                $castedDownModelClassName::getTableName($castedDownModelClassName));
                    }
                }
                if (!$joinTablesAdapter->isTableInFromTables($attributeTableName))
                {
                    $tableAliasName             = $joinTablesAdapter->addFromTableAndGetAliasName(
                                                  $attributeTableName,
                                                  "{$attributeTableName}_id",
                                                  $modelClassName::getTableName($modelClassName));
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
        protected static function buildJoinAndWhereForRelatedId(RedBeanModelAttributeToDataProviderAdapter
                                                                $modelAttributeToDataProviderAdapter,
                                                                $operatorType,
                                                                $value,
                                                                $whereKey,
                                                                $joinTablesAdapter,
                                                                &$where)
        {
            assert('is_string($operatorType)');
            assert('$modelAttributeToDataProviderAdapter->getRelatedAttribute() == "id"');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            //Is the relation type HAS_ONE or HAS_MANY_BELONGS_TO
            if ($modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_ONE ||
                $modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY_BELONGS_TO)
            {
                $tableAliasName = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $modelAttributeToDataProviderAdapter,
                                                        $joinTablesAdapter);
                self::addWherePartByClauseInformation(  $operatorType,
                                                        $value,
                                                        $where, $whereKey, $tableAliasName,
                                                        $modelAttributeToDataProviderAdapter->getColumnName());
            }
            elseif ($modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                self::buildJoinAndWhereForManyToManyRelatedAttribute( $modelAttributeToDataProviderAdapter, $operatorType, $value,
                                                            $whereKey, $joinTablesAdapter, $where);
            }
            else
            {
                self::buildJoinAndWhereForRelatedAttribute( $modelAttributeToDataProviderAdapter, $operatorType, $value,
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
        protected static function buildJoinAndWhereForManyToManyRelatedAttribute(RedBeanModelAttributeToDataProviderAdapter
                                                                                 $modelAttributeToDataProviderAdapter,
                                                                                 $operatorType,
                                                                                 $value,
                                                                                 $whereKey,
                                                                                 $joinTablesAdapter,
                                                                                 &$where)
        {
            assert('is_string($operatorType)');
            assert('$modelAttributeToDataProviderAdapter->getRelatedAttribute() != null');
            assert('is_int($whereKey)');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_array($where)');
            assert('$modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY');
            $relationTableName               = $modelAttributeToDataProviderAdapter->getRelationTableName();
            $onTableAliasName                = self::resolveShouldAddFromTableAndGetAliasName(
                                                        $modelAttributeToDataProviderAdapter,
                                                        $joinTablesAdapter);
            $manyToManyTables                = array($relationTableName, $onTableAliasName);
            sort($manyToManyTables);
            $relationJoiningTableAliasName   = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                               implode('_', $manyToManyTables),
                                               "id",
                                               $onTableAliasName,
                                               $modelAttributeToDataProviderAdapter->getAttributeTableName() . '_id');
            //if this is not the id column, then add an additional left join.
            if ($modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                $joinTablesAdapter->setSelectDistinctToTrue();
                $relationTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                            $relationTableName,
                                                                            $relationTableName . '_id',
                                                                            $relationJoiningTableAliasName,
                                                                            'id');
                $relationAttributeTableAliasName    = $relationTableAliasName;
                $whereClauseRelationColumnNameToUse = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                $whereClauseRelationColumnNameToUse = $relationTableName . '_id';
                $relationAttributeTableAliasName = $relationJoiningTableAliasName;
            }

            $relationWhere                   = array();
            self::addWherePartByClauseInformation($operatorType, $value,
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
         * Add a sql string to the where array base on the $operatorType, $value and $tableAliasAndColumnNames concated
         * together.  How the sql string is built depends on if the value is a string or not.
         * @see RedBeanModelDataProvider::makeWhere
         * @see buildJoinAndWhereForNonRelatedAttribute
         * @see buildJoinAndWhereForRelatedAttribute
         */
        protected static function addWherePartByClauseInformationForConcatedAttributes($operatorType, $value, &$where,
                                                                    $whereKey, $tableAliasAndColumnNames)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_array($tableAliasAndColumnNames) && count($tableAliasAndColumnNames) == 2');
            $quote = DatabaseCompatibilityUtil::getQuote();
            if (is_string($value) || (is_array($value) && count($value) > 0) || $value !== null)
            {
                $first  = $quote . $tableAliasAndColumnNames[0][0] . $quote . '.' . $quote .
                          $tableAliasAndColumnNames[0][1] . $quote;
                $second = $quote . $tableAliasAndColumnNames[1][0] . $quote . '.' . $quote .
                          $tableAliasAndColumnNames[1][1] . $quote;
                $concatedSqlPart = DatabaseCompatibilityUtil::concat(array($first, '\' \'', $second));

                $where[$whereKey] = "($concatedSqlPart " . // Not Coding Standard
                                    DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . ")";
            }
        }
    }
?>