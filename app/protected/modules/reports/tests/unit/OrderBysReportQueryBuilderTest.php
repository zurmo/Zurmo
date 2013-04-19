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

    class OrderBysReportQueryBuilderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testNonRelatedDisplayCalculationAsAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single sortable attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType  = 'float__Average';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("avg({$q}reportmodeltestitem{$q}.{$q}float{$q}) asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testCurrencyValueAttributeWithDifferentConversionTypes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //Test currencyValue using no conversion
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType  = 'currencyValue__Average';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("avg({$q}currencyvalue{$q}.{$q}value{$q}) asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Test currencyValue using base conversion
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter, Report::CURRENCY_CONVERSION_TYPE_BASE);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType  = 'currencyValue__Average';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("avg({$q}currencyvalue{$q}.{$q}value{$q} * {$q}currencyvalue{$q}.{$q}ratetobase{$q}) asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Test currencyValue using spot conversion
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter, Report::CURRENCY_CONVERSION_TYPE_SPOT);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType  = 'currencyValue__Average';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("avg({$q}currencyvalue{$q}.{$q}value{$q} * {$q}currencyvalue{$q}.{$q}ratetobase{$q}) asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single sortable attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'phone';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'integer';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc, " .
                                                     "{$q}reportmodeltestitem{$q}.{$q}integer{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //A single sortable attribute that is casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $this->assertEquals("{$q}item{$q}.{$q}createddatetime{$q} asc", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //Two sortable attribute that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'reportModelTestItem9___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with two on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasMany___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'reportModelTestItem9___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'reportModelTestItem9___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasMany1___createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $orderBy2->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasOneRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_ONE and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasOne___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasMany___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyBelongsToRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'reportModelTestItem9___createdDateTime';
            $orderBy2->order                       = 'desc';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'reportModelTestItem9___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                        = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} desc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAManyManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is MANY_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany1___createdDateTime';
            $orderBy3                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType = 'hasMany1___modifiedDateTime';
            $orderBy3->order                       = 'desc';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                        = "{$q}item{$q}.{$q}modifieddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}createddatetime{$q} asc, " .
                                                     "{$q}item1{$q}.{$q}modifieddatetime{$q} desc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenOneIsOnRelatedModelAndOneIsOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with 1 on relation and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'dropDown';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___dropDown';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModelButDifferentRelations()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but the links are different
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasMany___dropDown';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem1', $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem1', $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testTwoCustomFieldsWhenBothAreOnRelatedModelsThatAreDifferent()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on 2 different related models
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne2___dropDownX';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem', $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem', $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem8', $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem8', $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but 2 different dropdowns
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___dropDown2';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                     "{$q}customfield1{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 __User attributes on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdByUser__User';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'modifiedByUser__User';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',   $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',   $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[3]['onTableAliasName']);

            //2 __User attributes on the same model, one is owned, so not originating both from Item
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdByUser__User';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'owner__User';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',               $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',               $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',              $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',  $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('_user1',              $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereSameAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 createdByUser__User attributes. One of self, one on related.
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdByUser__User';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___createdByUser__User';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',   $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('_user',   $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('_user1',  $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Self createdByUser__User, related owner__User
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdByUser__User';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('_user',                 $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',                  $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('person',                $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('_user',                 $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem',   $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem9',  $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',   $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('_user1',                $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('person1',               $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('_user1',                $leftTablesAndAliases[5]['onTableAliasName']);
        }

        public function testDynamicallyDerivedAttributeBothOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Related createdByUser__User and related owner__User. On same related model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'hasOne___createdByUser__User';
            $orderBy2                              = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType = 'hasOne___owner__User';
            $content                               = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                        = "{$q}person{$q}.{$q}lastname{$q} asc, " .
                                                     "{$q}person1{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem9', $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('person',               $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('_user1',               $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[6]['onTableAliasName']);
            $this->assertEquals('person1',              $leftTablesAndAliases[7]['tableAliasName']);
            $this->assertEquals('_user1',               $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testNestedRelationsThatComeBackOnTheBaseModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Base model is Account.  Get related contact's opportunity's account's name
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                               = $builder->makeQueryContent(array($orderBy));
            $compareContent                        = "{$q}account1{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeTestedRelationsWhereTheyBothGoToTheSameModelButAtDifferentNestingPoints()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            //Accounts -> Opportunities, but also Accounts -> Contacts -> Opportunities,
            //and a third to go to Accounts again.
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'opportunities___name';
            $orderBy2                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'contacts___opportunities___name';
            $orderBy3                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy3->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2, $orderBy3));
            $compareContent                         = "{$q}opportunity{$q}.{$q}name{$q} asc, " .
                                                      "{$q}opportunity1{$q}.{$q}name{$q} asc, " .
                                                      "{$q}account1{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'meetings___category';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelTwo()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
        }

        public function testTwoAttributesDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModel()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'meetings___category';
            $orderBy2                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'meetings___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[2]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[3]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatDoesNotCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'model5ViaItem___name';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}reportmodeltestitem5{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'opportunities___meetings___category';
            $orderBy2                               = new OrderByForReportForm('AccountsModule', 'Account',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'opportunities___meetings___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}meeting{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('activity_id',    $leftTablesAndAliases[6]['tableJoinIdName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[6]['onTableAliasName']);
            $this->assertEquals('customfield',    $leftTablesAndAliases[7]['tableAliasName']);
            $this->assertEquals('meeting',        $leftTablesAndAliases[7]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFar()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'meetings___latestDateTime';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}activity{$q}.{$q}latestdatetime{$q} asc";
            $this->assertEquals($compareContent, $content);

            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',           $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',       $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',  $leftTablesAndAliases[1]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithTwoAttributes()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests inferredRelation with 2 attributes on the opposing model. Only one declares the module specifically
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___industry';
            $orderBy2                               = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}account{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',             $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',        $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('account',              $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('customfield',          $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('account',              $leftTablesAndAliases[5]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithTwoAttributesNestedTwoLevelsDeep()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___stage';
            $orderBy2                               = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___opportunities___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}customfield{$q}.{$q}value{$q} asc, " .
                                                      "{$q}opportunity{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',             $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',        $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('account',              $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('opportunity',          $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('account',              $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('customfield',          $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('opportunity',          $leftTablesAndAliases[6]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithTwoAttributesComingAtItFromANestedPoint()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Also declaring Via modules
            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem7');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'model5___ReportModelTestItem__reportItems__Inferred___phone';
            $orderBy2                               = new OrderByForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'model5___ReportModelTestItem__reportItems__Inferred___dropDown';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}reportmodeltestitem{$q}.{$q}phone{$q} asc, " .
                                                      "{$q}customfield{$q}.{$q}value{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('reportmodeltestitem5',         $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem7',         $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item_reportmodeltestitem5',    $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem5',         $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('item',                         $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item_reportmodeltestitem5',    $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('securableitem',                $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('item',                         $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',           $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('securableitem',                $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem',          $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',           $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('customfield',                  $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',          $leftTablesAndAliases[6]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}item{$q}.{$q}createddatetime{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',             $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',        $leftTablesAndAliases[1]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithMixedInAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___owner__User';
            $content                                = $builder->makeQueryContent(array($orderBy));
            $compareContent                         = "{$q}person{$q}.{$q}lastname{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',             $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',        $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('person',               $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('_user',                $leftTablesAndAliases[5]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDowButAlsoWithFullCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                      = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                                = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                                = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $orderBy2                               = new OrderByForReportForm('MeetingsModule', 'Meeting',
                                                      Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $content                                = $builder->makeQueryContent(array($orderBy, $orderBy2));
            $compareContent                         = "{$q}item{$q}.{$q}createddatetime{$q} asc, " .
                                                      "{$q}account{$q}.{$q}name{$q} asc";
            $this->assertEquals($compareContent, $content);
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',             $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',        $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item',                 $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('account',              $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',   $leftTablesAndAliases[4]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownTwiceWithNoSkips()
        {
            //todo: test casting down more than one level. not sure how to test this..
            //since meetings is only one skip past activity not really testing that castDown fully
            //$this->fail();
        }

        public function testPolymorphic()
        {
            //todo: test polymorphics too? maybe we wouldnt have any for now? but we should still mark fail test here...
            //$this->fail();
        }
    }
?>