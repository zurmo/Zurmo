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

    class ContactsUtilTest extends ZurmoBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function testResolveContactStateAdapterByModulesUserHasAccessTo()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $bobby   = User::getByUsername('bobby');
            $this->assertEquals(Right::DENY, $bobby->getEffectiveRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS));
            $this->assertEquals(Right::DENY, $bobby->getEffectiveRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS));

            //test Contact model where has no access to either the leads or contacts module.
            $adapterName = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('ContactsModule', 'LeadsModule', $bobby);
            $this->assertFalse($adapterName);

            //test Contact model where user has access to only the leads module
            $bobby->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($bobby->save());
            $adapterName = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('ContactsModule', 'LeadsModule', $bobby);
            $this->assertEquals('LeadsStateMetadataAdapter', $adapterName);

            //test Contact model where user has access to only the contacts module
            $bobby->removeRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $bobby->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $this->assertTrue($bobby->save());
            $adapterName = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('ContactsModule', 'LeadsModule', $bobby);
            $this->assertEquals('ContactsStateMetadataAdapter', $adapterName);

            //test Contact model where user has access to both the contacts and leads module.
            $bobby->setRight('LeadsModule', LeadsModule::RIGHT_ACCESS_LEADS);
            $this->assertTrue($bobby->save());
            $adapterName = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('ContactsModule', 'LeadsModule', $bobby);
            $this->assertNull($adapterName);
        }

        /**
         * @depends testResolveContactStateAdapterByModulesUserHasAccessTo
         */
        public function testGetContactStateDataFromStartingStateOnAndKeyedById()
        {
            $this->assertTrue(ContactsModule::loadStartingData());
            $this->assertEquals(6, count(ContactState::GetAll()));
            $contactStates = ContactsUtil::GetContactStateDataFromStartingStateOnAndKeyedById();
            $this->assertEquals(2, count($contactStates));
        }

        /**
         * @depends testGetContactStateDataFromStartingStateOnAndKeyedById
         */
        public function testGetContactStateLabelsKeyedByLanguageAndOrder()
        {
            $data                        = ContactsUtil::getContactStateLabelsKeyedByLanguageAndOrder();
            $compareData                 = null;
            $this->assertEquals($compareData, $data);
            $states                      = ContactState::getByName('Qualified');
            $states[0]->serializedLabels = serialize(array('fr' => 'QualifiedFr', 'de' => 'QualifiedDe'));
            $this->assertTrue($states[0]->save());
            $data                        = ContactsUtil::getContactStateLabelsKeyedByLanguageAndOrder();
            $compareData                 = array('fr' => array($states[0]->order => 'QualifiedFr'),
                                                 'de' => array($states[0]->order => 'QualifiedDe'));
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetContactStateLabelsKeyedByLanguageAndOrder
         */
        public function testResolveStateLabelByLanguage()
        {
            $states = ContactState::getByName('Qualified');
            $this->assertEquals('Qualified',   ContactsUtil::resolveStateLabelByLanguage($states[0], 'en'));
            $this->assertEquals('QualifiedFr', ContactsUtil::resolveStateLabelByLanguage($states[0], 'fr'));
            $this->assertEquals('QualifiedDe', ContactsUtil::resolveStateLabelByLanguage($states[0], 'de'));
        }

        /**
         * @depends testResolveStateLabelByLanguage
         */
        public function testGetContactStateDataFromStartingStateKeyedByIdAndLabelByLanguage()
        {
            $qualifiedStates = ContactState::getByName('Qualified');
            $customerStates = ContactState::getByName('Customer');
            $data = ContactsUtil::getContactStateDataFromStartingStateKeyedByIdAndLabelByLanguage('en');
            $compareData = array($qualifiedStates[0]->id => 'Qualified',
                                $customerStates[0]->id  => 'Customer');
            $this->assertEquals($compareData, $data);
            $data = ContactsUtil::getContactStateDataFromStartingStateKeyedByIdAndLabelByLanguage('fr');
            $compareData = array($qualifiedStates[0]->id => 'QualifiedFr',
                                $customerStates[0]->id  => 'Client');
            $this->assertEquals($compareData, $data);
        }

        /**
         * @depends testGetContactStateDataFromStartingStateKeyedByIdAndLabelByLanguage
         */
        public function testResolveAddressesFromRelatedAccount()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $contact = new Contact();
            $account = new Account();
            $account->name                        = 'some name';
            $account->billingAddress->city        = 'some city';
            $account->billingAddress->country     = 'some country';
            $account->billingAddress->postalCode  = 'some postalCode';
            $account->billingAddress->state       = 'some state';
            $account->billingAddress->street1     = 'some street1';
            $account->billingAddress->street2     = 'some street2';
            $account->shippingAddress->city       = 'some2 city';
            $account->shippingAddress->country    = 'some2 country';
            $account->shippingAddress->postalCode = 'some2 postalCode';
            $account->shippingAddress->state      = 'some2 state';
            $account->shippingAddress->street1    = 'some2 street1';
            $account->shippingAddress->street2    = 'some2 street2';
            $saved = $account->save();
            $this->assertTrue($saved);
            $contact->account                     = $account;
            ContactsUtil::resolveAddressesFromRelatedAccount($contact);
            $this->assertEquals('some city',         $contact->primaryAddress->city);
            $this->assertEquals('some country',      $contact->primaryAddress->country);
            $this->assertEquals('some postalCode',   $contact->primaryAddress->postalCode);
            $this->assertEquals('some state',        $contact->primaryAddress->state);
            $this->assertEquals('some street1',      $contact->primaryAddress->street1);
            $this->assertEquals('some street2',      $contact->primaryAddress->street2);
            $this->assertEquals('some2 city',        $contact->secondaryAddress->city);
            $this->assertEquals('some2 country',     $contact->secondaryAddress->country);
            $this->assertEquals('some2 postalCode',  $contact->secondaryAddress->postalCode);
            $this->assertEquals('some2 state',       $contact->secondaryAddress->state);
            $this->assertEquals('some2 street1',     $contact->secondaryAddress->street1);
            $this->assertEquals('some2 street2',     $contact->secondaryAddress->street2);
        }
    }
?>