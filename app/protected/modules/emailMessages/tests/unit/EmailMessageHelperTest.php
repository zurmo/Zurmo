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

    class EmailMessageHelperTest extends ZurmoBaseTest
    {
        public static $emailHelperSendEmailThroughTransport;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            self::$emailHelperSendEmailThroughTransport = Yii::app()->emailHelper->sendEmailThroughTransport;

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();

                Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
                Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
                Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
                Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
                Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
                Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
                Yii::app()->imap->setInboundSettings();
                Yii::app()->imap->init();
            }
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        public function testSendSystemEmail()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }

            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            Yii::app()->imap->connect();

            $messages = EmailMessage::getAll();
            foreach ($messages as $message)
            {
                $message->delete();
            }
            // Expunge all emails from dropbox
            Yii::app()->imap->deleteMessages(true);
            $this->assertEquals(0, count(EmailMessage::getAll()));
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            $subject = "System Message";
            $textMessage = "System message content.";
            $htmlMessage = "<strong>System</strong> message content.";

            EmailMessageHelper::sendSystemEmail($subject, array(Yii::app()->imap->imapUsername), $textMessage, $htmlMessage);
            sleep(30);

            Yii::app()->imap->connect();
            $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
            $this->assertEquals(1, $imapStats->Nmsgs);
            $this->assertEquals(1, count(EmailMessage::getAll()));
            $emailMessages = EmailMessage::getAll();
            $emailMessage = $emailMessages[0];

            $this->assertEquals('System Message', $emailMessage->subject);
            $this->assertEquals('System message content.', trim($emailMessage->content->textContent));
            $this->assertEquals('<strong>System</strong> message content.', trim($emailMessage->content->htmlContent));

            $this->assertEquals(1, count($emailMessage->recipients));
            foreach ($emailMessage->recipients as $recipient)
            {
                $this->assertEquals($recipient->toAddress, Yii::app()->imap->imapUsername);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipient->type);
            }
        }
    }
?>