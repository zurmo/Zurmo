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

    class ThingWithCalculatedDates extends RedBeanModel
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'now',
                    'today',
                    'tomorrow',
                    'yesterday',
                ),
                'rules' => array(
                    array('now',       'dateTimeDefault', 'value' => DateTimeCalculatorUtil::NOW),
                    array('today',     'dateTimeDefault', 'value' => DateTimeCalculatorUtil::TODAY),
                    array('tomorrow',  'dateTimeDefault', 'value' => DateTimeCalculatorUtil::TOMORROW),
                    array('yesterday', 'dateTimeDefault', 'value' => DateTimeCalculatorUtil::YESTERDAY),
                ),
            );
            return $metadata;
        }
    }

    class RedBeanModelDefaultCalculatedDateValidatorTest extends BaseTest
    {
        public function testDefaultCalculatedDates()
        {
            $now = time();
            $thing         = new ThingWithCalculatedDates();
            $thingNowValue = $thing->now;
            $this->assertEquals(DateTimeUtil::convertTimestampToDbFormatDateTime($now), $thing->now);
            $this->assertTrue($thing->save());
            $this->assertEquals($thingNowValue, $thing->now);
            $stamp = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(), $now);
            $this->assertEquals($stamp, $thing->today);
            $stamp = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(), $now + 24 * 60 * 60);
            $this->assertEquals($stamp, $thing->tomorrow);
            $stamp = Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(), $now - 24 * 60 * 60);
            $this->assertEquals($stamp, $thing->yesterday);
        }
    }
?>
