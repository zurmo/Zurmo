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
     * Class that defines the chart for use by summation reports.  A chart is optional.
     */
    class ChartForReportForm extends ConfigurableMetadataModel
    {
        /**
         * Type of chart
         * @var string
         */
        public $type;

        /**
         * First series in a chart, for example: opportunities sales stage
         * @var string
         */
        public $firstSeries;

        /**
         * First range in a chart, for example: opportunities amount (SUM)
         * @var string
         */
        public $firstRange;

        /**
         * If the chart supports 2 series, then this would be a second series from the report.
         * An example would grouping by opportunity sales stage and then (second series) by owner.
         * @var string
         */
        public $secondSeries;

        /**
         * If the chart supports 2 ranges, then this would be a second range from the report
         * @var string
         */
        public $secondRange;

        /**
         * Array of available series for this chart.  Depends on other report definitions
         * @var array string
         */
        private $availableSeriesDataAndLabels;

        /**
         * Array of available ranges for this chart.  Depends on other report definitions
         * @var array string
         */
        private $availableRangeDataAndLabels;

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('type',                    'type', 'type' => 'string'),
                array('firstSeries',             'type', 'type' => 'string'),
                array('firstRange',              'type', 'type' => 'string'),
                array('secondSeries',            'type', 'type' => 'string'),
                array('secondRange',             'type', 'type' => 'string'),
                array('type',                    'validateSeriesAndRange'),
            ));
        }

        public function attributeLabels()
        {
            return array();
        }

        /**
         * Depending on other report definitions, a chart may or may not be available.  When creating a chart,
         * define the available series and ranges based on those definitions.  For example, if you have not selected
         * a grouping, then there would be no available series.
         * @param array $availableSeriesDataAndLabels
         * @param array $availableRangeDataAndLabels
         */
        public function __construct($availableSeriesDataAndLabels = array(), $availableRangeDataAndLabels = array())
        {
            assert('is_array($availableSeriesDataAndLabels) || $availableSeriesDataAndLabels == null');
            assert('is_array($availableRangeDataAndLabels) || $availableRangeDataAndLabels == null');
            $this->availableSeriesDataAndLabels = $availableSeriesDataAndLabels;
            $this->availableRangeDataAndLabels  = $availableRangeDataAndLabels;
        }

        /**
         * Validates that the first and second series/ranges are properly formed.
         * @return bool
         */
        public function validateSeriesAndRange()
        {
            $passedValidation = true;
            if ($this->type != null)
            {
                if ($this->firstSeries == null)
                {
                    $this->addError('firstSeries', Zurmo::t('ReportsModule', 'First Series cannot be blank.'));
                    $passedValidation = false;
                }
                if ($this->firstRange == null)
                {
                    $this->addError('firstRange', Zurmo::t('ReportsModule', 'First Range cannot be blank.'));
                    $passedValidation = false;
                }
                if (in_array($this->type, ChartRules::getChartTypesRequiringSecondInputs()) && $this->secondSeries == null)
                {
                    $this->addError('secondSeries', Zurmo::t('ReportsModule', 'Second Series cannot be blank.'));
                    $passedValidation = false;
                }
                if (in_array($this->type, ChartRules::getChartTypesRequiringSecondInputs()) && $this->secondRange == null)
                {
                    $this->addError('secondRange', Zurmo::t('ReportsModule', 'Second Range cannot be blank.'));
                    $passedValidation = false;
                }
                if ($this->firstSeries != null && $this->secondSeries != null && $this->firstSeries == $this->secondSeries)
                {
                    $this->addError('secondSeries', Zurmo::t('ReportsModule', 'Second Series must be unique.'));
                    $passedValidation = false;
                }
                if ($this->firstRange != null && $this->secondRange != null && $this->firstRange == $this->secondRange)
                {
                    $this->addError('secondRange', Zurmo::t('ReportsModule', 'Second Range must be unique.'));
                    $passedValidation = false;
                }
            }
            return $passedValidation;
        }

        /**
         * Returns array of chart types and their corresponding labels
         * @return array
         */
        public function getTypeDataAndLabels()
        {
            $data  = array();
            $types = ChartRules::availableTypes();
            foreach ($types as $type)
            {
                 $data[$type] = ChartRules::getTranslatedTypeLabel($type);
            }
            return $data;
        }

        /**
         * @return array
         */
        public function getAvailableFirstSeriesDataAndLabels()
        {
            return $this->availableSeriesDataAndLabels;
        }

        /**
         * @return array
         */
        public function getAvailableFirstRangeDataAndLabels()
        {
            return $this->availableRangeDataAndLabels;
        }

        /**
         * @return array
         */
        public function getAvailableSecondSeriesDataAndLabels()
        {
            return $this->availableSeriesDataAndLabels;
        }

        /**
         * @return array
         */
        public function getAvailableSecondRangeDataAndLabels()
        {
            return $this->availableRangeDataAndLabels;
        }
    }
?>