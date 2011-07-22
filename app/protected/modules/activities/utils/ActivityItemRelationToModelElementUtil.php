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
     * Helper class to resolve correct model element to use in the user interface based on the user's rights to
     * access specified modules.  Special consideration is given for the contact model.  In the case of the contact
     * model, the access for the leads module must be considered. This will help determine how many states to show.
     */
    class ActivityItemRelationToModelElementUtil
    {
        public static function resolveModelElementClassNameByActionSecurity($modelClassName, $user)
        {
            assert('is_string($modelClassName)');
            assert('$user instanceof User && $user->id > 0');
            if ($modelClassName == 'Contact')
            {
                $canAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', $user);
                $canAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule', $user);
                if ($canAccessContacts && $canAccessLeads)
                {
                    return 'AllStatesContactElement';
                }
                elseif (!$canAccessContacts && $canAccessLeads)
                {
                    return 'FirstStatesElement';
                }
                elseif ($canAccessContacts && !$canAccessLeads)
                {
                    return 'ContactElement';
                }
                else
                {
                    return null;
                }
            }
            else
            {
                $moduleClassName = $modelClassName::getModuleClassName();
                if (!RightsUtil::canUserAccessModule($moduleClassName, $user))
                {
                    return null;
                }
                return $modelClassName . 'Element';
            }
        }
    }
?>