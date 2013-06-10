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

    class MarketingListGrowthChartDataProvider extends MarketingChartDataProvider
    {
        /**
         * @return array
         */
        public function getChartData()
        {
            $chartData = array();
            $groupedDateTimeData = static::makeGroupedDateTimeData($this->beginDate, $this->endDate, $this->groupBy, false);
            foreach ($groupedDateTimeData as $groupData)
            {
                $beginDateTime       = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($groupData['beginDate']);
                $endDateTime         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($groupData['endDate']);
                $searchAttributedata = static::makeSearchAttributeData($endDateTime, $this->marketingList);
                $sql                 = static::makeColumnSqlQuery($beginDateTime, $searchAttributedata);
                $row                 = R::getRow($sql);
                $columnData          = array(MarketingChartDataProvider::NEW_SUBSCRIBERS_COUNT      =>
                                                ArrayUtil::getArrayValueAndResolveNullAsZero($row,
                                                    static::NEW_SUBSCRIBERS_COUNT),
                                             MarketingChartDataProvider::EXISTING_SUBSCRIBERS_COUNT  =>
                                                ArrayUtil::getArrayValueAndResolveNullAsZero($row,
                                                    static::EXISTING_SUBSCRIBERS_COUNT),
                                             'displayLabel'        => $groupData['displayLabel'],
                                             'dateBalloonLabel'    => $this->resolveDateBalloonLabel($groupData['displayLabel'])
                );
                $chartData[]         = $columnData;
            }
            return $chartData;
        }

        /**
         * @param string $beginDateTime
         * @param array $searchAttributeData
         * @return string
         */
        protected static function makeColumnSqlQuery($beginDateTime, $searchAttributeData)
        {
            assert('is_string($beginDateTime)');
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $marketingListTableName    = MarketingList::getTableName('MarketingList');
            $marketingListMemberTableName = MarketingListMember::getTableName('MarketingListMember');
            $createdDateTimeColumnName = MarketingListMember::getColumnNameByAttribute('createdDateTime');
            $unsubscribedColumnName    = MarketingListMember::getColumnNameByAttribute('unsubscribed');
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('MarketingList');
            $where = RedBeanModelDataProvider::makeWhere('MarketingList', $searchAttributeData, $joinTablesAdapter);
            MarketingList::resolveReadPermissionsOptimizationToSqlQuery(Yii::app()->user->userModel,
                $joinTablesAdapter,
                $where,
                $selectDistinct);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $newSubscriberSelectPart   = "sum(CASE WHEN {$quote}{$marketingListMemberTableName}{$quote}.{$quote}{$createdDateTimeColumnName}" .
                                         $quote . " > '$beginDateTime' THEN 1 ELSE 0 END)";
            $existingSubscriberSelectPart = "sum(CASE WHEN {$quote}{$marketingListMemberTableName}{$quote}.{$quote}{$createdDateTimeColumnName}" .
                                            $quote . " < '$beginDateTime' AND " .
                                            "{$quote}{$marketingListMemberTableName}{$quote}.{$quote}" .
                                            "{$unsubscribedColumnName}{$quote}=0 THEN 1 ELSE 0 END)"; // Not Coding Standard
            $selectQueryAdapter->addClauseByQueryString($newSubscriberSelectPart,      static::NEW_SUBSCRIBERS_COUNT);
            $selectQueryAdapter->addClauseByQueryString($existingSubscriberSelectPart, static::EXISTING_SUBSCRIBERS_COUNT);
            $joinTablesAdapter->addLeftTableAndGetAliasName($marketingListMemberTableName, 'id', $marketingListTableName, 'marketinglist_id');
            $sql   = SQLQueryUtil::makeQuery($marketingListTableName, $selectQueryAdapter, $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        /**
         * @param string $endDateTime
         * @param null|MarketingList $marketingList
         * @return array
         */
        protected static function makeSearchAttributeData($endDateTime, $marketingList)
        {
            assert('is_string($endDateTime)');
            assert('$marketingList == null || ($marketingList instanceof MarketingList && $marketingList->id > 0)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'marketingListMembers',
                    'relatedAttributeName' => 'createdDateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => $endDateTime,
                ),
            );
            if ($marketingList instanceof MarketingList && $marketingList->id > 0)
            {
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $marketingList->id);
                $searchAttributeData['structure'] = '1 and 2';
            }
            else
            {
                $searchAttributeData['structure'] = '1';
            }
            return $searchAttributeData;
        }
    }
?>