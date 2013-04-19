<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class DateTimeCalculatorUtilTest extends BaseTest
    {
        public function testCalculateNew()
        {
            //Test Today.
            $todayDateStamp     = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::TODAY,
                                  new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
            $todayDateTime      = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                     $todayDateTime->getTimeStamp());
            $this->assertEquals($today, $todayDateStamp);
            //Test Tomorrow.
            $tomorrowDateStamp  = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::TOMORROW,
                                    new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
            $tomorrowDateTime   = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $tomorrow           = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $tomorrowDateTime->getTimeStamp() + (60 * 60 *24));
            $this->assertEquals($tomorrow, $tomorrowDateStamp);
            //Test Yesterday.
            $yesterdayDateStamp = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::YESTERDAY,
                                    new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
            $yesterdayDateTime  = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
            $this->assertEquals($yesterday, $yesterdayDateStamp);
            //Test Now.
            $nowDateTimeStamp   = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::NOW,
                                    new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser())));
            $nowDateTime        = new DateTime(null, new DateTimeZone(Yii::app()->timeZoneHelper->getForCurrentUser()));
            $this->assertWithinTolerance($nowDateTime->getTimeStamp(),
                                            DateTimeUtil::convertDbFormatDateTimeToTimestamp($nowDateTimeStamp), 1);

            //Now test all calculations using a different time zone.
            $this->assertNotEquals('Europe/Malta', Yii::app()->timeZoneHelper->getForCurrentUser());
            //Test Today.
            $todayDateStamp     = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::TODAY,
                                new DateTime(null, new DateTimeZone('Europe/Malta')));
            $todayDateTime      = new DateTime(null, new DateTimeZone('Europe/Malta'));
            $today              = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                     $todayDateTime->getTimeStamp());
            $this->assertEquals($today, $todayDateStamp);
            //Test Tomorrow.
            $tomorrowDateStamp  = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::TOMORROW,
                                    new DateTime(null, new DateTimeZone('Europe/Malta')));
            $tomorrowDateTime   = new DateTime(null, new DateTimeZone('Europe/Malta'));
            $tomorrow           = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $tomorrowDateTime->getTimeStamp() + (60 * 60 *24));
            $this->assertEquals($tomorrow, $tomorrowDateStamp);
            //Test Yesterday.
            $yesterdayDateStamp = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::YESTERDAY,
                                    new DateTime(null, new DateTimeZone('Europe/Malta')));
            $yesterdayDateTime  = new DateTime(null, new DateTimeZone('Europe/Malta'));
            $yesterday          = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                                    $yesterdayDateTime->getTimeStamp() - (60 * 60 *24));
            $this->assertEquals($yesterday, $yesterdayDateStamp);
            //Test Now.
            $nowDateTimeStamp   = DateTimeCalculatorUtil::calculateNew(DateTimeCalculatorUtil::NOW,
                                    new DateTime(null, new DateTimeZone('Europe/Malta')));
            $nowDateTime        = new DateTime(null, new DateTimeZone('Europe/Malta'));
            $this->assertWithinTolerance($nowDateTime->getTimeStamp(),
                                            DateTimeUtil::convertDbFormatDateTimeToTimestamp($nowDateTimeStamp), 1);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testCalculateNewNotSupportedCalculation()
        {
            DateTimeCalculatorUtil::calculateNew(1231, new DateTime());
        }
    }
?>