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
     * Helper class to work with notifications.
     */
    class NotificationsUtil
    {
        protected static function getEmailSubject()
        {
            return Yii::t('Default', 'You have a new notification');
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
            if(count($users) == 0)
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
            foreach($users as $user)
            {
                //todo: !!!process duplication check
                if($allowDuplicates || Notification::getUnreadCountByTypeAndUser($type, $user) == 0)
                {
                    $notification                      = new Notification();
                    $notification->owner               = $user;
                    $notification->type                = $type;
                    $notification->isRead              = false;
                    $notification->notificationMessage = $message;
                    $saved                             = $notification->save();
                    if(!$saved)
                    {
                        throw new NotSupportedException();
                    }
                    $notifications[] = $notification;
                }
            }
            if(static::resolveShouldSendEmailIfCritical() && $isCritical)
            {
                foreach($notifications as $notification)
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
            return; //Remove once Email is implemented.
            //Fix up MonitorJobTest since we can now test this properly.
            //throw new NotImplementedException();
            $adapter        = new NotificationMessageToEmailMessageAdapter($notification->message);
            $emailMessage   = new EmailMessage();
            $emailMessage->subject = static::getEmailSubject();
            $adapter->copyMessageIntoEmail($emailMessage); //textBody and htmlBody
            $emailMessage->setToByUsers(array($notification->owner));
            //$email->setFromByWhat(???); //How do we know who this should show as coming from?
            Yii::app()->emailHelper->send($emailMessage);
        }
    }
?>