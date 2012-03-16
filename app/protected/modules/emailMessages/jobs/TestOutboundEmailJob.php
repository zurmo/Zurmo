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
     * A job for testing that the outbound connection to SMTP is still working correctly.
     */
    class TestOutboundEmailJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Yii::t('Default', 'Testing Outbound Email Connection Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'TestOutboundEmail';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Yii::t('Default', 'Once a day, early in the morning.');
        }

        /**
         *
         * (non-PHPdoc)
         * @see BaseJob::run()
         */
        public function run()
        {
            $messageContent            = null;
            $userToSendMessagesFrom    = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = Yii::t('Default', 'A test email from Zurmo');
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = Yii::t('Default', 'A test text message from Zurmo');
            $emailContent->htmlContent = Yii::t('Default', 'A test text message from Zurmo');
            $emailMessage->content     = $emailContent;
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName          = strval($userToSendMessagesFrom);
            $emailMessage->sender      = $sender;
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = Yii::app()->emailHelper->defaultTestToAddress;
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
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            if (!$emailMessage->hasSendError())
            {
                $messageContent .= Yii::t('Default', 'Message successfully sent') . "\n";
                return true;
            }
            else
            {
                $messageContent .= Yii::t('Default', 'Message failed to send') . "\n";
                $messageContent .= $emailMessage->error     . "\n";
                $this->errorMessage = $messageContent;
                return false;
            }
        }
    }
?>