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
     * Class for defining the badge associated with creating a new lead
     */
    class CreateLeadGameBadgeRules extends GameBadgeRules
    {
        public static $valuesIndexedByGrade = array(
            1  => 1,
            2  => 5,
            3  => 10,
            4  => 25,
            5  => 50,
            6  => 75,
            7  => 100,
            8  => 125,
            9  => 150,
            10 => 175,
            11 => 200,
            12 => 225,
            13 => 250
        );

        public static function getPassiveDisplayLabel($value)
        {
            return Zurmo::t('LeadsModule', '{n} LeadsModuleSingularLabel created|{n} LeadsModulePluralLabel created',
                          array_merge(array($value), LabelUtil::getTranslationParamsForAllModules()));
        }

        public static function badgeGradeUserShouldHaveByPointsAndScores($userPointsByType, $userScoresByType)
        {
            assert('is_array($userPointsByType)');
            assert('is_array($userScoresByType)');
            if (isset($userScoresByType[ContactGamificationRules::SCORE_TYPE_CREATE_LEAD]))
            {
                return static::getBadgeGradeByValue((int)$userScoresByType[ContactGamificationRules::SCORE_TYPE_CREATE_LEAD]->value);
            }
            return 0;
        }
    }
?>