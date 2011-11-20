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
     * Views that extend this class have a modelClassName defined.
     * Examples of views that use this class include DetailsView,
     * ListView and SearchView.
     */
    abstract class ModelView extends ConfigurableMetadataView
    {
        protected $modelClassName;

        protected static function assertMetadataIsValid(array $metadata)
        {
            parent::assertMetadataIsValid($metadata);
            $attributeNames = array();
            $derivedTypes   = array();
            assert('is_array($metadata["global"]["panels"])');
            foreach ($metadata["global"]["panels"] as $panel)
            {
                assert('is_array($panel["rows"])');
                foreach ($panel["rows"] as $row)
                {
                    $cellCount = 0;
                    assert('is_array($row["cells"])');
                    foreach ($row["cells"] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            assert('count($cell["elements"]) == 1');
                            $elementInformation = $cell['elements'][0];
                            if ($elementInformation['attributeName'] == 'null')
                            {
                                assert('!in_array($elementInformation["type"], $derivedTypes)');
                                $derivedTypes[] = $elementInformation['type'];
                                $elementclassname = $elementInformation['type'] . 'Element';
                                assert('class_exists($elementclassname)');
                            }
                            elseif ($elementInformation['attributeName'] == null)
                            {
                                assert('$elementInformation["type"] == "Null"'); // Not Coding Standard
                            }
                            else
                            {
                                /* Is attribute present more than once on the view? */
                                assert('!in_array($elementInformation["attributeName"], $attributeNames)');
                                $attributeNames[] = $elementInformation['attributeName'];
                                assert('is_string($elementInformation["attributeName"])');
                            }
                        }
                        $cellCount++;
                        $designerRules = DesignerRulesFactory::createDesignerRulesByView(get_called_class());
                        assert('$cellCount <= $designerRules->maxCellsPerRow()');
                    }
                }
            }
            if (isset($metadata['global']['toolbar']))
            {
                assert('is_array($metadata["global"]["toolbar"]["elements"])');
                assert('count($metadata["global"]["toolbar"]) == 1');
                $elementTypes = array();
                foreach ($metadata['global']['toolbar']['elements'] as $elementInformation)
                {
                    assert('isset($elementInformation["type"])');
                    assert('!in_array($elementInformation["type"], $elementTypes)');
                    $elementTypes[] = $elementInformation['type'];
                    $elementclassname = $elementInformation['type'] . 'ActionElement';
                    assert('class_exists($elementclassname)');
                }
            }
            if (isset($metadata['global']['nonPlaceableAttributeNames']))
            {
                assert('is_array($metadata["global"]["nonPlaceableAttributeNames"])');
            }
            assert('!isset($metadata["derivedAttributeTypes"])');
            if (isset($metadata['global']['derivedAttributeTypes']))
            {
                assert('is_array($metadata["global"]["derivedAttributeTypes"])');
                foreach ($metadata['global']['derivedAttributeTypes'] as $elementType)
                {
                    $elementclassname = $elementType . 'Element';
                    assert('class_exists($elementclassname)');
                }
            }
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }
    }
?>