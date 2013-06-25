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

    class MarketingEmailsInThisListChartDataProvider extends MarketingGroupByEmailMessagesChartDataProvider
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
                    $chartData[$chartIndexToCompare][self::QUEUED]        = $row[self::QUEUED];
                    $chartData[$chartIndexToCompare][self::SENT]          = $row[self::SENT];
                    $chartData[$chartIndexToCompare][self::UNIQUE_OPENS]  = $row[self::UNIQUE_OPENS];
                    $chartData[$chartIndexToCompare][self::UNIQUE_CLICKS] = $row[self::UNIQUE_CLICKS];
                    $chartData[$chartIndexToCompare][self::BOUNCED]       = $row[self::BOUNCED];
                    $chartData[$chartIndexToCompare][self::UNSUBSCRIBED]  = $row[self::UNSUBSCRIBED];
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
            //todo: should fix and get proper table name of attribute instead of passing in item
            $groupBy             = $this->resolveGroupBy('EmailMessage', 'sentDateTime');
            $beginDateTime       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->beginDate);
            $endDateTime         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->endDate);
            if ($this->marketingList == null)
            {
                $searchAttributeData = static::makeCampaignsSearchAttributeData('createdDateTime', $beginDateTime,
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
                $searchAttributeData = static::makeAutorespondersSearchAttributeData('createdDateTime', $beginDateTime,
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
                        $combinedRows[$chartIndexToCompare][self::QUEUED]        += $row[self::QUEUED];
                        $combinedRows[$chartIndexToCompare][self::SENT]          += $row[self::SENT];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_OPENS]  += $row[self::UNIQUE_OPENS];
                        $combinedRows[$chartIndexToCompare][self::UNIQUE_CLICKS] += $row[self::UNIQUE_CLICKS];
                        $combinedRows[$chartIndexToCompare][self::BOUNCED]       += $row[self::BOUNCED];
                        $combinedRows[$chartIndexToCompare][self::UNSUBSCRIBED]  += $row[self::UNSUBSCRIBED];
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
            $itemTableName             = Item::getTableName('Item');
            $emailMessageTableName     = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName    = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $createdDateTimeColumnName = Item::getColumnNameByAttribute('createdDateTime');
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('Campaign');
            $where                     = RedBeanModelDataProvider::makeWhere('Campaign', $searchAttributeData, $joinTablesAdapter);
            Campaign::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                $joinTablesAdapter,
                $where,
                $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $queuedEmailsSelectPart    = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                         $quote . " = '0000-00-00 00:00:00' OR {$quote}{$emailMessageTableName}{$quote}" .
                                         ".{$quote}{$sentDateTimeColumnName}{$quote} IS NULL THEN 1 ELSE 0 END)"; // Not Coding Standard
            $sentEmailsSelectPart      = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                         $quote . " > '0000-00-00 00:00:00' THEN 1 ELSE 0 END)";
            $uniqueOpensSelectPart     = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart    = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            $bouncedSelectPart         = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_BOUNCE);
            $optedOutSelectPart        = static::resolveCampaignTypeSubQuery(EmailMessageActivity::TYPE_UNSUBSCRIBE);//
            $selectQueryAdapter->addDayDateClause($itemTableName, $createdDateTimeColumnName, static::DAY_DATE);
            $selectQueryAdapter->addFirstDayOfWeekDateClause($itemTableName, $createdDateTimeColumnName, static::FIRST_DAY_OF_WEEK_DATE);
            $selectQueryAdapter->addFirstDayOfMonthDateClause($itemTableName, $createdDateTimeColumnName, static::FIRST_DAY_OF_MONTH_DATE);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString($queuedEmailsSelectPart,  static::QUEUED);
            $selectQueryAdapter->addClauseByQueryString($sentEmailsSelectPart,  static::SENT);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $bouncedSelectPart . "))", static::BOUNCED);
            $selectQueryAdapter->addClauseByQueryString("count((" . $optedOutSelectPart . "))", static::UNSUBSCRIBED);
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
            $itemTableName              = Item::getTableName('Item');
            $marketingListTableName     = Autoresponder::getTableName('MarketingList');
            $autoresponderTableName     = Autoresponder::getTableName('Autoresponder');
            $autoresponderItemTableName = AutoresponderItem::getTableName('AutoresponderItem');
            $emailMessageTableName      = EmailMessage::getTableName('EmailMessage');
            $sentDateTimeColumnName     = EmailMessage::getColumnNameByAttribute('sentDateTime');
            $createdDateTimeColumnName  = Item::getColumnNameByAttribute('createdDateTime');
            $joinTablesAdapter          = new RedBeanModelJoinTablesQueryAdapter('Autoresponder');
            MarketingList::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                $joinTablesAdapter,
                $where,
                $selectDistinct);
            $selectQueryAdapter     = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $queuedEmailsSelectPart = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                      $quote . " = '0000-00-00 00:00:00' OR {$quote}{$emailMessageTableName}{$quote}" .
                                      ".{$quote}{$sentDateTimeColumnName}{$quote} IS NULL THEN 1 ELSE 0 END)"; // Not Coding Standard
            $sentEmailsSelectPart   = "sum(CASE WHEN {$quote}{$emailMessageTableName}{$quote}.{$quote}{$sentDateTimeColumnName}" .
                                      $quote . " > '0000-00-00 00:00:00' THEN 1 ELSE 0 END)";
            $uniqueOpensSelectPart  = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_OPEN);
            $uniqueClicksSelectPart = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_CLICK);
            $bouncedSelectPart      = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_BOUNCE);
            $optedOutSelectPart     = static::resolveAutoresponderTypeSubQuery(EmailMessageActivity::TYPE_UNSUBSCRIBE);
            $selectQueryAdapter->addDayDateClause($itemTableName, $createdDateTimeColumnName, static::DAY_DATE);
            $selectQueryAdapter->addFirstDayOfWeekDateClause($itemTableName, $createdDateTimeColumnName, static::FIRST_DAY_OF_WEEK_DATE);
            $selectQueryAdapter->addFirstDayOfMonthDateClause($itemTableName, $createdDateTimeColumnName, static::FIRST_DAY_OF_MONTH_DATE);
            $selectQueryAdapter->addNonSpecificCountClause();
            $selectQueryAdapter->addClauseByQueryString($queuedEmailsSelectPart,  static::QUEUED);
            $selectQueryAdapter->addClauseByQueryString($sentEmailsSelectPart,  static::SENT);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueOpensSelectPart  . "))",  static::UNIQUE_OPENS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $uniqueClicksSelectPart . "))", static::UNIQUE_CLICKS);
            $selectQueryAdapter->addClauseByQueryString("count((" . $bouncedSelectPart . "))", static::BOUNCED);
            $selectQueryAdapter->addClauseByQueryString("count((" . $optedOutSelectPart . "))", static::UNSUBSCRIBED);
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
            return array(self::QUEUED        => 0,
                         self::SENT          => 0,
                         self::UNIQUE_CLICKS => 0,
                         self::UNIQUE_OPENS  => 0,
                         self::BOUNCED       => 0,
                         self::UNSUBSCRIBED  => 0);
        }
    }
?>