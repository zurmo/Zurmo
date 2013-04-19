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

    class RedBeanModelTypeValidatorTest extends BaseTest
    {
        public function testValidAndInvalidDateDateTimeValidation()
        {
            $language = Yii::app()->getLanguage();
            $this->assertEquals($language, 'en');
            $this->assertEquals(false, CDateTimeParser::parse(
                                            '04:04:1980', DatabaseCompatibilityUtil::getDateFormat()));
            $this->assertEquals(null,  DateTimeUtil::resolveValueForDateLocaleFormattedDisplay('04:04:1980'));
            $model = new DateDateTime();
            $model->aDate = '04:04:1980';
            $model->aDateTime = 'notATimeStamp';
            $saved = $model->save();
            $this->assertFalse($saved);
            $compareData = array(
                'aDate'     => array('A Date must be date.'),
                'aDateTime' => array('A Date Time must be datetime.')
            );
            $this->assertEquals($compareData, $model->getErrors());
            //Now test setting an integer for dateTime which is wrong
            $model = new DateDateTime();
            $model->aDate = '1981-07-05';
            $model->aDateTime = 1241341412421;
            $saved = $model->save();
            $this->assertFalse($saved);
            $compareData = array(
                'aDateTime' => array('A Date Time must be datetime.')
            );
            $this->assertEquals($compareData, $model->getErrors());
            //Now test a successful validation.
            $this->assertEquals('M/d/yy', DateTimeUtil::getLocaleDateFormat());
            $model = new DateDateTime();
            $model->aDate = '1981-07-05';
            $model->aDateTime = '1981-07-05 04:04:04';
            $saved = $model->save();
            $this->assertEquals(array(), $model->getErrors());
            $this->assertTrue($saved);
            $this->assertNull($model->aDateTime2);

            //now set DateTime2 and test if you save and then clear it that it is behaving properly.
            $model->aDateTime2 = '1981-07-05 04:04:04';
            $saved = $model->save();
            $this->assertTrue($saved);
            $this->assertEquals('1981-07-05 04:04:04', $model->aDateTime2);
            $model->aDateTime2 = null;
            $saved = $model->save();
            $this->assertTrue($saved);
            $id = $model->id;
            $model->forget();
            $model = DateDateTime::getById($id);
            $this->assertNull($model->aDateTime2);
        }

        public function testDateTimeValidation()
        {
             $this->assertNotNull(CDateTimeParser::parse('2009-11-11 21:18:09', DatabaseCompatibilityUtil::getDateTimeFormat()));
             $this->assertEmpty(CDateTimeParser::parse('ascascasc', DatabaseCompatibilityUtil::getDateTimeFormat()));
             $this->assertEmpty(CDateTimeParser::parse(null, DatabaseCompatibilityUtil::getDateTimeFormat()));
        }
    }
?>
