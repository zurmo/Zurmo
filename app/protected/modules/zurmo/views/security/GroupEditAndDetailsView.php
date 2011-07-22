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
     * Base view for the group edit view and details view.
     */
    class GroupEditAndDetailsView extends EditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'           => 'CancelLink', 'renderType' => 'Edit'),
                            array('type'           => 'SaveButton', 'renderType' => 'Edit'),
                            array('type'           => 'EditLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'canModifyName'
                            ),
                            array(
                                'type'             => 'GroupUserMembershipEditLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'canModifyMemberships'
                                ),
                            array(
                                'type'             => 'GroupModulePermissionsEditLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'canGivePermissions'
                                ),
                            array(
                                'type'             => 'GroupRightsEditLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'canModifyRights'
                                ),
                            array(
                                'type'             => 'GroupPoliciesEditLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'canModifyPolicies'
                                ),
                            array(
                                'type'             => 'GroupDeleteLink',
                                'renderType'       => 'Details',
                                'resolveToDisplay' => 'isDeletable'
                            ),
                        ),
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'group', 'type' => 'ParentGroup'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to check for different scenarios depending on if the group is
         * special or not. Everyone and SuperAdministrators are special groups
         * for example.
         * Checks for $elementInformation['resolveToDisplay'] to be present and if it is,
         * will run the resolveName as a function on the group model.
         * @return boolean
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if (isset($elementInformation['resolveToDisplay']))
            {
                $resolveMethodName = $elementInformation['resolveToDisplay'];
                if (!$this->model->{$resolveMethodName}())
                {
                    return false;
                }
            }
            return true;
        }
    }
?>
