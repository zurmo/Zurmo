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

    class EmailHelperTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
            $someoneSuper = UserTestHelper::createBasicUser('someoneSuper');

            $group = Group::getByName('Super Administrators');
            $group->users->add($someoneSuper);
            $saved = $group->save();
            assert($saved); // Not Coding Standard

            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
        }

        public function testSetAndGetUserToSendNotificationAs()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //It should default to the first super user available.
            $user = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $this->assertEquals($user, $super);

            //Set a differnt super admin user, then make sure it correctly retrieves it.
            $anotherSuper = User::getByUsername('someoneSuper');
            Yii::app()->emailHelper->setUserToSendNotificationsAs($anotherSuper);
            $user = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $this->assertEquals($user, $anotherSuper);
        }

        /**
         * @depends testSetAndGetUserToSendNotificationAs
         */
        public function testSetAndGetUserToSendNotificationAsLoggedInAsNonSuper()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $anotherSuper               = User::getByUsername('someoneSuper');
            $user                       = Yii::app()->emailHelper->getUserToSendNotificationsAs();
            $this->assertEquals($user, $anotherSuper);
        }

        /**
         * @depends testSetAndGetUserToSendNotificationAsLoggedInAsNonSuper
         * @expectedException NotSupportedException
         */
        public function testSetUserToSendNotificationsAsWhoIsNotASuperAdmin()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            Yii::app()->emailHelper->setUserToSendNotificationsAs($billy);

        }

        /**
         * @depends testSetUserToSendNotificationsAsWhoIsNotASuperAdmin
         */
        public function testSend()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email', $super);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->send($emailMessage);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }

        /**
         * @depends testSend
         */
        public function testSendQueued()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //add a message in the outbox_error folder.
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 2', $super);
            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->save();

            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(2, Yii::app()->emailHelper->getSentCount());
        }

        /**
         * @depends testSendQueued
         */
        public function testSendImmediately()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 2', $super);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(2, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(3, Yii::app()->emailHelper->getSentCount());
        }

        /**
         * @depends testSendImmediately
         */
        public function testLoadOutboundSettings()
        {
            $emailHelper = new EmailHelper;
            $emailHelper->outboundHost = null;

            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'outboundHost', 'xxx');

            $emailHelper = new EmailHelper;
            $emailHelper->outboundHost = 'xxx';
        }
    }
?>