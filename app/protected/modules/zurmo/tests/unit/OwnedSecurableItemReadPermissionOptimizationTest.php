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

    global $freeze;

    if (!$freeze)
    {
        // These tests rely on the model ids being certain values, which relies
        // on it running on new tables, which is not the case when freezing.

        class OwnedSecurableItemReadPermissionOptimizationTest extends BaseTest
        {
            public static function setUpBeforeClass()
            {
                parent::setUpBeforeClass();
                ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
                SecurityTestHelper::createSuperAdmin();
                SecurityTestHelper::createUsers();
                SecurityTestHelper::createGroups();
                SecurityTestHelper::createRoles();
                RedBeanModel::forgetAll();
                //do the rebuild to ensure the tables get created properly.
                ReadPermissionsOptimizationUtil::rebuild();
                //Manually build the test model munge tables.
                ReadPermissionsOptimizationUtil::recreateTable(ReadPermissionsOptimizationUtil::getMungeTableName('OwnedSecurableTestItem'));
                ReadPermissionsOptimizationUtil::recreateTable(ReadPermissionsOptimizationUtil::getMungeTableName('OwnedSecurableTestItem2'));

                $benny = User::getByUsername('benny');

                $model = new OwnedSecurableTestItem();
                $model->member = 'test';
                assert($model->save()); // Not Coding Standard
                $model = new OwnedSecurableTestItem();
                $model->member = 'test2';
                assert($model->save()); // Not Coding Standard
                $model = new OwnedSecurableTestItem();
                $model->member = 'test3';
                $model->owner  = $benny;
                assert($model->save()); // Not Coding Standard
                assert(count(OwnedSecurableTestItem::getAll()) == 3); // Not Coding Standard
                $model = new OwnedSecurableTestItem2();
                $model->member = 'test5';
                assert($model->save()); // Not Coding Standard
            }

            public function setUp()
            {
                parent::setUp();
                Yii::app()->user->userModel = User::getByUsername('super');
            }

            public function testSuperAdminUserGetAllMakeSubsetOrCountSqlQuery()
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                //Query should not have distinct.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, null, null);
                $compareSubsetSql  = "select {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from {$quote}ownedsecurabletestitem{$quote} ";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //Make sure the sql runs properly.
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(3, count($data));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $this->assertEquals(3, OwnedSecurableTestItem::getCount($joinTablesAdapter));
            }

            public function testMungeForRegularUserMakeSubsetOrCountSqlQuery()
            {
                $quote = DatabaseCompatibilityUtil::getQuote();
                //regular user no elevation
                $benny = User::getByUsername('benny');
                Yii::app()->user->userModel = $benny;
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $mungeIds = ReadPermissionsOptimizationUtil::getMungeIdsByUser($benny);
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, null, null);
                $compareSubsetSql  = "select distinct {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from ({$quote}ownedsecurabletestitem{$quote}, {$quote}ownedsecurableitem{$quote}) ";
                $compareSubsetSql .= "left join {$quote}ownedsecurabletestitem_read{$quote} on ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem_read{$quote}.{$quote}securableitem_id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurableitem{$quote}.{$quote}securableitem_id{$quote} ";
                $compareSubsetSql .= "and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "') ";
                $compareSubsetSql .= "where ({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = $benny->id ";
                $compareSubsetSql .= "OR {$quote}ownedsecurabletestitem_read{$quote}.{$quote}munge_id{$quote} IS NOT NULL) ";  // Not Coding Standard
                $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem{$quote}.{$quote}ownedsecurableitem_id{$quote}";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //Make sure the sql runs properly.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(1, count($data));
                $this->assertTrue($data[0]->owner->isSame($benny));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $this->assertEquals(1, OwnedSecurableTestItem::getCount($joinTablesAdapter));

                //Add in a where clause and make sure the query still works.
                $metadataAdapter = new SearchDataProviderMetadataAdapter(new OwnedSecurableTestItem(), $benny->id, array('member' => 'test'));
                $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $where = RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $compareWhere = "({$quote}ownedsecurabletestitem{$quote}.{$quote}member{$quote} like lower('test%'))";
                $this->assertEquals($compareWhere, $where);
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, $where, null);
                $compareSubsetSql  = "select distinct {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from ({$quote}ownedsecurabletestitem{$quote}, {$quote}ownedsecurableitem{$quote}) ";
                $compareSubsetSql .= "left join {$quote}ownedsecurabletestitem_read{$quote} on ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem_read{$quote}.{$quote}securableitem_id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurableitem{$quote}.{$quote}securableitem_id{$quote} ";
                $compareSubsetSql .= "and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "') ";
                $compareSubsetSql .= "where ($compareWhere) and ({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = $benny->id ";
                $compareSubsetSql .= "OR {$quote}ownedsecurabletestitem_read{$quote}.{$quote}munge_id{$quote} IS NOT NULL) "; // Not Coding Standard
                $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem{$quote}.{$quote}ownedsecurableitem_id{$quote}";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //now run query to make sure it actually works.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $where = RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(1, count($data));
                $this->assertTrue($data[0]->owner->isSame($benny));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $this->assertEquals(1, OwnedSecurableTestItem::getCount($joinTablesAdapter));

                //Now give Benny READ on the module. After that he should not need the munge query.
                Yii::app()->user->userModel = User::getByUsername('super');
                $item       = NamedSecurableItem::getByName('ZurmoModule');
                $item->addPermissions($benny, Permission::READ);
                $this->assertTrue($item->save());
                Yii::app()->user->userModel = User::getByUsername('benny');

                //There should not be a 'distinct' in the query or the munge part.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, null, null);
                $compareSubsetSql  = "select {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from {$quote}ownedsecurabletestitem{$quote} ";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //Make sure the sql runs properly.
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(3, count($data));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $this->assertEquals(3, OwnedSecurableTestItem::getCount($joinTablesAdapter));

               //make sure adding a where query is ok without any munge query parts
                $metadataAdapter = new SearchDataProviderMetadataAdapter(new OwnedSecurableTestItem(), $benny->id, array('member' => 'test'));
                $searchAttributeData = $metadataAdapter->getAdaptedMetadata();
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $where = RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $compareWhere = "({$quote}ownedsecurabletestitem{$quote}.{$quote}member{$quote} like lower('test%'))";
                $this->assertEquals($compareWhere, $where);
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, $where, null);
                $compareSubsetSql  = "select {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from {$quote}ownedsecurabletestitem{$quote} ";
                $compareSubsetSql .= "where $compareWhere";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //now run query to make sure it actually works.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(3, count($data));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $where = RedBeanModelDataProvider::makeWhere('OwnedSecurableTestItem', $searchAttributeData, $joinTablesAdapter);
                $this->assertEquals(3, OwnedSecurableTestItem::getCount($joinTablesAdapter));

                //Now remove READ ALLOW for BENNY, and Add READ DENY
                Yii::app()->user->userModel = User::getByUsername('super');
                $item       = NamedSecurableItem::getByName('ZurmoModule');
                $item->removePermissions($benny, Permission::READ);
                $this->assertTrue($item->save());
                $item->addPermissions($benny, Permission::READ, Permission::DENY);
                $this->assertTrue($item->save());
                Yii::app()->user->userModel = User::getByUsername('benny');

                //Now confirm that the query is just on the owner only, no munge.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $mungeIds = ReadPermissionsOptimizationUtil::getMungeIdsByUser($benny);
                $subsetSql = OwnedSecurableTestItem::makeSubsetOrCountSqlQuery('ownedsecurabletestitem',
                                                        $joinTablesAdapter, 1, 5, null, null);
                $compareSubsetSql  = "select {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= "from ({$quote}ownedsecurabletestitem{$quote}, {$quote}ownedsecurableitem{$quote}) ";
                $compareSubsetSql .= "where {$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = $benny->id ";
                $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem{$quote}.{$quote}ownedsecurableitem_id{$quote}";
                $compareSubsetSql .= ' limit 5 offset 1';
                $this->assertEquals($compareSubsetSql, $subsetSql);
                //Make sure the sql runs properly.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $data = OwnedSecurableTestItem::getSubset($joinTablesAdapter, 0, 5, null, null);
                $this->assertEquals(1, count($data));
                $this->assertTrue($data[0]->owner->isSame($benny));
                //Check using the getCount method produces the same results.
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('OwnedSecurableTestItem');
                $this->assertEquals(1, OwnedSecurableTestItem::getCount($joinTablesAdapter));
            }

            public function testUnionQuery()
            {
                //regular user no elevation
                $user = new User();
                $user->username     = 'aaa';
                $user->firstName    = 'aaa';
                $user->lastName     = 'aaa';
                $saved = $user->save();
                $this->assertTrue($saved);
                $aUser = User::getByUsername('aaa');
                Yii::app()->user->userModel = $aUser;
                $mungeIds = ReadPermissionsOptimizationUtil::getMungeIdsByUser($aUser);

                $model = new OwnedSecurableTestItem();
                $model->member = 'test4';
                $model->owner  = $aUser;
                $this->assertTrue($model->save());
                Yii::app()->user->userModel = User::getByUsername('super');
                $this->assertEquals(4, count(OwnedSecurableTestItem::getAll()));
                $model = new OwnedSecurableTestItem2();
                $model->member = 'test4';
                $model->owner  = $aUser;
                $this->assertTrue($model->save());
                $this->assertEquals(2, count(OwnedSecurableTestItem2::getAll()));
                Yii::app()->user->userModel = User::getByUsername('aaa');

                $quote        = DatabaseCompatibilityUtil::getQuote();

                //Test search attribute data across multiple models.
                $aFakePost = array('member' => 'test4');
                $aMetadataAdapter = new SearchDataProviderMetadataAdapter(new OwnedSecurableTestItem(false),  $aUser->id, $aFakePost);
                $bFakePost = array('member' => 'test4');
                $bMetadataAdapter = new SearchDataProviderMetadataAdapter(new OwnedSecurableTestItem2(false), $aUser->id, $bFakePost);
                $modelClassNamesAndSearchAttributeData = array(
                    array('OwnedSecurableTestItem'  => $aMetadataAdapter->getAdaptedMetadata()),
                    array('OwnedSecurableTestItem2' => $bMetadataAdapter->getAdaptedMetadata()),
                );
                $unionSql     = RedBeanModelsDataProvider::makeUnionSql($modelClassNamesAndSearchAttributeData,
                                                                        null, false, 2, 7);
                $compareWhere = "({$quote}ownedsecurabletestitem{$quote}.{$quote}member{$quote} like lower('test4%'))";
                $compareWhere2 = "({$quote}ownedsecurabletestitem2{$quote}.{$quote}member{$quote} like lower('test4%'))";
                $compareSubsetSql  = "(";
                $compareSubsetSql .= "select distinct {$quote}ownedsecurabletestitem{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= ", 'OwnedSecurableTestItem' modelClassName ";
                $compareSubsetSql .= "from ({$quote}ownedsecurabletestitem{$quote}, {$quote}ownedsecurableitem{$quote}) ";
                $compareSubsetSql .= "left join {$quote}ownedsecurabletestitem_read{$quote} on ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem_read{$quote}.{$quote}securableitem_id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurableitem{$quote}.{$quote}securableitem_id{$quote} ";
                $compareSubsetSql .= "and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "') ";
                $compareSubsetSql .= "where ($compareWhere) and ({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = $aUser->id ";
                $compareSubsetSql .= "OR {$quote}ownedsecurabletestitem_read{$quote}.{$quote}munge_id{$quote} IS NOT NULL) "; // Not Coding Standard
                $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem{$quote}.{$quote}ownedsecurableitem_id{$quote}";
                $compareSubsetSql .= ") ";
                $compareSubsetSql .= "UNION (";
                $compareSubsetSql .= "select distinct {$quote}ownedsecurabletestitem2{$quote}.{$quote}id{$quote} id ";
                $compareSubsetSql .= ", 'OwnedSecurableTestItem2' modelClassName ";
                $compareSubsetSql .= "from ({$quote}ownedsecurabletestitem2{$quote}, {$quote}ownedsecurableitem{$quote}) ";
                $compareSubsetSql .= "left join {$quote}ownedsecurabletestitem2_read{$quote} on ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem2_read{$quote}.{$quote}securableitem_id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurableitem{$quote}.{$quote}securableitem_id{$quote} ";
                $compareSubsetSql .= "and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "') ";
                $compareSubsetSql .= "where ($compareWhere2) and ({$quote}ownedsecurableitem{$quote}.{$quote}owner__user_id{$quote} = $aUser->id ";
                $compareSubsetSql .= "OR {$quote}ownedsecurabletestitem2_read{$quote}.{$quote}munge_id{$quote} IS NOT NULL) "; // Not Coding Standard
                $compareSubsetSql .= "and {$quote}ownedsecurableitem{$quote}.{$quote}id{$quote} = ";
                $compareSubsetSql .= "{$quote}ownedsecurabletestitem2{$quote}.{$quote}ownedsecurableitem_id{$quote}";
                $compareSubsetSql .= ") ";
                $compareSubsetSql .= 'limit 7 offset 2';
                $this->assertEquals($compareSubsetSql, $unionSql);

                //Make sure the sql runs properly.
                $dataProvider = new RedBeanModelsDataProvider('anId', null, false, $modelClassNamesAndSearchAttributeData);
                $data = $dataProvider->getData();
                //Test results are correct
                $this->assertEquals(2, count($data));
                $this->assertEquals('OwnedSecurableTestItem', get_class($data[0]));
                $this->assertEquals('OwnedSecurableTestItem2', get_class($data[1]));

                //Make sure union count query produces the same count.
                $this->assertEquals(2, $dataProvider->calculateTotalItemCount());
            }
        }
    }
?>
