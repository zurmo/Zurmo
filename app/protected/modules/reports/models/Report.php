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
     * Class for interacting with Report definitions.  Gets information from either a SavedReport or via a POST.
     * Contains information about how a report should be constructed including how it looks in the user interface
     * when run.  The components of a report are filters, orderBys, groupBys, displayAttributes,
     * drillDownDisplayAttributes, and a chart.
     *
     * There are 3 different types of reports: TYPE_ROWS_AND_COLUMNS, TYPE_SUMMATION, and TYPE_MATRIX.  Only Summation
     * utilizes a chart and can have drillDownDisplayAttributes
     */
    class Report extends CComponent
    {
        /**
         * Defines a report type of Rows and Columns. This is the basic report type and isa simple list result
         */
        const TYPE_ROWS_AND_COLUMNS           = 'RowsAndColumns';

        /**
         * Defines a report type of Summation.  A summation report can group data into a result grid. It can also
         * have a chart and also allow for drill down into each row to get further information about each group
         */
        const TYPE_SUMMATION                  = 'Summation';

        /**
         * Defines a report type of Matrix. Complex report type that allows multiple groupings across both the x and y
         * axises.
         */
        const TYPE_MATRIX                     = 'Matrix';

        /**
         * Currency Conversion Type for rendering currency information.  "Actual" means the currency will not be converted
         * to the base or a spot currency. It can produce mixed results depending on how the data is being aggregated
         * for a report.
         */
        const CURRENCY_CONVERSION_TYPE_ACTUAL = 1;

        /**
         * Currency Conversion Type for rendering currency information.  "Base" means that currency data is converted
         * into the system base currency and displayed in this currency
         */
        const CURRENCY_CONVERSION_TYPE_BASE   = 2;

        /**
         * Currency Conversion Type for rendering currency information.  "Spot" means the currency is converted into
         * the base currency and then converted into a spot currency defined by the user when creating the report
         */
        const CURRENCY_CONVERSION_TYPE_SPOT   = 3;

        /**
         * User defined description of the report.  This is optional
         * @var string
         */
        private $description;

        /**
         * Set from SavedReport
         * @var object ExplicitReadWriteModelPermissions
         */
        private $explicitReadWriteModelPermissions;

        /**
         * Id of the saved report if it has already been saved
         * @var integer
         */
        private $id;

        /**
         * Module class name that the report is constructed on
         * @var string
         */
        private $moduleClassName;

        /**
         * User defined name of the report
         * @var string
         */
        private $name;

        /**
         * Set from the SavedReport
         * @var object User
         */
        private $owner;

        /**
         * Defines the report type
         * @var string
         */
        private $type;

        /**
         * Defines the filters structure. An example is "1 AND 2".  This example would be used if there are 2 filters
         * for the report.
         * @var
         */
        private $filtersStructure;

        /**
         * Array of of FilterForReportForm objects
         * @var array
         */
        private $filters                    = array();

        /**
         * Array of OrderByFoReportForm objects
         * @var array
         */
        private $orderBys                   = array();

        /**
         * Array of DisplayAttributeForReportForm objects
         * @var array
         */
        private $displayAttributes          = array();

        /**
         * Array of DrillDownDisplayAttributeForReportForm objects
         * @var array
         */
        private $drillDownDisplayAttributes = array();

        /**
         * Array of GroupByForReportForm objects
         * @var array
         */
        private $groupBys                   = array();

        /**
         * @var object ChartForReportForm
         */
        private $chart;

        /**
         * Currency conversion type used for rendering currency data.  There are three types
         * CURRENCY_CONVERSION_TYPE_ACTUAL, CURRENCY_CONVERSION_TYPE_BASE, and CURRENCY_CONVERSION_TYPE_SPOT
         * @var integer
         */
        private $currencyConversionType;

        /**
         * If the $currencyConversionType is CURRENCY_CONVERSION_TYPE_SPOT, then this property is utilized to define
         * the currency code for spot conversion
         * @var string
         */
        private $spotConversionCurrencyCode;

        /**
         * @return array of report type values and labels
         */
        public static function getTypeDropDownArray()
        {
            return array(self::TYPE_ROWS_AND_COLUMNS  => Zurmo::t('ReportsModule', 'Rows and Columns'),
                         self::TYPE_SUMMATION         => Zurmo::t('ReportsModule', 'Summation'),
                         self::TYPE_MATRIX            => Zurmo::t('ReportsModule', 'Matrix'));
        }

        /**
         * Based on the current user, return the reportable modules and their display labels.  Only include modules
         * that the user has a right to access.
         * @return array of module class names and display labels.
         */
        public static function getReportableModulesAndLabelsForCurrentUser()
        {
            $moduleClassNamesAndLabels = array();
            $modules = Module::getModuleObjects();
            foreach (self::getReportableModulesClassNamesCurrentUserHasAccessTo() as $moduleClassName)
            {
                if ($moduleClassName::getStateMetadataAdapterClassName() != null)
                {
                    $reportRules = ReportRules::makeByModuleClassName($moduleClassName);
                    $label       = $reportRules->getVariableStateModuleLabel(Yii::app()->user->userModel);
                }
                else
                {
                    $label = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural');
                }
                if ($label != null)
                {
                    $moduleClassNamesAndLabels[$moduleClassName] = $label;
                }
            }
            return $moduleClassNamesAndLabels;
        }

        /**
         * @return array of module class names and display labels the current user has access to
         */
        public static function getReportableModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module::isReportable())
                {
                    if (ReportSecurityUtil::canCurrentUserCanAccessModule(get_class($module)))
                    {
                        $moduleClassNames[] = get_class($module);
                    }
                }
            }
            return $moduleClassNames;
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('ReportsModule', '(Unnamed)');
            }
            return $this->name;
        }

        /**
         * Returns true if the current user can render a report's results properly.  This method checks to see if the
         * user has full access to all the related modules and data that the report uses in construction.  This method
         * is needed because it is possible the author of a report added access for users that do not have complete
         * rights to the modules that are part of the report.  It is also possible this access changed over time and
         * a report that was once properly rendered is no longer.
         * @return bool
         */
        public function canCurrentUserProperlyRenderResults()
        {
            if (!ReportSecurityUtil::canCurrentUserCanAccessModule($this->moduleClassName))
            {
                return false;
            }
            if (!ReportSecurityUtil::canCurrentUserAccessAllComponents($this->displayAttributes))
            {
                return false;
            }
            if (!ReportSecurityUtil::canCurrentUserAccessAllComponents($this->filters))
            {
                return false;
            }
            if (!ReportSecurityUtil::canCurrentUserAccessAllComponents($this->orderBys))
            {
                return false;
            }
            if (!ReportSecurityUtil::canCurrentUserAccessAllComponents($this->groupBys))
            {
                return false;
            }
            if (!ReportSecurityUtil::canCurrentUserAccessAllComponents($this->drillDownDisplayAttributes))
            {
                return false;
            }
            return true;
        }

        /**
         * @return string
         */
        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        /**
         * @param $moduleClassName string
         */
        public function setModuleClassName($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $this->moduleClassName = $moduleClassName;
        }

        /**
         * @return string
         */
        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @param $description
         */
        public function setDescription($description)
        {
            assert('is_string($description) || $description == null');
            $this->description = $description;
        }

        /**
         * @param $filtersStructure string
         */
        public function setFiltersStructure($filtersStructure)
        {
            assert('is_string($filtersStructure)');
            $this->filtersStructure = $filtersStructure;
        }

        /**
         * @return array of FilterForReportForm objects
         */
        public function getFiltersStructure()
        {
            return $this->filtersStructure;
        }

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param $id int
         */
        public function setId($id)
        {
            assert('is_int($id)');
            $this->id = $id;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param $name string
         */
        public function setName($name)
        {
            assert('is_string($name)');
            $this->name = $name;
        }

        /**
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }

        /**
         * @param $type string
         */
        public function setType($type)
        {
            assert('$type == self::TYPE_ROWS_AND_COLUMNS || $type == self::TYPE_SUMMATION || $type == self::TYPE_MATRIX');
            $this->type = $type;
        }

        /**
         * @return int
         */
        public function getCurrencyConversionType()
        {
            return $this->currencyConversionType;
        }

        /**
         * @param $currencyConversionType int
         */
        public function setCurrencyConversionType($currencyConversionType)
        {
            assert('is_int($currencyConversionType)');
            $this->currencyConversionType = $currencyConversionType;
        }

        /**
         * @return string
         */
        public function getSpotConversionCurrencyCode()
        {
            return $this->spotConversionCurrencyCode;
        }

        /**
         * @param $spotConversionCurrencyCode string
         */
        public function setSpotConversionCurrencyCode($spotConversionCurrencyCode)
        {
            assert('is_string($spotConversionCurrencyCode)');
            $this->spotConversionCurrencyCode = $spotConversionCurrencyCode;
        }

        /**
         * @return float
         */
        public function getFromBaseToSpotRate()
        {
            return 1 / Yii::app()->currencyHelper->getConversionRateToBase($this->spotConversionCurrencyCode);
        }

        /**
         * @return bool
         */
        public function isNew()
        {
            if ($this->id > 0)
            {
                return false;
            }
            return true;
        }

        /**
         * @return object
         */
        public function getOwner()
        {
            if ($this->owner == null)
            {
                $this->owner = Yii::app()->user->userModel;
            }
            return $this->owner;
        }

        /**
         * @param User $owner
         */
        public function setOwner(User $owner)
        {
            $this->owner = $owner;
        }

        /**
         * @return array of FilterForReportForm objects
         */
        public function getFilters()
        {
            return $this->filters;
        }

        /**
         * @param FilterForReportForm $filter
         */
        public function addFilter(FilterForReportForm $filter)
        {
            $this->filters[] = $filter;
        }

        /**
         * Removes all FilterForReportForm objects on this report
         */
        public function removeAllFilters()
        {
            $this->filters   = array();
        }

        /**
         * @return array of GroupByForReportForm objects
         */
        public function getGroupBys()
        {
            return $this->groupBys;
        }

        /**
         * @param GroupByForReportForm $groupBy
         */
        public function addGroupBy(GroupByForReportForm $groupBy)
        {
            $this->groupBys[] = $groupBy;
        }

        /**
         * Removes all GroupByForReportForm objects on this report
         */
        public function removeAllGroupBys()
        {
            $this->groupBys   = array();
        }

        /**
         * @return array of OrderByForReportForm objects
         */
        public function getOrderBys()
        {
            return $this->orderBys;
        }

        /**
         * @param OrderByForReportForm $orderBy
         */
        public function addOrderBy(OrderByForReportForm $orderBy)
        {
            $this->orderBys[] = $orderBy;
        }

        /**
         * Removes all OrderByForReportForm objects on this report
         */
        public function removeAllOrderBys()
        {
            $this->orderBys   = array();
        }

        /**
         * @return array of DisplayAttributeForReportForm objects
         */
        public function getDisplayAttributes()
        {
            return $this->displayAttributes;
        }

        /**
         * @param DisplayAttributeForReportForm $displayAttribute
         */
        public function addDisplayAttribute(DisplayAttributeForReportForm $displayAttribute)
        {
            $this->displayAttributes[] = $displayAttribute;
        }

        /**
         * Removes all DisplayAttributeForReportForm objects on this report
         */
        public function removeAllDisplayAttributes()
        {
            $this->displayAttributes   = array();
        }

        /**
         * @return array of DrillDownDisplayAttributeForReportForm objects
         */
        public function getDrillDownDisplayAttributes()
        {
            return $this->drillDownDisplayAttributes;
        }

        /**
         * @param DrillDownDisplayAttributeForReportForm $drillDownDisplayAttribute
         */
        public function addDrillDownDisplayAttribute(DrillDownDisplayAttributeForReportForm $drillDownDisplayAttribute)
        {
            $this->drillDownDisplayAttributes[] = $drillDownDisplayAttribute;
        }

        /**
         * Removes all DrillDownDisplayAttributeForReportForm objects on this report
         */
        public function removeAllDrillDownDisplayAttributes()
        {
            $this->drillDownDisplayAttributes   = array();
        }

        /**
         * @return ChartForReportForm|object
         */
        public function getChart()
        {
            if ($this->chart == null)
            {
                $this->chart     = new ChartForReportForm();
            }
            return $this->chart;
        }

        /**
         * @param ChartForReportForm $chart
         */
        public function setChart(ChartForReportForm $chart)
        {
            $this->chart = $chart;
        }

        /**
         * Returns true if the report has a chart
         * @return bool
         */
        public function hasChart()
        {
            if ($this->getChart()->type == null)
            {
                return false;
            }
            return true;
        }

        /**
         * @return ExplicitReadWriteModelPermissions|object
         */
        public function getExplicitReadWriteModelPermissions()
        {
            if ($this->explicitReadWriteModelPermissions == null)
            {
                $this->explicitReadWriteModelPermissions = new ExplicitReadWriteModelPermissions();
            }
            return $this->explicitReadWriteModelPermissions;
        }

        /**
         * Set from the value in the SavedReport
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        /**
         * Returns true if at least one filter is available at runtime.
         * @return bool
         */
        public function hasRuntimeFilters()
        {
            foreach ($this->getFilters() as $filter)
            {
                if ($filter->availableAtRunTime)
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * Given an attributeIndexOrDerivedType, return the key of the $displayAttributes that corresponds to the
         * DisplayAttributeForReportForm object that has the given attribute
         * @param $attribute
         * @return int|null|string
         */
        public function getDisplayAttributeIndex($attribute)
        {
            foreach ($this->displayAttributes as $key => $displayAttribute)
            {
                if ($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $key;
                }
            }
            return null;
        }

        /**
         * Given an attributeIndexOrDerivedType, return the DisplayAttributeForReportForm object that has that
         * attributeIndexOrDerivedType
         * @param $attribute
         * @return mixed
         * @throws NotFoundException if it is not found
         */
        public function getDisplayAttributeByAttribute($attribute)
        {
            foreach ($this->getDisplayAttributes() as $displayAttribute)
            {
                if ($attribute == $displayAttribute->attributeIndexOrDerivedType)
                {
                    return $displayAttribute;
                }
            }
            throw new NotFoundException();
        }

        /**
         * Utilized for summation with drill down rows.  For a given group, the grouped value needs to be used
         * as a filter for the drilled down row.  This method will add that groupBy as a filter and update the
         * filterStructure accordingly.
         * @param array $getData
         */
        public function resolveGroupBysAsFilters(Array $getData)
        {
            $newStartingStructurePosition = count($this->filters) + 1;
            $structure = null;
            foreach ($this->getGroupBys() as $groupBy)
            {
                $index = ReportResultsRowData::resolveDataParamKeyForDrillDown($groupBy->attributeIndexOrDerivedType);
                $value = $getData[$index];
                $filter                              = new FilterForReportForm($groupBy->getModuleClassName(),
                                                       $groupBy->getModelClassName(),
                                                       $this->type);
                $filter->attributeIndexOrDerivedType = $groupBy->attributeIndexOrDerivedType;
                self::resolveGroupByAsFilterValue($value, $filter);

                $this->addFilter($filter);
                if ($structure != null)
                {
                    $structure .= ' AND ';
                }
                $structure .= $newStartingStructurePosition;
                $newStartingStructurePosition++;
            }
            $structure = '(' . $structure . ')';
            if ($this->filtersStructure != null)
            {
                $this->filtersStructure .= ' AND ';
            }
            $this->filtersStructure .= $structure;
        }

        /**
         * Given a value and a filter, resolve the value for being null or not.  If null then a different operator
         * is used on the value than if it is not null.
         * @param $value
         * @param FilterForReportForm $filter
         */
        protected static function resolveGroupByAsFilterValue($value, FilterForReportForm $filter)
        {
            if ($value != null)
            {
                $filter->operator                    = OperatorRules::TYPE_EQUALS;
                $filter->value                       = $value;
            }
            else
            {
                $filter->operator                    = OperatorRules::TYPE_IS_NULL;
            }
        }
    }
?>