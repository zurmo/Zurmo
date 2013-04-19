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
     * This test class tests various relationship scenarios where there are 2 relationships pointing to the same model.
     * For example Model A has a relationship b1 that goes to B and also has a relationship b2 that goes to B.  This
     * presents new challenges that are tested in this class.
     */
    class RedBeanModelMulitpleSameModelRelationsTest extends BaseTest
    {
        public function testMultipleHasOnesToTheSameModel()
        {
            $pp1       = new PP();
            $pp1->name = 'pp1';
            $pp1->save();
            $this->assertTrue($pp1->save());
            $pp2       = new PP();
            $pp2->name = 'pp2';
            $this->assertTrue($pp2->save());
            $pp3       = new PP();
            $pp3->name = 'pp3';
            $this->assertTrue($pp3->save());

            $p       = new P();
            $p->name = 'name';
            $p->pp   = $pp1;
            $p->pp1  = $pp2;
            $p->pp2  = $pp3;
            $this->assertTrue($p->save());

            //Retrieve row to make sure columns are coppect
            $row = R::getRow('select * from p');
            $this->assertTrue(isset($row['id']) && $row['id'] = $p->id);
            $this->assertTrue(isset($row['pp_id']) && $row['pp_id'] = $pp1->id);
            $this->assertTrue(isset($row['pp1link_pp_id']) && $row['pp1link_pp_id'] = $pp2->id);
            $this->assertTrue(isset($row['pp2link_pp_id']) && $row['pp2link_pp_id'] = $pp3->id);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from pp');
            $this->assertTrue(isset($row['id']) && $row['id'] = $pp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'pp1');
            $this->assertCount(2, $row);
        }

        public function testMultipleHasManysToTheSameModel()
        {
            $ppp1       = new PPP();
            $ppp1->name = 'ppp1';
            $ppp1->save();
            $this->assertTrue($ppp1->save());
            $ppp2       = new PPP();
            $ppp2->name = 'ppp2';
            $this->assertTrue($ppp2->save());
            $ppp3       = new PPP();
            $ppp3->name = 'ppp3';
            $this->assertTrue($ppp3->save());

            $p        = new P();
            $p->name  = 'name2';
            $p->ppp->add ($ppp1);
            $p->ppp1->add($ppp2);
            $p->ppp2->add($ppp3);
            $this->assertTrue($p->save());

            //Retrieve row to make sure columns are correct
            $row = R::getRow('select * from p where id =' . $p->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $p->id);
            $this->assertEquals(null, $row['pp_id']);
            $this->assertEquals(null, $row['pp1link_pp_id']);
            $this->assertEquals(null, $row['pp2link_pp_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp1->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp1');
            $this->assertTrue(isset($row['p_id']) && $row['p_id'] = $p->id);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp2->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp2->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp2');
            $this->assertEquals(null, $row['p_id']);
            $this->assertTrue(isset($row['ppp1link_p_id']) && $row['ppp1link_p_id'] = $p->id);
            $this->assertEquals(null, $row['ppp2link_p_id']);
            $this->assertCount(5, $row);

            $row = R::getRow('select * from ppp where id =' . $ppp3->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp3->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp3');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertTrue(isset($row['ppp2link_p_id']) && $row['ppp2link_p_id'] = $p->id);
            $this->assertCount(5, $row);

            $pId    = $p->id;
            $ppp1Id = $ppp1->id;
            $ppp2Id = $ppp2->id;
            $ppp3Id = $ppp3->id;
            $p->forget();
            $ppp1->forget();
            $ppp2->forget();
            $ppp3->forget();

            $p      = P::getById($pId);
            $this->assertEquals(1, $p->ppp->count());
            $this->assertEquals(1, $p->ppp1->count());
            $this->assertEquals(1, $p->ppp2->count());
            $this->assertEquals($ppp1Id, $p->ppp[0]->id);
            $this->assertEquals($ppp2Id, $p->ppp1[0]->id);
            $this->assertEquals($ppp3Id, $p->ppp2[0]->id);

            //Unlink relationships to make sure they are removed properly
            $p->ppp->remove(PPP::getById($ppp1Id));
            $p->ppp1->remove(PPP::getById($ppp2Id));
            $p->ppp2->remove(PPP::getById($ppp3Id));
            $saved = $p->save();
            $this->assertTrue($saved);

            //test rows are empty..
            $row = R::getRow('select * from ppp where id =' . $ppp1->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp1->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp1');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);

            $row = R::getRow('select * from ppp where id =' . $ppp2->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp2->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp2');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);

            $row = R::getRow('select * from ppp where id =' . $ppp3->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $ppp3->id);
            $this->assertTrue(isset($row['name']) && $row['name'] = 'ppp3');
            $this->assertEquals(null, $row['p_id']);
            $this->assertEquals(null, $row['ppp1link_p_id']);
            $this->assertEquals(null, $row['ppp2link_p_id']);
        }

        public function testAssumptiveLinkWithARelationNameThatIsNotTheModelClassName()
        {
            $pp1       = new PP();
            $pp1->name = 'pp1';
            $pp1->save();
            $this->assertTrue($pp1->save());
            $pp2       = new PP();
            $pp2->name = 'pp2';
            $this->assertTrue($pp2->save());

            $pppp               = new PPPP();
            $pppp->name         = 'name1';
            $pppp->ppAssumptive = $pp1;
            $pppp->pp1          = $pp2;
            $this->assertTrue($pppp->save());

            //Retrieve row to make sure columns are correct
            $row = R::getRow('select * from pppp where id =' . $pppp->id);
            $this->assertTrue(isset($row['id']) && $row['id'] = $pppp->id);
            $this->assertEquals($pp1->id, $row['pp_id']);
            $this->assertEquals($pp2->id, $row['pp1link_pp_id']);
            $this->assertCount(4, $row);
        }

        /**
         * Test that search where clause is generated properly when the assumptive link is for a relation
         * with a different name than the model class name
         * @depends testAssumptiveLinkWithARelationNameThatIsNotTheModelClassName
         */
        public function testSearchQueryFormulatesProperlyForAssumptiveLink()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'ppAssumptive',
                    'relatedModelData' => array(
                        'attributeName'     => 'name',
                        'operatorType'      => 'equals',
                        'value'             => 'somevalue',
                    ),
                ),
                2 => array(
                    'attributeName' => 'pp1',
                    'relatedModelData' => array(
                        'attributeName'     => 'name',
                        'operatorType'      => 'equals',
                        'value'             => 'somevalue2',
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('PPPP');
            $where             = ModelDataProviderUtil::makeWhere('PPPP', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}pp{$quote}.{$quote}name{$quote} = 'somevalue') and ";
            $compareWhere     .= "({$quote}pp1{$quote}.{$quote}name{$quote} = 'somevalue2')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('pp', $leftTables[0]['tableName']);
            $this->assertEquals('pp', $leftTables[1]['tableName']);

            //Only stringing hasOne relations together so it makes sense not to need distinct
            $this->assertFalse($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = AAA::makeSubsetOrCountSqlQuery('pppp', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select {$quote}pppp{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}pppp{$quote} ";
            $compareSubsetSql .= "left join {$quote}pp{$quote} on ";
            $compareSubsetSql .= "{$quote}pp{$quote}.{$quote}id{$quote} = {$quote}pppp{$quote}.{$quote}pp_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}pp{$quote} pp1 on ";
            $compareSubsetSql .= "{$quote}pp1{$quote}.{$quote}id{$quote} = {$quote}pppp{$quote}.{$quote}pp1link_pp_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = PPPP::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        public function testMultipleManyManysToTheSameModel()
        {
            $pp1       = new PP();
            $pp1->name = 'pp1a';
            $pp1->save();
            $this->assertTrue($pp1->save());
            $pp1Id = $pp1->id;
            $pp2       = new PP();
            $pp2->name = 'pp2a';
            $this->assertTrue($pp2->save());
            $pp2Id = $pp2->id;

            $p       = new P();
            $p->name = 'manyNames';
            $p->ppManyAssumptive->add($pp1);
            $p->ppManySpecific->add($pp2);
            $this->assertTrue($p->save());
            $pId = $p->id;

            //Retrieve row to make sure columns are coppect
            $row = R::getRow('select * from p');
            $this->assertCount(5, $row);

            $this->assertEquals(1, R::count('p_pp'));
            $row = R::getRow('select * from p_pp');
            $this->assertTrue(isset($row['p_id']) && $row['p_id'] = $p->id);
            $this->assertTrue(isset($row['pp_id']) && $row['pp_id'] = $pp1->id);
            $this->assertCount(3, $row);

            $this->assertEquals(1, R::count('ppmanyspecificlink_p_pp'));
            $row = R::getRow('select * from ppmanyspecificlink_p_pp');
            $this->assertTrue(isset($row['p_id']) && $row['p_id'] = $p->id);
            $this->assertTrue(isset($row['pp_id']) && $row['pp_id'] = $pp2->id);
            $this->assertCount(3, $row);

            //Unlink and make sure the tables are cleared
            $p->forget();
            $pp1->forget();
            $pp2->forget();

            $p   = P::getById($pId);
            $this->assertEquals(1, $p->ppManyAssumptive->count());
            $this->assertEquals(1, $p->ppManySpecific->count());
            $p->ppManyAssumptive->removeAll();
            $p->ppManySpecific->removeAll();
            $this->assertTrue($p->save());

            $this->assertEquals(0, R::count('p_pp'));
            $this->assertEquals(0, R::count('ppmanyspecificlink_p_pp'));
        }

        public function testMultipleManyManysToTheSameModelSearchQueryFormsCorrectly()
        {
            $quote               = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName' => 'ppManyAssumptive',
                    'relatedModelData' => array(
                        'attributeName'     => 'name',
                        'operatorType'      => 'equals',
                        'value'             => 'somevalue',
                    ),
                ),
                2 => array(
                    'attributeName' => 'ppManySpecific',
                    'relatedModelData' => array(
                        'attributeName'     => 'name',
                        'operatorType'      => 'equals',
                        'value'             => 'somevalue2',
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            //Build the query 'where' and 'joins'. Confirm they are as expected
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('P');
            $where             = ModelDataProviderUtil::makeWhere('P', $searchAttributeData, $joinTablesAdapter);
            $compareWhere      = "({$quote}pp{$quote}.{$quote}name{$quote} = 'somevalue') and ";
            $compareWhere     .= "({$quote}pp1{$quote}.{$quote}name{$quote} = 'somevalue2')";
            $this->assertEquals($compareWhere, $where);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTables = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals('p_pp',                    $leftTables[0]['tableName']);
            $this->assertEquals('pp',                      $leftTables[1]['tableName']);
            $this->assertEquals('ppmanyspecificlink_p_pp', $leftTables[2]['tableName']);
            $this->assertEquals('pp',                      $leftTables[3]['tableName']);

            //Distinct because MANY_MANY relationships
            $this->assertTrue($joinTablesAdapter->getSelectDistinct());

            //Now test that the subsetSQL query produced is correct.
            $subsetSql = AAA::makeSubsetOrCountSqlQuery('p', $joinTablesAdapter, 1, 5, $where,
                                                        null, false, $joinTablesAdapter->getSelectDistinct());
            $compareSubsetSql  = "select distinct {$quote}p{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= "from {$quote}p{$quote} ";
            $compareSubsetSql .= "left join {$quote}p_pp{$quote} on ";
            $compareSubsetSql .= "{$quote}p_pp{$quote}.{$quote}p_id{$quote} = {$quote}p{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}pp{$quote} on ";
            $compareSubsetSql .= "{$quote}pp{$quote}.{$quote}id{$quote} = {$quote}p_pp{$quote}.{$quote}pp_id{$quote} ";
            $compareSubsetSql .= "left join {$quote}ppmanyspecificlink_p_pp{$quote} on ";
            $compareSubsetSql .= "{$quote}ppmanyspecificlink_p_pp{$quote}.{$quote}p_id{$quote} = {$quote}p{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "left join {$quote}pp{$quote} pp1 on ";
            $compareSubsetSql .= "{$quote}pp1{$quote}.{$quote}id{$quote} = {$quote}ppmanyspecificlink_p_pp{$quote}.{$quote}pp_id{$quote} ";
            $compareSubsetSql .= "where " . $compareWhere . ' ';
            $compareSubsetSql .= 'limit 5 offset 1';
            $this->assertEquals($compareSubsetSql, $subsetSql);
            //Make sure the sql runs properly.
            $data = P::getSubset($joinTablesAdapter, 0, 5, $where, null, null, $joinTablesAdapter->getSelectDistinct());
        }

        public function testMultipleBelongsToTheSameModel()
        {
            //HAS_MANY_BELONGS_TO
            //Todo: later - either remove it or add specific link support. Not sure it is needed anymore since we can
            //define the link type.
        }

        public function testAreRelationsValidWithOnlyOneAssumptiveLinkAgainstASingleModel()
        {
            //Todo later:  Need to make some validity checker that works when debug is on during RedBean hit/cache setup
            //that makes sure you don't have more than one assumptive link to the same model.
        }

        public function testMoveThisSomewhereElse()
        {
            //Todo later:
            //RedBeanModel::LINK_TYPE_POLYMORPHIC;
            //Currently there is no way to query from the other side of a polymorphic relationship.  Currently that
            //use case is limited or non-existent.  Add as needed.
        }

        public function testHasOneBelongsToSupportsSpecificLinks()
        {
            //Todo later:
            //We don't have any real usage of HAS_ONE_BELONGS_TO yet.  So until we need to support specific links
            //this is on hold.
        }
    }
?>
