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
     * Adapter class to get attributes from
     * a model as an array.
     */
    class ModelAttributesAdapter
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
            ModelAttributeCollectionUtil::populateCollection(
                $attributes,
                'id',
                $this->model->getAttributeLabel('id'),
                'Text'
            );
            foreach ($this->model->getAttributes() as $attributeName => $notUsed)
            {
                if (!$this->model->isRelation($attributeName) ||
                    $this->model->getRelationType($attributeName) == RedBeanModel::HAS_ONE)
                {
                    if ($this->model instanceof Item)
                    {
                        $isAudited = $this->model->isAttributeAudited($attributeName);
                    }
                    else
                    {
                        $isAudited = false;
                    }
                    ModelAttributeCollectionUtil::populateCollection(
                        $attributes,
                        $attributeName,
                        $this->model->getAttributeLabel($attributeName),
                        ModelAttributeToDesignerTypeUtil::getDesignerType($this->model, $attributeName),
                        $this->model->isAttributeRequired($attributeName),
                        $this->model->isAttributeReadOnly($attributeName),
                        $isAudited
                    );
                }
            }
            return $attributes;
        }

        /**
         * Returns standard attributes in same
         * format as getAttributes returns.
         */
        public function getStandardAttributes()
        {
            $attributes = $this->getAttributes();
            $defaultAttributeNames = $this->getStandardAttributeNames();
            foreach ($attributes as $attributeName => $notUsed)
            {
                if (!in_array($attributeName, $defaultAttributeNames))
                {
                    unset($attributes[$attributeName]);
                }
            }
            return $attributes;
        }

        /**
         * Given an attributeName, is this a default attribute on the model
         */
        public function isStandardAttribute($attributeName)
        {
            $defaultAttributeNames = $this->getStandardAttributeNames();
            if (in_array($attributeName, $defaultAttributeNames))
            {
                return true;
            }
            return false;
        }

        /**
         * Returns custom attributes in same
         * format as getAttributes returns.
         */
        public function getCustomAttributes()
        {
            $attributes = $this->getAttributes();
            $defaultAttributeNames = $this->getStandardAttributeNames();
            foreach ($attributes as $attributeName => $notUsed)
            {
                if (in_array($attributeName, $defaultAttributeNames))
                {
                    unset($attributes[$attributeName]);
                }
            }
            return $attributes;
        }

        private function getStandardAttributeNames()
        {
            $defaultAttributeNames = array('id');
            $metadata = $this->model->getDefaultMetadata();
            foreach ($metadata as $className => $perClassMetadata)
            {
                foreach ($perClassMetadata as $key => $value)
                {
                    if ($key == 'members')
                    {
                        $defaultAttributeNames = array_merge($defaultAttributeNames, $value);
                    }
                    elseif ($key == 'relations')
                    {
                        $defaultAttributeNames = array_merge($defaultAttributeNames, array_keys($value));
                    }
                }
            }
            return $defaultAttributeNames;
        }

        public function setAttributeMetadataFromForm(AttributeForm $attributeForm)
        {
            $modelClassName  = get_class($this->model);
            $attributeName   = $attributeForm->attributeName;
            $attributeLabels = $attributeForm->attributeLabels;
            $defaultValue    = $attributeForm->defaultValue;
            $elementType     = $attributeForm->getAttributeTypeName();
            $partialTypeRule = $attributeForm->getModelAttributePartialRule();

            //should we keep this here with (boolean)?
            $isRequired      = (boolean)$attributeForm->isRequired;
            $isAudited       = (boolean)$attributeForm->isAudited;
            if (!$attributeForm instanceof DropDownAttributeForm)
            {
                if($defaultValue === '')
                {
                    $defaultValue = null;
                }
                if ($attributeForm instanceof MaxLengthAttributeForm)
                {
                    $maxLength = (int)$attributeForm->maxLength;
                }
                else
                {
                    $maxLength = null;
                }
                if ($attributeForm instanceof MinMaxValueAttributeForm)
                {
                    $minValue = (int)$attributeForm->minValue;
                    $maxValue = (int)$attributeForm->maxValue;
                }
                else
                {
                    $minValue = null;
                    $maxValue = null;
                }
                if ($attributeForm instanceof DecimalAttributeForm)
                {
                    $precision = (int)$attributeForm->precisionLength;
                }
                else
                {
                    $precision = null;
                }
                ModelMetadataUtil::addOrUpdateMember($modelClassName,
                                                     $attributeName,
                                                     $attributeLabels,
                                                     $defaultValue,
                                                     $maxLength,
                                                     $minValue,
                                                     $maxValue,
                                                     $precision,
                                                     $isRequired,
                                                     $isAudited,
                                                     $elementType,
                                                     $partialTypeRule);
                $this->resolveDatabaseSchemaForModel($modelClassName);
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function removeAttributeMetadata($attributeName)
        {
            assert('is_string($attributeName) && $attributeName != ""');
            $modelClassName = get_class($this->model);
            ModelMetadataUtil::removeAttribute($modelClassName, $attributeName);
        }

        public function resolveDatabaseSchemaForModel($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $messageLogger = new MessageLogger();
                RedBeanDatabaseBuilderUtil::autoBuildModels(array('User', $modelClassName), $messageLogger);
                RedBeanDatabase::freeze();
                if ($messageLogger->isErrorMessagePresent())
                {
                    throw new FailedDatabaseSchemaChangeException($messageLogger->printMessages(true, true));
                }
            }
        }
    }
?>
