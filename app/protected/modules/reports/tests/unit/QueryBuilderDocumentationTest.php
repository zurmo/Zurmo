<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class QueryBuilderDocumentationTest extends ZurmoBaseTest
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

        public function testSomething()
        {
            //This class should serve as documentation for all various scenarios that occur in the various builders
            //Additionally deeper nesting should be tested for all scenarios. In OrderBysReportQueryBuilderTest we are not
            //testing beyond one layer deep and we should in this class.
            //Also test when existing filters have nestings and you are getting orderBys or groupBys generated.
        }

        /**
         * Test to confirm joins are working correctly when ordering by an attribute on item.
         * Testing when the join adapter has already added a display attribute from a related account
         */
        public function testOrderByNoteCreatedDateTimeAndConfirmJoinsAreCorrect()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Note');
            $selectQueryAdapter                    = new RedBeanModelSelectQueryAdapter();
            $builder                               = new DisplayAttributesReportQueryBuilder($joinTablesAdapter, $selectQueryAdapter);
            $displayAttribute                      = new DisplayAttributeForReportForm('NotesModule', 'Note',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType  = 'Account__activityItems__Inferred___name';
            //First build the display attributes from clauses
            $builder->makeQueryContent(array($displayAttribute));

            $builder                               = new OrderBysReportQueryBuilder($joinTablesAdapter);
            $orderBy                               = new OrderByForReportForm('NotesModule', 'Note',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            //Second build the order by from clauses
            $builder->makeQueryContent(array($orderBy));

            $fromTablesAndAliases                     = $joinTablesAdapter->getFromTablesAndAliases();
            $this->assertEquals('activity',             $fromTablesAndAliases[0]['tableAliasName']);
            $this->assertEquals('note',                 $fromTablesAndAliases[0]['onTableAliasName']);
            $this->assertEquals('ownedsecurableitem1',  $fromTablesAndAliases[1]['tableAliasName']);
            $this->assertEquals('activity',             $fromTablesAndAliases[1]['onTableAliasName']);
            $this->assertEquals('securableitem1',       $fromTablesAndAliases[2]['tableAliasName']);
            $this->assertEquals('ownedsecurableitem1',  $fromTablesAndAliases[2]['onTableAliasName']);
            $this->assertEquals('item1',                $fromTablesAndAliases[3]['tableAliasName']);
            $this->assertEquals('securableitem1',       $fromTablesAndAliases[3]['onTableAliasName']);
        }

        /**
         * Running a report centered on notes, with a display attribute from notes and accounts.  Ordered by
         * created date time in notes.  Should produce proper query to order by notes.  This test just makes sure
         * the sql is structured properly
         */
        public function testOrderByWorksOnAccountsAndNoteReport()
        {
            $report = new Report();
            $report->setFiltersStructure('');
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('NotesModule');
            $displayAttribute = new DisplayAttributeForReportForm('NotesModule', 'Note', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'description';
            $report->addDisplayAttribute($displayAttribute);
            $displayAttribute2 = new DisplayAttributeForReportForm('NotesModule', 'Note', $report->getType());
            $displayAttribute2->attributeIndexOrDerivedType = 'Account__activityItems__Inferred___name';
            $report->addDisplayAttribute($displayAttribute2);
            $orderBy = new OrderByForReportForm('NotesModule', 'Note', Report::TYPE_SUMMATION);
            $orderBy->attributeIndexOrDerivedType  = 'createdDateTime';
            $report->addOrderBy($orderBy);
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $sql                = $reportDataProvider->makeSqlQueryForDisplay();
            $q                  = DatabaseCompatibilityUtil::getQuote();
            $compareSql = "select {$q}note{$q}.{$q}id{$q} noteid, {$q}account{$q}.{$q}id{$q} accountid " .
                          "from ({$q}note{$q}, {$q}activity{$q}, {$q}ownedsecurableitem{$q} ownedsecurableitem1, {$q}securableitem{$q} securableitem1, {$q}item{$q} item1) " .
                          "left join {$q}activity_item{$q} on {$q}activity_item{$q}.{$q}activity_id{$q} = {$q}activity{$q}.{$q}id{$q} " .
                          "left join {$q}item{$q} on {$q}item{$q}.{$q}id{$q} = {$q}activity_item{$q}.{$q}item_id{$q} " .
                          "left join {$q}securableitem{$q} on {$q}securableitem{$q}.{$q}item_id{$q} = {$q}item{$q}.{$q}id{$q} " .
                          "left join {$q}ownedsecurableitem{$q} on {$q}ownedsecurableitem{$q}.{$q}securableitem_id{$q} = {$q}securableitem{$q}.{$q}id{$q} " .
                          "left join {$q}account{$q} on {$q}account{$q}.{$q}ownedsecurableitem_id{$q} = {$q}ownedsecurableitem{$q}.{$q}id{$q}  " .
                          "where {$q}activity{$q}.{$q}id{$q} = {$q}note{$q}.{$q}activity_id{$q} and " .
                          "{$q}ownedsecurableitem1{$q}.{$q}id{$q} = {$q}activity{$q}.{$q}ownedsecurableitem_id{$q} " .
                          "and {$q}securableitem1{$q}.{$q}id{$q} = {$q}ownedsecurableitem1{$q}.{$q}securableitem_id{$q} and " .
                          "{$q}item1{$q}.{$q}id{$q} = {$q}securableitem1{$q}.{$q}item_id{$q} order by {$q}item1{$q}.{$q}createddatetime{$q} asc limit 10 offset 0";
            $this->assertEquals($compareSql, $sql);
        }
    }
?>