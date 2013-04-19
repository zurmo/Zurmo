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
     * Helper class for transforming searchForm attributes into their real model attributes.
     */
    class SearchFormAttributesToSearchDataProviderMetadataUtil
    {
        /**
         * Given a searchForm model, a searchForm attribute, and its value, transform this into a metadata array
         * that is understood by the SearchDataProviderMetadataAdapter.
         */
        public static function getMetadata($model, $attributeName, $value)
        {
            assert('$model instanceof SearchForm');
            $metadata        = $model->getAttributesMappedToRealAttributesMetadata();
            $model->resolveMixedSearchAttributeMappedToRealAttributesMetadata($metadata);
            $adaptedMetadata = array();
            if (isset($metadata[$attributeName]))
            {
                static::resolveMetadataForResolveEntireMappingByRules($model, $metadata, $attributeName, $value);
                foreach ($metadata[$attributeName] as $attributesAndRelations)
                {
                    $attributesAndRelationsValue = $value;
                    assert('count($attributesAndRelations) > 0 && count($attributesAndRelations) < 6');
                    $adaptedMetadataClause = array();
                    if (isset($attributesAndRelations['concatedAttributeNames']))
                    {
                        assert('count($attributesAndRelations["concatedAttributeNames"]) == 2');
                        $adaptedMetadataClause['concatedAttributeNames'] = array($attributesAndRelations['concatedAttributeNames'],
                                                                                 'value' => $attributesAndRelationsValue);
                    }
                    else
                    {
                        if (isset($attributesAndRelations[3]))
                        {
                            if ($attributesAndRelations[3] == 'resolveValueByRules')
                            {
                                $searchFormAttributeMappingRules = $model::getSearchFormAttributeMappingRulesTypeByAttribute(
                                                                   $attributeName);
                                $className                       = $searchFormAttributeMappingRules . 'SearchFormAttributeMappingRules';
                                $attributesAndRelationsValue     = $className::resolveValueDataIntoUsableValue(
                                                                   $attributesAndRelationsValue);
                            }
                            elseif ($attributesAndRelations[3] != null)
                            {
                                $attributesAndRelationsValue = $attributesAndRelations[3];
                            }
                        }
                        if (isset($attributesAndRelations[1]) && $attributesAndRelations[1] != null)
                        {
                            $adaptedMetadataClause[$attributesAndRelations[0]] = array('value' =>
                                                                                 array($attributesAndRelations[1] =>
                                                                                       $attributesAndRelationsValue));
                        }
                        else
                        {
                            $adaptedMetadataClause[$attributesAndRelations[0]] = array('value' => $attributesAndRelationsValue);
                        }
                        $adaptedMetadataClause[$attributesAndRelations[0]] = array_merge($adaptedMetadataClause[$attributesAndRelations[0]],
                        static::resolveOperatorTypeDataFromAttributesAndRelations($attributesAndRelations));
                        $adaptedMetadataClause[$attributesAndRelations[0]] = array_merge($adaptedMetadataClause[$attributesAndRelations[0]],
                        static::resolveAppendTypeDataFromAttributesAndRelations($attributesAndRelations));
                    }
                    $adaptedMetadata[] = $adaptedMetadataClause;
                }
                return $adaptedMetadata;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected static function resolveMetadataForResolveEntireMappingByRules($model, & $metadata, $attributeName,
                                                                                $value)
        {
            assert('$model instanceof SearchForm');
            assert('is_array($metadata)');
            assert('is_string($attributeName)');
            if (!is_array($metadata[$attributeName]))
            {
                if ($metadata[$attributeName] == 'resolveEntireMappingByRules')
                {
                    $searchFormAttributeMappingRules = $model::getSearchFormAttributeMappingRulesTypeByAttribute(
                                                       $attributeName);
                    $className                       = $searchFormAttributeMappingRules .
                                                       'SearchFormAttributeMappingRules';
                    $className::resolveAttributesAndRelations($attributeName, $metadata[$attributeName], $value);
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            else
            {
                //Special use-case where there is a Mixed Date or DateTime search form attribute that is from a related
                //model. So for example if you are in Accounts and searching on Opportunity Date.
                //Currently this requires the definition for this search attribute to be in AccountSearchForm.
                //@see ASearchFormModel for an example.
                foreach ($metadata[$attributeName] as $index => $attributesAndRelations)
                {
                    $attributesAndRelationsValue = $value;
                    assert('count($attributesAndRelations) > 0 && count($attributesAndRelations) < 6');
                    if (isset($attributesAndRelations[3]) && $attributesAndRelations[3] == 'resolveRelatedAttributeValueByRules')
                    {
                        $searchFormAttributeMappingRules = $model::getSearchFormAttributeMappingRulesTypeByAttribute(
                                                           $attributeName);
                        //Only supports Date and DateTime.  Could support more types, but would need additional testing
                        //and research first before allowing for that.
                        assert('$searchFormAttributeMappingRules == "MixedDateTimeTypes" ||
                                $searchFormAttributeMappingRules == "MixedDateTypes"');
                        $className                       = $searchFormAttributeMappingRules . 'SearchFormAttributeMappingRules';
                        $attributesAndRelationsValue     = $className::resolveValueDataIntoUsableValue(
                                                           $attributesAndRelationsValue);
                        $newAttributesAndRelations = 'resolveEntireMappingByRules';
                        $className::resolveAttributesAndRelations($attributeName, $newAttributesAndRelations, $value);

                        unset($metadata[$attributeName][$index]);
                        foreach ($newAttributesAndRelations as $newAttributesAndRelationsItem)
                        {
                            $newAttributesAndRelationsItem[0] = $attributesAndRelations[0];
                            $newAttributesAndRelationsItem[1] = $attributesAndRelations[1];
                            $metadata[$attributeName][] = $newAttributesAndRelationsItem;
                        }
                    }
                }
            }
        }

        protected static function resolveOperatorTypeDataFromAttributesAndRelations($attributesAndRelations)
        {
            assert('is_array($attributesAndRelations)');
            if (isset($attributesAndRelations[2]) && $attributesAndRelations[2] != null)
            {
                return array('operatorType' => $attributesAndRelations[2]);
            }
            return array();
        }

        protected static function resolveAppendTypeDataFromAttributesAndRelations($attributesAndRelations)
        {
            assert('is_array($attributesAndRelations)');
            if (isset($attributesAndRelations[4]) && $attributesAndRelations[4] == true)
            {
                return array('appendStructureAsAnd' => true);
            }
            return array();
        }
    }
?>