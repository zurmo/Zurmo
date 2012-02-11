<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Adapter class to get attributes from
     * a model as an array.
     */
    class ModelAttributesImportMappingAdapter
    {
        protected $model;

        public function __construct(RedBeanModel $model)
        {
            assert('$model !== null');
            $this->model = $model;
        }

        /**
         * Returns HAS_ONE relation attributes
         * and non-relation attributes in an array
         * mapping attribute names to 'attributeLabel' to the
         * attribute label.  Also returns 'isRequired' and 'isAudited' information.
         */
        public function getAttributes()
        {
            $attributes = array();
            ModelAttributeImportMappingCollectionUtil::populateCollection(
                $attributes,
                'id',
                $this->model->getAttributeLabel('id'),
                'id',
                'Id'
            );
            foreach ($this->model->getAttributes() as $attributeName => $notUsed)
            {
                if (!$this->model->isRelation($attributeName) ||
                     $this->isAttributeAnOwnedCustomFieldRelation($attributeName) ||
                     $this->isAttributeAHasOneNotOwnedRelation($attributeName) ||
                     $this->isAttributeAHasOneOwnedRelationThatShouldBehaveAsNotOwnedRelation($attributeName))
                {
                   $type         = ModelAttributeToMixedTypeUtil::getType($this->model, $attributeName);
                   $resolvedType = static::resolveAttributeImportTypeByAttributeName($type, $attributeName);
                    ModelAttributeImportMappingCollectionUtil::populateCollection(
                        $attributes,
                        $attributeName,
                        $this->model->getAttributeLabel($attributeName),
                        $attributeName,
                        $resolvedType,
                        null,
                        $this->model->isAttributeRequired($attributeName)
                    );
                }
                elseif ($this->isAttributeAHasOneOwnedRelation($attributeName))
                {
                    foreach ($this->model->{$attributeName}->getAttributes() as $relationAttributeName => $notUsed)
                    {
                        if (!$this->model->{$attributeName}->isRelation($relationAttributeName))
                        {
                            $attributeLabel = $this->model->getAttributeLabel($attributeName) .
                                              ' - ' .
                                              $this->model->{$attributeName}->getAttributeLabel($relationAttributeName);
                            $type         = ModelAttributeToMixedTypeUtil::getType($this->model->$attributeName,
                                                                                   $relationAttributeName);
                            $resolvedType = static::
                                            resolveAttributeImportTypeByAttributeName($type, $relationAttributeName);
                            ModelAttributeImportMappingCollectionUtil::populateCollection(
                                $attributes,
                                $attributeName . FormModelUtil::DELIMITER . $relationAttributeName,
                                $attributeLabel,
                                $attributeName,
                                $resolvedType,
                                $relationAttributeName,
                                $this->model->{$attributeName}->isAttributeRequired($relationAttributeName)
                            );
                        }
                    }
                }
            }
            return $attributes;
        }

        protected function isAttributeAHasOneOwnedRelation($attributeName)
        {
            assert('is_string($attributeName)');
            if ($this->model->isRelation($attributeName) &&
                       $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE &&
                       $this->model->isOwnedRelation($attributeName))
            {
                return true;
            }
            return false;
        }

        protected function isAttributeAHasOneNotOwnedRelation($attributeName)
        {
            assert('is_string($attributeName)');
            if ($this->model->isRelation($attributeName) &&
                       ($this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE ||
                       $this->model->getRelationType($attributeName) == RedBeanModel::HAS_MANY_BELONGS_TO )  &&
                       !$this->model->isOwnedRelation($attributeName))
            {
                return true;
            }
            return false;
        }

        protected function isAttributeAnOwnedCustomFieldRelation($attributeName)
        {
            assert('is_string($attributeName)');
            if ($this->model->isRelation($attributeName) &&
                       $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE &&
                       $this->model->isOwnedRelation($attributeName) &&
                       $this->model->{$attributeName} instanceof OwnedCustomField)
            {
                return true;
            }
            return false;
        }

        /**
         * There are some HAS_ONE owned relations that should be treated as non owned relations.
         * @param string $attributeName
         * @return true/false
         */
        protected function isAttributeAHasOneOwnedRelationThatShouldBehaveAsNotOwnedRelation($attributeName)
        {
            assert('is_string($attributeName)');
            if ($this->model->isRelation($attributeName) &&
                       $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE &&
                       $this->model->isOwnedRelation($attributeName) &&
                       in_array($this->model->getRelationModelClassName($attributeName),
                       static::getRelationModelClassNamesToTreatAsNonOwnedRelations()))
            {
                return true;
            }
            return false;
        }

        /**
         * CurrencyValue while usually owned and HAS_ONE, should be treated as a non-owned relation.
         * This is because the rateToBase, currency, and value attributes of currencyValue will be handled as
         * mappingRules for this attribute instead of individually selectable attributes to map to the import columns.
         * @see self::isAttributeAHasOneOwnedRelationThatShouldBehaveAsNotOwnedRelation();
         * @return array
         */
        protected static function getRelationModelClassNamesToTreatAsNonOwnedRelations()
        {
            return array('CurrencyValue');
        }

        /**
         * Resolves against special attributes.  Some attributes, even though they have a 'type' that matches an existing
         * generic attribute import rule, require their own attribute import rule.  These attributes are not derived and
         * this is why their attribute import type must be adjusted here.  If the attribute was derived, then the derived
         * attribute name becomes the type and this wouldn't be required.
         * @param string $type
         * @param string $attributeName
         */
        protected static function resolveAttributeImportTypeByAttributeName($type, $attributeName)
        {
            assert('is_string($type)');
            assert('is_string($attributeName)');
            if ($attributeName =='createdByUser')
            {
                return 'CreatedByUser';
            }
            if ($attributeName =='modifiedByUser')
            {
                return 'ModifiedByUser';
            }
            if ($attributeName =='createdDateTime')
            {
                return 'CreatedDateTime';
            }
            if ($attributeName =='modifiedDateTime')
            {
                return 'ModifiedDateTime';
            }
            if ($attributeName =='username')
            {
                return 'Username';
            }
            return $type;
        }
    }
?>
