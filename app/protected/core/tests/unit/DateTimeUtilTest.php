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

    class DateTimeUtilTest extends BaseTest
    {
        public function teardown()
        {
            parent::teardown();
            //reset language to english
            Yii::app()->setLanguage('en');
        }

        //todo:getDatesBetweenTwoDatesInARange($beginDate, $endDate)

        //todo:getMonthStartAndEndDatesBetweenTwoDatesInARange($beginDate, $endDate)
        //todo:test year spanning range for days, weeks and months
        //todo:test timzeon stuff on these??

        public function testGetDatesBetweenTwoDatesInARange()
        {
            $monthsData = DateTimeUtil::getDatesBetweenTwoDatesInARange('2013-01-20', '2013-01-24');
            $compareData = array('2013-01-20',
                                 '2013-01-21',
                                 '2013-01-22',
                                 '2013-01-23',
                                 '2013-01-24');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getDatesBetweenTwoDatesInARange('2013-06-29', '2013-07-01');
            $compareData = array('2013-06-29',
                                 '2013-06-30',
                                 '2013-07-01');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getDatesBetweenTwoDatesInARange('2012-12-28', '2013-01-03');
            $compareData = array('2012-12-28',
                                 '2012-12-29',
                                 '2012-12-30',
                                 '2012-12-31',
                                 '2013-01-01',
                                 '2013-01-02',
                                 '2013-01-03');
            $this->assertEquals($compareData, $monthsData);
        }

        public function testGetWeekStartAndEndDatesBetweenTwoDatesInARange()
        {
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2013-08-03');
            $this->assertEquals('2013-01-20', $monthsData['2013-01-14']);
            $this->assertEquals('2013-08-04', $monthsData['2013-07-29']);
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2013-01-21', '2013-01-28');
            $compareData = array(
                '2013-01-21' => '2013-01-27',
                '2013-01-28' => '2013-02-03');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2013-01-26');
            $compareData = array(
                '2013-01-14' => '2013-01-20',
                '2013-01-21' => '2013-01-27');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2014-01-26');
            $this->assertEquals('2013-01-20', $monthsData['2013-01-14']);
            $this->assertEquals('2014-01-26', $monthsData['2014-01-20']);
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2012-12-28', '2013-01-03');
            $compareData = array(
                '2012-12-24' => '2012-12-30',
                '2012-12-31' => '2013-01-06');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getWeekStartAndEndDatesBetweenTwoDatesInARange('2013-04-01', '2013-04-01');
            $compareData = array(
                '2013-04-01' => '2013-04-07');
            $this->assertEquals($compareData, $monthsData);
        }

        public function testGetMonthStartAndEndDatesBetweenTwoDatesInARange()
        {
            $monthsData = DateTimeUtil::getMonthStartAndEndDatesBetweenTwoDatesInARange('2013-02-01', '2013-06-01');
            $compareData = array(
                '2013-02-01' => '2013-02-28',
                '2013-03-01' => '2013-03-31',
                '2013-04-01' => '2013-04-30',
                '2013-05-01' => '2013-05-31',
                '2013-06-01' => '2013-06-30');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getMonthStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2013-08-03');
            $compareData = array(
                '2013-01-01' => '2013-01-31',
                '2013-02-01' => '2013-02-28',
                '2013-03-01' => '2013-03-31',
                '2013-04-01' => '2013-04-30',
                '2013-05-01' => '2013-05-31',
                '2013-06-01' => '2013-06-30',
                '2013-07-01' => '2013-07-31',
                '2013-08-01' => '2013-08-31');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getMonthStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2013-01-26');
            $compareData = array(
                '2013-01-01' => '2013-01-31');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getMonthStartAndEndDatesBetweenTwoDatesInARange('2013-01-20', '2014-01-26');
            $compareData = array(
                '2013-01-01' => '2013-01-31',
                '2013-02-01' => '2013-02-28',
                '2013-03-01' => '2013-03-31',
                '2013-04-01' => '2013-04-30',
                '2013-05-01' => '2013-05-31',
                '2013-06-01' => '2013-06-30',
                '2013-07-01' => '2013-07-31',
                '2013-08-01' => '2013-08-31',
                '2013-09-01' => '2013-09-30',
                '2013-10-01' => '2013-10-31',
                '2013-11-01' => '2013-11-30',
                '2013-12-01' => '2013-12-31',
                '2014-01-01' => '2014-01-31');
            $this->assertEquals($compareData, $monthsData);
            $monthsData = DateTimeUtil::getMonthStartAndEndDatesBetweenTwoDatesInARange('2013-04-01', '2013-04-01');
            $compareData = array(
                '2013-04-01' => '2013-04-30');
            $this->assertEquals($compareData, $monthsData);
        }

        public function testGetTimeSinceDisplayContent()
        {
            //30 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (30 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '0 hours ago');

            //58 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (58 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '0 hours ago');

            //61 minutes ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (61 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '1 hour ago');

            //3 hours ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (3 * 60 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '3 hours ago');

            //27 hours ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (27 * 60 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '1 day ago');

            //10 days ago
            $timeStampLatestUpdate  = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (10 * 24 * 60 * 60));
            $timeSinceLastestUpdate = DateTimeUtil::getTimeSinceDisplayContent($timeStampLatestUpdate);
            $this->assertEquals($timeSinceLastestUpdate, '10 days ago');
        }

        public function testConvertTimestampToDbFormatDateTimeAndBackToTimeStamp()
        {
            $time = time();
            $timeZone   = date_default_timezone_get();

            date_default_timezone_set('GMT');
            $gmtDbFormatDateTime = Yii::app()->dateFormatter->format(
                                   DatabaseCompatibilityUtil::getDateTimeFormat(), $time);

            date_default_timezone_set('America/New_York');
            $dbFormatDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($time);
            $timeStamp        = DateTimeUtil::convertDbFormatDateTimeToTimestamp($dbFormatDateTime);
            $this->assertEquals($gmtDbFormatDateTime, $dbFormatDateTime);
            $this->assertEquals($time, $timeStamp);
            date_default_timezone_set($timeZone);
        }

        public function testGetLocaleFormats()
        {
            $language = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $this->assertEquals('M/d/yy h:mm a', DateTimeUtil::getLocaleDateTimeFormat());
            $this->assertEquals('M/d/yy', DateTimeUtil::getLocaleDateFormat());
            $this->assertEquals('h:mm a', DateTimeUtil::getLocaleTimeFormat());
            Yii::app()->setLanguage('de');
            $this->assertEquals('dd.MM.yy HH:mm', DateTimeUtil::getLocaleDateTimeFormat());
            $this->assertEquals('dd.MM.yy', DateTimeUtil::getLocaleDateFormat());
            $this->assertEquals('HH:mm', DateTimeUtil::getLocaleTimeFormat());
        }

        public function testIsLocaleTimeDisplayedAs12Hours()
        {
            $language = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $this->assertTrue(DateTimeUtil::isLocaleTimeDisplayedAs12Hours());
            Yii::app()->setLanguage('de');
            $this->assertFalse(DateTimeUtil::isLocaleTimeDisplayedAs12Hours());
            Yii::app()->setLanguage('fr');
            $this->assertFalse(DateTimeUtil::isLocaleTimeDisplayedAs12Hours());
            Yii::app()->setLanguage('it');
            $this->assertFalse(DateTimeUtil::isLocaleTimeDisplayedAs12Hours());
            Yii::app()->setLanguage('es');
            $this->assertFalse(DateTimeUtil::isLocaleTimeDisplayedAs12Hours());
        }

        public function testResolveTimeStampForDateTimeLocaleFormattedDisplay()
        {
            $value = strtotime("3 June 1980");
            $displayValue = DateTimeUtil::resolveTimeStampForDateTimeLocaleFormattedDisplay($value);
            $this->assertEquals('6/3/80 12:00 AM', $displayValue);
            //For input
            $value = strtotime("3 June 1080");
            $displayValue = DateTimeUtil::resolveTimeStampForDateTimeLocaleFormattedDisplay(
                $value,
                DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH,
                true);
            $this->assertEquals('6/3/1080 12:00 AM', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $value = strtotime("3 June 1980");
            $displayValue = DateTimeUtil::resolveTimeStampForDateTimeLocaleFormattedDisplay($value);
            $this->assertEquals('03.06.80 00:00', $displayValue);
            //For input
            $value = strtotime("3 June 1080");
            $displayValue = DateTimeUtil::resolveTimeStampForDateTimeLocaleFormattedDisplay(
                $value,
                DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH,
                DateTimeUtil::DATETIME_FORMAT_TIME_WIDTH,
                true);
            $this->assertEquals('03.06.1080 00:00', $displayValue);
        }

        public function testResolveValueForDateLocaleFormattedDisplay()
        {
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01');
            $this->assertEquals('7/1/07', $displayValue);
            //For input
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01', DateTimeUtil::DISPLAY_FORMAT_FOR_INPUT);
            $this->assertEquals('7/1/2007', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01');
            $this->assertEquals('01.07.07', $displayValue);
            //For input
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01', DateTimeUtil::DISPLAY_FORMAT_FOR_INPUT);
            $this->assertEquals('01.07.2007', $displayValue);
        }

        public function testResolveValueForDateDBFormatted()
        {
            $displayValue = DateTimeUtil::resolveValueForDateDBFormatted('7/1/2007');
            $this->assertEquals('2007-07-01', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::resolveValueForDateDBFormatted('01.07.2007');
            $this->assertEquals('2007-07-01', $displayValue);
        }

        public function testConvertFromUtcUnixStampByTimeZone()
        {
            $timeZoneObject = new DateTimeZone('America/Chicago');
            $offset = $timeZoneObject->getOffset(new DateTime());
            $this->assertTrue($offset == -18000 || $offset == -21600);
            $utcTimeStamp = time();
            $adjustedTimeStamp = DateTimeUtil::convertFromUtcUnixStampByTimeZone($utcTimeStamp, 'America/Chicago');
            $this->assertEquals($utcTimeStamp + $offset, $adjustedTimeStamp);

            //other locales
            $timeZoneObject = new DateTimeZone('America/New_York');
            $offset = $timeZoneObject->getOffset(new DateTime());
            $this->assertTrue($offset == -18000 || $offset == -14400);
            $adjustedTimeStamp = DateTimeUtil::convertFromUtcUnixStampByTimeZone($utcTimeStamp, 'America/New_York');
            $this->assertEquals($utcTimeStamp + $offset, $adjustedTimeStamp);
        }

        public function testIsValidDbFormattedDate()
        {
            $this->assertTrue (DateTimeUtil::isValidDbFormattedDate('2011-09-23'));
            $this->assertTrue (DateTimeUtil::isValidDbFormattedDate('1756-01-01'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDate('0011-09-23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDate('2011-13-32'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDate('zxczxc'));
        }

        public function testIsValidDbFormattedDateTime()
        {
            $this->assertTrue (DateTimeUtil::isValidDbFormattedDateTime('2011-09-23 23:23:23'));
            $this->assertTrue (DateTimeUtil::isValidDbFormattedDateTime('1756-01-01 00:59:59'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('0011-09-23 23:23:23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('2011-13-32 23:23:23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('1011-09-23 24:23:23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('2011-12-32 23:23:23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('2011-12-32 23:60:23'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('2011-12-32 23:23:60'));
            $this->assertFalse(DateTimeUtil::isValidDbFormattedDateTime('cascacasc'));
        }

        public function testConvertDbFormattedDateTimeToLocaleFormattedDisplay()
        {
            $timeZone   = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $displayValue = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay('1980-06-03 00:00:00');
            $this->assertEquals('6/3/80 12:00 AM', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay('1980-06-03 00:00:00');
            $this->assertEquals('03.06.80 00:00', $displayValue);

            //test null value returns null.
            $displayValue = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(null);
            $this->assertEquals(null, $displayValue);
            date_default_timezone_set($timeZone);
        }

        public function testConvertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero()
        {
            $timeZone   = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $dbValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('6/3/1980 12:00 AM');
            $this->assertEquals('1980-06-03 00:00:00', $dbValue);

            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03.06.1980 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            Yii::app()->setLanguage('it');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03/06/1980 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            Yii::app()->setLanguage('fr');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03/06/1980 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            //test null value returns null.
            $displayValue = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(null);
            $this->assertEquals(null, $displayValue);
            date_default_timezone_set($timeZone);
        }

        public function testGetLocaleDateTimeFormatForInput()
        {
            $timeZone   = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $localDateTimeFormatForInput = DateTimeUtil::getLocaleDateTimeFormatForInput();
            $this->assertEquals('M/d/yyyy h:mm a', $localDateTimeFormatForInput);

            //other locales
            Yii::app()->setLanguage('de');
            $localDateTimeFormatForInput = DateTimeUtil::getLocaleDateTimeFormatForInput();
            $this->assertEquals('dd.MM.yyyy HH:mm', $localDateTimeFormatForInput);

            Yii::app()->setLanguage('it');
            $localDateTimeFormatForInput = DateTimeUtil::getLocaleDateTimeFormatForInput();
            $this->assertEquals('dd/MM/yyyy HH:mm', $localDateTimeFormatForInput);

            Yii::app()->setLanguage('fr');
            $localDateTimeFormatForInput = DateTimeUtil::getLocaleDateTimeFormatForInput();
            $this->assertEquals('dd/MM/yyyy HH:mm', $localDateTimeFormatForInput);
            date_default_timezone_set($timeZone);
        }

        public function testGetLocaleDateFormatForInput()
        {
            $timeZone   = date_default_timezone_get();
            date_default_timezone_set('GMT');
            $localDateFormatForInput = DateTimeUtil::getLocaleDateFormatForInput();
            $this->assertEquals('M/d/yyyy', $localDateFormatForInput);

            //other locales
            Yii::app()->setLanguage('de');
            $localDateFormatForInput = DateTimeUtil::getLocaleDateFormatForInput();
            $this->assertEquals('dd.MM.yyyy', $localDateFormatForInput);

            Yii::app()->setLanguage('it');
            $localDateFormatForInput = DateTimeUtil::getLocaleDateFormatForInput();
            $this->assertEquals('dd/MM/yyyy', $localDateFormatForInput);

            Yii::app()->setLanguage('fr');
            $localDateFormatForInput = DateTimeUtil::getLocaleDateFormatForInput();
            $this->assertEquals('dd/MM/yyyy', $localDateFormatForInput);
            date_default_timezone_set($timeZone);
        }
    }
?>
