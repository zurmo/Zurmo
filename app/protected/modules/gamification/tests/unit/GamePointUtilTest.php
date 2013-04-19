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

    class GamePointUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function tearDown()
        {
            parent::tearDown();
            Yii::app()->gameHelper->resetDeferredPointTypesAndValuesByUserIdToAdd();
        }

        public function testaddPointsByPointData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(array(), Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
            $pointTypeAndValueData = array('some type' => 400);
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel, $pointTypeAndValueData);
            $compareData = array(Yii::app()->user->userModel->id => $pointTypeAndValueData);
            $this->assertEquals($compareData, Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
        }

        public function testGetUserLeaderboardData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $pointTypeAndValueData = array('some type' => 400);
            GamePointUtil::addPointsByPointData(Yii::app()->user->userModel, $pointTypeAndValueData);
            Yii::app()->gameHelper->processDeferredPoints();
            $data = GamePointUtil::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_WEEKLY);
            $this->assertTrue(Yii::app()->gameHelper->enabled); //test to see if enabled when running all tests
            $this->assertTrue(count($data) > 0);
            $data = GamePointUtil::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_MONTHLY);
            $this->assertTrue(count($data) > 0);
            $data = GamePointUtil::getUserLeaderboardData(GamePointUtil::LEADERBOARD_TYPE_OVERALL);
            $this->assertTrue(count($data) > 0);
        }

        public function testGetUserRankingData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $data = GamePointUtil::getUserRankingData($super);
            $this->assertEquals(3, count($data));
        }
    }
?>