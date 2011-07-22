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

    class GroupsModule extends SecurableModule
    {
        const RIGHT_CREATE_GROUPS = 'Create Groups';
        const RIGHT_ACCESS_GROUPS = 'Access Groups Tab';

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
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Groups',
                        'descriptionLabel' => 'Manage Groups',
                        'route'            => '/zurmo/group',
                        'right'            => self::RIGHT_ACCESS_GROUPS,
                    ),
                ),
                'shortcutsMenuItems' => array(
                    array(
                        'label' => 'Groups',
                        'url'   => array('/zurmo/group'),
                        'right' => self::RIGHT_ACCESS_GROUPS,
                        'items' => array(
                            array(
                                'label' => 'Create Group',
                                'url'   => array('/zurmo/group/create'),
                                'right' => self::RIGHT_ACCESS_GROUPS,
                            ),
                            array(
                                'label' => 'Groups',
                                'url'   => array('/zurmo/group'),
                                'right' => self::RIGHT_ACCESS_GROUPS,
                            ),
                        ),
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
    }
?>
