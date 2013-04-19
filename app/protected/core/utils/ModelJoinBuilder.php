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
     * Base Builder for creating joins.
     */
    class ModelJoinBuilder
    {
        /**
         * @var RedBeanModelAttributeToDataProviderAdapter
         */
        protected $modelAttributeToDataProviderAdapter;

        /**
         * @var RedBeanModelJoinTablesQueryAdapter
         */
        protected $joinTablesAdapter;

        /**
         * @var bool
         */
        protected $setDistinct;

        /**
         * @var string
         */
        protected $resolvedOnTableAliasName;

        /**
         * This is set during resolveJoins as the tableAliasName for the base model class.  This can then be accessed
         * outside this class for querying purposes.
         * Base model class is represented by $modelAttributeToDataProviderAdapter->getModelClassName();
         * @var string
         */
        protected $tableAliasNameForBaseModel;

        /**
         * This is set during resolveJoins as the tableAliasName for the related model class.  This can then be accessed
         * outside this class for querying purposes.
         * Related model class is represented by $modelAttributeToDataProviderAdapter->getRelatedModelClassName();
         * @var string
         */
        protected $tableAliasNameForRelatedModel;

        /**
         * @param string $tableAliasName
         * @param string $columnName
         * @return string
         */
        public static function makeColumnNameWithTableAlias($tableAliasName, $columnName)
        {
            assert('is_string($tableAliasName)');
            assert('is_string($columnName)');
            $quote = DatabaseCompatibilityUtil::getQuote();
            return $quote . $tableAliasName . $quote . '.' . $quote . $columnName . $quote;
        }

        /**
         * @param string $idName
         * @return string
         */
        protected static function resolveForeignKey($idName)
        {
            assert('is_string($idName)');
            return $idName . '_id';
        }

        /**
         * @param RedBeanModelAttributeToDataProviderAdapter $modelAttributeToDataProviderAdapter
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param boolean $setDistinct
         */
        public function __construct(RedBeanModelAttributeToDataProviderAdapter
                                    $modelAttributeToDataProviderAdapter,
                                    RedBeanModelJoinTablesQueryAdapter
                                    $joinTablesAdapter,
                                    $setDistinct = false)
        {
            $this->modelAttributeToDataProviderAdapter = $modelAttributeToDataProviderAdapter;
            $this->joinTablesAdapter                   = $joinTablesAdapter;
            $this->setDistinct                         = $setDistinct;
        }

        /**
         * @return string
         */
        public function getTableAliasNameForBaseModel()
        {
            return $this->tableAliasNameForBaseModel;
        }

        /**
         * @return string
         * @throws NotSupportedException
         */
        public function getTableAliasNameForRelatedModel()
        {
            if (!$this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                throw new NotSupportedException();
            }
            return $this->tableAliasNameForRelatedModel;
        }

        /**
         * @param null $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        public function resolveJoins($onTableAliasName = null, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            assert('is_bool($canUseFromJoins)');
            $onTableAliasName                 = $this->resolveOnTableAliasName($onTableAliasName);
            $this->tableAliasNameForBaseModel = $onTableAliasName;
            $onTableAliasName                 = $this->resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins);
            $this->resolvedOnTableAliasName   = $onTableAliasName;
            if ($this->modelAttributeToDataProviderAdapter->hasRelatedAttribute())
            {
                $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                       $this->modelAttributeToDataProviderAdapter->
                                                       getRelationModelClassNameThatCanHaveATable(),
                                                       $this->modelAttributeToDataProviderAdapter->
                                                       getRelatedAttribute());
                $builderClassName                    = get_class($this);
                $builder                             = new $builderClassName($modelAttributeToDataProviderAdapter,
                                                       $this->joinTablesAdapter);
                $this->tableAliasNameForRelatedModel = $onTableAliasName;
                $onTableAliasName                    = $builder->resolveJoinsForAttribute($onTableAliasName, false);
            }
            return $onTableAliasName;
        }

        /**
         * @param null $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        public function resolveOnlyAttributeJoins($onTableAliasName = null, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName) || $onTableAliasName == null');
            assert('is_bool($canUseFromJoins)');
            $onTableAliasName = $this->resolveOnTableAliasName($onTableAliasName);
            $onTableAliasName = $this->resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins);
            $this->resolvedOnTableAliasName = $onTableAliasName;
            return $onTableAliasName;
        }

        /**
         * @param null $onTableAliasName
         * @return null | string
         */
        public function resolveOnTableAliasName($onTableAliasName = null)
        {
            if ($onTableAliasName == null)
            {
                $onTableAliasName = $this->resolveOnTableAliasNameForDerivedRelationViaCastedUpModel();
            }
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForAttribute($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if ($this->modelAttributeToDataProviderAdapter->isAttributeDerivedRelationViaCastedUpModel())
            {
                return $this->resolveJoinsForDerivedRelationViaCastedUpModel($onTableAliasName, $canUseFromJoins);
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isInferredRelation())
            {
                return $this->resolveJoinsForInferredRelation($onTableAliasName, $canUseFromJoins);
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isAttributeOnDifferentModel())
            {
                return $this->resolveJoinsForAttributeOnDifferentModel($onTableAliasName, $canUseFromJoins);
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelation())
            {
                return $this->resolveJoinsForAttributeOnSameModelThatIsARelation($onTableAliasName);
            }
            else
            {
                return $this->resolveJoinsForAttributeOnSameModelThatIsNotARelation($onTableAliasName);
            }
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForDerivedRelationViaCastedUpModel($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            //First cast up
            $onTableAliasName        = $this->resolveJoinsForDerivedRelationViaCastedUpModelThatIsCastedUp(
                                       $onTableAliasName, $canUseFromJoins);
            //Second build relation across to the opposing model
            $onTableAliasName        = $this->resolveJoinsForDerivedRelationViaCastedUpModelThatIsManyToMany(
                                       $onTableAliasName);
            //Third cast down if necessary
            if ($this->modelAttributeToDataProviderAdapter->isDerivedRelationViaCastedUpModelDifferentThanOpposingModelClassName())
            {
                $opposingRelationModelClassName  = $this->modelAttributeToDataProviderAdapter->
                                                   getOpposingRelationModelClassName();
                $derivedRelationModelClassName   = $this->modelAttributeToDataProviderAdapter->
                                                   getDerivedRelationViaCastedUpModelClassName();
                $onTableAliasName                = $this->resolveAndProcessLeftJoinsForAttributeThatIsCastedDownOrUp(
                                                   $opposingRelationModelClassName,
                                                   $derivedRelationModelClassName, $onTableAliasName);
            }
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForDerivedRelationViaCastedUpModelThatIsCastedUp($onTableAliasName, $canUseFromJoins = true)
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->
                                       getCastedUpModelClassNameForDerivedRelation();
            if ($canUseFromJoins)
            {
                return $this->processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName);
            }
            else
            {
                return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
            }
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForDerivedRelationViaCastedUpModelThatIsManyToMany($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $opposingRelationModelClassName  = $this->modelAttributeToDataProviderAdapter->getOpposingRelationModelClassName();
            $opposingRelationTableName       = $this->modelAttributeToDataProviderAdapter->getOpposingRelationTableName();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $this->modelAttributeToDataProviderAdapter->getManyToManyTableNameForDerivedRelationViaCastedUpModel(),
                "id",
                $onTableAliasName,
                self::resolveForeignKey($opposingRelationTableName));
            $onTableAliasName                = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                               $opposingRelationTableName,
                                               self::resolveForeignKey($opposingRelationModelClassName),
                                               $relationJoiningTableAliasName,
                                               'id');
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         * @throws NotImplementedException
         */
        protected function resolveJoinsForInferredRelation($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            //First cast up
            $onTableAliasName        = $this->resolveJoinsForInferredRelationThatIsCastedUp(
                                       $onTableAliasName, $canUseFromJoins);
            //Second build relation across to the opposing model
            $onTableAliasName        = $this->resolveJoinsForForARelationAttributeThatIsManyToMany($onTableAliasName);
            //Casting down should always be necessary since that is the whole point of using a referred relation
            $opposingRelationModelClassName  = $this->modelAttributeToDataProviderAdapter->getRelationModelClassName();
            if ($opposingRelationModelClassName != 'Item')
            {
                throw new NotImplementedException();
            }
            $inferredRelationModelClassName = $this->modelAttributeToDataProviderAdapter->getInferredRelationModelClassName();
            $onTableAliasName               = $this->resolveAndProcessLeftJoinsForAttributeThatIsCastedDownOrUp(
                                              $opposingRelationModelClassName,
                                              $inferredRelationModelClassName, $onTableAliasName);
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForInferredRelationThatIsCastedUp($onTableAliasName, $canUseFromJoins = true)
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName();
            if ($modelClassName == $attributeModelClassName)
            {
                return $onTableAliasName;
            }
            if ($canUseFromJoins)
            {
                return $this->processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName);
            }
            else
            {
                return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
            }
        }

        /**
         * @param $attributeModelClassName
         * @return string
         */
        protected function resolveAttributeModelClassNameWithCastingHintForCastingDown($attributeModelClassName)
        {
            assert('is_string($attributeModelClassName)');
            if ($this->modelAttributeToDataProviderAdapter->getCastingHintModelClassNameForAttribute() != null)
            {
                return $this->modelAttributeToDataProviderAdapter->getCastingHintModelClassNameForAttribute();
            }
            return $attributeModelClassName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForAttributeOnDifferentModel($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if ($this->modelAttributeToDataProviderAdapter->isRelation())
            {
                return $this->resolveJoinsForAttributeOnDifferentModelThatIsARelation($onTableAliasName, $canUseFromJoins);
            }
            else
            {
                return $this->resolveJoinsForAttributeOnDifferentModelThatIsNotARelation($onTableAliasName,
                                                                                         $canUseFromJoins);
            }
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForAttributeOnSameModelThatIsARelation($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            return $this->resolveLeftJoinsForARelationAttribute($onTableAliasName);
        }

        /**
         * @param $onTableAliasName
         * @return mixed
         */
        protected function resolveJoinsForAttributeOnSameModelThatIsNotARelation($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function resolveJoinsForAttributeOnDifferentModelThatIsARelation($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if ($canUseFromJoins)
            {
                $onTableAliasName = $this->addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName);
            }
            else
            {
                $onTableAliasName = $this->addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName);
            }
            return $this->resolveLeftJoinsForARelationAttribute($onTableAliasName);
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveLeftJoinsForARelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            return $this->addLeftJoinsForARelationAttribute($onTableAliasName);
        }

        /**
         * @param $onTableAliasName
         * @param bool $canUseFromJoins
         * @return null|string
         */
        protected function
                  resolveJoinsForAttributeOnDifferentModelThatIsNotARelation($onTableAliasName, $canUseFromJoins = true)
        {
            assert('is_string($onTableAliasName)');
            assert('is_bool($canUseFromJoins)');
            if ($canUseFromJoins)
            {
                return $this->addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName);
            }
            else
            {
                return $this->addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName);
            }
        }

        /**
         * @param $onTableAliasName
         * @return string
         */
        protected function addMixedInOrCastedUpFromJoinsForAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if ($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addFromJoinsForAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addFromJoinsForAttributeThatIsCastedUp();
            }
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function addMixedInOrCastedUpLeftJoinsForAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if ($this->modelAttributeToDataProviderAdapter->isAttributeMixedIn())
            {
                return $this->addLeftJoinsForAttributeThatIsMixedIn($onTableAliasName);
            }
            else
            {
                return $this->addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName);
            }
        }

        /**
         * @param $onTableAliasName
         * @return string
         */
        protected function addFromJoinsForAttributeThatIsMixedIn($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $modelClassName     = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                $onTableAliasName = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                    $attributeTableName,
                    self::resolveForeignKey($attributeTableName),
                    $modelClassName::getTableName($modelClassName));
            }
            return $onTableAliasName;
        }

        /**
         * @return string
         */
        protected function addFromJoinsForAttributeThatIsCastedUp()
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName();
            return $this->processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName);
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function addLeftJoinsForAttributeThatIsMixedIn($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $attributeTableName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            return $this->addLeftJoinForMixedInAttribute($onTableAliasName, $attributeTableName);
        }

        /**
         * @param $onTableAliasName
         * @param $attributeTableName
         * @return null|string
         */
        protected function addLeftJoinForMixedInAttribute($onTableAliasName, $attributeTableName)
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($attributeTableName)');
            $onTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $attributeTableName,
                self::resolveForeignKey($attributeTableName),
                $onTableAliasName);
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function addLeftJoinsForAttributeThatIsCastedUp($onTableAliasName)
        {
            $modelClassName          = $this->modelAttributeToDataProviderAdapter->getResolvedModelClassName();
            $attributeModelClassName = $this->modelAttributeToDataProviderAdapter->getAttributeModelClassName();
            return $this->resolveAndProcessLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         * @throws NotSupportedException
         */
        protected function addLeftJoinsForARelationAttribute($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::MANY_MANY)
            {
                return $this->resolveJoinsForForARelationAttributeThatIsManyToMany($onTableAliasName);
            }
            elseif ($this->modelAttributeToDataProviderAdapter->isRelationTypeAHasManyVariant())
            {
                $onTableAliasName = $this->resolveJoinsForForARelationAttributeThatIsAHasManyVariant($onTableAliasName);
                $this->resolveSettingDistinctForARelationAttributeThatIsHasMany();
                return $onTableAliasName;
            }
            elseif ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_ONE)
            {
                return $this->resolveJoinsForForARelationAttributeThatIsAHasOne($onTableAliasName);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function resolveSettingDistinctForARelationAttributeThatIsHasMany()
        {
            if ($this->modelAttributeToDataProviderAdapter->getRelationType() == RedBeanModel::HAS_MANY)
            {
                $this->resolveSetToDistinct();
            }
        }

        protected function resolveSetToDistinct()
        {
            if ($this->setDistinct)
            {
                $this->joinTablesAdapter->setSelectDistinctToTrue();
            }
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForForARelationAttributeThatIsManyToMany($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $relationTableName               = $this->modelAttributeToDataProviderAdapter->getRelationTableName();
            $attributeTableName              = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            $relationJoiningTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                               $this->modelAttributeToDataProviderAdapter->getManyToManyTableName(),
                                               "id",
                                               $onTableAliasName,
                                               self::resolveForeignKey($attributeTableName));
            //if this is not the id column, then add an additional left join.
            if ($this->modelAttributeToDataProviderAdapter->getRelatedAttribute() != 'id')
            {
                $this->resolveSetToDistinct();
                return  $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                        $relationTableName,
                        self::resolveForeignKey($relationTableName),
                        $relationJoiningTableAliasName,
                        'id');
            }
            else
            {
                return $relationJoiningTableAliasName;
            }
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForForARelationAttributeThatIsAHasManyVariant($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $onTableJoinIdName  = 'id';
            $tableJoinIdName    = self::resolveForeignKey($onTableAliasName);
            $onTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                  $this->modelAttributeToDataProviderAdapter->getRelationTableName(),
                                  $onTableJoinIdName,
                                  $onTableAliasName,
                                  $tableJoinIdName);
            return $onTableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveJoinsForForARelationAttributeThatIsAHasOne($onTableAliasName)
        {
            assert('is_string($onTableAliasName)');
            $tableJoinIdName    = 'id';
            $onTableJoinIdName  = $this->modelAttributeToDataProviderAdapter->getColumnName();
            $onTableAliasName   = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                  $this->modelAttributeToDataProviderAdapter->getRelationTableName(),
                                  $onTableJoinIdName,
                                  $onTableAliasName,
                                  $tableJoinIdName);
            return $onTableAliasName;
        }

        /**
         * @param $modelClassName
         * @param $castedDownModelClassName
         * @param $onTableAliasName
         * @return null|string
         */
        protected function resolveAndProcessLeftJoinsForAttributeThatIsCastedDownOrUp($modelClassName,
                                                                                      $castedDownModelClassName,
                                                                                      $onTableAliasName)

        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            assert('is_string($onTableAliasName)');
            $resolvedCastedDownModelClassName   = $this->resolveAttributeModelClassNameWithCastingHintForCastingDown(
                                                  $castedDownModelClassName);
            if ($modelClassName != $resolvedCastedDownModelClassName)
            {
                //If the resolvedCastedDownModelClassName is actually casted up
                $modelDerivationPathToItem = $this->resolveModelDerivationPathToItemForCastingDown(
                                             $modelClassName, $resolvedCastedDownModelClassName);
                if (empty($modelDerivationPathToItem))
                {
                    return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName,
                           $resolvedCastedDownModelClassName);
                }
                else
                {
                    return $this->processLeftJoinsForAttributeThatIsCastedDown($modelClassName,
                        $resolvedCastedDownModelClassName,
                        $onTableAliasName);
                }
            }
            return $onTableAliasName;
        }

        /**
         * @param $modelClassName
         * @param $castedDownModelClassName
         * @param $onTableAliasName
         * @return null|string
         */
        protected function processLeftJoinsForAttributeThatIsCastedDown($modelClassName, $castedDownModelClassName,
                                                                        $onTableAliasName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            assert('is_string($onTableAliasName)');
            $modelDerivationPathToItem = $this->resolveModelDerivationPathToItemForCastingDown($modelClassName, $castedDownModelClassName);
            foreach ($modelDerivationPathToItem as $modelClassNameToCastDownTo)
            {
                if ($modelClassNameToCastDownTo::getCanHaveBean())
                {
                    $castedDownTableName = $modelClassNameToCastDownTo::getTableName($modelClassNameToCastDownTo);
                    $onTableAliasName    = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                                           $castedDownTableName,
                                           'id',
                                           $onTableAliasName,
                                           self::resolveForeignKey($modelClassName::getTableName($modelClassName)));
                    $modelClassName      = $modelClassNameToCastDownTo;
                }
            }
            return $onTableAliasName;
        }

        /**
         * @param $modelClassName
         * @param $castedDownModelClassName
         * @return array
         */
        protected function resolveModelDerivationPathToItemForCastingDown($modelClassName, $castedDownModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($castedDownModelClassName);
            if ($modelClassName == 'Item')
            {
                return $modelDerivationPathToItem;
            }
            foreach ($modelDerivationPathToItem as $key => $modelClassNameToCastDown)
            {
                unset($modelDerivationPathToItem[$key]);
                if ($modelClassName == $modelClassNameToCastDown)
                {
                    break;
                }
            }
            return $modelDerivationPathToItem;
        }

        /**
         * @param $modelClassName
         * @param $castedDownModelClassName
         * @return mixed
         * @throws NotSupportedException
         */
        protected static function resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($castedDownModelClassName)');
            if (!$modelClassName::getCanHaveBean())
            {
                if (!$castedDownModelClassName::getCanHaveBean())
                {
                    throw new NotSupportedException();
                }
                return $castedDownModelClassName;
            }
            else
            {
                return $modelClassName;
            }
        }

        /**
         * @param $modelClassName
         * @param $attributeModelClassName
         * @return string
         * @throws NotSupportedException
         */
        private function processFromJoinsForAttributeThatIsCastedUp($modelClassName, $attributeModelClassName)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attributeModelClassName)');
            $attributeTableName = $attributeModelClassName::getTableName($attributeModelClassName);
            $tableAliasName     = $attributeTableName;
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) != $attributeModelClassName &&
                get_parent_class($modelClassName) != 'RedBeanModel')
            {
                $castedDownFurtherModelClassName = $castedDownModelClassName;
                $castedDownModelClassName        = $modelClassName;
                $modelClassName                  = get_parent_class($modelClassName);
                if ($modelClassName::getCanHaveBean())
                {
                    $castedUpAttributeTableName = $modelClassName::getTableName($modelClassName);
                    if (!$this->joinTablesAdapter->isTableInFromTables($castedUpAttributeTableName))
                    {
                        if ($castedDownModelClassName::getCanHaveBean())
                        {
                            $onTableAliasName = $castedDownModelClassName::getTableName($castedDownModelClassName);
                        }
                        elseif ($castedDownFurtherModelClassName::getCanHaveBean())
                        {
                            $onTableAliasName = $castedDownModelClassName::getTableName($castedDownFurtherModelClassName);
                        }
                        else
                        {
                            throw new NotSupportedException();
                        }
                        $onTableAliasName = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                            $castedUpAttributeTableName,
                            self::resolveForeignKey($castedUpAttributeTableName),
                            $onTableAliasName);
                    }
                }
            }
            if (!$this->joinTablesAdapter->isTableInFromTables($attributeTableName))
            {
                $modelClassName   = static::resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName);
                $tableAliasName   = $this->joinTablesAdapter->addFromTableAndGetAliasName(
                    $attributeTableName,
                    self::resolveForeignKey($attributeTableName),
                    $modelClassName::getTableName($modelClassName));
            }
            return $tableAliasName;
        }

        /**
         * @param $onTableAliasName
         * @param $modelClassName
         * @param $attributeModelClassName
         * @return null|string
         */
        private function resolveAndProcessLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName)
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($modelClassName)');
            assert('is_string($attributeModelClassName)');
            if ($modelClassName == $attributeModelClassName)
            {
                return $onTableAliasName;
            }
            return $this->processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName);
        }

        /**
         * @param $onTableAliasName
         * @param $modelClassName
         * @param $attributeModelClassName
         * @return null|string
         * @throws NotSupportedException
         */
        private function processLeftJoinsForAttributeThatIsCastedUp($onTableAliasName, $modelClassName, $attributeModelClassName)
        {
            assert('is_string($onTableAliasName)');
            assert('is_string($modelClassName)');
            assert('is_string($attributeModelClassName)');
            $attributeTableName       = $attributeModelClassName::getTableName($attributeModelClassName);
            $castedDownModelClassName = $modelClassName;
            while (get_parent_class($modelClassName) != $attributeModelClassName &&
                get_parent_class($modelClassName) != 'RedBeanModel')
            {
                $castedDownFurtherModelClassName = $castedDownModelClassName;
                $castedDownModelClassName        = $modelClassName;
                $modelClassName                  = get_parent_class($modelClassName);
                if ($modelClassName::getCanHaveBean())
                {
                    $castedUpAttributeTableName = $modelClassName::getTableName($modelClassName);

                    /**
                    if ($castedDownModelClassName::getCanHaveBean())
                    {
                        $resolvedTableJoinIdName = $castedDownModelClassName::getTableName($castedDownModelClassName);
                    }
                    elseif ($castedDownFurtherModelClassName::getCanHaveBean())
                    {
                        $resolvedTableJoinIdName = $castedDownModelClassName::getTableName($castedDownFurtherModelClassName);
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                     * */
                    $onTableAliasName =     $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                        $castedUpAttributeTableName,
                        self::resolveForeignKey($castedUpAttributeTableName),
                        $onTableAliasName);//,
                        //$resolvedTableJoinIdName);
                }
            }
            //Add left table if it is not already added
            $modelClassName   = static::resolveModelClassNameThatCanHaveTable($modelClassName, $castedDownModelClassName);
            $onTableAliasName = $this->joinTablesAdapter->addLeftTableAndGetAliasName(
                $attributeTableName,
                self::resolveForeignKey($attributeTableName),
                $onTableAliasName); //,
                //$modelClassName::getTableName($modelClassName));
            return $onTableAliasName;
        }

        /**
         * @return mixed
         */
        private function resolveOnTableAliasNameForDerivedRelationViaCastedUpModel()
        {
            if ($this->modelAttributeToDataProviderAdapter->isAttributeDerivedRelationViaCastedUpModel())
            {
                $onTableAliasName = $this->modelAttributeToDataProviderAdapter->getModelTableName();
            }
            else
            {
                $onTableAliasName = $this->modelAttributeToDataProviderAdapter->getAttributeTableName();
            }
            return $onTableAliasName;
        }
    }
?>