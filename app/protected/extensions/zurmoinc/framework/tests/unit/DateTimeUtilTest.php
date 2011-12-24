<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class DateTimeUtilTest extends BaseTest
    {
        public function teardown()
        {
            parent::teardown();
            //reset language to english
            Yii::app()->setLanguage('en');
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
            //other locales
            Yii::app()->setLanguage('de');
            $value = strtotime("3 June 1980");
            $displayValue = DateTimeUtil::resolveTimeStampForDateTimeLocaleFormattedDisplay($value);
            $this->assertEquals('03.06.80 00:00', $displayValue);
        }

        public function testResolveValueForDateLocaleFormattedDisplay()
        {
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01');
            $this->assertEquals('7/1/07', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('2007-07-01');
            $this->assertEquals('01.07.07', $displayValue);
        }

        public function testResolveValueForDateDBFormatted()
        {
            $displayValue = DateTimeUtil::resolveValueForDateDBFormatted('7/1/07');
            $this->assertEquals('2007-07-01', $displayValue);
            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::resolveValueForDateDBFormatted('01.07.07');
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
            $dbValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('6/3/80 12:00 AM');
            $this->assertEquals('1980-06-03 00:00:00', $dbValue);

            //other locales
            Yii::app()->setLanguage('de');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03.06.80 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            Yii::app()->setLanguage('it');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03/06/80 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            Yii::app()->setLanguage('fr');
            $displayValue = DateTimeUtil::convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero('03/06/80 00:00');
            $this->assertEquals('1980-06-03 00:00:00', $displayValue);

            //test null value returns null.
            $displayValue = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(null);
            $this->assertEquals(null, $displayValue);
            date_default_timezone_set($timeZone);
        }
    }
?>
