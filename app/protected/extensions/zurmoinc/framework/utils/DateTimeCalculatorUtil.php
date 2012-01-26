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
     * Helper functionality for making date/time calculations
     * on a given date/time stamp.
     */
    class DateTimeCalculatorUtil
    {
        /**
         * The calculation will be done for yesterday.
         */
        const YESTERDAY = 1;

        /**
         * The calculation will be done for today.
         */
        const TODAY = 2;

        /**
         * The calculation will be done for tomorrow.
         */
        const TOMORROW = 3;

        /**
         * The calculation will be done for now. This is a dateTime calculation.
         */
        const NOW = 4;

        /**
         * Calculate a date/time stamp given a calculation value and DateTime object
         * @param $calculation corresponds to a calculation value from this class.
         * @see http://www.php.net/manual/en/class.datetime.php
         */
        public static function calculateNew($calculation, DateTime $dateTime)
        {
            assert('is_int($calculation)');
            if ($calculation == self::YESTERDAY)
            {
                $dateTime->modify('-1 day');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                            $dateTime->getTimestamp());
            }
            if ($calculation == self::TODAY)
            {
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                            $dateTime->getTimestamp());
            }
            if ($calculation == self::TOMORROW)
            {
                $dateTime->modify('+1 day'); // Not Coding Standard
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                            $dateTime->getTimestamp());
            }
            if ($calculation == self::NOW)
            {
                return DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            }
            throw new NotSupportedException();
        }

        /**
         * Given an integer representing a count of days from the present day, returns a DB formatted date stamp based
         * on that calculation.
         * @param integer $daysFromNow
         */
        public static function calculateNewByDaysFromNow($daysFromNow, DateTime $dateTime)
        {
            assert('is_int($daysFromNow)');
            $dateTime->modify($daysFromNow . ' day');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
        }
    }
?>
