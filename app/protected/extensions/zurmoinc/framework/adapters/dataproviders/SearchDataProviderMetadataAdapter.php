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
     * Adapter class to manipulate searchAttribute information into DataProvider metadata.
     * Takes either a RedBeanModel or a SearchForm model.
     */
    class SearchDataProviderMetadataAdapter extends DataProviderMetadataAdapter
    {
        /**
         * Override to make sure the model is a RedBeanModel or a SearchForm model.
         */
        public function __construct($model, $userId, $metadata)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            parent::__construct($model, $userId, $metadata);
        }

        /**
         * Convert metadata which is just an array
         * of posted searchAttributes into metadata that is
         * readable by the RedBeanModelDataProvider
         */
        public function getAdaptedMetadata($appendStructureAsAnd = true)
        {
            assert('is_bool($appendStructureAsAnd)');
            $adaptedMetadata = array('clauses' => array(), 'structure' => '');
            $clauseCount = 1;
            $structure = '';

            foreach ($this->metadata as $attributeName => $value)
            {
                //If attribute is a pseudo attribute on the SearchForm
                if ($this->model instanceof SearchForm && $this->model->isAttributeOnForm($attributeName))
                {
                    static::populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                             $value,
                                                                             $adaptedMetadata['clauses'],
                                                                             $clauseCount,
                                                                             $structure,
                                                                             $appendStructureAsAnd);
                }
                else
                {
                    static::populateClausesAndStructureForAttribute($attributeName,
                                                                    $value,
                                                                    $adaptedMetadata['clauses'],
                                                                    $clauseCount,
                                                                    $structure,
                                                                    $appendStructureAsAnd);
                }
            }
            $adaptedMetadata['structure'] = $structure;
            return $adaptedMetadata;
        }

        /**
         * $param $appendStructureAsAnd - true/false. If false, then the structure will be appended as OR.
         */
        protected function populateClausesAndStructureForAttribute( $attributeName,
                                                                    $value,
                                                                    &$adaptedMetadataClauses,
                                                                    &$clauseCount,
                                                                    &$structure,
                                                                    $appendStructureAsAnd = true,
                                                                    $operatorType = null)
        {
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            assert('is_bool($appendStructureAsAnd)');
            if (!is_array($value))
            {
                if ($value !== null)
                {
                    if ($operatorType == null)
                    {
                        $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType($this->model, $attributeName);
                    }
                    $value        = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                        $this->model, $attributeName, $value);
                    $mixedType    = ModelAttributeToMixedTypeUtil::getType(
                                        $this->model, $attributeName);
                    static::
                    resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                    $value,
                                                                                    $operatorType);

                    $adaptedMetadataClauses[($clauseCount)] = array(
                        'attributeName' => $attributeName,
                        'operatorType'  => $operatorType,
                        'value'         => $value,
                    );
                    if ($appendStructureAsAnd)
                    {
                        static::appendClauseAsAndToStructureString($structure, $clauseCount);
                    }
                    else
                    {
                        static::appendClauseAsOrToStructureString($structure, $clauseCount);
                    }
                    $clauseCount++;
                }
            }
            //An array of metadata doesn't always mean the attribute is a relation attribute.
            //todo: refactor into a 'rules' pattern.
            elseif (!$this->model->isRelation($attributeName))
            {
                if (isset($value['value']) && $value['value'] != '')
                {
                    if ($operatorType == null)
                    {
                        $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType(
                                            $this->model, $attributeName);
                    }
                    $value     = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                        $this->model, $attributeName, $value['value']);

                    $mixedType = ModelAttributeToMixedTypeUtil::getType(
                                        $this->model, $attributeName);
                    static::
                    resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                    $value,
                                                                                    $operatorType);
                    $adaptedMetadataClauses[($clauseCount)] = array(
                        'attributeName' => $attributeName,
                        'operatorType'  => $operatorType,
                        'value'         => $value,
                    );
                    if ($appendStructureAsAnd)
                    {
                        static::appendClauseAsAndToStructureString($structure, $clauseCount);
                    }
                    else
                    {
                        static::appendClauseAsOrToStructureString($structure, $clauseCount);
                    }
                    $clauseCount++;
                }
            }
            else
            {
                //todo!!! if we move the search form fork , here we can eliminate some things.
                foreach ($value as $relatedAttributeName => $relatedValue)
                {
                    if (is_array($relatedValue))
                    {
                        if (isset($relatedValue['value']) && $relatedValue['value'] != '')
                        {
                            $relatedValue = $relatedValue['value'];
                        }
                        elseif ($this->model->$attributeName->$relatedAttributeName instanceof RedBeanModels &&
                               is_array($relatedValue) && count($relatedValue) > 0)
                        {
                            //Continue on using relatedValue as is.
                        }
                        else
                        {
                            break;
                        }
                    }
                    if ($relatedValue !== null)
                    {
                        if ($this->model->isRelation($attributeName))
                        {
                            if ($operatorType == null)
                            {
                                $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType(
                                                $this->model->$attributeName, $relatedAttributeName);
                            }
                            $relatedValue  = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                                $this->model->$attributeName, $relatedAttributeName, $relatedValue);
                            if ($this->model->$attributeName instanceof RedBeanModel)
                            {
                                $mixedType = ModelAttributeToMixedTypeUtil::getType(
                                                    $this->model->$attributeName, $relatedAttributeName);
                                static::
                                resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                                $relatedValue,
                                                                                                $operatorType);
                            }
                            $adaptedMetadataClauses[($clauseCount)] = array(
                                'attributeName'        => $attributeName,
                                'relatedAttributeName' => $relatedAttributeName,
                                'operatorType'         => $operatorType,
                                'value'                => $relatedValue,
                            );
                            if ($appendStructureAsAnd)
                            {
                                static::appendClauseAsAndToStructureString($structure, $clauseCount);
                            }
                            else
                            {
                                static::appendClauseAsOrToStructureString($structure, $clauseCount);
                            }
                            $clauseCount++;
                        }
                        else
                        {
                            throw new NotSupportedException();
                        }
                    }
                }
            }
        }

        /**
         * Method for populating clauses for concated attributes.  The first concated attribute $attributeNames[0]
         * will be used to determine the operator types.
         */
        protected function populateClausesAndStructureForConcatedAttributes($attributeNames,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure,
                                                                            $appendStructureAsAnd = true,
                                                                            $operatorType = null)
        {
            assert('is_array($attributeNames) && count($attributeNames) == 2');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            assert('is_bool($appendStructureAsAnd)');
            if ($value !== null)
            {
                if ($operatorType == null)
                {
                    $operatorType        = ModelAttributeToOperatorTypeUtil::getOperatorType($this->model, $attributeNames[0]);
                    $operatorTypeCompare = ModelAttributeToOperatorTypeUtil::getOperatorType($this->model, $attributeNames[1]);
                    if ($operatorType != $operatorTypeCompare)
                    {
                        throw New NotSupportedException();
                    }
                }
                $value = ModelAttributeToCastTypeUtil::resolveValueForCast($this->model, $attributeNames[0], $value);
                $adaptedMetadataClauses[($clauseCount)] = array(
                    'concatedAttributeNames' => $attributeNames,
                    'operatorType'           => $operatorType,
                    'value'                  => $value,
                );
                if ($appendStructureAsAnd)
                {
                    static::appendClauseAsAndToStructureString($structure, $clauseCount);
                }
                else
                {
                    static::appendClauseAsOrToStructureString($structure, $clauseCount);
                }
                $clauseCount++;
            }
        }

        protected function populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure,
                                                                            $appendStructureAsAnd = true)
        {
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            assert('is_bool($appendStructureAsAnd)');
            $tempStructure = null;
            $metadataFromSearchFormAttributes = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata(
                                                $this->model, $attributeName, $value);
            foreach ($metadataFromSearchFormAttributes as $searchFormClause)
            {
                if (isset($searchFormClause['concatedAttributeNames']))
                {
                    assert('is_array($searchFormClause["concatedAttributeNames"][0]) &&
                             count($searchFormClause["concatedAttributeNames"][0]) == 2');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["operatorType"])');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["appendStructureAsAnd"])');
                    static::populateClausesAndStructureForConcatedAttributes($searchFormClause['concatedAttributeNames'][0],
                                                                             $searchFormClause['concatedAttributeNames']['value'],
                                                                             $adaptedMetadataClauses,
                                                                             $clauseCount,
                                                                             $tempStructure,
                                                                             false);
                }
                else
                {
                    foreach ($searchFormClause as $searchFormAttributeName => $searchFormStructure)
                    {
                        if (isset($searchFormStructure['operatorType']))
                        {
                            $operatorType = $searchFormStructure['operatorType'];
                        }
                        else
                        {
                            $operatorType = null;
                        }
                        if (isset($searchFormStructure['appendStructureAsAnd']))
                        {
                            $appendTempStructureAsAnd = $searchFormStructure['appendStructureAsAnd'];
                        }
                        else
                        {
                            $appendTempStructureAsAnd = false;
                        }
                        static::populateClausesAndStructureForAttribute($searchFormAttributeName,
                                                                        $searchFormStructure['value'],
                                                                        $adaptedMetadataClauses,
                                                                        $clauseCount,
                                                                        $tempStructure,
                                                                        $appendTempStructureAsAnd,
                                                                        $operatorType);
                    }
                }
            }
            if ($tempStructure != null)
            {
                $tempStructure = '(' . $tempStructure . ')';
                if ($appendStructureAsAnd)
                {
                    static::appendClauseAsAndToStructureString($structure, $tempStructure);
                }
                else
                {
                    static::appendClauseAsOrToStructureString($structure, $tempStructure);
                }
            }
        }

        protected static function appendClauseAsAndToStructureString(& $structure, $clause)
        {
            assert('$structure == null || is_string($structure)');
            assert('$clause != null || (is_string($clause) || is_int(clause))');
            if (!empty($structure))
            {
                $structure .= ' and ' . $clause;
            }
            else
            {
                $structure .= $clause;
            }
        }

        protected static function appendClauseAsOrToStructureString(& $structure, $clause)
        {
            assert('$structure == null || is_string($structure)');
            assert('$clause != null || (is_string($clause) || is_int(clause))');
            if (!empty($structure))
            {
                $structure .= ' or ' . $clause;
            }
            else
            {
                $structure .= $clause;
            }
        }

        protected static function resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($type, & $value,
                                                                                                  & $operatorType)
        {
            assert('is_string($type)');
            assert('is_string($operatorType)');
            if ($type == 'CheckBox' && ($value == '0' || !$value))
            {
                $operatorType = 'doesNotEqual';
                $value        = (bool)1;
            }
        }
    }
?>