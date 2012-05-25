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

    class ContactTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateSourceValues()
        {
            $sourceValues = array(
                'Word of Mouth',
                'Outbound',
                'Trade Show',
            );
            $sourceFieldData = CustomFieldData::getByName('LeadSources');
            $sourceFieldData->serializedData = serialize($sourceValues);
            $this->assertTrue($sourceFieldData->save());
        }

        /**
         * @depends testCreateSourceValues
         */
        public function testCreateStateValues()
        {
            $this->assertTrue(ContactsModule::loadStartingData());
            $this->assertEquals(6, count(ContactState::GetAll()));
        }

        /**
         * @depends testCreateStateValues
         */
        public function testCreateAndGetContactById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Steven');

            $account = new Account();
            $account->name  = 'Some Account';
            $account->owner = $user;
            $this->assertTrue($account->save());

            $contactStates = ContactState::getByName('Qualified');

            $contact = new Contact();
            $contact->owner         = $user;
            $contact->title->value  = 'Mr.';
            $contact->firstName     = 'Super';
            $contact->lastName      = 'Man';
            $contact->jobTitle      = 'Superhero';
            $contact->source->value = 'Outbound';
            $contact->account       = $account;
            $contact->description   = 'Some Description';
            $contact->department    = 'Red Tape';
            $contact->officePhone   = '1234567890';
            $contact->mobilePhone   = '0987654321';
            $contact->officeFax     = '1222222222';
            $contact->state         = $contactStates[0];
            $this->assertTrue($contact->save());
            $id = $contact->id;
            unset($contact);

            $contact = Contact::getById($id);
            $this->assertEquals('Super',            $contact->firstName);
            $this->assertEquals('Man',              $contact->lastName);
            $this->assertEquals('Mr.',               $contact->title->value);
            $this->assertEquals('Superhero',        $contact->jobTitle);
            $this->assertEquals('Outbound',         $contact->source->value);
            $this->assertEquals($account->id,       $contact->account->id);
            $this->assertEquals($user->id,          $contact->owner->id);
            $this->assertEquals('Some Description', $contact->description);
            $this->assertEquals('Red Tape',         $contact->department);
            $this->assertEquals('1234567890',       $contact->officePhone);
            $this->assertEquals('0987654321',       $contact->mobilePhone);
            $this->assertEquals('1222222222',       $contact->officeFax);
            $this->assertEquals('Qualified',        $contact->state->name);
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testEmailAndAddresses()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $contacts = Contact::getAll();
            $this->assertTrue(count($contacts) > 0);
            $contact = $contacts[0];
            $contact->primaryEmail->emailAddress   = 'thejman@zurmoinc.com';
            $contact->primaryEmail->optOut         = 0;
            $contact->primaryEmail->isInvalid      = 0;
            $contact->secondaryEmail->emailAddress = 'digi@magic.net';
            $contact->secondaryEmail->optOut       = 1;
            $contact->secondaryEmail->isInvalid    = 1;
            $contact->primaryAddress->street1      = '129 Noodle Boulevard';
            $contact->primaryAddress->street2      = 'Apartment 6000A';
            $contact->primaryAddress->city         = 'Noodleville';
            $contact->primaryAddress->postalCode   = '23453';
            $contact->primaryAddress->country      = 'The Good Old US of A';
            $contact->secondaryAddress->street1    = '25 de Agosto 2543';
            $contact->secondaryAddress->street2    = 'Local 3';
            $contact->secondaryAddress->city       = 'Ciudad de Los Fideos';
            $contact->secondaryAddress->postalCode = '5123-4';
            $contact->secondaryAddress->country    = 'Latinoland';
            $this->assertTrue($contact->save(false));
            $id = $contact->id;
            unset($contact);
            $contact = Contact::getById($id);
            $this->assertEquals('thejman@zurmoinc.com', $contact->primaryEmail->emailAddress);
            $this->assertEquals(0,                          $contact->primaryEmail->optOut);
            $this->assertEquals(0,                          $contact->primaryEmail->isInvalid);
            $this->assertEquals('digi@magic.net',           $contact->secondaryEmail->emailAddress);
            $this->assertEquals(1,                          $contact->secondaryEmail->optOut);
            $this->assertEquals(1,                          $contact->secondaryEmail->isInvalid);
            $this->assertEquals('129 Noodle Boulevard',     $contact->primaryAddress->street1);
            $this->assertEquals('Apartment 6000A',          $contact->primaryAddress->street2);
            $this->assertEquals('Noodleville',              $contact->primaryAddress->city);
            $this->assertEquals('23453',                    $contact->primaryAddress->postalCode);
            $this->assertEquals('The Good Old US of A',     $contact->primaryAddress->country);
            $this->assertEquals('25 de Agosto 2543',        $contact->secondaryAddress->street1);
            $this->assertEquals('Local 3',                  $contact->secondaryAddress->street2);
            $this->assertEquals('Ciudad de Los Fideos',     $contact->secondaryAddress->city);
            $this->assertEquals('5123-4',                   $contact->secondaryAddress->postalCode);
            $this->assertEquals('Latinoland',               $contact->secondaryAddress->country);
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testGetContactsByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $this->assertEquals('Super Man', strval($contacts[0]));
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $this->assertEquals('Contact',  $contacts[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Contacts', $contacts[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetContactsByName
         */
        public function testGetContactsByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contacts = Contact::getByName('I dont exist');
            $this->assertEquals(0, count($contacts));
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('steven');

            $contactStates = ContactState::getByName('Qualified');

            $contact = new Contact();
            $contact->owner     = $user;
            $contact->firstName = 'Super';
            $contact->lastName  = 'Woman';
            $contact->state     = $contactStates[0];
            $this->assertTrue($contact->save());
            $contacts = Contact::getAll();
            $this->assertEquals(2, count($contacts));
            $this->assertTrue('Super Man'   == strval($contacts[0]) &&
                              'Super Woman' == strval($contacts[1]) ||
                              'Super Woman' == strval($contacts[0]) &&
                              'Super Man'   == strval($contacts[1]));
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testSetAndGetOwner()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = UserTestHelper::createBasicUser('Billy');

            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $contact = $contacts[0];
            $contact->owner = $user;
            $this->assertTrue($contact->save());
            unset($user);
            $this->assertTrue($contact->owner !== null);
            $user = $contact->owner;
            $contact->owner = null;
            $this->assertFalse($contact->validate());
            $contact->forget();
            unset($contact);
        }

        /**
         * @depends testSetAndGetOwner
         */
        public function testReplaceOwnerUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $contact = $contacts[0];
            $user = User::getByUsername('billy');
            $this->assertEquals($user->id, $contact->owner->id);
            unset($user);
            $user2 = UserTestHelper::createBasicUser('Benny');
            $contact->owner = $user2;
            unset($user2);
            $this->assertTrue($contact->owner !== null);
            $user = $contact->owner;
            $this->assertEquals('benny', $user->username);
            unset($user);
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testUpdateContactFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('steven');

            $contacts = Contact::getByName('Super Man');
            $contact = $contacts[0];
            $this->assertEquals(strval($contact), 'Super Man');
            $postData = array(
                'owner' => array(
                    'id' => $user->id
                ),
                'firstName' => 'New',
                'lastName'  => 'Name',
            );
            $contact->setAttributes($postData);
            $this->assertTrue($contact->save());

            $id = $contact->id;
            unset($contact);
            $contact = Contact::getById($id);
            $this->assertEquals('New Name', strval($contact));
        }

        /**
         * @depends testGetAll
         */
        public function testDeleteContact()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $contacts = Contact::getAll();
            $this->assertEquals(2, count($contacts));
            $contacts[0]->delete();
            $contacts = Contact::getAll();
            $this->assertEquals(1, count($contacts));
            $contacts[0]->delete();
            $contacts = Contact::getAll();
            $this->assertEquals(0, count($contacts));
        }

        /**
         * @depends testDeleteContact
         */
        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contacts = Contact::getAll();
            $this->assertEquals(0, count($contacts));
        }

        /**
         * @depends testCreateStateValues
         */
        public function testSetAndGetStartingState()
        {
            $startingStateId = ContactsUtil::getStartingState()->id;
            $contactStates = ContactState::getAll();
            foreach ($contactStates as $contactState)
            {
                if ($contactState->id != $startingStateId)
                {
                    $otherStateId = $contactState->id;
                    break;
                }
            }
            $metadata = ContactsModule::getMetadata();
            $this->assertEquals($startingStateId, $metadata['global']['startingStateId']);
            ContactsUtil::setStartingStateById($otherStateId);
            $metadata = ContactsModule::getMetadata();
            $this->assertEquals($otherStateId, $metadata['global']['startingStateId']);
        }

        /**
         * @depends testCreateAndGetContactById
         */
        public function testCreateContactFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $states = ContactState::GetAll();
            $user = User::getByUsername('billy');
            $contact = new Contact();
            $postData = array(
                'firstName' => 'Something',
                'lastName' => 'NewAndExciting',
                'owner' => array(
                    'id' => $user->id
                ),
                'state'    => array(
                    'id' => $states[0]->id,
                )
            );
            $contact->setAttributes($postData);
            $this->assertTrue($contact->save());
            $id = $contact->id;
            unset($contact);
            $contact = Contact::getById($id);
            $this->assertEquals('Something NewAndExciting', strval($contact));
            $this->assertEquals($user->id, $contact->owner->id);
            $this->assertEquals($states[0]->id, $contact->state->id);
        }

        /**
         * @depends testSetAndGetStartingState
         */
        public function testContactsStateMetadataAdapter()
        {
            $this->assertEquals(6, count(ContactState::GetAll()));
            $metadata = ContactsModule::getMetadata();
            $startingStateId = ContactsUtil::getStartingState()->id;
            $this->assertEquals($startingStateId, $metadata['global']['startingStateId']);
            $statesToInclude = ContactsUtil::getContactStateDataFromStartingStateOnAndKeyedById();
            $this->assertEquals(6, count($statesToInclude));
            $metadata = array('clauses' => array(), 'structure' => '');
            $adapter = new ContactsStateMetadataAdapter($metadata);
            $adaptedMetadata = $adapter->getAdaptedDataProviderMetadata();
            $compareMetadata['clauses'] = array();
            $compareMetadata['structure'] = null;
            $index = 1;
            foreach ($statesToInclude as $stateId => $notUsed)
            {
                $compareMetadata['clauses'][$index] = array(
                        'attributeName' => 'state',
                        'operatorType' => 'equals',
                        'value' => $stateId
                );
                $index++;
            }
            $compareMetadata['structure'] = '(1 or 2 or 3 or 4 or 5 or 6)';
            $this->assertEquals($compareMetadata, $adaptedMetadata);

            $metadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                    2 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'city',
                        'operatorType' => 'startsWith',
                        'value' => 'Chicago'
                    ),
                ),
                'structure' => '1 and 2',
            );
            $adapter = new ContactsStateMetadataAdapter($metadata);
            $adaptedMetadata = $adapter->getAdaptedDataProviderMetadata();
            $compareMetadata = array(
                'clauses' => array(
                    1 => array(
                        'attributeName' => 'name',
                        'operatorType' => 'equals',
                        'value' => 'Vomo'
                    ),
                    2 => array(
                        'attributeName' => 'billingAddress',
                        'relatedAttributeName' => 'city',
                        'operatorType' => 'startsWith',
                        'value' => 'Chicago'
                    ),
                ),
                'structure' => '(1 and 2) and (3 or 4 or 5 or 6 or 7 or 8)',
            );
            $index = 3;
            foreach ($statesToInclude as $stateId => $notUsed)
            {
                $compareMetadata['clauses'][$index] = array(
                        'attributeName' => 'state',
                        'operatorType' => 'equals',
                        'value' => $stateId
                );
                $index++;
            }
            $this->assertEquals($compareMetadata, $adaptedMetadata);
        }

        public function testDeleteContactCascadesToDeleteEverythingItShould()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertEquals(6, count(ContactState::GetAll()));
            $manager = UserTestHelper::createBasicUser('Godzilla');
            $this->assertTrue($manager->save());

            $account = new Account();
            $account->name = 'Os Drogas Mais Legais';
            $this->assertTrue($account->save());

            $contact = new Contact();
            $contact->title->value                 = 'Senhor';
            $contact->firstName                    = 'José';
            $contact->lastName                     = 'Olivereira';
            $contact->jobTitle                     = 'Traficante';
            $contact->primaryAddress->street1      = 'R. das Mulheres, 69';
            $contact->primaryAddress->street2      = '';
            $contact->primaryAddress->city         = 'Centro';
            $contact->primaryAddress->state        = 'RJ';
            $contact->primaryAddress->postalCode   = '';
            $contact->primaryAddress->country      = 'Brasil';
            $contact->primaryEmail->emailAddress   = 'jose@gmail.com';
            $contact->primaryEmail->optOut         = 1;
            $contact->primaryEmail->isInvalid      = 0;
            $contact->secondaryAddress->street1    = 'Passagem do Comando Vermelho';
            $contact->secondaryAddress->street2    = '';
            $contact->secondaryAddress->city       = 'Complexo do Alemão';
            $contact->secondaryAddress->state      = 'RJ';
            $contact->secondaryAddress->postalCode = '';
            $contact->secondaryAddress->country    = 'Brasil';
            $contact->account                      = $account;
            $contact->state->name                  = 'Novo Hermão';
            $contact->state->order                 = 6;
            $this->assertTrue($contact->save());

            $titleId            = $contact->title           ->id;
            $primaryAddressId   = $contact->primaryAddress  ->id;
            $primaryEmailId     = $contact->primaryEmail    ->id;
            $secondaryAddressId = $contact->secondaryAddress->id;
            $accountId          = $account                  ->id;
            $stateId            = $contact->state           ->id;

            $this->assertEquals(7, count(ContactState::GetAll())); //new state created. Confirm this
            $contact->delete();
            unset($contact);
            unset($manager);
            unset($account);
            User::getByUsername('godzilla');
            Account::getById($accountId);
            ContactState::getById($stateId);

            try
            {
                CustomField::getById($titleId);
                $this->fail("Title should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }

            try
            {
                Address::getById($primaryAddressId);
                $this->fail("Primary address should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }

            try
            {
                Email::getById($primaryEmailId);
                $this->fail("Primary email should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }

            try
            {
                Address::getById($secondaryAddressId);
                $this->fail("Secondary address should have been deleted.");
            }
            catch (NotFoundException $e)
            {
            }
        }

        public function testContactStateAdapterReturnsCorrectStatesUponStartingStateChange()
        {
            $this->assertEquals(7, count(ContactState::GetAll()));
            $metadata = ContactsModule::getMetadata();
            $metadata['global']['startingStateId'] = ContactsUtil::getStartingState()->id;
            ContactsModule::setMetadata($metadata);
            $metadata = array('clauses' => array(), 'structure' => '');
            $adapter = new ContactsStateMetadataAdapter($metadata);
            $adaptedMetadata = $adapter->getAdaptedDataProviderMetadata();
            $statesToInclude = ContactsUtil::getContactStateDataFromStartingStateOnAndKeyedById();
            $this->assertEquals(7, count($statesToInclude));
            $compareMetadata['clauses'] = array();
            $compareMetadata['structure'] = '(1 or 2 or 3 or 4 or 5 or 6 or 7)';
            $index = 1;
            foreach ($statesToInclude as $stateId => $notUsed)
            {
                $compareMetadata['clauses'][$index] = array(
                        'attributeName' => 'state',
                        'operatorType' => 'equals',
                        'value' => $stateId
                );
                $index++;
            }
            $this->assertEquals($compareMetadata, $adaptedMetadata);
        }

        /**
         * @depends testCreateStateValues
         */
        public function testSaveContactFromPostWithoutAccount()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            //Save contact without account.
            $startingState = ContactsUtil::getStartingState();
            $contacts = Contact::getByName('jilly simpson');
            $this->assertEmpty($contacts);
            $fakePostData = array(
                'firstName' => 'jilly',
                'lastName' => 'simpson',
                'account'     => array('id' => ''),
                'state'     => array('id' => $startingState->id),
            );
            $contact = new Contact();
            $contact->setAttributes($fakePostData);
            $saved = $contact->save();
            $this->assertTrue($saved);
            $contactId = $contact->id;
            $contact->forget();

            //Now try to make a change to that contact.  Still no account.
            $fakePostData = array(
                'firstName'   => 'jilly',
                'lastName'    => 'simpson',
                'officePhone' => '12345',
                'account'     => array('id' => ''),
                'state'       => array('id' => $startingState->id),
            );
            $contact = Contact::getById($contactId);
            $contact->setAttributes($fakePostData);
            $saved = $contact->save();
            $this->assertTrue($saved);

            //Create a contact not through post without an account.
            $contact   = ContactTestHelper::createContactByNameForOwner('shozin', Yii::app()->user->userModel);
            $contactId = $contact->id;
            $contact->forget();

            //Now try to make a change to that contact via post.  Still no account.
            $fakePostData = array(
                'firstName'   => 'shozin',
                'lastName'    => 'shozinson',
                'officePhone' => '12345',
                'account'     => array('id' => ''),
                'state'       => array('id' => $startingState->id),
            );
            $contact = Contact::getById($contactId);
            $contact->setAttributes($fakePostData);
            $saved = $contact->save();
            $this->assertTrue($saved);
        }

        public function testContactsUtilGetContactStateDataKeyedByOrder()
        {
            $contactStatesData = ContactsUtil::getContactStateDataKeyedByOrder();
            $compareData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Dead',
                4 => 'Qualified',
                5 => 'Customer',
                6 => 'Novo Hermão',
            );
            $this->assertEquals($compareData, $contactStatesData);
        }

        public function testContactsUtilGetAndSetStartingStateById()
        {
            $expectedStartingStateId = ContactsUtil::getStartingState()->id;
            $contactStates = ContactState::getAll();
            foreach ($contactStates as $contactState)
            {
                if ($contactState->id != $expectedStartingStateId)
                {
                    $otherStateId = $contactState->id;
                    break;
                }
            }
            $startingStateId = ContactsUtil::getStartingStateId();
            $this->assertEquals($expectedStartingStateId, $startingStateId);
            ContactsUtil::setStartingStateById($otherStateId);
            $startingStateId = ContactsUtil::getStartingStateId();
            $this->assertEquals($otherStateId, $startingStateId);
        }

        public function testContactsUtilSetStartingStateByOrder()
        {
            $startingStateId = ContactsUtil::getStartingStateId();
            $startingState = ContactState::getById($startingStateId);
            $startingState->delete();
            $this->assertEquals(6, count(ContactState::GetAll()));
            ContactsUtil::setStartingStateByOrder(2);
            $startingStateId = ContactsUtil::getStartingStateId();
            $states = ContactState::getAll('order');
            $this->assertEquals($states[1]->id, $startingStateId);
            $startingState = ContactState::getByName('Recycled');
            $this->assertEquals(1, count($startingState));
            $this->assertEquals($startingState[0]->id, $startingStateId);
        }

        public function testContactsUtilGetContactStateDataKeyedById()
        {
            $contactStatesData = ContactsUtil::getContactStateDataKeyedById();
            $state1 = ContactState::getByName('New');
            $state3 = ContactState::getByName('Recycled');
            $state4 = ContactState::getByName('Dead');
            $state5 = ContactState::getByName('Qualified');
            $state6 = ContactState::getByName('Customer');
            $state7 = ContactState::getByName('Novo Hermão');
            $compareData = array(
                $state1[0]->id => 'New',
                $state3[0]->id => 'Recycled',
                $state4[0]->id => 'Dead',
                $state5[0]->id => 'Qualified',
                $state6[0]->id => 'Customer',
                $state7[0]->id => 'Novo Hermão',
            );
            $this->assertEquals($compareData, $contactStatesData);
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = ContactsModule::getModelClassNames();
            $this->assertEquals(4, count($modelClassNames));
            $this->assertEquals('Contact', $modelClassNames[0]);
            $this->assertEquals('ContactSearch', $modelClassNames[1]);
            $this->assertEquals('ContactState', $modelClassNames[2]);
            $this->assertEquals('ContactsFilteredList', $modelClassNames[3]);
        }

        public function testChangingContactWithoutChangingRelatedAccountShouldNotAuditAccountChangeWhenDoneViaPost()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contactStates              = ContactState::getByName('Qualified');

            $contact = new Contact();
            $contact->owner         = Yii::app()->user->userModel;
            $contact->title->value  = 'Mr.';
            $contact->firstName     = 'Supero';
            $contact->lastName      = 'Mano';
            $contact->state         = $contactStates[0];
            $this->assertTrue($contact->save());
            $beforeCount = AuditEvent::getCount();

            //Test that saving an existing contact without a related contact will not produce an audit event showing the
            //related account has changed.  This is a test to show when the account is not populated but has a negative
            //id.
            $contactId = $contact->id;
            $contact->forget();
            unset($contact);
            $contact   = Contact::getById($contactId);
            $fakePostData = array('account' => array('id' => ''));
            $contact->setAttributes($fakePostData);
            $this->assertTrue($contact->save());
            $this->assertEquals($beforeCount, AuditEvent::getCount());
        }
    }
?>
