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
     * For each row of data generated using the data provider, a ReportResultsRowData object is created.  The methods
     * in this class allow ListViewColumnAdapters to easily retrieve the values of display attributes to display.
     * There are 2 types of data, raw data such as SUM(amount) and model data such as $account->name.  The columnAliasNames
     * on the display attributes are accessed internally so if you call $reportResultsRowsData->col1, the object will
     * resolve the value either from the raw data or from an attribute on a model.
     */
    class ReportResultsRowData extends CComponent
    {
        const ATTRIBUTE_NAME_PREFIX            = 'attribute';

        const DRILL_DOWN_GROUP_BY_VALUE_PREFIX = 'groupByRowValue';

        /**
         * @var int
         */
        protected $id;

        /**
         * @var array
         */
        protected $displayAttributes;

        /**
         * RedBeanModels indexed by aliases.
         * @var array
         */
        protected $modelsByAliases                = array();

        /**
         * @var array
         */
        protected $selectedColumnNamesAndValues   = array();

        /**
         * @var array
         */
        protected $selectedColumnNamesAndRowSpans = array();

        /**
         * @var array
         */
        protected $selectedColumnNamesAndLabels   = array();

        /**
         * @param $key
         * @return string
         */
        public static function resolveAttributeNameByKey($key)
        {
            assert('is_numeric($key) || is_string($key)');
            return self::ATTRIBUTE_NAME_PREFIX . $key;
        }

        public function getDisplayAttributes()
        {
            return $this->displayAttributes;
        }

        /**
         * @param array $displayAttributes
         * @param int $id
         */
        public function __construct(array $displayAttributes, $id)
        {
            assert('is_int($id)');
            $this->displayAttributes = $displayAttributes;
            $this->id                = $id;
        }

        /**
         * @param string $name
         * @return bool
         */
        public function __isset($name)
        {
            if ($this->$name !== null)
            {
                return true;
            }
            return parent::__isset($name);
        }

        /**
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $name);
            if (count($parts) == 2 && $parts[1] != null)
            {
                return $this->resolveValueFromModel($parts[1]);
            }
            //Not using isset, because a null value would not resolve correctly
            if (array_key_exists($name, $this->selectedColumnNamesAndValues))
            {
                return $this->selectedColumnNamesAndValues[$name];
            }
            return parent::__get($name);
        }

        /**
         * @param RedBeanModel $model
         * @param string $alias
         * @throws NotSupportedException if the alias does not have a corresponding model
         */
        public function addModelAndAlias(RedBeanModel $model, $alias)
        {
            assert('is_string($alias)');
            if (isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            $this->modelsByAliases[$alias] = $model;
        }

        /**
         * @param string $columnName
         * @param mixed $value
         */
        public function addSelectedColumnNameAndValue($columnName, $value)
        {
            $this->selectedColumnNamesAndValues[$columnName] = $value;
        }

        /**
         * @param string $columnName
         * @param string $label
         */
        public function addSelectedColumnNameAndLabel($columnName, $label)
        {
            assert('is_string($label)');
            $this->selectedColumnNamesAndLabels[$columnName] = $label;
        }

        /**
         * @param string $columnName
         * @return string
         */
        public function getLabel($columnName)
        {
            assert('is_string($columnName)');
            return $this->selectedColumnNamesAndLabels[$columnName];
        }

        /**
         * @param string $columnName
         * @param mixed $value
         */
        public function addSelectedColumnNameAndRowSpan($columnName, $value)
        {
            assert('is_int($value)');
            $this->selectedColumnNamesAndRowSpans[$columnName] = $value;
        }

        /**
         * @param  string $columnName
         * @return string
         */
        public function getSelectedColumnRowSpan($columnName)
        {
            assert('is_string($columnName)');
            return $this->selectedColumnNamesAndRowSpans[$columnName];
        }

        /**
         * @param string $attribute
         * @return null
         * @throws NotSupportedException if the displayAttributeKey can not be extracted from the string $attribute
         * passed as a parameter
         */
        public function getModel($attribute)
        {
            assert('is_string($attribute)');
            list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attribute);
            if ($displayAttributeKey != null)
            {
                return $this->resolveModel($displayAttributeKey);
            }
            throw new NotSupportedException();
        }

        /**
         * Utilized by export adapters to get the header label for each column.
         * @param $attribute
         * @return string
         * @throws NotSupportedException
         */
        public function getAttributeLabel($attribute)
        {
            assert('is_string($attribute)');
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $attribute);
            if (count($parts) == 2 && $parts[1] != null)
            {
                list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attribute);
                if ($displayAttributeKey != null && isset($this->displayAttributes[$displayAttributeKey]))
                {
                    return $this->displayAttributes[$displayAttributeKey]->getDisplayLabel();
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            $parts = explode(DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX, $attribute);
            if (count($parts) == 2 && $parts[1] != null)
            {
                list($notUsed, $displayAttributeKey) = explode(DisplayAttributeForReportForm::COLUMN_ALIAS_PREFIX, $attribute);
                if ($displayAttributeKey != null && isset($this->displayAttributes[$displayAttributeKey]))
                {
                    return $this->displayAttributes[$displayAttributeKey]->getDisplayLabel();
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
        }

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @return array
         */
        public function getDataParamsForDrillDownAjaxCall()
        {
            $dataParams = array();
            foreach ($this->displayAttributes as $key => $displayAttribute)
            {
                if ($displayAttribute->valueUsedAsDrillDownFilter)
                {
                    $attributeAlias = $displayAttribute->resolveAttributeNameForGridViewColumn($key);
                    if ($this->shouldResolveValueFromModel($attributeAlias))
                    {
                        list($notUsed, $displayAttributeKey) = explode(self::ATTRIBUTE_NAME_PREFIX, $attributeAlias);
                        $model = $this->resolveModel($displayAttributeKey);
                        if ($model == null)
                        {
                            $value = null;
                        }
                        else
                        {
                            $value = $this->resolveRawValueByModel($displayAttribute, $model);
                        }
                    }
                    else
                    {
                        $value = $this->selectedColumnNamesAndValues[$attributeAlias];
                    }

                    $dataParams[self::resolveDataParamKeyForDrillDown($displayAttribute->attributeIndexOrDerivedType)] = $value;
                }
            }
            return $dataParams;
        }

        /**
         * @param string $attributeIndexOrDerivedType
         * @return string
         */
        public static function resolveDataParamKeyForDrillDown($attributeIndexOrDerivedType)
        {
            return self::DRILL_DOWN_GROUP_BY_VALUE_PREFIX . $attributeIndexOrDerivedType;
        }

        /**
         * @param string $displayAttributeKey
         * @return A
         */
        public function resolveRawValueByDisplayAttributeKey($displayAttributeKey)
        {
            assert('is_int($displayAttributeKey)');
            if (null != $model = $this->resolveModel($displayAttributeKey))
            {
                return $this->resolveRawValueByModel($this->displayAttributes[$displayAttributeKey], $model);
            }
            //Not using isset, because a null value would not resolve correctly
            $columnAliasName = $this->displayAttributes[$displayAttributeKey]->columnAliasName;
            if (array_key_exists($columnAliasName, $this->selectedColumnNamesAndValues))
            {
                return $this->selectedColumnNamesAndValues[$columnAliasName];
            }
            throw new NotSupportedException();
        }

        /**
         * @param string $attributeAlias
         * @return bool
         */
        protected function shouldResolveValueFromModel($attributeAlias)
        {
            $parts = explode(self::ATTRIBUTE_NAME_PREFIX, $attributeAlias);
            if (count($parts) == 2 && $parts[1] != null)
            {
                return true;
            }
            return false;
        }

        /**
         * @param $displayAttributeKey
         * @return null
         * @throws NotSupportedException if the key specified does not exist
         */
        protected function resolveModel($displayAttributeKey)
        {
            if (!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            if (!isset($this->modelsByAliases[$modelAlias]))
            {
                return null;
            }
            return $this->getModelByAlias($modelAlias);
        }

        /**
         * @param string $displayAttributeKey
         * @return mixed $value
         * @throws NotSupportedException if the key specified does not exist
         */
        protected function resolveValueFromModel($displayAttributeKey)
        {
            if (!isset($this->displayAttributes[$displayAttributeKey]))
            {
                throw new NotSupportedException();
            }
            $displayAttribute = $this->displayAttributes[$displayAttributeKey];
            $modelAlias       = $displayAttribute->getModelAliasUsingTableAliasName();
            $attribute        = $displayAttribute->getResolvedAttributeRealAttributeName();
            if (!isset($this->modelsByAliases[$modelAlias]))
            {
                $defaultModelClassName = $displayAttribute->getResolvedAttributeModelClassName();
                $model = new $defaultModelClassName(false);
            }
            else
            {
                $model = $this->getModelByAlias($modelAlias);
            }
            return $this->resolveModelAttributeValueForPenultimateRelation($model, $attribute, $displayAttribute);
        }

        /**
         * @param RedBeanModel $model
         * @param string $attribute
         * @param DisplayAttributeForReportForm $displayAttribute
         * @return mixedg $value
         * @throws NotSupportedException
         */
        protected function resolveModelAttributeValueForPenultimateRelation(RedBeanModel $model, $attribute,
                                                                            DisplayAttributeForReportForm $displayAttribute)
        {
            if ($model->isAttribute($attribute))
            {
                return $model->$attribute;
            }
            $penultimateRelation = $displayAttribute->getPenultimateRelation();
            if (!$model->isAttribute($penultimateRelation))
            {
                throw new NotSupportedException();
            }
            return $model->$penultimateRelation->$attribute;
        }

        /**
         * @param DisplayAttributeForReportForm $displayAttribute
         * @param RedBeanModel $model
         * @return mixed $value
         */
        protected function resolveRawValueByModel(DisplayAttributeForReportForm $displayAttribute, RedBeanModel $model)
        {
            $type                 = $displayAttribute->getDisplayElementType();
            $attribute            = $displayAttribute->getResolvedAttribute();
            if ($type == 'CurrencyValue')
            {
                return $model->{$attribute}->value;
            }
            elseif ($type == 'User')
            {
                $realAttributeName = $displayAttribute->getResolvedAttributeRealAttributeName();
                return $model->{$realAttributeName}->id;
            }
            elseif ($type == 'DropDown')
            {
                return $model->{$attribute}->value;
            }
            elseif (null != $rawValueRelatedAttribute = $displayAttribute->getRawValueRelatedAttribute())
            {
                return $model->{$attribute}->{$rawValueRelatedAttribute};
            }
            else
            {
                return $this->resolveModelAttributeValueForPenultimateRelation($model, $attribute, $displayAttribute);
            }
        }

        /**
         * @param string $alias
         * @return RedBeanModel
         * @throws NotSupportedException if the $alias does not exist
         */
        protected function getModelByAlias($alias)
        {
            if (!isset($this->modelsByAliases[$alias]))
            {
                throw new NotSupportedException();
            }
            return $this->modelsByAliases[$alias];
        }
    }
?>