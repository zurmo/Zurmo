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
     * Helper class for converting date time stamps between time zones and working
     * with date formats and locales.
     */
    class DateTimeUtil
    {
        const DATETIME_FORMAT_DATE_WIDTH = 'short';
        const DATETIME_FORMAT_TIME_WIDTH = 'short';

        /**
         * For the DateTime formatted attributes, get the locale specific date time format string.
         * @return string - datetime format.
         */
        public static function getLocaleDateTimeFormat()
        {
            $dateTimePattern = Yii::app()->locale->getDateTimeFormat();
            $timeFormat      = Yii::app()->locale->getTimeFormat(DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH);
            $dateFormat      = Yii::app()->locale->getDateFormat(DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH);
            return strtr($dateTimePattern, array('{0}' => $timeFormat, '{1}' => $dateFormat));
        }

        public static function getLocaleDateFormat()
        {
            return Yii::app()->locale->getDateFormat(DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH);
        }

        public static function getLocaleTimeFormat()
        {
            return Yii::app()->locale->getTimeFormat(DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH);
        }

        public static function isLocaleTimeDisplayedAs12Hours()
        {
            $timeFormat = DateTimeUtil::getLocaleTimeFormat();
            if (strpos($timeFormat, 'H') === false)
            {
                return true;
            }
            return false;
        }

        public static function resolveTimeStampForDateTimeLocaleFormattedDisplay($value,
                                    $dateWidth = DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH)
        {
            if ($value == null)
            {
                return null;
            }
            return Yii::app()->dateFormatter->formatDateTime($value, $dateWidth, $timeWidth);
        }

        public static function resolveValueForDateLocaleFormattedDisplay($date)
        {
            if ($date == null)
            {
                return null;
            }
            $parsedTimeStamp = CDateTimeParser::parse($date, DatabaseCompatibilityUtil::getDateFormat());
            if ($parsedTimeStamp === false)
            {
                return null;
            }
            return Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), $parsedTimeStamp);
        }

        public static function resolveValueForDateDBFormatted($value)
        {
            if ($value == null)
            {
                return null;
            }
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                CDateTimeParser::parse($value, DateTimeUtil::getLocaleDateFormat()));
        }

        /**
         * @return local timezone adjusted unix timestamp
         */
        public static function convertFromUtcUnixStampByTimeZone($utcTimeStamp, $timeZone)
        {
            assert('is_string($timeZone)');
            $timeZoneObject = new DateTimeZone($timeZone);
            $offset = $timeZoneObject->getOffset(new DateTime());
            return $utcTimeStamp + $offset;
        }

        /**
         * @return timezone adjusted utc unix timestamp
         */
        public static function convertFromLocalUnixStampByTimeZoneToUtcUnixStamp($utcTimeStamp, $timeZone)
        {
            assert('is_string($timeZone)');
            $timeZoneObject = new DateTimeZone($timeZone);
            $offset = $timeZoneObject->getOffset(new DateTime());
            return $utcTimeStamp - $offset;
        }

        public static function convertTimestampToDbFormatDate($timestamp)
        {
            assert('is_int($timestamp)');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                                     $timestamp);
        }

        public static function convertTimestampToDbFormatDateTime($timestamp)
        {
            assert('is_int($timestamp)');
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $result = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateTimeFormat(),
                                                     $timestamp);
            date_default_timezone_set($timeZone);
            return $result;
        }

        public static function convertDbFormatDateTimeToTimestamp($dbFormatDateTime)
        {
            assert('is_string($dbFormatDateTime)');
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $result = strtotime($dbFormatDateTime);
            date_default_timezone_set($timeZone);
            return $result;
        }

        public static function convertTimestampToDisplayFormat($timestamp,
                                    $dateWidth = DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH)
        {
            assert('is_int($timestamp)');
            return self::resolveTimeStampForDateTimeLocaleFormattedDisplay($timestamp, $dateWidth, $timeWidth);
        }

        public static function isValidDbFormattedDate($date) // Basic version, feel free to enhance.
        {
            if($date == '0000-00-00')
            {
                return true;
            }
            return preg_match('/^[1-2][0-9][0-9][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|[3][0-1])$/',
                        $date) == 1;
        }

        public static function isValidDbFormattedDateTime($datetime) // Basic version, feel free to enhance.
        {
            if($datetime == '0000-00-00 00:00:00')
            {
                return true;
            }
            return preg_match(  '/^[1-2][0-9][0-9][0-9]-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|[3][0-1]) ' .
                                '(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/',
                                $datetime) == 1;
        }

        public static function convertDbFormattedDateTimeToLocaleFormattedDisplay($dbFormatDateTime,
                                    $dateWidth = DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                                    $timeWidth = DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH)
        {
            assert('is_string($dbFormatDateTime) || $dbFormatDateTime == null');
            if ($dbFormatDateTime == null || $dbFormatDateTime == '0000-00-00 00:00:00')
            {
                return null;
            }
            $timestamp = self::convertDbFormatDateTimeToTimestamp($dbFormatDateTime);
            return self::convertTimestampToDisplayFormat($timestamp, $dateWidth, $timeWidth);
        }

        /**
         * Given a locale formatted date time string.
         * Convert to db formatted date time setting the seconds always as 00.
         * @param string $localeFormattedDateTime
         */
        public static function convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($localeFormattedDateTime)
        {
            assert('is_string($localeFormattedDateTime) || $localeFormattedDateTime == null');
            if ($localeFormattedDateTime == null)
            {
                return null;
            }
            $timestamp = CDateTimeParser::parse($localeFormattedDateTime, self::getLocaleDateTimeFormat());
            if ($timestamp == null)
            {
                return null;
            }
            $dbFormattedDateTime =  self::convertTimestampToDbFormatDateTime($timestamp);
            //todo deal with potential diffferent db format
            return substr_replace($dbFormattedDateTime, '00', -2, 2);
        }

        /**
         * Given a db formatted date string, return the db formatted dateTime stamp representing the first minute of
         *  the provided date.  This will be adjusted for the current user's timezone.
         *  Example: date provided is 1980-06-03, the first minute is '1980-06-03 00:00:00'.  If the user is in Chicago
         *  then the time needs to be adjusted 5 or 6 hours forward depending on daylight savings time
         * @param string $dateValue - db formatted
         */
        public static function convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($dateValue)
        {
            assert('is_string($dateValue) && DateTimeUtil::isValidDbFormattedDate($dateValue)');
            $greaterThanValue = $dateValue . ' 00:00:00';
            $adjustedTimeStamp = Yii::app()->timeZoneHelper->convertFromLocalTimeStampForCurrentUser(
                                 DateTimeUtil::convertDbFormatDateTimeToTimestamp($greaterThanValue));
            return               DateTimeUtil::convertTimestampToDbFormatDateTime($adjustedTimeStamp);
        }

        /**
         *
         * Given a db formatted date string, return the db formatted dateTime stamp representing the last minute of
         *  the provided date.  This will be adjusted for the current user's timezone.
         *  Example: date provided is 1980-06-03, the first minute is '1980-06-03 23:59:59'.  If the user is in Chicago
         *  then the time needs to be adjusted 5 or 6 hours forward depending on daylight savings time
         * @param string $dateValue - db formatted
         */
        public static function convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($dateValue)
        {
            assert('is_string($dateValue) && DateTimeUtil::isValidDbFormattedDate($dateValue)');
            $lessThanValue     = $dateValue . ' 23:59:59';
            $adjustedTimeStamp = Yii::app()->timeZoneHelper->convertFromLocalTimeStampForCurrentUser(
                                 DateTimeUtil::convertDbFormatDateTimeToTimestamp($lessThanValue));
            return               DateTimeUtil::convertTimestampToDbFormatDateTime($adjustedTimeStamp);
        }

        public static function getFirstDayOfThisMonthDate()
        {
            $dateTime = new DateTime();
            $dateTime->modify('first day of this month');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
        }

        public static function getLastDayOfThisMonthDate()
        {
            $dateTime = new DateTime();
            $dateTime->modify('last day of this month');
            return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
        }
    }
?>
