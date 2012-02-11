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

    class ContactsUtilTest extends BaseTest
    {
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
    }
?>