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

    class GameHelperTest extends ZurmoBaseTest
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

        public function tearDown()
        {
            parent::tearDown();
            Yii::app()->gameHelper->resetDeferredPointTypesAndValuesByUserIdToAdd();
        }

        public function testTriggerSearchModelsEvent()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(0, count($models));
            Yii::app()->gameHelper->triggerSearchModelsEvent('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($models));
            $this->assertEquals('SearchAccount', $models['SearchAccount']->type);
        }

        /**
         * @depends testTriggerSearchModelsEvent
         */
        public function testTriggerMassEditEvent()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($models));
            Yii::app()->gameHelper->triggerMassEditEvent('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($models));
            $this->assertEquals('MassEditAccount', $models['MassEditAccount']->type);
        }

        /**
         * @depends testTriggerMassEditEvent
         */
        public function testTriggerImportEvent()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($models));
            Yii::app()->gameHelper->triggerImportEvent('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(3, count($models));
            $this->assertEquals('ImportAccount', $models['ImportAccount']->type);
        }

        /**
         * @depends testTriggerImportEvent
         */
        public function testAddPointsByUserDeferred()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(array(), Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
            $pointTypeAndValueData = array('SomeThing' => 50);
            Yii::app()->gameHelper->addPointsByUserDeferred($super, 'SomeThing', 50);
            $compareData = array(Yii::app()->user->userModel->id => $pointTypeAndValueData);
            $this->assertEquals($compareData, Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
        }

        /**
         * @depends testAddPointsByUserDeferred
         */
        public function testProcessDeferredPoints()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(array(), Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
            $pointTypeAndValueData = array(GamePoint::TYPE_USER_ADOPTION => 50);
            Yii::app()->gameHelper->addPointsByUserDeferred($super, GamePoint::TYPE_USER_ADOPTION, 50);
            $compareData = array(Yii::app()->user->userModel->id => $pointTypeAndValueData);
            $this->assertEquals($compareData, Yii::app()->gameHelper->getDeferredPointTypesAndValuesByUserIdToAdd());
            Yii::app()->gameHelper->processDeferredPoints();
        }

        /**
         * @depends testProcessDeferredPoints
         */
        public function testResolveLevelChange()
        {
           Yii::app()->gameHelper->resolveLevelChange();
        }

        /**
         * @depends testResolveLevelChange
         */
        public function testResolveNewBadges()
        {
            Yii::app()->gameHelper->resolveNewBadges();
        }
    }
?>
