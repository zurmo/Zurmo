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

    /**
     * Helper class for generating notifications
     */
    class ZurmoNotificationUtil
    {
        public static function renderDesktopNotificationsScript()
        {
            if (UserConfigurationFormAdapter::resolveAndGetValue(Yii::app()->user->userModel, 'enableDesktopNotifications'))
            {
                $makeNotification = "
                    if (window.webkitNotifications.checkPermission() == 0)
                    {
                        nf = window.webkitNotifications.createNotification(image, title, body);
                        if (nf.hasOwnProperty('onshow'))
                        {
                            nf.onshow = function() {setTimeout(function () {nf.close();}, 20000);};
                        }
                        nf.show();
                        return true;
                    }
                    ";
            }
            else
            {
                $makeNotification = "";
            }
            // Begin Not Coding Standard
            $script = "
            var desktopNotifications =
            {
                notify:function(image, title, body)
                {
                    " . $makeNotification . "
                    return false;
                },
                isSupported:function()
                {
                    if (typeof window.webkitNotifications != 'undefined')
                    {
                        return true
                    }
                    else
                    {
                        return false
                    }
                },
                requestAutorization:function()
                {
                    if (typeof window.webkitNotifications != 'undefined')
                    {
                        if (window.webkitNotifications.checkPermission() == 1)
                        {
                            window.webkitNotifications.requestPermission();
                        }
                        else if (window.webkitNotifications.checkPermission() == 2)
                        {
                            alert('" . Zurmo::t('ZurmoModule', 'You have blocked desktop notifications for this browser.') . "');
                        }
                        else
                        {
                            alert('" . Zurmo::t('ZurmoModule', 'You have already activated desktop notifications for Chrome') . "');
                        }
                    }
                    else
                    {
                        alert('" . Zurmo::t('ZurmoModule', 'This is only available in Chrome.') . "');
                    }
                }
            };
            ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('AutoUpdater', $script, CClientScript::POS_HEAD);
        }

        public static function renderAutoUpdaterScript()
        {
            // Begin Not Coding Standard
            $script = "
                    var conversationsPlacer = $('#MenuView').find('li.last').find('span:last'); //TODO: Make an id for this span
                    var unreadConversations = conversationsPlacer.text();
                    var url                 = '" . Yii::app()->createUrl('zurmo/default/getUpdatesForRefresh') . "';
                    function startAutoUpdater()
                    {
                        if (unreadConversations >= 0 && unreadConversations != '')
                        {
                            $.ajax(
                            {
                                type: 'GET',
                                url: url + '?unreadConversations=' + unreadConversations,
                                async: true,
                                cache: false,
                                timeout: 15000,
                                success: function(data)
                                {
                                    data = JSON.parse(data);
                                    if (data != null)
                                    {
                                        if (unreadConversations != data.unreadConversations)
                                        {
                                            unreadConversations = data.unreadConversations;
                                            conversationsPlacer.html(unreadConversations);
                                            if (desktopNotifications.isSupported())
                                            {
                                                desktopNotifications.notify(data.imgUrl,
                                                                            data.title,
                                                                            data.message);
                                            }
                                        }
                                    }
                                    setTimeout(startAutoUpdater, 10000);
                                },
                                error: function(XMLHttpRequest, textStatus, errorThrown)
                                {
                                    setTimeout(startAutoUpdater, 30000);
                                }
                            });
                        }
                    }
                    setTimeout(startAutoUpdater, 10000);
                ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('AutoUpdater', $script, CClientScript::POS_READY);
        }
    }
?>