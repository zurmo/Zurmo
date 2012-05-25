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
     * Rules for the general level type.
     */
    class GeneralGameLevelRules extends GameLevelRules
    {
        /**
         * Defines the last level for the level type.
         * @var integer
         */
        protected static $lastLevel     = 15;

        /**
         * Array of data that provides the point value required to move up to each level.
         * @var array
         */
        protected static $levelPointMap = array( 1  => 200,
                                                 2  => 500,
                                                 3  => 1000,
                                                 4  => 1750,
                                                 5  => 2750,
                                                 6  => 4000,
                                                 7  => 5500,
                                                 8  => 7500,
                                                 9  => 9750,
                                                 10 => 12250,
                                                 11 => 15000,
                                                 12 => 18000,
                                                 13 => 21250,
                                                 14 => 24750,
                                                 15 => 28500);

        public static function getDisplayLabel()
        {
            return Yii::t('Default', 'General');
        }
    }
?>