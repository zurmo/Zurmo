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

    class GameLevelUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetNextLevelByTypeAndCurrentLevel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $gameLevel        = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL, $gameLevel));

            $gameLevel = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_SALES, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_SALES, $gameLevel));

            $gameLevel = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_NEW_BUSINESS, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_NEW_BUSINESS, $gameLevel));

            $gameLevel = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_ACCOUNT_MANAGEMENT, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_ACCOUNT_MANAGEMENT, $gameLevel));

            $gameLevel = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_TIME_MANAGEMENT, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_TIME_MANAGEMENT, $gameLevel));

            $gameLevel = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(2, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_COMMUNICATION, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(3, GameLevelUtil::getNextLevelByTypeAndCurrentLevel(GameLevel::TYPE_COMMUNICATION, $gameLevel));
        }

        public function testGetNextLevelPointValueByTypeAndCurrentLevel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $gameLevel        = new GameLevel();
            $gameLevel->value = 1;
            $this->assertEquals(500, GameLevelUtil::getNextLevelPointValueByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL, $gameLevel));
            $gameLevel->value = 2;
            $this->assertEquals(1000, GameLevelUtil::getNextLevelPointValueByTypeAndCurrentLevel(GameLevel::TYPE_GENERAL, $gameLevel));
        }

        public function testGetUserStatisticsData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $data = GameLevelUtil::getUserStatisticsData($super);
            $this->assertEquals(6, count($data));
        }
    }
?>