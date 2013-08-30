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
     * Helper class for working with SavedSearch.
     */
    class SavedSearchUtil
    {
        /**
         * @param DynamicSearchForm $searchForm
         * @param string $viewClassName
         * @param null $stickySearchData
         * @return A|SavedSearch
         */
        public static function makeSavedSearchBySearchForm(DynamicSearchForm $searchForm, $viewClassName, $stickySearchData = null)
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

            $sortAttribute  = null;
            $sortDescending = null;
            //As sticky data contains latest search attributes
            if ($stickySearchData != null && isset($stickySearchData['sortAttribute']))
            {
                $sortAttribute = $stickySearchData['sortAttribute'];

                if (isset($stickySearchData['sortDescending']) && $stickySearchData['sortDescending'] == true )
                {
                    $sortDescending = ".desc";
                }
            }
            else
            {
                $sortAttribute  = $searchForm->sortAttribute;
                $sortDescending = $searchForm->sortDescending;
            }

            $data = array(
                'anyMixedAttributes'      => $searchForm->anyMixedAttributes,
                'anyMixedAttributesScope' => $searchForm->getAnyMixedAttributesScope(),
                'dynamicStructure'        => $searchForm->dynamicStructure,
                'dynamicClauses'          => $searchForm->dynamicClauses,
                'sortAttribute'           => $sortAttribute,
                'sortDescending'          => $sortDescending
            );

            if ($searchForm->getListAttributesSelector() != null)
            {
                $data[SearchForm::SELECTED_LIST_ATTRIBUTES]  = $searchForm->getListAttributesSelector()->getSelected();
            }
            if ($searchForm->getKanbanBoard() != null)
            {
                $data[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES] = $searchForm->getKanbanBoard()->getGroupByAttributeVisibleValues();
                $data[KanbanBoard::SELECTED_THEME]                    = $searchForm->getKanbanBoard()->getSelectedTheme();
            }
            $savedSearch->serializedData = serialize($data);
            return $savedSearch;
        }

        /**
         * @param array $getData
         * @param DynamicSearchForm $searchForm
         */
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
                if (isset($unserializedData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]) &&
                    $searchForm->getKanbanBoard() != null)
                {
                    $searchForm->getKanbanBoard()->setIsActive();
                    $searchForm->getKanbanBoard()->setGroupByAttributeVisibleValues(
                        $unserializedData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]);
                }
                if (isset($unserializedData[KanbanBoard::SELECTED_THEME]) && $searchForm->getKanbanBoard() != null)
                {
                    $searchForm->getKanbanBoard()->setSelectedTheme($unserializedData[KanbanBoard::SELECTED_THEME]);
                }
                if (isset($unserializedData['dynamicStructure']))
                {
                    $searchForm->dynamicStructure = $unserializedData['dynamicStructure'];
                }
                if (isset($unserializedData['dynamicClauses']))
                {
                    $searchForm->dynamicClauses = $unserializedData['dynamicClauses'];
                }
                if (isset($unserializedData['sortAttribute']))
                {
                    $searchForm->sortAttribute = $unserializedData['sortAttribute'];
                }
                if (isset($unserializedData['sortDescending']))
                {
                    $searchForm->sortDescending = $unserializedData['sortDescending'];
                }
            }
        }

        /**
         * @param string $key
         * @param SearchAttributesDataCollection $dataCollection
         * @param array $stickyData
         */
        public static function setDataByKeyAndDataCollection($key, SearchAttributesDataCollection $dataCollection, $stickyData)
        {
            assert('is_string($key)');
            assert('is_array($stickyData)');
            $stickyData['dynamicClauses']          = $dataCollection->getSanitizedDynamicSearchAttributes();
            $stickyData['dynamicStructure']        = $dataCollection->getDynamicStructure();
            if ($dataCollection->getFilterByStarred() != null)
            {
                $stickyData['filterByStarred']         = $dataCollection->getFilterByStarred();
            }
            $anyMixedAttributes                    = $dataCollection->resolveSearchAttributesFromSourceData();
            if (isset($anyMixedAttributes['anyMixedAttributes']))
            {
                $stickyData['anyMixedAttributes']      = $anyMixedAttributes['anyMixedAttributes'];
            }
            $dataCollection->resolveAnyMixedAttributesScopeForSearchModelFromSourceData();
            $dataCollection->resolveSelectedListAttributesForSearchModelFromSourceData();
            $dataCollection->resolveKanbanBoardOptionsForSearchModelFromSourceData();

            $stickyData['anyMixedAttributesScope']            = $dataCollection->getAnyMixedAttributesScopeFromModel();
            $stickyData[SearchForm::SELECTED_LIST_ATTRIBUTES] = $dataCollection->getSelectedListAttributesFromModel();
            static::resolveKanbanBoardDataByCollection($dataCollection, $stickyData);
            if ($dataCollection instanceof SavedSearchAttributesDataCollection)
            {
                $stickyData['savedSearchId'] = $dataCollection->getSavedSearchId();
            }
            // Resolve the sort and desc attribute from source data and set it in sticky array
            $listSortModel = get_class($dataCollection->getModel()->getModel());

            $sortAttribute = $dataCollection->resolveSortAttributeFromSourceData($listSortModel);
            //There are two cases
            //a) When user clicks on sorting in grid view, at that time Model Class inside form is used
            //b) When user save the search, sort attributes are in form model
            if ($sortAttribute == null)
            {
               $sortAttribute = $dataCollection->resolveSortAttributeFromSourceData(get_class($dataCollection->getModel()));
            }
            if (!empty($sortAttribute))
            {
                $stickyData['sortAttribute'] = $sortAttribute;
                if ($dataCollection->resolveSortDescendingFromSourceData($listSortModel))
                {
                    $stickyData['sortDescending'] = true;
                }
                else
                {
                    $sortDescending = $dataCollection->resolveSortDescendingFromSourceData(get_class($dataCollection->getModel()));
                    if ($sortDescending === true)
                    {
                        $stickyData['sortDescending'] = true;
                    }
                    else
                    {
                        $stickyData['sortDescending'] = false;
                    }
                }
            }
            StickySearchUtil::setDataByKeyAndData($key, $stickyData);
        }

        /**
         * @param array $stickyData
         * @param SavedDynamicSearchForm $model
         */
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
            if (isset($stickyData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]) &&
                $model->getKanbanBoard() != null && !$model->getKanbanBoard()->getClearSticky())
            {
                $model->getKanbanBoard()->setIsActive();
                $model->getKanbanBoard()->setGroupByAttributeVisibleValues(
                    $stickyData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]);
            }
            if (isset($stickyData[KanbanBoard::SELECTED_THEME]) && $model->getKanbanBoard() != null &&
                !$model->getKanbanBoard()->getClearSticky())
            {
                $model->getKanbanBoard()->setSelectedTheme($stickyData[KanbanBoard::SELECTED_THEME]);
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

        /**
         * @param array $getData
         * @param DynamicSearchForm $searchForm
         * @param $stickyData
         */
        public static function resolveSearchFormByStickySortData(array $getData, DynamicSearchForm $searchForm, $stickyData)
        {
            if (isset($getData[get_class($searchForm)]))
            {
                if (isset($stickyData['sortAttribute']))
                {
                    $searchForm->sortAttribute = $stickyData['sortAttribute'];
                }
                if (isset($stickyData['sortDescending']))
                {
                    $searchForm->sortDescending = $stickyData['sortDescending'];
                }
            }
        }

        /**
         * @param SearchAttributesDataCollection $dataCollection
         * @param array $stickyData
         */
        protected static function resolveKanbanBoardDataByCollection(SearchAttributesDataCollection $dataCollection, & $stickyData)
        {
            if ($dataCollection->hasKanbanBoard() && $dataCollection->getKanbanBoard()->getIsActive() &&
               !$dataCollection->shouldClearStickyForKanbanBoard())
            {
                $stickyData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES] = $dataCollection->getKanbanBoardGroupByAttributeVisibleValuesFromModel();
                $stickyData[KanbanBoard::SELECTED_THEME]                    = $dataCollection->getKanbanBoardSelectedThemeFromModel();
            }
            elseif ($dataCollection->hasKanbanBoard() && $dataCollection->shouldClearStickyForKanbanBoard())
            {
                unset($stickyData[KanbanBoard::GROUP_BY_ATTRIBUTE_VISIBLE_VALUES]);
                unset($stickyData[KanbanBoard::SELECTED_THEME]);
            }
        }
    }
?>