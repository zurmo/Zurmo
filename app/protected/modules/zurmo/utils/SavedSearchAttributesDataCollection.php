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
     * Handles resolving when a search attribute has a SavedSearch model as a potential source.
     */
    class SavedSearchAttributesDataCollection extends SearchAttributesDataCollection
    {
        public function getDynamicSearchAttributes()
        {
            $searchArray = SearchUtil::getDynamicSearchAttributesFromGetArray(get_class($this->model));
            if (!empty($searchArray))
            {
                return $searchArray;
            }
            elseif ($this->model->dynamicClauses != null)
            {
                $searchArray = $this->model->dynamicClauses;
                return SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            }
            else
            {
                return array();
            }
        }

        public function getSanitizedDynamicSearchAttributes()
        {
            $searchArray = SearchUtil::getDynamicSearchAttributesFromGetArray(get_class($this->model));
            if (!empty($searchArray))
            {
                return SearchUtil::
                   sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($this->model, $searchArray);
            }
            elseif ($this->model->dynamicClauses != null)
            {
                $searchArray = $this->model->dynamicClauses;
                return SearchUtil::getSearchAttributesFromSearchArray($searchArray);
            }
            else
            {
                return array();
            }
        }

        public function getDynamicStructure()
        {
            $dynamicStructure = SearchUtil::getDynamicSearchStructureFromGetArray(get_class($this->model));
            if ($dynamicStructure != null)
            {
                return $dynamicStructure;
            }
            return $this->model->dynamicStructure;
        }

        public function resolveSearchAttributesFromSourceData()
        {
            $anyMixedAttributes = SearchUtil::resolveSearchAttributesFromGetArray(get_class($this->model), get_class($this->model));
            if ($anyMixedAttributes != null)
            {
                return $anyMixedAttributes;
            }
            if ($this->model->anyMixedAttributes != null)
            {
                //might need to run this through the @see SearchUtil::getSearchAttributesFromSearchArray but not sure.
                return array('anyMixedAttributes' => $this->model->anyMixedAttributes);
            }
            return array();
        }

        public function getSavedSearchId()
        {
            return $this->model->savedSearchId;
        }
    }
?>