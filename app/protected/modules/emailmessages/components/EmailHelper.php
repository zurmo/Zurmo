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
     * Component for working with outbound and inbound email transport
     */
    class EmailHelper extends CApplicationComponent
    {
        public $outboundType = 'smtp';

        public $outboundHost;

        public $outboundPort = 25;

        public $outboundUsername;

        public $outboundPassword;

        protected $settingsToLoad = array(
            'outboundType',
            'outboundHost',
            'outboundPort',
            'outboundUsername',
            'outboundPassword'
        );

        public $defaultFromAddress   = 'notifications@zurmoalerts.com';

        /**
         * Utilized when sending a test email nightly to check the status of the smtp server
         * @var string
         */
        public $defaultTestToAddress = 'testJobEmail@zurmoalerts.com';

        public function init()
        {
            $this->loadOutboundSettings();
        }

        protected function loadOutboundSettings()
        {
            foreach($this->settingsToLoad as $keyName)
            {
                if (null !== $keyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName))
                {
                    $this->$keyName = $keyValue;
                }
            }
        }

        public function setOutboundSettings()
        {
            foreach($this->settingsToLoad as $keyName)
            {
                ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $this->$keyName);
            }
        }

        /**
         * Send an email message. This will queue up the email to be sent by the queue sending process. If you want to
         * send immediately, consider using @sendImmediately
         * @param EmailMessage $email
         */
        public function send(EmailMessage $emailMessage)
        {
            if($emailMessage->folder->type == EmailFolder::TYPE_OUTBOX ||
               $emailMessage->folder->type == EmailFolder::TYPE_SENT ||
               $emailMessage->folder->type == EmailFolder::TYPE_OUTBOX_ERROR)
            {
                throw new NotSupportedException();
            }
            $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_OUTBOX);
            $saved                  = $emailMessage->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
            return true;
        }

        public function sendImmediately(EmailMessage $emailMessage)
        {
            if($emailMessage->folder->type == EmailFolder::TYPE_SENT)
            {
                throw new NotSupportedException();
            }
            $mailer           = $this->getOutboundMailer();
            $this->populateMailer($mailer, $emailMessage);
            $this->sendEmail($mailer, $emailMessage);
            $saved = $emailMessage->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function sendQueued()
        {
            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            foreach($queuedEmailMessages as $emailMessage)
            {
                $this->sendImmediately($emailMessage);
            }
            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX_ERROR);
            foreach($queuedEmailMessages as $emailMessage)
            {
                $this->sendImmediately($emailMessage);
            }
            return true;
        }

        protected function populateMailer(Mailer $mailer, EmailMessage $emailMessage)
        {
            $mailer->mailer   = $this->outboundType;
            $mailer->host     = $this->outboundHost;
            $mailer->port     = $this->outboundPort;
            $mailer->username = $this->outboundUsername;
            $mailer->password = $this->outboundPassword;
            $mailer->Subject  = $emailMessage->subject;
            if($emailMessage->content->htmlContent == null && $emailMessage->content->textContent != null)
            {
                $mailer->body     = $emailMessage->content->textContent;
                $mailer->altBody  = $emailMessage->content->textContent;
            }
            elseif($emailMessage->content->htmlContent != null && $emailMessage->content->textContent == null)
            {
                $mailer->body     = $emailMessage->content->htmlContent;
            }
            elseif($emailMessage->content->htmlContent != null && $emailMessage->content->textContent != null)
            {
                $mailer->body     = $emailMessage->content->htmlContent;
                $mailer->altBody  = $emailMessage->content->textContent;
            }
            $mailer->From = array($emailMessage->sender->fromAddress => $emailMessage->sender->fromName);
            foreach($emailMessage->recipients as $recipient)
            {
                $mailer->addAddressByType($recipient->toAddress, $recipient->toName, $recipient->type);
            }
        }

        protected function sendEmail(Mailer $mailer, EmailMessage $emailMessage)
        {
            try
            {
                $acceptedRecipients = $mailer->send();
                if($acceptedRecipients != $emailMessage->recipients->count())
                {
                    $content = Yii::t('Default', 'Response from Server') . "\n";
                    foreach($mailer->getSendResponseLog() as $logMessage)
                    {
                        $content .= $logMessage . "\n";
                    }
                    $emailMessageSendError = new EmailMessageSendError();
                    $data                  = array();
                    $data['message']                       = $content;
                    $emailMessageSendError->serializedData = serialize($data);
                    $emailMessage->folder                  = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                                                          EmailFolder::TYPE_OUTBOX_ERROR);
                    $emailMessage->error                   = $emailMessageSendError;
                }
                else
                {
                    $emailMessage->error    = null;
                    $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_SENT);
                }
            }
            catch (OutboundEmailSendException $e)
            {
                $emailMessageSendError = new EmailMessageSendError();
                $data = array();
                $data['code']                          = $e->getCode();
                $data['message']                       = $e->getMessage();
                $data['trace']                         = $e->getPrevious();
                $emailMessageSendError->serializedData = serialize($data);
                $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_OUTBOX_ERROR);
                $emailMessage->error    = $emailMessageSendError;
            }
        }

        protected function getOutboundMailer()
        {
            $mailer = new ZurmoSwiftMailer();
            $mailer->init();
            return $mailer;
        }

        public function getUserToSendNotificationsAs()
        {
            $keyName      = 'UserIdToSendNotificationsAs';
            $superGroup   = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (null != $userId = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName))
            {
                try
                {
                    $user  = User::getById($userId);

                    if($user->groups->contains($superGroup))
                    {
                        return $user;
                    }
                }
                catch(NotFoundException $e)
                {
                }
            }
            if($superGroup->users->count() == 0)
            {
                throw new NotSupportedException();
            }
            return $superGroup->users->offsetGet(0);
        }

        public function setUserToSendNotificationsAs(User $user)
        {
            assert('$user->id > 0');
            $superGroup   = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if(!$user->groups->contains($superGroup))
            {
                throw new NotSupportedException();
            }
            $keyName      = 'UserIdToSendNotificationsAs';
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $user->id);
        }

        public function getQueuedCount()
        {
            return count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX)) +
                   count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX_ERROR));
        }

        public function resolveFromAddressByUser(User$user)
        {
            assert('$user->id >0');
            if($user->primaryEmail->emailAddress == null)
            {
                return $this->defaultFromAddress;
            }
            return $user->primaryEmail->emailAddress;
        }
    }
?>