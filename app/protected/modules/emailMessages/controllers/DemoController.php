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

    Yii::import('application.modules.emailMessages.controllers.DefaultController', true);
    Yii::import('application.modules.accounts.tests.unit.AccountTestHelper', true);
    Yii::import('application.modules.contacts.tests.unit.ContactTestHelper', true);
    class EmailMessagesDemoController extends EmailMessagesDefaultController
    {
        /**
         * Special method to load each type of email message.  Can help with reviewing user interface scenarios.
         */
        public function actionLoadEmailMessagesSampler()
        {
            if (Yii::app()->user->userModel->username != 'super')
            {
                throw new NotSupportedException();
            }
            $box                  = EmailBoxUtil::getDefaultEmailBoxByUser(Yii::app()->user->userModel);
            $account = AccountTestHelper::
                            createAccountByNameForOwner('Email Messages Test Account', Yii::app()->user->userModel);
            $contact = ContactTestHelper::
                            createContactWithAccountByNameForOwner('BobMessage', Yii::app()->user->userModel, $account);

            //#1 Create Archived - Sent
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test archived sent email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'super@zurmotest.com';
            $sender->fromName          = 'Super User';
            $sender->personOrAccount   = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = strval($contact);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = $contact;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //#2 Create Archived - Received
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test archived received email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'bob.message@zurmotest.com';
            $sender->fromName          = strval($contact);
            $sender->personOrAccount   = $contact;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'super@zurmotest.com';
            $recipient->toName          = 'Super User';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = Yii::app()->user->userModel;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //#3 Sent from Zurmo UI
                    $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test archived sent email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'super@zurmotest.com';
            $sender->fromName          = 'Super User';
            $sender->personOrAccount   = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = strval($contact);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = $contact;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //#4 Received from future Zurmo mail client
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test archived received email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'bob.message@zurmotest.com';
            $sender->fromName          = strval($contact);
            $sender->personOrAccount   = $contact;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'super@zurmotest.com';
            $recipient->toName          = 'Super User';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = Yii::app()->user->userModel;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_INBOX);
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Special method to load archived, but unmatched emails. This is for use with the @see ArchivedEmailMatchingListView
         */
        public function actionLoadUnmatchedSampler()
        {
            if (Yii::app()->user->userModel->username != 'super')
            {
                throw new NotSupportedException();
            }
            $box                  = EmailBoxUtil::getDefaultEmailBoxByUser(Yii::app()->user->userModel);

                    //#1 Create Archived - Sent
            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test unmatched archived sent email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'super@zurmotest.com';
            $sender->fromName          = 'Super User';
            $sender->personOrAccount   = Yii::app()->user->userModel;
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = 'Bobby Bobson';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED_UNMATCHED);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }

            //#2 Create Archived - Received
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
            $recipient->toAddress       = 'super@zurmotest.com';
            $recipient->toName          = 'Super User';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = Yii::app()->user->userModel;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED_UNMATCHED);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $saved = $emailMessage->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function actionSendDemoEmailNotifications()
        {
            if (Yii::app()->user->userModel->username != 'super')
            {
                throw new NotSupportedException();
            }
            $template        = "{message}<br/>";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageLogger = new MessageLogger($messageStreamer);

            if (Yii::app()->user->userModel->primaryEmail->emailAddress == null)
            {
                $messageLogger->addErrorMessage('Cannot send test emails because the current user does not have an email address');
                Yii::app()->end(0, false);
            }
            $messageLogger->addInfoMessage('Using type:' . Yii::app()->emailHelper->outboundType);
            $messageLogger->addInfoMessage('Using host:' . Yii::app()->emailHelper->outboundHost);
            $messageLogger->addInfoMessage('Using port:' . Yii::app()->emailHelper->outboundPort);
            $messageLogger->addInfoMessage('Using username:' . Yii::app()->emailHelper->outboundUsername);
            if (Yii::app()->emailHelper->outboundPassword != null)
            {
                $messageLogger->addInfoMessage('Using password: Yes');
            }
            else
            {
                $messageLogger->addInfoMessage('Using password: No');
            }
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $notificationClassNames = $module::getAllClassNamesByPathFolder('data');
                foreach ($notificationClassNames as $notificationClassName)
                {
                    if (!strpos($notificationClassName, 'DemoEmailNotifications') === false)
                    {
                        $demoNotification = new $notificationClassName();
                        $demoNotification->run(Yii::app()->user->userModel, $messageLogger);
                    }
                }
            }
            Yii::app()->emailHelper->sendQueued();
        }
    }
?>
