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
     * Helper class for Rights.
     */
    class RightsUtil
    {
        /**
         * @return array of all module rights data
         */
        public static function getAllModuleRightsDataByPermitable(Permitable $permitable)
        {
            $data = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module instanceof SecurableModule)
                {
                    $moduleClassName = get_class($module);
                    $rights          = $moduleClassName::getRightsNames();
                    $rightLabels    = $moduleClassName::getTranslatedRightsLabels();
                    $reflectionClass = new ReflectionClass($moduleClassName);
                    if (!empty($rights))
                    {
                        $rightsData = array();
                        foreach ($rights as $right)
                        {
                            if (!isset($rightLabels[$right]))
                            {
                                throw new NotSupportedException($right);
                            }
                            $explicit = $permitable->getExplicitActualRight  ($moduleClassName, $right);
                            $inherited = $permitable->getInheritedActualRight($moduleClassName, $right);
                            $effective = $permitable->getEffectiveRight      ($moduleClassName, $right);
                            $constants = $reflectionClass->getConstants();
                            $constantId = array_search($right, $constants);
                            $rightsData[$constantId] = array(
                                'displayName' => $rightLabels[$right],
                                'explicit'    => RightsUtil::getRightStringFromRight($explicit),
                                'inherited'   => RightsUtil::getRightStringFromRight($inherited),
                                'effective'   => RightsUtil::getRightStringFromRight($effective),
                            );
                        }
                        $data[$moduleClassName] = ArrayUtil::subValueSort($rightsData, 'displayName', 'asort');
                    }
                }
            }
            return $data;
        }

        protected static function getRightStringFromRight($right)
        {
            if ($right == Right::DENY || $right == Right::ALLOW)
            {
                return $right;
            }
            return null;
        }

        /**
         * Given a user model and a module class name, can this user
         * access the module tab or sub-tabs in the user interface.
         * If the moduleClassName is not a subclass of securableModule
         * then this function returns true.  Otherwise it checks the access
         * right for this module against the user.
         * @see SecurableModule::getAccessRightName
         * @return boolean.
         */
        public static function canUserAccessModule($moduleClassName, $user)
        {
            assert('$user instanceof User');
            assert('$moduleClassName != null && is_string($moduleClassName)');
            assert('is_subclass_of($moduleClassName, "Module")');
            if (is_subclass_of($moduleClassName, 'SecurableModule'))
            {
                $rightName = $moduleClassName::getAccessRight();
                return self::doesUserHaveAllowByRightName($moduleClassName, $rightName, $user);
            }
            return true;
        }

        /**
         * Given a user model, a module class name, and a right name,
         * checks if user can perform the right.
         * @return boolean.
         */
        public static function doesUserHaveAllowByRightName($moduleClassName, $rightName, $user)
        {
            assert('$moduleClassName != null && is_string($moduleClassName)');
            assert('$rightName == null || is_string($rightName)');

            if ($rightName == null)
            {
                return true;
            }
            assert('$user instanceof User');
            return Right::ALLOW == $user->getEffectiveRight($moduleClassName, $rightName);
        }
    }
?>
