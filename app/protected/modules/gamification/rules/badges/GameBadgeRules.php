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
     * Base class defining rules for game badges
     */
    abstract class GameBadgeRules
    {
        public static function getDisplayName()
        {
            throw new NotImplementedException();
        }

        /**
         * @return The type of the GameBadgeRules
         */
        public static function getType()
        {
            $name = get_called_class();
            $name = substr($name, 0, strlen($name) - strlen('GameBadgeRules'));
            return $name;
        }

        /**
         * @return array of available badge rule class names
         */
        public static function getBadgeRulesData()
        {
            $badgeRulesData       = array();
            $modules              = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $rulesClassNames = $module::getAllClassNamesByPathFolder('rules.badges');
                foreach ($rulesClassNames as $ruleClassName)
                {
                    $classToEvaluate     = new ReflectionClass($ruleClassName);
                    if (!$classToEvaluate->isAbstract())
                    {
                        $badgeRulesData[] = $ruleClassName;
                    }
                }
            }
            return $badgeRulesData;
        }

        /**
         * Given a user's points and scores, determine if the user should have this badge.  And if so, which 'grade'
         * the badge should be.
         * @param array $userPointsByType
         * @param array $userScoresByType
         * @return integer.  Returns 0 if the user should not have this badge.
         */
        public static function badgeGradeUserShouldHaveByPointsAndScores($userPointsByType, $userScoresByType)
        {
            assert('is_array($userPointsByType)');
            assert('is_array($userScoresByType)');
            throw new NotImplementedException();
        }

        /**
         * For a given badge, when it is first received by a user, are there bonus points?
         * @return boolean.
         */
        public static function hasBonusPointsOnCreation()
        {
            return true;
        }

        /**
         * For a given badge, when it has a grade change for a user, are there bonus points?
         * @return boolean.
         */
        public static function hasBonusPointsOnGradeChange()
        {
            return true;
        }

        /**
         * @return null if no bonus points for receiving the badge, or returns integer.
         */
        public static function getNewBonusPointType()
        {
            return GamePoint::TYPE_USER_ADOPTION;
        }

        /**
         * Implement in child class.
         * @return null if no bonus points for changing the grade of a badge, or returns integer.
         */
        public static function getNewBonusPointValue()
        {
            return 50;
        }

        /**
         * @return Point type of the bonus points.
         */
        public static function getGradeBonusPointType()
        {
            return GamePoint::TYPE_USER_ADOPTION;
        }

        /**
         * @param integer $grade
         * @return integer of point value based on specified grade.
         */
        public static function getGradeBonusPointValue($grade)
        {
            assert('is_int($grade)');
            if ($grade == 1)
            {
                return 50;
            }
            elseif ($grade == 2)
            {
                return 150;
            }
            elseif ($grade == 3)
            {
                return 300;
            }
            elseif ($grade == 4)
            {
                return 500;
            }
            elseif ($grade == 5)
            {
                return 750;
            }
            elseif ($grade == 6)
            {
                return 1000;
            }
            elseif ($grade == 7)
            {
                return 1250;
            }
            elseif ($grade == 8)
            {
                return 1500;
            }
            elseif ($grade == 9)
            {
                return 1750;
            }
            elseif ($grade == 10)
            {
                return 2000;
            }
            elseif ($grade == 11)
            {
                return 2250;
            }
            elseif ($grade == 12)
            {
                return 2500;
            }
            elseif ($grade == 13)
            {
                return 3000;
            }
        }
    }
?>