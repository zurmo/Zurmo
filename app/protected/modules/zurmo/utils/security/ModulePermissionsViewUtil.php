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
     * Helper class for module permission views
     */
    abstract class ModulePermissionsViewUtil extends SecurityViewUtil
    {
        /**
         * Makes view metadata based on data array.
         * @return array - view metadata
         */
        protected static function makeMetadataFromData($data)
        {
            $elements        = array();
            $calledClassName = get_called_class();
            $viewPermissions = $calledClassName::getPermissionsForView();
            foreach ($data as $moduleClassName => $modulePermissions)
            {
                foreach ($viewPermissions as $permission)
                {
                    $permissionsInformation       = $modulePermissions[$permission];
                    $element                      = $calledClassName::getElementInformation(
                                                        $moduleClassName,
                                                        $permission,
                                                        $permissionsInformation);
                    $elements[$moduleClassName][] = $element;
                }
            }
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                            ),
                        ),
                    ),
                )
            );
            foreach ($elements as $moduleClassName => $moduleElements)
            {
                $rowTitle                                  = $moduleClassName::getSecurableModuleDisplayName();
                $metadata['global']['panels'][0]['rows'][] = $calledClassName::getRowByModuleElement(
                                                                $moduleElements,
                                                                $rowTitle);
            }
            return $metadata;
        }

        protected static function getRowByModuleElement($moduleElements, $rowTitle)
        {
            assert('is_array($moduleElements)');
            assert('$rowTitle == null | is_string($rowTitle)');
            $row = array();
            foreach ($moduleElements as $element)
            {
                if ($rowTitle != null)
                {
                    $row['title'] = $rowTitle;
                }
                $row['cells'][] = array(
                            'elements' => array(
                                $element,
                    ),
                );
            }
            return $row;
        }

        /**
         * Returns list of applicable permissions to show
         * on the view and in the order to show them.
         * @return array of permissions
         */
        public static function getPermissionsForView()
        {
            $permissions     = array();
            $calledClassName = get_called_class();
            $permissionNames = $calledClassName::getPermissionNamesForView();
            foreach ($permissionNames as $name)
            {
                $permissions[] = constant('Permission::' . $name);
            }
            return $permissions;
        }

        /**
         * Returns list of applicable permission names to show
         * on the view and in the order to show them.
         * @return array of permission names
         */
        public static function getPermissionNamesForView()
        {
            return array(
                'READ',
                'WRITE',
                'DELETE',
            );
        }

        /**
         * Returns list of permission labels
         */
        public static function getPermissionLabelsForView()
        {
            return array(
                'READ'   => Yii::t('Default', 'Read'),
                'WRITE'  => Yii::t('Default', 'Write'),
                'DELETE' => Yii::t('Default', 'Delete'),
            );
        }
    }
?>