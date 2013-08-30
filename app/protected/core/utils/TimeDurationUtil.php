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
     * Helper functionality for working with time duration and calculating durations
     */
    class TimeDurationUtil
    {
        const DURATION_SIGN_POSITIVE = 'Positive';

        const DURATION_SIGN_NEGATIVE = 'Negative';

        const DURATION_TYPE_MINUTE   = 'Minute';

        const DURATION_TYPE_HOUR     = 'Hour';

        const DURATION_TYPE_DAY      = 'Day';

        const DURATION_TYPE_WEEK     = 'Week';

        const DURATION_TYPE_MONTH    = 'Month';

        const DURATION_TYPE_YEAR     = 'Year';

        /**
         * @param integer $initialTimeStamp
         * @param integer $durationInterval
         * @param string $durationSign
         * @param string $durationType
         * @return integer timestamp based on durationInterval, durationSign, and durationType
         */
        public static function resolveNewTimeStampForDuration($initialTimeStamp, $durationInterval, $durationSign, $durationType)
        {
            assert('is_int($initialTimeStamp)');
            assert('is_int($durationInterval)');
            assert('is_string($durationSign)');
            assert('is_string($durationType)');
            if ($durationInterval == 0)
            {
                return $initialTimeStamp;
            }
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $dateTime = DateTime::createFromFormat('U', (int)$initialTimeStamp);
            if ($durationSign == self::DURATION_SIGN_NEGATIVE)
            {
                $dateTime->modify('-' . $durationInterval . ' ' . $durationType); // Not Coding Standard
            }
            else
            {
                $dateTime->modify('+' . $durationInterval . ' ' . $durationType); // Not Coding Standard
            }
            $resolvedTimeStamp = $dateTime->getTimestamp();
            date_default_timezone_set($timeZone);
            return $resolvedTimeStamp;
        }

        public static function getValueAndLabels()
        {
            return array(TimeDurationUtil::DURATION_TYPE_MINUTE => Zurmo::t('Core', 'Minute(s)'),
                         TimeDurationUtil::DURATION_TYPE_HOUR   => Zurmo::t('Core', 'Hour(s)'),
                         TimeDurationUtil::DURATION_TYPE_DAY    => Zurmo::t('Core', 'Day(s)'),
                         TimeDurationUtil::DURATION_TYPE_WEEK   => Zurmo::t('Core', 'Week(s)'),
                         TimeDurationUtil::DURATION_TYPE_MONTH  => Zurmo::t('Core', 'Month(s)'),
                         TimeDurationUtil::DURATION_TYPE_YEAR   => Zurmo::t('Core', 'Year(s)'));
        }

        public static function getDateOnlyValueAndLabels()
        {
            return array(TimeDurationUtil::DURATION_TYPE_DAY    => Zurmo::t('Core', 'Day(s)'),
                         TimeDurationUtil::DURATION_TYPE_WEEK   => Zurmo::t('Core', 'Week(s)'),
                         TimeDurationUtil::DURATION_TYPE_MONTH  => Zurmo::t('Core', 'Month(s)'),
                         TimeDurationUtil::DURATION_TYPE_YEAR   => Zurmo::t('Core', 'Year(s)'));
        }
    }
?>