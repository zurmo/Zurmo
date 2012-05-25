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
     * Helper class for working with game levels.
     */
    class GameLevelUtil
    {
        /**
         * Given a level type and GameLevel, get the point value needed to 'level up' to the next level.
         * @param string $type
         * @param GameLevel $level
         * @return false if there is no next level or returns an integer of the points required to 'level up' to the
         * next level.
         */
        public static function getNextLevelPointValueByTypeAndCurrentLevel($type, GameLevel $level)
        {
            assert('is_string($type)');
            $nextLevel = self::getNextLevelByTypeAndCurrentLevel($type, $level);
            if ($nextLevel !== false)
            {
                $className = $type . 'GameLevelRules';
                return $className::getMinimumPointsForLevel($nextLevel);
            }
            return false;
        }

        /**
         * Given a level type and GameLevel, get the next level.
         * @param string $type
         * @param GameLevel $level
         * @return Next level as integer or false if there is no next level.
         */
        public static function getNextLevelByTypeAndCurrentLevel($type, GameLevel $level)
        {
            assert('is_string($type)');
            $className = $type . 'GameLevelRules';
            if (!$className::isLastLevel((int)$level->value))
            {
                return $level->value + 1;
            }
            return false;
        }

        /**
         * Given a user, return level information including total points, what level the user is at for each level type,
         * and how many points until the user reaches the next of each level type.
         * @param unknown_type $user
         */
        public static function getUserStatisticsData(User $user)
        {
            $statisticsData   = array();
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_GENERAL);
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_SALES);
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_NEW_BUSINESS);
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_ACCOUNT_MANAGEMENT);
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_TIME_MANAGEMENT);
            $statisticsData[] = self::getStatisticsDataForAGivenLevelType($user, GameLevel::TYPE_COMMUNICATION);
            return $statisticsData;
        }

        protected static function getStatisticsDataForAGivenLevelType(User $user, $levelType)
        {
            assert('is_string($levelType) && $levelType != null');
            $rulesClassName      = $levelType . 'GameLevelRules';
            $currentGameLevel    = GameLevel::resolveByTypeAndPerson($levelType, $user);
            $currentPointsData   = GamePoint::getSummationPointsDataByLevelTypeAndUser($user, $levelType);
            if ($currentPointsData != null)
            {
                $currentPoints = $currentPointsData['sum'];
            }
            else
            {
                $currentPoints = 0;
            }
            //If the user has not reached level one, the model has not been saved yet
            if ($currentGameLevel->id < 0)
            {
                $nextLevel                     = 1;
                $trueCurrentGameLevel          = 0;
                $className                     = $levelType . 'GameLevelRules';
                $nextLevelPointValue           = $className::getMinimumPointsForLevel(1);
                $currentLevelMinimumPointValue = 0;
            }
            else
            {
                $nextLevelPointValue             = GameLevelUtil::
                                                   getNextLevelPointValueByTypeAndCurrentLevel($levelType, $currentGameLevel);
                $nextLevel                       = GameLevelUtil::
                                                   getNextLevelByTypeAndCurrentLevel($levelType, $currentGameLevel);
                $currentLevelMinimumPointValue   = $rulesClassName::getMinimumPointsForLevel(intval($currentGameLevel->value));
            }
            if ($nextLevel !== false)
            {

                $pointsCollectedTowardsNextLevel = ($currentPoints - $currentLevelMinimumPointValue);
                if ($pointsCollectedTowardsNextLevel == 0)
                {
                    $nextLevelPercentageComplete = 0;
                }
                else
                {
                    $nextLevelPercentageComplete = ($pointsCollectedTowardsNextLevel / ($nextLevelPointValue - $currentLevelMinimumPointValue)) * 100;
                }
                $trueCurrentGameLevel        = $currentGameLevel->value;
            }
            else
            {
                $nextLevelPercentageComplete = null;
                $trueCurrentGameLevel        = $currentGameLevel->value;
            }

            $rankingData = array(
                'level'                       => (int)$trueCurrentGameLevel,
                'points'                      => (int)$currentPoints,
                'nextLevelPercentageComplete' => round($nextLevelPercentageComplete),
                'levelTypeLabel'              => $rulesClassName::getDisplayLabel(),
            );
            return $rankingData;
        }
    }
?>