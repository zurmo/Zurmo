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
     * Manage which attributes are selected as columns for a listView.  Utilized by searchView to allow user
     * to hide/show columns on a per search basis.
     */
    class ListAttributesSelector
    {
        private $designerLayoutAttributes;

        private $selectedValues;

        private $layoutMetadataAdapter;

        private $viewClassName;

        private $editableMetadata;

        public function __construct($viewClassName, $moduleClassName)
        {
            assert('is_string($viewClassName)');
            assert('is_string($moduleClassName)');
            $modelClassName           = $moduleClassName::getPrimaryModelName();
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
            $this->layoutMetadataAdapter = new LayoutMetadataAdapter(
                $viewClassName,
                $moduleClassName,
                $editableMetadata,
                $designerRules,
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $this->designerLayoutAttributes = $attributesLayoutAdapter->makeDesignerLayoutAttributes();
            $this->viewClassName            = $viewClassName;
            $this->editableMetadata         = $editableMetadata;
        }

        protected function getDefaultListAttributesNamesAndLabelsFromEditableMetadata($metadata)
        {
            assert('isset($metadata["global"]["panels"])');
            $attributeNames = array();
            foreach ($metadata['global']['panels'] as $panelKey => $panel)
            {
                foreach ($panel['rows'] as $rowKey => $row)
                {
                    foreach ($row['cells'] as $cellKey => $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementKey => $elementInformation)
                            {
                                //Expects listvie to always have a single cell per row
                                if (count($row['cells']) != 1)
                                {
                                    throw new NotSupportedException();
                                }
                                if ($elementInformation['attributeName'] != 'null')
                                {
                                    $attributeNames[] = $elementInformation['attributeName'];
                                }
                                else
                                {
                                    $attributeNames[] = $elementInformation['type'];
                                }
                            }
                        }
                    }
                }
            }
            return $attributeNames;
        }

        /**
         * @return array
         */
        public function getUnselectedListAttributesNamesAndLabelsAndAll()
        {
            $selectedValues = $this->getSelected();
            $attributeNames = array();
            foreach ($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if (!in_array($attributeName, $selectedValues))
                {
                    $attributeNames[$attributeName] = $data['attributeLabel'];
                }
            }
            asort($attributeNames);
            return $attributeNames;
        }

        /**
         * @return array
         */
        public function getSelectedListAttributesNamesAndLabelsAndAll()
        {
            $selectedValues = $this->getSelected();
            $attributeNames = array();
            $allAttributes  = $this->designerLayoutAttributes->get();
            foreach ($selectedValues as $attributeName)
            {
                if (key_exists($attributeName, $allAttributes))
                {
                    $attributeNames[$attributeName] = $allAttributes[$attributeName]['attributeLabel'];
                }
                else
                {
                    throw NotSupportedException();
                }
            }
            return $attributeNames;
        }

        /**
         * @return array
         */
        public function getSelected()
        {
            if ($this->selectedValues != null)
            {
                return $this->selectedValues;
            }
            $attributeNames = $this->getDefaultListAttributesNamesAndLabelsFromEditableMetadata($this->editableMetadata);
            return $attributeNames;
        }

        /**
         * @return array
         */
        public function getMetadataDefinedListAttributeNames()
        {
            $attributeNames = array();
            foreach ($this->designerLayoutAttributes->get() as $attributeName => $data)
            {
                if (!$data['availableToSelect'])
                {
                    $attributeNames[] = $attributeName;
                }
            }
            return $attributeNames;
        }

        public function setSelected($values)
        {
            $this->selectedValues = $values;
        }

        /**
         * @return array of listView metadata that resolves the specifically selected attributes to override
         * the metadata defined attributes.
         */
        public function getResolvedMetadata()
        {
            return $this->layoutMetadataAdapter->resolveMetadataFromSelectedListAttributes($this->viewClassName,
                                                                                           $this->getSelected());
        }
    }
?>