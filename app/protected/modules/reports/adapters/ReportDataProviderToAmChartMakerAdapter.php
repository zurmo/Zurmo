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
     * Helper class for adapting the ReportDataProvider data to an AmChartMakerAdapter
     */
    class ReportDataProviderToAmChartMakerAdapter
    {
        const FIRST_SERIES_VALUE            = 'FirstSeriesValue';

        const FIRST_SERIES_DISPLAY_LABEL    = 'FirstSeriesDisplayLabel';

        const FIRST_RANGE_DISPLAY_LABEL     = 'FirstRangeDisplayLabel';

        const FIRST_SERIES_FORMATTED_VALUE  = 'FirstSeriesFormattedValue';

        const SECOND_SERIES_VALUE           = 'SecondSeriesValue';

        const SECOND_SERIES_DISPLAY_LABEL   = 'SecondSeriesDisplayLabel';

        const SECOND_SERIES_FORMATTED_VALUE = 'SecondSeriesFormattedValue';

        /**
         * @var Report
         */
        protected $report;

        /**
         * @var array
         */
        protected $data;

        /**
         * @var array
         */
        protected $secondSeriesValueData     = array();

        /**
         * @var array
         */
        protected $secondSeriesDisplayLabels = array();

        /**
         * @var null | integer
         */
        protected $secondSeriesValueCount;

        /**
         * @var
         */
        protected $formattedData;

        /**
         * @param $key
         * @return string
         */
        public static function resolveFirstSeriesValueName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_VALUE . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveFirstSeriesDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_DISPLAY_LABEL . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveFirstRangeDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::FIRST_RANGE_DISPLAY_LABEL . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveFirstSeriesFormattedValueName($key)
        {
            assert('is_int($key)');
            return self::FIRST_SERIES_FORMATTED_VALUE . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveSecondSeriesValueName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_VALUE . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveSecondSeriesDisplayLabelName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_DISPLAY_LABEL . $key;
        }

        /**
         * @param $key
         * @return string
         */
        public static function resolveSecondSeriesFormattedValueName($key)
        {
            assert('is_int($key)');
            return self::SECOND_SERIES_FORMATTED_VALUE . $key;
        }

        /**
         * @param Report $report
         * @param array $data
         * @param array $secondSeriesValueData
         * @param array $secondSeriesDisplayLabels
         * @param null | integer $secondSeriesValueCount
         */
        public function __construct(Report $report, Array $data, Array $secondSeriesValueData = array(),
                                    Array $secondSeriesDisplayLabels = array(),
                                    $secondSeriesValueCount = null)
        {
            assert('is_int($secondSeriesValueCount) || $secondSeriesValueCount == null');
            $this->report                     = $report;
            $this->data                       = $data;
            $this->secondSeriesValueData      = $secondSeriesValueData;
            $this->secondSeriesDisplayLabels  = $secondSeriesDisplayLabels;
            $this->secondSeriesValueCount     = $secondSeriesValueCount;
        }

        /**
         * @return string
         */
        public function getType()
        {
            return $this->report->getChart()->type;
        }

        /**
         * @return array
         */
        public function getData()
        {
            if ($this->formattedData == null)
            {
                $this->formattedData = $this->formatData($this->data);
            }
            return  $this->formattedData;
        }

        /**
         * @return null|integer
         */
        public function getSecondSeriesValueCount()
        {
            return $this->secondSeriesValueCount;
        }

        /**
         * @return bool
         */
        public function isStacked()
        {
            return ChartRules::isStacked($this->getType());
        }

        /**
         * @param $key
         * @return string
         */
        public function getSecondSeriesDisplayLabelByKey($key)
        {
            assert('is_int($key)');
            return $this->secondSeriesDisplayLabels[$key];
        }

        /**
         * @param $data
         * @return array
         */
        protected function formatData($data)
        {
            if (!$this->isStacked())
            {
                return $data;
            }
            foreach ($this->secondSeriesValueData as $secondSeriesKey)
            {
                foreach ($data as $firstSeriesDataKey => $firstSeriesData)
                {
                    if (isset($firstSeriesData[self::resolveFirstSeriesValueName($secondSeriesKey)]) &&
                        !isset($firstSeriesData[self::resolveFirstSeriesFormattedValueName($secondSeriesKey)]))
                    {
                        $value            = $firstSeriesData[self::resolveFirstSeriesValueName($secondSeriesKey)];
                        $displayAttribute = $this->report->getDisplayAttributeByAttribute($this->report->getChart()->firstRange);
                        $data[$firstSeriesDataKey][self::resolveFirstSeriesFormattedValueName($secondSeriesKey)] =
                            $this->formatValue($displayAttribute, $value);
                    }
                    if (isset($firstSeriesData[self::resolveSecondSeriesValueName($secondSeriesKey)]) &&
                        !isset($firstSeriesData[self::resolveSecondSeriesFormattedValueName($secondSeriesKey)]))
                    {
                        $value            = $firstSeriesData[self::resolveSecondSeriesValueName($secondSeriesKey)];
                        $displayAttribute = $this->report->getDisplayAttributeByAttribute($this->report->getChart()->secondRange);
                        $data[$firstSeriesDataKey][self::resolveSecondSeriesFormattedValueName($secondSeriesKey)] =
                            $this->formatValue($displayAttribute, $value);
                    }
                }
            }
            return $data;
        }

        /**
         * @param DisplayAttributeForReportForm $displayAttribute
         * @param mixed $value
         * @return mixed
         * @throws NotSupportedException if the currencyConversionType is invalid or null, when the displayAttribute
         * is a currency type
         */
        protected function formatValue(DisplayAttributeForReportForm $displayAttribute, $value)
        {
            if ($displayAttribute->isATypeOfCurrencyValue())
            {
                if ($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_ACTUAL)
                {
                    return Yii::app()->numberFormatter->formatDecimal($value);
                }
                elseif ($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_BASE)
                {
                    return Yii::app()->numberFormatter->formatCurrency($value, Yii::app()->currencyHelper->getBaseCode());
                }
                elseif ($this->report->getCurrencyConversionType() == Report::CURRENCY_CONVERSION_TYPE_SPOT)
                {
                    return Yii::app()->numberFormatter->formatCurrency($value * $this->report->getFromBaseToSpotRate(),
                                                                       $this->report->getSpotConversionCurrencyCode());
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            elseif ($displayAttribute->getDisplayElementType() == 'Decimal')
            {
                return Yii::app()->numberFormatter->formatDecimal($value);
            }
            elseif ($displayAttribute->getDisplayElementType() == 'Integer')
            {
                return Yii::app()->numberFormatter->formatDecimal($value);
            }
            elseif ($displayAttribute->getDisplayElementType()  == 'Date')
            {
                return DateTimeUtil::resolveValueForDateLocaleFormattedDisplay($value);
            }
            elseif ($displayAttribute->getDisplayElementType() == 'DateTime')
            {
                return DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($value);
            }
            else
            {
                return $value;
            }
        }
    }
?>

