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
     * For testing the interaction of MultipleValuesOwnedCustomField and the DataProvider
     */
    class OwnedMultipleValuesCustomFieldDataProviderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();

            $values = array(
                'A',
                'B',
                'C',
                'CC',
                'CCC',
            );
            $customFieldData = CustomFieldData::getByName('MultipleIndustries');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard

            $values = array(
                'D',
                'E',
                'F',
                'FF',
                'FFF',
            );
            $customFieldData = CustomFieldData::getByName('MultipleSomethings');
            $customFieldData->serializedData = serialize($values);
            $saved = $customFieldData->save();
            assert($saved);    // Not Coding Standard
        }

        public function testSearchByMultipleValuesCustomField()
        {
            if (!RedBeanDatabase::isFrozen())
            {
                //Save a sample model.
                $model = new TestOwnedCustomFieldsModel();
                $customFieldValue = new CustomFieldValue();
                $customFieldValue->value = 'A';
                $model->multipleIndustries->values->add($customFieldValue);
                $customFieldValue = new CustomFieldValue();
                $customFieldValue->value = 'D';
                $model->multipleSomethings->values->add($customFieldValue);
                $this->assertTrue($model->save());

                //Save a second model with nothing.
                $model = new TestOwnedCustomFieldsModel();
                $this->assertTrue($model->save());

                $quote        = DatabaseCompatibilityUtil::getQuote();
                //Test where relatioon id is in a joining table.  Many to Many relationship
                $_FAKEPOST['TestOwnedCustomFieldsModel'] = array();
                $_FAKEPOST['TestOwnedCustomFieldsModel']['multipleIndustries']['values'] = array('A', 'B', 'C');
                $metadataAdapter     = new SearchDataProviderMetadataAdapter(
                                            new TestOwnedCustomFieldsModel(false), 1, $_FAKEPOST['TestOwnedCustomFieldsModel']);
                $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
                $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestOwnedCustomFieldsModel');
                $where        = RedBeanModelDataProvider::makeWhere('TestOwnedCustomFieldsModel', $searchAttributeData, $joinTablesAdapter);
                $compareWhere = "(1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                                "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                                "{$quote}multiplevaluescustomfield{$quote}.id " .
                                "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('A','B','C') limit 1))"; // Not Coding Standard
                $this->assertEquals($compareWhere, $where);
                //Now test that the joinTablesAdapter has correct information.
                $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
                $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
                $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
                $this->assertEquals('multiplevaluescustomfield',      $leftTables[0]['tableName']);

                //Now test that the subsetSQL query produced is correct.
                $subsetSql         = TestOwnedCustomFieldsModel::
                                     makeSubsetOrCountSqlQuery('testownedcustomfieldsmodel', $joinTablesAdapter, 1, 5, $where, null);
                $compareSubsetSql  = "select {$quote}testownedcustomfieldsmodel{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from {$quote}testownedcustomfieldsmodel{$quote} ";
                $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} on ";
                $compareSubsetSql .= "{$quote}multiplevaluescustomfield{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}testownedcustomfieldsmodel{$quote}.{$quote}multipleindustries_multiplevaluescustomfield_id{$quote} ";
                $compareSubsetSql .= "where " . $compareWhere . ' ';
                $compareSubsetSql .= 'limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);

                //Make sure the sql runs properly.
                $dataProvider = new RedBeanModelDataProvider('TestOwnedCustomFieldsModel', null, false, $searchAttributeData);
                $data = $dataProvider->getData();
                $this->assertEquals(1, count($data));
            }
        }

       /**
         * @depends testSearchByMultipleValuesCustomField
         */
        public function testSearchByTwoMultipleValuesCustomField()
        {
            if (!RedBeanDatabase::isFrozen())
            {
                $quote        = DatabaseCompatibilityUtil::getQuote();
                //Test where relatioon id is in a joining table.  Many to Many relationship
                $_FAKEPOST['TestOwnedCustomFieldsModel'] = array();
                $_FAKEPOST['TestOwnedCustomFieldsModel']['multipleIndustries']['values'] = array('A', 'B', 'C');
                $_FAKEPOST['TestOwnedCustomFieldsModel']['multipleSomethings']['values'] = array('D', 'E', 'F');
                $metadataAdapter     = new SearchDataProviderMetadataAdapter(
                                            new TestOwnedCustomFieldsModel(false), 1, $_FAKEPOST['TestOwnedCustomFieldsModel']);
                $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
                $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestOwnedCustomFieldsModel');
                $where        = RedBeanModelDataProvider::makeWhere('TestOwnedCustomFieldsModel', $searchAttributeData, $joinTablesAdapter);
                $compareWhere = "(1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                                "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                                "{$quote}multiplevaluescustomfield{$quote}.id " .
                                "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('A','B','C') limit 1))"; // Not Coding Standard
                $compareWhere .= " and (1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                                "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                                "{$quote}multiplevaluescustomfield1{$quote}.id " .
                                "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('D','E','F') limit 1))"; // Not Coding Standard
                $this->assertEquals($compareWhere, $where);
                //Now test that the joinTablesAdapter has correct information.
                $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
                $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
                $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
                $this->assertEquals('multiplevaluescustomfield',      $leftTables[0]['tableName']);
                $this->assertEquals('multiplevaluescustomfield',      $leftTables[1]['tableName']);

                //Now test that the subsetSQL query produced is correct.
                $subsetSql         = TestOwnedCustomFieldsModel::
                                     makeSubsetOrCountSqlQuery('testcustomfieldsmodel', $joinTablesAdapter, 1, 5, $where, null);
                $compareSubsetSql  = "select {$quote}testcustomfieldsmodel{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from {$quote}testcustomfieldsmodel{$quote} ";
                $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} on ";
                $compareSubsetSql .= "{$quote}multiplevaluescustomfield{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}testownedcustomfieldsmodel{$quote}.{$quote}multipleindustries_multiplevaluescustomfield_id{$quote} ";
                $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} multiplevaluescustomfield1 on ";
                $compareSubsetSql .= "{$quote}multiplevaluescustomfield1{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}testownedcustomfieldsmodel{$quote}.{$quote}multiplesomethings_multiplevaluescustomfield_id{$quote} ";
                $compareSubsetSql .= "where " . $compareWhere . ' ';
                $compareSubsetSql .= 'limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);

                //Make sure the sql runs properly.
                $dataProvider = new RedBeanModelDataProvider('TestOwnedCustomFieldsModel', null, false, $searchAttributeData);
                $data = $dataProvider->getData();
                $this->assertEquals(1, count($data));
            }
        }
    }
?>