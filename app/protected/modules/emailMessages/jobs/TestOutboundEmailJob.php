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
     * A job for testing that the outbound connection to SMTP is still working correctly.
     */
    class TestOutboundEmailJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('EmailMessagesModule', 'Testing Outbound Email Connection Job');
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
            return Zurmo::t('EmailMessagesModule', 'Once a day, early in the morning.');
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
            $emailMessage->subject     = Zurmo::t('EmailMessagesModule', 'A test email from Zurmo');
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = Zurmo::t('EmailMessagesModule', 'A test text message from Zurmo.');
            $emailContent->htmlContent = Zurmo::t('EmailMessagesModule', 'A test text message from Zurmo.');
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
                $messageContent .= Zurmo::t('EmailMessagesModule', 'Message successfully sent') . "\n";
                return true;
            }
            else
            {
                $messageContent .= Zurmo::t('EmailMessagesModule', 'Message failed to send') . "\n";
                $messageContent .= $emailMessage->error     . "\n";
                $this->errorMessage = $messageContent;
                return false;
            }
        }
    }
?>