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

    class EmailArchivingUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ReadPermissionsOptimizationUtil::rebuild();
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
        }

        public function testIsMessageForwarded()
        {
            Yii::app()->imap->imapUsername = 'dropbox@emample.com';

            $imapMessage = new ImapMessage();
            $imapMessage->subject        = "Test subject";
            $imapMessage->to[0]['email'] = 'dropbox@emample.com';
            $imapMessage->fromEmail      = 'steve@example.com';
            $this->assertTrue(EmailArchivingUtil::isMessageForwarded($imapMessage));

            $imapMessage->to[0]['email'] = 'dropbox@emample.com';
            $imapMessage->to[1]['email'] = 'john@emample.com';
            $this->assertTrue(EmailArchivingUtil::isMessageForwarded($imapMessage));

            $imapMessage->to[0]['email'] = 'john@emample.com';
            $imapMessage->to[1]['email'] = 'dropbox@emample.com';
            $this->assertTrue(EmailArchivingUtil::isMessageForwarded($imapMessage));

            $imapMessage = new ImapMessage();
            $imapMessage->subject        = "Test subject";
            $imapMessage->to[0]['email'] = 'john@emample.com';
            $imapMessage->cc[0]['email'] = 'dropbox@emample.com';
            $this->assertFalse(EmailArchivingUtil::isMessageForwarded($imapMessage));

            $imapMessage = new ImapMessage();
            $imapMessage->subject        = "Test subject";
            $imapMessage->to[0]['email'] = 'john@emample.com';
            $imapMessage->cc[0]['email'] = 'peter@emample.com';
            $imapMessage->cc[1]['email'] = 'dropbox@emample.com';
            $this->assertFalse(EmailArchivingUtil::isMessageForwarded($imapMessage));

            // Bcc is not visible when email message is sent to dropbox as Bcc
            $imapMessage = new ImapMessage();
            $imapMessage->subject        = "Test subject";
            $imapMessage->to[0]['email'] = 'john@emample.com';
            $this->assertFalse(EmailArchivingUtil::isMessageForwarded($imapMessage));
        }

        public function testResolveEmailSenderFromForwardedEmailMessage()
        {
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";

            // Outlook, Yahoo, Outlook express format
            // Begin Not Coding Standard
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve Tytler' <steve@example.com>, Peter Smith <peter@example.com>
Cc: 'John Wein' <john@example.com>, Peter Smith <peter@example.com>
Subject: Hello Steve";
            // End Not Coding Standard

            $imapMessage->subject = "FW: Test subject";
            $sender = EmailArchivingUtil::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $sender['email']);
            $this->assertEquals('John Smith', $sender['name']);

            //Google, Thunderbird format
            // Begin Not Coding Standard
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve Tytler' <steve@example.com>, Peter Smith <peter@example.com>
Cc: 'John Wein' <john@example.com>, Peter Smith <peter@example.com>";
            // End Not Coding Standard

            $sender = EmailArchivingUtil::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $sender['email']);
            $this->assertEquals('John Smith', $sender['name']);

            $imapMessage->textBody = "
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve'";

            $sender = EmailArchivingUtil::resolveEmailSenderFromForwardedEmailMessage($imapMessage);
            $this->assertFalse($sender);
        }

        /**
        * @depends testResolveEmailSenderFromForwardedEmailMessage
        */
        public function testResolveEmailSenderFromEmailMessage()
        {
            Yii::app()->imap->imapUsername = 'dropbox@emample.com';

            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";
            $imapMessage->cc[0]['email'] = 'dropbox@emample.com';
            $from = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->fromEmail, $from['email']);

            $imapMessage = new ImapMessage();
            $imapMessage->fromEmail = "test@example.com";
            $imapMessage->to[0]['email'] = 'dropbox@emample.com';

            // Outlook, Yahoo, Outlook express format
            $imapMessage->textBody = "
From: John Smith [mailto:john@example.com]
Sent: 02 March 2012 AM 01:23
To: 'Steve Tytler' <steve@example.com>, Peter Smith <peter@example.com>
Cc: Peter Smith <peter@example.com>
Subject: Hello Steve";

            $imapMessage->subject = "FW: Test subject";
            $from = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            //Google, Thunderbird format
            $imapMessage->textBody = "
From: John Smith <john@example.com>
Date: Thu, Apr 19, 2012 at 5:22 PM
Subject: Hello Steve
To: 'Steve Tytler' <steve@example.com>, Peter Smith <peter@example.com>
Cc: 'John Wein' <john@example.com>, Peter Smith <peter@example.com>";
            $from = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->to[0]['email'] = 'dropbox@emample.com';
            // Begin Not Coding Standard
            $imapMessage->htmlBody = "

            -------- Original Message --------
            Subject:    Test
            Date:   Mon, 28 May 2012 15:43:39 +0200
            From:   John Smith <john@example.com>
            To: 'Steve'
            ";
            // End Not Coding Standard
            $from = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);

            $imapMessage = new ImapMessage();
            $imapMessage->to[0]['email'] = 'dropbox@emample.com';
            $imapMessage->subject = "Fwd: Test subject";
            // Begin Not Coding Standard
            $imapMessage->textBody = "
-------- Original Message --------
Subject:     Test
Date:   Mon, 28 May 2012 15:43:39 +0200
From:   John Smith <john@example.com>
To: 'Steve Tytler' <steve@example.com>, Peter Smith <peter@example.com>
Cc: 'John Wein' <john@example.com>, Peter Smith <peter@example.com>
";
            // End Not Coding Standard
            $from = EmailArchivingUtil::resolveEmailSenderFromEmailMessage($imapMessage);
            $this->assertEquals('john@example.com', $from['email']);
            $this->assertEquals('John Smith', $from['name']);
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveEmailRecipientsFromEmailMessage()
        {
            Yii::app()->imap->imapUsername = 'dropbox@emample.com';
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "test@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com')
            );

            $recipients = EmailArchivingUtil::resolveEmailRecipientsFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->to, $recipients);

            // Check with multiple recipients.
            $imapMessage->to = array(
                array('email' => 'info2@example.com'),
                array('email' => 'info@example.com')
            );
            $recipients = EmailArchivingUtil::resolveEmailRecipientsFromEmailMessage($imapMessage);
            $this->assertEquals($imapMessage->to, $recipients);

            $imapMessage->subject = "FW: Test subject";
            $imapMessage->to[0]['email'] = 'dropbox@emample.com';
            $recipients = EmailArchivingUtil::resolveEmailRecipientsFromEmailMessage($imapMessage);
            $compareData = array(
                               array(
                                   'email' => $imapMessage->fromEmail,
                                   'name' => ''
                               )
                           );
            $this->assertEquals($compareData, $recipients);
        }

        /**
        * @depends testIsMessageForwarded
        */
        public function testResolveOwnerOfEmailMessage()
        {
            $user = UserTestHelper::createBasicUser('billy');
            $email = new Email();
            $email->emailAddress = 'billy@example.com';
            $user->primaryEmail = $email;
            $this->assertTrue($user->save());

            // User send message to dropbox, via additional to field
            // This shouldn't be done in practice, but we need to cover it.
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
                array('email' => 'dropbox@example.com'),
            );
            $owner = EmailArchivingUtil::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // User sent CC copy of his email to dropbox
            // This also shouldn't be done in practice, because email recipient will see dropbox email account,
            // but we need to cover it.
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
            );
            $imapMessage->cc = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = EmailArchivingUtil::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // User sent BCC copy of his email to dropbox
            $imapMessage = new ImapMessage();
            $imapMessage->subject = "Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'info@example.com'),
            );
            $imapMessage->bcc = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = EmailArchivingUtil::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);

            // Forwarded message, user should be in from field
            $imapMessage->subject = "Fwd: Test subject";
            $imapMessage->fromEmail = "billy@example.com";
            $imapMessage->to = array(
                array('email' => 'dropbox@example.com'),
            );
            $owner = EmailArchivingUtil::resolveOwnerOfEmailMessage($imapMessage);
            $this->assertEquals($user->id, $owner->id);
        }

        public function testResolvePersonOrAccountByEmailAddress()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $user = UserTestHelper::createBasicUser('joseph');
            $anotherUser = UserTestHelper::createBasicUser('josephine');

            $emailAddress = 'sameone234@example.com';

            // User can access users, but there are no users in system with the email.
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        false,
                                                                                        true);
            $this->assertNull($personOrAccount);

            // User can access users, and there is user is system with the email.
            Yii::app()->user->userModel = $super;
            $anotherUser->primaryEmail->emailAddress = $emailAddress;
            $this->assertTrue($anotherUser->save());

            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        false,
                                                                                        true);
            $this->assertEquals($anotherUser->id, $personOrAccount->id);
            $this->assertTrue($personOrAccount instanceof User);

            // Now test email with accounts, we will left user email there.
            // User can access accounts, but there are no accounts in system with the email.
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        true,
                                                                                        false);
            $this->assertNull($personOrAccount);

            // User can access accounts, but there are no accounts in system with the email.
            // But there is user is system with the email
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        true,
                                                                                        true);
            $this->assertEquals($anotherUser->id, $personOrAccount->id);
            $this->assertTrue($personOrAccount instanceof User);

            // User can access accounts, and there is account in system with the email.
            // But owner of email is super users, so it shouldn't return account
            Yii::app()->user->userModel = $super;
            $email = new Email();
            $email->emailAddress = $emailAddress;
            $email2 = new Email();
            $email2->emailAddress = 'aabb@example.com';
            $account = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->primaryEmail = $email;
            $account->secondaryEmail = $email2;

            $this->assertTrue($account->save());
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        true,
                                                                                        false);
            $this->assertNull($personOrAccount);
            Yii::app()->user->userModel = $super;
            $account->owner       = $user;
            $this->assertTrue($account->save());
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        true,
                                                                                        false);

            $this->assertEquals($account->id, $personOrAccount->id);
            $this->assertTrue($personOrAccount instanceof Account);

            // Now test with contacts/leads. Please note that we are not removing email address
            // from users and accounts, so if contact or lead exist with this email, they should be returned
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        true,
                                                                                        true,
                                                                                        false,
                                                                                        false);
            $this->assertNull($personOrAccount);

            // User can access contacts, but there are no contact in system with the email.
            // But there is user and account is system with the email
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        false,
                                                                                        false,
                                                                                        true,
                                                                                        true);
            $this->assertEquals($account->id, $personOrAccount->id);
            $this->assertTrue($personOrAccount instanceof Account);

            // User can access contacts, and there is contact in system with the email.
            // But owner of email is super users, so it shouldn't return contact
            Yii::app()->user->userModel = $super;
            $this->assertTrue(ContactsModule::loadStartingData());
            $this->assertEquals(6, count(ContactState::GetAll()));
            $contactStates = ContactState::getByName('Qualified');
            $email = new Email();
            $email->emailAddress = $emailAddress;
            $email2 = new Email();
            $email2->emailAddress = 'aabb@example.com';
            $contact                = new Contact();
            $contact->state         = $contactStates[0];
            $contact->owner         = $super;
            $contact->firstName     = 'Super';
            $contact->lastName      = 'Man';
            $contact->primaryEmail = $email;
            $contact->secondaryEmail = $email;
            $this->assertTrue($account->save());

            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        true,
                                                                                        true,
                                                                                        false,
                                                                                        false);
            $this->assertNull($personOrAccount);

            Yii::app()->user->userModel = $super;
            $contact->owner       = $user;
            $this->assertTrue($contact->save());
            Yii::app()->user->userModel = $user;
            $personOrAccount = EmailArchivingUtil::resolvePersonOrAccountByEmailAddress($emailAddress,
                                                                                        true,
                                                                                        true,
                                                                                        true,
                                                                                        true);
            $this->assertEquals($contact->id, $personOrAccount->id);
            $this->assertTrue($personOrAccount instanceof Contact);
        }

        public function testGetPersonsAndAccountsByEmailAddress()
        {
            //Create user, contact, lead and accout
            $user    = UserTestHelper::createBasicUser('newUser');
            $account = AccountTestHelper::createAccountByNameForOwner('newAccount', $user);
            $lead    = LeadTestHelper::createLeadbyNameForOwner('newLead', $user);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('newContact', $user, $account);
            $user->primaryEmail->emailAddress      = 'useremail@zurmoland.com';
            $lead->primaryEmail->emailAddress      = 'leademail@zurmoland.com';
            $lead->secondaryEmail->emailAddress    = 'leademail2@zurmoland.com';
            $account->primaryEmail->emailAddress   = 'accountemail@zurmoland.com';
            $account->secondaryEmail->emailAddress = 'accountemail2@zurmoland.com';
            $contact->primaryEmail->emailAddress   = 'contactemail@zurmoland.com';
            $contact->secondaryEmail->emailAddress = 'contactemail2@zurmoland.com';
            $user->save();
            $lead->save();
            $account->save();
            $contact->save();

            //Test with defaults
            $emailAddress    = 'useremail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'leademail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'accountemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'contactemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress);
            $this->assertEmpty($personOrAccount);

            //Test user can access contacts
            $emailAddress    = 'contactemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress, true);
            $this->assertNotEmpty($personOrAccount);
            $this->assertEquals($personOrAccount[0], $contact);

            //Test user can access leads
            $emailAddress    = 'leademail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress, false, true);
            $this->assertNotEmpty($personOrAccount);
            $this->assertEquals($personOrAccount[0], $lead);

            //Test user can access accounts
            $emailAddress    = 'accountemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress, false, false, true);
            $this->assertNotEmpty($personOrAccount);
            $this->assertEquals($personOrAccount[0], $account);

            //Test user can access users
            $emailAddress    = 'useremail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddress($emailAddress, false, false, false, true);
            $this->assertNotEmpty($personOrAccount);
            $this->assertEquals($personOrAccount[0], $user);
        }

        /**
         * @depends testGetPersonsAndAccountsByEmailAddress
         */
        public function testGetPersonsAndAccountsByEmailAddressForUser()
        {
            $user = User::getByUsername('newUser');
            //Test user with no access
            $emailAddress    = 'useremail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddressForUser($emailAddress, $user);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'leademail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddressForUser($emailAddress, $user);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'accountemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddressForUser($emailAddress, $user);
            $this->assertEmpty($personOrAccount);
            $emailAddress    = 'contactemail@zurmoland.com';
            $personOrAccount = EmailArchivingUtil::getPersonsAndAccountsByEmailAddressForUser($emailAddress, $user);
            $this->assertEmpty($personOrAccount);
        }
    }
?>
