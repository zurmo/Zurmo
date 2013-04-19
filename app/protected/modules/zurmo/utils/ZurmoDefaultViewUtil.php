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
     * Helper class for constructing the default view used by the classes that extend the ZurmoPageView.
     */
    class ZurmoDefaultViewUtil
    {
        protected static $showRecentlyViewed = true;

        /**
         * Override if you have a module with views that when displayed, the left side menu shows a different module
         * as 'active'
         * @var string
         */
        protected static $activeModuleId;

        /**
         * Given a controller, contained view, construct the gridview
         * used by the designer page view.
         * @param CController $controller
         * @param View $containedView
         * @param mixed $activeNodeModuleClassName (null or string)
         */
        public static function makeViewWithBreadcrumbsForCurrentUser(CController $controller,
                                                                     View $containedView,
                                                                     $breadcrumbLinks,
                                                                     $breadcrumbViewClassName,
                                                                     $cssClasses = array())
        {
            assert('is_array($breadcrumbLinks)');
            $gridView    = new GridView(2, 1);
            $gridView->setCssClasses($cssClasses);
            $gridView->setView(new $breadcrumbViewClassName($controller->getId(),
                                                            $controller->getModule()->getId(),
                                                            $breadcrumbLinks), 0, 0);
            $gridView->setView($containedView, 1, 0);
            return static::makeStandardViewForCurrentUser($controller, $gridView);
        }

        /**
         * Given a controller and contained view, construct the gridview
         * used by the zurmo page view.
         * @param CController $controller
         * @param View $containedView
         */
        public static function makeStandardViewForCurrentUser(CController $controller, View $containedView)
        {
            // in case of mobile we render it as part of menu.
            if (static::$showRecentlyViewed && !Yii::app()->userInterface->isMobile())
            {
                $verticalColumns = 2;
            }
            else
            {
                $verticalColumns = 1;
            }
            $aVerticalGridView   = new GridView($verticalColumns, 1);

            $aVerticalGridView->setCssClasses( array('AppNavigation', 'clearfix')); //navigation left column
            $aVerticalGridView->setView(static::makeMenuView($controller), 0, 0);
            if (static::$showRecentlyViewed)
            {
                $aVerticalGridView->setView(static::makeRecentlyViewedView(), 1, 0);
            }

            $horizontalGridView = new GridView(1, 3);
            $horizontalGridView->setCssClasses(array('AppContainer', 'clearfix')); //teh conatiner for the floated items
            $horizontalGridView->setView($aVerticalGridView, 0, 0);

            $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent'))); //the app itself to the right

            $horizontalGridView->setView($containedView, 0, 1);
            $horizontalGridView->setView(static::makeFlashMessageView($controller),   0, 2); //TODO needs to move into $cotainedView

            $verticalGridView   = new GridView(5, 1);
            $verticalGridView->setView(static::makeHeaderView($controller),                 0, 0);
            $verticalGridView->setView($horizontalGridView,                                 1, 0);
            $verticalGridView->setView(static::makeModalContainerView(),                    2, 0);
            $verticalGridView->setView(static::makeModalGameNotificationContainerView(),    3, 0);
            $verticalGridView->setView(static::makeFooterView(),                            4, 0);

            return $verticalGridView;
        }

        /**
         * Given a contained view, construct the gridview
         * used by the zurmo page view for errors.
         * @param View $containedView
         */
        public static function makeErrorViewForCurrentUser(CController $controller, View $containedView)
        {
            $aVerticalGridView   = new GridView(1, 1);
            $aVerticalGridView->setCssClasses( array('AppNavigation', 'clearfix')); //navigation left column
            $aVerticalGridView->setView(static::makeMenuView($controller), 0, 0);

            $horizontalGridView = new GridView(2, 1);
            $horizontalGridView->setCssClasses(array('AppContainer', 'clearfix'));
            $horizontalGridView->setView($aVerticalGridView, 0, 0);
            $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent', 'ErrorView'))); //the app itself to the right
            $horizontalGridView->setView($containedView, 1, 0);

            $verticalGridView   = new GridView(3, 1);
            $verticalGridView->setView(static::makeHeaderView($controller),         0, 0);
            $verticalGridView->setView($horizontalGridView,                         1, 0);
            $verticalGridView->setView(static::makeFooterView(),                    2, 0);
            return $verticalGridView;
        }

        protected static function makeHeaderView(CController $controller)
        {
            $headerView               = null;
            $settingsMenuItems        = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $settingsMenuItems        = static::resolveHeaderMenuItemsForMobile($settingsMenuItems);
            $userMenuItems            = static::getAndResolveUserMenuItemsForHeader();
            $applicationName          = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            if (Yii::app()->userInterface->isMobile())
            {
                $headerView               = new MobileHeaderView($settingsMenuItems, $userMenuItems, $applicationName);
            }
            else
            {
                $shortcutsCreateMenuItems = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
                $moduleNamesAndLabels     = GlobalSearchUtil::
                                                getGlobalSearchScopingModuleNamesAndLabelsDataByUser(Yii::app()->user->userModel);
                $sourceUrl                = Yii::app()->createUrl('zurmo/default/globalSearchAutoComplete');
                GlobalSearchUtil::resolveModuleNamesAndLabelsDataWithAllOption($moduleNamesAndLabels);
                $headerView               = new HeaderView($controller->getId(),
                                                            $controller->getModule()->getId(),
                                                            $settingsMenuItems,
                                                            $userMenuItems,
                                                            $shortcutsCreateMenuItems,
                                                            $moduleNamesAndLabels,
                                                            $sourceUrl,
                                                            $applicationName);
            }
            return $headerView;
        }

        protected static function resolveHeaderMenuItemsForMobile(Array $items)
        {
            if (Yii::app()->userInterface->isMobile())
            {
                $resolvedItems = array();
                foreach ($items as $item)
                {
                    if (!isset($item['mobile']) || $item['mobile'] == true)
                    {
                        $resolvedItems[] = $item;
                    }
                }
            }
            else
            {
                $resolvedItems = $items;
            }
            return $resolvedItems;
        }

        protected static function getAndResolveUserMenuItemsForHeader()
        {
            $userMenuItems             = MenuUtil::getAccessibleOrderedUserHeaderMenuForCurrentUser();
            return $userMenuItems;
        }

        protected static function makeMenuView($controller = null)
        {
            assert('$controller == null || $controller instanceof CController');
            $items = MenuUtil::resolveByCacheAndGetVisibleAndOrderedTabMenuByCurrentUser();
            $useMinimalDynamicLabelMbMenu = false;
            static::resolveForMobileInterface($items, $useMinimalDynamicLabelMbMenu, $controller);
            static::resolveForActiveMenuItem($items, $controller);
            return new MenuView($items, $useMinimalDynamicLabelMbMenu);
        }

        protected static function resolveForActiveMenuItem(&$items, $controller)
        {
            assert('$controller == null || $controller instanceof CController');
            assert('is_array($items)');
            foreach ($items as $key => $item)
            {
                if ($controller != null && isset($item['moduleId']) &&
                    static::resolveActiveModuleId($controller) == $item['moduleId'])
                {
                    $items[$key]['active'] = true;
                }
            }
        }

        protected static function resolveActiveModuleId($controller)
        {
            if (static::$activeModuleId != null)
            {
                return static::$activeModuleId;
            }
            return $controller->resolveAndGetModuleId();
        }

        protected static function makeRecentlyViewedView()
        {
            $items = AuditEventsRecentlyViewedUtil::getRecentlyViewedItemsByUser(Yii::app()->user->userModel, 10);
            return new RecentlyViewedView($items);
        }

        protected static function makeFlashMessageView(CController $controller)
        {
            return new FlashMessageView($controller);
        }

        protected static function makeModalContainerView()
        {
            return new ModalContainerView();
        }

        protected static function makeModalGameNotificationContainerView()
        {
            return new ModalGameNotificationContainerView(GameNotification::getAllByUser(Yii::app()->user->userModel));
        }

        protected static function makeFooterView()
        {
            return new FooterView();
        }

        protected static function resolveForMobileInterface(& $items, & $useMinimalDynamicLabelMbMenu, $controller = null)
        {
            if (Yii::app()->userInterface->isMobile())
            {
                foreach ($items as $key => $item)
                {
                    if (isset($item['mobile']) && $item['mobile'] == false)
                    {
                        unset($items[$key]);
                    }
                }
                static::resolveItemsAsItemsForMobile($items, $useMinimalDynamicLabelMbMenu, $controller);
            }
            else
            {
                return;
            }
        }

        protected static function resolveItemsAsItemsForMobile(& $items, &$useMinimalDynamicLabelMbMenu, $controller = null)
        {
            $useMinimalDynamicLabelMbMenu   = true;
            static::$showRecentlyViewed     = false;
            $controller                     = ($controller)? $controller: Yii::app()->request->controller;
            $shortcutsCreateMenuItems       = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            static::resolveShortcutsCreateMenuItemsForMobile($shortcutsCreateMenuItems);
            $shortcutsCreateMenuView        = new MobileShortcutsCreateMenuView(
                $controller->getId(),
                $controller->getModule()->getId(),
                $shortcutsCreateMenuItems
            );
            $moduleNamesAndLabels           = GlobalSearchUtil::
                getGlobalSearchScopingModuleNamesAndLabelsDataByUser(
                Yii::app()->user->userModel);
            $sourceUrl                      = Yii::app()->createUrl('zurmo/default/globalSearchAutoComplete');
            $globalSearchView               = new MobileGlobalSearchView($moduleNamesAndLabels, $sourceUrl);
            $recentlyViewed                 = static::makeRecentlyViewedView();
            $recentlyViewedMenu             = $recentlyViewed->renderMenu();
            $searchItem                     = array(
                array(
                    'label'                 => '',
                    'dynamicLabelContent'   => $globalSearchView->render(),
                    'itemOptions'           => array('id' => 'search'),
                ));
            $shortcutsItems                 = array(
                array(
                    'label'                 => '',
                    'dynamicLabelContent'   => $shortcutsCreateMenuView->render(),
                    'itemOptions'           => array('id' => 'shortcuts'),
                ));
            $recentlyViewedItems            = array(
                array(
                    'label'                 => '',
                    'dynamicLabelContent'   => MobileHtml::renderFlyoutTrigger('Recently Viewed'),
                    'itemOptions'           => array('id' => 'recently-viewed'),
                    'items'                 => ($recentlyViewedMenu) ? $recentlyViewedMenu : null,
                ));
            $items                          = CMap::mergeArray($searchItem, $items, $shortcutsItems, $recentlyViewedItems);
        }

        protected static function resolveShortcutsCreateMenuItemsForMobile(& $shortcutsCreateMenuItems)
        {
            if (!empty($shortcutsCreateMenuItems['items']))
            {
                foreach ($shortcutsCreateMenuItems['items'] as $key => $item)
                {
                    if (isset($item['mobile']) && $item['mobile'] == false)
                    {
                        unset($shortcutsCreateMenuItems['items'][$key]);
                    }
                }
            }
        }
    }
?>