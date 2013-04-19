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
     * Builder for creating where clauses and making sure the appropriate joins are added to facilitate these clauses.
      */
    class ModelWhereAndJoinBuilder extends ModelJoinBuilder
    {
        protected $wherePartColumnModifierType;

        public function __construct(RedBeanModelAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter,
                                    $setDistinct = false,
                                    $wherePartColumnModifierType = null)
        {
            assert('is_string($wherePartColumnModifierType) || $wherePartColumnModifierType == null');
            parent::__construct($modelAttributeToDataProviderAdapter, $joinTablesAdapter, $setDistinct);
            $this->wherePartColumnModifierType = $wherePartColumnModifierType;
        }

        /**
         * @param $operatorType
         * @param $value
         * @param $clausePosition
         * @param $where
         * @param null | string $onTableAliasName
         */
        public function resolveJoinsAndBuildWhere($operatorType, $value, & $clausePosition, & $where,
                                                  $onTableAliasName = null)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if (!$this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $tableAliasName = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                $this->addWherePartByClauseInformation($operatorType, $value, $where, $clausePosition, $tableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->getColumnName());
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelationTypeAHasOneVariant() &&
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttribute() == 'id')
            {
                $this->addWherePartByClauseInformation($operatorType, $value, $where, $clausePosition,
                                                      $this->resolveJoinsForRelatedId($onTableAliasName),
                                                      $this->modelAttributeToDataProviderAdapter->getColumnName());
            }
            else
            {
                $this->buildJoinAndWhereForRelatedAttribute($operatorType, $value, $clausePosition, $where,
                                                            $onTableAliasName);
            }
        }

        /**
         * Given a related attribute on a model, build the join and where sql string information.
         * @param $operatorType
         * @param $value
         * @param $whereKey
         * @param $where
         * @param null | string $onTableAliasName
         */
        protected function buildJoinAndWhereForRelatedAttribute($operatorType, $value, $whereKey, &$where,
                                                                $onTableAliasName = null)
        {
            assert('is_string($operatorType)');
            assert('is_int($whereKey)');
            assert('is_array($where)');
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            $relationWhere                          = array();
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                $relationAttributeTableAliasName    = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                $this->addWherePartByClauseInformation($operatorType, $value, $relationWhere, 1,
                                                      $relationAttributeTableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->resolveManyToManyColumnName());
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelatedAttributeRelation() &&
                    $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationType() == RedBeanModel::HAS_MANY)
            {
                $relationAttributeTableAliasName    = $this->resolveOnlyAttributeJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                $this->buildWhereForRelatedAttributeThatIsItselfAHasManyRelation(
                                                      $relationAttributeTableAliasName,
                                                      $operatorType,
                                                      $value,
                                                      $relationWhere,
                                                      1);
            }
            else
            {
                $relationAttributeTableAliasName    = $this->resolveJoins($onTableAliasName,
                                                      ModelDataProviderUtil::resolveCanUseFromJoins($onTableAliasName));
                $this->addWherePartByClauseInformation($operatorType,
                                                      $value,
                                                      $relationWhere,
                                                      1,
                                                      $relationAttributeTableAliasName,
                                                      $this->modelAttributeToDataProviderAdapter->getRelatedAttributeColumnName());
            }
            $where[$whereKey] = strtr('1', $relationWhere);
        }

        /**
         * Given a related attribute on a model and the related attribute is a has_many relation,
         * build the join and where sql string information.
         * @param $onTableAliasName
         * @param $operatorType
         * @param $value
         * @param $where
         * @param $whereKey
         * @throws NotSupportedException
         */
        protected function buildWhereForRelatedAttributeThatIsItselfAHasManyRelation($onTableAliasName,
                                                                                     $operatorType,
                                                                                     $value,
                                                                                     & $where,
                                                                                     $whereKey
        )
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($operatorType)');
            assert('(is_array($value) && count($value) > 0) || is_string($value)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            $relationAttributeModelClassName = $this->modelAttributeToDataProviderAdapter->getRelatedAttributeRelationModelClassName();
            if ($relationAttributeModelClassName != 'CustomFieldValue' && $operatorType != 'allOf')
            {
                //Until we can add a third parameter to the search adapter metadata, we have to assume we are only doing
                //this for CustomFieldValue searches. Below we have $joinColumnName, since we don't have any other way
                //of ascertaining this information for now.

                //Once we add allOf, need to have an alternative sub-query
                //below that uses if/else logic to compare count against how many possibles. then return 1 or 0.
                throw new NotSupportedException('modelClassName: ' . $relationAttributeModelClassName .
                                                ' operatorType: ' . $operatorType);
            }
            $relationAttributeTableName      = RedBeanModel::getTableName($relationAttributeModelClassName);
            $tableAliasName                  = $relationAttributeTableName;
            $joinColumnName                  = 'value';
            $relationColumnName              = self::resolveForeignKey(RedBeanModel::getTableName(
                                               $this->modelAttributeToDataProviderAdapter->
                                                   getRelatedAttributeModelClassName()));
            $quote                           = DatabaseCompatibilityUtil::getQuote();
            $where[$whereKey]                = "(1 = (select 1 from $quote$relationAttributeTableName$quote $tableAliasName " . // Not Coding Standard
                                               "where $quote$tableAliasName$quote.$quote$relationColumnName$quote = " . // Not Coding Standard
                                               "$quote$onTableAliasName$quote.id " . // Not Coding Standard
                                               "and $quote$tableAliasName$quote.$quote$joinColumnName$quote " . // Not Coding Standard
                                               DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType, $value) . " limit 1))";
        }

        /**
         * @param null | string $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForRelatedId($onTableAliasName = null)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            if ($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() && $onTableAliasName == null)
            {
                return $this->addFromJoinsForAttributeThatIsCastedUp();
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel() &&
                   $onTableAliasName != null)
            {
                return $this->addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName);
            }
            else
            {
                return $this->resolveOnTableAliasName($onTableAliasName);
                //return $this->modelAttributeToDataProviderAdapter->getModelTableName();
            }
        }

        /**
         * Add a sql string to the where array. How the sql string is built depends on if the value is a string or not.
         * @param $operatorType
         * @param $value
         * @param $where
         * @param $whereKey
         * @param $tableAliasName
         * @param $columnName
         */
        protected function addWherePartByClauseInformation(  $operatorType, $value, &$where,
                                                                    $whereKey, $tableAliasName, $columnName)
        {
            assert('is_string($operatorType)');
            assert('is_array($where)');
            assert('is_int($whereKey)');
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            if (is_string($value) || (is_array($value) && count($value) > 0) || $value !== null  ||
                ($value === null && SQLOperatorUtil::doesOperatorTypeAllowNullValues($operatorType)))
            {
                $where[$whereKey] = "(" . self::resolveWhereColumnContentForModifier($tableAliasName, $columnName) . " " .
                                    DatabaseCompatibilityUtil::getOperatorAndValueWherePart($operatorType,
                                    $value) . ")";
            }
        }

        /**
         * @param $tableAliasName
         * @param $columnName
         * @return string
         */
        protected function resolveWhereColumnContentForModifier($tableAliasName, $columnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            $content  = self::makeColumnNameWithTableAlias($tableAliasName, $columnName);
            if ($this->wherePartColumnModifierType != null)
            {
                $content .= $this->resolveTimeZoneAdjustmentForACalculatedDateTimeModifier();
                $content  = strtolower($this->wherePartColumnModifierType) . '(' . $content . ')';
            }
            return $content;
        }

        /**
         * //todo: this override method was needed because otherwise the conversation listview would blow up.  The elseif
         * was added to properly support owned relations since those should always run the addLeftJoin.  I am not sure
         * what else is affected by this method, but reporting can't really use this override since even if the attribute
         * is not related, it doesn't mean we shouldn't do the left join.  Further work needs to be done with this method
         * to test things out.  The starting point would be to test the conversation listview issue which is around the use
         * of owner and then work out from there.
         *
         * ZurmoModelDataProviderUtilTest breaks if we don't have the elseif, but ModulesSearchWithDataProviderTest
         * breaks if we do and it breaks really bad.
         *
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveLeftJoinsForARelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if ($this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
            }
            //elseif ($this->modelAttributeToDataProviderAdapter->isOwnedRelation())
            //{
            //    return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
            //}
            return $onTableAliasName;
        }

        /**
         * @return string
         */
        protected function resolveTimeZoneAdjustmentForACalculatedDateTimeModifier()
        {
            $attributeType = ModelAttributeToMixedTypeUtil::getType(
                             $this->modelAttributeToDataProviderAdapter->getModel(),
                             $this->modelAttributeToDataProviderAdapter->getAttribute());
            if ($attributeType == 'DateTime')
            {
                return DatabaseCompatibilityUtil::makeTimeZoneAdjustmentContent();
            }
        }
    }
?>