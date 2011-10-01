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
     * LayoutMetadataAdapter adapts the saved layout data from
     * the designer tool into view metadata that can be saved.
     */
    class LayoutMetadataAdapter
    {
        protected $viewClassName;
        protected $moduleClassName;
        protected $existingMtadata;
        protected $designerRules;
        protected $placeableLayoutAttributes;
        protected $requiredDerivedLayoutAttributeTypes;
        protected $message;
        private   $placedAttributeNames = array();
        private   $placedDerivedAttributeTypes = array();

        public function __construct($viewClassName,
            $moduleClassName,
            $existingMetadata,
            DesignerRules $designerRules,
            $placeableLayoutAttributes,
            $requiredDerivedLayoutAttributeTypes
        )
        {
            assert('is_string($viewClassName)');
            assert('is_string($moduleClassName)');
            assert('is_array($existingMetadata)');
            assert('is_array($placeableLayoutAttributes)');
            assert('is_array($requiredDerivedLayoutAttributeTypes)');
            $this->viewClassName                       = $viewClassName;
            $this->moduleClassName                     = $moduleClassName;
            $this->existingMetadata                    = $existingMetadata;
            $this->designerRules                       = $designerRules;
            $this->placeableLayoutAttributes           = $placeableLayoutAttributes;
            $this->requiredDerivedLayoutAttributeTypes = $requiredDerivedLayoutAttributeTypes;
        }

        /**
         * Given layout which most likely is coming from a POST,
         * process the layout into savable metadata and save the metadata
         * against the view.
         * @returns Boolean true on success, false on error @see getMessage()
         */
        public function setMetadataFromLayout($layout, $savableMetadata)
        {
            array('is_array($savableMetadata)');
            if (isset($layout['panels']) &&
                is_array($layout['panels']) &&
                count($layout['panels']) > 0)
            {
                foreach ($layout['panels'] as $panelKey => $panel)
                {
                    $panelMetadata = array();
                    $savableMetadata['panels'][$panelKey] = $this->adaptPanelSettingsToMetadata($panel, $panelMetadata);
                    if (is_array($panel['rows']))
                    {
                        foreach ($panel['rows'] as $rowKey => $row)
                        {
                            foreach ($row['cells'] as $cellKey => $cell)
                            {
                                $cellMetadata = array();
                                $cellMetadata = $this->adaptCellElementToMetadata($cell['element'], $cellMetadata);
                                $cellMetadata = $this->adaptCellSettingsToMetadata($cell, $cellMetadata);
                                if (isset($cellMetadata['elements']) && count($cellMetadata['elements']) > 0)
                                {
                                    $savableMetadata['panels'][$panelKey]['rows'][$rowKey]['cells'][$cellKey] = $cellMetadata;
                                }
                            }
                        }
                    }
                    else
                    {
                        $savableMetadata['panels'][$panelKey]['rows'] = array();
                    }
                }
            }
            else
            {
                $this->message = yii::t('Default', 'You must have at least one panel in order to save a layout.');
                return false;
            }
            if ($this->designerRules->requireAllRequiredFieldsInLayout() && !$this->areAllRequiredAttributesPlaced())
            {
                $this->message = yii::t('Default', 'All required fields must be placed in this layout.');
                return false;
            }
            $viewsToSetMetadataFor = $this->designerRules->getMetadataViewClassNames($this->viewClassName, $this->moduleClassName);
            foreach ($viewsToSetMetadataFor as $viewClassName)
            {
                $viewClassName::setMetadata($this->makeMergedSaveableMetadata($viewClassName, $savableMetadata));
            }
            $this->message = yii::t('Default', 'Layout saved successfully.');
            return true;
        }

        protected function makeMergedSaveableMetadata($viewClassName, $savableMetadata)
        {
            $metadata = $this->getexistingMetadataToMerge($viewClassName);
            $metadata['global']['panels'] = $savableMetadata['panels'];
            if (count($savableMetadata) > 1)
            {
                foreach ($savableMetadata as $keyName => $notUsed)
                {
                    if ($keyName != 'panels')
                    {
                        $metadata['global'][$keyName] = $savableMetadata[$keyName];
                    }
                }
            }
            return $this->designerRules->formatSavableMetadataFromLayout($metadata, $viewClassName);
        }

        protected function getexistingMetadataToMerge($viewClassName)
        {
            if ($viewClassName == $this->viewClassName)
            {
                $existingMetadata = $this->existingMetadata;
            }
            else
            {
                $existingMetadata = $viewClassName::getMetadata();
            }
            return $existingMetadata;
        }

        /**
         * Get a message if populated commmunicating information regarding
         * the adaption of layout data to metadata.
         */
        public function getMessage()
        {
            return $this->message;
        }

        protected function adaptCellElementToMetadata($elementName, $cellMetadata)
        {
            assert('is_string($elementName)');
            $derivedAttributes = $this->getDerivedAttributesFromMetadata();
            $placeElement      = false;
            if (in_array($elementName, $derivedAttributes))
            {
                $element = array('attributeName' => 'null', 'type' => $elementName); // Not Coding Standard
                $elementClassName = $elementName . 'Element';
                $attributesUsed = $elementClassName::getModelAttributeNames();
                $this->placedAttributeNames = array_merge($this->placedAttributeNames, $attributesUsed);
                $this->placedDerivedAttributeTypes[] = $elementName;
                $placeElement = true;
            }
            elseif (isset($this->placeableLayoutAttributes[$elementName]))
            {
                $element = array(
                    'attributeName' => $elementName,
                    'type' => $this->placeableLayoutAttributes[$elementName]['elementType']
                );
                $this->placedAttributeNames[] = $elementName;
                $placeElement = true;
            }
            else
            {
                if ($this->designerRules->shouldPlaceNullElement())
                {
                    $element = array('attributeName' => null, 'type' => 'Null'); // Not Coding Standard
                    $placeElement = true;
                }
            }
            if ($placeElement)
            {
                $cellMetadata['elements'][] = $element;
            }
            return $cellMetadata;
        }

        protected function adaptPanelSettingsToMetadata($panel, $panelMetadata)
        {
            assert('is_array($panel)');
            assert('isset($panel["rows"])');
            $settingsAttributes = $this->designerRules->getPanelSettingsAttributes();
            foreach ($settingsAttributes as $elementInformation)
            {
                $elementclassname = $elementInformation['type'] . 'LayoutSettingElement';
                $element  = new $elementclassname($elementInformation['attributeName']);
                $panelMetadata = $element->processToMetadata($panel, $panelMetadata);
            }
            return $panelMetadata;
        }

        protected function adaptCellSettingsToMetadata($cell, $cellMetadata)
        {
            assert('is_array($cell)');
            assert('isset($cell["element"])');
            $settingsAttributes = $this->designerRules->getCellSettingsAttributes();
            foreach ($settingsAttributes as $elementInformation)
            {
                $elementclassname = $elementInformation['type'] . 'LayoutSettingElement';
                $element  = new $elementclassname($elementInformation['attributeName']);
                $panelMetadata = $element->processToMetadata($cell, $cellMetadata);
            }
            return $cellMetadata;
        }

        protected function getDerivedAttributesFromMetadata()
        {
            if (isset($this->existingMetadata['global']['derivedAttributeTypes']))
            {
                assert('is_array($this->existingMetadata["global"]["derivedAttributeTypes"])');
                return $this->existingMetadata['global']['derivedAttributeTypes'];
            }
            return array();
        }

        protected function areAllRequiredAttributesPlaced()
        {
            foreach ($this->placeableLayoutAttributes as $attributeName => $attributeInformation)
            {
                if ($attributeInformation['isRequired'] &&
                    !in_array($attributeName, $this->placedAttributeNames))
                {
                    $elementClassName = $attributeInformation['elementType'] . 'Element';
                    if (!$elementClassName::isReadOnly())
                    {
                        return false;
                    }
                }
            }
            foreach ($this->requiredDerivedLayoutAttributeTypes as $attributeType)
            {
                if (!in_array($attributeType, $this->placedDerivedAttributeTypes))
                {
                    return false;
                }
            }

            return true;
        }
    }
?>