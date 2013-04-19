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

    class EmailHelperTest extends ZurmoBaseTest
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

        /**
         * @depends testLoadOutboundSettings
         */
        public function testLoadOutboundSettingsFromUserEmailAccount()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailHelper = new EmailHelper;

            //Load outbound setting when no EmailAccount was created
            try
            {
                $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
            }
            catch (NotFoundException $e)
            {
                $this->addToAssertionCount(1);
            }

            //Load outbound setting when EmailAccount useCustomOutboundSettings = false
            EmailMessageTestHelper::createEmailAccount($billy);
            $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
            $this->assertEquals('smtp', $emailHelper->outboundType);
            $this->assertEquals(25, $emailHelper->outboundPort);
            $this->assertEquals('xxx', $emailHelper->outboundHost);
            $this->assertNull($emailHelper->outboundUsername);
            $this->assertNull($emailHelper->outboundPassword);
            $this->assertNull($emailHelper->outboundSecurity);
            $this->assertEquals('notifications@zurmoalerts.com', $emailHelper->fromAddress);
            $this->assertEquals(strval($billy), $emailHelper->fromName);

            //Load outbound setting when EmailAccount useCustomOutboundSettings = true
            $emailAccount = EmailAccount::getByUserAndName($billy);
            $emailAccount->useCustomOutboundSettings = true;
            $emailAccount->outboundType = 'xyz';
            $emailAccount->outboundPort = 55;
            $emailAccount->outboundHost = 'zurmo.com';
            $emailAccount->outboundUsername = 'billy';
            $emailAccount->outboundPassword = 'billypass';
            $emailAccount->outboundSecurity = 'ssl';
            $emailAccount->save();
            $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
            $this->assertEquals('xyz', $emailHelper->outboundType);
            $this->assertEquals(55, $emailHelper->outboundPort);
            $this->assertEquals('zurmo.com', $emailHelper->outboundHost);
            $this->assertEquals('billy', $emailHelper->outboundUsername);
            $this->assertEquals('billypass', $emailHelper->outboundPassword);
            $this->assertEquals('ssl', $emailHelper->outboundSecurity);
            $this->assertEquals($billy->getFullName(), $emailHelper->fromName);
            $this->assertEquals('user@zurmo.com', $emailHelper->fromAddress);
        }

        /**
         * @depends testSend
         */
        public function testSendRealEmail()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            Yii::app()->emailHelper->sendEmailThroughTransport = true;

            Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapHost'];
            Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPassword'];
            Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPort'];
            Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapSSL'];
            Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapFolder'];
            Yii::app()->imap->setInboundSettings();
            Yii::app()->imap->init();

            Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
            Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
            Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
            Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
            Yii::app()->emailHelper->outboundSecurity = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundSecurity'];
            Yii::app()->emailHelper->sendEmailThroughTransport = true;
            Yii::app()->emailHelper->setOutboundSettings();
            Yii::app()->emailHelper->init();

            $steve = UserTestHelper::createBasicUser('steve');
            EmailMessageTestHelper::createEmailAccount($steve);

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $user = User::getByUsername('steve');
                $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
                $this->assertTrue($user->save());
            }

            Yii::app()->imap->connect();
            Yii::app()->imap->deleteMessages(true);
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            $emailMessage = EmailMessageTestHelper::createOutboxEmail($super, 'Test email',
                'Raw content', ',b>html content</b>end.', // Not Coding Standard
                'Zurmo', Yii::app()->emailHelper->outboundUsername,
                'Ivica', Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername']);

            Yii::app()->imap->connect();
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(3, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued($emailMessage);
            $job = new ProcessOutboundEmailJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(4, Yii::app()->emailHelper->getSentCount());

            sleep(30);
            Yii::app()->imap->connect();
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);

            Yii::app()->emailHelper->sendEmailThroughTransport = false;
        }
    }
?>