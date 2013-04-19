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

    class GameBadgeTest extends ZurmoBaseTest
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

        public function testCreateAndGetGameBadgeById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameBadge             = new GameBadge();
            $gameBadge->person     = $user;
            $gameBadge->type       = 'SomeType';
            $gameBadge->grade      = 1;
            $this->assertTrue($gameBadge->save());
            $id = $gameBadge->id;
            unset($gameBadge);
            $gameBadge = GameBadge::getById($id);
            $this->assertEquals('SomeType',  $gameBadge->type);
            $this->assertEquals(1,           $gameBadge->grade);
            $this->assertEquals($user,       $gameBadge->person);
        }

        /**
         * @depends testCreateAndGetGameBadgeById
         */
        public function testGetAllByPersonIndexedByType()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $gameBadges                 = GameBadge::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($gameBadges));
            $gameBadge = $gameBadges['SomeType'];
            $this->assertEquals('SomeType',                    $gameBadge->type);
            $this->assertEquals(1,                             $gameBadge->grade);
            $this->assertEquals(Yii::app()->user->userModel,   $gameBadge->person);
        }

        /**
         * @depends testGetAllByPersonIndexedByType
         */
        public function testProcessBonusPoints()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');

            $gamePoint = GamePoint::resolveToGetByTypeAndPerson(GamePoint::TYPE_USER_ADOPTION,  Yii::app()->user->userModel);
            $this->assertEquals(GamePoint::TYPE_USER_ADOPTION, $gamePoint->type);
            $this->assertEquals(0,                             $gamePoint->value);

            //Testing a badge that does not give bonus points.
            $gameBadge             = new GameBadge();
            $gameBadge->person     = Yii::app()->user->userModel;
            $gameBadge->type       = 'CreateLead';
            $gameBadge->grade      = 1;
            $this->assertTrue($gameBadge->save());
            GameBadge::processBonusPoints($gameBadge, Yii::app()->user->userModel, 'NewBadge');

            //Test that bonus points were actually received.
            $gamePoint = GamePoint::resolveToGetByTypeAndPerson(GamePoint::TYPE_USER_ADOPTION,  Yii::app()->user->userModel);
            $this->assertEquals(GamePoint::TYPE_USER_ADOPTION, $gamePoint->type);
            $this->assertEquals(50,                           $gamePoint->value);
        }
    }
?>
