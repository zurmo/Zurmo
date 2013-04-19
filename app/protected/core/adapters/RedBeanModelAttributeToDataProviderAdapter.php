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
     * Adapts RedBeanModel information based on a specific model attribute, and optionally a specific related attribute
     * into data provider ready information.
     */
    class RedBeanModelAttributeToDataProviderAdapter
    {
        /**
         * @var string
         */
        protected $modelClassName;

        /**
         * @var string
         */
        protected $attribute;

        /**
         * If the attribute specified is a relation on the $modelClassName, then a related attribute can be specified.
         * @var string
         */
        protected $relatedAttribute;

        /**
         * RedBeanModel of $this->modelClassName
         * @var RedBeanModel
         */
        protected $model;

        /**
         * @var string|null
         */
        protected $castingHintAttributeModelClassName;

        /**
         * @var string|null
         */
        protected $castingHintStartingModelClassName;

        private $relatedModel;

        /**
         * @param string $modelClassName
         * @param string $attribute
         * @param string $relatedAttribute
         */
        public function __construct($modelClassName, $attribute, $relatedAttribute = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($attribute)');
            assert('is_string($relatedAttribute) || $relatedAttribute == null');
            $this->modelClassName   = $modelClassName;
            $this->attribute        = $attribute;
            $this->relatedAttribute = $relatedAttribute;
        }

        /**
         * Utilizing casting hint to ensure a castUp or castDown procedure done in ModelJoinBuilder for example
         * does not cast too far down or up when looking for the end node of a relation.  For example, if you are
         * coming from meeting into account via activityItems, but the final attribute is owner, then you don't need
         * to cast all the way down to account.
         *
         * @param string $castingHintAttributeModelClassName
         */
        public function setCastingHintModelClassNameForAttribute($castingHintAttributeModelClassName)
        {
            assert('is_string($castingHintAttributeModelClassName)');
            $this->castingHintAttributeModelClassName = $castingHintAttributeModelClassName;
        }

        /**
         * @return string
         */
        public function getCastingHintModelClassNameForAttribute()
        {
            return $this->castingHintAttributeModelClassName;
        }

        /**
         * @param string|null $castingHintStartingModelClassName
         */
        public function setCastingHintStartingModelClassName($castingHintStartingModelClassName)
        {
            assert('is_string($castingHintStartingModelClassName) || $castingHintStartingModelClassName == null');
            $this->castingHintStartingModelClassName = $castingHintStartingModelClassName;
        }

        /**
         * Resolves what the model class name is to use as the starting point.  For cast hinting it is possible
         * the starting model class name is already casted up, therefore casting up further is not required.
         * @see setCastingHintModelClassNameForAttribute
         * @return string
         */
        public function getResolvedModelClassName()
        {
            if ($this->castingHintStartingModelClassName != null)
            {
                return $this->castingHintStartingModelClassName;
            }
            return $this->getModelClassName();
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        public function getAttribute()
        {
            return $this->attribute;
        }

        public function getRelatedAttribute()
        {
            return $this->relatedAttribute;
        }

        /**
         * @returns The table name for the model class name specified in this adapter.
         */
        public function getModelTableName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getTableName($modelClassName);
        }

        /**
         * @returns The model class name for the attribute specified in this adapter.  Since the attribute might not be on
         * the same model as the model class name specified, (might be casted up) this method is needed.
         */
        public function getAttributeModelClassName()
        {
            if ($this->attribute == 'id')
            {
                return $this->modelClassName;
            }
            $modelClassName = $this->modelClassName;
            return $modelClassName::getAttributeModelClassName($this->attribute);
        }

        /**
         * @returns The table name for the attribute specified in this adapter.  Since the attribute might not be on
         * the same table as the model class name specified, this method is needed.
         */
        public function getAttributeTableName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getTableName($this->getAttributeModelClassName());
        }

        public function getModel()
        {
            if ($this->model == null)
            {
                $this->model = new $this->modelClassName(false);
            }
            return $this->model;
        }

        /**
         * @return The column name for the attribute specified in this adapter.
         */
        public function getColumnName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getColumnNameByAttribute($this->attribute);
        }

        /**
         * @return string The column name for the attribute to be used in the
         * sort by the attribute specified in this adapater based on the position
         * in the array returned by getSortAttributesByAttribute
         */
        public function getColumnNameByPosition($attributePosition)
        {
            $modelClassName = $this->modelClassName;
            $sortAttributes = $modelClassName::getSortAttributesByAttribute($this->attribute);
            if ($attributePosition >= count($sortAttributes))
            {
                throw new InvalidArgumentException('Attribute position is not valid');
            }
            $sortAtribute = $sortAttributes[$attributePosition];
            return $modelClassName::getColumnNameByAttribute($sortAtribute);
        }

        /**
         * @return true/false - Is the attribute a relation on the model class name.
         */
        public function isRelation()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::isRelation($this->attribute);
        }

        /**
         * @return The relation type of the attribute on the model class name.
         */
        public function getRelationType()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getRelationType($this->attribute);
        }

        public function isOwnedRelation()
        {
            if (!$this->getModel()->isRelation($this->attribute))
            {
                return false;
            }
            return $this->getModel()->isOwnedRelation($this->attribute);
        }

        /**
         * @return bool
         */
        public function isRelationTypeAHasManyVariant()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::isRelationTypeAHasManyVariant($this->attribute);
        }

        /**
         * @return bool
         */
        public function isRelationTypeAHasOneVariant()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::isRelationTypeAHasOneVariant($this->attribute);
        }

        /**
         * @return true/false if the $relatedAttribute was specified in the construcor.
         */
        public function hasRelatedAttribute()
        {
            if ($this->relatedAttribute == null)
            {
                return false;
            }
            return true;
        }

        /**
         * If the attribute is a relation, returns the RedBeanModel of that relation.
         */
        protected function getRelationModel()
        {
            if ($this->relatedModel == null)
            {
                $relationModelClassName     = $this->getRelationModelClassName();
                $this->relatedModel         = new $relationModelClassName(false);
            }
            return $this->relatedModel;
        }

        /**
         * If the attribute is a relation, returns the model class name of that relation.
         */
        public function getRelationModelClassName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getRelationModelClassName($this->attribute);
        }

        /**
         * If the attribute is a relation, returns the model class name of that relation's relatedAttribute. This might
         * be different than the relation's model class name if the relatedAttribute is casted up.
         */
        public function getRelatedAttributeModelClassName()
        {
            if ($this->relatedAttribute == 'id')
            {
                return $this->getRelationModelClassName();
            }
            $relationModelClassName = $this->getRelationModelClassName();
            return $relationModelClassName::getAttributeModelClassName($this->relatedAttribute);
        }

        /**
         * If the relation model class can have a bean.
         */
        public function canRelationHaveTable()
        {
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::getCanHaveBean();
        }

        /**
         * If the attribute is a relation, returns the relation's table name.
         */
        public function getRelationTableName()
        {
            $modelClassName = $this->getRelationModelClassName();
            if ($this->canRelationHaveTable())
            {
                return $modelClassName::getTableName($modelClassName);
            }
            else
            {
                while (get_parent_class($modelClassName) != 'RedBeanModel')
                {
                    $modelClassName = get_parent_class($modelClassName);
                    if ($modelClassName::getCanHaveBean())
                    {
                        return $modelClassName::getTableName($modelClassName);
                    }
                }
                throw new NotSupportedException();
            }
        }

        /**
         * If the attribute is a relation, returns the relation model class name or the next available that
         * can have a table
         */
        public function getRelationModelClassNameThatCanHaveATable()
        {
            $modelClassName = $this->getRelationModelClassName();
            if ($this->canRelationHaveTable())
            {
                return $modelClassName;
            }
            else
            {
                while (get_parent_class($modelClassName) != 'RedBeanModel')
                {
                    $modelClassName = get_parent_class($modelClassName);
                    if ($modelClassName::getCanHaveBean())
                    {
                        return $modelClassName;
                    }
                }
                throw new NotSupportedException();
            }
        }

        /**
         * If the attribute is a relation, returns the relation's relatedAttribute's table name.
         */
        public function getRelatedAttributeTableName()
        {
            $modelClassName = $this->getRelatedAttributeModelClassName();
            return            $modelClassName::getTableName($modelClassName);
        }

        /**
         *
         * If the attribute is a relation, returns the relation's relatedAttribute's column name.
         */
        public function getRelatedAttributeColumnName()
        {
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::getColumnNameByAttribute($this->relatedAttribute);
        }

        /**
         * If the attribute is a relation and the attribute has more sort attributes on the relation
         * retunrs the column name to make the sort by the position in the array return from
         * getSortAttributesByAttribute
         * @return string
         */
        public function getRelatedAttributeColumnNameByPosition($attributePosition)
        {
            $modelClassName = $this->getRelationModelClassName();
            $sortAttributes = $modelClassName::getSortAttributesByAttribute($this->relatedAttribute);
            if ($attributePosition >= count($sortAttributes))
            {
                throw new InvalidArgumentException('Attribute position is not valid');
            }
            $sortAttribute = $sortAttributes[$attributePosition];
            return $modelClassName::getColumnNameByAttribute($sortAttribute);
        }

        /**
         * @return true/false - Is the attribute a relation on the model class name.
         */
        public function isRelatedAttributeRelation()
        {
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::isRelation($this->relatedAttribute);
        }

        /**
         * @return The relation type of the attribute on the model class name.
         */
        public function getRelatedAttributeRelationType()
        {
            if (!$this->isRelatedAttributeRelation())
            {
                throw new NotSupportedException();
            }
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::getRelationType($this->relatedAttribute);
        }

        /**
         * @return mixed
         * @throws NotSupportedException
         */
        public function getRelatedAttributeRelationModelClassName()
        {
            if (!$this->isRelatedAttributeRelation())
            {
                throw new NotSupportedException();
            }
            $modelClassName = $this->getRelationModelClassName();
            return $modelClassName::getRelationModelClassName($this->relatedAttribute);
        }

        /**
         * @return string
         * @throws NotSupportedException
         */
        public function getManyToManyTableName()
        {
            if ($this->getRelationType() != RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            $attributeName = $this->getAttribute();
            return $this->getModel()->{$attributeName}->getTableName();
        }

        /**
         * @return bool
         */
        public function isAttributeMixedIn()
        {
            if ($this->getModelClassName() == 'User' &&
                $this->getAttributeModelClassName() == 'Person')
            {
                return true;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function isAttributeOnDifferentModel()
        {
            if ($this->getAttributeModelClassName() == $this->getModelClassName())
            {
                return false;
            }
            return true;
        }

        /**
         * Resolve which column to use when querying a many to many relation.
         * @return string
         */
        public function resolveManyToManyColumnName()
        {
            if ($this->getRelatedAttribute() != 'id')
            {
               return $this->getRelatedAttributeColumnName();
            }
            else
            {
                return $this->getRelationTableName() . '_id';
            }
        }

        public function isAttributeDerivedRelationViaCastedUpModel()
        {
            $modelClassName = $this->modelClassName;
            if ($modelClassName::isADerivedRelationViaCastedUpModel($this->attribute))
            {
                return true;
            }
            return false;
        }

        public function getCastedUpModelClassNameForDerivedRelation()
        {
            if (!$this->isAttributeDerivedRelationViaCastedUpModel())
            {
                throw new NotSupportedException();
            }
            $modelClassName         = $this->modelClassName;
            $relationModelClassName = $modelClassName::getDerivedRelationModelClassName($this->attribute);
            $opposingRelationName   = $modelClassName::getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();
            return $relationModel->getRelationModelClassName($opposingRelationName);
        }

        public function getManyToManyTableNameForDerivedRelationViaCastedUpModel()
        {
            $modelClassName         = $this->modelClassName;
            $relationModelClassName = $modelClassName::getDerivedRelationModelClassName($this->attribute);
            $opposingRelationName   = $modelClassName::getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();

            if ($modelClassName::getDerivedRelationType($this->attribute) != RedBeanModel::MANY_MANY)
            {
                throw new NotSupportedException();
            }
            $attributeName = $this->getAttribute();
            return $relationModel->{$opposingRelationName}->getTableName();
        }

        /**
         * In the case of account -> meeting, this method returns 'Activity' since 'Activity' is the model that the
         * opposing relation rests on.  This is different than getDerivedRelationModelClassName which would be 'Meeting'.
         * Sometimes both are the same model, it just depends if the final model class is casted down or not
         * @return mixed
         */
        public function getOpposingRelationModelClassName()
        {
            $modelClassName         = $this->modelClassName;
            $relationModelClassName = $this->getDerivedRelationViaCastedUpModelClassName();
            $opposingRelationName   = $modelClassName::getDerivedRelationViaCastedUpModelOpposingRelationName($this->attribute);
            $relationModel          = new $relationModelClassName();
            return $relationModel->getAttributeModelClassName($opposingRelationName);
        }

        /**
         * @return mixed
         */
        public function getDerivedRelationViaCastedUpModelClassName()
        {
            $modelClassName = $this->modelClassName;
            return $modelClassName::getDerivedRelationModelClassName($this->getAttribute());
        }

        public function getOpposingRelationTableName()
        {
            $opposingRelationModelClassName  = $this->getOpposingRelationModelClassName();
            return $opposingRelationModelClassName::getTableName($opposingRelationModelClassName);
        }

        public function isDerivedRelationViaCastedUpModelDifferentThanOpposingModelClassName()
        {
            $opposingRelationModelClassName  = $this->getOpposingRelationModelClassName();
            $derivedRelationModelClassName   = $this->getDerivedRelationViaCastedUpModelClassName();
            if ($opposingRelationModelClassName != $derivedRelationModelClassName)
            {
                return true;
            }
            return false;
        }

        /**
         * Extend as needed to support inferred relations
         * @see InferredRedBeanModelAttributeToDataProviderAdapter
         * @return bool
         */
        public function isInferredRelation()
        {
            return false;
        }

        /**
         * Returns true if the attribute uses another attribute in the sort
         * @return boolean
         */
        public function sortUsesTwoAttributes()
        {
            $modelClassName = $this->modelClassName;
            if (count($modelClassName::getSortAttributesByAttribute($this->attribute)) > 1)
            {
                return true;
            }
            return false;
        }

        public function relatedAttributesSortUsesTwoAttributes()
        {
            $modelClassName = $this->getRelationModelClassName();
            if (count($modelClassName::getSortAttributesByAttribute($this->relatedAttribute)) > 1)
            {
                return true;
            }
            return false;
        }
    }
?>