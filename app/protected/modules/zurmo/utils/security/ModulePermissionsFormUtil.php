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
     * Helper class to make a ModulePermissionsForm
     * and populate the data attribute.
     */
    class ModulePermissionsFormUtil
    {
        /**
         * @param $modulesPermissionsData - combined array of all permission
         * and existing permissions on a permittable.  Organized by module.
         * Example below showing just the accounts module:
         * @code
            <?php
                $modulePermissionsData = array(
                    'accounts' => array(
                        'CREATE' => array(
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'CHANGE_OWNER' => array(
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'CHANGE_PERMISSIONS' => array(
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'DELETE' => array(
                            'explicit'    => null,
                            'inherited'   => null,
                        ),
                        'READ' => array(
                            'explicit'    => null,
                            'inherited'   => Permission::ALLOW,
                        ),
                        'WRITE' => array(
                            'explicit'    => null,
                            'inherited'   => Permission::DENY,
                        ),
                    ),
                );
            ?>
         * @endcode
         */
        public static function makeFormFromPermissionsData($data)
        {
            assert('is_array($data)');
            $form       = new ModulePermissionsForm();
            $form->data = $data;
            return $form;
        }

        /**
         * Set Module Permissions from Post
         * @return boolean - true on success
         */
        public static function setPermissionsFromCastedPost(array $validatedAndCastedPostData, $permitable)
        {
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');
            foreach ($validatedAndCastedPostData as $concatenatedIndex => $value)
            {
                $moduleClassName = self::getModuleClassNameFromPostConcatenatedIndexString($concatenatedIndex);
                $permission      = self::getPermissionFromPostConcatenatedIndexString($concatenatedIndex);
                $saved           = self::AddorRemoveSpecificPermission($moduleClassName, $permitable, $permission, $value);
                if (!$saved)
                {
                    return false;
                }
            }
            return true;
        }

        /**
         * @return $moduleClassName string
         */
        protected static function getModuleClassNameFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                          = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $permission) = $nameParts;
            return $moduleClassName;
        }

        /**
         * @return intval permission
         */
        protected static function getPermissionFromPostConcatenatedIndexString($string)
        {
            assert('is_string($string)');
            $nameParts                          = explode(FormModelUtil::DELIMITER, $string);
            list($moduleClassName, $permission) = $nameParts;
            $permission                         = intval($permission);
            return $permission;
        }

        protected static function AddorRemoveSpecificPermission($moduleClassName, $permitable, $permission, $value)
        {
            assert('is_string($moduleClassName)');
            assert('$permitable instanceof Permitable');
            assert('$permitable->id > 0');
            assert('is_int($permission)');
            assert('is_int($value) || $value == null');
            $item = NamedSecurableItem::getByName($moduleClassName);
            if (!empty($value) && $value    == Permission::ALLOW)
            {
                $item->addPermissions   ($permitable, $permission, Permission::ALLOW);
            }
            elseif (!empty($value) && $value == Permission::DENY)
            {
                $item->addPermissions   ($permitable, $permission, Permission::DENY);
            }
            else
            {
                $item->removePermissions($permitable, $permission);
            }
            $saved = $item->save();
            $item->forget();
            return $saved;
        }

        /**
         * Used to properly type cast incoming POST data
         */
        public static function typeCastPostData($postData)
        {
            assert('is_array($postData)');
            foreach ($postData as $concatenatedIndex => $value)
            {
                if ($value != '')
                {
                    $postData[$concatenatedIndex] = intval($value);
                }
            }
            return $postData;
        }
    }
?>