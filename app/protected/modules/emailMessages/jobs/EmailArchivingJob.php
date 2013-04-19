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
     * A job for retriving emails from dropbox(catch-all) folder
     */
    class EmailArchivingJob extends BaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('EmailMessagesModule', 'Process Inbound Email Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'EmailArchiving';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('EmailMessagesModule', 'Every 1 minute.');
        }

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 30;
        }

        /**
         *
         * (non-PHPdoc)
         * @see BaseJob::run()
         */
        public function run()
        {
            if (Yii::app()->imap->connect())
            {
                $lastImapCheckTime     = EmailMessagesModule::getLastImapDropboxCheckTime();
                if (isset($lastImapCheckTime) && $lastImapCheckTime != '')
                {
                   $criteria = "SINCE \"{$lastImapCheckTime}\" UNDELETED";
                   $lastImapCheckTimeStamp = strtotime($lastImapCheckTime);
                }
                else
                {
                    $criteria = "ALL UNDELETED";
                    $lastImapCheckTimeStamp = 0;
                }
                $messages = Yii::app()->imap->getMessages($criteria, $lastImapCheckTimeStamp);

                $lastCheckTime = null;
                if (count($messages))
                {
                   foreach ($messages as $message)
                   {
                       $lastMessageCreatedTime = strtotime($message->createdDate);
                       if (strtotime($message->createdDate) > strtotime($lastCheckTime))
                       {
                           $lastCheckTime = $message->createdDate;
                       }
                       $this->saveEmailMessage($message);
                   }
                   Yii::app()->imap->expungeMessages();
                   if ($lastCheckTime != '')
                   {
                       EmailMessagesModule::setLastImapDropboxCheckTime($lastCheckTime);
                   }
                }
                return true;
            }
            else
            {
                $messageContent     = Zurmo::t('EmailMessagesModule', 'Failed to connect to mailbox');
                $this->errorMessage = $messageContent;
                return false;
            }
        }

        /**
         * Resolve system message to be sent, and send it
         * @param string $messageType
         * @param ImapMessage $originalMessage
         * @return boolean
         * @throws NotSupportedException
         */
        protected function resolveMessageSubjectAndContentAndSendSystemMessage($messageType, $originalMessage)
        {
            switch ($messageType)
            {
                case "OwnerNotExist":
                    $subject = Zurmo::t('EmailMessagesModule', 'Invalid email address');
                    $textContent = Zurmo::t('EmailMessagesModule', 'Email address does not exist in system') . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Zurmo::t('EmailMessagesModule', 'Email address does not exist in system') . "<br\><br\>" . $originalMessage->htmlBody;
                    break;
                case "SenderNotExtracted":
                    $subject = Zurmo::t('EmailMessagesModule', "Sender info can't be extracted from email message");
                    $textContent = Zurmo::t('EmailMessagesModule', "Sender info can't be extracted from email message") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Zurmo::t('EmailMessagesModule', "Sender info can't be extracted from email message") . "<br\><br\>" . $originalMessage->htmlBody;
                    break;
                case "RecipientNotExtracted":
                    $subject = Zurmo::t('EmailMessagesModule', "Recipient info can't be extracted from email message");
                    $textContent = Zurmo::t('EmailMessagesModule', "Recipient info can't be extracted from email message") . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Zurmo::t('EmailMessagesModule', "Recipient info can't be extracted from email message") . "<br\><br\>" . $originalMessage->htmlBody;
                    break;
                case "EmailMessageNotValidated":
                    $subject = Zurmo::t('EmailMessagesModule', 'Email message could not be validated');
                    $textContent = Zurmo::t('EmailMessagesModule', 'Email message could not be validated') . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Zurmo::t('EmailMessagesModule', 'Email message could not be validated') . "<br\><br\>" . $originalMessage->htmlBody;
                    break;
                case "EmailMessageNotSaved":
                    $subject = Zurmo::t('EmailMessagesModule', 'Email message could not be saved');
                    $textContent = Zurmo::t('EmailMessagesModule', 'Email message could not be saved') . "\n\n" . $originalMessage->textBody;
                    $htmlContent = Zurmo::t('EmailMessagesModule', 'Email message could not be saved') . "<br\><br\>" . $originalMessage->htmlBody;
                    break;
                default:
                    throw NotSupportedException();
            }
            return EmailMessageHelper::sendSystemEmail($subject, array($originalMessage->fromEmail), $textContent, $htmlContent);
        }

        /**
         * Create EmailMessageSender
         * @param array $senderInfo
         * @param boolean $userCanAccessContacts
         * @param boolean $userCanAccessLeads
         * @param boolean $userCanAccessAccounts
         * @return EmailMessageSender
         */
        protected function createEmailMessageSender($senderInfo, $userCanAccessContacts, $userCanAccessLeads,
                                                     $userCanAccessAccounts)
        {
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = $senderInfo['email'];
            if (isset($senderInfo['name']))
            {
                $sender->fromName          = $senderInfo['name'];
            }
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress(
                    $senderInfo['email'],
                    $userCanAccessContacts,
                    $userCanAccessLeads,
                    $userCanAccessAccounts);
            $sender->personOrAccount = $personOrAccount;
            return $sender;
        }

        /**
         * Create EmailMessageRecipient
         * @param array $recipientInfo
         * @param boolean $userCanAccessContacts
         * @param boolean $userCanAccessLeads
         * @param boolean $userCanAccessAccounts
         * @return EmailMessageRecipient
         */
        protected function createEmailMessageRecipient($recipientInfo, $userCanAccessContacts, $userCanAccessLeads,
                                                     $userCanAccessAccounts)
        {
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = $recipientInfo['email'];
            $recipient->toName         = $recipientInfo['name'];
            $recipient->type           = $recipientInfo['type'];

            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress(
                    $recipientInfo['email'],
                    $userCanAccessContacts,
                    $userCanAccessLeads,
                    $userCanAccessAccounts);
            $recipient->personOrAccount = $personOrAccount;
            return $recipient;
        }

        /**
         * Create FileModel
         * @param array $attachment
         * @return FileModel
         */
        protected function createEmailAttachment($attachment)
        {
            // Save attachments
            if ($attachment['filename'] != null)
            {
                $fileContent          = new FileContent();
                $fileContent->content = $attachment['attachment'];
                $file                 = new FileModel();
                $file->fileContent    = $fileContent;
                $file->name           = $attachment['filename'];
                $file->type           = ZurmoFileHelper::getMimeType($attachment['filename']);
                $file->size           = strlen($attachment['attachment']);
                $saved                = $file->save();
                assert('$saved'); // Not Coding Standard
                return $file;
            }
            else
            {
                return false;
            }
        }

        /**
         * Save email message
         * This method should be protected, but we made it public for unit testing, so don't call it outside this class.
         * @param ImapMessage $message
         * @throws NotSupportedException
         * @return boolean
         */
        public function saveEmailMessage($message)
        {
            // Get owner for message
            try
            {
                $emailOwner = EmailArchivingUtil::resolveOwnerOfEmailMessage($message);
            }
            catch (CException $e)
            {
                // User not found, so inform user about issue and continue with next email.
                $this->resolveMessageSubjectAndContentAndSendSystemMessage('OwnerNotExist', $message);
                return false;
            }
            $emailSenderOrRecipientEmailNotFoundInSystem = false;
            $userCanAccessContacts = RightsUtil::canUserAccessModule('ContactsModule', $emailOwner);
            $userCanAccessLeads    = RightsUtil::canUserAccessModule('LeadsModule',    $emailOwner);
            $userCanAccessAccounts = RightsUtil::canUserAccessModule('AccountsModule', $emailOwner);

            $senderInfo = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($message);
            if (!$senderInfo)
            {
                $this->resolveMessageSubjectAndContentAndSendSystemMessage('SenderNotExtracted', $message);
                return false;
            }
            else
            {
                $sender = $this->createEmailMessageSender($senderInfo, $userCanAccessContacts,
                              $userCanAccessLeads, $userCanAccessAccounts);

                if (empty($sender->personOrAccount) || $sender->personOrAccount->id <= 0)
                {
                    $emailSenderOrRecipientEmailNotFoundInSystem = true;
                }
            }

            $recipientsInfo = EmailArchivingUtil::resolveEmailRecipientsFromEmailMessage($message);
            if (!$recipientsInfo)
            {
                $this->resolveMessageSubjectAndContentAndSendSystemMessage('RecipientNotExtracted', $message);
                return false;
            }

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $emailOwner;
            $emailMessage->subject = $message->subject;

            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = $message->textBody;
            $emailContent->htmlContent = $message->htmlBody;
            $emailMessage->content     = $emailContent;
            $emailMessage->sender      = $sender;

            $emailRecipientNotFoundInSystem = true;
            foreach ($recipientsInfo as $recipientInfo)
            {
                $recipient = $this->createEmailMessageRecipient($recipientInfo, $userCanAccessContacts,
                    $userCanAccessLeads, $userCanAccessAccounts);
                $emailMessage->recipients->add($recipient);
                // Check if at least one recipient email can't be found in Contacts, Leads, Account and User emails
                // so we will save email message in EmailFolder::TYPE_ARCHIVED_UNMATCHED folder, and user will
                // be able to match emails with items(Contacts, Accounts...) emails in systems
                if (!(empty($recipient->personOrAccount) || $recipient->personOrAccount->id <= 0))
                {
                    $emailRecipientNotFoundInSystem = false;
                }
            }

            // Override $emailSenderOrRecipientEmailNotFoundInSystem only if there are no errors
            if ($emailSenderOrRecipientEmailNotFoundInSystem == false)
            {
                $emailSenderOrRecipientEmailNotFoundInSystem = $emailRecipientNotFoundInSystem;
            }
            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            if ($emailSenderOrRecipientEmailNotFoundInSystem)
            {
                $emailMessage->folder  = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED_UNMATCHED);
                $notificationMessage                    = new NotificationMessage();
                $notificationMessage->htmlContent       = Zurmo::t('EmailMessagesModule', 'At least one archived email message does ' .
                                                                 'not match any records in the system. ' .
                                                                 '<a href="{url}">Click here</a> to manually match them.',
                    array(
                        '{url}'      => Yii::app()->createUrl('emailMessages/default/matchingList'),
                    )
                );
                if ($emailOwner instanceof User)
                {
                    $rules                      = new EmailMessageArchivingEmailAddressNotMatchingNotificationRules();
                    $rules->addUser($emailOwner);
                    NotificationsUtil::submit($notificationMessage, $rules);
                }
            }
            else
            {
                $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED);
            }

            if (!empty($message->attachments))
            {
                foreach ($message->attachments as $attachment)
                {
                    if (!$attachment['is_attachment'])
                    {
                        continue;
                    }
                    $file = $this->createEmailAttachment($attachment);
                    if ($file instanceof FileModel)
                    {
                        $emailMessage->files->add($file);
                    }
                }
            }
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $validated                 = $emailMessage->validate();
            if (!$validated)
            {
                // Email message couldn't be validated(some related models can't be validated). Email user.
                $this->resolveMessageSubjectAndContentAndSendSystemMessage('EmailMessageNotValidated', $message);
                return false;
            }

            EmailArchivingUtil::resolveSanitizeFromImapToUtf8($emailMessage);
            $saved = $emailMessage->save();
            try
            {
                if (!$saved)
                {
                    throw new NotSupportedException();
                }
                if (isset($message->uid)) // For tests uid will not be setup
                {
                    Yii::app()->imap->deleteMessage($message->uid);
                }
            }
            catch (NotSupportedException $e)
            {
                // Email message couldn't be saved. Email user.
                $this->resolveMessageSubjectAndContentAndSendSystemMessage('EmailMessageNotSaved', $message);
                return false;
            }
            return true;
        }
    }
?>
