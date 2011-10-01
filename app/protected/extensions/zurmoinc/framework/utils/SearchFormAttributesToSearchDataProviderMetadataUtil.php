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
            $adaptedMetadata = array();
            if (isset($metadata[$attributeName]))
            {
                foreach ($metadata[$attributeName] as $attributesAndRelations)
                {
                    assert('count($attributesAndRelations) > 0 && count($attributesAndRelations) < 5');
                    if(isset($attributesAndRelations[3]))
                    {
                        assert('$attributesAndRelations[3] == "resolveValueByRules"');
                        $searchFormAttributeMappingRules = $model::getSearchFormAttributeMappingRulesTypeByAttribute(
                                                           $attributeName);
                        $className = $searchFormAttributeMappingRules . 'SearchFormAttributeMappingRules';

                        $className::resolveValue($value);
                    }
                    if (isset($attributesAndRelations[1]) && $attributesAndRelations[1] != null)
                    {
                        $adaptedMetadata[$attributesAndRelations[0]] = array('value' =>
                                                                             array($attributesAndRelations[1] => $value));
                    }
                    else
                    {
                        $adaptedMetadata[$attributesAndRelations[0]] = array('value' => $value);
                    }
                    $adaptedMetadata[$attributesAndRelations[0]] = array_merge($adaptedMetadata[$attributesAndRelations[0]],
                    static::resolveOperatorTypeDataFromAttributesAndRelations($attributesAndRelations));
                }
                return $adaptedMetadata;
            }
            else
            {
                throw NotSupportedException();
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
    }
?>