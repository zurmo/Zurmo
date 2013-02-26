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

    class CommentsUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('steven');
            UserTestHelper::createBasicUser('jack');
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testsSendNotificationOnNewComment()
        {
            $super                      = User::getByUsername('super');
            $steven                     = User::getByUsername('steven');
            $jack                       = User::getByUsername('jack');
            $conversation               = new Conversation();
            $conversation->owner        = Yii::app()->user->userModel;
            $conversation->subject      = 'My test subject2';
            $conversation->description  = 'My test description2';
            $this->assertTrue($conversation->save());
            $comment                    = new Comment();
            $comment->description       = 'This is the 1st test comment';

            //Confirm no email notifications are sitting in the queue
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //No message was sent because Steven and Jack don't have primary email address
            CommentsUtil::sendNotificationOnNewComment($conversation, $comment, $super, array($steven, $jack));
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $super->primaryEmail->emailAddress   = 'super@zurmo.org';
            $steven->primaryEmail->emailAddress  = 'steven@zurmo.org';
            $jack->primaryEmail->emailAddress    = 'jack@zurmo.org';
            $this->assertTrue($super->save());
            $this->assertTrue($steven->save());
            $this->assertTrue($jack->save());

            //One email message was sent because to Steven and Jack
            CommentsUtil::sendNotificationOnNewComment($conversation, $comment, $super, array($steven, $jack));
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAll();
            $emailMessage  = $emailMessages[0];
            $this->assertEquals(2, count($emailMessage->recipients));

            //One email message was sent to Super but not to Steven
            UserConfigurationFormAdapter::setValue($steven, true, 'turnOffEmailNotifications');
            CommentsUtil::sendNotificationOnNewComment($conversation, $comment, $jack, array($steven, $super));
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAll();
            $emailMessage  = $emailMessages[1];
            $this->assertEquals(1, count($emailMessage->recipients));
        }
    }
?>