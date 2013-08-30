<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Base Class for all searchForms that are module specific. This for is to be used if your module form
     * needs to be adapted in the SearchDataProviderMetadataAdapter
     */
    abstract class SearchForm extends ModelForm
    {
        const ANY_MIXED_ATTRIBUTES_SCOPE_NAME       = 'anyMixedAttributesScope';

        const SELECTED_LIST_ATTRIBUTES              = 'selectedListAttributes';

        private $dynamicAttributeData;

        private $dynamicAttributeNames = array();

        private $attributeNamesThatCanBeSplitUsingDelimiter = array();

        private $supportsMixedSearch;

        /**
         * String of search content to search on a mixed set of attributes scoped by
         * @see $anyMixedAttributesScope
         * @var string
         */
        public  $anyMixedAttributes;

        /**
         * Array of attributes to only use for @see $anyMixedAttributes data.  Or set as
         * null if nothing specifically scoped on.
         * @var array or null
         */
        private $anyMixedAttributesScope;

        /**
         * An object to assist with selecting specific list attributes to display after each search is run.
         * @var ListAttributesSelector
         */
        private $listAttributesSelector;

        /**
         * True to scope data by starred only
         * @var boolean
         */
        public $filterByStarred;

        /**
         * When utilized, shows a kanban board view instead of a listview
         * @var null | object KanbanBoard
         */
        private $kanbanBoard;

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

        protected function getModelModuleClassName()
        {
            return $this->model->getModuleClassName();
        }

        protected function addAttributeNamesThatCanBeSplitUsingDelimiter($value)
        {
            $this->attributeNamesThatCanBeSplitUsingDelimiter[] = $value;
        }

        public function setAnyMixedAttributesScope($anyMixedAttributesScope)
        {
            $this->anyMixedAttributesScope = $anyMixedAttributesScope;
        }

        public function getAnyMixedAttributesScope()
        {
            return $this->anyMixedAttributesScope;
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
            $rules = array_merge(parent::rules(), $dynamicAttributeRules);
            return array_merge($rules, $this->getMixedSearchRules());
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
            $attributeLabels = array_merge(parent::attributeLabels(), $dynamicAttributeLabels);
            return array_merge($attributeLabels, $this->getMixedSearchAttributeLabels());
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::__set()
         */
        public function __set($name, $value)
        {
            if (static::doesNameResolveNameForDelimiterSplit($name))
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
            if (static::doesNameResolveNameForDelimiterSplit($name))
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
            if (static::doesNameResolveNameForDelimiterSplit($attributeName))
            {
                return true;
            }
            return false;
        }

        /**
         * @return true if the provided attribute is searchable and not just a special form property.
         */
        public static function isAttributeSearchable($attributeName)
        {
            if (in_array($attributeName, static::getNonSearchableAttributes()))
            {
                return false;
            }
            return true;
        }

        public static function getNonSearchableAttributes()
        {
            return array(self::ANY_MIXED_ATTRIBUTES_SCOPE_NAME,
                         self::SELECTED_LIST_ATTRIBUTES,
                         KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES,
                         KanbanBoard::SELECTED_THEME,
                         'filterByStarred');
        }

        public function getSearchableAttributes()
        {
            $searchableAttributes    = array();
            foreach ($this->getAttributes() as $attributeName => $notUsed)
            {
                if (!in_array($attributeName, static::getNonSearchableAttributes()))
                {
                    $searchableAttributes[$attributeName] = $notUsed;
                }
            }
            return $searchableAttributes;
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
            foreach ($this->attributeNamesThatCanBeSplitUsingDelimiter as $attributeName)
            {
                $delimiter                      = FormModelUtil::DELIMITER;
                list($realAttributeName, $type) = explode($delimiter, $attributeName);
                assert('$dynamicAttributeToElementTypes[$type] != null');
                $metadata[get_called_class()]['elements'][$attributeName] = $dynamicAttributeToElementTypes[$type];
            }
            //add something to resolve for global search....
            $this->resolveMixedSearchAttributeElementForMetadata($metadata[get_called_class()]['elements']);
            return $metadata;
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::isRelation()
         */
        public static function isRelation($attributeName)
        {
            if (static::doesNameResolveNameForDelimiterSplit($attributeName))
            {
                return false;
            }
            return parent::isRelation($attributeName);
        }

        /**
         * (non-PHPdoc)
         * @see ModelForm::getRelationModelClassName()
         */
        public static function getRelationModelClassName($relationName)
        {
            if (static::doesNameResolveNameForDelimiterSplit($relationName))
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
            if (static::doesNameResolveNameForDelimiterSplit($attributeName))
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
            if (static::doesNameResolveNameForDelimiterSplit($attribute))
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
            $nonDynamicAttributeValues = array();
            foreach ($values as $name => $value)
            {
                if (static::doesNameResolveNameForDelimiterSplit($name))
                {
                    $this->$name = $value;
                }
                else
                {
                    $nonDynamicAttributeValues[$name] = $value;
                }
            }
            //Dropdowns can be searched on as mulit-selects.  This below foreach resolves the issue of needing to show
            //multiple values in the dropdown.
            foreach ($values as $name => $value)
            {
                $modelClassName = get_class($this->model);
                if ($value != null && $this->model->isAttribute($name) && $modelClassName::isRelation($name))
                {
                    $relationModelClassName = $modelClassName::getRelationModelClassName($name);
                    if (($relationModelClassName == 'CustomField' ||
                       is_subclass_of($relationModelClassName, 'CustomField') && isset($value['value']) &&
                       is_array($value['value']) && count($value['value']) > 0))
                    {
                        $this->model->$name->value = $value['value'];
                    }
                }
            }
            parent::setAttributes($nonDynamicAttributeValues, $safeOnly);
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

        private function supportsMixedSearch()
        {
            if ($this->supportsMixedSearch === null)
            {
                $this->supportsMixedSearch = false;
                $moduleClassName = $this->model->getModuleClassName();
                if ($moduleClassName != null && $moduleClassName::getGlobalSearchFormClassName() != null)
                {
                    $this->supportsMixedSearch  = true;
                }
            }
            return $this->supportsMixedSearch;
        }

        private function getMixedSearchRules()
        {
            if ($this->supportsMixedSearch())
            {
                return array(array('anyMixedAttributes', 'safe'));
            }
            return array();
        }

        private function getMixedSearchAttributeLabels()
        {
            if ($this->supportsMixedSearch())
            {
                return array('anyMixedAttributes' => Zurmo::t('Core', 'Basic Search Fields'));
            }
            return array();
        }

        /**
         * Resolves a mixed attribute search by filtering out any attributes not part of the scope.
         * @param unknown_type $realAttributesMetadata
         */
        public function resolveMixedSearchAttributeMappedToRealAttributesMetadata(& $realAttributesMetadata)
        {
            assert('is_array($realAttributesMetadata)');
            if ($this->supportsMixedSearch())
            {
                $moduleClassName            = $this->model->getModuleClassName();
                $metadata                   = $moduleClassName::getMetadata();
                $data                       = array('anyMixedAttributes' => array());
                if (isset($metadata['global']['globalSearchAttributeNames']) &&
                    $metadata['global']['globalSearchAttributeNames'] != null)
                {
                    foreach ($metadata['global']['globalSearchAttributeNames'] as $attributeName)
                    {
                        if ($this->anyMixedAttributesScope == null ||
                           in_array($attributeName, $this->anyMixedAttributesScope))
                        {
                            if (!isset($realAttributesMetadata[$attributeName]))
                            {
                                $data['anyMixedAttributes'][] = array($attributeName);
                            }
                            elseif (isset($realAttributesMetadata[$attributeName]) &&
                                   is_array($realAttributesMetadata[$attributeName]))
                            {
                                foreach ($realAttributesMetadata[$attributeName] as $mixedAttributeMetadata)
                                {
                                    $data['anyMixedAttributes'][] = $mixedAttributeMetadata;
                                }
                            }
                            else
                            {
                                throw new NotSupportedException();
                            }
                        }
                    }
                }
                $realAttributesMetadata = array_merge($realAttributesMetadata, $data);
            }
        }

        protected function resolveMixedSearchAttributeElementForMetadata(& $metadata)
        {
            if ($this->supportsMixedSearch())
            {
                $metadata['anyMixedAttributes'] = 'AnyMixedAttributesSearch';
            }
        }

        /**
         * @return array of attributeName and label pairings.  Based on what attributes are used
         * in a mixed attribute search.
         */
        public function getGlobalSearchAttributeNamesAndLabelsAndAll()
        {
            $namesAndLabels = array();
            if ($this->supportsMixedSearch())
            {
                $moduleClassName            = $this->getModelModuleClassName();
                $metadata                   = $moduleClassName::getMetadata();
                if ($metadata['global']['globalSearchAttributeNames'] != null)
                {
                    foreach ($metadata['global']['globalSearchAttributeNames'] as $attributeName)
                    {
                        if ($this->isAttribute($attributeName))
                        {
                            $namesAndLabels[$attributeName] = $this->getAttributeLabel($attributeName);
                        }
                        else
                        {
                            $namesAndLabels[$attributeName] = $this->model->getAttributeLabel($attributeName);
                        }
                    }
                }
            }
            else
            {
                throw new NotSupportedException();
            }
            return array_merge(array('All' => Zurmo::t('Core', 'All')), $namesAndLabels);
        }

        /**
         * @see ListAttributesSelector class
         */
        public function setListAttributesSelector(ListAttributesSelector $listAttributesSelector)
        {
            $this->listAttributesSelector = $listAttributesSelector;
        }

        /**
         * @see ListAttributesSelector class
         */
        public function getListAttributesSelector()
        {
            return $this->listAttributesSelector;
        }

        /**
         * @see KanbanBoard class
         */
        public function setKanbanBoard(KanbanBoard $kanbanBoard)
        {
            $this->kanbanBoard = $kanbanBoard;
        }

        /**
         * @return null|object KanbanBoard
         */
        public function getKanbanBoard()
        {
            return $this->kanbanBoard;
        }
    }
?>
