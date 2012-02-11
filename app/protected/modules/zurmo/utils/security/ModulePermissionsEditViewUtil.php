<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Helper class to dynamically generate
     * view metadata based on data array.
     */
    class ModulePermissionsEditViewUtil extends ModulePermissionsViewUtil
    {
        protected static function getElementInformation($moduleClassName, $permission, $permissionInformation)
        {
            if ($permissionInformation['inherited']     == Permission::DENY)
            {
                $type = 'PermissionDenyText';
            }
            elseif ($permissionInformation['inherited'] == Permission::ALLOW &&
                    $permissionInformation['explicit']  == null)
            {
                $type = 'PermissionInheritedAllowStaticDropDown';
            }
            else
            {
                $type = 'PermissionStaticDropDown';
            }
            $element = array(
                        'attributeName' =>
                            FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                $moduleClassName,
                                $permission),
                        'type'          => $type,
                    );
            return $element;
        }

        /**
         * The WRITE column in the user interface is a combination of
         * the WRITE, CHANGE_OWNER, and CHANGE_PERMISSIONS permissions.
         * WRITE controlls the other 2 permissions in the user interface.
         * @return array - resolved data
         */
        public static function resolveWritePermissionsFromArray($data)
        {
            assert('is_array($data)');
            foreach ($data as $moduleNamePermission => $value)
            {
                $nameParts                          = explode(FormModelUtil::DELIMITER, $moduleNamePermission);
                list($moduleClassName, $permission) = $nameParts;
                assert('is_numeric($permission)');
                if ($permission == Permission::WRITE)
                {
                    $index        = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                        $moduleClassName,
                                        Permission::CHANGE_OWNER);
                    $data[$index] = $value;
                    $index        = FormModelUtil::getDerivedAttributeNameFromTwoStrings(
                                        $moduleClassName,
                                        Permission::CHANGE_PERMISSIONS);
                    $data[$index] = $value;
                }
            }
            return $data;
        }
    }
?>