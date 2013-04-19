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

    class EmailMessageSendErrorTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
            EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
        }

        /**
         * A notification email is different than a regular outbound email because it is owned by a super user
         * that is different than the user logged in.  So the sender does not have a 'person'
         */
        public function testCreateEmailMessageThatIsANotification()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $this->assertEquals(0, count(EmailMessage::getAll()));

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $emailMessage->subject = 'My First Email';
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
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'billy@fakeemail.com';
            $recipient->toName          = 'Billy James';
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personOrAccount = $billy;
            $emailMessage->recipients->add($recipient);

            //At this point the message is in no folder
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            $this->assertEquals(0, count(EmailMessageSendError::getAll()));
            $emailMessageSendError = new EmailMessageSendError();
            $data                  = array();
            $data['message']       = 'error message';
            $emailMessageSendError->serializedData = serialize($data);
            $emailMessage->folder                  = EmailFolder::getByBoxAndType($emailMessage->folder->emailBox,
                                                     EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->error                   = $emailMessageSendError;
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertEquals(1, count(EmailMessageSendError::getAll()));

            //Now swap the error with a new one
            $emailMessageId = $emailMessage->id;
            $emailMessage->forget();
            $emailMessage   = EmailMessage::getById($emailMessageId);
            $emailMessageSendError = new EmailMessageSendError();
            $data                  = array();
            $data['message']       = 'error message 2';
            $emailMessageSendError->serializedData = serialize($data);
            $emailMessage->error                   = $emailMessageSendError;
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertEquals(1, count(EmailMessageSendError::getAll()));
        }
    }
?>