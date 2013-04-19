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
                'dynamicClauses'          => $searchForm->dynamicClauses,
            );

            if ($searchForm->getListAttributesSelector() != null)
            {
                $data[SearchForm::SELECTED_LIST_ATTRIBUTES]  = $searchForm->getListAttributesSelector()->getSelected();
            }
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
                if (isset($unserializedData[SearchForm::SELECTED_LIST_ATTRIBUTES]) &&
                   $searchForm->getListAttributesSelector() != null)
                {
                    $searchForm->getListAttributesSelector()->setSelected(
                                    $unserializedData[SearchForm::SELECTED_LIST_ATTRIBUTES]);
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

        public static function setDataByKeyAndDataCollection($key, SearchAttributesDataCollection $dataCollection, $stickyData)
        {
            assert('is_string($key)');
            assert('is_array($stickyData)');
            $stickyData['dynamicClauses']          = $dataCollection->getSanitizedDynamicSearchAttributes();
            $stickyData['dynamicStructure']        = $dataCollection->getDynamicStructure();
            $anyMixedAttributes                    = $dataCollection->resolveSearchAttributesFromSourceData();
            if (isset($anyMixedAttributes['anyMixedAttributes']))
            {
                $stickyData['anyMixedAttributes']      = $anyMixedAttributes['anyMixedAttributes'];
            }
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();

            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();

            $stickyData['anyMixedAttributesScope']            = $dataCollection->getAnyMixedAttributesScopeFromModel();
            $stickyData[SearchForm::SELECTED_LIST_ATTRIBUTES] = $dataCollection->getSelectedListAttributesFromModel();
            if ($dataCollection instanceof SavedSearchAttributesDataCollection)
            {
                $stickyData['savedSearchId'] = $dataCollection->getSavedSearchId();
            }

            // Resolve the sort and desc attribute from source data and set it in sticky array
            $listSortModel = get_class($dataCollection->getModel()->getModel());

            $sortAttribute = $dataCollection->resolveSortAttributeFromSourceData($listSortModel);

            if (!empty($sortAttribute))
            {
                $stickyData['sortAttribute'] = $sortAttribute;
                if ($dataCollection->resolveSortDescendingFromSourceData($listSortModel))
                {
                    $stickyData['sortDescending'] = true;
                }
                else
                {
                    $stickyData['sortDescending'] = false;
                }
            }

            StickySearchUtil::setDataByKeyAndData($key, $stickyData);
        }

        public static function resolveSearchFormByStickyDataAndModel($stickyData, SavedDynamicSearchForm $model)
        {
            assert('$stickyData != null && is_array($stickyData)');
            if (isset($stickyData['savedSearchId']) && $stickyData['savedSearchId'] != '')
            {
                try
                {
                    $savedSearch            = SavedSearch::getById((int)$stickyData['savedSearchId']);
                    $model->savedSearchName = $savedSearch->name;
                    $model->savedSearchId   = $savedSearch->id;
                }
                catch (NotFoundException $e)
                {
                }
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
            if (isset($stickyData[SearchForm::SELECTED_LIST_ATTRIBUTES]) &&
               $model->getListAttributesSelector() != null)
            {
                $model->getListAttributesSelector()->setSelected($stickyData[SearchForm::SELECTED_LIST_ATTRIBUTES]);
            }

            // If the sort attribute is not in get request but in sticky data, set it into get array
            $listModelClassName = get_class($model->getModel());
            if (!isset($_GET[$listModelClassName . '_sort']) && isset($stickyData['sortAttribute']))
            {
                if ($stickyData['sortAttribute'] != '')
                {
                    $model->sortAttribute = $stickyData['sortAttribute'];
                }

                if (isset($stickyData['sortDescending']))
                {
                    if ($stickyData['sortDescending'] == true)
                    {
                        $model->sortDescending = ".desc";
                    }
                }
            }
        }
    }
?>