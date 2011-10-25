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

    class ContactStateTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateAndGetContactState()
        {
            $state = new ContactState();
            $state->name = 'First State';
            $state->order = 0;
            $this->assertTrue($state->save());
            $id = $state->id;
            unset($state);
            $state = ContactState::getById($id);
            $this->assertEquals('First State', $state->name);
            $this->assertEquals(0, $state->order);
            $state->delete();
        }

        public function testContactStateModelAttributesAdapter()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertTrue(ContactsModule::loadStartingData());
            $this->assertEquals(6, count(ContactState::GetAll()));

            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Contact(), 'state');
            $compareData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Dead',
                4 => 'Qualified',
                5 => 'Customer',
            );
            $this->assertEquals($compareData, $attributeForm->contactStatesData);
            $this->assertEquals(null, $attributeForm->contactStatesLabels);
            $this->assertEquals(4, $attributeForm->startingStateOrder);

            //Now add new values.
            $attributeForm->contactStatesData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Dead',
                4 => 'Qualified',
                5 => 'Customer',
                6 => 'AAA',
                7 => 'BBB',
            );
            $contactStatesLabels = array(
                'fr' => array('New', 'In ProgressFr', 'RecycledFr', 'DeadFr', 'QualifiedFr', 'CustomerFr', 'AAAFr', 'BBBFr')
            );
            $attributeForm->contactStatesLabels = $contactStatesLabels;
            $attributeForm->startingStateOrder  = 5;
            $adapter = new ContactStateModelAttributesAdapter(new Contact());
            $adapter->setAttributeMetadataFromForm($attributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Contact(), 'state');
            $compareData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Dead',
                4 => 'Qualified',
                5 => 'Customer',
                6 => 'AAA',
                7 => 'BBB',
            );
            $this->assertEquals($compareData, $attributeForm->contactStatesData);
            $this->assertEquals($contactStatesLabels, $attributeForm->contactStatesLabels);
            $contactState = ContactState::getByName('Customer');
            $this->assertEquals(5, $contactState[0]->order);
            $this->assertEquals(5, $attributeForm->startingStateOrder);

            //Test removing existing values.
            $attributeForm->contactStatesData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Customer',
                4 => 'AAA',
                5 => 'BBB',
            );
            $attributeForm->startingStateOrder = 5;
            $adapter = new ContactStateModelAttributesAdapter(new Contact());
            $adapter->setAttributeMetadataFromForm($attributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Contact(), 'state');
            $compareData = array(
                0 => 'New',
                1 => 'In Progress',
                2 => 'Recycled',
                3 => 'Customer',
                4 => 'AAA',
                5 => 'BBB',
            );
            $this->assertEquals($compareData, $attributeForm->contactStatesData);
            $this->assertEquals(5, $attributeForm->startingStateOrder);

            //Test switching order of existing values.
            $attributeForm->contactStatesData = array(
                0 => 'New',
                3 => 'In Progress',
                5 => 'Recycled',
                1 => 'Customer',
                4 => 'AAA',
                2 => 'BBB',
            );
            $attributeForm->startingStateOrder = 2;
            $adapter = new ContactStateModelAttributesAdapter(new Contact());
            $adapter->setAttributeMetadataFromForm($attributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Contact(), 'state');
            $compareData = array(
                0 => 'New',
                3 => 'In Progress',
                5 => 'Recycled',
                1 => 'Customer',
                4 => 'AAA',
                2 => 'BBB',
            );
            $this->assertEquals($compareData, $attributeForm->contactStatesData);
            $this->assertEquals(2, $attributeForm->startingStateOrder);

            //Test switching order of existing values and adding new values mixed in.
            $attributeForm->contactStatesData = array(
                3 => 'New',
                6 => 'In Progress',
                5 => 'Recycled',
                1 => 'Customer',
                4 => 'AAA',
                2 => 'BBB',
                0 => 'CCC',
            );
            $attributeForm->startingStateOrder = 2;
            $adapter = new ContactStateModelAttributesAdapter(new Contact());
            $adapter->setAttributeMetadataFromForm($attributeForm);
            $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName(new Contact(), 'state');
            $compareData = array(
                3 => 'New',
                6 => 'In Progress',
                5 => 'Recycled',
                1 => 'Customer',
                4 => 'AAA',
                2 => 'BBB',
                0 => 'CCC',
            );
            $this->assertEquals($compareData, $attributeForm->contactStatesData);
            $this->assertEquals(2, $attributeForm->startingStateOrder);
        }
    }
?>