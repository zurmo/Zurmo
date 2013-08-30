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

    class TimeDurationUtilTest extends BaseTest
    {
        /**
         * @dataProvider dataProviderForTestResolveNewTimeStampForDuration
         */
        public function testResolveNewTimeStampForDuration(
                $initialTimeStamp,
                $durationInterval,
                $durationSign,
                $durationType,
                $expectedTimeStamp)
        {
            $timeZone = date_default_timezone_get();
            date_default_timezone_set('America/Chicago'); //Test using an alternative time zone from GMT
            $this->assertEquals(
                        $expectedTimeStamp,
                        TimeDurationUtil::resolveNewTimeStampForDuration($initialTimeStamp, $durationInterval, $durationSign, $durationType)
                    );
            date_default_timezone_set($timeZone);
        }

        public function dataProviderForTestResolveNewTimeStampForDuration()
        {
            return array(
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_DAY,
                    500000 - 100 * 24 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_DAY,
                    500000 + 100 * 24 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_HOUR,
                    500000 - 100 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_HOUR,
                    500000 + 100 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_MINUTE,
                    500000 - 100 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_MINUTE,
                    500000 + 100 * 60),
              array(500000,
                    1,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_MONTH,
                    500000 - 1 * 31 * 24 * 60 * 60),
              array(500000,
                    1,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_MONTH,
                    500000 + 1 * 31 * 24 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_WEEK,
                    500000 - 100 * 7 * 24 * 60 * 60),
              array(500000,
                    100,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_WEEK,
                    500000 + 100 * 7 * 24 * 60 * 60),
              array(500000,
                    4,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_YEAR,
                    500000 - (4 * 365 + 1) * 24 * 60 * 60),
              array(500000,
                    4,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_YEAR,
                    500000 + (4 * 365 + 1) * 24 * 60 * 60),
              array(123456,
                    0,
                    TimeDurationUtil::DURATION_SIGN_NEGATIVE,
                    TimeDurationUtil::DURATION_TYPE_DAY,
                    123456),
              array(123456,
                    1,
                    TimeDurationUtil::DURATION_SIGN_POSITIVE,
                    TimeDurationUtil::DURATION_TYPE_MINUTE,
                    123516),
            );
        }
    }
