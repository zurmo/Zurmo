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

    class GameLevelTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetGameLevelById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameLevel             = new GameLevel();
            $gameLevel->person     = $user;
            $gameLevel->type       = 'SomeType';
            $gameLevel->value      = 10;
            $this->assertTrue($gameLevel->save());
            $id = $gameLevel->id;
            unset($gameLevel);
            $gameLevel = GameLevel::getById($id);
            $this->assertEquals('SomeType',  $gameLevel->type);
            $this->assertEquals(10,          $gameLevel->value);
            $this->assertEquals($user,       $gameLevel->person);
        }

        /**
         * @depends testCreateAndGetGameLevelById
         */
        public function testResolveByTypeAndPerson()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gameLevel                  = GameLevel::resolveByTypeAndPerson('SomeType',  Yii::app()->user->userModel);
            $this->assertEquals('SomeType',                     $gameLevel->type);
            $this->assertEquals(10,                             $gameLevel->value);
            $this->assertEquals(Yii::app()->user->userModel,    $gameLevel->person);

            $gameLevel = GameLevel::resolveByTypeAndPerson('SomeType2',  Yii::app()->user->userModel);
            $this->assertTrue($gameLevel->id < 0);
        }

        /**
         * @depends testResolveByTypeAndPerson
         */
        public function testProcessBonusPointsOnLevelChange()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            $gamePoint = GamePoint::resolveToGetByTypeAndPerson(GamePoint::TYPE_USER_ADOPTION,  Yii::app()->user->userModel);
            $this->assertEquals(GamePoint::TYPE_USER_ADOPTION, $gamePoint->type);
            $this->assertEquals(0,                            $gamePoint->value);

            //Testing a level that does not give bonus points.
            $gameLevel             = new GameLevel();
            $gameLevel->person     = Yii::app()->user->userModel;
            $gameLevel->type       = GameLevel::TYPE_SALES;
            $gameLevel->value      = 1;
            $this->assertTrue($gameLevel->save());
            GameLevel::processBonusPointsOnLevelChange($gameLevel, Yii::app()->user->userModel);

            //Test that bonus points were actually received.
            $gamePoint = GamePoint::resolveToGetByTypeAndPerson(GamePoint::TYPE_USER_ADOPTION,  Yii::app()->user->userModel);
            $this->assertEquals(GamePoint::TYPE_USER_ADOPTION, $gamePoint->type);
            $this->assertEquals(100,                           $gamePoint->value);

            //Now get the GameLevel again, and make sure it works for level 2

            $gameLevel             = GameLevel::resolveByTypeAndPerson(GameLevel::TYPE_SALES, Yii::app()->user->userModel);
            $gameLevel->person     = Yii::app()->user->userModel;
            $gameLevel->type       = GameLevel::TYPE_SALES;
            $gameLevel->value      = 2;
            $this->assertTrue($gameLevel->save());
            GameLevel::processBonusPointsOnLevelChange($gameLevel, Yii::app()->user->userModel);

            //Test that bonus points were actually received.
            $gamePoint = GamePoint::resolveToGetByTypeAndPerson(GamePoint::TYPE_USER_ADOPTION,  Yii::app()->user->userModel);
            $this->assertEquals(GamePoint::TYPE_USER_ADOPTION, $gamePoint->type);
            $this->assertEquals(210,                           $gamePoint->value);
        }
    }
?>
