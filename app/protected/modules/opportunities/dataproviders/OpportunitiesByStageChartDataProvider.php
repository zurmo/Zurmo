<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Build a data set showing opportunities grouped by the stage summed by the amount.
     */
    class OpportunitiesByStageChartDataProvider extends ChartDataProvider
    {
        protected $model;

        public function __construct()
        {
            $this->model = new Opportunity(false);
        }

        public function getXAxisName()
        {
            return $this->model->getAttributeLabel('stage');
        }

        public function getYAxisName()
        {
            return $this->model->getAttributeLabel('amount');
        }

        public function getChartData()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            $sql  = "select {$quote}customfield{$quote}.{$quote}value{$quote} stage, ";
            $sql .= "sum({$quote}currencyvalue{$quote}.{$quote}value{$quote} * {$quote}currencyvalue{$quote}.{$quote}ratetobase{$quote}) amount ";
            $sql .= "from {$quote}opportunity{$quote}, {$quote}customfield{$quote}, {$quote}currencyvalue{$quote} ";
            $sql .= "where {$quote}opportunity{$quote}.{$quote}stage_ownedcustomfield_id{$quote} = ";
            $sql .= "{$quote}customfield{$quote}.{$quote}id{$quote} ";
            $sql .= "and {$quote}opportunity{$quote}.{$quote}amount_currencyvalue_id{$quote} = ";
            $sql .= "{$quote}currencyvalue{$quote}.{$quote}id{$quote} ";
            $sql .= "group by {$quote}customfield{$quote}.{$quote}value{$quote}";
            $rows = R::getAll($sql);
            $chartData = array();
            foreach ($rows as $row)
            {
                $chartData[] = array(
                    'value'        => $this->resolveCurrencyValueConversionRateForCurrentUserForDisplay($row['amount']),
                    'displayLabel' => $row['stage'], //todo: at some point translate to locale language once defined.
                );
            }
            return $chartData;
        }
    }
?>