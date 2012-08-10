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

    class ArchivedEmailMatchingUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            $super->primaryEmail->emailAddress = 'super@supertest.com';
            if (!$super->save())
            {
                throw new NotSupportedException();
            }
        }

        public function testResolveEmailAddressAndNameToContact()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $message1                   = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $contact                    = new Contact();
            $this->assertNull($contact->primaryEmail->emailAddress);
            $this->assertNull($contact->firstName);
            $this->assertNull($contact->lastName);
            ArchivedEmailMatchingUtil::resolveEmailAddressAndNameToContact($message1, $contact);
            $this->assertEquals('bob.message@zurmotest.com', $contact->primaryEmail->emailAddress);
            $this->assertEquals('Bobby',                     $contact->firstName);
            $this->assertEquals('Bobson',                    $contact->lastName);

            $message2                   = EmailMessageTestHelper::createArchivedUnmatchedSentMessage($super);
            $contact                    = new Contact();
            $this->assertNull($contact->primaryEmail->emailAddress);
            $this->assertNull($contact->firstName);
            $this->assertNull($contact->lastName);
            ArchivedEmailMatchingUtil::resolveEmailAddressAndNameToContact($message2, $contact);
            $this->assertEquals('bob.message@zurmotest.com', $contact->primaryEmail->emailAddress);
            $this->assertEquals('Bobby',                     $contact->firstName);
            $this->assertEquals('Bobson',                    $contact->lastName);
        }

        /**
         * @depends testResolveEmailAddressAndNameToContact
         */
        public function testResolveEmailAddressToContactIfEmailRelationAvailableForReceivedMessage()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $message1                   = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $contact                    = new Contact();
            $this->assertNull($contact->primaryEmail->emailAddress);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($message1, $contact);
            $this->assertEquals('bob.message@zurmotest.com', $contact->primaryEmail->emailAddress);
            $this->assertNull($contact->secondaryEmail->emailAddress);

            //Test placing in secondary address from recipient
            $contact                             = new Contact();
            $contact->primaryEmail->emailAddress = 'someaddress@test.com';
            $this->assertNull($contact->secondaryEmail->emailAddress);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($message1, $contact);
            $this->assertEquals('someaddress@test.com',      $contact->primaryEmail->emailAddress);
            $this->assertEquals('bob.message@zurmotest.com', $contact->secondaryEmail->emailAddress);
        }

        /**
         * @depends testResolveEmailAddressToContactIfEmailRelationAvailableForReceivedMessage
         */
        public function testResolveEmailAddressToContactIfEmailRelationAvailableForSentMessage()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $message1                   = EmailMessageTestHelper::createArchivedUnmatchedSentMessage($super);
            $contact                    = new Contact();
            $this->assertNull($contact->primaryEmail->emailAddress);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($message1, $contact);
            $this->assertEquals('bob.message@zurmotest.com', $contact->primaryEmail->emailAddress);
            $this->assertNull($contact->secondaryEmail->emailAddress);

            //Test placing in secondary address from recipient
            $contact                             = new Contact();
            $contact->primaryEmail->emailAddress = 'someaddress@test.com';
            $this->assertNull($contact->secondaryEmail->emailAddress);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            ArchivedEmailMatchingUtil::resolveEmailAddressToContactIfEmailRelationAvailable($message1, $contact);
            $this->assertEquals('someaddress@test.com',      $contact->primaryEmail->emailAddress);
            $this->assertEquals('bob.message@zurmotest.com', $contact->secondaryEmail->emailAddress);
        }

        /**
         * @depends testResolveEmailAddressToContactIfEmailRelationAvailableForSentMessage
         */
        public function testResolveContactToSenderOrRecipient()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $message1                   = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $contact                    = new Contact();
            $this->assertTrue($message1->sender->personOrAccount->id < 0);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            $this->assertTrue($message1->recipients->count() == 1);
            $this->assertTrue($message1->recipients->offsetGet(0)->personOrAccount->isSame($super));
            $this->assertTrue($message1->sender->personOrAccount->isSame($contact));

            $message1                   = EmailMessageTestHelper::createArchivedUnmatchedSentMessage($super);
            $contact                    = new Contact();
            $this->assertTrue($message1->recipients->count() == 1);
            $this->assertTrue($message1->recipients->offsetGet(0)->personOrAccount->id < 0);
            ArchivedEmailMatchingUtil::resolveContactToSenderOrRecipient($message1, $contact);
            $this->assertTrue($message1->sender->personOrAccount->isSame($super));
            $this->assertTrue($message1->recipients->count() == 1);
            $this->assertTrue($message1->recipients->offsetGet(0)->personOrAccount->isSame($contact));
        }
    }
?>