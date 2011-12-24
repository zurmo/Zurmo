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
     * Removes module permission data that is not applicable
     * for the administrative group module permissions user
     * interface.  Some modules, while they do have permissions
     * are not administered through the user interface.
     */
    class GroupModulePermissionsDataToEditViewAdapater
    {
        public static function resolveData($data)
        {
            assert('is_array($data)');
            $resolvedData = array();
            foreach ($data as $moduleClassName => $modulePermissions)
            {
                if (in_array($moduleClassName, GroupModulePermissionsDataToEditViewAdapater::getAdministrableModuleClassNames()))
                {
                    $resolvedData[$moduleClassName] = $modulePermissions;
                }
            }
            return $resolvedData;
        }

        /**
         * @return array of module class names that are available for
         * module permissions administration through the user interface
         */
        public static function getAdministrableModuleClassNames()
        {
            return array(
                'AccountsModule',
                'ContactsModule',
                'MeetingsModule',
                'NotesModule',
                'OpportunitiesModule',
                'TasksModule'
            );
        }
    }
?>