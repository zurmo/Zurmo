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
     * Zurmo models RedBeanModelDataProvider Documentation test.  Demonstrates the different scenarios of search.
     */
    class ZurmoModelsDataProviderDocumentationTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetAllModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user1 = UserTestHelper::createBasicUser('Billy');

            $user2 = UserTestHelper::createBasicUser('Dicky');

            $dataProvider = new RedBeanModelDataProvider('User');
            $users = $dataProvider->getData();
            $this->assertEquals(3, count($users)); // Including 'super'.
        }

        /**
         * @depends testGetAllModels
         */
        public function testSortingModels()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $dataProvider = new RedBeanModelDataProvider('User', 'username');
            $users = $dataProvider->getData();
            $this->assertEquals(3, count($users));
            $this->assertEquals('billy',  $users[0]->username);
            $this->assertEquals('dicky',  $users[1]->username);
            $this->assertEquals('super', $users[2]->username);

            $dataProvider = new RedBeanModelDataProvider('User', 'username', false);
            $users = $dataProvider->getData();
            $this->assertEquals(3, count($users));
            $this->assertEquals('billy',  $users[0]->username);
            $this->assertEquals('dicky',  $users[1]->username);
            $this->assertEquals('super', $users[2]->username);

            $dataProvider = new RedBeanModelDataProvider('User', 'username', true);
            $users = $dataProvider->getData();
            $this->assertEquals(3, count($users));
            $this->assertEquals('super', $users[0]->username);
            $this->assertEquals('dicky',  $users[1]->username);
            $this->assertEquals('billy',  $users[2]->username);
        }

       /**
         * @depends testSortingModels
         */
        public function testSortingRelatedOwnerAttribute()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $quote = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $orderByColumnName = RedBeanModelDataProvider::resolveSortAttributeColumnName('Account', $joinTablesAdapter, 'owner');
            $this->assertEquals("{$quote}person{$quote}.{$quote}lastname{$quote}", $orderByColumnName);

            $leftTablesAndAliases = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('person',    $leftTablesAndAliases[1]['tableName']);
            $this->assertEquals('person',    $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('id',        $leftTablesAndAliases[1]['tableJoinIdName']);
            $this->assertEquals('_user',     $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('person_id', $leftTablesAndAliases[1]['onTableJoinIdName']);

            $subsetSql = Account::makeSubsetOrCountSqlQuery('account', $joinTablesAdapter, 1, 5, null, $orderByColumnName);
            $compareSubsetSql  = "select {$quote}account{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}account{$quote}, {$quote}ownedsecurableitem{$quote}) ";
            $compareSubsetSql .= "left join {$quote}_user{$quote} on ";
            $compareSubsetSql .= "{$quote}_user$quote.{$quote}id{$quote} = {$quote}ownedsecurableitem$quote.{$quote}owner__user_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}person{$quote} on ";
            $compareSubsetSql .= "{$quote}person$quote.{$quote}id{$quote} = {$quote}_user$quote.{$quote}person_id{$quote} ";
            $compareSubsetSql .= " where {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
            $compareSubsetSql .= "{$quote}account{$quote}.{$quote}ownedsecurableitem_id{$quote} ";
            $compareSubsetSql .= "order by {$quote}person{$quote}.{$quote}lastname{$quote} limit 5 offset 1";
            $this->assertEquals($compareSubsetSql, $subsetSql);
        }

       /**
         * @depends testSortingRelatedOwnerAttribute
         */
        public function testMakeWhereAndGetSubset()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => null,
                    'officeFax'     => null,
                    'employees'     => null,
                    'annualRevenue' => null,
                    'website'       => null,
                    'billingAddress' => array(
                        'street1'    => null,
                        'street2'    => null,
                        'city'       => null,
                        'postalCode' => null,
                        'country'    => null,
                    ),
                    'description' => null,
                ),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $quote = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote like lower('Vomitorio Corp%'))", $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Now add to the search with officePhone.
            $_FAKEPOST['Account']['officePhone'] = '123456789';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $this->assertEquals("({$quote}account$quote.{$quote}name$quote like lower('Vomitorio Corp%')) and " .
                                "({$quote}account$quote.{$quote}officephone$quote like lower('123456789%'))",
                                $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Now add to the search with billingAddress - country. This now tests related information, which utilizes
            //joins in the query.
            $_FAKEPOST['Account']['billingAddress']['country'] = 'Countralia';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}account$quote.{$quote}name$quote like lower('Vomitorio Corp%')) and " .
                                "({$quote}account$quote.{$quote}officephone$quote like lower('123456789%')) and " .
                                "({$quote}address$quote.{$quote}country$quote like lower('Countralia%'))";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('address', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Account::makeSubsetOrCountSqlQuery('account', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}account{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}account{$quote} ";
            $compareSubsetSql .= "left join {$quote}address{$quote} on ";
            $compareSubsetSql .= "{$quote}address$quote.{$quote}id{$quote} = {$quote}account$quote.{$quote}billingaddress_address_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //Add another related attribute, billing address city. This will still have one single left join, but the
            //where part should have two separate clauses, one for city and the other for country.
            $_FAKEPOST['Account']['billingAddress']['city'] = 'Buffalo Grove';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}account$quote.{$quote}name$quote like lower('Vomitorio Corp%')) and " .
                                "({$quote}account$quote.{$quote}officephone$quote like lower('123456789%')) and " .
                                "({$quote}address$quote.{$quote}city$quote like lower('Buffalo Grove%')) and " .
                                "({$quote}address$quote.{$quote}country$quote like lower('Countralia%'))";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('address', $leftTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Account::makeSubsetOrCountSqlQuery('account', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}account{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}account{$quote} ";
            $compareSubsetSql .= "left join {$quote}address{$quote} on ";
            $compareSubsetSql .= "{$quote}address$quote.{$quote}id{$quote} = {$quote}account$quote.{$quote}billingaddress_address_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);

            //todo: add shipping address into the equation.
            $_FAKEPOST['Account']['shippingAddress']['city'] = 'Chicago';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Account');
            $where = RedBeanModelDataProvider::makeWhere('Account', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}account$quote.{$quote}name$quote like lower('Vomitorio Corp%')) and " .
                                "({$quote}account$quote.{$quote}officephone$quote like lower('123456789%')) and " .
                                "({$quote}address$quote.{$quote}city$quote like lower('Buffalo Grove%')) and " .
                                "({$quote}address$quote.{$quote}country$quote like lower('Countralia%')) and " .
                                "({$quote}address1$quote.{$quote}city$quote like lower('Chicago%'))";
            $this->assertEquals($compareWhere, $where);

            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('address', $leftTables[0]['tableName']);
            $this->assertEquals('address', $leftTables[1]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Account::makeSubsetOrCountSqlQuery('account', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}account{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}account{$quote} ";
            $compareSubsetSql .= "left join {$quote}address{$quote} on ";
            $compareSubsetSql .= "{$quote}address$quote.{$quote}id{$quote} = {$quote}account$quote.{$quote}billingaddress_address_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}address{$quote} address1 on ";
            $compareSubsetSql .= "{$quote}address1$quote.{$quote}id{$quote} = {$quote}account$quote.{$quote}shippingaddress_address_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('Account', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testMakeWhereAndGetSubset
         */
        public function testMakeWhereWithInheritedAttributes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Test a join that is an inner join. First name is not on the user table, but the person table. All users
            //have a person row, so it should be an inner join.
            $_FAKEPOST = array(
                'User' => array(
                    'firstName' => 'billy',
                ),
            );

            $quote = DatabaseCompatibilityUtil::getQuote();
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new User(false),
                1,
                $_FAKEPOST['User']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            $compareWhere = "({$quote}person$quote.{$quote}firstname$quote like lower('billy%'))";
            $this->assertEquals($compareWhere, $where);
            //Now test that the joinTablesAdapter has correct information.
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            $fromTables = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('person', $fromTables[0]['tableName']);

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = User::makeSubsetOrCountSqlQuery('user', $joinTablesAdapter, 1, 5, $where, null);
            $compareSubsetSql  = "select {$quote}user{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from ({$quote}user{$quote}, {$quote}person{$quote}) ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= "and {$quote}person$quote.{$quote}id{$quote} = {$quote}_user$quote.{$quote}person_id{$quote} ";
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelDataProvider('User', null, false, $searchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testMakeWhereAndGetSubset
         */
        public function testSearch()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array(
                'Account1' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => '123456789',
                    'officeFax'     => null,
                    'employees'     => 123,
                    'annualRevenue' => null,
                    'website'       => 'http://barf.com',
                    'billingAddress' => array(
                        'street1'    => '123 Road Rd',
                        'street2'    => null,
                        'city'       => 'Cityville',
                        'postalCode' => '12345',
                        'country'    => 'Countralia',
                    ),
                    'description' => 'a description',
                ),
                'Account2' => array(
                    'name'          => 'Victorinox',
                    'officePhone'   => '987654321',
                    'officeFax'     => '55566666',
                    'employees'     => 123,
                    'annualRevenue' => null,
                    'website'       => 'http://www.victorinox.com',
                    'description' => 'A pretty sharp bunch.',
                ),
            );

            $user = User::getByUsername('billy');

            $account1 = new Account();
            $account1->owner = $user;
            $account1->setAttributes($_FAKEPOST['Account1']);
            $this->assertTrue($account1->save());

            $account2 = new Account();
            $account2->owner = $user;
            $account2->setAttributes($_FAKEPOST['Account2']);
            $this->assertTrue($account2->save());

            $_FAKEPOST = array(
                'Account' => array(
                    'name'          => 'Vomitorio Corp',
                    'officePhone'   => null,
                    'officeFax'     => null,
                    'employees'     => null,
                    'annualRevenue' => null,
                    'website'       => null,
                    'billingAddress' => array(
                        'street1'    => null,
                        'street2'    => null,
                        'city'       => null,
                        'postalCode' => null,
                        'country'    => null,
                    ),
                    'description' => null,
                ),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(1, count($accounts));
            $this->assertEquals(1, $dataProvider->calculateTotalItemCount());

            $_FAKEPOST['Account']['officePhone'] = '123456';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(1, count($accounts));
            $this->assertEquals(1, $dataProvider->calculateTotalItemCount());

            $_FAKEPOST['Account']['officePhone'] = '5551234567';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(0, count($accounts));
            $this->assertEquals(0, $dataProvider->calculateTotalItemCount());

            $_FAKEPOST['Account']['name']                      = null;
            $_FAKEPOST['Account']['officePhone']               = null;
            $_FAKEPOST['Account']['billingAddress']['country'] = null;
            $_FAKEPOST['Account']['employees']                 = 123;
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(2, count($accounts));
            $this->assertEquals(2, $dataProvider->calculateTotalItemCount());

            $_FAKEPOST['Account']['employees'] = 124;
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(0, count($accounts));
            $this->assertEquals(0, $dataProvider->calculateTotalItemCount());

            $_FAKEPOST['Account']['name']      = 'V';
            $_FAKEPOST['Account']['employees'] = 123;
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(2, count($accounts));
            $this->assertEquals(2, $dataProvider->calculateTotalItemCount());
        }

        /**
         * @depends testSearch
         */
        public function testSearchCrossingTables()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $_FAKEPOST = array();
            $_FAKEPOST['Account']['name'] = 'Victorinox';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(1, count($accounts));
            $this->assertEquals('Victorinox', $accounts[0]->name);
            $accounts[0]->billingAddress->country = 'Countralia';
            $this->assertTrue($accounts[0]->save());

            $_FAKEPOST = array();
            $_FAKEPOST['Account']['officePhone']               = null;
            $_FAKEPOST['Account']['billingAddress']['country'] = 'Countralia';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(2, count($accounts));
            $this->assertEquals(2, $dataProvider->calculateTotalItemCount());
            $this->assertEquals('Victorinox',     $accounts[0]->name);
            $this->assertEquals('Countralia',     $accounts[0]->billingAddress->country);
            $this->assertEquals('Vomitorio Corp', $accounts[1]->name);
            $this->assertEquals('Countralia',     $accounts[1]->billingAddress->country);

            $_FAKEPOST['Account']['billingAddress']['country'] = 'Iran';
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new Account(false),
                1,
                $_FAKEPOST['Account']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('Account', 'name', false, $searchAttributeData);
            $accounts = $dataProvider->getData();
            $this->assertEquals(0, count($accounts));
            $this->assertEquals(0, $dataProvider->calculateTotalItemCount());
        }

        /**
         * @depends testSearchCrossingTables
         * @depends testGetAllModels
         */
        public function testSearchUserFirstName()
        {
            $_FAKEPOST = array(
                'User' => array(
                    'firstName' => 'billy',
                ),
            );
            $metadataAdapter = new SearchDataProviderMetadataAdapter(
                new User(false),
                1,
                $_FAKEPOST['User']
            );
            $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
            $dataProvider = new RedBeanModelDataProvider('User', null, false, $searchAttributeData);
            $users = $dataProvider->getData();
            $this->assertEquals(1, count($users));
            $this->assertEquals(1, $dataProvider->calculateTotalItemCount());
        }
    }
?>
