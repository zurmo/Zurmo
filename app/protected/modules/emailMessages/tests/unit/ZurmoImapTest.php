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

    class ZurmoImapTest extends ZurmoBaseTest
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
                Yii::app()->emailHelper->outboundSecurity = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundSecurity'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();
            }
        }

        public static function tearDownAfterClass()
        {
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapFolder'];
            $imap->init();
            $imap->connect();
            $imap->deleteMessages(true);

            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        public function testInit()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $this->assertEquals(null,    $imap->imapHost);
            $this->assertEquals(null,    $imap->imapUsername);
            $this->assertEquals(null,    $imap->imapPassword);
            $this->assertEquals(143,     $imap->imapPort);
            $this->assertEquals(null,    $imap->imapSSL);
            $this->assertEquals('INBOX', $imap->imapFolder);

            $imap->init();
            $this->assertEquals(null,    $imap->imapHost);
            $this->assertEquals(null,    $imap->imapUsername);
            $this->assertEquals(null,    $imap->imapPassword);
            $this->assertEquals(143,     $imap->imapPort);
            $this->assertEquals(null,    $imap->imapSSL);
            $this->assertEquals('INBOX', $imap->imapFolder);

            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapHost'));
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapUsername'));
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapPassword'));
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapPort'));
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapSSL'));
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'],
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'imapFolder'));

            $imap->init();
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'],     $imap->imapHost);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'], $imap->imapUsername);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'], $imap->imapPassword);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'],     $imap->imapPort);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'],      $imap->imapSSL);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'],   $imap->imapFolder);
        }

        /**
        * @depends testInit
        */
        public function testConnect()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = 'Wrong Password';
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertFalse($imap->connect());

            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort = "20";
            $imap->setInboundSettings();
            $imap->init();
            $this->assertFalse($imap->connect());

            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapFolder = 'UnexistingFolderName';
            $imap->setInboundSettings();
            $imap->init();
            $this->assertFalse($imap->connect());

            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];
            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());
        }

        public function testGetMessageBoxStatsDetailed()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());

            $messageBoxStatDetails = $imap->getMessageBoxStatsDetailed();
            $this->assertTrue($messageBoxStatDetails instanceof stdClass);
            $this->assertEquals('imap', $messageBoxStatDetails->Driver);
        }

        public function testGetMessageBoxStats()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());

            $messageBoxStat = $imap->getMessageBoxStats();
            $this->assertTrue($messageBoxStat instanceof stdClass);
            $this->assertEquals('imap', $messageBoxStat->Driver);
        }

        public function testDeleteMessages()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());
            $imap->deleteMessages(false);

            Yii::app()->emailHelper->sendRawEmail("Test Email",
                                                  Yii::app()->emailHelper->outboundUsername,
                                                  $imap->imapUsername,
                                                  'Test email body',
                                                  '<strong>Test</strong> email html body',
                                                  null, null, null
            );
            sleep(20);
            $this->assertTrue($imap->connect());
            $imapStats = $imap->getMessageBoxStatsDetailed();
            $this->assertTrue($imapStats->Nmsgs > 0);

            $imap->deleteMessages(true);
            $this->assertTrue($imap->connect());
            $imapStats = $imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);
        }

        public function testDeleteMessage()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());
            $imap->deleteMessages(true);
            $imapStats = $imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);

            Yii::app()->emailHelper->sendRawEmail("Test Email",
                                                  Yii::app()->emailHelper->outboundUsername,
                                                  $imap->imapUsername,
                                                  'Test email body',
                                                  '<strong>Test</strong> email html body',
                                                  null, null, null
            );
            sleep(3);
            $this->assertTrue($imap->connect());
            $imapStats = $imap->getMessageBoxStatsDetailed();
            $this->assertTrue($imapStats->Nmsgs > 0);

            $messages = $imap->getMessages();

            $imap->deleteMessage($messages[0]->uid);
            $imap->expungeMessages();
            $this->assertTrue($imap->connect());
            $imapStats = $imap->getMessageBoxStatsDetailed();
            $this->assertEquals(0, $imapStats->Nmsgs);
        }

        public function testGetMessagesWithoutAttachments()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());

            $imap->deleteMessages(true);
            Yii::app()->emailHelper->sendRawEmail("Test Email",
                                                  Yii::app()->emailHelper->outboundUsername,
                                                  $imap->imapUsername,
                                                  'Test email body',
                                                  '<strong>Test</strong> email html body',
                                                  null,
                                                  null,
                                                  null
            );
            sleep(20);
            $messages = $imap->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Test Email", $messages[0]->subject);
            $this->assertEquals("Test email body", trim($messages[0]->textBody));
            $this->assertEquals("<strong>Test</strong> email html body", trim($messages[0]->htmlBody));
            $this->assertEquals($imap->imapUsername, $messages[0]->to[0]['email']);
            $this->assertEquals(Yii::app()->emailHelper->outboundUsername, $messages[0]->fromEmail);
        }

        public function testGetMessagesWithAttachments()
        {
            if (!EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $this->markTestSkipped(Zurmo::t('EmailMessagesModule', 'Test email settings are not configured in perInstanceTest.php file.'));
            }
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['dropboxImapSettings']['imapFolder'];

            $imap->setInboundSettings();
            $imap->init();
            $this->assertTrue($imap->connect());

            $imap->deleteMessages(true);

            $pathToFiles = Yii::getPathOfAlias('application.modules.emailMessages.tests.unit.files');
            $filePath_1    = $pathToFiles . DIRECTORY_SEPARATOR . 'table.csv';
            $filePath_2    = $pathToFiles . DIRECTORY_SEPARATOR . 'image.png';
            $filePath_3    = $pathToFiles . DIRECTORY_SEPARATOR . 'text.txt';

            Yii::app()->emailHelper->sendRawEmail("Test Email",
                                                  Yii::app()->emailHelper->outboundUsername,
                                                  $imap->imapUsername,
                                                  'Test email body',
                                                  '<strong>Test</strong> email html body',
                                                  array(Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername']),
                                                  array(Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername']),
                                                  array($filePath_1, $filePath_2, $filePath_3)
            );
            sleep(40);
            $messages = $imap->getMessages();
            $this->assertEquals(1, count($messages));
            $this->assertEquals("Test Email", $messages[0]->subject);
            $this->assertEquals("Test email body", trim($messages[0]->textBody));
            $this->assertEquals("<strong>Test</strong> email html body", trim($messages[0]->htmlBody));
            $this->assertEquals($imap->imapUsername, $messages[0]->to[0]['email']);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'], $messages[0]->cc[0]['email']);
            $this->assertEquals(Yii::app()->emailHelper->outboundUsername, $messages[0]->fromEmail);

            $this->assertEquals(3, count($messages[0]->attachments));

            $this->assertEquals('table.csv', $messages[0]->attachments[0]['filename']);
            $this->assertTrue(strlen($messages[0]->attachments[0]['attachment']) > 0);
            $this->assertEquals('image.png', $messages[0]->attachments[1]['filename']);
            $this->assertTrue($messages[0]->attachments[1]['attachment'] != '');
            $this->assertEquals('text.txt', $messages[0]->attachments[2]['filename']);
            $this->assertTrue(strlen($messages[0]->attachments[2]['attachment']) > 0);
        }
    }
?>
