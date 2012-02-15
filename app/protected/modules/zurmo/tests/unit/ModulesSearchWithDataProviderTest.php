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

    /**
     * Broad data provider tests that touch across different modules in the zurmo application.
     */
    class ModulesSearchWithDataProviderTest extends DataProviderBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ModulesSearchWithDataProviderTestHelper::createCustomFieldData('Industries');
            ModulesSearchWithDataProviderTestHelper::createCustomFieldData('AccountTypes');
            ModulesSearchWithDataProviderTestHelper::createCustomAttributesForModel(new Account());
        }

        public function testAllCustomAttributesAdaptToMetadataFromPostCorrectly()
        {
            $super = User::getByUsername('super');
            //Account Model - Fake post with all custom attribute types that they adapt correct to metadata.
            $fakePostData = array(
                'checkBox'    => array('value' => '1'),
                'currency'    => '108.45',
                'date'        => '2007-07-01',
                'dateTime'     => '2007-07-01 06:12:45',
                'decimal'      => '45.6',
                'dropDown'     => array('value' => '3'),
                'integer'      => '67876',
                //'multiSelect'  => '',
                'phone'        => '123456',
                'radio'        => array('value' => '2'),
                'text'         => 'Some Text',
                'textArea'     => 'Some description',
                'url'          => 'somesite.com',
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(new Account(), $super->id, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $compareClauses = array(
                1 => array(
                    'attributeName' => 'checkBox',
                    'operatorType'  => 'equals',
                    'value'         => (bool)1,
                ),
                2 => array(
                    'attributeName' => 'currency',
                    'operatorType'  => 'equals',
                    'value'         => (float)108.45,
                ),
                3 => array(
                    'attributeName' => 'date',
                    'operatorType'  => 'equals',
                    'value'         => '2007-07-01',
                ),
                4 => array(
                    'attributeName' => 'dateTime',
                    'operatorType' => 'equals',
                    'value'        => '2007-07-01 06:12:45',
                ),
                5 => array(
                    'attributeName' => 'decimal',
                    'operatorType' => 'equals',
                    'value'        => (float)45.6,
                ),
                6 => array(
                    'attributeName'        => 'dropDown',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'equals',
                    'value'                => '3',
                ),
                7 => array(
                    'attributeName' => 'integer',
                    'operatorType' => 'equals',
                    'value'        => (int)67876,
                ),
                8 => array(
                    'attributeName' => 'phone',
                    'operatorType' => 'startsWith',
                    'value'        => '123456',
                ),
                9 => array(
                    'attributeName'        => 'radio',
                    'relatedAttributeName' => 'value',
                    'operatorType'         => 'equals',
                    'value'                => '2',
                ),
                10 => array(
                    'attributeName' => 'text',
                    'operatorType' => 'startsWith',
                    'value'        => 'Some Text',
                ),
                11 => array(
                    'attributeName' => 'textArea',
                    'operatorType' => 'contains',
                    'value'        => 'Some description',
                ),
                12 => array(
                    'attributeName' => 'url',
                    'operatorType' => 'contains',
                    'value'        => 'somesite.com',
                ),
            );
            $compareStructure = '1 and 2 and 3 and 4 and 5 and 6 and 7 and 8 and 9 and 10 and 11 and 12';
            $this->assertEquals($compareClauses,   $searchAttributeData['clauses']);
            $this->assertEquals($compareStructure, $searchAttributeData['structure']);

            //Build the query 'where' and 'joins'. Confirm they are as expected
            $quote = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $compareWhere     = "({$quote}account{$quote}.{$quote}checkbox{$quote} = 1)"                               .
                                " and ({$quote}account{$quote}.{$quote}currency_currencyvalue_id{$quote} = 108.45)"                       .
                                " and ({$quote}account{$quote}.{$quote}date{$quote} = '2007-07-01')"              .
                                " and ({$quote}account{$quote}.{$quote}datetime{$quote} = '2007-07-01 06:12:45')" .
                                " and ({$quote}account{$quote}.{$quote}decimal{$quote} = 45.6)" .
                                " and ({$quote}customfield{$quote}.{$quote}value{$quote} = '3')" .
                                " and ({$quote}account{$quote}.{$quote}integer{$quote} = 67876)" .
                                " and ({$quote}account{$quote}.{$quote}phone{$quote} like '123456%')" .
                                " and ({$quote}customfield1{$quote}.{$quote}value{$quote} = '2')" .
                                " and ({$quote}account{$quote}.{$quote}text{$quote} like 'Some Text%')" .
                                " and ({$quote}account{$quote}.{$quote}textarea{$quote} like '%Some description%')" .
                                " and ({$quote}account{$quote}.{$quote}url{$quote} like '%somesite.com%')" .
                                "";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('ownedcustomfield', $leftTables[0]['tableName']);
            $this->assertEquals('customfield',      $leftTables[1]['tableName']);
            $this->assertEquals('ownedcustomfield', $leftTables[2]['tableName']);
            $this->assertEquals('customfield',      $leftTables[3]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Account::makeSubsetOrCountSqlQuery('account', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}account{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}account{$quote} ";
            $compareSubsetSql .= "left join {$quote}ownedcustomfield{$quote} on ";
            $compareSubsetSql .= "{$quote}ownedcustomfield{$quote}.{$quote}id{$quote} = {$quote}account{$quote}.{$quote}dropdown_ownedcustomfield_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}customfield{$quote} on ";
            $compareSubsetSql .= "{$quote}customfield{$quote}.{$quote}id{$quote} = {$quote}ownedcustomfield{$quote}.{$quote}customfield_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ownedcustomfield{$quote} ownedcustomfield1 on ";
            $compareSubsetSql .= "{$quote}ownedcustomfield1{$quote}.{$quote}id{$quote} = {$quote}account{$quote}.{$quote}radio_ownedcustomfield_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}customfield{$quote} customfield1 on ";
            $compareSubsetSql .= "{$quote}customfield1{$quote}.{$quote}id{$quote} = {$quote}ownedcustomfield1{$quote}.{$quote}customfield_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = Account::getSubset($joinTablesAdapter, 0, 5, $where, null);
        }

        /**
         * @depends testAllCustomAttributesAdaptToMetadataFromPostCorrectly
         */
        public function testSomeStandardAttributesBothNotRelatedAndRelated()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test searching contacts where the related account is ABC with account id = 5.
            $fakePostData = array(
                'account'    => array(
                    'id' => 5
                ),
            );
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new Contact(false), 1, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}contact{$quote}.{$quote}account_id{$quote} = 5)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();

            //Add searching on both primary and secondary email address.
            $fakePostData = array(
                'primaryEmail'    => array(
                    'emailAddress' => 'asearch@something.com',
                ),
                'secondaryEmail'    => array(
                    'emailAddress' => 'bsearch@something.com',
                ),
            );
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new Contact(false), 1, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}email{$quote}.{$quote}emailaddress{$quote} like 'asearch@something.com%') ";
            $compareWhere .= "and ({$quote}email1{$quote}.{$quote}emailaddress{$quote} like 'bsearch@something.com%')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('person', $fromTables[0]['tableName']);
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('email', $leftTables[0]['tableName']);
            $this->assertEquals('email', $leftTables[1]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Contact::makeSubsetOrCountSqlQuery('contact', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}contact{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}contact{$quote}, {$quote}person{$quote}) ";
            $compareSubsetSql .= "left join {$quote}email{$quote} on ";
            $compareSubsetSql .= "{$quote}email{$quote}.{$quote}id{$quote} = {$quote}person{$quote}.{$quote}primaryemail_email_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}email{$quote} email1 on ";
            $compareSubsetSql .= "{$quote}email1{$quote}.{$quote}id{$quote} = {$quote}contact{$quote}.{$quote}secondaryemail_email_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= "and {$quote}person{$quote}.{$quote}id{$quote} = {$quote}contact{$quote}.{$quote}person_id{$quote} ";
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSomeStandardAttributesBothNotRelatedAndRelated
         */
        public function testSearchByOwnerId()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test searching contacts where the related account is ABC with account id = 5.
            $fakePostData = array(
                'owner'    => array(
                    'id' => 3
                ),
            );
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new Contact(false), 1, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = 3)";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(2, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('person', $fromTables[0]['tableName']);
            $this->assertEquals('ownedsecurableitem', $fromTables[1]['tableName']);
            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Contact::makeSubsetOrCountSqlQuery('contact', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}contact{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}contact{$quote}, {$quote}person{$quote}, {$quote}ownedsecurableitem{$quote}) ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= "and {$quote}person{$quote}.{$quote}id{$quote} = {$quote}contact{$quote}.{$quote}person_id{$quote} ";
            $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = {$quote}person{$quote}.{$quote}ownedsecurableitem_id{$quote} ";
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSearchByOwnerId
         */
        public function testSearchByRelatedAttributeName()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test searching contacts where the related account is ABC with account id = 5.
            $fakePostData = array(
                'account'    => array(
                    'name' => 'abc'
                ),
            );
            $metadataAdapter     = new SearchDataProviderMetadataAdapter(new Contact(false), 1, $fakePostData);
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $where        = RedBeanModelDataProvider::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}account{$quote}.{$quote}name{$quote} like 'abc%')";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('account', $leftTables[0]['tableName']);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Contact', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testSearchByRelatedAttributeName
         */
        public function testSearchByRelatedAttributeRelatedTableThatFromJoins()
        {
            //search by contact -> account -> createdDate, modified date
            //todo: this is not supported yet in RedBeanModelDataProvider
        }

        /**
         * @depends testSearchByRelatedAttributeRelatedTableThatFromJoins
         */
        public function testSearchByRelatedAttributeThatHasRelatedTableButShouldNotLeftJoin()
        {
            //search by contact -> account -> owner.
            //todo: this is not supported yet in RedBeanModelDataProvider
        }

        //todo: add many_many, has_many distinct tests
    }
?>
