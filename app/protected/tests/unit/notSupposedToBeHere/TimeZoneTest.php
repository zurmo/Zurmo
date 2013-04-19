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

    class TimeZoneTest extends BaseTest
    {
        public function testUsingYiiTimeZoneSwitcherWithPhpTimeFunction()
        {
            $oldTimeZone = Yii::app()->getTimeZone();
            $dateTimeUtc = new DateTime();
            $timeStamp = time(); //always UTC regardless of server timezone or any timezone setting.
            Yii::app()->setTimeZone('UTC');
            $dateStamp  = date("Y-m-d G:i",  $timeStamp);
            Yii::app()->setTimeZone('America/Chicago');
            $dateStamp2 = date("Y-m-d G:i",  $timeStamp);
            Yii::app()->setTimeZone('America/New_York');

            $timeZoneObject = new DateTimeZone('America/New_York');
            $offset = $timeZoneObject->getOffset(new DateTime());
            $this->assertTrue($offset == -18000 || $offset == -14400);

            $newYorkTimeZone = new DateTimeZone(date_default_timezone_get());
            $offset1         = $newYorkTimeZone->getOffset($dateTimeUtc);
            $offset2         = timezone_offset_get($newYorkTimeZone , $dateTimeUtc);
            $this->assertEquals($offset, $offset1);
            $this->assertEquals($offset, $offset2);

            if ($offset == -18000)
            {
                $offsetHours = 6;
            }
            else
            {
                $offsetHours = 5;
            }

            $dateStamp3 = date("Y-m-d G:i",  $timeStamp);
            $this->assertEquals(strtotime($dateStamp),  strtotime($dateStamp2) + (3600 * $offsetHours));   // + 5 from GMT or +6 depending on DST
            $this->assertEquals(strtotime($dateStamp3), strtotime($dateStamp2) + (3600 * 1));   // + 1 from NY
            //Use retrieved offset based on timezone.
            Yii::app()->setTimeZone($oldTimeZone);
        }
    }
?>
