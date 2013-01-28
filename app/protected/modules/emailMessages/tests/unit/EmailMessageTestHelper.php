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

    class EmailMessageTestHelper
    {
        public static function createDraftSystemEmail($subject, User $owner)
        {
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = $owner;
            $emailMessage->subject     = $subject;

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;

            //Sending from the system, does not have a 'person'.
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'system@somewhere.com';
            $sender->fromName          = 'Zurmo System';
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'billy@fakeemail.com';
            $recipient->toName         = 'Billy James';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);

            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $emailMessage;
        }

        public static function createOutboxEmail(User $owner, $subject,
                                                       $htmlContent, $textContent,
                                                       $fromName, $fromAddress,
                                                       $toName, $toAddress)
        {
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = $owner;
            $emailMessage->subject     = $subject;

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = $textContent;
            $emailContent->htmlContent = $htmlContent;
            $emailMessage->content     = $emailContent;

            //Sending from the system, does not have a 'person'.
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = $fromAddress;
            $sender->fromName          = $fromName;
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = $toAddress;
            $recipient->toName         = $toName;
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);

            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $emailMessage;
        }

        public static function isSetEmailAccountsTestConfiguration()
        {
            $isSetEmailAccountsTestConfiguration = false;

            if (isset(Yii::app()->params['emailTestAccounts']))
            {
                $smtpSettings        = Yii::app()->params['emailTestAccounts']['smtpSettings'];
                $dropboxImapSettings = Yii::app()->params['emailTestAccounts']['dropboxImapSettings'];
                $userSmtpSettings    = Yii::app()->params['emailTestAccounts']['userSmtpSettings'];
                $userImapSettings    = Yii::app()->params['emailTestAccounts']['userImapSettings'];
                $testEmailAddress    = Yii::app()->params['emailTestAccounts']['testEmailAddress'];

                if ( $smtpSettings['outboundHost'] != '' && $smtpSettings['outboundPort'] != '' &&
                     $dropboxImapSettings['imapHost'] != '' && $dropboxImapSettings['imapUsername'] != '' &&
                     $dropboxImapSettings['imapPassword'] != '' && $dropboxImapSettings['imapPort'] != '' &&
                     $dropboxImapSettings['imapFolder'] != '' &&
                     $userSmtpSettings['outboundHost'] != '' && $userSmtpSettings['outboundPort'] != '' &&
                     $userImapSettings['imapHost'] != '' && $userImapSettings['imapUsername'] != '' &&
                     $userImapSettings['imapPassword'] != '' && $userImapSettings['imapPort'] != '' &&
                     $userImapSettings['imapFolder'] != '' &&
                     $testEmailAddress != ''
                )
                {
                    $isSetEmailAccountsTestConfiguration = true;
                }
            }
            return $isSetEmailAccountsTestConfiguration;
        }

        public static function createArchivedUnmatchedReceivedMessage(User $user)
        {
            if ($user->primaryEmail->emailAddress == null)
            {
                throw new NotSupportedException();
            }
            $box                       = EmailBoxUtil::getDefaultEmailBoxByUser($user);
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test unmatched archived received email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'bob.message@zurmotest.com';
            $sender->fromName          = 'Bobby Bobson';
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = $user->primaryEmail->emailAddress;
            $recipient->toName          = strval($user);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = $user;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED_UNMATCHED);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $emailMessage;
        }

        public static function createArchivedUnmatchedSentMessage(User $user)
        {
            if ($user->primaryEmail->emailAddress == null)
            {
                throw new NotSupportedException();
            }
            $box                       = EmailBoxUtil::getDefaultEmailBoxByUser($user);
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test unmatched archived sent email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = $user->primaryEmail->emailAddress;
            $sender->fromName          = strval($user);
            $sender->personOrAccount   = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = 'Bobby Bobson';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED_UNMATCHED);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $emailMessage;
        }

        public static function createEmailAccount(User $user)
        {
            $emailAccount                    = new EmailAccount();
            $emailAccount->user              = $user;
            $emailAccount->name              = EmailAccount::DEFAULT_NAME;
            $emailAccount->fromName          = $user->getFullName();
            $emailAccount->fromAddress       = 'user@zurmo.com';
            $emailAccount->useCustomOutboundSettings = false;
            $emailAccount->outboundType      = 'smtp';
            $emailAccount->save();
        }
    }