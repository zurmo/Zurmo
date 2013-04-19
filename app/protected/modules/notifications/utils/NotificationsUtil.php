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
     * Helper class to work with notifications.
     */
    class NotificationsUtil
    {
        protected static function getEmailSubject()
        {
            return Zurmo::t('NotificationsModule', 'You have a new notification');
        }

        /**
         * Given a NotificationMessage and a NotificationRule submit and process a notification
         * to one or more users.
         * @param NotificationMessage $message
         * @param NotificationRules $rules
         */
        public static function submit(NotificationMessage $message, NotificationRules $rules)
        {
            $users = $rules->getUsers();
            if (count($users) == 0)
            {
                throw new NotSupportedException();
            }
            static::processNotification($message,
                                        $rules->getType(),
                                        $users,
                                        $rules->allowDuplicates(),
                                        $rules->isCritical());
        }

        protected static function processNotification(NotificationMessage $message, $type, $users,
                                                      $allowDuplicates, $isCritical)
        {
            assert('is_string($type) && $type != ""');
            assert('is_array($users) && count($users) > 0');
            assert('is_bool($allowDuplicates)');
            assert('is_bool($isCritical)');
            $notifications = array();
            foreach ($users as $user)
            {
                //todo: !!!process duplication check
                if ($allowDuplicates || Notification::getCountByTypeAndUser($type, $user) == 0)
                {
                    $notification                      = new Notification();
                    $notification->owner               = $user;
                    $notification->type                = $type;
                    $notification->notificationMessage = $message;
                    $saved                             = $notification->save();
                    if (!$saved)
                    {
                        throw new NotSupportedException();
                    }
                    $notifications[] = $notification;
                }
            }
            if (static::resolveShouldSendEmailIfCritical() && $isCritical)
            {
                foreach ($notifications as $notification)
                {
                    static::sendEmail($notification);
                }
            }
        }

        protected static function resolveShouldSendEmailIfCritical()
        {
            return true;
        }

        protected static function sendEmail(Notification $notification)
        {
            if ($notification->owner->primaryEmail->emailAddress !== null &&
                !UserConfigurationFormAdapter::resolveAndGetValue($notification->owner, 'turnOffEmailNotifications'))
            {
                $userToSendMessagesFrom     = Yii::app()->emailHelper->getUserToSendNotificationsAs();
                $emailMessage               = new EmailMessage();
                $emailMessage->owner        = Yii::app()->user->userModel;
                $emailMessage->subject      = static::getEmailSubject();
                $emailContent               = new EmailMessageContent();
                $emailContent->textContent  = EmailNotificationUtil::
                                                resolveNotificationTextTemplate(
                                                $notification->notificationMessage->textContent);
                $emailContent->htmlContent  = EmailNotificationUtil::
                                                resolveNotificationHtmlTemplate(
                                                $notification->notificationMessage->htmlContent);
                $emailMessage->content      = $emailContent;
                $sender                     = new EmailMessageSender();
                $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                $sender->fromName           = strval($userToSendMessagesFrom);
                $emailMessage->sender       = $sender;
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $notification->owner->primaryEmail->emailAddress;
                $recipient->toName          = strval($notification->owner);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personOrAccount = $notification->owner;
                $emailMessage->recipients->add($recipient);
                $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                try
                {
                    Yii::app()->emailHelper->sendImmediately($emailMessage);
                }
                catch (CException $e)
                {
                    //Not sure what to do yet when catching an exception here. Currently ignoring gracefully.
                }
            }
        }

        /**
         * Get the content for displaying recent notifications information via an ajax call.
         * @see HeaderLinksView->renderNotificationsLinkContent()
         * @param User $user
         */
        public static function getRecentAjaxContentByUser(User $user, $count)
        {
            assert('is_int($count)');
            $content     = null;
            $notification = new Notification(false);
            $searchAttributes = array(
                'owner'    => array('id' => Yii::app()->user->userModel->id)
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                $notification,
                Yii::app()->user->userModel->id,
                $searchAttributes
            );
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadataAdapter->getAdaptedMetadata(),
                'Notification',
                'RedBeanModelDataProvider',
                'createdDateTime',
                true,
                10
            );
            $notifications = $dataProvider->getData();
            if (count($notifications) > 0)
            {
                foreach ($notifications as $notification)
                {
                        $content .= '<div class="single-notification">';
                        $content .= self::renderShortenedListViewContent($notification);
                        $content .= ZurmoHtml::link("Delete<span class='icon'></span>", "#",
                                                array("class"   => "remove",
                                                      "onclick" => "deleteNotificationFromAjaxListView(this, " . $notification->id . ", event)"));
                        $content .= '</div>';
                }
            }
            else
            {
                $content .= '<div class="single-notification">' . Zurmo::t('NotificationsModule', 'There are no recent notifications.') . '</div>';
            }
            return $content;
        }

        public static function renderShortenedListViewContent(Notification $notification)
        {
            $content = strval($notification);
            if ($content != null)
            {
                $content = '<h4>' . StringUtil::getChoppedStringContent($content, 68) . '</h4>';
            }
            if ($notification->notificationMessage->id > 0)
            {
                if ($notification->notificationMessage->htmlContent != null && strlen($notification->notificationMessage->htmlContent) < 136)
                {
                    $content .= '<div>' . Yii::app()->format->raw($notification->notificationMessage->htmlContent). '</div>';
                }
                elseif ($notification->notificationMessage->textContent != null)
                {
                    $content .= '<div>' . Yii::app()->format->text(StringUtil::
                                            getChoppedStringContent($notification->notificationMessage->textContent, 136)) .
                                '</div>';
                }
            }
            return $content;
        }

        public static function renderListViewContent(Notification $notification)
        {
            $content = strval($notification);
            if ($content != null)
            {
                $content = '<b>' . $content . '</b>';
            }
            if ($notification->notificationMessage->id > 0)
            {
                if ($notification->notificationMessage->htmlContent != null)
                {
                    $content .= ZurmoHtml::wrapLabel(Yii::app()->format->
                                                        raw($notification->notificationMessage->htmlContent),
                                                    "last-comment");
                }
                elseif ($notification->notificationMessage->textContent != null)
                {
                    $content .= ZurmoHtml::wrapLabel(Yii::app()->format->
                                                        text($notification->notificationMessage->textContent),
                                                    "last-comment");
                }
            }
            return $content;
        }
    }
?>