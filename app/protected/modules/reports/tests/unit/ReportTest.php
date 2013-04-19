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

    class ReportTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
            DrillDownDisplayAttributeForReportForm::resetCount();
        }

        public function testHasRuntimeFilters()
        {
            $report                              = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $filter = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->value                       = 'aValue';
            $filter->operator                    = 'equals';
            $report->addFilter($filter);
            $this->assertFalse($report->hasRuntimeFilters());
            $filter2 = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $filter2->attributeIndexOrDerivedType = 'string';
            $filter2->value                       = 'aValue';
            $filter2->operator                    = 'equals';
            $filter2->availableAtRunTime           = true;
            $report->addFilter($filter2);
            $this->assertTrue($report->hasRuntimeFilters());
        }

        public function testGetDisplayAttributeIndex()
        {
            $report           = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 $report->getType());
            $displayAttribute2->attributeIndexOrDerivedType = 'string';
            $displayAttribute2->label                       = 'someNewLabel 2';
            $report->addDisplayAttribute($displayAttribute2);
            $this->assertEquals(1, $report->getDisplayAttributeIndex('string'));
            $this->assertNull($report->getDisplayAttributeIndex('notHere'));
        }

        public function testGetDisplayAttributeByAttribute()
        {
            $report           = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'phone';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);
            $displayAttribute2 = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                 $report->getType());
            $displayAttribute2->attributeIndexOrDerivedType = 'string';
            $displayAttribute2->label                       = 'someNewLabel 2';
            $report->addDisplayAttribute($displayAttribute2);
            $this->assertEquals(1, $report->getDisplayAttributeIndex('string'));
            $displayAttributeResult = $report->getDisplayAttributeByAttribute('phone');
            $this->assertEquals($displayAttribute->getAttributeIndexOrDerivedType(),
                                $displayAttributeResult->getAttributeIndexOrDerivedType());
        }

        public function testResolveGroupBysAsFilters()
        {
            $report           = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $groupBy = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'lastName';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);
            $groupBy2 = new GroupByForReportForm('ReportsTestModule', 'ReportModelTestItem', $report->getType());
            $groupBy2->attributeIndexOrDerivedType = 'firstName';
            $groupBy2->axis                        = 'y';
            $report->addGroupBy($groupBy2);
            $this->assertNull($report->getFiltersStructure());
            $getData = array('groupByRowValuelastName' => '50', 'groupByRowValuefirstName' => '');
            $this->assertCount(0, $report->getFilters());
            $report->resolveGroupBysAsFilters($getData);
            $this->assertEquals('(1 AND 2)', $report->getFiltersStructure());
            $filters = $report->getFilters();
            $this->assertCount(2, $filters);
            $this->assertEquals('lastName',                  $filters[0]->getAttributeIndexOrDerivedType());
            $this->assertEquals('50',                        $filters[0]->value);
            $this->assertEquals(OperatorRules::TYPE_EQUALS,  $filters[0]->operator);
            $this->assertEquals('firstName',                 $filters[1]->getAttributeIndexOrDerivedType());
            $this->assertNull  ($filters[1]->value);
            $this->assertEquals(OperatorRules::TYPE_IS_NULL, $filters[1]->operator);
        }

        public function testGetReportableModulesAndLabelsForCurrentUser()
        {
            $modulesAndLabels = Report::getReportableModulesAndLabelsForCurrentUser();
            $this->assertCount(6, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('billy');
            $modulesAndLabels = Report::getReportableModulesAndLabelsForCurrentUser();
            $this->assertCount(0, $modulesAndLabels);
        }

        public function testGetReportableModulesClassNamesCurrentUserHasAccessTo()
        {
            $modulesAndLabels = Report::getReportableModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(6, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('billy');
            $modulesAndLabels = Report::getReportableModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(0, $modulesAndLabels);
        }

        public function testCanCurrentUserProperlyRenderResults()
        {
            $billy       = User::getByUsername('billy');
            $billy->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $billy->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $saved       = $billy->save();
            $this->assertTrue($saved);
            $report      = new Report();
            $report->setModuleClassName('ContactsModule');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //Now set to Billy
            Yii::app()->user->userModel = $billy;
            //Billy can't see the contacts module.
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //Billy can see accounts
            $report->setModuleClassName('AccountsModule');
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //A filter on accounts is ok for Billy to see
            $filter                              = new FilterForReportForm('AccountsModule', 'Account', $report->getType());
            $filter->attributeIndexOrDerivedType = 'officePhone';
            $filter->value                       = 'aValue';
            $filter->operator                    = 'equals';
            $report->addFilter($filter);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //A filter on contacts is not ok for Billy to see
            $filter2                              = new FilterForReportForm('AccountsModule', 'Account', $report->getType());
            $filter2->attributeIndexOrDerivedType = 'contacts___lastName';
            $filter2->value                       = 'aValue';
            $filter2->operator                    = 'equals';
            $report->addFilter($filter2);
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //A related filter on opportunities would be ok for Billy to see
            $report->removeAllFilters();
            $filter                              = new FilterForReportForm('AccountsModule', 'Account', $report->getType());
            $filter->attributeIndexOrDerivedType = 'opportunities___name';
            $filter->value                       = 'aValue';
            $filter->operator                    = 'equals';
            $report->addFilter($filter);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());
            $report->removeAllFilters();

            //Billy can see a groupBy on Accounts
            $groupBy = new GroupByForReportForm('AccountsModule', 'Account', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'name';
            $groupBy->axis                        = 'y';
            $report->addGroupBy($groupBy);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //Billy cannot see a related groupBy on Contacts
            $groupBy = new GroupByForReportForm('AccountsModule', 'Account', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'contacts___lastName';
            $groupBy->axis                        = 'y';
            $report->addGroupBy($groupBy);
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //Billy can see a related groupBy on Opportunities
            $report->removeAllGroupBys();
            $groupBy = new GroupByForReportForm('AccountsModule', 'Account', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'opportunities___name';
            $groupBy->axis                        = 'y';
            $report->addGroupBy($groupBy);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());
            $report->removeAllGroupBys();

            //Billy can see an orderBy on Accounts
            $orderBy = new OrderByForReportForm('AccountsModule', 'Account', $report->getType());
            $orderBy->attributeIndexOrDerivedType = 'name';
            $orderBy->order                       = 'desc';
            $report->addOrderBy($orderBy);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //Billy cannot see a related orderBy on Contacts
            $orderBy = new OrderByForReportForm('AccountsModule', 'Account', $report->getType());
            $orderBy->attributeIndexOrDerivedType = 'contacts___lastName';
            $orderBy->order                       = 'desc';
            $report->addOrderBy($orderBy);
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //Billy can see a related orderBy on Opportunities
            $report->removeAllOrderBys();
            $orderBy = new OrderByForReportForm('AccountsModule', 'Account', $report->getType());
            $orderBy->attributeIndexOrDerivedType = 'opportunities___name';
            $orderBy->order                       = 'desc';
            $report->addOrderBy($orderBy);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());
            $report->removeAllOrderBys();

            //Billy can see a displayAttribute on Accounts
            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'name';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //Billy cannot see a related displayAttribute on Contacts
            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'contacts___firstName';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //Billy can see a related displayAttribute on Opportunities
            $report->removeAllDisplayAttributes();
            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'opportunities___name';
            $displayAttribute->label                       = 'someNewLabel';
            $report->addDisplayAttribute($displayAttribute);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());
            $report->removeAllDisplayAttributes();

            //Billy can see a drillDownDisplayAttribute on Accounts
            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'name';
            $drillDownDisplayAttribute->label                       = 'someNewLabel';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());

            //Billy cannot see a related drillDownDisplayAttribute on Contacts
            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'contacts___firstName';
            $drillDownDisplayAttribute->label                       = 'someNewLabel';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);
            $this->assertFalse($report->canCurrentUserProperlyRenderResults());

            //Billy can see a related drillDownDisplayAttribute on Opportunities
            $report->removeAllDrillDownDisplayAttributes();
            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('AccountsModule', 'Account', $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'opportunities___name';
            $drillDownDisplayAttribute->label                       = 'someNewLabel';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);
            $this->assertTrue($report->canCurrentUserProperlyRenderResults());
        }
    }
?>