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

    class FiltersReportQueryBuilderTest extends ZurmoBaseTest
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
            DisplayAttributeForReportForm::resetCount();
        }

        public function testASingleAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'string';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}reportmodeltestitem{$q}.{$q}string{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testASingleAttributeThatHasAModifier()
        {
            $tempTimeZone = Yii::app()->user->userModel->timeZone;
            Yii::app()->user->userModel->timeZone = 'America/Chicago';
            //Deal with daylight savings time.
            $timeZoneObject  = new DateTimeZone(Yii::app()->user->userModel->timeZone);
            $offsetInSeconds = $timeZoneObject->getOffset(new DateTime());
            $this->assertTrue($offsetInSeconds == -18000 || $offsetInSeconds == -21600);
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime__Day';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("(day({$q}item{$q}.{$q}createddatetime{$q} - INTERVAL " .
                                abs($offsetInSeconds) . " SECOND) = 'a value')", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
            Yii::app()->user->userModel->timeZone = $tempTimeZone;
        }

        public function testTwoAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'string';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'integer';
            $filter2->value                        = '54.24';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}reportmodeltestitem{$q}.{$q}string{$q} = 'a value') and " .
                                                     "({$q}reportmodeltestitem{$q}.{$q}integer{$q} = '54.24')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testLikeContactState()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'likeContactState';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}reportmodeltestitem{$q}.{$q}reportmodeltestitem7_id{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testCurrencyValueAttributeWithoutCurrencyIdSpecified()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'currencyValue';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}currencyvalue{$q}.{$q}value{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testCurrencyValueAttributeWithCurrencyIdSpecified()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'currencyValue';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter->currencyIdForValue            = '6';
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "(({$q}currencyvalue{$q}.{$q}value{$q} = 'a value') and " .
                                                     "({$q}currencyvalue{$q}.{$q}currency_id{$q} = '6'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testLikeContactStateWhenRelated()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem2');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasMany2___likeContactState';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}reportmodeltestitem{$q}.{$q}reportmodeltestitem7_id{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkips()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'primaryAddress___street1';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}address{$q}.{$q}street1{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithBeanSkip()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'dropDown';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}customfield{$q}.{$q}value{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());;

            //Two filter attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'owner___lastName';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "({$q}person{$q}.{$q}lastname{$q} = 'green')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());;

            //Two filter attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'owner___lastName';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'modifiedByUser___lastName';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}person{$q}.{$q}lastname{$q} = 'green') and " .
                                                     "({$q}person1{$q}.{$q}lastname{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedAttributeNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___phone';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}reportmodeltestitem2{$q}.{$q}phone{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());

            //Add a second attribute
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 and 2');
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'integer';
            $filter2->value                        = 'a value';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}reportmodeltestitem2{$q}.{$q}phone{$q} = 'a value') and " .
                                                     "({$q}reportmodeltestitem{$q}.{$q}integer{$q} = 'a value')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testAttributeOnOwnedModelWithNoBeanSkipsThatIsNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem2');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem2',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasMany2___primaryAddress___street1';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}address{$q}.{$q}street1{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNonRelatedNonDerivedCastedUpAttributeThatIsAUserRelationWhenNested()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___owner___lastName';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}person{$q}.{$q}lastname{$q} = 'a value')", $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());

            //Two display attributes that are casted up several levels
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 and 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___owner___lastName';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___modifiedByUser___lastName';
            $filter2->value                        = 'a value 2';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}person{$q}.{$q}lastname{$q} = 'a value') and " .
                                                     "({$q}person1{$q}.{$q}lastname{$q} = 'a value 2')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());

            //Add third display attribute on the base model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 and 2 and 3');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___owner___lastName';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___modifiedByUser___lastName';
            $filter2->value                        = 'a value 2';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $filter3                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'modifiedByUser___lastName';
            $filter3->value                        = 'a value 3';
            $filter3->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "({$q}person{$q}.{$q}lastname{$q} = 'a value') and " .
                                                     "({$q}person1{$q}.{$q}lastname{$q} = 'a value 2') and " .
                                                     "({$q}person2{$q}.{$q}lastname{$q} = 'a value 3')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(10, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'reportModelTestItem9___createdDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithOneOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with one on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany1___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasOneRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with two on a relation that is HAS_ONE
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasMany___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAHasManyBelongsToRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'reportModelTestItem9___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'reportModelTestItem9___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoNonRelatedNonDerivedCastedUpAttributeWithBothOnAManyManyRelation()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is MANY_MANY
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasMany1___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany1___modifiedDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-05 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasOneRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_ONE and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2 AND 3');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'modifiedDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___createdDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter3                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'hasOne___modifiedDateTime';
            $filter3->value                        = '1991-03-06';
            $filter3->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-05 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-06 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-06 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is HAS_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2 AND 3');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'modifiedDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany___createdDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter3                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'hasMany___modifiedDateTime';
            $filter3->value                        = '1991-03-06';
            $filter3->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-05 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-06 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-06 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAHasManyBelongsToRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with both on a relation that is HAS_MANY_BELONGS_TO
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2 AND 3');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'reportModelTestItem9___createdDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter3                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'reportModelTestItem9___modifiedDateTime';
            $filter3->value                        = '1991-03-06';
            $filter3->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-05 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-06 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-06 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeNonRelatedNonDerivedCastedUpAttributeWithTwoOnAManyManyRelationAndOneOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 casted up attributes with 2 on a relation that is MANY_MANY and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem3');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2 AND 3');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'modifiedDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany1___createdDateTime';
            $filter2->value                        = '1991-03-05';
            $filter2->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter3                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem3',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'hasMany1___modifiedDateTime';
            $filter3->value                        = '1991-03-06';
            $filter3->valueType                    = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "(({$q}item{$q}.{$q}modifieddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}modifieddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-05 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-05 23:59:59')) and " .
                                                     "(({$q}item1{$q}.{$q}modifieddatetime{$q} >= '1991-03-06 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}modifieddatetime{$q} <= '1991-03-06 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenOneIsOnRelatedModelAndOneIsOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with 1 on relation and one on self
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'dropDown';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___dropDown';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}customfield1{$q}.{$q}value{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModelButDifferentRelations()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but the links are different
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___dropDown';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasMany___dropDown';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}customfield1{$q}.{$q}value{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnRelatedModelsThatAreDifferent()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on 2 different related models
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___dropDown';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne2___dropDownX';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}customfield1{$q}.{$q}value{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoCustomFieldsWhenBothAreOnTheSameRelatedModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            //2 custom fields attributes with both on a related model, but 2 different dropdowns
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___dropDown';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___dropDown2';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}customfield1{$q}.{$q}value{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOnSelf()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 __User attributes on the same model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdByUser__User';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'modifiedByUser__User';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}item{$q}.{$q}createdbyuser__user_id{$q} = 'green') and " .
                                                     "({$q}item{$q}.{$q}modifiedbyuser__user_id{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());

            //2 __User attributes on the same model, one is owned, so not originating both from Item
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdByUser__User';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'owner__User';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}item{$q}.{$q}createdbyuser__user_id{$q} = 'green') and " .
                                                     "({$q}ownedsecurableitem{$q}.{$q}owner__user_id{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereSameAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //2 createdByUser__User attributes. One of self, one on related.
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdByUser__User';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___modifiedByUser__User';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}item{$q}.{$q}createdbyuser__user_id{$q} = 'green') and " .
                                                     "({$q}item1{$q}.{$q}modifiedbyuser__user_id{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeOneOnSelfAndOneOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Self createdByUser__User, related owner__User
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'createdByUser__User';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___owner__User';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}item{$q}.{$q}createdbyuser__user_id{$q} = 'green') and " .
                                                     "({$q}ownedsecurableitem1{$q}.{$q}owner__user_id{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDynamicallyDerivedAttributeBothOnRelatedModelWhereDifferentAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Related createdByUser__User and related owner__User. On same related model
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem9');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___createdByUser__User';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem9',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'hasOne___owner__User';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}item{$q}.{$q}createdbyuser__user_id{$q} = 'green') and " .
                                                     "({$q}ownedsecurableitem{$q}.{$q}owner__user_id{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testNestedRelationsThatComeBackOnTheBaseModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            //Base model is Account.  Get related contact's opportunity's account's name
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'contacts___opportunities___account___name';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "({$q}account1{$q}.{$q}name{$q} = 'green')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testThreeTestedRelationsWhereTheyBothGoToTheSameModelButAtDifferentNestingPoints()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();

            //Accounts -> Opportunities, but also Accounts -> Contacts -> Opportunities,
            //and a third to go to Accounts again.
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2 AND 3');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___name';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'contacts___opportunities___name';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $filter3                               = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter3->attributeIndexOrDerivedType  = 'contacts___opportunities___account___name';
            $filter3->value                        = 'yellow';
            $filter3->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2, $filter3));
            $compareContent                        = "({$q}opportunity{$q}.{$q}name{$q} = 'green') and " .
                                                     "({$q}opportunity1{$q}.{$q}name{$q} = 'blue') and " .
                                                     "({$q}account1{$q}.{$q}name{$q} = 'yellow')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___category';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelTwo()
        {
            //This test tests name instead of category which is an attribute on the meeting model.
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___name';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "({$q}meeting{$q}.{$q}name{$q} = 'green')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testTwoAttributesDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModel()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___category';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'meetings___name';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}meeting{$q}.{$q}name{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatDoesNotCastDown()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'model5ViaItem___name';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}reportmodeltestitem5{$q}.{$q}name{$q} = 'a value')", $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___meetings___category';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'opportunities___meetings___name';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}meeting{$q}.{$q}name{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(8, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFar()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___latestDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "(({$q}activity{$q}.{$q}latestdatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}activity{$q}.{$q}latestdatetime{$q} <= '1991-03-04 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWithCastingHintToNotCastDownSoFarAndCastUpBackIntoItem()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "(({$q}item1{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item1{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59'))";
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('activity_item',         $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('item',                  $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('activity',              $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',         $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('activity',              $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('securableitem1',        $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem1',   $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('item1',                 $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('securableitem1',        $leftTablesAndAliases[4]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithTwoAttributes()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests inferredRelation with 2 attributes on the opposing model. Only one declares the module specifically
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___industry';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}account{$q}.{$q}name{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(6, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithTwoAttributesNestedTwoLevelsDeep()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___opportunities___stage';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___opportunities___name';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}customfield{$q}.{$q}value{$q} = 'green') and " .
                                                     "({$q}opportunity{$q}.{$q}name{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithTwoAttributesComingAtItFromANestedPoint()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Also declaring Via modules
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem7');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 AND 2');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'model5___ReportModelTestItem__reportItems__Inferred___phone';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem7',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'model5___ReportModelTestItem__reportItems__Inferred___dropDown';
            $filter2->value                        = 'blue';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "({$q}reportmodeltestitem{$q}.{$q}phone{$q} = 'green') and " .
                                                     "({$q}customfield{$q}.{$q}value{$q} = 'blue')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('reportmodeltestitem5',        $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem7',        $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item_reportmodeltestitem5',   $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem5',        $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('item',                        $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item_reportmodeltestitem5',   $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('securableitem',               $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('item',                        $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',          $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('securableitem',               $leftTablesAndAliases[4]['onTableAliasName']);
            $this->assertEquals('reportmodeltestitem',         $leftTablesAndAliases[5]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',          $leftTablesAndAliases[5]['onTableAliasName']);
            $this->assertEquals('customfield',                 $leftTablesAndAliases[6]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',         $leftTablesAndAliases[6]['onTableAliasName']);
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59'))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithMixedInAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___owner__User';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $this->assertEquals("({$q}ownedsecurableitem{$q}.{$q}owner__user_id{$q} = 'a value')", $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDowButAlsoWithFullCastDown()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1 and 2');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___createdDateTime';
            $filter->value                         = '1991-03-04';
            $filter->valueType                     = MixedDateTypesSearchFormAttributeMappingRules::TYPE_ON;
            $filter2                               = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            $filter2->value                        = 'a value';
            $filter2->operator                     = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent                        = "(({$q}item{$q}.{$q}createddatetime{$q} >= '1991-03-04 00:00:00') and " .
                                                     "({$q}item{$q}.{$q}createddatetime{$q} <= '1991-03-04 23:59:59')) and " .
                                                     "({$q}account{$q}.{$q}name{$q} = 'a value')";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(5, $joinTablesAdapter->getLeftTableJoinCount());
            $leftTablesAndAliases = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('activity_item',           $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('activity',                $leftTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('item',                    $leftTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity_item',           $leftTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem',           $leftTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('item',                    $leftTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem',      $leftTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem',           $leftTablesAndAliases[3]['onTableAliasName']);
            $this->assertEquals('account',                 $leftTablesAndAliases[4]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem',      $leftTablesAndAliases[4]['onTableAliasName']);
        }

        /**
         * Multi-select should utilize a sub-query as part of its query.
         */

        public function testASingleMultiSelectAttributeWithOneOfOperator()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'multiDropDown';
            $filter->value                         = array('a', 'b');
            $filter->operator                      = OperatorRules::TYPE_ONE_OF;
            $content                               = $builder->makeQueryContent(array($filter));
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $compareContent = "(1 = (select 1 from {$q}customfieldvalue{$q} customfieldvalue where " .
                              "{$q}customfieldvalue{$q}.{$q}multiplevaluescustomfield_id{$q} = {$q}" .
                              "multiplevaluescustomfield{$q}.id and {$q}customfieldvalue{$q}.{$q}value{$q}" .
                              " IN('a','b') limit 1))"; // Not Coding Standard
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('multiplevaluescustomfield',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',        $leftTablesAndAliases[0]['onTableAliasName']);
        }

        /**
         * Tag cloud should utilize a sub-query as part of its query.
         */
        public function testASingleTagCloudAttributeWithEqualsOperator()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();

            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'tagCloud';
            $filter->value                         = 'a';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $leftTablesAndAliases                  = $joinTablesAdapter->getLeftTablesAndAliases();
            $fromTablesAndAliases                  = $joinTablesAdapter->getFromTablesAndAliases();
            $compareContent =   "(1 = (select 1 from {$q}customfieldvalue{$q} customfieldvalue where " .
                                "{$q}customfieldvalue{$q}.{$q}multiplevaluescustomfield_id{$q} = {$q}" .
                                "multiplevaluescustomfield{$q}.id and {$q}customfieldvalue{$q}.{$q}value{$q}" .
                                " = 'a' limit 1))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(1, $joinTablesAdapter->getLeftTableJoinCount());
            $this->assertEquals('multiplevaluescustomfield',  $leftTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('reportmodeltestitem',        $leftTablesAndAliases[0]['onTableAliasName']);
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownTwiceWithNoSkips()
        {
            //todo: test casting down more than one level. not sure how to test this.. since meetings is only one skip past activity not really testing that castDown fully
            //$this->fail();
        }

        public function testPolymorphic()
        {
            //todo: test polymorphics too? maybe we wouldnt have any for now? but we should still mark fail test here...
            //$this->fail();
        }
    }
?>