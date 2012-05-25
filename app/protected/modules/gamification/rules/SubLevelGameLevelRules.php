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
     * Base class for sub-level rules
     */
    abstract class SubLevelGameLevelRules extends GameLevelRules
    {
        public static function hasBonusPointsOnLevelChange()
        {
            return true;
        }

        public static function getLevelBonusPointType()
        {
            return GamePoint::TYPE_USER_ADOPTION;
        }

        public static function getLevelBonusPointValue($level)
        {
            assert('is_int($level)');
            if ($level == 1)
            {
                return 100;
            }
            elseif ($level == 1)
            {
                return 110;
            }
            elseif ($level == 2)
            {
                return 110;
            }
            elseif ($level == 3)
            {
                return 130;
            }
            elseif ($level == 4)
            {
                return 140;
            }
            elseif ($level == 5)
            {
                return 150;
            }
            elseif ($level == 6)
            {
                return 160;
            }
            elseif ($level == 7)
            {
                return 170;
            }
        }
    }
?>