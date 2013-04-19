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
     * Class to help the designer module editing understand
     * how to parse and handle the views it is editing.
     */
    abstract class DesignerRules
    {
        public function allowEditInLayoutTool()
        {
            return true;
        }

        public function canAddPanels()
        {
            return true;
        }

        public function canAddRows()
        {
            return true;
        }

        public function canMergeAndSplitCells()
        {
            return true;
        }

        public function canModifyCellSettings()
        {
            return true;
        }

        public function canModifyPanelSettings()
        {
            return true;
        }

        public function canMovePanels()
        {
            return true;
        }

        public function canMoveRows()
        {
            return true;
        }

        public function canRemovePanels()
        {
            return true;
        }

        public function canRemoveRows()
        {
            return true;
        }

        public function canConfigureLayoutPanelsType()
        {
            return false;
        }

        /**
         * Override to add special formatting to the savableMetadata
         */
        public function formatSavableMetadataFromLayout($metadata, $viewClassName)
        {
            assert('is_string($viewClassName)');
            $rules = $this->getSavableMetadataRules();
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
                                foreach ($rules as $rule)
                                {
                                    if (static::doesRuleApplyToElement($rule, $elementInformation, $viewClassName))
                                    {
                                        $ruleClassName = $rule . 'ViewMetadataRules';
                                        $ruleClassName::resolveElementMetadata(
                                            $elementInformation,
                                            $metadata['global']['panels'][$panelKey]['rows'][$rowKey]['cells'][$cellKey]['elements'][$elementKey]
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $metadata;
        }

        /**
         * @see formatSavableMetadataFromLayout.  This method is if you just want to format a single element and return it.
         * @return formatted element
         */
        public function formatSavableElement($element, $viewClassName)
        {
            assert('is_array($element)');
            $rules        = $this->getSavableMetadataRules();
            $finalElement = $element;
            foreach ($rules as $rule)
            {
                if (static::doesRuleApplyToElement($rule, $element, $viewClassName))
                {
                    $ruleClassName = $rule . 'ViewMetadataRules';
                    $ruleClassName::resolveElementMetadata(
                        $element,
                        $finalElement
                    );
                }
            }
            return $finalElement;
        }

        /**
         * Override if special handling is required to ignore certain rules from applying to the element before
         * the metadata is saved. @see formatSavableMetadataFromLayout()
         * @param string $rule
         * @param array $elementInformation
         * @param string $viewClassName
         */
        protected static function doesRuleApplyToElement($rule, $elementInformation, $viewClassName)
        {
            return true;
        }

        public function getCellSettingsAttributes()
        {
            return array();
        }

        /**
         * Override if a rule requires that certain derived attributes
         * types be made not available for placement using the layout tool
         * @return array
         */
        public function getDerivedAttributeTypes()
        {
            return array();
        }

        public function getDisplayName()
        {
        }

        /**
         * Override if you need to return a different display name than what designer rules provides.
         */
        public function resolveDisplayNameByView($viewClassName)
        {
            return $this->getDisplayName();
        }

        public function getMetadataViewClassNames($viewClassName, $moduleClassName)
        {
            return array($viewClassName);
        }

        public function getPanelSettingsAttributes()
        {
            return array();
        }

        public function getSavableMetadataRules()
        {
        }

        public function mergeRowAndAttributePlacement()
        {
            return false;
        }

        public function requireAllRequiredFieldsInLayout()
        {
            return false;
        }

        public function requireOnlyUniqueFieldsInLayout()
        {
            return false;
        }

        /**
         * Override if a rule requires that certain attributes
         * be made not available for placement using the layout tool
         */
        public function getNonPlaceableLayoutAttributeNames()
        {
            return array();
        }

        /**
         * Override if a rule requires that certain attributes types
         * be made not available for placement using the layout tool
         */
        public function getNonPlaceableLayoutAttributeTypes()
        {
            return array();
        }

        /**
         * Adds an extra formatting to ensure uniformity
         * for layout parsing.  Adds 'wide' => true if the
         * cell should span.
         */
        public function formatEditableMetadataForLayoutParsing($metadata)
        {
            assert('isset($metadata["global"]["panels"])');
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
                                if (count($row['cells']) == 1 && count($row['cells']) < $this->maxCellsPerRow())
                                {
                                    $metadata['global']['panels'][$panelKey]['rows'][$rowKey]['cells'][$cellKey]['elements'][$elementKey]['wide'] = true;
                                }
                            }
                        }
                    }
                }
            }
            return $metadata;
        }

        public function maxCellsPerRow()
        {
            return 1;
        }

        /**
         * If an element is null, should we place it in the metadata.
         */
        public function shouldPlaceNullElement()
        {
            return true;
        }
    }
?>