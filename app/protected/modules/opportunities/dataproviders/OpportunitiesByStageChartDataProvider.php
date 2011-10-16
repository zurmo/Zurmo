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
            $sql       = static::makeChartSqlQuery();
            $rows      = R::getAll($sql);
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

        protected static function makeChartSqlQuery()
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Opportunity');
            Opportunity::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                                                      $joinTablesAdapter,
                                                                      $where,
                                                                      $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $sumPart                   = "{$quote}currencyvalue{$quote}.{$quote}value{$quote} ";
            $sumPart                  .= "* {$quote}currencyvalue{$quote}.{$quote}ratetobase{$quote}";
            $selectQueryAdapter->addClause('customfield', 'value', 'stage');
            $selectQueryAdapter->addSummationClause($sumPart, 'amount');
            $joinTablesAdapter->addFromTableAndGetAliasName('customfield', 'stage_ownedcustomfield_id', 'opportunity');
            $joinTablesAdapter->addFromTableAndGetAliasName('currencyvalue', 'amount_currencyvalue_id', 'opportunity');
            $groupBy                   = "{$quote}customfield{$quote}.{$quote}value{$quote}";
            $sql                       = SQLQueryUtil::makeQuery('opportunity', $selectQueryAdapter,
                                                                 $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }
    }
?>