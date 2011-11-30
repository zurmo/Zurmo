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

    /**
     * Tests on contact pecific search permutations.
     */
    class ContactsSearchWithDataProviderTest extends ZurmoDataProviderBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSearchOnContactState()
        {
            //Test member of search.
            $_FAKEPOST['Contact'] = array();
            $_FAKEPOST['Contact']['state']['id'] = '4';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Contact(false),
                1,
                $_FAKEPOST['Contact']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}contact{$quote}.{$quote}state_contactstate_id{$quote} = 4)";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        public function testFullNameOnContactsSearchFormSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $_FAKEPOST['Contact'] = array();
            $_FAKEPOST['Contact']['fullName'] = 'Jackie Tyler';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new ContactsSearchForm(new Contact(false)),
                1,
                $_FAKEPOST['Contact']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareData = array('clauses' => array(1 => array('attributeName' => 'firstName',
                                                          'operatorType'  => 'startsWith',
                                                          'value'         => 'Jackie Tyler'),
                                                    2 => array('attributeName' => 'lastName',
                                                          'operatorType'  => 'startsWith',
                                                          'value'         => 'Jackie Tyler'),
                                                    3 => array('concatedAttributeNames' => array('firstName', 'lastName'),
                                                          'operatorType'  => 'startsWith',
                                                          'value'         => 'Jackie Tyler')),
                                 'structure' => '(1 or 2 or 3)');
            $this->assertEquals($compareData, $searchAttributeData);

            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere  = "(({$quote}person{$quote}.{$quote}firstname{$quote} like 'Jackie Tyler%') or ";
            $compareWhere .= "({$quote}person{$quote}.{$quote}lastname{$quote} like 'Jackie Tyler%') or ";
            $compareWhere .= "(concat({$quote}person{$quote}.{$quote}firstname{$quote}, ' ', ";
            $compareWhere .= "{$quote}person{$quote}.{$quote}lastname{$quote}) like 'Jackie Tyler%'))";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('person', $fromTables[0]['tableName']);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();

            $this->assertEquals(0, count($data));

            ContactTestHelper::createContactByNameForOwner('Dino', $super);

            $dataProvider->getTotalItemCount(true); //refreshes the total item count
            $data = $dataProvider->getData();
            $this->assertEquals(0, count($data));

            ContactTestHelper::createContactByNameForOwner('Jackie', $super);

            $dataProvider->getTotalItemCount(true); //refreshes the total item count
            $data = $dataProvider->getData();
            $this->assertEquals(0, count($data));

            ContactsModule::loadStartingData();
            $contact = new Contact();
            $contact->firstName  = 'Jackie';
            $contact->lastName   = 'Tyler';
            $contact->owner      = $super;
            $contact->state      = ContactsUtil::getStartingState();
            $this->assertTrue($contact->save());

            $dataProvider->getTotalItemCount(true); //refreshes the total item count
            $data = $dataProvider->getData(true);

            $this->assertEquals(1, count($data));
            $this->assertEquals($contact->id, $data[0]->id);
        }
    }
?>