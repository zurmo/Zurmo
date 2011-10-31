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

    class LeadTest extends BaseTest
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
        public function testGetSetConvertToAccountSetting()
        {
            $this->assertEquals(2, LeadsModule::getConvertToAccountSetting());
            $this->assertEquals(2, LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED);
            $metadata = LeadsModule::getMetadata();
            $metadata['global']['convertToAccountSetting'] = LeadsModule::CONVERT_ACCOUNT_REQUIRED;
            LeadsModule::setMetadata($metadata);
            $this->assertEquals(3, LeadsModule::getConvertToAccountSetting());
        }

        /**
         * @depends testCreateStateValues
         */
        public function testLeadsStateMetadataAdapter()
        {
            $this->assertEquals(6, count(ContactState::GetAll()));
            $metadata = ContactsModule::getMetadata();
            $this->assertEquals(ContactsUtil::getStartingStateId(), $metadata['global']['startingStateId']);
            $metadata = array('clauses' => array(), 'structure' => '');
            $adapter = new LeadsStateMetadataAdapter($metadata);
            $adaptedMetadata = $adapter->getAdaptedDataProviderMetadata();

            $statesToInclude = LeadsUtil::getLeadStateDataFromStartingStateOnAndKeyedById();
            $this->assertEquals(4, count($statesToInclude));
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
            $compareMetadata['structure'] = '(1 or 2 or 3 or 4)';
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
            $adapter = new LeadsStateMetadataAdapter($metadata);
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
                'structure' => '(1 and 2) and (3 or 4 or 5 or 6)',
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

        /**
         * @depends testCreateStateValues
         */
        public function testAttributesToAccount()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $industries = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industries);
            $this->assertTrue($industryFieldData->save());

            $user = UserTestHelper::createBasicUser('Bobby');

            $contact = new Contact();
            $contact->owner           = $user;
            $contact->title->value    = 'Mr.';
            $contact->firstName       = 'Super';
            $contact->lastName        = 'Man';
            $contact->companyName     = 'ABC Company';
            $stateIds = ContactsUtil::getContactStateDataKeyedById();
            foreach ($stateIds as $stateId => $notUsed)
            {
                $stateToUse = ContactState::getById($stateId);
                break;
            }
            $contact->state           = $stateToUse; //grab first state.
            $contact->officePhone     = '1234567890';
            $contact->officeFax       = '1222222222';
            $contact->industry->value = $industries[1];
            $contact->website         = 'http://www.something.com';
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
            $this->assertTrue($contact->save());
            $id = $contact->id;
            unset($contact);
            $contact = Contact::getById($id);
            $account = new Account();
            $account = LeadsUtil::AttributesToAccount($contact, $account);
            $this->assertTrue($account->save());
            $id = $account->id;
            unset($account);
            $account = Account::getById($id);

            $this->assertEquals('ABC Company',              $account->name);
            $this->assertEquals('1234567890',               $account->officePhone);
            $this->assertEquals('1222222222',               $account->officeFax);
            $this->assertEquals('http://www.something.com', $account->website);
            $this->assertEquals($industries[1],             $account->industry->value);
            $this->assertEquals('bobby',                    $account->owner->username);
            $this->assertEquals('129 Noodle Boulevard',     $account->billingAddress->street1);
            $this->assertEquals('Apartment 6000A',          $account->billingAddress->street2);
            $this->assertEquals('Noodleville',              $account->billingAddress->city);
            $this->assertEquals('23453',                    $account->billingAddress->postalCode);
            $this->assertEquals('The Good Old US of A',     $account->billingAddress->country);
            $this->assertEquals('25 de Agosto 2543',        $account->shippingAddress->street1);
            $this->assertEquals('Local 3',                  $account->shippingAddress->street2);
            $this->assertEquals('Ciudad de Los Fideos',     $account->shippingAddress->city);
            $this->assertEquals('5123-4',                   $account->shippingAddress->postalCode);
            $this->assertEquals('Latinoland',               $account->shippingAddress->country);
        }

        /**
         * @depends testAttributesToAccount
         */
        public function testAttributesToAccountWithNoPostData()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $postData = array(
                'name' => '',
            );
            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $contact = $contacts[0];
            $this->assertEquals('ABC Company', $contact->companyName);
            $this->assertEquals('1234567890', $contact->officePhone);
            $account = new Account();
            $this->assertEmpty($account->name);
            $this->assertEmpty($account->officePhone);
            $account = LeadsUtil::AttributesToAccountWithNoPostData($contact, $account, $postData);
            $this->assertEquals('1234567890', $account->officePhone);
            $this->assertEquals(null, $account->name);

            $postData = array(
            );
            $contacts = Contact::getByName('Super Man');
            $this->assertEquals(1, count($contacts));
            $contact = $contacts[0];
            $this->assertEquals('ABC Company', $contact->companyName);
            $this->assertEquals('1234567890', $contact->officePhone);
            $account = new Account();
            $this->assertEmpty($account->name);
            $this->assertEmpty($account->officePhone);
            $account = LeadsUtil::AttributesToAccountWithNoPostData($contact, $account, $postData);
            $this->assertEquals('1234567890', $account->officePhone);
            $this->assertEquals('ABC Company', $account->name);
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = LeadsModule::getModelClassNames();
            $this->assertEquals(1, count($modelClassNames));
            $this->assertEquals('LeadsFilteredList', $modelClassNames[0]);
        }

        public function testIsStateALead()
        {
            $allContactStates = ContactState::GetAll();
            $this->assertGreaterThan(1, count($allContactStates));
            foreach ($allContactStates as $contactState)
            {
                if ($contactState->id < ContactsUtil::getStartingStateId())
                {
                    $isStateALeadCorrect = true;
                }
                else
                {
                    $isStateALeadCorrect = false;
                }
                $isStateALead = LeadsUtil::isStateALead($contactState);
                $this->assertEquals($isStateALead, $isStateALeadCorrect);
            }
        }

        public function testGetLeadStateDataFromStartingStateKeyedByIdAndLabelByLanguage()
        {
            $newStates        = ContactState::getByName('New');
            $inProgressStates = ContactState::getByName('In Progress');
            $recycledStates   = ContactState::getByName('Recycled');
            $deadStates       = ContactState::getByName('Dead');
            $data             = LeadsUtil::getLeadStateDataFromStartingStateKeyedByIdAndLabelByLanguage('en');
            $compareData = array($newStates[0]->id         => 'New',
                                 $inProgressStates[0]->id  => 'In Progress',
                                 $recycledStates[0]->id    => 'Recycled',
                                 $deadStates[0]->id        => 'Dead');
            $this->assertEquals($compareData, $data);
            $data             = LeadsUtil::getLeadStateDataFromStartingStateKeyedByIdAndLabelByLanguage('fr');
            $compareData = array($newStates[0]->id         => 'Nouveau',
                                 $inProgressStates[0]->id  => 'En cours',
                                 $recycledStates[0]->id    => 'Réactivé',
                                 $deadStates[0]->id        => 'Mort');
            $this->assertEquals($compareData, $data);
        }
    }
?>
