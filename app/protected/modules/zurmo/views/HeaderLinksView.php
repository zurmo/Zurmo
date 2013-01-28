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
            $homeUrl   = Yii::app()->createUrl('home/default');
            $content   = '<div class="clearfix"><div id="corp-logo">';
            $content  .= '<a href="' . $homeUrl . '"><img src="' . $imagePath . 'Zurmo_logo.png" alt="Zurmo Logo" width="107" height="32" /></a>';
            if ($this->applicationName != null)
            {
                $content  .= ZurmoHtml::tag('span', array(), $this->applicationName);
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
            $finalMenuItems             = array(array('label' => Zurmo::t('ZurmoModule', 'Settings'), 'url' => null));
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
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'items'                   => $menuItems,
                'htmlOptions' => array('id'     => $menuId,
                                       'class'  => 'user-menu-item'),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['headerMenu'];
        }

        protected function renderNotificationsLinkContent()
        {
            $label    = Zurmo::t('ZurmoModule', 'Notifications');
            $content  = null;
            $count    = Notification::getCountByUser(Yii::app()->user->userModel);
            // Begin Not Coding Standard
            $content  .= '<div id="notifications" class="user-menu-item">';
            $content  .= "<a id=\"notifications-flyout-link\" href=\"#\" class=\"notifications-link unread\">";
            $content  .= "<span id='notifications-link'><strong>" . $count ."</strong></span></a>";
            $content  .= ZurmoHtml::tag('div', array('id' => 'notifications-flyout'), '<span class="z-spinner"></span>', 'div');
            Yii::app()->clientScript->registerScript('notificationPopupLinkScript', "
                $('#notifications-link').live('click', function()
                {
                        if ( $('#notifications').hasClass('nav-open') === true ){
                            attachLoadingSpinner('notifications-flyout', true);
                            $.ajax({
                                url 	 : '" . $this->notificationsUrl . "',
                                type     : 'GET',
                                dataType : 'html',
                                success  : function(html)
                                {
                                    jQuery('#notifications-flyout').empty().html(html);
                                }
                            });
                        }
                        return false;
                });
            ", CClientScript::POS_HEAD);
            Yii::app()->clientScript->registerScript('deleteNotificationFromAjaxListViewScript', "
                function deleteNotificationFromAjaxListView(element, modelId, event)
                {
                    event.stopPropagation();
                    $.ajax({
                        url : '" . Yii::app()->createUrl('notifications/default/deleteFromAjax') . "?id=' + modelId,
                        type : 'GET',
                        dataType : 'json',
                        success : function(data)
                        {
                            //remove row
                            $(element).parent().remove();
                        },
                        error : function()
                        {
                            //todo: error call
                        }
                    });
                }
            ", CClientScript::POS_END);
            $content  .= '</div>';
            // End Not Coding Standard
            return $content;
        }
    }
?>
