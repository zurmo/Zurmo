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

    class ReportDataProviderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            SecurityTestHelper::createSuperAdmin();
            $sally = UserTestHelper::createBasicUser('sally');
            $sally->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $sally->setRight('ContactsModule',      ContactsModule::RIGHT_ACCESS_CONTACTS);
            $sally->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS);
            $sally->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $sally->setRight('ReportsTestModule',   ReportsTestModule::RIGHT_ACCESS_REPORTS_TESTS);
            if (!$sally->save())
            {
                throw new FailedToSaveModelException();
            }
            $sarah = UserTestHelper::createBasicUser('sarah');
            $sarah->setRight('AccountsModule',      AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $sarah->setRight('LeadsModule',         LeadsModule::RIGHT_ACCESS_LEADS);
            $sarah->setRight('MeetingsModule',      MeetingsModule::RIGHT_ACCESS_MEETINGS);
            $sarah->setRight('OpportunitiesModule', OpportunitiesModule::RIGHT_ACCESS_OPPORTUNITIES);
            $sarah->setRight('ReportsTestModule',   ReportsTestModule::RIGHT_ACCESS_REPORTS_TESTS);
            if (!$sarah->save())
            {
                throw new FailedToSaveModelException();
            }
            $nobody = UserTestHelper::createBasicUser('nobody');
            if (!$nobody->save())
            {
                throw new FailedToSaveModelException();
            }
        }

        public function testResolveFiltersForReadPermissionsWithoutAnyExistingFiltersForASuperUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $filters = $reportDataProvider->resolveFiltersForReadPermissions($filters, $filtersStructure);
            $this->assertEquals(0, count($filters));
            $this->assertEquals('', $filtersStructure);
        }

        public function testResolveFiltersForReadPermissionsWithoutAnyExistingFiltersForANonSuperUser()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $filters = $reportDataProvider->resolveFiltersForReadPermissions($filters, $filtersStructure);
            $this->assertEquals(2, count($filters));
            $this->assertEquals('owner__User', $filters[0]->attributeIndexOrDerivedType);
            $this->assertEquals('ReadOptimization', $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals('(1 or 2)', $filtersStructure);
        }

        public function testResolveFiltersForReadPermissionsWithOneDisplayAttributeAndOneFilterForANonSuperUser()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ReportsTestModule');

            $displayAttribute = new DisplayAttributeForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType = 'hasOne___name';
            $report->addDisplayAttribute($displayAttribute);

            $reportDataProvider                  = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure                    = '1';
            $filter                              = new FilterForReportForm('ReportsTestModule', 'ReportModelTestItem',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'string';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $filters                             = array($filter);
            $filters = $reportDataProvider->resolveFiltersForReadPermissions($filters, $filtersStructure);
            $this->assertEquals(5, count($filters));
            $this->assertEquals('owner__User',                 $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals('ReadOptimization',            $filters[2]->attributeIndexOrDerivedType);
            $this->assertEquals('hasOne___owner__User',        $filters[3]->attributeIndexOrDerivedType);
            $this->assertEquals('hasOne___ReadOptimization',   $filters[4]->attributeIndexOrDerivedType);
            $this->assertEquals('1 and ((2 or 3) and (4 or 5))', $filtersStructure);
        }

        public function testResolveFiltersForVariableStatesWithoutAnyExistingFiltersForASuperUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ContactsModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $filters = $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
            $this->assertEquals(0, count($filters));
            $this->assertEquals('', $filtersStructure);
        }

        public function testResolveFiltersForVariableStatesWithoutAnyExistingFiltersForANonSuperUserWhoCanSeeOneState()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ContactsModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $filters = $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
            $stateAdapter = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $this->assertEquals(1, count($filters));
            $this->assertEquals('state', $filters[0]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF,   $filters[0]->operator);
            $this->assertEquals($stateAdapter->getStateIds(), $filters[0]->value);
            $this->assertEquals('1', $filtersStructure);
        }

        public function testResolveFiltersForVariableStatesWithoutAnyExistingFiltersForANonSuperUserWhoCanSeeAnotherState()
        {
            Yii::app()->user->userModel = User::getByUsername('sarah');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ContactsModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $filters = $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
            $stateAdapter = new LeadsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $this->assertEquals(1, count($filters));
            $this->assertEquals('state', $filters[0]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF,   $filters[0]->operator);
            $this->assertEquals($stateAdapter->getStateIds(), $filters[0]->value);
            $this->assertEquals('1', $filtersStructure);
        }

        /**
         * @expectedException PartialRightsForReportSecurityException
         **/
        public function testPartialRightsForReportSecurityExceptionThrown()
        {
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('ContactsModule');
            $reportDataProvider = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure   = '';
            $filters            = array();
            $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
        }

        public function testResolveFiltersForVariableStatesWithOneDisplayAttributeAndOneFilterForANonSuperUserWhoCanSeeOneState()
        {
            Yii::app()->user->userModel = User::getByUsername('sally');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType = 'contacts___officePhone';
            $report->addDisplayAttribute($displayAttribute);

            $reportDataProvider                  = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure                    = '1';
            $filter                              = new FilterForReportForm('AccountsModule', 'Account',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'opportunities___contacts___website';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $filters                             = array($filter);
            $filters = $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
            $stateAdapter = new ContactsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $this->assertEquals(3, count($filters));
            $this->assertEquals('contacts___state',         $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF, $filters[1]->operator);
            $this->assertEquals($stateAdapter->getStateIds(), $filters[1]->value);
            $this->assertEquals('opportunities___contacts___state', $filters[2]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF,         $filters[2]->operator);
            $this->assertEquals($stateAdapter->getStateIds(),       $filters[2]->value);
            $this->assertEquals('1 and (2 and 3)', $filtersStructure);
        }

        public function testResolveFiltersForVariableStatesWithOneDisplayAttributeAndOneFilterForANonSuperUserWhoCanSeeAnotherState()
        {
            Yii::app()->user->userModel = User::getByUsername('sarah');
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->attributeIndexOrDerivedType = 'contacts___officePhone';
            $report->addDisplayAttribute($displayAttribute);

            $reportDataProvider                  = new RowsAndColumnsReportDataProvider($report);
            $filtersStructure                    = '1';
            $filter                              = new FilterForReportForm('AccountsModule', 'Account',
                                                   Report::TYPE_ROWS_AND_COLUMNS);
            $filter->attributeIndexOrDerivedType = 'opportunities___contacts___website';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $filter->value                       = 'Zurmo';
            $filters                             = array($filter);
            $filters = $reportDataProvider->resolveFiltersForVariableStates($filters, $filtersStructure);
            $stateAdapter = new LeadsStateMetadataAdapter(array('clauses' => array(), 'structure' => ''));
            $this->assertTrue(count($stateAdapter->getStateIds()) > 0);
            $this->assertEquals(3, count($filters));
            $this->assertEquals('contacts___state',         $filters[1]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF, $filters[1]->operator);
            $this->assertEquals($stateAdapter->getStateIds(), $filters[1]->value);
            $this->assertEquals('opportunities___contacts___state', $filters[2]->attributeIndexOrDerivedType);
            $this->assertEquals(OperatorRules::TYPE_ONE_OF,         $filters[2]->operator);
            $this->assertEquals($stateAdapter->getStateIds(),       $filters[2]->value);
            $this->assertEquals('1 and (2 and 3)', $filtersStructure);
        }
    }
?>