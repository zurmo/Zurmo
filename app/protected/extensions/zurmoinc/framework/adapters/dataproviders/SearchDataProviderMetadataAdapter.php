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
        public function getAdaptedMetadata()
        {
            $adaptedMetadata = array('clauses' => array(), 'structure' => '');
            $clauseCount = 1;
            $structure = '';

            foreach ($this->metadata as $attributeName => $value)
            {
                //If attribute is a pseudo attribute on the SearchForm
                if ($this->model instanceof SearchForm && property_exists(get_class($this->model), $attributeName))
                {
                    static::populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                             $value,
                                                                             $adaptedMetadata['clauses'],
                                                                             $clauseCount,
                                                                             $structure);
                }
                else
                {
                    static::populateClausesAndStructureForAttribute($attributeName,
                                                                    $value,
                                                                    $adaptedMetadata['clauses'],
                                                                    $clauseCount,
                                                                    $structure);
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
                                                                    $appendStructureAsAnd = true)
        {
            if (!is_array($value))
            {
                if ($value !== null)
                {
                    //todo!!! if we move the search form fork , here we can eliminate some things.
                    $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType(
                                        $this->model, $attributeName);
                    $value        = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                        $this->model, $attributeName, $value);
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
                if (isset($value['value']))
                {
                    //todo!!! if we move the search form fork , here we can eliminate some things.
                    $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType(
                                        $this->model, $attributeName);
                    $value        = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                        $this->model, $attributeName, $value['value']);
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
                    if ($relatedValue !== null)
                    {
                        if ($this->model->isRelation($attributeName))
                        {
                            $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType(
                                                $this->model->$attributeName, $relatedAttributeName);
                            $relatedValue = ModelAttributeToCastTypeUtil::resolveValueForCast(
                                                $this->model->$attributeName, $relatedAttributeName, $relatedValue);
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

        protected function populateAdaptedMetadataFromSearchFormAttributes( $attributeName,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure)
        {
            $tempStructure = null;
            $metadataFromSearchFormAttributes = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata(
                                                    $this->model, $attributeName, $value);
            foreach ($metadataFromSearchFormAttributes as $searchFormAttributeName => $searchFormValue)
            {
                static::populateClausesAndStructureForAttribute($searchFormAttributeName,
                                                                $searchFormValue,
                                                                $adaptedMetadataClauses,
                                                                $clauseCount,
                                                                $tempStructure,
                                                                false);
            }
            $tempStructure = '(' . $tempStructure . ')';
            if (!empty($structure))
            {
                $structure .= ' and ' . $tempStructure;
            }
            else
            {
                $structure .= $tempStructure;
            }
        }

        protected static function appendClauseAsAndToStructureString(& $structure, $clauseCount)
        {
            if (!empty($structure))
            {
                $structure .= ' and ' . $clauseCount;
            }
            else
            {
                $structure .= $clauseCount;
            }
        }

        protected static function appendClauseAsOrToStructureString(& $structure, $clauseCount)
        {
            if (!empty($structure))
            {
                $structure .= ' or ' . $clauseCount;
            }
            else
            {
                $structure .= $clauseCount;
            }
        }
    }
?>