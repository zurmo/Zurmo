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
     * Base class defining rules for game levels
     */
    abstract class GameLevelRules
    {
        /**
         * Defines the last level for the level type.
         * @var integer
         */
        protected static $lastLevel     = null;

        /**
         * Array of data that provides the point value required to move up to each level.
         * @var array
         */
        protected static $levelPointMap = array();

       /**
        * Can be used by application component to override and set last level value.
        * @param integer $level
        */
        public static function setLastLevel($level)
        {
            assert('is_int($level)');
            static::$lastLevel = $level;
        }

       /**
        * Can be used by application component to override and set level point map.
        * @param array $levelPointMap
        */
        public static function setLevelPointMap($levelPointMap)
        {
            assert('is_array($levelPointMap)');
            static::$levelPointMap = $levelPointMap;
        }

        /**
         * @param integer $level
         */
        public static function isLastLevel($level)
        {
            assert('is_int($level)');
            if ($level == static::$lastLevel)
            {
                return true;
            }
            return false;
        }

        /**
         * @param integer $level
         */
        public static function getMinimumPointsForLevel($level)
        {
            assert('is_int($level)');
            if (isset(static::$levelPointMap[$level]))
            {
                return static::$levelPointMap[$level];
            }
            throw new NotSupportedException();
        }

        public static function hasBonusPointsOnLevelChange()
        {
            return false;
        }

        /**
         * Override in child if you want to have bonus points. This will define what point type the bonus points
         * are applied towards
         */
        public static function getLevelBonusPointType()
        {
            throw new NotImplementedException();
        }

        /**
         * Override in child if you want to have bonus points. This will return the bonus points applicable given
         * a $level
         */
        public static function getLevelBonusPointValue($level)
        {
            assert('is_int($level)');
            throw new NotImplementedException();
        }

        /**
         * Override in child to have a display label for the type of level.
         */
        public static function getDisplayLabel()
        {
            throw new NotImplementedException();
        }
    }
?>