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
     * Helper class for retrieving menus
     */
    class MenuUtil
    {
        public static function resolveByCacheAndGetVisibleAndOrderedTabMenuByCurrentUser()
        {
            try
            {
                $items = GeneralCache::getEntry(self::getMenuViewItemsCacheIdentifier());
            }
            catch (NotFoundException $e)
            {
                $items = MenuUtil::getVisibleAndOrderedTabMenuByCurrentUser();
                GeneralCache::cacheEntry(self::getMenuViewItemsCacheIdentifier(), $items);
            }
            return $items;
        }

        /**
         * The menu view items cache identifier is a combination of the language and current user.
         * This ensures if the user or language changes, that it properly retrieves the cache.
         */
        protected static function getMenuViewItemsCacheIdentifier()
        {
            return 'MenuViewItems' . Yii::app()->user->userModel->id . Yii::app()->language;
        }

        /**
         * Get the tab menu items ordered and only
         * the visible tabs based on the effective user setting for tab
         * menu items. A module can have more than one top level menu
         * item.  Utilizes current user.
         * @return array tab menu items
         */
        public static function getVisibleAndOrderedTabMenuByCurrentUser()
        {
            $moduleMenuItemsInOrder = array();
            $tabMenuItems           = array();
            $user                   = Yii::app()->user->userModel;
            $orderedModules         = self::getModuleOrderingForTabMenuByUser($user);
            $modules                = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $moduleMenuItems = MenuUtil::getAccessibleModuleTabMenuByUser(get_class($module), $user);
                if ($module->isEnabled() && count($moduleMenuItems) > 0)
                {
                    if (($order = array_search($module->getName(), $orderedModules)) !== false)
                    {
                        $moduleMenuItemsInOrder[$order] = self::resolveMenuItemsForLanguageLocalization($moduleMenuItems, get_class($module));
                    }
                }
            }
            ksort($moduleMenuItemsInOrder);
            foreach ($moduleMenuItemsInOrder as $menuItems)
            {
                foreach ($menuItems as $itemKey => $item)
                {
                    $tabMenuItems[] = $item;
                }
            }
            return $tabMenuItems;
        }

        /**
         * Get accessible shortcuts menu item based on the current user.
         * @return array of menu items.
         */
        public static function getAccessibleShortcutsMenuByCurrentUser($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $user      = Yii::app()->user->userModel;
            $metadata  = $moduleClassName::getShortCutsMenuItems();
            $menuItems = MenuUtil::resolveModuleMenuForAccess($moduleClassName, $metadata, $user);
            return self::resolveMenuItemsForLanguageLocalization($menuItems, $moduleClassName);
        }

        /**
         * Get accessible coinfigure menu item based on the current user.
         * @return array of menu items.
         */
        public static function getAccessibleConfigureMenuByCurrentUser($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $user      = Yii::app()->user->userModel;
            $metadata  = $moduleClassName::getConfigureMenuItems();
            $menuItems = MenuUtil::resolveModuleMenuForAccess($moduleClassName, $metadata, $user);
            return self::resolveMenuItemsForLanguageLocalization(  $menuItems,
                                                    $moduleClassName,
                                                    array('titleLabel', 'descriptionLabel'));
        }

        public static function getOrderedAccessibleHeaderMenuForCurrentUser()
        {
            $headerMenuItems = static::getAccessibleHeaderMenuForCurrentUser();
            usort($headerMenuItems, "static::orderHeaderMenuItems");
            return $headerMenuItems;
        }

        /**
         * Get accessible header menu item based on the specified module class name for the current user.
         * @return array of menu items.
         */
        protected static function getAccessibleHeaderMenuForCurrentUser()
        {
            $user            = Yii::app()->user->userModel;
            $modules         = Module::getModuleObjects();
            $headerMenuItems = array();
            foreach ($modules as $module)
            {
                $metadata = $module::getMetadata();
                if(!empty($metadata['global']['headerMenuItems']))
                {
                    $menuItems = MenuUtil::resolveModuleMenuForAccess(  get_class($module),
                                                                        $metadata['global']['headerMenuItems'],
                                                                        $user);

                    $headerMenuItems = array_merge($headerMenuItems,
                                                   self::resolveMenuItemsForLanguageLocalization
                                                       ($menuItems, get_class($module)));
                }
            }
            return $headerMenuItems;
        }

        protected static function orderHeaderMenuItems($a, $b) {
            if(!isset($a['order']))
            {
                $aOrder = 1;
            }
            else
            {
                $aOrder = $a['order'];
            }
            if(!isset($b['order']))
            {
                $bOrder = 1;
            }
            else
            {
                $bOrder = $b['order'];
            }
            return $aOrder - $bOrder;
        }

        /**
         * Get accessible user header menu item based for the current user.
         * @return array of menu items.
         */
        public static function getAccessibleUserHeaderMenuForCurrentUser()
        {
            $user      = Yii::app()->user->userModel;
            $metadata  = UsersModule::getMetadata();
            assert('!empty($metadata["global"]["userHeaderMenuItems"])');
            $menuItems = MenuUtil::resolveModuleMenuForAccess('UsersModule',
                                                                $metadata['global']['userHeaderMenuItems'],
                                                                $user);
            return self::resolveMenuItemsForLanguageLocalization            ($menuItems, 'UsersModule');
        }

        /**
         * Public for testing purposes only.
         * @return array of accessible tab menu items
         */
       public static function getAccessibleModuleTabMenuByUser($moduleClassName, $user)
        {
            assert('$user instanceof User && $user != null');
            assert('is_string($moduleClassName)');
            $user = Yii::app()->user->userModel;
            if (RightsUtil::canUserAccessModule($moduleClassName, $user))
            {
                $metadata = $moduleClassName::getTabMenuItems($user);
                if (!empty($metadata))
                {
                    return self::resolveModuleMenuForAccess($moduleClassName, $metadata, $user);
                }
            }
            return array();
        }

        /**
         * Currently only supports one level of nesting.
         */
        protected static function resolveModuleMenuForAccess($moduleClassName, array $menu, $user = null)
        {
            assert('is_string($moduleClassName)');
            assert('$user == null || $user instanceof User');
            $resolvedMenu = array();
            foreach ($menu as $index => $menuItem)
            {
                if (self::doesUserHaveRightToViewMenuItem($moduleClassName, $menuItem, $user))
                {
                    if (!empty($menuItem['items']))
                    {
                        $resolvedNestedItems = self::resolveModuleMenuForAccess($moduleClassName,
                                                                                $menuItem['items'],
                                                                                $user);
                        if (count($resolvedNestedItems) > 0)
                        {
                            $menuItem['items'] = $resolvedNestedItems;
                        }
                        else
                        {
                            unset($menuItem['items']);
                        }
                    }
                    $resolvedMenu[] =  $menuItem;
                }
            }
            return $resolvedMenu;
        }

        /**
         * @return boolean true if user has right to view menu items
         */
        protected static function doesUserHaveRightToViewMenuItem($moduleClassName, $item, $user)
        {
            assert('$user == null || $user instanceof User');
            if ( $user == null           ||
                !isset($item['right'])  ||
                Right::ALLOW == $user->getEffectiveRight($moduleClassName , $item['right']))
            {
                return true;
            }
            return false;
        }

        /**
         * Temporarily statically defined until we implement
         * module sorting/visibility for tab menu items.
         */
        protected static function getModuleOrderingForTabMenuByUser($user)
        {
            assert('$user instanceof User');
            $metadata = ZurmoModule::getMetadata();
            if (isset($metadata['global']['tabMenuItemsModuleOrdering']))
            {
                assert('is_array($metadata["global"]["tabMenuItemsModuleOrdering"])');
                $orderedModules = $metadata['global']['tabMenuItemsModuleOrdering'];
            }
            else
            {
                throw new NotSupportedException();
            }
            return $orderedModules;
        }

        /**
         * Given a menu item array, each label element, specified by $labelElements,
         * will be iterated over and translated
         * for the current user's language.
         * @return menu item array
         */
        protected static function resolveMenuItemsForLanguageLocalization(   $menuItems,
                                                                    $moduleClassName,
                                                                    $labelElements = array('label'))
        {
            assert('is_array($menuItems)');
            assert('is_string($moduleClassName)');
            $translationParams = LabelUtil::getTranslationParamsForAllModules();
            foreach ($menuItems as $itemKey => $item)
            {
                foreach ($labelElements as $labelElement)
                {
                    $menuItems[$itemKey][$labelElement] = Yii::t( 'Default', $item[$labelElement], $translationParams);
                }
                if (isset($item['items']))
                {
                    $menuItems[$itemKey]['items'] = self::resolveMenuItemsForLanguageLocalization($item['items'],
                                                                                                  $moduleClassName,
                                                                                                  $labelElements);
                }
            }
            return $menuItems;
        }
    }
?>