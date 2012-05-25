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

    class GamePointTest extends ZurmoBaseTest
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

        public function testCreateAndGetGamePointById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gamePoint             = new GamePoint();
            $gamePoint->person     = $user;
            $gamePoint->type       = 'SomeType';
            $gamePoint->addValue(10);
            $this->assertTrue($gamePoint->save());
            $id = $gamePoint->id;
            unset($gamePoint);
            $gamePoint = GamePoint::getById($id);
            $this->assertEquals('SomeType',  $gamePoint->type);
            $this->assertEquals(10,          $gamePoint->value);
            $this->assertEquals($user,       $gamePoint->person);

            $this->assertEquals(1, $gamePoint->transactions->count());
            $gamePoint->addValue(50);
            $this->assertEquals(60, $gamePoint->value);
            $this->assertEquals(2, $gamePoint->transactions->count());
            $this->assertEquals(10, $gamePoint->transactions[0]->value);
            $this->assertEquals(50, $gamePoint->transactions[1]->value);
        }

        /**
         * @depends testCreateAndGetGamePointById
         */
        public function testCreateGamePointSettingValueDirectly()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gamePoint = new GamePoint();
            $gamePoint->value = 5;
        }

        /**
         * @depends testCreateGamePointSettingValueDirectly
         */
        public function testResolveToGetByTypeAndPerson()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gamePoint = GamePoint::resolveToGetByTypeAndPerson('SomeType',  Yii::app()->user->userModel);
            $this->assertEquals('SomeType',                   $gamePoint->type);
            $this->assertEquals(60,                           $gamePoint->value);
            $this->assertEquals(Yii::app()->user->userModel,  $gamePoint->person);

            $gamePoint = GamePoint::resolveToGetByTypeAndPerson('SomeType2',  Yii::app()->user->userModel);
            $this->assertTrue($gamePoint->id < 0);
        }

        /**
         * @depends testResolveToGetByTypeAndPerson
         */
        public function testGetAllByPersonIndexedByType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            $gamePoint             = new GamePoint();
            $gamePoint->person     = Yii::app()->user->userModel;
            $gamePoint->type       = 'SomeTypeX';
            $gamePoint->addValue(10);
            $this->assertTrue($gamePoint->save());

            $gamePoints = GamePoint::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($gamePoints));
            $this->assertTrue(isset($gamePoints['SomeType']));
            $this->assertTrue(isset($gamePoints['SomeTypeX']));
        }

        /**
         * @depends testGetAllByPersonIndexedByType
         */
        public function testDoesUserExceedPointsByLevelType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, GameLevel::TYPE_GENERAL);
            $this->assertTrue($result);
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, GameLevel::TYPE_SALES);
            $this->assertFalse($result);
        }

        /**
         * @depends testDoesUserExceedPointsByLevelType
         * @expectedException NotSupportedException
         */
        public function testDoesUserExceedPointsByInvalidLevelType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $result = GamePoint::doesUserExceedPointsByLevelType(Yii::app()->user->userModel, 5, 'SomethingInvalid');
        }
    }
?>
