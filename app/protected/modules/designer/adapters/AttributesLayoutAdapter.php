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
     * Adapter for converting an attribute collection
     * into a collection that is usuable by the designer
     * layout editor.
     */
    class AttributesLayoutAdapter
    {
        protected $attributes;

        protected $metadata;

        protected $designerLayoutAttributes;

        protected $nonPlaceableAttributeNames;

        protected $nonPlaceableAttributeTypes;

        protected $derivedAttributeTypes;

        public function __construct(
            $attributes,
            $metadata,
            $derivedAttributeTypes      = array(),
            $nonPlaceableAttributeNames = array(),
            $nonPlaceableAttributeTypes = array()
            )
        {
            assert('is_array($attributes)');
            assert('is_array($metadata)');
            assert('isset($metadata["global"]["panels"])');
            assert('is_array($derivedAttributeTypes)');
            assert('is_array($nonPlaceableAttributeNames)');
            assert('is_array($nonPlaceableAttributeTypes)');

            $this->attributes                  = $attributes;
            $this->metadata                    = $metadata;
            $this->nonPlaceableAttributeNames  = $nonPlaceableAttributeNames;
            $this->nonPlaceableAttributeTypes  = $nonPlaceableAttributeTypes;
            $this->derivedAttributeTypes       = $derivedAttributeTypes;
            $this->designerLayoutAttributes    = new DesignerLayoutAttributes();
        }

        /**
         * Take the attributesCollection and filter out nonPlaceable attribute names
         * and attribute types.
         * @return returns attributesCollection
         */
        public function getPlaceableLayoutAttributes()
        {
            $attributeCollection = array();
            foreach ($this->attributes as $attributeName => $attributeInformation)
            {
                if (in_array($attributeName, $this->nonPlaceableAttributeNames))
                {
                    continue;
                }
                if (in_array($attributeInformation['elementType'], $this->nonPlaceableAttributeTypes))
                {
                    continue;
                }
                $attributeCollection[$attributeName] = $attributeInformation;
            }
            return $attributeCollection;
        }

        /**
         * Returns array of required derived layout attribute types
         *
         */
        public function getRequiredDerivedLayoutAttributeTypes()
        {
            $requiredAttributeTypes = array();
            foreach ($this->derivedAttributeTypes as $attributeType)
            {
                if ($this->isDerivedLayoutAttributeTypeRequired($attributeType))
                {
                    $requiredAttributeTypes[] = $attributeType;
                }
            }
            return $requiredAttributeTypes;
        }

        protected function isDerivedLayoutAttributeTypeRequired($attributeType)
        {
            $elementClassName = $attributeType . 'Element';
            $attributesUsed = $elementClassName::getModelAttributeNames();
            foreach ($attributesUsed as $attributeName)
            {
                if ($this->attributes[$attributeName]['isRequired'] == true &&
                $this->attributes[$attributeName]['isReadOnly'] == false)
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * Adapt an attribute collection into DesignerLayoutAttributes
         * @return returns DesignerLayoutAttributes
         */
        public function makeDesignerLayoutAttributes()
        {
            $attributesInPlace = $this->getAttributesInPlace();
            $layoutAttributes = array();
            foreach ($this->attributes as $attributeName => $attributeInformation)
            {
                if (in_array($attributeName, $this->nonPlaceableAttributeNames))
                {
                    continue;
                }
                if (in_array($attributeInformation['elementType'], $this->nonPlaceableAttributeTypes))
                {
                    continue;
                }
                if (in_array($attributeName, $attributesInPlace))
                {
                    $availableToSelect = false;
                }
                else
                {
                    $availableToSelect = true;
                }
                $this->designerLayoutAttributes->setItem(
                    $attributeName,
                    $attributeName,
                    $availableToSelect,
                    $attributeInformation['attributeLabel'],
                    $attributeInformation['isRequired']
                );
            }
            $this->populateDerivedAttributes();
            return $this->designerLayoutAttributes;
        }

        protected function getAttributesInPlace()
        {
            $attributesInPlace = array();
            foreach ($this->metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                if ($elementInformation['type'] != 'Null' && // Not Coding Standard
                                    $elementInformation['attributeName'] != 'null')
                                {
                                    $attributesInPlace[] = $elementInformation['attributeName'];
                                }
                            }
                        }
                    }
                }
            }
            return $attributesInPlace;
        }

        protected function getDerivedAttributesInPlace()
        {
            $derivedAttributeTypesInPlace = array();
            foreach ($this->metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $elementInformation)
                            {
                                if ($elementInformation['attributeName'] == 'null') // Not Coding Standard
                                {
                                    assert('$elementInformation["type"] != "Null"'); // Not Coding Standard
                                    $derivedAttributeTypesInPlace[] = $elementInformation['type'];
                                }
                            }
                        }
                    }
                }
            }
            return $derivedAttributeTypesInPlace;
        }

        protected function populateDerivedAttributes()
        {
            $derivedAttributeTypesInPlace = $this->getDerivedAttributesInPlace();
            if (isset($this->metadata['global']['derivedAttributeTypes']))
            {
                assert('is_array($this->metadata["global"]["derivedAttributeTypes"])');
                foreach ($this->metadata['global']['derivedAttributeTypes'] as $attributeType)
                {
                    if (in_array($attributeType, $derivedAttributeTypesInPlace))
                    {
                        $availableToSelect = false;
                    }
                    else
                    {
                        $availableToSelect = true;
                    }
                    $elementClassName = $attributeType . 'Element';
                    $this->designerLayoutAttributes->setItem(
                        $attributeType,
                        $attributeType,
                        $availableToSelect,
                        $elementClassName::getDisplayName(),
                        $this->isDerivedLayoutAttributeTypeRequired($attributeType)
                    );
                }
            }
        }
    }
?>
