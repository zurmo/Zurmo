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
     * For data provider work that is not specific to the application. This data provider is used
     * for querying multiple models in a UNION statement.
     * Using I, J, K, L, and H models. I, J, K, and L all extend H.
     */
    class RedBeanModelsDataProviderTest extends DataProviderBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $i = new I();
            $i->iMember = 'iString1';
            $i->name    = 'd';
            assert('$i->save()'); // Not Coding Standard
            $i = new I();
            $i->iMember = 'xString1';
            $i->name    = 'e';
            assert('$i->save()'); // Not Coding Standard
            $i = new I();
            $i->iMember = 'yString1';
            $i->name    = 'f';
            assert('$i->save()'); // Not Coding Standard

            $j = new J();
            $j->jMember = 'jString1';
            $j->name    = 'a';
            assert('$j->save()'); // Not Coding Standard
            $j = new J();
            $j->jMember = 'xString1';
            $j->name    = 'b';
            assert('$j->save()'); // Not Coding Standard
            $j = new J();
            $j->jMember = 'yString1';
            $j->name    = 'c';
            assert('$j->save()'); // Not Coding Standard

            $k = new K();
            $k->kMember = 'kString1';
            $k->name    = 'g';
            assert('$k->save()'); // Not Coding Standard
            $k = new K();
            $k->kMember = 'xString1';
            $k->name    = 'i';
            assert('$k->save()'); // Not Coding Standard
            $k = new K();
            $k->kMember = 'yString1';
            $k->name    = 'h';
            assert('$k->save()'); // Not Coding Standard
        }

        public function testUnionSqlAcrossMultipleModels()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            //Test search attribute data across multiple models.
            $iFakePost = array('iMember' => 'iString');
            $iMetadataAdapter = new SearchDataProviderMetadataAdapter(new I(false), 1, $iFakePost);
            $jFakePost = array('jMember' => 'jString');
            $jMetadataAdapter = new SearchDataProviderMetadataAdapter(new J(false), 1, $jFakePost);
            $kFakePost = array('kMember' => 'kString');
            $kMetadataAdapter = new SearchDataProviderMetadataAdapter(new K(false), 1, $kFakePost);
            $modelClassNamesAndSearchAttributeData = array(
                'I' => $iMetadataAdapter->getAdaptedMetadata(),
                'J' => $jMetadataAdapter->getAdaptedMetadata(),
                'K' => $kMetadataAdapter->getAdaptedMetadata(),
            );
            $unionSql     = RedBeanModelsDataProvider::makeUnionSql($modelClassNamesAndSearchAttributeData,
                                                                    null, false, 2, 7);
            $compareSubsetSql  = "(";
            $compareSubsetSql .= "select {$quote}i{$quote}.{$quote}id{$quote} id , 'I' modelClassName from {$quote}i{$quote} ";
            $compareSubsetSql .= "where ({$quote}i{$quote}.{$quote}imember{$quote} like lower('iString%'))";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}j{$quote}.{$quote}id{$quote} id , 'J' modelClassName from {$quote}j{$quote} ";
            $compareSubsetSql .= "where ({$quote}j{$quote}.{$quote}jmember{$quote} like lower('jString%'))";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}k{$quote}.{$quote}id{$quote} id , 'K' modelClassName from {$quote}k{$quote} ";
            $compareSubsetSql .= "where ({$quote}k{$quote}.{$quote}kmember{$quote} like lower('kString%'))";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= 'limit 7 offset 2';
            $this->assertEquals($compareSubsetSql, $unionSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelsDataProvider('anId', null, false, $modelClassNamesAndSearchAttributeData);
            $data = $dataProvider->getData();
            //Test results are correct
            $this->assertEquals(3, count($data));
            $this->assertEquals('I', get_class($data[0]));
            $this->assertEquals('J', get_class($data[1]));
            $this->assertEquals('K', get_class($data[2]));
        }

        /**
         * @depends testUnionSqlAcrossMultipleModels
         */
        public function testUnionSqlAcrossMultipleModelsOrderByCastedUpModelAttribute()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $iFakePost = array();
            $iMetadataAdapter = new SearchDataProviderMetadataAdapter(new I(false), 1, $iFakePost);
            $jFakePost = array();
            $jMetadataAdapter = new SearchDataProviderMetadataAdapter(new J(false), 1, $jFakePost);
            $kFakePost = array();
            $kMetadataAdapter = new SearchDataProviderMetadataAdapter(new K(false), 1, $kFakePost);
            $modelClassNamesAndSearchAttributeData = array(
                'I' => $iMetadataAdapter->getAdaptedMetadata(),
                'J' => $jMetadataAdapter->getAdaptedMetadata(),
                'K' => $kMetadataAdapter->getAdaptedMetadata(),
            );
            $modelClassNamesAndSortAttributes = array(
                'I' => 'name',
                'J' => 'name',
                'K' => 'name',
            );
            $unionSql     = RedBeanModelsDataProvider::makeUnionSql($modelClassNamesAndSearchAttributeData,
                                                                    $modelClassNamesAndSortAttributes, true, 2, 7);
            $compareSubsetSql  = "(";
            $compareSubsetSql .= "select {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'I' modelClassName , {$quote}h{$quote}.{$quote}name{$quote} orderByColumn ";
            $compareSubsetSql .= "from ({$quote}i{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= " where {$quote}h{$quote}.{$quote}id{$quote} = {$quote}i{$quote}.{$quote}h_id{$quote}";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}j{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'J' modelClassName , {$quote}h{$quote}.{$quote}name{$quote} orderByColumn ";
            $compareSubsetSql .= "from ({$quote}j{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= " where {$quote}h{$quote}.{$quote}id{$quote} = {$quote}j{$quote}.{$quote}h_id{$quote}";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}k{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'K' modelClassName , {$quote}h{$quote}.{$quote}name{$quote} orderByColumn ";
            $compareSubsetSql .= "from ({$quote}k{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= " where {$quote}h{$quote}.{$quote}id{$quote} = {$quote}k{$quote}.{$quote}h_id{$quote}";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "order by orderByColumn desc ";
            $compareSubsetSql .= 'limit 7 offset 2';
            $this->assertEquals($compareSubsetSql, $unionSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelsDataProvider('anId', $modelClassNamesAndSortAttributes, true,
                                                            $modelClassNamesAndSearchAttributeData);
            $data = $dataProvider->getData();
            //Test results are correct
            $this->assertEquals(9, count($data));
            $this->assertEquals('xString1', $data[0]->kMember);
            $this->assertEquals('yString1', $data[1]->kMember);
            $this->assertEquals('kString1', $data[2]->kMember);
            $this->assertEquals('yString1', $data[3]->iMember);
            $this->assertEquals('xString1', $data[4]->iMember);
            $this->assertEquals('iString1', $data[5]->iMember);
            $this->assertEquals('yString1', $data[6]->jMember);
            $this->assertEquals('xString1', $data[7]->jMember);
            $this->assertEquals('jString1', $data[8]->jMember);
        }

        /**
         * @depends testUnionSqlAcrossMultipleModelsOrderByCastedUpModelAttribute
         */
        public function testUnionSqlAcrossMultipleModelsUsingDifferentOrderBys()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $iFakePost = array();
            $iMetadataAdapter = new SearchDataProviderMetadataAdapter(new I(false), 1, $iFakePost);
            $jFakePost = array();
            $jMetadataAdapter = new SearchDataProviderMetadataAdapter(new J(false), 1, $jFakePost);
            $kFakePost = array();
            $kMetadataAdapter = new SearchDataProviderMetadataAdapter(new K(false), 1, $kFakePost);
            $modelClassNamesAndSearchAttributeData = array(
                'I' => $iMetadataAdapter->getAdaptedMetadata(),
                'J' => $jMetadataAdapter->getAdaptedMetadata(),
                'K' => $kMetadataAdapter->getAdaptedMetadata(),
            );
            $modelClassNamesAndSortAttributes = array(
                'I' => 'name',
                'J' => 'jMember',
                'K' => 'kMember',
            );
            $unionSql     = RedBeanModelsDataProvider::makeUnionSql($modelClassNamesAndSearchAttributeData,
                                                                    $modelClassNamesAndSortAttributes, true, 2, 7);
            $compareSubsetSql  = "(";
            $compareSubsetSql .= "select {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'I' modelClassName , {$quote}h{$quote}.{$quote}name{$quote} orderByColumn ";
            $compareSubsetSql .= "from ({$quote}i{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= " where {$quote}h{$quote}.{$quote}id{$quote} = {$quote}i{$quote}.{$quote}h_id{$quote}";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}j{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'J' modelClassName , {$quote}j{$quote}.{$quote}jmember{$quote} orderByColumn ";
            $compareSubsetSql .= "from {$quote}j{$quote} ";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}k{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'K' modelClassName , {$quote}k{$quote}.{$quote}kmember{$quote} orderByColumn ";
            $compareSubsetSql .= "from {$quote}k{$quote} ";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "order by orderByColumn desc ";
            $compareSubsetSql .= 'limit 7 offset 2';
            $this->assertEquals($compareSubsetSql, $unionSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelsDataProvider('anId', $modelClassNamesAndSortAttributes, true,
                                                            $modelClassNamesAndSearchAttributeData);
            $data = $dataProvider->getData();
            //Test results are correct
            $this->assertEquals(9, count($data));
        }

        /**
         * @depends testUnionSqlAcrossMultipleModelsUsingDifferentOrderBys
         */
        public function testUnionSqlAcrossMultipleModelsUsingOneOfFilterWhichMakesSelectDistinct()
        {
            $quote        = DatabaseCompatibilityUtil::getQuote();
            $searchAttributeDataForJ['clauses'][1] = array(
                    'attributeName'        => 'jMember',
                    'operatorType'         => 'oneOf',
                    'value'                => array('a', 'b', 'c')
            );
            $searchAttributeDataForJ['structure'] = '1';
            $searchAttributeDataForI['clauses'][1] = array(
                    'attributeName'        => 'ks',
                    'relatedAttributeName' => 'kMember',
                    'operatorType'         => 'oneOf',
                    'value'                => array('d', 'e', 'f')
            );
            $searchAttributeDataForI['structure'] = '1';
            $modelClassNamesAndSearchAttributeData = array(
                'I' => $searchAttributeDataForI,
                'J' => $searchAttributeDataForJ,
            );
            $modelClassNamesAndSortAttributes = array(
                'I' => 'name',
                'J' => 'jMember',
            );
            $unionSql     = RedBeanModelsDataProvider::makeUnionSql($modelClassNamesAndSearchAttributeData,
                                                                    $modelClassNamesAndSortAttributes, true, 2, 7);
            $compareSubsetSql  = "(";
            $compareSubsetSql .= "select distinct {$quote}i{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'I' modelClassName , {$quote}h{$quote}.{$quote}name{$quote} orderByColumn ";
            $compareSubsetSql .= "from ({$quote}i{$quote}, {$quote}h{$quote}) ";
            $compareSubsetSql .= "left join {$quote}k{$quote} on {$quote}k{$quote}.{$quote}i_id{$quote} = ";
            $compareSubsetSql .= "{$quote}i{$quote}.{$quote}id{$quote} ";
            $compareSubsetSql .= "where ({$quote}k{$quote}.{$quote}kmember{$quote} IN(lower('d'),lower('e'),lower('f')))";
            $compareSubsetSql .= " and {$quote}h{$quote}.{$quote}id{$quote} = {$quote}i{$quote}.{$quote}h_id{$quote}";
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "UNION (";
            $compareSubsetSql .= "select {$quote}j{$quote}.{$quote}id{$quote} id ";
            $compareSubsetSql .= ", 'J' modelClassName , {$quote}j{$quote}.{$quote}jmember{$quote} orderByColumn ";
            $compareSubsetSql .= "from {$quote}j{$quote} ";
            $compareSubsetSql .= "where ({$quote}j{$quote}.{$quote}jmember{$quote} IN(lower('a'),lower('b'),lower('c')))";;
            $compareSubsetSql .= ") ";
            $compareSubsetSql .= "order by orderByColumn desc ";
            $compareSubsetSql .= 'limit 7 offset 2';
            $this->assertEquals($compareSubsetSql, $unionSql);

            //Make sure the sql runs properly.
            $dataProvider = new RedBeanModelsDataProvider('anId', $modelClassNamesAndSortAttributes, true,
                                                            $modelClassNamesAndSearchAttributeData);
            $data = $dataProvider->getData();
        }

        /**
         * @depends testUnionSqlAcrossMultipleModelsUsingOneOfFilterWhichMakesSelectDistinct
         */
        public function testUnionSqlAcrossMultipleModelsUsingManyManyRelationFilterWhichMakesSelectDistinct()
        {

        }
    }
?>