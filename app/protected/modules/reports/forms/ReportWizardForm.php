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
     * Base class for all report wizard form models.  Manages the interaction between the Report object and the
     * user interface.
     */
    abstract class ReportWizardForm extends WizardForm
    {
        const MODULE_VALIDATION_SCENARIO                        = 'ValidateForModule';

        const FILTERS_VALIDATION_SCENARIO                       = 'ValidateForFilters';

        const DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO            = 'ValidateForDisplayAttributes';

        const DRILL_DOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO = 'ValidateForDisplayAttributes';

        const ORDER_BYS_VALIDATION_SCENARIO                     = 'ValidateForOrderBys';

        const GROUP_BYS_VALIDATION_SCENARIO                     = 'ValidateForGroupBys';

        const CHART_VALIDATION_SCENARIO                         = 'ValidateForChart';

        const GENERAL_DATA_VALIDATION_SCENARIO                  = 'ValidateForGeneralData';

        public $description;

        /**
         * @var string
         */
        public $moduleClassName;

        /**
         * Name of report
         * @var string
         */
        public $name;

        /**
         * Type of report
         * @var string
         */
        public $type;

        /**
         * @var integer
         */
        public $ownerId;

        /**
         * @var string
         */
        public $ownerName;

        /**
         * @var string
         */
        public $filtersStructure;

        /**
         * @var array
         */
        public $filters                    = array();

        /**
         * @var array
         */
        public $groupBys                   = array();

        /**
         * @var array
         */
        public $orderBys                   = array();

        /**
         * @var array
         */
        public $displayAttributes          = array();

        /**
         * @var array
         */
        public $drillDownDisplayAttributes = array();

        /**
         * @var object ChartForReportForm
         */
        public $chart;

        /**
         * @see Report->currencyConversionType
         * @var integer
         */
        public $currencyConversionType;

        /**
         * @see Report->spotConversionCurrencyCode
         * @var string
         */
        public $spotConversionCurrencyCode;

        /**
         * Object containing information on how to setup permissions for the new models that are created during the
         * import process.
         * @var object ExplicitReadWriteModelPermissions
         * @see ExplicitReadWriteModelPermissions
         */
        protected $explicitReadWriteModelPermissions;

        public function rules()
        {
            return array(
                array('description',         'type',               'type' => 'string'),
                array('name',                'type',               'type' => 'string'),
                array('name',                'length',             'max' => 64),
                array('name',                'required',           'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('moduleClassName',     'type',               'type' => 'string'),
                array('moduleClassName',     'length',             'max' => 64),
                array('moduleClassName',     'required',           'on' => self::MODULE_VALIDATION_SCENARIO),
                array('type',                'type',               'type' => 'string'),
                array('type',                'length',             'max' => 64),
                array('type',                'required'),
                array('ownerId',             'type',               'type' => 'integer'),
                array('ownerId',             'required',           'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('ownerName',           'required',           'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('filters',             'validateFilters',
                                             'on' => self::FILTERS_VALIDATION_SCENARIO),
                array('filtersStructure',    'validateFiltersStructure',
                                             'on' => self::FILTERS_VALIDATION_SCENARIO),
                array('displayAttributes',   'validateDisplayAttributes',
                                             'on' => self::DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO),
                array('drillDownAttributes', 'validateDrillDownDisplayAttributes',
                                             'on' => self::DRILL_DOWN_DISPLAY_ATTRIBUTES_VALIDATION_SCENARIO),
                array('orderBys',            'validateOrderBys',   'on' => self::ORDER_BYS_VALIDATION_SCENARIO),
                array('groupBys',            'validateGroupBys',   'on' => self::GROUP_BYS_VALIDATION_SCENARIO),
                array('chart',               'validateChart',      'on' => self::CHART_VALIDATION_SCENARIO),
                array('currencyConversionType',      'type',       'type' => 'integer'),
                array('currencyConversionType',      'required',   'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
                array('spotConversionCurrencyCode',  'type',       'type' => 'string'),
                array('spotConversionCurrencyCode',  'validateSpotConversionCurrencyCode', 'on' => self::GENERAL_DATA_VALIDATION_SCENARIO),
            );
        }

        public function attributeLabels()
        {
            return array(
                'name'                       => Zurmo::t('ReportsModule', 'Name'),
                'ownerId'                    => Zurmo::t('ReportsModule', 'Owner Id'),
                'ownerName'                  => Zurmo::t('ReportsModule', 'Owner Name'),
                'currencyConversionType'     => Zurmo::t('ReportsModule', 'Currency Conversion'),
                'spotConversionCurrencyCode' => Zurmo::t('ReportsModule', 'Spot Currency'),
            );
        }

        /**
         * @return object
         */
        public function getExplicitReadWriteModelPermissions()
        {
            return $this->explicitReadWriteModelPermissions;
        }

        /**
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        /**
         * @return bool
         */
        public function validateFilters()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_FILTERS, 'filters');
        }

        /**
         * Validates if the filter structure is valid.
         */
        public function validateFiltersStructure()
        {
            if (count($this->filters) > 0)
            {
                if (null != $errorMessage = SQLOperatorUtil::
                           resolveValidationForATemplateSqlStatementAndReturnErrorMessage($this->filtersStructure,
                           count($this->filters)))
                {
                    $this->addError('filtersStructure', $errorMessage);
                }
            }
        }

        /**
         * @return bool
         */
        public function validateOrderBys()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_ORDER_BYS, 'orderBys');
        }

        /**
         * @return bool
         */
        public function validateDisplayAttributes()
        {
            $validated = $this->validateComponent(ComponentForReportForm::TYPE_DISPLAY_ATTRIBUTES, 'displayAttributes');
            if (count($this->displayAttributes) == 0)
            {
                $this->addError( 'displayAttributes', Zurmo::t('ReportsModule', 'At least one display column must be selected'));
                $validated = false;
            }
            return $validated;
        }

        /**
         * @return bool
         */
        public function validateDrillDownDisplayAttributes()
        {
            return $this->validateComponent(ComponentForReportForm::TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES, 'drillDownDisplayAttributes');
        }

        /**
         * @return bool
         */
        public function validateGroupBys()
        {
            $validated = $this->validateComponent(ComponentForReportForm::TYPE_GROUP_BYS, 'groupBys');
            $existingGroupByAttributeIndexOrDerivedTypes = array();
            $duplicateGroupByFound                       = false;
            foreach ($this->groupBys as $groupBy)
            {
                if (in_array($groupBy->attributeIndexOrDerivedType, $existingGroupByAttributeIndexOrDerivedTypes))
                {
                    $duplicateGroupByFound = true;
                }
                else
                {
                    $existingGroupByAttributeIndexOrDerivedTypes[] = $groupBy->attributeIndexOrDerivedType;
                }
            }
            if ($duplicateGroupByFound)
            {
                $this->addError( 'groupBys', Zurmo::t('ReportsModule', 'Each grouping must be unique'));
                $validated = false;
            }
            return $validated;
        }

        /**
         * @return bool
         */
        public function validateChart()
        {
            $passedValidation = true;
            if ($this->chart != null)
            {
                $validated = $this->chart->validate();
                if (!$validated)
                {
                    foreach ($this->chart->getErrors() as $attribute => $error)
                    {
                        $this->addError( 'ChartForReportForm_' . $attribute, $error);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        /**
         * @return bool
         */
        public function validateSpotConversionCurrencyCode()
        {
            $passedValidation = true;
            if ($this->currencyConversionType == Report::CURRENCY_CONVERSION_TYPE_SPOT &&
               $this->spotConversionCurrencyCode == null)
            {
                $this->addError('spotConversionCurrencyCode', Zurmo::t('ReportsModule', 'Spot Currency cannot be blank.'));
                $passedValidation = false;
            }
            return $passedValidation;
        }

        /**
         * @return array
         */
        public function getCurrencyConversionTypeDataAndLabels()
        {
            $baseCurrencyCode = Yii::app()->currencyHelper->getBaseCode();
            return array(
                Report::CURRENCY_CONVERSION_TYPE_ACTUAL =>
                    Zurmo::t('ReportsModule', 'Do not convert (Can produce mixed results)'),
                Report::CURRENCY_CONVERSION_TYPE_BASE   =>
                    Zurmo::t('ReportsModule', 'Convert to base currency ({baseCurrencyCode})',
                        array('{baseCurrencyCode}' => $baseCurrencyCode)),
                Report::CURRENCY_CONVERSION_TYPE_SPOT   =>
                    Zurmo::t('ReportsModule', 'Convert to base currency ({baseCurrencyCode}) and then to a spot currency',
                                      array('{baseCurrencyCode}' => $baseCurrencyCode))
            );
        }

        /**
         * @param $componentType
         * @param $componentName
         * @return bool
         */
        protected function validateComponent($componentType, $componentName)
        {
            assert('is_string($componentType)');
            assert('is_string($componentName)');
            $passedValidation = true;
            foreach ($this->{$componentName} as $model)
            {
                if (!$model->validate())
                {
                    foreach ($model->getErrors() as $attribute => $error)
                    {
                        $attributePrefix = static::resolveErrorAttributePrefix($componentType, $model->getRowKey());
                        $this->addError( $attributePrefix . $attribute, $error);
                    }
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }
    }
?>