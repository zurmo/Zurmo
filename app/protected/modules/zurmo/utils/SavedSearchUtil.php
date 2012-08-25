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
     * Helper class for working with SavedSearch.
     */
    class SavedSearchUtil
    {
        public static function makeSavedSearchBySearchForm(DynamicSearchForm $searchForm, $viewClassName)
        {
            assert('is_string($viewClassName)');
            if ($searchForm->savedSearchId != null)
            {
                $savedSearch       = SavedSearch::getById((int)$searchForm->savedSearchId);
            }
            else
            {
                $savedSearch                = new SavedSearch();
                $savedSearch->viewClassName = $viewClassName;
            }
            $savedSearch->name = $searchForm->savedSearchName;

            $data = array(
                'anyMixedAttributes'      => $searchForm->anyMixedAttributes,
                'anyMixedAttributesScope' => $searchForm->getAnyMixedAttributesScope(),
                'dynamicStructure'        => $searchForm->dynamicStructure,
                'dynamicClauses'          => $searchForm->dynamicClauses
            );
            $savedSearch->serializedData = serialize($data);
            return $savedSearch;
        }

        public static function resolveSearchFormByGetData(array $getData, DynamicSearchForm $searchForm)
        {
            if (isset($getData['savedSearchId']) && $getData['savedSearchId'] != '')
            {
                $savedSearch                 = SavedSearch::getById((int)$getData['savedSearchId']);
                $searchForm->savedSearchName = $savedSearch->name;
                $searchForm->savedSearchId   = $savedSearch->id;
                $unserializedData            = unserialize($savedSearch->serializedData);
                if (isset($unserializedData['anyMixedAttributes']))
                {
                    $searchForm->anyMixedAttributes = $unserializedData['anyMixedAttributes'];
                }
                if (isset($unserializedData['anyMixedAttributesScope']))
                {
                    $searchForm->setAnyMixedAttributesScope($unserializedData['anyMixedAttributesScope']);
                }
                if (isset($unserializedData['dynamicStructure']))
                {
                    $searchForm->dynamicStructure = $unserializedData['dynamicStructure'];
                }
                if (isset($unserializedData['dynamicClauses']))
                {
                    $searchForm->dynamicClauses = $unserializedData['dynamicClauses'];
                }
            }
        }

        public static function setDataByKeyAndDataCollection($key, SearchAttributesDataCollection $dataCollection)
        {
            assert('is_string($key)');
            $stickyData['dynamicClauses']          = $dataCollection->getSanitizedDynamicSearchAttributes();
            $stickyData['dynamicStructure']        = $dataCollection->getDynamicStructure();
            $anyMixedAttributes                    = $dataCollection->resolveSearchAttributesFromSourceData();
            if (isset($anyMixedAttributes['anyMixedAttributes']))
            {
                $stickyData['anyMixedAttributes']      = $anyMixedAttributes['anyMixedAttributes'];
            }
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $stickyData['anyMixedAttributesScope']     = $dataCollection->getAnyMixedAttributesScopeFromModel();
            if ($dataCollection instanceof SavedSearchAttributesDataCollection)
            {
                $stickyData['savedSearchId']           = $dataCollection->getSavedSearchId();
            }
            Yii::app()->user->setState($key, serialize($stickyData));
        }

        public static function resolveSearchFormByStickyDataAndModel($stickyData, SavedDynamicSearchForm $model)
        {
            assert('$stickyData != null && is_array($stickyData)');
            if (isset($stickyData['savedSearchId']) && $stickyData['savedSearchId'] != '')
            {
                $savedSearch            = SavedSearch::getById((int)$stickyData['savedSearchId']);
                $model->savedSearchName = $savedSearch->name;
                $model->savedSearchId   = $savedSearch->id;
            }
            if (isset($stickyData['anyMixedAttributes']))
            {
                $model->anyMixedAttributes = $stickyData['anyMixedAttributes'];
            }
            if (isset($stickyData['anyMixedAttributesScope']))
            {
                $model->setAnyMixedAttributesScope($stickyData['anyMixedAttributesScope']);
            }
            if (isset($stickyData['dynamicStructure']))
            {
                $model->dynamicStructure = $stickyData['dynamicStructure'];
            }
            if (isset($stickyData['dynamicClauses']))
            {
                $model->dynamicClauses = $stickyData['dynamicClauses'];
            }
        }
    }
?>