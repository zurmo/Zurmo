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

    class EmailMessageTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
            UserTestHelper::createBasicUser('sally');
            UserTestHelper::createBasicUser('jason');
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

            //Attempt to save without setting required information
            $saved        = $email->save();
            $this->assertFalse($saved);
            $compareData = array('should be array of sender, recipient, content etc.');
            $this->assertEquals($compareData, $email->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
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
            $recipient->type           = EmailMessageRecipient::TO;
            $recipient->person         = $billy;
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertNull($emailMessage->folder);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
            $this->assertEquals(EmailFolder::getByName(EmailFolder::EVERYONE_DRAFT_NAME), $emailMessage->folder->id);
        }

        /**
         * @depends testCreateEmailMessageThatIsANotification
         * @expectedException NotSupportedException
         */
        public function testAttemptingToSendEmailNotInOutbox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessages              = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            Yii::app()->emailHelper->send($emailMessages[0]);
        }

        /**
         * @depends testAttemptingToSendEmailNotInOutbox
         */
        public function testAttemptingToSendEmailInOutbox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessages              = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $emailMessages[0]->folder   = EmailFolder::EVERYONE_OUTBOX_NAME;
            Yii::app()->emailHelper->send($emailMessages[0]);
            //todo: should send() return errors? something?
            $this->assertTrue($emailMessages[0]->wasSentOk()); //todo: i dont think this is how i want this to happen,
            //i think having send() return something might be better, maybe not but we should think about this a bit more
            //and decide how to do the error handling for sending email. I dont really want the error info to be properties
            //on the emailMessage would be better to somehow be more decoupled... But think about sending 100 emails at one
            //we probably have to store this error info related to the emailMessage.
            $emailMessages[0]->folder   = EmailFolder::EVERYONE_SENT_NOTIFICATIONS_NAME;
            $saved                      = $emailMessages[0]->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testAttemptingToSendEmailInOutbox
         */
        public function testCreateANormalEmailMessage()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');

            $this->assertEquals(1, count(EmailMessage::getAll()));

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Second Email';

            //Attempt to save without setting required information
            $saved        = $email->save();
            $this->assertFalse($saved);
            $compareData = array('should be array of sender, recipient, content etc.');
            $this->assertEquals($compareData, $email->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->person            = $jane;
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'billy@fakeemail.com';
            $recipient->toName         = 'Billy James';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $recipient->person         = $billy;
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertNull($emailMessage->folder);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
            $this->assertEquals(EmailFolder::getByName(EmailFolder::EVERYONE_DRAFT_NAME), $emailMessage->folder->id);
        }

        /**
         * @depends testCreateANormalEmailMessage
         */
        public function testCreateAndSendEmailMessageWithAttachment()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Email with an Attachment';
            $emailFileModel        = ZurmoTestHelper::createFileModel('testNote.txt', 'EmailFileModel');
            $emailMessage->files->add($emailFileModel);

            //Attempt to save without setting required information
            $saved        = $email->save();
            $this->assertFalse($saved);
            $compareData = array('should be array of sender, recipient, content etc.');
            $this->assertEquals($compareData, $email->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Second Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->person            = $jane;
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'billy@fakeemail.com';
            $recipient->toName         = 'Billy James';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $recipient->person         = $billy;
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertNull($emailMessage->folder);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
            $this->assertEquals(EmailFolder::getByName(EmailFolder::EVERYONE_DRAFT_NAME), $emailMessage->folder->id);

            $id = $emailMessage->id;
            unset($emailMessage);
            $note = EmailMessage::getById($id);
            $this->assertEquals('My Email with an Attachment', $emailMessage->subject);
            $this->assertEquals(1, $emailMessage->files->count());
            $this->assertEquals($emailFileModel, $note->files->offsetGet(0));
        }

        /**
         * @depends testCreateAndSendEmailMessageWithAttachment
         */
        public function testMultipleRecipientsAndTypes()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');
            $jane                       = User::getByUsername('jane');
            $sally                      = User::getByUsername('sally');
            $jason                      = User::getByUsername('jason');

            $this->assertEquals(1, count(EmailMessage::getAll()));

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Third Email';

            //Attempt to save without setting required information
            $saved        = $email->save();
            $this->assertFalse($saved);
            $compareData = array('should be array of sender, recipient, content etc.');
            $this->assertEquals($compareData, $email->getErrors());

            //Set sender, and recipient, and content
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My Third Message';
            $emailMessage->content     = $emailContent;

            //Sending from jane
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'jane@fakeemail.com';
            $sender->fromName          = 'Jane Smith';
            $sender->person            = $jane;
            $emailMessage->sender      = $sender;

            //Recipient is billy.
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'billy@fakeemail.com';
            $recipient->toName         = 'Billy James';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $recipient->person         = $billy;
            $emailMessage->recipients->add($recipient);

            //CC recipient is Sally
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'sally@fakeemail.com';
            $recipient->toName         = 'Sally Pail';
            $recipient->type           = EmailMessageRecipient::TYPE_CC;
            $recipient->person         = $sally;
            $emailMessage->recipients->add($recipient);

            //BCC recipient is Jason
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'jason@fakeemail.com';
            $recipient->toName         = 'Jason Blue';
            $recipient->type           = EmailMessageRecipient::TYPE_BCC;
            $recipient->person         = $jason;
            $emailMessage->recipients->add($recipient);

            //At this point the message is not in a folder.
            $this->assertNull($emailMessage->folder);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
            $this->assertEquals(EmailFolder::getByName(EmailFolder::EVERYONE_DRAFT_NAME), $emailMessage->folder->id);

            //Now send the message.
            Yii::app()->emailHelper->send($emailMessages);
        }
    }
?>