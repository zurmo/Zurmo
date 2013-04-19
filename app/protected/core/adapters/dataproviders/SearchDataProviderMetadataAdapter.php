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
     * Adapter class to manipulate searchAttribute information into DataProvider metadata.
     * Takes either a RedBeanModel or a SearchForm model.
     */
    class SearchDataProviderMetadataAdapter extends DataProviderMetadataAdapter
    {
        protected $appendStructureAsAnd;

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
         * @param $appendStructureAsAnd - true/false. If false, then the structure will be appended as OR.
         */
        public function getAdaptedMetadata($appendStructureAsAnd = true, $clauseCount = 1)
        {
            assert('is_bool($appendStructureAsAnd)');
            assert('is_int($clauseCount)');
            $this->appendStructureAsAnd = $appendStructureAsAnd;
            $adaptedMetadata = array('clauses' => array(), 'structure' => '');
            $structure = '';
            foreach ($this->metadata as $attributeName => $value)
            {
                //If attribute is a pseudo attribute on the SearchForm
                if ($this->model instanceof SearchForm && $this->model->isAttributeOnForm($attributeName))
                {
                    $this->populateAdaptedMetadataFromSearchFormAttributes( $this->model,
                                                                            $attributeName,
                                                                            $value,
                                                                            $adaptedMetadata['clauses'],
                                                                            $clauseCount,
                                                                            $structure);
                }
                else
                {
                    $this->populateClausesAndStructureForAttribute( $this->model,
                                                                    $attributeName,
                                                                    $value,
                                                                    $adaptedMetadata['clauses'],
                                                                    $clauseCount,
                                                                    $structure);
                }
            }
            if (!$appendStructureAsAnd)
            {
                $structure = '(' . $structure . ')';
            }
            $adaptedMetadata['structure'] = $structure;
            return $adaptedMetadata;
        }

        protected function populateClausesAndStructureForAttributeWithRelatedModelData( $model,
                                                                                        $relatedMetaData,
                                                                                        $adaptedMetadataClauseBasePart,
                                                                                        & $adaptedMetadataClauses,
                                                                                        & $clauseCount,
                                                                                        & $structure,
                                                                                        $depth,
                                                                                        $operatorType = null)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            assert('is_array($relatedMetaData)');
            assert('is_int($depth)');
            $startingOperatorType = $operatorType;
            foreach ($relatedMetaData as $attributeName => $value)
            {
                //If attribute is a pseudo attribute on the SearchForm
                if ($model instanceof SearchForm && $model->isAttributeOnForm($attributeName))
                {
                    $this->populateAdaptedMetadataFromSearchFormAttributes( $model,
                                                                            $attributeName,
                                                                            $value,
                                                                            $adaptedMetadataClauses,
                                                                            $clauseCount,
                                                                            $structure,
                                                                            $adaptedMetadataClauseBasePart,
                                                                            $depth);
                }
                else
                {
                    $this->populateClausesAndStructureForAttribute($model,
                                                                   $attributeName,
                                                                   $value,
                                                                   $adaptedMetadataClauses,
                                                                   $clauseCount,
                                                                   $structure,
                                                                   $adaptedMetadataClauseBasePart,
                                                                   $operatorType,
                                                                   $depth);
                }
            }
        }

        protected function populateClausesAndStructureForAttribute( $model,
                                                                    $attributeName,
                                                                    $value,
                                                                    & $adaptedMetadataClauses,
                                                                    & $clauseCount,
                                                                    & $structure,
                                                                    $adaptedMetadataClauseBasePart = array(),
                                                                    $operatorType = null,
                                                                    $depth = 0)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            assert('is_int($depth)');
            $basePartAtRequiredDepth = static::
                                       getAdaptedMetadataClauseBasePartAtRequiredDepth($adaptedMetadataClauseBasePart, $depth);
            //non-relation attribute that has single data value
            if (!is_array($value))
            {
                if ($value !== null)
                {
                    $currentClauseCount = $clauseCount;
                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(  $model,
                                                                                           $attributeName,
                                                                                           $operatorType,
                                                                                           $value,
                                                                                           $basePartAtRequiredDepth,
                                                                                           $structure,
                                                                                           $clauseCount);
                    $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                $adaptedMetadataClauseBasePart,
                                                                                $basePartAtRequiredDepth,
                                                                                $depth);
                }
            }
            //non-relation attribute that has array of data
            elseif (!$model::isRelation($attributeName))
            {
                if (isset($value['value']) && $value['value'] != '')
                {
                    $currentClauseCount                         = $clauseCount;
                    $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(   $model,
                                                                                           $attributeName,
                                                                                           $operatorType,
                                                                                           $value['value'],
                                                                                           $basePartAtRequiredDepth,
                                                                                           $structure,
                                                                                           $clauseCount);
                    $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                $adaptedMetadataClauseBasePart,
                                                                                $basePartAtRequiredDepth,
                                                                                $depth);
                }
            }
            //relation attribute that is relatedData
            elseif (isset($value['relatedData']) && $value['relatedData'] == true)
            {
                $partToAppend                    = array('attributeName'    => $attributeName,
                                                         'relatedModelData' => array());
                $appendedClauseToPassRecursively = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                $adaptedMetadataClauseBasePart,
                                                                $partToAppend,
                                                                $depth);
                unset($value['relatedData']);

                $finalModel = static::resolveAsRedBeanModel($model->$attributeName);
                if ($finalModel::getModuleClassName() != null)
                {
                    $moduleClassName = $finalModel::getModuleClassName();
                    if ($moduleClassName::getGlobalSearchFormClassName() != null)
                    {
                        $searchFormClassName = $moduleClassName::getGlobalSearchFormClassName();
                        $finalModel          = new $searchFormClassName($finalModel);
                    }
                }
                $this->populateClausesAndStructureForAttributeWithRelatedModelData(
                    $finalModel,
                    $value,
                    $appendedClauseToPassRecursively,
                    $adaptedMetadataClauses,
                    $clauseCount,
                    $structure,
                    ($depth + 1),
                    $operatorType);
            }
            //relation attribute that has array of data
            else
            {
                foreach ($value as $relatedAttributeName => $relatedValue)
                {
                    $currentClauseCount = $clauseCount;
                    if (static::resolveRelatedValueWhenArray( $model->$attributeName,
                                                             $relatedAttributeName,
                                                             $relatedValue,
                                                             $operatorType))
                    {
                        if ($relatedValue !== null)
                        {
                            if ($model::isRelation($attributeName))
                            {
                                $this->resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(
                                                                                               $model->$attributeName,
                                                                                               $relatedAttributeName,
                                                                                               $operatorType,
                                                                                               $relatedValue,
                                                                                               $basePartAtRequiredDepth,
                                                                                               $structure,
                                                                                               $clauseCount,
                                                                                               $attributeName);
                                $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                            $adaptedMetadataClauseBasePart,
                                                                                            $basePartAtRequiredDepth,
                                                                                            $depth);
                            }
                            else
                            {
                                throw new NotSupportedException();
                            }
                        }
                    }
                }
            }
        }

        protected static function resolveRelatedValueWhenArray($model,
                                                               $relatedAttributeName,
                                                               & $relatedValue,
                                                               & $operatorType)
        {
            if (is_array($relatedValue))
            {
                if (isset($relatedValue['value']) && $relatedValue['value'] != '')
                {
                    $relatedValue = $relatedValue['value'];
                }
                elseif (isset($relatedValue['value']) && empty($relatedValue['value']) &&
                        ModelAttributeToMixedTypeUtil::getType($model, $relatedAttributeName) == 'CheckBox')
                {
                    //Boolean field with an empty value means there is no clause to include
                    return false;
                }
                //Run this before the next set of elseifs to make this scenario is properly checked
                elseif ($model instanceof MultipleValuesCustomField && count($relatedValue) > 0)
                {
                    if (count($relatedValue) == 1 && $relatedValue[0] == null)
                    {
                        return false;
                    }
                }
                elseif ($model instanceof MultipleValuesCustomField && count($relatedValue) == 0)
                {
                    return false;
                }
                elseif (($model instanceof RedBeanManyToManyRelatedModels ||
                        $model instanceof RedBeanOneToManyRelatedModels ) &&
                       is_array($relatedValue) && count($relatedValue) > 0)
                {
                    //Continue on using relatedValue as is.
                }
                elseif ($model->$relatedAttributeName instanceof RedBeanModels &&
                       is_array($relatedValue) && count($relatedValue) > 0)
                {
                    //Continue on using relatedValue as is.
                }
                elseif ($model instanceof CustomField && count($relatedValue) > 0)
                {
                    //Handle scenario where the UI posts or sends a get string with an empty value from
                    //a multi-select field.
                    if (count($relatedValue) == 1 && $relatedValue[0] == null)
                    {
                        return false;
                    }
                    //Continue on using relatedValue as is.
                    if ($operatorType == null)
                    {
                        $operatorType = 'oneOf';
                    }
                }
            }
            return true;
        }

        public static function resolveAsRedBeanModel($model)
        {
            if ($model instanceof RedBeanOneToManyRelatedModels || $model instanceof RedBeanManyToManyRelatedModels)
            {
                $relationModelClassName = $model->getModelClassName();
                return new $relationModelClassName(false);
            }
            else
            {
                return $model;
            }
        }

        protected function resolveOperatorAndCastsAndAppendClauseAsAndToStructureString(   $model,
                                                                                           $attributeName,
                                                                                           $operatorType,
                                                                                           $value,
                                                                                           & $adaptedMetadataClause,
                                                                                           & $structure,
                                                                                           & $clauseCount,
                                                                                           $previousAttributeName = null)
        {
            assert('$previousAttributeName == null || is_string($previousAttributeName)');
            $modelForTypeOperations = static::resolveAsRedBeanModel($model);
            if ($operatorType == null)
            {
                $operatorType = ModelAttributeToOperatorTypeUtil::getOperatorType($modelForTypeOperations, $attributeName);
            }
            if (is_array($value) && $model instanceof CustomField)
            {
                //do nothing, the cast is fine as is. Maybe eventually remove this setting of cast.
            }
            else
            {
                $value        = ModelAttributeToCastTypeUtil::resolveValueForCast($modelForTypeOperations, $attributeName, $value);
            }
            if ($model instanceof RedBeanModel)
            {
                $mixedType = ModelAttributeToMixedTypeUtil::getType($model, $attributeName);
                static::resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                        $value,
                                                                                        $operatorType);
            }
            elseif ($model instanceof SearchForm)
            {
                $mixedType = ModelAttributeToMixedTypeUtil::getType($model->getModel(), $attributeName);
                static::resolveBooleanFalseValueAndOperatorTypeForAdaptedMetadataClause($mixedType,
                                                                                        $value,
                                                                                        $operatorType);
            }
            if ($previousAttributeName == null)
            {
                $adaptedMetadataClause['attributeName']        = $attributeName;
            }
            else
            {
                $adaptedMetadataClause['attributeName']        = $previousAttributeName;
                $adaptedMetadataClause['relatedAttributeName'] = $attributeName;
            }
            $adaptedMetadataClause['operatorType']  = $operatorType;
            $adaptedMetadataClause['value']         = $value;
            $this->resolveAppendClauseAsAndToStructureString($structure,
                                                              $clauseCount);
        }

        protected function resolveAppendClauseAsAndToStructureString(& $structure, & $clauseCount)
        {
            if ($this->appendStructureAsAnd)
            {
                static::appendClauseAsAndToStructureString($structure, $clauseCount);
            }
            else
            {
                static::appendClauseAsOrToStructureString($structure, $clauseCount);
            }
            $clauseCount++;
        }

        /**
         * Method for populating clauses for concated attributes.  The first concated attribute $attributeNames[0]
         * will be used to determine the operator types.
         */
        protected function populateClausesAndStructureForConcatedAttributes($model,
                                                                            $attributeNames,
                                                                            $value,
                                                                            & $adaptedMetadataClauseBasePart,
                                                                            & $clauseCount,
                                                                            & $structure,
                                                                            $operatorType = null)
        {
            assert('is_array($attributeNames) && count($attributeNames) == 2');
            assert('is_array($adaptedMetadataClauseBasePart)');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            if ($value !== null)
            {
                if ($operatorType == null)
                {
                    $operatorType        = ModelAttributeToOperatorTypeUtil::getOperatorType($model, $attributeNames[0]);
                    $operatorTypeCompare = ModelAttributeToOperatorTypeUtil::getOperatorType($model, $attributeNames[1]);
                    if ($operatorType != $operatorTypeCompare)
                    {
                        throw New NotSupportedException();
                    }
                }
                $value = ModelAttributeToCastTypeUtil::resolveValueForCast($model, $attributeNames[0], $value);
                $adaptedMetadataClauseBasePart = array(
                    'concatedAttributeNames' => $attributeNames,
                    'operatorType'           => $operatorType,
                    'value'                  => $value,
                );
                if ($this->appendStructureAsAnd)
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

        protected function populateAdaptedMetadataFromSearchFormAttributes( $model,
                                                                            $attributeName,
                                                                            $value,
                                                                            &$adaptedMetadataClauses,
                                                                            &$clauseCount,
                                                                            &$structure,
                                                                            $adaptedMetadataClauseBasePart = array(),
                                                                            $depth = 0)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($attributeName)');
            assert('is_array($adaptedMetadataClauses) || $adaptedMetadataClauses == null');
            assert('is_int($clauseCount)');
            assert('$structure == null || is_string($structure)');
            $tempStructure = null;
            $metadataFromSearchFormAttributes = SearchFormAttributesToSearchDataProviderMetadataUtil::getMetadata(
                                                $model, $attributeName, $value);
            foreach ($metadataFromSearchFormAttributes as $searchFormClause)
            {
                if (isset($searchFormClause['concatedAttributeNames']))
                {
                    assert('is_array($searchFormClause["concatedAttributeNames"][0]) &&
                             count($searchFormClause["concatedAttributeNames"][0]) == 2');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["operatorType"])');
                    assert('!isset($searchFormClause["concatedAttributeNames"]["appendStructureAsAnd"])');
                    $oldAppendStructureAsAndValue = $this->appendStructureAsAnd;
                    $this->appendStructureAsAnd   = false;
                    $basePartAtRequiredDepth      = static::
                                                       getAdaptedMetadataClauseBasePartAtRequiredDepth(
                                                       $adaptedMetadataClauseBasePart, $depth);
                    $currentClauseCount           = $clauseCount;
                    if ($searchFormClause['concatedAttributeNames']['value'] != null)
                    {
                        $this->populateClausesAndStructureForConcatedAttributes( $model,
                                                                                 $searchFormClause['concatedAttributeNames'][0],
                                                                                 $searchFormClause['concatedAttributeNames']['value'],
                                                                                 $basePartAtRequiredDepth,
                                                                                 $clauseCount,
                                                                                 $tempStructure,
                                                                                 false);
                        $adaptedMetadataClauses[$currentClauseCount] = static::getAppendedAdaptedMetadataClauseBasePart(
                                                                                    $adaptedMetadataClauseBasePart,
                                                                                    $basePartAtRequiredDepth,
                                                                                    $depth);
                    }
                    $this->appendStructureAsAnd   = $oldAppendStructureAsAndValue;
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
                        //setting a temp value is not ideal. But it avoids passing the parameter.
                        $oldAppendStructureAsAndValue = $this->appendStructureAsAnd;
                        if (isset($searchFormStructure['appendStructureAsAnd']))
                        {
                            $this->appendStructureAsAnd = $searchFormStructure['appendStructureAsAnd'];
                        }
                        else
                        {
                            $this->appendStructureAsAnd = false;
                        }
                        $this->populateClausesAndStructureForAttribute( $model,
                                                                        $searchFormAttributeName,
                                                                        $searchFormStructure['value'],
                                                                        $adaptedMetadataClauses,
                                                                        $clauseCount,
                                                                        $tempStructure,
                                                                        $adaptedMetadataClauseBasePart,
                                                                        $operatorType,
                                                                        $depth);
                        $this->appendStructureAsAnd = $oldAppendStructureAsAndValue;
                    }
                }
            }
            if ($tempStructure != null)
            {
                $tempStructure = '(' . $tempStructure . ')';
                if ($this->appendStructureAsAnd)
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

        protected static function getAdaptedMetadataClauseBasePartAtRequiredDepth($adaptedMetadataClauseBasePart, $depth)
        {
            assert('is_array($adaptedMetadataClauseBasePart)');
            assert('is_int($depth)');
            if ($depth == 0)
            {
                return $adaptedMetadataClauseBasePart;
            }
            $finalPart = $adaptedMetadataClauseBasePart;
            for ($i = 0; $i < $depth; $i++)
            {
                $finalPart = $finalPart['relatedModelData'];
            }
            return $finalPart;
        }

        protected static function getAppendedAdaptedMetadataClauseBasePart($adaptedMetadataClauseBasePart, $partToAppend, $depth)
        {
            assert('is_array($adaptedMetadataClauseBasePart)');
            assert('is_array($partToAppend)');
            assert('is_int($depth)');
            if ($depth == 0)
            {
                return $partToAppend;
            }
            $finalPart = & $adaptedMetadataClauseBasePart;
            for ($i = 0; $i < $depth; $i++)
            {
                $finalPart = & $finalPart['relatedModelData'];
            }
            $finalPart = $partToAppend;
            return $adaptedMetadataClauseBasePart;
        }
    }
?>