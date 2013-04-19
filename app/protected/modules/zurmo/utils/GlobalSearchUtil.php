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
     * Helper class to assist with Global Search functionality.
     */
    class GlobalSearchUtil
    {
        /**
         * Given a user, return an array of module names and their translated labels, for which the user
         * has the right to access and only modules that support the global search.
         * @param  User $user
         * @return array of module names and labels.
         */
        public static function getGlobalSearchScopingModuleNamesAndLabelsDataByUser(User $user)
        {
            assert('$user->id > 0');
            try
            {
                return GeneralCache::getEntry(self::getGlobalSearchScopingCacheIdentifier($user));
            }
            catch (NotFoundException $e)
            {
                $moduleNamesAndLabels = self::findGlobalSearchScopingModuleNamesAndLabelsDataByUser($user);
                GeneralCache::cacheEntry(self::getGlobalSearchScopingCacheIdentifier($user), $moduleNamesAndLabels);
                return $moduleNamesAndLabels;
            }
        }

        protected static function findGlobalSearchScopingModuleNamesAndLabelsDataByUser(User $user)
        {
            assert('$user->id > 0');
            $moduleNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                    $globalSearchFormClassName = $module::getGlobalSearchFormClassName();
                    if (GlobalSearchUtil::resolveIfModuleShouldBeGloballySearched($module) &&
                        $globalSearchFormClassName != null && RightsUtil::canUserAccessModule(get_class($module), $user))
                    {
                        $moduleNamesAndLabels[$module->getName()] = $module::getModuleLabelByTypeAndLanguage('Plural');
                    }
            }
            return $moduleNamesAndLabels;
        }

       /**
         * The global search scoping module names cache identifier is a combination of the
         * language and specified user.  This ensures if the user or language changes,
         * that it properly retrieves the cache.
         */
        protected static function getGlobalSearchScopingCacheIdentifier(User $user)
        {
            assert('$user->id > 0');
            return 'GlobalSearchScopingModuleNamesAndLabels' . $user->id . Yii::app()->language;
        }

        /**
         * Add a 'All' element as the first element in the array.
         * @param  array $moduleNamesAndLabels
         * @return modified $moduleNamesAndLabels array with All as first element.
         */
        public static function resolveModuleNamesAndLabelsDataWithAllOption(& $moduleNamesAndLabels)
        {
            $moduleNamesAndLabels = array_merge(array('All' => Zurmo::t('ZurmoModule', 'All')), $moduleNamesAndLabels);
        }

        /**
         * Given a $_GET array, resolve the value of the globalSearchScope.  if the globalSearchScope
         * isset but the value is 'All', return null, since this is the same as having null to begin with.
         * @param  array $get
         * @return null or array of globalSearchScope value.
         */
        public static function resolveGlobalSearchScopeFromGetData($get)
        {
            if (!isset($get['globalSearchScope']) || in_array('All', $get['globalSearchScope']))
            {
                return null;
            }
            else
            {
                return $get['globalSearchScope'];
            }
        }

        /**
         * Given a module, return true/false if it should be able to be globally searched.  This is just an initial
         * safety pass as the module will still need to return a class for $module::getGlobalSearchFormClassName();
         * This handles the exception of the UsersModule which can have module scoping for UsersListView, but we do not
         * want this to be globally searched.
         * @param Module $module
         */
        public static function resolveIfModuleShouldBeGloballySearched(Module $module)
        {
            if ($module::modelsAreNeverGloballySearched())
            {
                return false;
            }
            return true;
        }
    }
?>