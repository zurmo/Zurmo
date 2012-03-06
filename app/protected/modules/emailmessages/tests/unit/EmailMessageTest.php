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
            $jane = UserTestHelper::createBasicUser('jane');
            UserTestHelper::createBasicUser('sally');
            UserTestHelper::createBasicUser('jason');
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            EmailBoxUtil::setBoxAndDefaultFoldersByUserAndName($jane, 'JaneBox');
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
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.'),
                                                   'fromName'      => array('From Name cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

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
            $recipient->person         = $billy;
            $emailMessage->recipients->add($recipient);

            //At this point the message is in no folder
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            //At this point the message should be in the draft folder by default.
            $this->assertEquals(EmailFolder::getDefaultDraftName(), $emailMessage->folder->name);
            $this->assertEquals(EmailFolder::TYPE_DRAFT, $emailMessage->folder->type);
        }

        /**
         * @depends testCreateEmailMessageThatIsANotification
         * @expectedException NotSupportedException
         */
        public function testAttemptingToSendEmailInOutbox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessages              = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));

            //Now put the message in the outbox. Should not send.
            $box                      = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessages[0]->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);

            Yii::app()->emailHelper->send($emailMessages[0]);
        }

        /**
         * @depends testAttemptingToSendEmailInOutbox
         */
        public function testAttemptingToSendEmailNotOutbox()
        {
            $super                            = User::getByUsername('super');
            Yii::app()->user->userModel       = $super;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages                    = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            //Because it was set to outbox from last test, stil at outbox.
            $this->assertTrue($emailMessages[0]->folder->type   == EmailFolder::TYPE_OUTBOX);
            $box                      = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessages[0]->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            $sentOrQueued = Yii::app()->emailHelper->send($emailMessages[0]);
            $this->assertTrue($sentOrQueued);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            //The message, because it is queued, should still be in the outbox
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $emailMessages[0]->folder->type);
        }

        /**
         * @depends testAttemptingToSendEmailNotOutbox
         */
        public function testCreateNormalEmailMessage()
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
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.'),
                                                   'fromName'      => array('From Name cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

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
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);
        }

        /**
         * @depends testCreateNormalEmailMessage
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
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.'),
                                                   'fromName'      => array('From Name cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());

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
            $this->assertTrue($emailMessage->folder->id < 0);

            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            $id = $emailMessage->id;
            unset($emailMessage);
            $emailMessage = EmailMessage::getById($id);
            $this->assertEquals('My Email with an Attachment', $emailMessage->subject);
            $this->assertEquals(1, $emailMessage->files->count());
            $this->assertEquals($emailFileModel, $emailMessage->files->offsetGet(0));
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

            $this->assertEquals(3, count(EmailMessage::getAll()));

            $emailMessage = new EmailMessage();
            $emailMessage->owner   = $jane;
            $emailMessage->subject = 'My Third Email';

            //Attempt to save without setting required information
            $saved        = $emailMessage->save();
            $this->assertFalse($saved);
            $compareData = array('folder' => array('name'          => array('Name cannot be blank.'),
                                                   'emailBox'      => array('name' => array('Name cannot be blank.'))),
                                 'sender' => array('fromAddress'   => array('From Address cannot be blank.'),
                                                   'fromName'      => array('From Name cannot be blank.')));
            $this->assertEquals($compareData, $emailMessage->getErrors());
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
            $this->assertTrue($emailMessage->folder->id < 0);
            $box                  = EmailBox::resolveAndGetByName('JaneBox');
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);

            //Save, at this point the email should be in the draft folder
            $saved = $emailMessage->save();
            $this->assertTrue($saved);
            $this->assertTrue($emailMessage->folder->id > 0);

            //Now send the message.
            Yii::app()->emailHelper->send($emailMessage);
        }

        /**
         * @depends testMultipleRecipientsAndTypes
         */
        public function testQueuedEmailsWhenEmailMessageChangeToSentFolder()
        {
            $super                            = User::getByUsername('super');
            Yii::app()->user->userModel       = $super;
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages                    = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(2, count($emailMessages));
            $emailMessages[0]->folder->type = EmailFolder::TYPE_OUTBOX;
            $emailMessages[1]->folder->type = EmailFolder::TYPE_OUTBOX;
            $emailMessageId = $emailMessages[0]->id;

            $sent = Yii::app()->emailHelper->sendQueued();
            $this->assertTrue($sent);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, count(EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX)));

            $emailMessages                    = EmailMessage::getAllByFolderType(EmailFolder::TYPE_SENT);
            $this->assertEquals(2, count($emailMessages));
            $this->assertEquals($emailMessageId, $emailMessages[0]->id);
        }
    }
?>