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

    class GameScoreTest extends ZurmoBaseTest
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

        public function testCreateAndGetGameScoreById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameScore             = new GameScore();
            $gameScore->person     = $user;
            $gameScore->type       = 'SomeType';
            $gameScore->value      = 10;
            $this->assertTrue($gameScore->save());
            $id = $gameScore->id;
            unset($gameScore);
            $gameScore = GameScore::getById($id);
            $this->assertEquals('SomeType',  $gameScore->type);
            $this->assertEquals(10,          $gameScore->value);
            $this->assertEquals($user,       $gameScore->person);

            $gameScore->addValue();
            $this->assertEquals(11, $gameScore->value);
        }

        /**
         * @depends testCreateAndGetGameScoreById
         */
        public function testResolveToGetByTypeAndPerson()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gameScore                  = GameScore::resolveToGetByTypeAndPerson('SomeType',  Yii::app()->user->userModel);
            $this->assertEquals('SomeType',                     $gameScore->type);
            $this->assertEquals(11,                             $gameScore->value);
            $this->assertEquals(Yii::app()->user->userModel,    $gameScore->person);

            $gameScore = GameScore::resolveToGetByTypeAndPerson('SomeType2',  Yii::app()->user->userModel);
            $this->assertTrue($gameScore->id < 0);
        }

        /**
         * @depends testResolveToGetByTypeAndPerson
         */
        public function testGetAllByPersonIndexedByType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            $gameScore             = new GameScore();
            $gameScore->person     = Yii::app()->user->userModel;
            $gameScore->type       = 'SomeTypeX';
            $gameScore->value      = 10;
            $this->assertTrue($gameScore->save());

            $gameScores = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($gameScores));
            $this->assertTrue(isset($gameScores['SomeType']));
            $this->assertTrue(isset($gameScores['SomeTypeX']));
        }
    }
?>
