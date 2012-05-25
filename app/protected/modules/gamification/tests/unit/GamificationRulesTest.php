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

    class GamificationRulesTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testScoreOnSearchModels()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(0, count($models));
            GamificationRules::scoreOnSearchModels('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($models));
            $this->assertEquals('SearchAccount', $models['SearchAccount']->type);
        }

        /**
         * @depends testScoreOnSearchModels
         */
        public function testScoreOnMassEditModels()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(1, count($models));
            GamificationRules::scoreOnMassEditModels('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($models));
            $this->assertEquals('MassEditAccount', $models['MassEditAccount']->type);
        }

        /**
         * @depends testScoreOnMassEditModels
         */
        public function testScoreOnImportModels()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(2, count($models));
            GamificationRules::scoreOnImportModels('Account');
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(3, count($models));
            $this->assertEquals('ImportAccount', $models['ImportAccount']->type);
        }

       /**
         * @depends testScoreOnImportModels
         */
        public function testGetPointTypeAndValueDataByCategory()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $pointTypeAndValueData = GamificationRules::getPointTypeAndValueDataByCategory('Search');
            $this->assertEquals(array('UserAdoption' => 5), $pointTypeAndValueData);
        }

       /**
         * @depends testGetPointTypeAndValueDataByCategory
         */
        public function testScoreOnSaveModelIndirectly()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(3, count($models));
            $account        = new Account();
            $account->owner = $super;
            $account->name  = 'someName';
            $this->assertTrue($account->save());
            $models = GameScore::getAllByPersonIndexedByType(Yii::app()->user->userModel);
            $this->assertEquals(4, count($models));
            $this->assertEquals('CreateAccount', $models['CreateAccount']->type);
        }
    }
?>