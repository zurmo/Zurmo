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

    class HeaderLinksView extends View
    {
        protected $settingsMenuItems;

        protected $userMenuItems;

        protected $notificationsUrl;

        protected $applicationName;

        const USER_MENU_ID                              = 'user-header-menu';

        const SETTINGS_MENU_ID                          = 'settings-header-menu';

        const MERGED_MENU_ID                            = 'settings-header-menu';

        const MERGE_USER_AND_SETTINGS_MENU_IF_MOBILE    = true;

        public function __construct($settingsMenuItems, $userMenuItems, $applicationName)
        {
            assert('is_array($settingsMenuItems)');
            assert('is_array($userMenuItems)');
            assert('is_string($applicationName) || $applicationName == null');
            $this->settingsMenuItems     = $settingsMenuItems;
            $this->userMenuItems         = $userMenuItems;
            $this->applicationName       = $applicationName;
        }

        protected function renderContent()
        {
            $homeUrl   = Yii::app()->createUrl('home/default');
            $content   = '<div class="clearfix">';
            $content  .= '<a href="#" id="nav-trigger" title="Toggle Navigation">&rsaquo;</a>';
            $content  .= '<div id="corp-logo">';
            if ($logoFileModelId = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                $logoFileModel = FileModel::getById($logoFileModelId);
                $logoFileSrc   = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias('application.runtime.uploads') .
                                                                                DIRECTORY_SEPARATOR . $logoFileModel->name);
            }
            else
            {
                $logoFileSrc   = Yii::app()->baseUrl . '/themes/default/images/Zurmo_logo.png';
            }
            $logoHeight = ZurmoConfigurationFormAdapter::resolveLogoHeight();
            $logoWidth  = ZurmoConfigurationFormAdapter::resolveLogoWidth();
            $content   .= '<a href="' . $homeUrl . '"><img src="' . $logoFileSrc . '" alt="Zurmo Logo" /></a>';
            if ($this->applicationName != null)
            {
                $content  .= ZurmoHtml::tag('span', array(), $this->applicationName);
            }
            $content  .= '</div>';
            $content  .= '<div id="user-toolbar" class="clearfix">';
            $content  .= static::renderHeaderMenus($this->userMenuItems, $this->settingsMenuItems);
            $content  .= '</div></div>';
            return $content;
        }

        protected static function renderHeaderMenus($userMenuItems, $settingsMenuItems)
        {
            $userMenuItemsWithTopLevel = static::resolveUserMenuItemsWithTopLevelItem($userMenuItems);
            $settingsMenuItemsWithTopLevel = static::resolveSettingsMenuItemsWithTopLevelItem($settingsMenuItems);
            return static::renderHeaderMenuContent($userMenuItemsWithTopLevel, self::USER_MENU_ID) .
                static::renderHeaderMenuContent($settingsMenuItemsWithTopLevel, self::SETTINGS_MENU_ID);
        }

        protected static function resolveUserMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $topLevel = static::getUserMenuTopLevelItem();
            return static::resolveMenuItemsWithTopLevelItem($topLevel, $menuItems);
        }

        protected static function resolveSettingsMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $topLevel = static::getSettingsMenuTopLevel();
            return static::resolveMenuItemsWithTopLevelItem($topLevel, $menuItems);
        }

        protected static function resolveMenuItemsWithTopLevelItem($topLevel, $menuItems)
        {
            assert('is_array($menuItems)');
            assert('is_array($topLevel)');
            $topLevel[0]['items'] = $menuItems;
            return $topLevel;
        }

        protected static function getUserMenuTopLevelItem()
        {
            return array(array('label' => Yii::app()->user->userModel->username, 'url' => null));
        }

        protected static function getSettingsMenuTopLevel()
        {
            return array(array('label' => Zurmo::t('ZurmoModule', 'Settings'), 'url' => null));
        }

        protected static function renderHeaderMenuContent($menuItems, $menuId)
        {
            assert('is_array($menuItems)');
            assert('is_string($menuId) && $menuId != null');
            if (empty($menuItems))
            {
                return;
            }
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("headerMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'items'                   => $menuItems,
                'htmlOptions' => array('id'     => $menuId,
                                       'class'  => 'user-menu-item'),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['headerMenu'];
        }
    }
?>
