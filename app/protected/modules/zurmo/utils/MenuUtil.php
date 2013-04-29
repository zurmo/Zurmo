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
     * Helper class for retrieving menus
     */
    class MenuUtil
    {
        const MENU_VIEW_ITEMS       = 'MenuViewItems';
        const ADMIN_MENU_VIEW_ITEMS = 'AdminMenuViewItems';

        public static function resolveByCacheAndGetVisibleAndOrderedTabMenuByCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::resolveByCacheAndGetVisibleAndOrderedTabMenuByUser($user);
        }

        public static function resolveByCacheAndGetVisibleAndOrderedTabMenuByUser($user)
        {
            assert('$user instanceof User && $user != null');
            try
            {
                $items = GeneralCache::getEntry(self::getMenuViewItemsCacheIdentifier());
            }
            catch (NotFoundException $e)
            {
                $items = self::getVisibleAndOrderedTabMenuByUser($user);
                GeneralCache::cacheEntry(self::getMenuViewItemsCacheIdentifier(), $items);
            }
            static::resolveTabMenuForDynamicLabelContent($items);
            return $items;
        }

        public static function resolveByCacheAndGetVisibleAndOrderedAdminTabMenuByCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::resolveByCacheAndGetVisibleAndOrderedAdminTabMenuByUser($user);
        }

        public static function resolveByCacheAndGetVisibleAndOrderedAdminTabMenuByUser($user)
        {
            assert('$user instanceof User && $user != null');
            try
            {
                $items = GeneralCache::getEntry(self::getAdminMenuViewItemsCacheIdentifier());
            }
            catch (NotFoundException $e)
            {
                $items = self::getVisibleAndOrderedAdminTabMenuByUser($user);
                GeneralCache::cacheEntry(self::getAdminMenuViewItemsCacheIdentifier(), $items);
            }
            return $items;
        }

        /**
         * The menu view items cache identifier is a combination of the language and current user.
         * This ensures if the user or language changes, that it properly retrieves the cache.
         */
        protected static function getMenuViewItemsCacheIdentifier()
        {
            return self::getMenuViewItemsCacheIdentifierByUser(Yii::app()->user->userModel);
        }

        public static function getMenuViewItemsCacheIdentifierByUser($user)
        {
            return self::MENU_VIEW_ITEMS . $user->id . Yii::app()->language;
        }

        /**
         * The admin menu view items cache identifier is a combination of the language and current user.
         * This ensures if the user or language changes, that it properly retrieves the cache.
         */
        protected static function getAdminMenuViewItemsCacheIdentifier()
        {
            return self::getAdminMenuViewItemsCacheIdentifierByUser(Yii::app()->user->userModel);
        }

        public static function getAdminMenuViewItemsCacheIdentifierByUser($user)
        {
            assert('$user instanceof User && $user != null');
            return self::ADMIN_MENU_VIEW_ITEMS . $user->id . Yii::app()->language;
        }

        public static function forgetCacheEntryForTabMenuByUser($user)
        {
            $identifier = self::getMenuViewItemsCacheIdentifierByUser($user);
            GeneralCache::forgetEntry($identifier);
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
            return self::getVisibleAndOrderedTabMenuByUser(Yii::app()->user->userModel);
        }

        public static function getVisibleAndOrderedTabMenuByUser($user)
        {
            assert('$user instanceof User && $user != null');
            $moduleMenuItemsInOrder = array();
            $tabMenuItems           = self::getCustomVisibleAndOrderedTabMenuItemsByUser($user);
            $orderedModules         = self::getModuleOrderingForTabMenuByUser($user);
            $modules                = Module::getModuleObjects();
            foreach ($modules as $moduleId => $module)
            {
                $moduleMenuItems = self::getAccessibleModuleTabMenuByUser(get_class($module), $user);
                if ($module->isEnabled() && count($moduleMenuItems) > 0)
                {
                    if (($order = array_search($module->getName(), $orderedModules)) !== false)
                    {
                        $moduleMenuItemsInOrder[$order] = self::resolveMenuItemsForLanguageLocalization(
                                                          $moduleMenuItems, get_class($module));
                        $moduleMenuItemsInOrder[$order][0]['moduleId']    = $moduleId;
                        $moduleMenuItemsInOrder[$order][0]['itemOptions'] = array('id' => $moduleId);
                    }
                }
            }
            ksort($moduleMenuItemsInOrder);
            foreach ($moduleMenuItemsInOrder as $menuItems)
            {
                foreach ($menuItems as $item)
                {
                    $tabMenuItems[$item['moduleId']] = $item;
                }
            }
            foreach($tabMenuItems as $key => $menuItem)
            {
                if (!is_array($menuItem))
                {
                    unset($tabMenuItems[$key]);
                }
            }
            return $tabMenuItems;
        }

        public static function getCustomVisibleAndOrderedTabMenuItemsByUser($user)
        {
            $tabMenuItems = array();
            if (!null == ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'VisibleAndOrderedTabMenuItems'))
            {
                $customOrderedTabMenuItems = unserialize(ZurmoConfigurationUtil::getByUserAndModuleName(
                                             $user, 'ZurmoModule', 'VisibleAndOrderedTabMenuItems'));
                foreach ($customOrderedTabMenuItems as $moduleId)
                {
                    $tabMenuItems[$moduleId] = "";
                }
            }
            return $tabMenuItems;
        }

        /**
         * Get the admin tab menu items ordered and only
         * the visible tabs based on the effective user setting for tab
         * menu items. A module can have more than one top level menu
         * item.  Utilizes current user.
         * @return array tab menu items
         */
        public static function getVisibleAndOrderedAdminTabMenuByCurrentUser()
        {
            return self::getVisibleAndOrderedAdminTabMenuByUser(Yii::app()->user->userModel);
        }

        public static function getVisibleAndOrderedAdminTabMenuByUser($user)
        {
            assert('$user instanceof User && $user != null');
            $moduleMenuItemsInOrder = array();
            $tabMenuItems           = array();
            $orderedModules         = self::getModuleOrderingForAdminTabMenuByUser($user);
            $modules                = Module::getModuleObjects();
            foreach ($modules as $moduleId => $module)
            {
                $moduleMenuItems = self::getAccessibleModuleAdminTabMenuByUser(get_class($module), $user);
                if ($module->isEnabled() && count($moduleMenuItems) > 0)
                {
                    if (($order = array_search($module->getName(), $orderedModules)) !== false)
                    {
                        $moduleMenuItemsInOrder[$order] = self::resolveMenuItemsForLanguageLocalization(
                                                          $moduleMenuItems, get_class($module));
                        $moduleMenuItemsInOrder[$order][0]['moduleId'] = $moduleId;
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
        public static function getAccessibleShortcutsCreateMenuByCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::getAccessibleShortcutsCreateMenuByUser($user);
        }

        public static function getAccessibleShortcutsCreateMenuByUser($user)
        {
            assert('$user instanceof User && $user != null');
            $modules         = Module::getModuleObjects();
            $createMenuItems = array('label' => Zurmo::t('ZurmoModule', 'Create'),
                                     'url'   => null,
                                     'items' => array());
            foreach ($modules as $module)
            {
                $metadata  = $module::getShortCutsCreateMenuItems();
                $menuItems = self::resolveModuleMenuForAccess(get_class($module), $metadata, $user);
                $menuItems = self::resolveMenuItemsForLanguageLocalization($menuItems, get_class($module));
                if (!empty($menuItems))
                {
                    $createMenuItems['items'] = array_merge($createMenuItems['items'],
                                                self::resolveMenuItemsForLanguageLocalization
                                                ($menuItems, get_class($module)));
                }
            }
            if (empty($createMenuItems['items']))
            {
                return array();
            }
            return $createMenuItems;
        }

        /**
         * Get accessible coinfigure menu item based on the current user.
         * @return array of menu items.
         */
        public static function getAccessibleConfigureMenuByCurrentUser($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $user = Yii::app()->user->userModel;
            return self::getAccessibleConfigureMenuByUser($moduleClassName, $user);
        }

        public static function getAccessibleConfigureMenuByUser($moduleClassName, $user)
        {
            assert('is_string($moduleClassName)');
            assert('$user instanceof User && $user != null');
            $metadata  = $moduleClassName::getConfigureMenuItems();
            $menuItems = self::resolveModuleMenuForAccess($moduleClassName, $metadata, $user);
            return self::resolveMenuItemsForLanguageLocalization($menuItems,
                                                                 $moduleClassName,
                                                                 array('titleLabel', 'descriptionLabel'));
        }

        public static function getAccessibleConfigureSubMenuByCurrentUser($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $user = Yii::app()->user->userModel;
            return self::getAccessibleConfigureSubMenuByUser($moduleClassName, $user);
        }

        public static function getAccessibleConfigureSubMenuByUser($moduleClassName, $user)
        {
            assert('is_string($moduleClassName)');
            assert('$user instanceof User && $user != null');
            $metadata  = $moduleClassName::getConfigureSubMenuItems();
            $menuItems = self::resolveModuleMenuForAccess($moduleClassName, $metadata, $user);
            return self::resolveMenuItemsForLanguageLocalization($menuItems,
                                                                 $moduleClassName,
                                                                 array('titleLabel', 'descriptionLabel'));
        }

        public static function getOrderedAccessibleHeaderMenuForCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::getOrderedAccessibleHeaderMenuForUser($user);
        }

        public static function getOrderedAccessibleHeaderMenuForUser($user)
        {
            assert('$user instanceof User && $user != null');
            $headerMenuItems = static::getAccessibleHeaderMenuForUser($user);
            usort($headerMenuItems, "static::orderHeaderMenuItems");
            return $headerMenuItems;
        }

        /**
         * Get accessible header menu item based on the specified module class name for the current user.
         * @return array of menu items.
         */
        protected static function getAccessibleHeaderMenuForCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::getAccessibleHeaderMenuForUser($user);
        }

        protected static function getAccessibleHeaderMenuForUser($user)
        {
            assert('$user instanceof User && $user != null');
            $modules         = Module::getModuleObjects();
            $headerMenuItems = array();
            foreach ($modules as $module)
            {
                $metadata = $module::getMetadata();
                if (!empty($metadata['global']['headerMenuItems']))
                {
                    $menuItems = self::resolveModuleMenuForAccess(get_class($module),
                                                                  $metadata['global']['headerMenuItems'],
                                                                  $user);
                    $headerMenuItems = array_merge($headerMenuItems,
                                                   self::resolveMenuItemsForLanguageLocalization
                                                   ($menuItems, get_class($module)));
                }
            }
            return $headerMenuItems;
        }

        protected static function orderHeaderMenuItems($a, $b)
        {
            if (!isset($a['order']))
            {
                $aOrder = 1;
            }
            else
            {
                $aOrder = $a['order'];
            }
            if (!isset($b['order']))
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
        public static function getAccessibleOrderedUserHeaderMenuForCurrentUser()
        {
            $user = Yii::app()->user->userModel;
            return self::getAccessibleOrderedUserHeaderMenuForUser($user);
        }

        public static function getAccessibleOrderedUserHeaderMenuForUser($user)
        {
            assert('$user instanceof User && $user != null');
            $modules         = Module::getModuleObjects();
            $headerMenuItems = array();
            foreach ($modules as $module)
            {
                $metadata = $module::getMetadata();
                if (!empty($metadata['global']['userHeaderMenuItems']))
                {
                    $menuItems = self::resolveModuleMenuForAccess(get_class($module),
                                                                  $metadata['global']['userHeaderMenuItems'],
                                                                  $user);
                    $headerMenuItems = array_merge($headerMenuItems,
                                                   self::resolveMenuItemsForLanguageLocalization
                                                   ($menuItems, get_class($module)));
                }
            }
            $orderedHeaderMenuItems = array();
            foreach ($headerMenuItems as $item)
            {
                if (isset($item['order']))
                {
                    $orderedHeaderMenuItems[$item['order']] = $item;
                }
                else
                {
                    $orderedHeaderMenuItems[] = $item;
                }
            }
            ksort($orderedHeaderMenuItems);
            return $orderedHeaderMenuItems;
        }

        /**
         * Public for testing purposes only.
         * @return array of accessible tab menu items
         */
        public static function getAccessibleModuleTabMenuByUser($moduleClassName, $user)
        {
            assert('$user instanceof User && $user != null');
            assert('is_string($moduleClassName)');
            if (null == $user)
            {
                $user = Yii::app()->user->userModel;
            }
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
         * Public for testing purposes only.
         * @return array of accessible admin tab menu items
         */
        public static function getAccessibleModuleAdminTabMenuByUser($moduleClassName, $user)
        {
            assert('$user instanceof User && $user != null');
            assert('is_string($moduleClassName)');
            if (null == $user)
            {
                $user = Yii::app()->user->userModel;
            }
            if (RightsUtil::canUserAccessModule($moduleClassName, $user))
            {
                $metadata = $moduleClassName::getAdminTabMenuItems($user);
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
         * Temporarily statically defined until we implement
         * module sorting/visibility for tab menu items.
         */
        protected static function getModuleOrderingForAdminTabMenuByUser($user)
        {
            assert('$user instanceof User');
            $metadata = ZurmoModule::getMetadata();
            if (isset($metadata['global']['adminTabMenuItemsModuleOrdering']))
            {
                assert('is_array($metadata["global"]["adminTabMenuItemsModuleOrdering"])');
                $orderedModules = $metadata['global']['adminTabMenuItemsModuleOrdering'];
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
                    MetadataUtil::resolveEvaluateSubString($menuItems[$itemKey][$labelElement], 'translationParams', $translationParams);
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

        protected static function resolveTabMenuForDynamicLabelContent(& $items)
        {
            foreach ($items as $key => $item)
            {
                if (isset($items[$key]['dynamicLabelContent']))
                {
                    MetadataUtil::resolveEvaluateSubString($items[$key]['dynamicLabelContent']);
                    if (isset($items[$key]['items']))
                    {
                        static::resolveTabMenuForDynamicLabelContent($items[$key]['items']);
                    }
                }
            }
        }
    }
?>