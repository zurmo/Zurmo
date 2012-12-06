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
     * Test class to illustrate a bug related to nested search from a many-many relationship when searching a
     * dropdown field.  This test which now works, shows that this bug is fixed.
     */
    class SearchOwnedCustomFieldRecursiveDataTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        /**
         * Searching Many To Many on a custom field (dropdown)
         */
        public function testManyManyCustomFieldSearch()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'opportunities',
                        'relatedModelData' => array(
                            'attributeName'     => 'stage',
                            'relatedAttributeName' => 'value',
                            'operatorType'      => 'oneOf',
                            'value'             => array(0 => 'something'),
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Contact');
            $where             = ModelDataProviderUtil::makeWhere('Contact', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}customfield{$quote}.{$quote}value{$quote} IN('something'))";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('contact_opportunity',     $leftTables[0]['tableName']);
            $this->assertEquals('opportunity',             $leftTables[1]['tableName']);
            $this->assertEquals('customfield',             $leftTables[2]['tableName']);
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = Contact::makeSubsetOrCountSqlQuery('contact', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}contact{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}contact{$quote} ";
            $compareSubsetSql .= "left join {$quote}contact_opportunity{$quote} on ";
            $compareSubsetSql .= "{$quote}contact_opportunity{$quote}.{$quote}contact_id{$quote} = {$quote}contact{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}opportunity{$quote} on ";
            $compareSubsetSql .= "{$quote}opportunity{$quote}.{$quote}id{$quote} = {$quote}contact_opportunity{$quote}.{$quote}opportunity_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}customfield{$quote} on ";
            $compareSubsetSql .= "{$quote}customfield{$quote}.{$quote}id{$quote} = {$quote}opportunity{$quote}.{$quote}stage_customfield_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = Contact::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }
    }
?>