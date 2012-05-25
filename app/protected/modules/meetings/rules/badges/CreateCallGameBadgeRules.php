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
     * Class for defining the badge associated with creating a new call
     */
    class CreateCallGameBadgeRules extends GameBadgeRules
    {
        public static function getDisplayName()
        {
            return Yii::t('Default', 'Creating Calls');
        }

        public static function badgeGradeUserShouldHaveByPointsAndScores($userPointsByType, $userScoresByType)
        {
            assert('is_array($userPointsByType)');
            assert('is_array($userScoresByType)');
            if (isset($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]))
            {
                if ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 1)
                {
                    return 0;
                }
                if ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 2)
                {
                    return 1;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 11)
                {
                    return 2;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 26)
                {
                    return 3;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 51)
                {
                    return 4;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 76)
                {
                    return 5;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 101)
                {
                    return 6;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 126)
                {
                    return 7;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 151)
                {
                    return 8;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 176)
                {
                    return 9;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 201)
                {
                    return 10;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 226)
                {
                    return 11;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value < 251)
                {
                    return 12;
                }
                elseif ($userScoresByType[MeetingGamificationRules::SCORE_TYPE_CREATE_CALL]->value >= 300)
                {
                    return 13;
                }
            }
            return 0;
        }
    }
?>