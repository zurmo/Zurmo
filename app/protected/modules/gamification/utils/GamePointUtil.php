<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Helper class for working with game points.
     */
    class GamePointUtil
    {
        const LEADERBOARD_TYPE_WEEKLY = 'Weekly Leaderboard';

        const LEADERBOARD_TYPE_MONTHLY = 'Monthly Leaderboard';

        const LEADERBOARD_TYPE_OVERALL = 'Overall Leaderboard';

        /**
         * Given an array of point values indexed by point types, add points for the specified user.
         * This will call a method to add points in a deferred way. This means that at the end of the request all
         * deferred points will be added at once.  This is done to improve performance.
         * @param User $user
         * @param array $pointTypeAndValueData
         */
        public static function addPointsByPointData(User $user, $pointTypeAndValueData)
        {
            assert('$user->id > 0');
            assert('is_array($pointTypeAndValueData)');
            foreach ($pointTypeAndValueData as $type => $value)
            {
                Yii::app()->gameHelper->addPointsByUserDeferred($user, $type, $value);
            }
        }

        public static function getUserLeaderboardData($type)
        {
            assert('is_string($type)');
            $sql             = static::makeUserLeaderboardSqlQuery($type);
            $rows            = R::getAll($sql);
            $rank            = 1;
            $leaderboardData = array();
            foreach ($rows as $row)
            {
                $leaderboardData[$row['userid']] = array(
                    'rank'         => StringUtil::resolveOrdinalIntegerAsStringContent(intval($rank)),
                    'userLabel'    => strval(User::getById(intval($row['userid']))),
                    'points'       => intval($row['points'])
                );
                $rank++;
            }
            return $leaderboardData;
        }

        protected static function makeUserLeaderboardSqlQuery($type)
        {
            assert('is_string($type)');
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = null;
            $selectDistinct            = false;
            $orderBy                   = "points desc";
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('GamePointTransaction');
            static::resolveLeaderboardWhereClausesByType($type, $joinTablesAdapter, $where);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $sumPart                   = "{$quote}gamepointtransaction{$quote}.{$quote}value{$quote} ";
            $selectQueryAdapter->addClause('_user', 'id', 'userid');
            $selectQueryAdapter->addSummationClause($sumPart, 'points');
            $joinTablesAdapter->addFromTableAndGetAliasName('gamepoint', 'gamepoint_id', 'gamepointtransaction');
            $joinTablesAdapter->addFromTableAndGetAliasName('permitable', 'person_item_id', 'gamepoint', 'item_id');
            $joinTablesAdapter->addFromTableAndGetAliasName('_user', 'id', 'permitable', 'permitable_id');
            $groupBy                   = "{$quote}_user{$quote}.{$quote}id{$quote}";
            $sql                       = SQLQueryUtil::makeQuery('gamepointtransaction', $selectQueryAdapter,
                                                                 $joinTablesAdapter, null, null, $where, $orderBy, $groupBy);
            return $sql;
        }

        protected static function resolveLeaderboardWhereClausesByType($type,
                                                                       RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                                                       & $where)
        {
            if ($type == static::LEADERBOARD_TYPE_OVERALL)
            {
                //Nothing to add to the where clause.
                return;
            }
            $quote = DatabaseCompatibilityUtil::getQuote();
            $today = MixedDateTimeTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(0);
            if ($type == static::LEADERBOARD_TYPE_WEEKLY)
            {
                $todayMinusSevenDays   = MixedDateTimeTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(-7);
                $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusSevenDays);
                $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                if ($where != null)
                {
                    $where = '(' . $where . ') and ';
                }
                $where .= "{$quote}gamepointtransaction{$quote}.{$quote}createdDateTime{$quote} >= '" . $greaterThanValue . "'";
                $where .= " and ";
                $where .= "{$quote}gamepointtransaction{$quote}.{$quote}createdDateTime{$quote} <= '" . $lessThanValue . "'";
            }
            elseif ($type == static::LEADERBOARD_TYPE_MONTHLY)
            {
                $todayMinusThirtyDays  = MixedDateTimeTypesSearchFormAttributeMappingRules::calculateNewDateByDaysFromNow(-30);
                $greaterThanValue      = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($todayMinusThirtyDays);
                $lessThanValue         = DateTimeUtil::convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($today);
                if ($where != null)
                {
                    $where = '(' . $where . ') and ';
                }
                $where .= "{$quote}gamepointtransaction{$quote}.{$quote}createdDateTime{$quote} >= '" . $greaterThanValue . "'";
                $where .= " and ";
                $where .= "{$quote}gamepointtransaction{$quote}.{$quote}createdDateTime{$quote} <= '" . $lessThanValue . "'";
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function getUserRankingData(User $user)
        {
            $weeklyData  = self::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_WEEKLY);
            $monthlyData = self::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_MONTHLY);
            $overallData = self::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_OVERALL);
            $rankingData = array();
            if (isset($weeklyData[$user->id]))
            {
                $rankLabel = $weeklyData[$user->id]['rank'];
            }
            else
            {
                $rankLabel = '--';
            }
            $rankingData[] = array('typeLabel' => Yii::t('Default', 'Weekly'), 'rank' => $rankLabel);
            if (isset($monthlyData[$user->id]))
            {
                $rankLabel = $monthlyData[$user->id]['rank'];
            }
            else
            {
                $rankLabel = '--';
            }
            $rankingData[] = array('typeLabel' => Yii::t('Default', 'Monthly'), 'rank' => $rankLabel);
            if (isset($overallData[$user->id]))
            {
                $rankLabel = $overallData[$user->id]['rank'];
            }
            else
            {
                $rankLabel = '--';
            }
            $rankingData[] = array('typeLabel' => Yii::t('Default', 'Overall'), 'rank' => $rankLabel);
            return $rankingData;
        }
    }
?>