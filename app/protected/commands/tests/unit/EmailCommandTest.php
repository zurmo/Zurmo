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

    class EmailCommandTest extends ZurmoBaseTest
    {
        public static $emailHelperSendEmailThroughTransport;

        public static $userImap;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();

            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapFolder'];
            $imap->init();
            $imap->connect();

            self::$emailHelperSendEmailThroughTransport = Yii::app()->emailHelper->sendEmailThroughTransport;
            self::$userImap = $imap;
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        public function testActionSend()
        {
            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $super                      = User::getByUsername('super');
                Yii::app()->user->userModel = $super;

                chdir(COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands');

                $outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                $outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                $outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                $outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                $outboundSecurity = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundSecurity'];

                $toAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];

                $subject          = 'A test email from Zurmo';
                $textContent      = 'A test text message from Zurmo.';
                $htmlContent      = 'A test html message from Zurmo.';

                self::$userImap->deleteMessages(true);
                self::$userImap->connect();
                $imapStats = self::$userImap->getMessageBoxStatsDetailed();
                $this->assertEquals(0, $imapStats->Nmsgs);

                // Begin Not Coding Standard
                $command = "php zurmocTest.php email send --username=super --toAddress=$toAddress --subject='$subject' --textContent='$textContent' " .
                           "--htmlContent='$htmlContent' --host=$outboundHost --port=$outboundPort --outboundUsername=$outboundUsername " .
                           "--outboundPassword=$outboundPassword";
                // End Not Coding Standard
                if (isset($outboundSecurity) && $outboundSecurity != false)
                {
                    $command .= " --outboundSecurity=$outboundSecurity"; // Not Coding Standard
                }
                if (!IS_WINNT)
                {
                    $command .= ' 2>&1';
                }

                exec($command, $output);

                // Check if user got email
                sleep(30);
                self::$userImap->connect();
                $imapStats = self::$userImap->getMessageBoxStatsDetailed();
                $this->assertEquals(1, $imapStats->Nmsgs);
                $messages = self::$userImap->getMessages();
                $this->assertEquals(1, count($messages));
                $this->assertEquals('A test email from Zurmo', trim($messages[0]->subject));
                $this->assertEquals('A test html message from Zurmo.', trim($messages[0]->htmlBody));
                $this->assertEquals('A test text message from Zurmo.', trim($messages[0]->textBody));
                $this->assertEquals(strval(Yii::app()->user->userModel), trim($messages[0]->fromName));
                $this->assertEquals(Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel), trim($messages[0]->fromEmail));
                $this->assertTrue(empty($messages[0]->attachments));
            }
        }
    }
?>
