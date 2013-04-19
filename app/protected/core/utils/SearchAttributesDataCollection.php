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
     * Base class for managing the source of search attributes.  Attributes can be coming from a $_GET, a $_POST or
     * potentially a model as a saved search.
     */
    class SearchAttributesDataCollection
    {
        protected $model;

        public function __construct($model)
        {
            assert('$model instanceof RedBeanModel || $model instanceof SearchForm');
            $this->model = $model;
        }

        public function getModel()
        {
            return $this->model;
        }

        public function getDynamicSearchAttributes()
        {
            $dynamicSearchAttributes = SearchUtil::getDynamicSearchAttributesFromGetArray(get_class($this->model));
            if ($dynamicSearchAttributes == null)
            {
                return array();
            }
            return $dynamicSearchAttributes;
        }

        public function getSanitizedDynamicSearchAttributes()
        {
            $dynamicSearchAttributes = SearchUtil::getDynamicSearchAttributesFromGetArray(get_class($this->model));
            if ($dynamicSearchAttributes == null)
            {
                return array();
            }
            return SearchUtil::
                   sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel($this->model, $dynamicSearchAttributes);
        }

        public function getDynamicStructure()
        {
            return SearchUtil::getDynamicSearchStructureFromGetArray(get_class($this->model));
        }

        public function getAnyMixedAttributesScopeFromModel()
        {
            return $this->model->getAnyMixedAttributesScope();
        }

        public function getSelectedListAttributesFromModel()
        {
            if ($this->model->getListAttributesSelector() != null)
            {
                return $this->model->getListAttributesSelector()->getSelected();
            }
        }

        public function resolveSearchAttributesFromSourceData()
        {
            return SearchUtil::resolveSearchAttributesFromGetArray(get_class($this->model), get_class($this->model));
        }

        public function resolveAnyMixedAttributesScopeForSearchModelFromSourceData()
        {
            return SearchUtil::resolveAnyMixedAttributesScopeForSearchModelFromGetArray($this->model, get_class($this->model));
        }

        public function resolveSelectedListAttributesForSearchModelFromSourceData()
        {
            return SearchUtil::resolveSelectedListAttributesForSearchModelFromGetArray($this->model, get_class($this->model));
        }

        public function resolveSortAttributeFromSourceData($name)
        {
            assert('is_string($name)');
            $sortAttribute = SearchUtil::resolveSortAttributeFromGetArray($name);
            if ($sortAttribute == null)
            {
                if (!empty($this->model->sortAttribute))
                {
                    $sortAttribute = $this->model->sortAttribute;
                }
                else
                {
                    $sortAttribute = null;
                }
            }

            return $sortAttribute;
        }

        public function resolveSortDescendingFromSourceData($name)
        {
            assert('is_string($name)');
            $sortDescending =  SearchUtil::resolveSortDescendingFromGetArray($name);

            if ($sortDescending === false)
            {
                if (!empty($this->model->sortDescending))
                {
                    $sortDescending = true;
                }
                else
                {
                    $sortDescending = false;
                }
            }

            return $sortDescending;
        }
    }
?>