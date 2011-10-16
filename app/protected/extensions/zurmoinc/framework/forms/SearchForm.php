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
     * Base Class for all searchForms that are module specific. This for is to be used if your module form
     * needs to be adapted in the SearchDataProviderMetadataAdapter
     */
    abstract class SearchForm extends ModelForm
    {
        private $dynamicAttributeData;

        private $dynamicAttributeNames = array();

        public function __construct(RedBeanModel $model)
        {
            parent::__construct($model);
            $modelAttributesAdapter = new ModelAttributesAdapter($this->model);
            $attributeInformation   = $modelAttributesAdapter->getAttributes();
            foreach ($attributeInformation as $attributeName => $attributeData)
            {
                if (in_array($attributeData['elementType'], static::getDynamicAttributeTypes()))
                {
                    $this->dynamicAttributeNames[] = $attributeName .
                                                     FormModelUtil::DELIMITER . $attributeData['elementType'];
                }
            }
        }

        /**
         * (non-PHPdoc)
         * @see CModel::rules()
         */
        public function rules()
        {
            $dynamicAttributeRules = array();
            foreach ($this->dynamicAttributeNames as $attributeName)
            {
                $dynamicAttributeRules[] = array($attributeName, 'safe');
            }
            return array_merge(parent::rules(), $dynamicAttributeRules);
        }

        /**
         * (non-PHPdoc)
         * @see CModel::attributeLabels()
         */
        public function attributeLabels()
        {
            $dynamicAttributeLabels = array();
            foreach ($this->dynamicAttributeNames as $attributeName)
            {
                $delimiter                              = FormModelUtil::DELIMITER;
                list($realAttributeName, $type)         = explode($delimiter, $attributeName);
                $dynamicAttributeLabels[$attributeName] = $this->model->getAttributeLabel($realAttributeName);
            }
            return array_merge(parent::attributeLabels(), $dynamicAttributeLabels);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::__set()
         */
        public function __set($name, $value)
        {
            if ($this->doesNameResolveNameForDelimiterSplit($name))
            {
                return $this->dynamicAttributeData[$name] = $value;
            }
            parent::__get($name);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::__get()
         */
        public function __get($name)
        {
            if ($this->doesNameResolveNameForDelimiterSplit($name))
            {
                return $this->dynamicAttributeData[$name];
            }
            return parent::__get($name);
        }

        /**
         * @return true if the provided attributeName is in fact an attribute on this form and not the $this->model.
         */
        public function isAttributeOnForm($attributeName)
        {
            if (property_exists($this, $attributeName))
            {
                return true;
            }
            if ($this->doesNameResolveNameForDelimiterSplit($attributeName))
            {
                return true;
            }
            return false;
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::getMetadata()
         */
        public function getMetadata()
        {
            $metadata = parent::getMetadata();
            $dynamicAttributeToElementTypes = static::getDynamicAttributeToElementTypes();
            foreach ($this->dynamicAttributeNames as $attributeName)
            {
                $delimiter                      = FormModelUtil::DELIMITER;
                list($realAttributeName, $type) = explode($delimiter, $attributeName);
                assert('$dynamicAttributeToElementTypes[$type] != null');
                $metadata[get_called_class()]['elements'][$attributeName] = $dynamicAttributeToElementTypes[$type];
            }
            return $metadata;
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::isRelation()
         */
        public function isRelation($attributeName)
        {
            if ($this->doesNameResolveNameForDelimiterSplit($attributeName))
            {
                return false;
            }
            return parent::isRelation($attributeName);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::getRelationModelClassName()
         */
        public function getRelationModelClassName($relationName)
        {
            if ($this->doesNameResolveNameForDelimiterSplit($relationName))
            {
                return false;
            }
            return parent::getRelationModelClassName($relationName);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::isAttribute()
         */
        public function isAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            assert('$attributeName != ""');
            if ($this->doesNameResolveNameForDelimiterSplit($attributeName))
            {
                return true;
            }
            return parent::isAttribute($attributeName);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::isAttributeRequired()
         */
        public function isAttributeRequired($attribute)
        {
            if ($this->doesNameResolveNameForDelimiterSplit($attribute))
            {
                return false;
            }
            return parent::isAttributeRequired($attribute);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::attributeNames()
         */
        public function attributeNames()
        {
            $attributeNames = parent::attributeNames();
            return array_merge($attributeNames, $this->dynamicAttributeNames);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::setAttributes()
         */
        public function setAttributes($values, $safeOnly = true)
        {
            $nonDyanmicAttributeValues = array();
            foreach ($values as $name => $value)
            {
                if ($this->doesNameResolveNameForDelimiterSplit($name))
                {
                    $this->$name = $value;
                }
                else
                {
                    $nonDyanmicAttributeValues[$name] = $value;
                }
            }
            parent::setAttributes($nonDyanmicAttributeValues, $safeOnly);
        }

        /**
         * Checks if the supplied name is a normal attribute, or a dynamic attribute which utilizes a delimiter in
         * the string to define two distinct values.  If the delimiter is present, but the format is invalid an exception
         * is thrown, otherwise it returns true.  If there is no delimiter present then it returns false.
         * @param string $name
         * @throws NotSupportedException
         * @return true/false
         */
        protected static function doesNameResolveNameForDelimiterSplit($name)
        {
            assert('is_string($name)');
            $delimiter                  = FormModelUtil::DELIMITER;
            $parts                      = explode($delimiter, $name);
            if (isset($parts[1]) && $parts[1] != null)
            {
                //also wanted to check for safety:
                //&& in_array($name, $this->dynamicAttributeNames) but that cant be done statically.
                if (in_array($parts[1], static::getDynamicAttributeTypes()))
                {
                    return true;
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            return false;
        }

        /**
         * @return array of available dyanamic attribute types.  Whatever is used in the name with a delimiter for the
         * second part, example: test__Date, must match a value in this array.
         */
        protected static function getDynamicAttributeTypes()
        {
            return array('Date', 'DateTime');
        }

        /**
         * @return array of dynamic attribute types as the indexes and their corresponding mapping rules as the values.
         */
        protected static function getDynamicAttributeToElementTypes()
        {
            return array('Date' => 'MixedDateTypesForSearch', 'DateTime' => 'MixedDateTypesForSearch');
        }

        /**
         * For each SearchForm attribute, there is either 1 or more corresponding model attributes. Specify this
         * information in this method as an array
         * @return array of metadata or null.
         */
        public function getAttributesMappedToRealAttributesMetadata()
        {
            $dynamicMappingData = array();
            foreach ($this->dynamicAttributeNames as $attributeName)
            {
                $dynamicMappingData[$attributeName] = 'resolveEntireMappingByRules';
            }
            return $dynamicMappingData;
        }

        /**
         * All search forms on validation would ignore required.  There are no required attributes on
         * a search form.  This is an override.
         */
        protected static function shouldIgnoreRequiredValidator()
        {
            return true;
        }

        /**
         * Override if any attributes support SearchFormAttributeMappingRules
         */
        protected static function getSearchFormAttributeMappingRulesTypes()
        {
            return array();
        }

        /**
         * Given an attributeName, return the corresponding rule type.
         * @param string $attributeName
         */
        public static function getSearchFormAttributeMappingRulesTypeByAttribute($attributeName)
        {
            assert('is_string($attributeName)');
            if (static::doesNameResolveNameForDelimiterSplit($attributeName))
            {
                $delimiter                      = FormModelUtil::DELIMITER;
                list($realAttributeName, $type) = explode($delimiter, $attributeName);
                if ($type == 'Date')
                {
                    return 'MixedDateTypes';
                }
                elseif ($type == 'DateTime')
                {
                    return 'MixedDateTimeTypes';
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                $ruleTypesIndexedByAttributeName = static::getSearchFormAttributeMappingRulesTypes();
                if (isset($ruleTypesIndexedByAttributeName[$attributeName]))
                {
                    return $ruleTypesIndexedByAttributeName[$attributeName];
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
        }
    }
?>