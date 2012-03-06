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
        /**
         * Send an email message. This will queue up the email to be sent by the queue sending process. If you want to
         * send immediately, consider using @sendImmediately
         * @param EmailMessage $email
         */
        public function send(EmailMessage $emailMessage)
        {
            if($emailMessage->folder->type == EmailFolder::TYPE_OUTBOX ||
               $emailMessage->folder->type == EmailFolder::TYPE_SENT)
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

            //todo: move this into a getOutboundMailer, then you can use private to detect if this object is already created and populated.
            //todo: override a method in EmailHelperForTesting, that doesnt return a swiftmailer? or maybe it doesnt but then interjects somehow
            //the sending process.
            //with smtp info etc.
            //Yii::import('ext.swiftmailer.SwiftMailer');
            //$swiftMailer = new SwiftMailer();

            $emailMessage->folder   = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox, EmailFolder::TYPE_SENT);
            $saved                  = $emailMessage->save();
            if(!$saved)
            {
                throw new NotSupportedException();
            }
            return true;

        }

        public function sendQueued()
        {
            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            foreach($queuedEmailMessages as $emailMessage)
            {
                $this->sendImmediately($emailMessage);
            }
            return true;
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

        public function getQueuedCount()
        {
            return count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX));
        }

       //todo: note, build interactive command (or just taking the subject/message/to as params so we can test and send email via command line.
       //make it interactive.
    }
?>