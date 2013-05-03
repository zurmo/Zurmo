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

    class GroupsModule extends SecurableModule
    {
        const RIGHT_CREATE_GROUPS = 'Create Groups';
        const RIGHT_DELETE_GROUPS = 'Delete Groups';
        const RIGHT_ACCESS_GROUPS = 'Access Groups Tab';

        public static function getTranslatedRightsLabels()
        {
            $labels                             = array();
            $labels[self::RIGHT_CREATE_GROUPS] = Zurmo::t('ZurmoModule', 'Create Groups');
            $labels[self::RIGHT_DELETE_GROUPS] = Zurmo::t('ZurmoModule', 'Delete Groups');
            $labels[self::RIGHT_ACCESS_GROUPS] = Zurmo::t('ZurmoModule', 'Access Groups Tab');
            return $labels;
        }

        public function canDisable()
        {
            return false;
        }

        public function getDependencies()
        {
            return array('zurmo');
        }

        public function getRootModelNames()
        {
            return array('Group');
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'adminTabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ZurmoModule', 'Groups')",
                        'url'   => array('/zurmo/group'),
                        'right' => self::RIGHT_ACCESS_GROUPS,
                        'items' => array(
                            array(
                                'label' => "eval:Zurmo::t('ZurmoModule', 'Create Group')",
                                'url'   => array('/zurmo/group/create'),
                                'right' => self::RIGHT_CREATE_GROUPS
                            ),
                            array(
                                'label' => "eval:Zurmo::t('ZurmoModule', 'Groups')",
                                'url'   => array('/zurmo/group'),
                                'right' => self::RIGHT_ACCESS_GROUPS
                            ),
                        ),
                    ),
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ZurmoModule', 'Groups')",
                        'descriptionLabel' => "eval:Zurmo::t('ZurmoModule', 'Manage Groups')",
                        'route'            => '/zurmo/group',
                        'right'            => self::RIGHT_ACCESS_GROUPS,
                    ),
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Group';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_GROUPS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_GROUPS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_GROUPS;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'GroupsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('GroupsDemoDataMaker');
        }
    }
?>
