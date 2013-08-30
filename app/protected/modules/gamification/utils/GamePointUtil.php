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

        /**
         * @param string $type
         * @param int $startingRank
         * @param null|int $offset
         * @param null|int $count
         * @return array
         */
        public static function getUserLeaderboardData($type, $startingRank = 1, $offset = null, $count = null)
        {
            assert('is_string($type)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            $sql             = static::makeUserLeaderboardSqlQuery($type, $offset, $count);
            $rows            = R::getAll($sql);
            $rank            = $startingRank;
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

        public static function getUserLeaderboardCount($type)
        {
            $sql = self::makeUserLeaderboardCountSqlQuery($type);
            $count = R::getCell($sql);
            if ($count === null || (is_array($count) && count($count) == 0))
            {
                $count = 0;
            }
            return $count;
        }

        /**
         * @param string $type
         * @param null|int $offset
         * @param null|int $count
         * @return string
         */
        protected static function makeUserLeaderboardSqlQuery($type, $offset = null, $count = null)
        {
            assert('is_string($type)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = '_user.hidefromleaderboard is null OR _user.hidefromleaderboard = 0';
            $selectDistinct            = false;
            $orderBy                   = "points desc";
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('GamePointTransaction');
            static::resolveLeaderboardWhereClausesByType($type, $where);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $selectQueryAdapter->addClause('_user', 'id', 'userid');
            $selectQueryAdapter->addSummationClause('gamepointtransaction', 'value', 'points');
            $joinTablesAdapter->addFromTableAndGetAliasName('gamepoint', 'gamepoint_id', 'gamepointtransaction');
            $joinTablesAdapter->addFromTableAndGetAliasName('permitable', 'person_item_id', 'gamepoint', 'item_id');
            $joinTablesAdapter->addFromTableAndGetAliasName('_user', 'id', 'permitable', 'permitable_id');
            $groupBy                   = "{$quote}_user{$quote}.{$quote}id{$quote}";
            $sql                       = SQLQueryUtil::makeQuery('gamepointtransaction', $selectQueryAdapter,
                                         $joinTablesAdapter, $offset, $count, $where, $orderBy, $groupBy);
            return $sql;
        }

        /**
         * @param string $type
         * @return string
         */
        protected static function makeUserLeaderboardCountSqlQuery($type)
        {
            assert('is_string($type)');
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $where                     = '_user.hidefromleaderboard is null OR _user.hidefromleaderboard = 0';
            $selectDistinct            = true;
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter('GamePointTransaction');
            static::resolveLeaderboardWhereClausesByType($type, $where);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $selectQueryAdapter->addCountClause('_user', 'id');
            $joinTablesAdapter->addFromTableAndGetAliasName('gamepoint', 'gamepoint_id', 'gamepointtransaction');
            $joinTablesAdapter->addFromTableAndGetAliasName('permitable', 'person_item_id', 'gamepoint', 'item_id');
            $joinTablesAdapter->addFromTableAndGetAliasName('_user', 'id', 'permitable', 'permitable_id');
            $sql                       = SQLQueryUtil::makeQuery('gamepointtransaction', $selectQueryAdapter,
                                         $joinTablesAdapter, null, null, $where);
            return $sql;
        }

        protected static function resolveLeaderboardWhereClausesByType($type, & $where)
        {
            if ($type == static::LEADERBOARD_TYPE_OVERALL)
            {
                $where = '(' . $where . ')';
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

        /**
         * @param User $user
         * @return array
         */
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
            $rankingData[] = array('typeLabel' => Zurmo::t('GamificationModule', 'Weekly'), 'rank' => $rankLabel);
            if (isset($monthlyData[$user->id]))
            {
                $rankLabel = $monthlyData[$user->id]['rank'];
            }
            else
            {
                $rankLabel = '--';
            }
            $rankingData[] = array('typeLabel' => Zurmo::t('GamificationModule', 'Monthly'), 'rank' => $rankLabel);
            if (isset($overallData[$user->id]))
            {
                $rankLabel = $overallData[$user->id]['rank'];
            }
            else
            {
                $rankLabel = '--';
            }
            $rankingData[] = array('typeLabel' => Zurmo::t('GamificationModule', 'Overall'), 'rank' => $rankLabel);
            return $rankingData;
        }
    }
?>