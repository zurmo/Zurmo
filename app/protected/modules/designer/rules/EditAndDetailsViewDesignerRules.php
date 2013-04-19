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

    class EditAndDetailsViewDesignerRules extends DesignerRules
    {
        public function getCellSettingsAttributes()
        {
            return array(
                array('attributeName' => 'detailViewOnly', 'type' => 'CheckBox')
            );
        }

        public function getDisplayName()
        {
            return Zurmo::t('DesignerModule', 'Detail and Edit View');
        }

        public function getNonPlaceableLayoutAttributeNames()
        {
            return array(
                'createdDateTime',
                'modifiedDateTime',
                'createdByUser',
                'modifiedByUser',
                'id'
            );
        }

        public function getPanelSettingsAttributes()
        {
            return array(
                array('attributeName' => 'title', 'type' => 'Text'),
                array('attributeName' => 'detailViewOnly', 'type' => 'CheckBox')
            );
        }

        public function getSavableMetadataRules()
        {
            return array('AddBlankForDropDown');
        }

        public function maxCellsPerRow()
        {
            return 2;
        }

        public function requireAllRequiredFieldsInLayout()
        {
            return true;
        }

        public function requireOnlyUniqueFieldsInLayout()
        {
            return true;
        }

        public function canConfigureLayoutPanelsType()
        {
            return true;
        }

        /**
         * This override is here because sometimes certain elements should not be modified based on the
         * SavableMetadataRules.  An example is the contact status that should never show a blank entry for the edit
         * view.
         * @param string $rule
         * @param array $elementInformation
         * @param string $viewClassName
         */
        protected static function doesRuleApplyToElement($rule, $elementInformation, $viewClassName)
        {
            assert('is_string($rule)');
            assert('is_array($elementInformation)');
            assert('is_string($viewClassName)');
            if ($elementInformation['type'] != null)
            {
                $editAndDetailsViewRulesClassName = $elementInformation['type'] . 'EditAndDetailsViewAttributeRules';
                if (@class_exists($editAndDetailsViewRulesClassName))
                {
                    $ignoredRules              = $editAndDetailsViewRulesClassName::getIgnoredSavableMetadataRules();
                    if (in_array($rule, $ignoredRules))
                    {
                        return false;
                    }
                }
            }
            return true;
        }
    }
?>