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
     * Class for displaying a modal window with a game notification.
     */
    class ModalGameNotificationContainerView extends View
    {
        protected $gameNotifications = array();

        /**
         * Given an array of GameNotification models.
         * @param array $gameNotifications
         */
        public function __construct($gameNotifications)
        {
            $this->gameNotifications = $gameNotifications;
        }

        protected function renderContent()
        {
            $content           = null;
            $index             = 0;
            $firstDialog       = true;
            foreach ($this->gameNotifications as $notification)
            {
                $content       .= self::renderNotificationContent($notification, $index, $firstDialog);
                $firstDialog    = false;
                $index++;
                if (!$notification->delete())
                {
                    throw new FailedToDeleteModelException();
                }
            }
            return $content;
        }

        protected static function renderNotificationContent($notification, $index, $modal)
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModalGameNotificationView");
            $cClipWidget->beginWidget('zii.widgets.jui.CJuiDialog', array(
                'id' => 'ModalGameNotification' . $index,
                'options' => array(
                    'autoOpen' => true,
                    'modal'    => $modal,
                    'height'   => 375,
                    'width'    => 350,
                    'dialogClass' => 'ModalGameNotificationParent',
                    'position' => 'center',
                    'clearStyle' => true,
                    'open'     => 'js:function(event, ui) {$(this).parent().children(".ui-dialog-titlebar").hide();}',
                ),
                'htmlOptions' => array('class' => 'ModalGameNotification')
            ));
            $adapter      = new GameNotificationToModalContentAdapter($notification);

            echo '<span class="badge-message">' . $adapter->getMessageContent() . '</span>' .
                 '<div class="game-badge ' . $adapter->getIconCssName() . '"><div class="gloss"></div>' .
                 '<strong class="badge-icon"></strong></div>';
            echo '<br />';
            echo static::resolveAndRenderPostingAndContinueLinksContent($notification, $index);
            $cClipWidget->endWidget('zii.widgets.jui.CJuiDialog');
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ModalGameNotificationView'];
        }

        protected static function resolveAndRenderPostingAndContinueLinksContent(GameNotification $notification, $index)
        {
            if (!RightsUtil::canUserAccessModule('SocialItemsModule', Yii::app()->user->userModel))
            {
                return ZurmoHtml::link(Zurmo::t('GamificationModule', 'Continue'), '#',
                             array('class'   => 'close-ModalGameNotification',
                                    'onclick' => '$("#ModalGameNotification' . $index . '").dialog("close");'));
            }
            else
            {
                $content = ZurmoHtml::link(Zurmo::t('GamificationModule', 'Skip'), '#',
                                 array('class'   => 'close-ModalGameNotification simple-link',
                                       'onclick' => '$("#ModalGameNotification' . $index . '").dialog("close");'));
                $content .= static::renderPostToProfileLinkContent($notification, $index);
                return $content;
            }
        }

        protected static function renderPostToProfileLinkContent(GameNotification $notification, $index)
        {
            assert('is_int($index)');
            $socialItemAdapter = new GameNotificationToSocialItemContentAdapter($notification);
            $url       =   Yii::app()->createUrl('socialItems/default/postGameNotificationToProfile',
                                               array('content' => $socialItemAdapter->getMessageContent()));

            $aContent                = ZurmoHtml::wrapLink(Zurmo::t('GamificationModule', 'Post to Profile'));
            // Begin Not Coding Standard
            $content   = ZurmoHtml::ajaxLink($aContent, $url,
                         array('type'     => 'GET',
                               'complete' => "function(XMLHttpRequest, textStatus){
                                              $('#ModalGameNotification" . $index . "').dialog('close');}"),
                         array('class'     => 'close-ModalGameNotification',
                               'onclick'   => 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                              makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"), "dark");',
                         ));
            // End Not Coding Standard
            return $content;
        }

        public function isUniqueToAPage()
        {
            return true;
        }
    }
?>
