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
     * For data provider work that is not specific to the application.
     */
    class RedBeanModelDataProviderTest extends DataProviderBaseTest
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
            //Save a sample model.
            $model = new TestCustomFieldsModel();
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'A';
            $model->multipleIndustries->values->add($customFieldValue);
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'D';
            $model->multipleSomethings->values->add($customFieldValue);
            $this->assertTrue($model->save());

            //Save a second model with nothing.
            $model = new TestCustomFieldsModel();
            $this->assertTrue($model->save());

            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test where relatioon id is in a joining table.  Many to Many relationship
            $_FAKEPOST['TestCustomFieldsModel'] = array();
            $_FAKEPOST['TestCustomFieldsModel']['multipleIndustries']['values'] = array('A', 'B', 'C');
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(
                                        new TestCustomFieldsModel(false), 1, $_FAKEPOST['TestCustomFieldsModel']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $where        = RedBeanModelDataProvider::makeWhere('TestCustomFieldsModel', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "(1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                            "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                            "{$quote}multiplevaluescustomfield{$quote}.id " .
                            "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('A','B','C') limit 1))";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('multiplevaluescustomfield', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql         = TestCustomFieldsModel::
                                 makeSubsetOrCountSqlQuery('testcustomfieldsmodel', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}testcustomfieldsmodel{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}testcustomfieldsmodel{$quote} ";
            $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} on ";
            $compareSubsetSql .= "{$quote}multiplevaluescustomfield{$quote}.{$quote}id{$quote} = ";
            $compareSubsetSql .= "{$quote}testcustomfieldsmodel{$quote}.{$quote}multipleindustries_multiplevaluescustomfield_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('TestCustomFieldsModel', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
        }

       /**
         * @depends testSearchByMultipleValuesCustomField
         */
        public function testSearchByTwoMultipleValuesCustomField()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test where relatioon id is in a joining table.  Many to Many relationship
            $_FAKEPOST['TestCustomFieldsModel'] = array();
            $_FAKEPOST['TestCustomFieldsModel']['multipleIndustries']['values'] = array('A', 'B', 'C');
            $_FAKEPOST['TestCustomFieldsModel']['multipleSomethings']['values'] = array('D', 'E', 'F');
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(
                                        new TestCustomFieldsModel(false), 1, $_FAKEPOST['TestCustomFieldsModel']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $where        = RedBeanModelDataProvider::makeWhere('TestCustomFieldsModel', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "(1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                            "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                            "{$quote}multiplevaluescustomfield{$quote}.id " .
                            "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('A','B','C') limit 1))";
            $compareWhere .= " and (1 = (select 1 from {$quote}customfieldvalue{$quote} customfieldvalue " .
                            "where {$quote}customfieldvalue{$quote}.{$quote}multiplevaluescustomfield_id{$quote} = " .
                            "{$quote}multiplevaluescustomfield1{$quote}.id " .
                            "and {$quote}customfieldvalue{$quote}.{$quote}value{$quote} IN('D','E','F') limit 1))";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('multiplevaluescustomfield', $leftTables[0]['tableName']);
            $this->assertEquals('multiplevaluescustomfield', $leftTables[1]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql         = TestCustomFieldsModel::
                                 makeSubsetOrCountSqlQuery('testcustomfieldsmodel', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}testcustomfieldsmodel{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}testcustomfieldsmodel{$quote} ";
            $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} on ";
            $compareSubsetSql .= "{$quote}multiplevaluescustomfield{$quote}.{$quote}id{$quote} = ";
            $compareSubsetSql .= "{$quote}testcustomfieldsmodel{$quote}.{$quote}multipleindustries_multiplevaluescustomfield_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}multiplevaluescustomfield{$quote} multiplevaluescustomfield1 on ";
            $compareSubsetSql .= "{$quote}multiplevaluescustomfield1{$quote}.{$quote}id{$quote} = ";
            $compareSubsetSql .= "{$quote}testcustomfieldsmodel{$quote}.{$quote}multiplesomethings_multiplevaluescustomfield_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('TestCustomFieldsModel', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(1, count($data));
        }

       /**
         * @depends testSearchByTwoMultipleValuesCustomField
         */
        public function testNullOrEmptyOperatorsInWhereClause()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //I has many ls.
            $i = new I();
            $i->iMember = 'abc';
            $this->assertTrue($i->save());

            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'iMember',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}imember{$quote} IS NULL)";
            $this->assertEquals($compareWhere, $where);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();

            //Test is not null
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'iMember',
                    'operatorType'         => 'isNotNull',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}imember{$quote} IS NOT NULL)";
            $this->assertEquals($compareWhere, $where);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();

            //Test is empty
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'iMember',
                    'operatorType'         => 'isEmpty',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}imember{$quote} = '')";
            $this->assertEquals($compareWhere, $where);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();

            //Test is not empty
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'iMember',
                    'operatorType'         => 'isNotEmpty',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}imember{$quote} != '')";
            $this->assertEquals($compareWhere, $where);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        public function testEscapedSingleQuoteInWhereClause()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //I has many ls.
            $i = new I();
            $l = new L();
            $l->lMember = 'def';
            $this->assertTrue($l->save());
            $i->iMember = 'abc';
            $i->ls->add($l);
            $this->assertTrue($i->save());

            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'ls',
                    'relatedAttributeName' => 'lMember',
                    'operatorType'         => 'equals',
                    'value'                => "some'value",
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}l{$quote}.{$quote}lmember{$quote} = lower('some\'value'))";
            $this->assertEquals($compareWhere, $where);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testEscapedSingleQuoteInWhereClause
         */
        public function testSearchByRelationId()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test where relation id is a column on the model.
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['hasOneRelation']['id'] = '3';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}hasonerelation_customfield_id{$quote} = 3)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test where relation id is no a column on the model.
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['hasManyRelation']['id'] = '5';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}a{$quote}.{$quote}id{$quote} = 5)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('a', $leftTables[0]['tableName']);

            //Test where relation id is a column on the castUp model.
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['castUpHasOne']['id'] = '4';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}h{$quote}.{$quote}castuphasone_g_id{$quote} = 4)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test where relatioon id is in a joining table.  Many to Many relationship
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['manyManyRelation']['id'] = '55';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i_z{$quote}.{$quote}z_id{$quote} = 55)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('i_z', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = I::makeSubsetOrCountSqlQuery('i', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}i{$quote} ";
            $compareSubsetSql .= "left join {$quote}i_z{$quote} on ";
            $compareSubsetSql .= "{$quote}i_z{$quote}.{$quote}i_id{$quote} = {$quote}i{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('II', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSearchByRelationId
         */
        public function testSearchBelongsRelationWhenRelationIsSameModelType()
        {
            //Test member of search.
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['i']['id'] = '4';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new I(false),
                1,
                $_FAKEPOST['I']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i{$quote}.{$quote}i_id{$quote} = 4)";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSearchBelongsRelationWhenRelationIsSameModelType
         */
        public function testSearchHasManyRelationWhenRelationIsSameModelType()
        {
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['is']['id'] = '5';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}i1{$quote}.{$quote}id{$quote} = 5)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('i', $leftTables[0]['tableName']);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSearchHasManyRelationWhenRelationIsSameModelType
         */
        public function testRelatedAttributeNeedsToBeJoinedFromCastedUpModel()
        {
            $_FAKEPOST['I'] = array();
            $_FAKEPOST['I']['castUpHasOne']['g'] = 'somevalue';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new I(false), 1, $_FAKEPOST['I']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}g{$quote}.{$quote}g{$quote} like 'somevalue%')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('h', $fromTables[0]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);
        }

        /**
         * @depends testRelatedAttributeNeedsToBeJoinedFromCastedUpModel
         */
        public function testRelatedAttributeNeedsToBeJoinedFromFartherCastedUpModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //II extends I which extends H, show search on II where you are querying an attribute on H.
            $_FAKEPOST['II'] = array();
            $_FAKEPOST['II']['castUpHasOne']['g'] = 'somevalue';
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new II(false), 1, $_FAKEPOST['II']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('II');
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('II', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}g{$quote}.{$quote}g{$quote} like 'somevalue%')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(2, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('i', $fromTables[0]['tableName']);
            $this->assertEquals('h', $fromTables[1]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = II::makeSubsetOrCountSqlQuery('ii', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}ii{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}ii{$quote}, {$quote}i{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= "left join {$quote}g{$quote} on ";
            $compareSubsetSql .= "{$quote}g{$quote}.{$quote}id{$quote} = {$quote}h{$quote}.{$quote}castuphasone_g_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= "and {$quote}i{$quote}.{$quote}id{$quote} = {$quote}ii{$quote}.{$quote}i_id{$quote} ";
            $compareSubsetSql .= "and {$quote}h{$quote}.{$quote}id{$quote} = {$quote}i{$quote}.{$quote}h_id{$quote} ";
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('II', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testRelatedAttributeNeedsToBeJoinedFromFartherCastedUpModel
         */
        public function testHasManyRelationSqlQuery()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //I has many Ks.
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'ks',
                    'relatedAttributeName' => 'kMember',
                    'operatorType'         => 'equals',
                    'value'                => 'somevalue',
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}k{$quote}.{$quote}kmember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('k', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = I::makeSubsetOrCountSqlQuery('i', $joinTablesAdapter, 1, 5,
                                                      $where, null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}i{$quote} ";
            $compareSubsetSql .= "left join {$quote}k{$quote} on ";
            $compareSubsetSql .= "{$quote}k{$quote}.{$quote}i_id{$quote} = {$quote}i{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testHasManyRelationSqlQuery
         */
        public function testManyManyRelationSqlQuery()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //I has many ls.
            $i = new I();
            $l = new L();
            $l->lMember = 'def';
            $this->assertTrue($l->save());
            $i->iMember = 'abc';
            $i->ls->add($l);
            $this->assertTrue($i->save());

            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'ls',
                    'relatedAttributeName' => 'lMember',
                    'operatorType'         => 'equals',
                    'value'                => 'somevalue',
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');

            $quote        = DatabaseCompatibilityUtil::getQuote();
            $where        = RedBeanModelDataProvider::makeWhere('I', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}l{$quote}.{$quote}lmember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('i_l', $leftTables[0]['tableName']);
            $this->assertEquals('l', $leftTables[1]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = I::makeSubsetOrCountSqlQuery('i', $joinTablesAdapter, 1, 5,
                                                      $where, null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}i{$quote} ";
            $compareSubsetSql .= "left join {$quote}i_l{$quote} on ";
            $compareSubsetSql .= "{$quote}i_l{$quote}.{$quote}i_id{$quote} = {$quote}i{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}l{$quote} on ";
            $compareSubsetSql .= "{$quote}l{$quote}.{$quote}id{$quote} = {$quote}i_l{$quote}.{$quote}l_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('I', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testManyManyRelationSqlQuery
         * See ModelDataProviderUtilTest for more order by testing.
         */
        public function testOrderByCombinations()
        {
            $gg = new GG();
            $gg->gg = 'a';
            $gg->g  = 'v';
            $this->assertTrue($gg->save());
            $gg = new GG();
            $gg->gg = 'b';
            $gg->g  = 't';
            $this->assertTrue($gg->save());
            $gg = new GG();
            $gg->gg = 'c';
            $gg->g  = 'u';
            $this->assertTrue($gg->save());

            $quote               = DatabaseCompatibilityUtil::getQuote();
            $_FAKEPOST['GG']     = array();
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new GG(false), 1, $_FAKEPOST['GG']);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider        = new RedBeanModelDataProvider('GG', 'gg', false, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(3, count($data));
            $this->assertEquals('a', $data[0]->gg);
            $this->assertEquals('b', $data[1]->gg);
            $this->assertEquals('c', $data[2]->gg);
            $dataProvider        = new RedBeanModelDataProvider('GG', 'gg', true, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(3, count($data));
            $this->assertEquals('c', $data[0]->gg);
            $this->assertEquals('b', $data[1]->gg);
            $this->assertEquals('a', $data[2]->gg);
            $compareString       = "{$quote}gg{$quote}.{$quote}gg{$quote}";
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('GG');
            $this->assertEquals($compareString, RedBeanModelDataProvider::resolveSortAttributeColumnName('GG', $joinTablesAdapter, 'gg'));
            $compareString       = "{$quote}g{$quote}.{$quote}g{$quote}";
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('GG');
            $this->assertEquals($compareString, RedBeanModelDataProvider::resolveSortAttributeColumnName('GG', $joinTablesAdapter, 'g'));

            //test ordering by castedUp model.
            $dataProvider        = new RedBeanModelDataProvider('GG', 'g', false, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(3, count($data));
            $this->assertEquals('b', $data[0]->gg);
            $this->assertEquals('c', $data[1]->gg);
            $this->assertEquals('a', $data[2]->gg);
            $dataProvider        = new RedBeanModelDataProvider('GG', 'g', true, $searchAttributeData);
            $data = $dataProvider->getData();
            $this->assertEquals(3, count($data));
            $this->assertEquals('a', $data[0]->gg);
            $this->assertEquals('c', $data[1]->gg);
            $this->assertEquals('b', $data[2]->gg);

            //test ordering by custom attribute value.
        }

        /**
         * See ModelDataProviderUtilTest->testResolveSortAttributeColumnName
         */
            public function testResolveSortAttributeColumnName()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();

            //Test a standard non-relation attribute on I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = RedBeanModelDataProvider::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'iMember');
            $this->assertEquals("{$quote}i{$quote}.{$quote}imember{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a standard casted up attribute on H from I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = RedBeanModelDataProvider::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'name');
            $this->assertEquals("{$quote}h{$quote}.{$quote}name{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Test a relation attribute G->g from H
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('H');
            $sort = RedBeanModelDataProvider::resolveSortAttributeColumnName('H', $joinTablesAdapter, 'castUpHasOne');
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a relation attribute G->g where casted up from I
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('I');
            $sort = RedBeanModelDataProvider::resolveSortAttributeColumnName('I', $joinTablesAdapter, 'castUpHasOne');
            $this->assertEquals("{$quote}g{$quote}.{$quote}g{$quote}", $sort);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('h', $fromTables[0]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('g', $leftTables[0]['tableName']);

            //Test a customField like TestCustomFieldsModel->industry
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('TestCustomFieldsModel');
            $sort = RedBeanModelDataProvider::resolveSortAttributeColumnName(
                                            'TestCustomFieldsModel', $joinTablesAdapter, 'industry');
            $this->assertEquals("{$quote}customfield{$quote}.{$quote}value{$quote}", $sort);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('customfield', $leftTables[0]['tableName']);
        }
    }
?>
