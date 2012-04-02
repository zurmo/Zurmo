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

    class HeaderLinksView extends View
    {
        protected $settingsMenuItems;

        protected $userMenuItems;

        protected $notificationsUrl;

        protected $applicationName;

        public function __construct($settingsMenuItems, $userMenuItems, $notificationsUrl, $applicationName)
        {
            assert('is_array($settingsMenuItems)');
            assert('is_array($userMenuItems)');
            assert('is_string($notificationsUrl)');
            assert('is_string($applicationName) || $applicationName == null');
            $this->settingsMenuItems     = $settingsMenuItems;
            $this->userMenuItems         = $userMenuItems;
            $this->notificationsUrl      = $notificationsUrl;
            $this->applicationName       = $applicationName;
        }

        protected function renderContent()
        {

            $imagePath = Yii::app()->baseUrl . '/themes/default/images/';
            $content   = '<div class="clearfix"><div id="corp-logo"><img src="' . $imagePath. 'Zurmo_logo.png">';
            if($this->applicationName != null)
            {
                $content  .= CHtml::tag('span', array(), $this->applicationName);
            }
            $content  .= '</div>';
            $content  .= '<div id="user-toolbar" class="clearfix">';
            $content  .= static::renderHeaderMenuContent(
                            static::resolveUserMenuItemsWithTopLevelItem($this->userMenuItems),
                            'user-header-menu');
            $content  .= static::renderNotificationsLinkContent();
            $content  .= static::renderHeaderMenuContent(
                            static::resolveSettingsMenuItemsWithTopLevelItem($this->settingsMenuItems),
                            'settings-header-menu');
            $content  .= '</div></div>';
            return $content;
        }

        protected static function resolveUserMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $finalMenuItems             = array(array('label' => Yii::app()->user->userModel->username, 'url' => null));
            $finalMenuItems[0]['items'] = $menuItems;
            return $finalMenuItems;
        }

        protected static function resolveSettingsMenuItemsWithTopLevelItem($menuItems)
        {
            assert('is_array($menuItems)');
            $finalMenuItems             = array(array('label' => Yii::t('Default', 'Settings'), 'url' => null));
            $finalMenuItems[0]['items'] = $menuItems;
            return $finalMenuItems;
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
            $cClipWidget->widget('ext.zurmoinc.framework.widgets.MbMenu', array(
                'items'                   => $menuItems,
                'navContainerClass'       => 'nav-single-container',
                'navBarClass'             => 'nav-single-bar',
                'htmlOptions' => array('id' => $menuId),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['headerMenu'];
        }

        protected function renderNotificationsLinkContent()
        {
            $label    = Yii::t('Default', 'Notifications');
            $content  = null;
            $count    = Notification::getUnreadCountByUser(Yii::app()->user->userModel);

            if ($count > 0)
            {
                $content  .= "<a href=\"#\" class=\"notifications-link unread\">";
                $content  .= "<span id='notifications-link' class='tooltip'>" .
                                Yii::t('Default', '{count}', array('{count}' => $count))."</span></a>";
                Yii::import('application.extensions.qtip.QTip');
                $imageSourceUrl = Yii::app()->baseUrl . '/themes/default/images/loading.gif';
                $qtip   = new QTip();
                $params = array(
                    'content'  => array('text'    => CHtml::image($imageSourceUrl, Yii::t('Default', 'Loading')),
                                        'url'     => $this->notificationsUrl,
                                        'title'   => array('text'   => Yii::t('Default', 'Recently Viewed'),
                                                           'button' => Yii::t('Default', 'Close'))),
                    'show'     => array('when'    => 'click'),
                    'hide'     => array('when'    => 'click'),
                    'position' => array('corner'  => array(
                                    'target'      => 'bottomRight',
                                    'tooltip'     => 'topRight')),
                    'style'    => array('width'   =>  300));
                $qtip->addQTip("#notifications-link", $params);
            } else {
                $content  .= "<a href=\"$link\" class=\"notifications-link all-read\"><span>".Yii::t('Default', '{count}', array('{count}' => $count))."</span></a>";
            }

            return $content;
        }
    }
?>
