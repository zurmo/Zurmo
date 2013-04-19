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
     * Class that builds demo accounts.
     */
    class ReportsDemoDataMaker extends DemoDataMaker
    {
        protected $ratioToLoad = 1;

        public static function getDependencies()
        {
            return array('contacts');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            static::makeNewLeadsReport();
            static::makeActiveCustomerEmailList();
            static::makeClosedWonOpportunitiesByOwner();
            static::makeClosedWonOpportunitiesByMonth();
            static::makeOpportunitiesByStage();
        }

        public static function makeNewLeadsReport()
        {
            $leadStateIdsAndNames = LeadsUtil::getLeadStateDataFromStartingStateOnAndKeyedById();
            $report               = new Report();
            $report->setDescription    ('A report showing new leads');
            $report->setModuleClassName('ContactsModule');
            $report->setName           ('New Leads Report');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('1 AND 2');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $filter = new FilterForReportForm('ContactsModule', 'Contact', $report->getType());
            $filter->attributeIndexOrDerivedType = 'createdDateTime';
            $filter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_LAST_7_DAYS;
            $filter->availableAtRunTime          = true;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ContactsModule', 'Contact', $report->getType());
            $filter->attributeIndexOrDerivedType = 'state';
            $filter->value                       = array_keys($leadStateIdsAndNames);
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $report->addFilter($filter);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'FullName';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'source';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'officePhone';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $report->addDisplayAttribute($displayAttribute);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            //set explicit
            $saved = $savedReport->save();
            assert('$saved');
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                makeBySecurableItem($savedReport);
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            $success = ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissions($savedReport, $explicitReadWriteModelPermissions);
            assert('$success');
            $saved = $savedReport->save();
            assert('$saved');
        }

        public static function makeActiveCustomerEmailList()
        {
            $contactStateIdsAndNames = ContactsUtil::getContactStateDataFromStartingStateOnAndKeyedById();
            $report                  = new Report();
            $report->setDescription    ('A report showing active customers who have not opted out of receiving emails');
            $report->setModuleClassName('ContactsModule');
            $report->setName           ('Active Customer Email List');
            $report->setType           (Report::TYPE_ROWS_AND_COLUMNS);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('1 AND 2 AND 3');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $filter = new FilterForReportForm('ContactsModule', 'Contact', $report->getType());
            $filter->attributeIndexOrDerivedType = 'account___type';
            $filter->value                       = 'Customer';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ContactsModule', 'Contact', $report->getType());
            $filter->attributeIndexOrDerivedType = 'state';
            $filter->value                       = array_keys($contactStateIdsAndNames);
            $filter->operator                    = OperatorRules::TYPE_ONE_OF;
            $report->addFilter($filter);

            $filter = new FilterForReportForm('ContactsModule', 'Contact', $report->getType());
            $filter->attributeIndexOrDerivedType = 'primaryEmail___optOut';
            $filter->value                       = true;
            $filter->operator                    = OperatorRules::TYPE_DOES_NOT_EQUAL;
            $report->addFilter($filter);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'FullName';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'account___name';
            $displayAttribute->label                       = 'Account Name';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('ContactsModule', 'Contact', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'primaryEmail___emailAddress';
            $report->addDisplayAttribute($displayAttribute);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            //set explicit
            $saved = $savedReport->save();
            assert('$saved');
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                makeBySecurableItem($savedReport);
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            $success = ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissions($savedReport, $explicitReadWriteModelPermissions);
            assert('$success');
            $saved = $savedReport->save();
            assert('$saved');
        }

        public static function makeClosedWonOpportunitiesByOwner()
        {
            $report                  = new Report();
            $report->setDescription    ('A report showing closed won opportunties by owner');
            $report->setModuleClassName('OpportunitiesModule');
            $report->setName           ('Opportunities By Owner');
            $report->setType           (Report::TYPE_SUMMATION);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('1');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $filter = new FilterForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $filter->attributeIndexOrDerivedType = 'stage';
            $filter->value                       = 'Closed Won';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $report->addFilter($filter);

            $groupBy = new GroupByForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'owner__User';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'owner__User';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'amount__Summation';
            $report->addDisplayAttribute($displayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity',
                                                                                    $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'name';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity',
                                                                                    $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'account___name';
            $drillDownDisplayAttribute->label                       = 'Account Name';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity',
                                                                                    $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'amount';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $drillDownDisplayAttribute = new DrillDownDisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity',
                                                                                    $report->getType());
            $drillDownDisplayAttribute->attributeIndexOrDerivedType = 'closeDate';
            $report->addDrillDownDisplayAttribute($drillDownDisplayAttribute);

            $chart               = new ChartForReportForm();
            $chart->type         = 'Pie2D';
            $chart->firstSeries  = 'owner__User';
            $chart->firstRange   = 'amount__Summation';
            $report->setChart($chart);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            //set explicit
            $saved = $savedReport->save();
            assert('$saved');
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                makeBySecurableItem($savedReport);
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            $success = ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissions($savedReport, $explicitReadWriteModelPermissions);
            assert('$success');
            $saved = $savedReport->save();
            assert('$saved');
        }

        public static function makeClosedWonOpportunitiesByMonth()
        {
            $report                  = new Report();
            $report->setDescription    ('A report showing closed won opportunties bymonth');
            $report->setModuleClassName('OpportunitiesModule');
            $report->setName           ('Closed won opportunities by month');
            $report->setType           (Report::TYPE_SUMMATION);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('1');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $filter = new FilterForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $filter->attributeIndexOrDerivedType = 'stage';
            $filter->value                       = 'Closed Won';
            $filter->operator                    = OperatorRules::TYPE_EQUALS;
            $report->addFilter($filter);

            $groupBy = new GroupByForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'closeDate__Month';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'closeDate__Month';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'amount__Summation';
            $report->addDisplayAttribute($displayAttribute);

            $chart               = new ChartForReportForm();
            $chart->type         = 'Bar2D';
            $chart->firstSeries  = 'closeDate__Month';
            $chart->firstRange   = 'amount__Summation';
            $report->setChart($chart);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            //set explicit
            $saved = $savedReport->save();
            assert('$saved');
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                makeBySecurableItem($savedReport);
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            $success = ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissions($savedReport, $explicitReadWriteModelPermissions);
            assert('$success');
            $saved = $savedReport->save();
            assert('$saved');
        }

        public static function makeOpportunitiesByStage()
        {
            $report                  = new Report();
            $report->setModuleClassName('OpportunitiesModule');
            $report->setName           ('Opportunities by Stage');
            $report->setType           (Report::TYPE_SUMMATION);
            $report->setOwner          (Yii::app()->user->userModel);
            $report->setFiltersStructure('');
            $report->setCurrencyConversionType(Report::CURRENCY_CONVERSION_TYPE_BASE);

            $groupBy = new GroupByForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $groupBy->attributeIndexOrDerivedType = 'stage';
            $groupBy->axis                        = 'x';
            $report->addGroupBy($groupBy);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'stage';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $displayAttribute = new DisplayAttributeForReportForm('OpportunitiesModule', 'Opportunity', $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'amount__Summation';
            $report->addDisplayAttribute($displayAttribute);

            $chart               = new ChartForReportForm();
            $chart->type         = 'Column2D';
            $chart->firstSeries  = 'stage';
            $chart->firstRange   = 'amount__Summation';
            $report->setChart($chart);

            $savedReport = new SavedReport();
            SavedReportToReportAdapter::resolveReportToSavedReport($report, $savedReport);
            //set explicit
            $saved = $savedReport->save();
            assert('$saved');
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                makeBySecurableItem($savedReport);
            $explicitReadWriteModelPermissions->addReadWritePermitable(Group::getByName(Group::EVERYONE_GROUP_NAME));
            $success = ExplicitReadWriteModelPermissionsUtil::
                resolveExplicitReadWriteModelPermissions($savedReport, $explicitReadWriteModelPermissions);
            assert('$success');
            $saved = $savedReport->save();
            assert('$saved');
        }
    }
?>