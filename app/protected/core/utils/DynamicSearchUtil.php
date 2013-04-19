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
     * Helper class for working with advanced search panel.
     */
    class DynamicSearchUtil
    {
        public static function getSearchableAttributesAndLabels($viewClassName, $modelClassName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            $searchFormClassName      = $viewClassName::getModelForMetadataClassName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );
            $attributeIndexOrDerivedTypeAndLabels = array();
            foreach ($attributesLayoutAdapter->makeDesignerLayoutAttributes()->get() as $attributeIndexOrDerivedType => $data)
            {
                //special case with anyMixedAttributes since it is searchable but in the basic search part so never dynamically searchable
                if ($searchFormClassName::isAttributeSearchable($attributeIndexOrDerivedType) && $attributeIndexOrDerivedType != 'anyMixedAttributes')
                {
                    $attributeIndexOrDerivedTypeAndLabels[$attributeIndexOrDerivedType] = $data['attributeLabel'];
                }
            }
            self::resolveAndAddViewDefinedNestedAttributes($modelAttributesAdapter->getModel(), $viewClassName, $attributeIndexOrDerivedTypeAndLabels);

            if (is_subclass_of($viewClassName, 'DynamicSearchView'))
            {
                $viewClassName::
                resolveAttributeIndexOrDerivedTypeAndLabelsForDynamicSearchRow($attributeIndexOrDerivedTypeAndLabels);
            }
            return $attributeIndexOrDerivedTypeAndLabels;
        }

        public static function getCellElement($viewClassName, $modelClassName, $elementName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName) && is_subclass_of($modelClassName, "RedBeanModel")');
            assert('is_string($elementName)');
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );

            $derivedAttributes         = $attributesLayoutAdapter->getAvailableDerivedAttributeTypes();
            $placeableLayoutAttributes = $attributesLayoutAdapter->getPlaceableLayoutAttributes();
            if (in_array($elementName, $derivedAttributes))
            {
                $element = array('attributeName' => 'null', 'type' => $elementName); // Not Coding Standard
            }
            elseif (isset($placeableLayoutAttributes[$elementName]) &&
                   $placeableLayoutAttributes[$elementName]['elementType'] == 'DropDownDependency')
            {
                throw new NotSupportedException();
            }
            elseif (isset($placeableLayoutAttributes[$elementName]))
            {
                $element = array(
                    'attributeName' => $elementName,
                    'type'          => $placeableLayoutAttributes[$elementName]['elementType']
                );
            }
            else
            {
                throw new NotSupportedException();
            }
            return $designerRules->formatSavableElement($element, $viewClassName);
        }

        public static function resolveAndAddViewDefinedNestedAttributes($model, $viewClassName, & $attributeIndexOrDerivedTypeAndLabels)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($viewClassName)');
            assert('is_array($attributeIndexOrDerivedTypeAndLabels)');
            $metadata = $viewClassName::getMetadata();
            if (isset($metadata['global']['definedNestedAttributes']))
            {
                foreach ($metadata['global']['definedNestedAttributes'] as $definedNestedAttribute)
                {
                    $attributeIndexOrDerivedLabel = null;
                    $attributeIndexOrDerivedType  = self::makeDefinedNestedAttributeIndexOrDerivedTypeRecursively(
                                                            $model,
                                                            $attributeIndexOrDerivedLabel,
                                                            $definedNestedAttribute);
                    if ($attributeIndexOrDerivedLabel == null)
                    {
                        throw new NotSupportedException();
                    }
                    $attributeIndexOrDerivedTypeAndLabels[$attributeIndexOrDerivedType] = $attributeIndexOrDerivedLabel;
                }
            }
        }

        protected static function makeDefinedNestedAttributeIndexOrDerivedTypeRecursively($model, & $attributeIndexOrDerivedLabel, $definedNestedAttribute)
        {
            assert('$model instanceof SearchForm || $model instanceof RedBeanModel');
            assert('is_string($attributeIndexOrDerivedLabel) || $attributeIndexOrDerivedLabel == null');
            assert('is_array($definedNestedAttribute)');
            if (count($definedNestedAttribute) > 1)
            {
                //Each defined attribute should be in its own sub-array.
                throw new NotSupportedException();
            }
            foreach ($definedNestedAttribute as $positionOrAttributeName => $nestedAttributeDataOrAttributeName)
            {
                if (is_array($nestedAttributeDataOrAttributeName))
                {
                    $attributeIndexOrDerivedLabel .= $model->getAttributeLabel($positionOrAttributeName) . ' - ';
                    $modelToUse      = SearchUtil::resolveModelToUseByModelAndAttributeName(
                                                $model,
                                                $positionOrAttributeName);
                    $string          = self::makeDefinedNestedAttributeIndexOrDerivedTypeRecursively(
                                                $modelToUse,
                                                $attributeIndexOrDerivedLabel,
                                                $nestedAttributeDataOrAttributeName);
                    return $positionOrAttributeName . FormModelUtil::RELATION_DELIMITER . $string;
                }
                else
                {
                    $attributeIndexOrDerivedLabel .= $model->getAttributeLabel($nestedAttributeDataOrAttributeName);
                    return $nestedAttributeDataOrAttributeName;
                }
            }
        }

        public static function renderDynamicSearchAttributeInput($viewClassName,
                                                                    $modelClassName,
                                                                    $formModelClassName,
                                                                    $rowNumber,
                                                                    $attributeIndexOrDerivedType,
                                                                    $searchAttributes = array(),
                                                                    $suffix = null)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($formModelClassName)');
            assert('is_int($rowNumber)');
            assert('is_string($attributeIndexOrDerivedType) || $attributeIndexOrDerivedType == null');
            assert('is_array($searchAttributes)');
            assert('is_string($suffix) || $suffix == null');
            $content          = null;
            if (count(explode(FormModelUtil::RELATION_DELIMITER, $attributeIndexOrDerivedType)) > 1)
            {
                $model            = new $modelClassName(false);
                $nestedAttributes = explode(FormModelUtil::RELATION_DELIMITER, $attributeIndexOrDerivedType);
                $inputPrefix      = array($formModelClassName, DynamicSearchForm::DYNAMIC_NAME, $rowNumber);
                $totalNestedCount = count($nestedAttributes);
                $processCount     = 1;
                $nestedSearchAttributes = $searchAttributes;

                foreach ($nestedAttributes as $attribute)
                {
                    if ($processCount < $totalNestedCount && isset($nestedSearchAttributes[$attribute]))
                    {
                        $nestedSearchAttributes = $nestedSearchAttributes[$attribute];
                        if (isset($nestedSearchAttributes['relatedData']))
                        {
                            unset($nestedSearchAttributes['relatedData']);
                        }
                    }
                    if ($processCount < $totalNestedCount)
                    {
                        $model           = SearchUtil::resolveModelToUseByModelAndAttributeName($model, $attribute);
                        $inputPrefix[]   = $attribute;
                        $relatedDataName = Element::resolveInputNamePrefixIntoString($inputPrefix) . '[relatedData]';
                        $content        .= ZurmoHtml::hiddenField($relatedDataName, true);
                    }
                    $processCount++;
                }
                $attributeIndexOrDerivedType = $attribute;
                $modelToUse                  = $model;
                $modelToUse->setAttributes($nestedSearchAttributes);
                $cellElementModelClassName   = get_class($model->getModel());
                //Dynamic Search needs to always assume there is an available SearchForm
                //Always assumes the SearchView to use matches the exact pluralCamelCasedName.
                //Does not support nested relations to leads persay.  It will resolve as a Contact.
                //This is not a problem since you can't relate a model to a lead, it is related to a contact.
                //So this scenario would not come up naturally.
                $moduleClassName             = $model->getModel()->getModuleClassName();
                $viewClassName               = $moduleClassName::getPluralCamelCasedName() . 'SearchView';
                $element                     = DynamicSearchUtil::getCellElement($viewClassName, $cellElementModelClassName,
                                                                                 $attributeIndexOrDerivedType);
            }
            else
            {
                $model                 = new $modelClassName(false);
                $model->setScenario('importModel'); //this is so attributes such as modified user can be set
                $modelToUse            = new $formModelClassName($model);
                $modelToUse->setAttributes($searchAttributes);
                $inputPrefix           = array($formModelClassName, DynamicSearchForm::DYNAMIC_NAME, $rowNumber);
                $element               = DynamicSearchUtil::getCellElement($viewClassName, $modelClassName,
                                                                          $attributeIndexOrDerivedType);
            }
            $form                      = new NoRequiredsActiveForm();
            $element['inputPrefix']    = $inputPrefix;
            $elementclassname          = $element['type'] . 'Element';
            $element                   = new $elementclassname($modelToUse, $element['attributeName'],
                                                              $form, array_slice($element, 2));
            $element->editableTemplate = '{content}{error}';
            $content                  .= $element->render();
            DropDownUtil::registerScripts(CClientScript::POS_END);
            return $content;
        }

        public static function renderDynamicSearchRowContent($viewClassName,
                                               $modelClassName,
                                               $formModelClassName,
                                               $rowNumber,
                                               $attributeIndexOrDerivedType,
                                               $inputContent,
                                               $suffix = null,
                                               $renderAsAjax = false)
        {
            assert('is_string($viewClassName)');
            assert('is_string($modelClassName)');
            assert('is_string($formModelClassName)');
            assert('is_int($rowNumber)');
            assert('is_string($attributeIndexOrDerivedType) || $attributeIndexOrDerivedType == null');
            assert('is_string($suffix) || $suffix == null');
            assert('is_bool($renderAsAjax)');
            $searchableAttributeIndicesAndDerivedTypes = DynamicSearchUtil::
                                                            getSearchableAttributesAndLabels($viewClassName,
                                                                                             $modelClassName);
            $ajaxOnChangeUrl  = Yii::app()->createUrl("zurmo/default/dynamicSearchAttributeInput",
                                   array('viewClassName'      => $viewClassName,
                                         'modelClassName'     => $modelClassName,
                                         'formModelClassName' => $formModelClassName,
                                         'rowNumber'          => $rowNumber,
                                         'suffix'             => $suffix));
            $rowView     = new DynamicSearchRowView(
                                    $searchableAttributeIndicesAndDerivedTypes,
                                    (int)$rowNumber,
                                    $suffix,
                                    $formModelClassName,
                                    $ajaxOnChangeUrl,
                                    $attributeIndexOrDerivedType,
                                    $inputContent);

            if (!$renderAsAjax)
            {
                $view = $rowView;
            }
            else
            {
                $view = new AjaxPageView($rowView);
            }
            return ZurmoHtml::tag('div', array('class' => 'dynamic-search-row'), $view->render());
        }

        /**
         * This function is used only for API calls. Because for some relations, we are using
         * Item model, which is hidden in API call, so we let API clients to provide us
         * modelClassName and modelId, and this util will convert them into Item id.
         * Recoursevly go over all clauses, and check if there are MANY-MANY relations
         * with Item model. If yes, then convert modelVlassName and modelId, into itemId.
         * @param string $modelClassName
         * @param array $dynamicClauses
         */
        public static function resolveDynamicSearchClausesForModelIdsNeedingToBeItemIds($modelClassName, & $dynamicClauses)
        {
            assert(is_array($dynamicClauses) && !empty($dynamicClauses)); // Not Coding Standard
            $processRecursively = false;

            foreach ($dynamicClauses as $key => $clause)
            {
                // To-do: Should we check only on attributeIndexOrDerivedType key?
                if (isset($clause['attributeIndexOrDerivedType']))
                {
                    $attributeIndexOrDerivedType = $clause['attributeIndexOrDerivedType'];

                    if (isset($clause[$attributeIndexOrDerivedType]) && is_array($clause[$attributeIndexOrDerivedType]))
                    {
                        $relation = $clause[$attributeIndexOrDerivedType];

                        if (isset($relation['modelClassName']) &&
                            $relation['modelClassName'] != $modelClassName::getRelationModelClassName($attributeIndexOrDerivedType) &&
                            $modelClassName::getRelationType($attributeIndexOrDerivedType) == RedBeanModel::MANY_MANY &&
                            $modelClassName::getRelationModelClassName($attributeIndexOrDerivedType) == 'Item')
                        {
                            $relClassName = $relation['modelClassName'];
                            $relModel = $relClassName::getById((int)$relation['modelId']);
                            $itemId = $relModel->getClassId('Item');
                            unset($dynamicClauses[$key][$attributeIndexOrDerivedType]['modelClassName']);
                            unset($dynamicClauses[$key][$attributeIndexOrDerivedType]['modelId']);
                            $dynamicClauses[$key][$attributeIndexOrDerivedType]['id'] = $itemId;
                        }
                    }
                }
            }
        }
    }
?>