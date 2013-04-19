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

    /**
     * Testing the views for and controller actions for matching unmatched archived emails
     */
    class ArchivedEmailMatchingUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            ReadPermissionsOptimizationUtil::rebuild();
            $super->primaryEmail->emailAddress = 'super@supertest.com';
            if (!$super->save())
            {
                throw new NotSupportedException();
            }
            $nobody = UserTestHelper::createBasicUser('nobody');
            $nobody->primaryEmail->emailAddress = 'nobody@supertest.com';
            if (!$nobody->save())
            {
                throw new NotSupportedException();
            }
            $userCanDelete = UserTestHelper::createBasicUser('usercandelete');
            $userCanDelete->primaryEmail->emailAddress = 'usercandelete@supertest.com';
            if (!$userCanDelete->save())
            {
                throw new NotSupportedException();
            }
            ContactsModule::loadStartingData();
        }

        public function testSuperUserCompleteMatchVariations()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');
            $message1             = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $message2             = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $message3             = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($super);
            $contact              = ContactTestHelper::createContactByNameForOwner('gail', $super);
            $startingContactState = ContactsUtil::getStartingState();
            $startingLeadState    = LeadsUtil::getStartingState();
            //test validating selecting an existing contact
            $this->setGetArray(array('id' => $message1->id));
            $this->setPostArray(array('ajax' => 'select-contact-form-' . $message1->id,
                                    'AnyContactSelectForm' => array($message1->id => array(
                                    'contactId'   => $contact->id,
                                    'contactName' => 'Some Name'))));
            $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/completeMatch');

            //test validating creating a new contact
            $this->setGetArray(array('id' => $message1->id));
            $this->setPostArray(array('ajax' => 'contact-inline-create-form-' . $message1->id,
                                    'Contact' => array($message1->id => array(
                                    'firstName'         => 'Gail',
                                    'lastName'          => 'Green',
                                    'officePhone'       => '456765421',
                                    'state'             => array('id' => $startingContactState->id)))));
            $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/completeMatch');

            //test validating creating a new lead
            $this->setGetArray(array('id' => $message1->id));
            $this->setPostArray(array('ajax' => 'lead-inline-create-form-' . $message1->id,
                                    'Lead' => array($message1->id => array(
                                    'firstName'         => 'Gail',
                                    'lastName'          => 'Green',
                                    'officePhone'       => '456765421',
                                    'state'             => array('id' => $startingLeadState->id)))));
            $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/completeMatch');

            //test selecting existing contact and saving
            $this->assertNull($contact->primaryEmail->emailAddress);
            $this->setGetArray(array('id' => $message1->id));
            $this->setPostArray(array('AnyContactSelectForm' => array($message1->id => array(
                                            'contactId'   => $contact->id,
                                            'contactName' => 'Some Name'))));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/completeMatch', true);
            $this->assertEquals('bob.message@zurmotest.com', $contact->primaryEmail->emailAddress);
            $this->assertTrue($message1->sender->personOrAccount->isSame($contact));
            $this->assertEquals('Archived', $message1->folder);

            //test creating new contact and saving
            $this->assertEquals(1, Contact::getCount());
            $this->setGetArray(array('id' => $message2->id));
            $this->setPostArray(array('Contact' => array($message2->id => array(
                                        'firstName'         => 'George',
                                        'lastName'          => 'Patton',
                                        'officePhone'       => '456765421',
                                        'state'             => array('id' => $startingContactState->id)))));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/completeMatch', true);
            $this->assertEquals(2, Contact::getCount());
            $contacts = Contact::getByName('George Patton');
            $contact  = $contacts[0];
            $this->assertTrue($message2->sender->personOrAccount->isSame($contact));
            $this->assertEquals('Archived', $message2->folder);

            //test creating new lead and saving
            $this->assertEquals(2, Contact::getCount());
            $this->setGetArray(array('id' => $message3->id));
            $this->setPostArray(array('Lead' => array($message3->id => array(
                                        'firstName'         => 'Billy',
                                        'lastName'          => 'Kid',
                                        'officePhone'       => '456765421',
                                        'state'             => array('id' => $startingLeadState->id)))));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/completeMatch', true);
            $this->assertEquals(3, Contact::getCount());
            $contacts = Contact::getByName('Billy Kid');
            $contact  = $contacts[0];
            $this->assertTrue($message3->sender->personOrAccount->isSame($contact));
            $this->assertEquals('Archived', $message3->folder);
        }

        /**
         * @depends testSuperUserCompleteMatchVariations
         */
        public function testProgressiveUserRightsOnARegularUser()
        {
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $nobody->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_ACCESS_EMAIL_MESSAGES);
            $this->assertTrue($nobody->save());

            $message1 = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($nobody);
            $message2 = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($nobody);
            $message3 = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($nobody);

            //First check accessing where nobody can access
            $content = $this->runControllerWithExitExceptionAndGetContent('emailMessages/default/matchingList');
            $failureMessage = Zurmo::t('EmailMessagesModule', 'Matching archived emails requires access to either ContactsModulePluralLowerCaseLabel' .
                ' or LeadsModulePluralLowerCaseLabel both of which you do not have. Please contact your administrator.',
                LabelUtil::getTranslationParamsForAllModules());
            $this->assertContains($failureMessage, $content);

            $this->setGetArray(array('id' => $message1->id));
            $this->runControllerWithNotSupportedExceptionAndGetContent ('emailMessages/default/completeMatch');

            //Just access to leads
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');

            //Just access to contacts
            $nobody->removeRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');

            //Access to both leads and contacts
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');

            //Access to both leads and contacts, but can only create leads
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_CREATE_LEADS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');

            //Access to both leads and contacts, but can only create contacts
            $nobody->removeRight('LeadsModule', LeadsModule::RIGHT_CREATE_LEADS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');

            //Access to both leads and contacts, and can create both leads and contacts
            $nobody->setRight('LeadsModule', LeadsModule::RIGHT_CREATE_LEADS);
            $this->assertTrue($nobody->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/matchingList');
        }

        public function testDeleteAction()
        {
            $userCanDelete = $this->logoutCurrentUserLoginNewUserAndGetByUsername('usercandelete');
            $userCanDelete->setRight('EmailMessagesModule', EmailMessagesModule::RIGHT_DELETE_EMAIL_MESSAGES);
            $this->assertTrue($userCanDelete->save());
            $userCanDelete->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $userCanDelete->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $userCanDelete->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $contact              = ContactTestHelper::createContactByNameForOwner('gail', $userCanDelete);
            $startingContactState = ContactsUtil::getStartingState();
            $startingLeadState    = LeadsUtil::getStartingState();
            $message1 = EmailMessageTestHelper::createArchivedUnmatchedReceivedMessage($userCanDelete);
            $this->setGetArray(array('id' => $message1->id));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/delete', true);
       }
    }
?>