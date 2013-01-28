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

    class EmailMessageHelper
    {
        /**
         * Send system email message
         * @param string $subject
         * @param array $recipients
         * @param string $textContent
         * @param string $htmlContent
         * @throws NotSupportedException
         */
        public static function sendSystemEmail($subject, $recipients, $textContent = '', $htmlContent = '')
        {
            $emailMessage = new EmailMessage();
            $emailMessage->owner   = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $emailMessage->subject = $subject;

            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = $textContent;
            $emailContent->htmlContent = $htmlContent;
            $emailMessage->content     = $emailContent;

            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
            $sender->fromName          = strval(Yii::app()->user->userModel);
            $sender->personOrAccount            = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;

            foreach ($recipients as $recipientEmail)
            {
                $recipient                 = new EmailMessageRecipient();
                $recipient->toAddress      = $recipientEmail;
                $recipient->type           = EmailMessageRecipient::TYPE_TO;
                $emailMessage->recipients->add($recipient);
            }

            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            $validated                 = $emailMessage->validate();

            if (!$validated)
            {
                throw new NotSupportedException();
            }
            Yii::app()->emailHelper->sendImmediately($emailMessage);

            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            if (!$emailMessage->hasSendError())
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Send a test email.  Can use to determine if the SMTP settings are configured correctly.
         * @param EmailHelper $emailHelper
         * @param User $userToSendMessagesFrom
         * @param string $toAddress
         */
        public static function sendTestEmail(EmailHelper $emailHelper, User $userToSendMessagesFrom, $toAddress)
        {
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = Zurmo::t('EmailMessagesModule', 'A test email from Zurmo');
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = EmailNotificationUtil::
                                            resolveNotificationTextTemplate(
                                            Zurmo::t('EmailMessagesModule', 'A test text message from Zurmo.'));
            $emailContent->htmlContent = EmailNotificationUtil::
                                            resolveNotificationHtmlTemplate(
                                            Zurmo::t('EmailMessagesModule', 'A test text message from Zurmo.'));
            $emailMessage->content     = $emailContent;
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = $emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName          = strval($userToSendMessagesFrom);
            $emailMessage->sender      = $sender;
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = $toAddress;
            $recipient->toName         = 'Test Recipient';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            $validated                 = $emailMessage->validate();
            if (!$validated)
            {
                throw new NotSupportedException();
            }
            $emailHelper->sendImmediately($emailMessage);
            return $emailMessage;
        }
    }