<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class MarketingListPerformanceChartDataProvider extends MarketingGroupByEmailMessagesChartDataProvider
    {
        /**
         * @return array
         */
        public function getChartData()
        {
            $chartData = $this->resolveChartDataStructure();
            $rows      = $this->makeCombinedData();
            foreach ($rows as $row)
            {
                $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                if (isset($chartData[$chartIndexToCompare]))
                {
                    $uniqueOpenRate         = NumberUtil::divisionForZero($row[self::UNIQUE_OPENS], $row[self::COUNT]);
                    $uniqueClickThroughRate = NumberUtil::divisionForZero($row[self::UNIQUE_CLICKS], $row[self::COUNT]);
                    $chartData[$chartIndexToCompare][self::UNIQUE_OPEN_RATE]          = round($uniqueOpenRate * 100, 2);
                    $chartData[$chartIndexToCompare][self::UNIQUE_CLICK_THROUGH_RATE] = round($uniqueClickThroughRate * 100, 2);
                }
            }
            $newChartData = array();
            foreach ($chartData as $data)
            {
                $newChartData[] = $data;
            }
            return $newChartData;
        }

        /**
         * @return array
         */
        protected function makeCombinedData()
        {
            $combinedRows        = array();
            $groupBy             = $this->resolveGroupBy('EmailMessage', 'sentDateTime');
            $beginDateTime       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->beginDate);
            $endDateTime         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->endDate);
            if ($this->marketingList == null)
            {
                $searchAttributeData = static::makeCampaignsSearchAttributeData('sentDateTime', $beginDateTime,
                                       $endDateTime, $this->campaign);
                $sql                 = static::makeCampaignsSqlQuery($searchAttributeData, $groupBy);
                $rows                = R::getAll($sql);
                foreach ($rows as $row)
                {
                    $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                    $combinedRows[$chartIndexToCompare] = $row;
                }
            }
            if ($this->campaign == null)
            {
                $searchAttributeData = static::makeAutorespondersSearchAttributeData('sentDateTime', $beginDateTime,
                                       $endDateTime, $this->marketingList);
                $sql                 = static::makeAutorespondersSqlQuery($searchAttributeData, $groupBy);
                $rows                = R::getAll($sql);
                foreach ($rows as $row)
                {
                    $chartIndexToCompare = $row[$this->resolveIndexGroupByToUse()];
                    if (!isset($combinedRows[$chartIndexToCompare]))
                    {
                        $combinedRows[$chartIndexToCompare] = $row;
                    }
                    else
                    {
                        $combinedRows[$chartIndexToCompare][self::COUNT]         += $row[self::COUNT];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_OPENS]  += $row[self::UNIQUE_OPENS];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_CLICKS] += $row[self::UNIQUE_CLICKS];
                    }
                }
            }
            return $combinedRows;
        }

        /**
         * @param array $searchAttributeData
         * @param string $groupBy
         * @return string
         */
        protected static function makeCampaignsSqlQuery($searchAttributeData, $groupBy)
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $campaignTableName         = Campaign::getTableName('Campaign');
            $campaignItemTableName     = CampaignItem::getTableName('CampaignItem');
            $emailMessageTableName     = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName    = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Campaign');
            $where                     = RedBeanModelDataProvider::makeWhere('Campaign', $searchAttributeData, $joinTablesAdapter);
            Campaign::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                         $joinTablesAdapter,
                                         $where,
                                         $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $uniqueOpensSelectPart     = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart    = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            static::addEmailMessageDayDateClause            ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfWeekDateClause ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfMonthDateClause($selectQueryAdapter, $sentDateTimeColumnName);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS);
            $joinTablesAdapter->addLeftTableAndGetAliasName($campaignItemTableName, 'id', $campaignTableName, 'campaign_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $campaignItemTableName, 'id');

            $sql   = SQLQueryUtil::makeQuery($campaignTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }

        /**
         * @param array $searchAttributeData
         * @param string $groupBy
         * @return string
         */
        protected static function makeAutorespondersSqlQuery($searchAttributeData, $groupBy)
        {
            $quote                      = DatabaseCompatibilityUtil::getQuote();
            $where                      = null;
            $selectDistinct             = false;
            $marketingListTableName     = Autoresponder::getTableName('MarketingList');
            $autoresponderTableName     = Autoresponder::getTableName('Autoresponder');
            $autoresponderItemTableName = AutoresponderItem::getTableName('AutoresponderItem');
            $emailMessageTableName      = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName     = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $joinTablesAdapter          = new RedBeanModelJoinTablesQueryAdapter('Autoresponder');
            MarketingList::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                                          $joinTablesAdapter,
                                          $where,
                                          $selectDistinct);
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $uniqueOpensSelectPart  = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            static::addEmailMessageDayDateClause            ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfWeekDateClause ($selectQueryAdapter, $sentDateTimeColumnName);
            static::addEmailMessageFirstDayOfMonthDateClause($selectQueryAdapter, $sentDateTimeColumnName);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS);
            $joinTablesAdapter->addFromTableAndGetAliasName($marketingListTableName, 'marketinglist_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($autoresponderItemTableName, 'id', $autoresponderTableName, 'autoresponder_id');
            $joinTablesAdapter->addLeftTableAndGetAliasName($emailMessageTableName, 'emailmessage_id', $autoresponderItemTableName, 'id');
            $where = RedBeanModelDataProvider::makeWhere('Autoresponder', $searchAttributeData, $joinTablesAdapter);
            $sql   = SQLQueryUtil::makeQuery($autoresponderTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where, null, $groupBy);
            return $sql;
        }

        /**
         * @return array
         */
        protected static function resolveChartDataBaseGroupElements()
        {
            return array(self::UNIQUE_CLICK_THROUGH_RATE => 0, self::UNIQUE_OPEN_RATE => 0);
        }
    }
?>