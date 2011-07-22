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

    class ZurmoTimeZoneHelperTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testGetAndSetForCurrentUser()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $timeZoneHelper->setTimeZone('America/Chicago');
            $this->assertEquals('UTC', $timeZoneHelper->getForCurrentUser());
            Yii::app()->user->userModel->timeZone = 'America/New_York';
            $this->assertTrue(Yii::app()->user->userModel->save());
            $this->assertEquals('America/New_York', Yii::app()->user->userModel->timeZone);
            Yii::app()->user->clearStates();
            $this->assertEquals('America/New_York', $timeZoneHelper->getForCurrentUser());
        }

        /**
         * @depends testGetAndSetForCurrentUser
         */
        public function testGetAndSetByUser()
        {
            Yii::app()->user->userModel =  User::getByUsername('super');
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $billy =  User::getByUsername('billy');
            $this->assertEquals('UTC', $billy->timeZone);
            $timeZoneHelper->setTimeZone('America/Chicago');
            $this->assertEquals('UTC', $billy->timeZone);
            $billy->timeZone = 'Pacific/Guam';
            $this->assertTrue($billy->save());
            $this->assertEquals('America/New_York', Yii::app()->user->userModel->timeZone);
            Yii::app()->user->clearStates();
            $this->assertEquals('Pacific/Guam', $billy->timeZone);
            $this->assertEquals('America/New_York', Yii::app()->user->userModel->timeZone);
            $this->assertEquals('America/New_York', $timeZoneHelper->getForCurrentUser());
        }

        /**
         * @depends testGetAndSetByUser
         * @expectedException Exception
         */
        public function testSettingMalformedTimeZone()
        {
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $timeZoneHelper->setTimeZone('AFakeTimeZone');
        }

        /**
         * @depends testSettingMalformedTimeZone
         * @expectedException Exception
         */
        public function testSettingMalformedTimeZoneByUser()
        {
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $billy =  User::getByUsername('billy');
            $billy->timeZone = 'AnotherFakePlace';
            $this->assertFalse($billy->save());
        }

        public function testSetGetGlobalValue()
        {
            $timeZoneHelper = new ZurmoTimeZoneHelper();
            $timeZoneHelper->setTimeZone('Pacific/Guam');
            $this->assertEquals('Pacific/Guam', $timeZoneHelper->getGlobalValue());
            $timeZoneHelper->setGlobalValue('America/New_York');
            $this->assertEquals('America/New_York', $timeZoneHelper->getGlobalValue());
        }
    }
?>
