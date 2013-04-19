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

    class ReadOptimizationFiltersReportQueryBuilderTest extends ZurmoBaseTest
    {
        protected static $superUserId;

        protected static $everyoneGroupId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$superUserId = Yii::app()->user->userModel->id;
            $group = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            self::$everyoneGroupId = $group->id;
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            Yii::app()->user->userModel->timeZone = 'America/Chicago';
            DisplayAttributeForReportForm::resetCount();
        }

        public function testASingleAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'ReadOptimization';
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent = "{$q}ownedsecurableitem{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}reportmodeltestitem_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1)";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testASingleRelatedAttribute()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'hasOne___ReadOptimization';
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent = "{$q}ownedsecurableitem{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}reportmodeltestitem2_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1)";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(2, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testBothMungeAttributes()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('ReportModelTestItem');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '(1 or 2)');
            $filter                                = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_SUMMATION);
            $filter->attributeIndexOrDerivedType   = 'owner__User';
            $filter->value                         = 'a value';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $filter2                               = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter2->attributeIndexOrDerivedType  = 'ReadOptimization';
            $content                               = $builder->makeQueryContent(array($filter, $filter2));
            $compareContent = "(({$q}ownedsecurableitem{$q}.{$q}owner__user_id{$q} = 'a value') or " .
                              "{$q}ownedsecurableitem{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}reportmodeltestitem_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1))";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(0, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeThatCastsDownAndSkipsAModelOne()
        {
            $q                                     = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'meetings___ReadOptimization';
            $filter->value                         = 'green';
            $filter->operator                      = OperatorRules::TYPE_EQUALS;
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent = "{$q}ownedsecurableitem1{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}meeting_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1)";
            $this->assertEquals($compareContent, $content);

            $this->assertEquals(3, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(3, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testDerivedRelationViaCastedUpModelAttributeWhenThroughARelation()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            //Tests derivedRelation when going through a relation already before doing the derived relation
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Account');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('AccountsModule', 'Account',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'opportunities___meetings___ReadOptimization';
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent = "{$q}ownedsecurableitem1{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}meeting_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1)";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(0, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(7, $joinTablesAdapter->getLeftTableJoinCount());
        }

        public function testInferredRelationModelAttributeWithCastingHintToNotCastDownSoFarWithItemAttribute()
        {
            $q                                      = DatabaseCompatibilityUtil::getQuote();
            $joinTablesAdapter                     = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $builder                               = new FiltersReportQueryBuilder($joinTablesAdapter, '1');
            $filter                                = new FilterForReportForm('MeetingsModule', 'Meeting',
                                                     Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType   = 'Account__activityItems__Inferred___ReadOptimization';
            $content                               = $builder->makeQueryContent(array($filter));
            $compareContent = "{$q}ownedsecurableitem{$q}.{$q}securable_id{$q} = (select securable_id " .
                              "from {$q}account_read{$q} where {$q}munge_id{$q} in ('U" .
                              self::$superUserId . "', 'G" . self::$everyoneGroupId . "') limit 1)";
            $this->assertEquals($compareContent, $content);
            $this->assertEquals(1, $joinTablesAdapter->getFromTableJoinCount());
            $this->assertEquals(4, $joinTablesAdapter->getLeftTableJoinCount());
        }
    }
?>