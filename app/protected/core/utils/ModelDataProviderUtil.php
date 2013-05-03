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
     * A helper class for assisting the data providers in building query parts for fetching data.
     *
     */
    class ModelDataProviderUtil
    {
        /**
         * If the $onTableAliasName is used (not null):
         * Special use of sort attribute resolution. If you are resolving a sort attribute against a relation then
         * the joins must utilize a left join in the case of casting up.  Does not support when the attribute is a
         * relation itself as this expects any relation processing to be done before this is called.
         *
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @return string
         * @throws NotSupportedException
         */
        public static function resolveSortAttributeColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                              $modelAttributeToDataProviderAdapter,
                                                              RedBeanModelJoinTablesQueryAdapter
                                                              $joinTablesAdapter,
                                                              $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder               = new ModelJoinBuilder($modelAttributeToDataProviderAdapter,
                                                          $joinTablesAdapter);
            $tableAliasName        = $builder->resolveJoins($onTableAliasName,
                                                            self::resolveCanUseFromJoins($onTableAliasName));
            $shouldConcatenate     = false;
            if ($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                if ($modelAttributeToDataProviderAdapter->relatedAttributesSortUsesTwoAttributes())
                {
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnNameByPosition(0);
                    $sortColumnsNameString  = self::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnNameByPosition(1);
                    $shouldConcatenate      = true;
                }
                else
                {
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
                }
            }
            else
            {
                if ($modelAttributeToDataProviderAdapter->sortUsesTwoAttributes())
                {
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getColumnNameByPosition(0);
                    $sortColumnsNameString  =  self::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getColumnNameByPosition(1);
                    $shouldConcatenate      = true;
                }
                else
                {
                    $resolvedSortColumnName = $modelAttributeToDataProviderAdapter->getColumnName();
                }
            }
            if ($shouldConcatenate)
            {
                $sortColumnsNameString = DatabaseCompatibilityUtil::concat(
                        array($sortColumnsNameString,
                              self::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName)));
            }
            else
            {
                $sortColumnsNameString = self::resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName);
            }
            return $sortColumnsNameString;
        }

        /**
         * Wraps a string by concat to be used in queries
         * @param string $string
         * @return string
         */
        protected static function resolveConcatenation($string)
        {
            return "CONCAT(" . $string . ")";
        }

        /**
         * @param $onTableAliasName
         * @return bool
         */
        public static function resolveCanUseFromJoins($onTableAliasName)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if ($onTableAliasName != null)
            {
                return false;
            }
            return true;
        }

        /**
         * @param $tableAliasName
         * @param $resolvedSortColumnName
         * @return string
         */
        public static function resolveSortColumnNameString($tableAliasName, $resolvedSortColumnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($resolvedSortColumnName)');
            $sort  = DatabaseCompatibilityUtil::quoteString($tableAliasName);
            $sort .= '.';
            $sort .= DatabaseCompatibilityUtil::quoteString($resolvedSortColumnName);
            return $sort;
        }

        /**
         * If the $onTableAliasName is used (not null):
         * Special use of group by attribute resolution. If you are resolving a group by attribute against a relation then
         * the joins must utilize a left join in the case of casting up.  Does not support when the attribute is a
         * relation itself as this expects any relation processing to be done before this is called.
         *
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @return string
         * @throws NotSupportedException
         */
        public static function resolveGroupByAttributeColumnName(RedBeanModelAttributeToDataProviderAdapter
                                                                 $modelAttributeToDataProviderAdapter,
                                                                 RedBeanModelJoinTablesQueryAdapter
                                                                 $joinTablesAdapter,
                                                                 $onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $builder        = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter);
            $tableAliasName = $builder->resolveJoins($onTableAliasName, self::resolveCanUseFromJoins($onTableAliasName));
            if ($modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $resolvedGroupByColumnName = $modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName();
            }
            else
            {
                $resolvedGroupByColumnName = $modelAttributeToDataProviderAdapter->getColumnName();
            }
            return self::resolveGroupByColumnNameString($tableAliasName, $resolvedGroupByColumnName);
        }

        /**
         * Override from RedBeanModelDataProvider to support multiple
         * where clauses for the same attribute and operatorTypes
         * @param $modelClassName
         * @param array $metadata - array expected to have clauses and structure elements
         * @param $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @return string
         */
        public static function makeWhere($modelClassName, array $metadata, $joinTablesAdapter, $onTableAliasName = null)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if (empty($metadata))
            {
                return;
            }
            $where = array();
            foreach ($metadata['clauses'] as $key => $clauseInformation)
            {
                static::processMetadataClause($modelClassName, $key, $clauseInformation, $where, $joinTablesAdapter, $onTableAliasName);
            }
            if (count($where)> 0)
            {
                return strtr(strtolower($metadata["structure"]), $where);
            }
            return;
        }

        /**
         * @param $tableAliasName
         * @param $resolvedSortColumnName
         * @return string
         */
        protected static function resolveGroupByColumnNameString($tableAliasName, $resolvedSortColumnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($resolvedSortColumnName)');
            $groupBy  = DatabaseCompatibilityUtil::quoteString($tableAliasName);
            $groupBy .= '.';
            $groupBy .= DatabaseCompatibilityUtil::quoteString($resolvedSortColumnName);
            return $groupBy;
        }

        /**
         * @param string $modelClassName
         * @param integer $clausePosition
         * @param array $clauseInformation
         * @param array $where
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @throws NotSupportedException
         */
        protected static function processMetadataClause($modelClassName, $clausePosition, $clauseInformation, & $where,
                                                        & $joinTablesAdapter, $onTableAliasName = null)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_int($clausePosition)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if (isset($clauseInformation['relatedModelData']))
            {
                static::processMetadataContainingRelatedModelDataClause($modelClassName,
                    $clausePosition,
                    $clauseInformation,
                    $where,
                    $joinTablesAdapter,
                    $onTableAliasName);
            }
            elseif (isset($clauseInformation['concatedAttributeNames']))
            {
                if (isset($clauseInformation['relatedAttributeName']) &&
                   $clauseInformation['relatedAttributeName'] != null)
                {
                    throw new NotSupportedException();
                }
                $tableAliasAndColumnNames = self::makeTableAliasAndColumnNamesForNonRelatedConcatedAttributes(
                                            $modelClassName, $clauseInformation['concatedAttributeNames'],
                                            $joinTablesAdapter);
                self::addWherePartByClauseInformationForConcatedAttributes($clauseInformation['operatorType'],
                                            $clauseInformation['value'], $where, $clausePosition,
                                            $tableAliasAndColumnNames);
            }
            else
            {
                $modelAttributeToDataProviderAdapter =  new RedBeanModelAttributeToDataProviderAdapter(
                                                        $modelClassName,
                                                        $clauseInformation['attributeName'],
                                                        ArrayUtil::getArrayValue($clauseInformation, 'relatedAttributeName'));
                $builder = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter, true,
                                                        ArrayUtil::getArrayValue($clauseInformation, 'modifierType'));
                $builder->resolveJoinsAndBuildWhere(    $clauseInformation['operatorType'],
                                                        $clauseInformation['value'], $clausePosition,
                                                        $where, $onTableAliasName,
                                                        static::resolveResolveSubqueryValue($clauseInformation));
            }
        }

        protected static function resolveResolveSubqueryValue(Array $clauseInformation)
        {
            if(null == $resolveAsSubquery = ArrayUtil::getArrayValue($clauseInformation, 'resolveAsSubquery'))
            {
                return false;
            }
            return $resolveAsSubquery;
        }

        /**
         * @param string $modelClassName
         * @param integer $clausePosition
         * @param array $clauseInformation
         * @param array $where
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         */
        protected static function processMetadataContainingRelatedModelDataClause($modelClassName,
                                                                                  $clausePosition,
                                                                                  $clauseInformation,
                                                                                  & $where,
                                                                                  $joinTablesAdapter,
                                                                                  $onTableAliasName = null)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_int($clausePosition)');
            assert('is_array($clauseInformation["relatedModelData"]) && count($clauseInformation["relatedModelData"]) > 0');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                   $modelClassName,
                                                   $clauseInformation['attributeName'],
                                                   $clauseInformation['relatedModelData']['attributeName']);
            $builder                             = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter,
                                                   $joinTablesAdapter, true);
                                                   $builder->resolveJoins($onTableAliasName,
                                                   self::resolveCanUseFromJoins($onTableAliasName));
            $relationModelClassName              = $modelAttributeToDataProviderAdapter->getRelationModelClassName();
            //if there is no more relatedModelData then we know this is the end of the nested information.
            if (isset($clauseInformation['relatedModelData']['relatedModelData']))
            {
                return static::processMetadataClause($relationModelClassName, $clausePosition,
                                                     $clauseInformation['relatedModelData'],
                                                     $where, $joinTablesAdapter, $onTableAliasName);
            }
            //Supporting the use of relatedAttributeName. Alternatively you can use relatedModelData to produce the same results.
            if (isset($clauseInformation['relatedModelData']['relatedAttributeName']))
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                           $relationModelClassName,
                                                           $clauseInformation['relatedModelData']['attributeName'],
                                                           $clauseInformation['relatedModelData']['relatedAttributeName']);
                $builder = new ModelWhereAndJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter, true,
                               ArrayUtil::getArrayValue($clauseInformation['relatedModelData'], 'modifierType'));
            }
            $builder->resolveJoinsAndBuildWhere(
                        $clauseInformation['relatedModelData']['operatorType'],
                        $clauseInformation['relatedModelData']['value'], $clausePosition, $where, $onTableAliasName);
        }

        /**
         * @param string $modelClassName
         * @param array $concatedAttributeNames
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null | string $onTableAliasName
         * @return array
         */
        protected static function makeTableAliasAndColumnNamesForNonRelatedConcatedAttributes( $modelClassName,
                                                                                               $concatedAttributeNames,
                                                                                               $joinTablesAdapter,
                                                                                               $onTableAliasName = null)
        {
            assert('is_string($modelClassName)');
            assert('is_array($concatedAttributeNames) && count($concatedAttributeNames) == 2');
            assert('$joinTablesAdapter instanceof RedBeanModelJoinTablesQueryAdapter');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $tableAliasAndColumnNames = array();
            foreach ($concatedAttributeNames as $attributeName)
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                       $modelClassName, $attributeName);
                $builder                             = new ModelWhereAndJoinBuilder(
                                                       $modelAttributeToDataProviderAdapter, $joinTablesAdapter, true);
                $tableAliasName                      = $builder->resolveJoins($onTableAliasName,
                                                       self::resolveCanUseFromJoins($onTableAliasName));
                $tableAliasAndColumnNames[]          = array($tableAliasName,
                                                       $modelAttributeToDataProviderAdapter->getColumnName());
            }
            return $tableAliasAndColumnNames;
        }

        /**
         * Add a sql string to the where array base on the $operatorType, $value and $tableAliasAndColumnNames concated
         * together.  How the sql string is built depends on if the value is a string or not.
         * @param string $operatorType
         * @param mixed $value
         * @param array $where
         * @param integer $whereKey
         * @param array $tableAliasAndColumnNames
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
                $first            = ModelJoinBuilder::makeColumnNameWithTableAlias(
                                    $tableAliasAndColumnNames[0][0], $tableAliasAndColumnNames[0][1]);
                $second           = ModelJoinBuilder::makeColumnNameWithTableAlias(
                                    $tableAliasAndColumnNames[1][0], $tableAliasAndColumnNames[1][1]);
                $concatedSqlPart  = DatabaseCompatibilityUtil::concat(array($first, '\' \'', $second));
                $where[$whereKey] = "($concatedSqlPart " . // Not Coding Standard
                                    DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . ")";
            }
        }
    }
?>