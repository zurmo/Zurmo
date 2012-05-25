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