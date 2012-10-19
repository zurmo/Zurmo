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

        public static function resolveSortAttributeFromSourceData($name)
        {
            assert('is_string($name)');
            return SearchUtil::resolveSortAttributeFromGetArray($name);
        }

        public static function resolveSortDescendingFromSourceData($name)
        {
            assert('is_string($name)');
            return SearchUtil::resolveSortDescendingFromGetArray($$name);
        }
    }
?>