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
     * Testing recursive nested relation data.  This would occur if you are searching across multiple models
     * that span multiple relationships.
     * @see SearchDataProviderMetadataAdapterForRecursiveSearchesTest

    Models and relations used in this class

                                III -> hasOne EEE
                                  |
                                  | CCC hasMany III
                                  | III hasOne  CCC
                                CCC -> hasOne EEE
                                  |
                                  | CCC hasMany BBB
         /-> hasOne EEE           | BBB hasOne  CCC
         |                        |
         |                        |/---> BBB hasOne GGG -> hasOne EEE
         |                        ||
         |                        ||
         FFF <-hasOnehasMany ->  BBB <- manyMany -> DDD -> hasOne EEE
                                  |
          FFF hasOne  BBB         | BBB hasMany AAA
          BBB hasMany FFF         | AAA hasOne  BBB
                                  |
                                  |
                                 AAA --- hasOne HHH -> hasOne EEE
                                      HHH hasOneBelongsTo AAA
    **/
    class ModelDataProviderUtilRecursiveDataTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        /**
         * AAA -> hasOne -> BBB -> hasOne -> CCC -> hasOne -> EEE
         */
        public function testHasOneToHasOneToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AAA');
            $where             = ModelDataProviderUtil::makeWhere('AAA', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb', $leftTables[0]['tableName']);
            $this->assertEquals('ccc',      $leftTables[1]['tableName']);
            $this->assertEquals('eee', $leftTables[2]['tableName']);
            //Only stringing hasOne relations together so it makes sense not to need distinct
            $this->assertFalse($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = AAA::makeSubsetOrCountSqlQuery('aaa', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select {$quote}aaa{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}aaa{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}aaa{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ccc{$quote} on ";
            $compareSubsetSql .= "{$quote}ccc{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ccc_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ccc{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = AAA::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

            /**
         * AAA -> hasOne -> BBB -> hasOne -> CCC -> hasOne -> EEE
         * Use relatedAttributeName which should function the same as the previous method.  This is how the search
         * attributes will get converted.
         */
        public function testHasOneToHasOneToHasOneUsingRelatedAttributeName()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ccc',
                            'relatedModelData'  => array(
                                'attributeName'         => 'eee',
                                'relatedAttributeName'  => 'eeeMember',
                                'operatorType'          => 'equals',
                                'value'                 => 'somevalue',
                        ),
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AAA');
            $where             = ModelDataProviderUtil::makeWhere('AAA', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb', $leftTables[0]['tableName']);
            $this->assertEquals('ccc',      $leftTables[1]['tableName']);
            $this->assertEquals('eee', $leftTables[2]['tableName']);
            //Only stringing hasOne relations together so it makes sense not to need distinct
            $this->assertFalse($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = AAA::makeSubsetOrCountSqlQuery('aaa', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select {$quote}aaa{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}aaa{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}aaa{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ccc{$quote} on ";
            $compareSubsetSql .= "{$quote}ccc{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ccc_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ccc{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = AAA::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * AAA -> hasOne -> BBB -> hasMany -> FFF -> hasOne -> EEE
         * @depends testHasOneToHasOneToHasOneUsingRelatedAttributeName
         */
        public function testHasOneToHasManyToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'fff',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AAA');
            $where             = ModelDataProviderUtil::makeWhere('AAA', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb',     $leftTables[0]['tableName']);
            $this->assertEquals('fff',     $leftTables[1]['tableName']);
            $this->assertEquals('eee',     $leftTables[2]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = AAA::makeSubsetOrCountSqlQuery('aaa', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}aaa{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}aaa{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}aaa{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}fff{$quote} on ";
            $compareSubsetSql .= "{$quote}fff{$quote}.{$quote}bbb_id{$quote} = {$quote}bbb{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}fff{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = AAA::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * CCC -> hasMany -> BBB -> ManyMany -> DDD -> hasOne -> EEE
         * @depends testHasOneToHasOneToHasOne
         */
        public function testHasManyToManyManyToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ddd',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('CCC');
            $where             = ModelDataProviderUtil::makeWhere('CCC', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb',     $leftTables[0]['tableName']);
            $this->assertEquals('bbb_ddd', $leftTables[1]['tableName']);
            $this->assertEquals('ddd',     $leftTables[2]['tableName']);
            $this->assertEquals('eee',     $leftTables[3]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('ccc', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}ccc{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}ccc{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}ccc_id{$quote} = {$quote}ccc{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb_ddd{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb_ddd{$quote}.{$quote}bbb_id{$quote} = {$quote}bbb{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ddd{$quote} on ";
            $compareSubsetSql .= "{$quote}ddd{$quote}.{$quote}id{$quote} = {$quote}bbb_ddd{$quote}.{$quote}ddd_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ddd{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = CCC::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * CCC -> hasMany -> BBB -> hasOne -> GGG -> hasOne -> EEE
         * @depends testHasManyToManyManyToHasOne
         */
        public function testHasManyToHasOneToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ggg',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('CCC');
            $where             = ModelDataProviderUtil::makeWhere('CCC', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb',     $leftTables[0]['tableName']);
            $this->assertEquals('ggg',     $leftTables[1]['tableName']);
            $this->assertEquals('eee',     $leftTables[2]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('ccc', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}ccc{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}ccc{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}ccc_id{$quote} = {$quote}ccc{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ggg{$quote} on ";
            $compareSubsetSql .= "{$quote}ggg{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ggg_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ggg{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = CCC::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * DDD -> manyMany -> BBB -> hasMany -> FFF -> hasOne -> EEE
         * @depends testHasManyToHasOneToHasOne
         */
        public function testManyManyToHasManyToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'fff',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('DDD');
            $where             = ModelDataProviderUtil::makeWhere('DDD', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb_ddd', $leftTables[0]['tableName']);
            $this->assertEquals('bbb',     $leftTables[1]['tableName']);
            $this->assertEquals('fff',     $leftTables[2]['tableName']);
            $this->assertEquals('eee',     $leftTables[3]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('ddd', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}ddd{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}ddd{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb_ddd{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb_ddd{$quote}.{$quote}ddd_id{$quote} = {$quote}ddd{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}bbb_ddd{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}fff{$quote} on ";
            $compareSubsetSql .= "{$quote}fff{$quote}.{$quote}bbb_id{$quote} = {$quote}bbb{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}fff{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = DDD::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * DDD -> manyMany -> BBB -> hasOne -> GGG -> hasOne -> EEE
         * @depends testManyManyToHasManyToHasOne
         */
        public function testManyManyToHasOneToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ggg',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('DDD');
            $where             = ModelDataProviderUtil::makeWhere('DDD', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb_ddd', $leftTables[0]['tableName']);
            $this->assertEquals('bbb',     $leftTables[1]['tableName']);
            $this->assertEquals('ggg',     $leftTables[2]['tableName']);
            $this->assertEquals('eee',     $leftTables[3]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('ddd', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}ddd{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}ddd{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb_ddd{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb_ddd{$quote}.{$quote}ddd_id{$quote} = {$quote}ddd{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}bbb_ddd{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ggg{$quote} on ";
            $compareSubsetSql .= "{$quote}ggg{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ggg_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ggg{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = DDD::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

            /**
         * DDD -> manyMany -> BBB -> hasOne -> GGG -> hasOne -> EEE (eeeMember and eeeMember2)
         * @depends testManyManyToHasOneToHasOne
         */
        public function testManyManyToHasOneToHasOneWithMultipleClauses()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ggg',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
                2 => array(
                    'attributeName' => 'bbb',
                    'relatedModelData' => array(
                        'attributeName'     => 'ggg',
                            'relatedModelData'  => array(
                                'attributeName'     => 'eee',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'eeeMember2',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                )
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1 and 2';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('DDD');
            $where             = ModelDataProviderUtil::makeWhere('DDD', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}eee{$quote}.{$quote}eeemember{$quote} = 'somevalue') and ";
            $compareWhere     .= "({$quote}eee{$quote}.{$quote}eeemember2{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('bbb_ddd', $leftTables[0]['tableName']);
            $this->assertEquals('bbb',     $leftTables[1]['tableName']);
            $this->assertEquals('ggg',     $leftTables[2]['tableName']);
            $this->assertEquals('eee',     $leftTables[3]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('ddd', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}ddd{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}ddd{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb_ddd{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb_ddd{$quote}.{$quote}ddd_id{$quote} = {$quote}ddd{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}bbb_ddd{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ggg{$quote} on ";
            $compareSubsetSql .= "{$quote}ggg{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ggg_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}eee{$quote} on ";
            $compareSubsetSql .= "{$quote}eee{$quote}.{$quote}id{$quote} = {$quote}ggg{$quote}.{$quote}eee_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = DDD::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * HHH -> hasOneBelongsTo -> AAA -> hasOne -> BBB -> hasOne GGG
         * @depends testManyManyToHasOneToHasOneWithMultipleClauses
         */
        public function testHasOneBelongsToHasOneToHasOne()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'aaa',
                    'relatedModelData' => array(
                        'attributeName'     => 'bbb',
                            'relatedModelData'  => array(
                                'attributeName'     => 'ggg',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'gggMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('HHH');
            $where             = ModelDataProviderUtil::makeWhere('HHH', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}ggg{$quote}.{$quote}gggmember{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('aaa',     $leftTables[0]['tableName']);
            $this->assertEquals('bbb',     $leftTables[1]['tableName']);
            $this->assertEquals('ggg',     $leftTables[2]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertFalse($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('hhh', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select {$quote}hhh{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}hhh{$quote} ";
            $compareSubsetSql .= "left join {$quote}aaa{$quote} on ";
            $compareSubsetSql .= "{$quote}aaa{$quote}.{$quote}hhh_id{$quote} = {$quote}hhh{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}aaa{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ggg{$quote} on ";
            $compareSubsetSql .= "{$quote}ggg{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ggg_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = HHH::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         * HHH -> hasOneBelongsTo -> AAA -> hasOne -> BBB -> hasOne GGG (gggMember and gggMember2)
         * @depends testHasOneBelongsToHasOneToHasOne
         */
        public function testHasOneBelongsToHasOneToHasOneWithMultipleClauses()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'aaa',
                    'relatedModelData' => array(
                        'attributeName'     => 'bbb',
                            'relatedModelData'  => array(
                                'attributeName'     => 'ggg',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'gggMember',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                ),
                2 => array(
                    'attributeName' => 'aaa',
                    'relatedModelData' => array(
                        'attributeName'     => 'bbb',
                            'relatedModelData'  => array(
                                'attributeName'     => 'ggg',
                                    'relatedModelData' => array(
                                        'attributeName'     => 'gggMember2',
                                        'operatorType'      => 'equals',
                                        'value'             => 'somevalue',
                            ),
                        ),
                    ),
                )
            );
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $searchAttributeData['structure'] = '1 and 2';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('HHH');
            $where             = ModelDataProviderUtil::makeWhere('HHH', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}ggg{$quote}.{$quote}gggmember{$quote} = 'somevalue') and ";
            $compareWhere     .= "({$quote}ggg{$quote}.{$quote}gggmember2{$quote} = 'somevalue')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('aaa',     $leftTables[0]['tableName']);
            $this->assertEquals('bbb',     $leftTables[1]['tableName']);
            $this->assertEquals('ggg',     $leftTables[2]['tableName']);
            //Stringing together some hasMany relations, so we need to select distinct.
            $this->assertFalse($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = CCC::makeSubsetOrCountSqlQuery('hhh', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select {$quote}hhh{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}hhh{$quote} ";
            $compareSubsetSql .= "left join {$quote}aaa{$quote} on ";
            $compareSubsetSql .= "{$quote}aaa{$quote}.{$quote}hhh_id{$quote} = {$quote}hhh{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}bbb{$quote} on ";
            $compareSubsetSql .= "{$quote}bbb{$quote}.{$quote}id{$quote} = {$quote}aaa{$quote}.{$quote}bbb_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ggg{$quote} on ";
            $compareSubsetSql .= "{$quote}ggg{$quote}.{$quote}id{$quote} = {$quote}bbb{$quote}.{$quote}ggg_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = HHH::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }
    }
?>