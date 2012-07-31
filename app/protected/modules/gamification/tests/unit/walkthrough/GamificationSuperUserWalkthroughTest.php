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

    /**
     * Gamification Module
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class GamificationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertfalse(strpos($content, 'Leaderboard') === false);
            $this->setGetArray(array(
                'type' => GamePointUtil::LEADERBOARD_TYPE_WEEKLY));
            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertfalse(strpos($content, 'Leaderboard') === false);
            $this->setGetArray(array(
                'type' => GamePointUtil::LEADERBOARD_TYPE_MONTHLY));
            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertfalse(strpos($content, 'Leaderboard') === false);
            $this->setGetArray(array(
                'type' => GamePointUtil::LEADERBOARD_TYPE_OVERALL));
            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertfalse(strpos($content, 'Leaderboard') === false);
        }

        public function testGameNotificationsComingUpCorrectly()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->assertEquals(0, count(GameNotification::getAll()));
            $this->assertEquals(0, count(GameNotification::getAllByUser($super)));

            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertTrue(strpos($content, 'ModalGameNotification0') === false);

            //Level up notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $super;
            $gameNotification->setLevelChangeByNextLevelValue(2);
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);

            $this->assertEquals(1, count(GameNotification::getAll()));
            $this->assertEquals(1, count(GameNotification::getAllByUser($super)));

            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertFalse(strpos($content, 'ModalGameNotification0') === false);
            $this->assertTrue(strpos($content, 'ModalGameNotification1') === false);

            $this->assertEquals(0, count(GameNotification::getAll()));
            $this->assertEquals(0, count(GameNotification::getAllByUser($super)));
            Yii::app()->clientScript->reset();

            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertTrue(strpos($content, 'ModalGameNotification0') === false);

            //New badge notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $super;
            $gameNotification->setNewBadgeByType('LoginUser');
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);

            //Badge grade up notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $super;
            $gameNotification->setBadgeGradeChangeByTypeAndNewGrade('LoginUser', 5);
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);

            Yii::app()->clientScript->reset();

            $content = $this->runControllerWithNoExceptionsAndGetContent('gamification/default/leaderboard');
            $this->assertFalse(strpos($content, 'ModalGameNotification0') === false);
            $this->assertFalse(strpos($content, 'ModalGameNotification1') === false);
        }
    }
?>