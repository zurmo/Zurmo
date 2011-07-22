<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class ContactSearchTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
            $loaded = ContactsModule::loadStartingData();
            assert('$loaded'); // Not Coding Standard
            $contactData = array(
                'Sam',
                'Sally',
                'Sarah',
                'Jason',
                'James',
                'Roger'
            );

            $contactStates = ContactState::getAll();
            $lastContactState  = $contactStates[count($contactStates) - 1];

            foreach ($contactData as $key => $firstName)
            {
                $contact = new Contact();
                $contact->title->value = 'Mr';
                $contact->firstName    = $firstName;
                $contact->lastName     = $firstName . 'son';
                $contact->owner        = $user;
                $contact->state        = $lastContactState;
                $contact->primaryEmail = new Email();
                $contact->primaryEmail->emailAddress = $key . '@zurmoland.com';
                $contact->secondaryEmail = new Email();
                $contact->secondaryEmail->emailAddress = 'a' . $key . $firstName . '@zurmoworld.com';
                assert('$contact->save()'); // Not Coding Standard
            }
        }

        public function testGetContactsByPartialFullNameOrAnyEmailAddress()
        {
            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('sa', 5);
            $this->assertEquals(3, count($data));
            $this->assertEquals('Sally', $data[0]->firstName);
            $this->assertEquals('Sam', $data[1]->firstName);
            $this->assertEquals('Sarah', $data[2]->firstName);

            //search by primaryEmail
            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('a4', 5);
            $this->assertEquals(1, count($data));
            $this->assertEquals('James', $data[0]->firstName);

            //search by secondaryEmail
            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('a1sal', 5);
            $this->assertEquals(1, count($data));
            $this->assertEquals('Sally', $data[0]->firstName);
        }

        public function testUsingStateAdapters()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $contactStates = ContactState::getAll();
            $this->assertTrue(count($contactStates) > 1);
            $firstContactState = $contactStates[0];
            $lastContactState  = $contactStates[count($contactStates) - 1];

            $contact = new Contact();
            $contact->title->value = 'Mr';
            $contact->firstName    = 'Sallyy';
            $contact->lastName     = 'Sallyyson';
            $contact->owner        = $super;
            $contact->state        = $firstContactState;
            $contact->primaryEmail = new Email();
            $contact->primaryEmail->emailAddress = 'sally@zurmoland.com';
            $contact->secondaryEmail = new Email();
            $contact->secondaryEmail->emailAddress = 'a19Sallyy@zurmoworld.com';
            $this->assertTrue($contact->save());

            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('sally', 5);
            $this->assertEquals(2, count($data));
            $data = ContactSearch::getContactsByPartialFullName('sally', 5);
            $this->assertEquals(2, count($data));

            //Use contact state adapter
            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('sally', 5, 'ContactsStateMetadataAdapter');
            $this->assertEquals(1, count($data));
            $this->assertEquals($lastContactState, $data[0]->state);
            $data = ContactSearch::getContactsByPartialFullName('sally', 5, 'ContactsStateMetadataAdapter');
            $this->assertEquals(1, count($data));
            $this->assertEquals($lastContactState, $data[0]->state);

            //Use lead state adapter
            $data = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress('sally', 5, 'LeadsStateMetadataAdapter');
            $this->assertEquals(1, count($data));
            $this->assertEquals($firstContactState, $data[0]->state);
            $data = ContactSearch::getContactsByPartialFullName('sally', 5, 'LeadsStateMetadataAdapter');
            $this->assertEquals(1, count($data));
            $this->assertEquals($firstContactState, $data[0]->state);
        }
    }
?>
