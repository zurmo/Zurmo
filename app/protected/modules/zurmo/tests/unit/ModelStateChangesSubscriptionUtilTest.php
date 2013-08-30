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

    class ModelStateChangesSubscriptionUtilTest extends ZurmoBaseTest
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

        public function testGetCreatedModels()
        {
            ReadPermissionsSubscriptionUtil::recreateTable('account_read_subscription');
            ModelCreationApiSyncUtil::buildTable();
            $timestamp = time();
            sleep(1);
            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 5, 0, $timestamp);
            $this->assertEquals(false, $models);

            // Now create new account model
            AccountTestHelper::createAccountByNameForOwner('First Account', Yii::app()->user->userModel);
            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 5, 0, $timestamp);
            $this->assertEquals(false, $models);

            // Now run ReadPermissionSubscriptionUtil
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();
            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 5, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals('First Account', $models[0]->name);

            sleep(1);
            $timestamp = time();
            sleep(1);
            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 5, 0, $timestamp);
            $this->assertEquals(false, $models);

            AccountTestHelper::createAccountByNameForOwner('First Test Account', Yii::app()->user->userModel);
            AccountTestHelper::createAccountByNameForOwner('Second Test Account', Yii::app()->user->userModel);
            AccountTestHelper::createAccountByNameForOwner('Third Test Account', Yii::app()->user->userModel);
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();
            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(2, count($models));
            $this->assertEquals('First Test Account', $models[0]->name);
            $this->assertEquals('Second Test Account', $models[1]->name);

            $models = ModelStateChangesSubscriptionUtil::getCreatedModels('apiTest', 'Account', 2, 2, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals('Third Test Account', $models[0]->name);
        }

        public function testGetDeletedModelIds()
        {
            ReadPermissionsSubscriptionUtil::recreateTable('account_read_subscription');
            ModelCreationApiSyncUtil::buildTable();
            $timestamp = time();
            sleep(1);
            $account1 = AccountTestHelper::createAccountByNameForOwner('First Test Delete Account', Yii::app()->user->userModel);
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();
            $models = ModelStateChangesSubscriptionUtil::getDeletedModelIds('apiTest', 'Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertTrue(empty($models));

            $account1Id = $account1->id;
            $this->assertTrue($account1->delete());
            $models = ModelStateChangesSubscriptionUtil::getDeletedModelIds('apiTest', 'Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertTrue(empty($models));

            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();
            $models = ModelStateChangesSubscriptionUtil::getDeletedModelIds('apiTest', 'Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals($account1Id, $models[0]);

            // Check pagination
            sleep(1);
            $timestamp = time();
            sleep(1);
            $account2 = AccountTestHelper::createAccountByNameForOwner('Second Test Delete Account', Yii::app()->user->userModel);
            $account3 = AccountTestHelper::createAccountByNameForOwner('Third Test Delete Account', Yii::app()->user->userModel);
            $account4 = AccountTestHelper::createAccountByNameForOwner('Forth Test Delete Account', Yii::app()->user->userModel);
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();

            $account2Id = $account2->id;
            $account3Id = $account3->id;
            $account4Id = $account4->id;
            $this->assertTrue($account2->delete());
            $this->assertTrue($account3->delete());
            $this->assertTrue($account4->delete());
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables();
            $models = ModelStateChangesSubscriptionUtil::getDeletedModelIds('apiTest', 'Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(2, count($models));
            $this->assertEquals($account2Id, $models[0]);
            $this->assertEquals($account3Id, $models[1]);

            // Second page
            $models = ModelStateChangesSubscriptionUtil::getDeletedModelIds('apiTest', 'Account', 2, 2, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals($account4Id, $models[0]);
        }

        public function testGetUpdatedModels()
        {
            ReadPermissionsSubscriptionUtil::recreateTable('account_read_subscription');
            ModelCreationApiSyncUtil::buildTable();

            $account1 = AccountTestHelper::createAccountByNameForOwner('First Test Update Account', Yii::app()->user->userModel);
            $timestamp = time();
            $models = ModelStateChangesSubscriptionUtil::getUpdatedModels('Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertTrue(empty($models));

            $account1->name = 'First Test Update Account Modified';
            $this->assertTrue($account1->save());
            // This should return true, because there should be 3 second gap between created and modified timestamps
            $models = ModelStateChangesSubscriptionUtil::getUpdatedModels('Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertTrue(empty($models));

            sleep(4);
            $account1->name = 'First Test Update Account Modified 2';
            $this->assertTrue($account1->save());
            $models = ModelStateChangesSubscriptionUtil::getUpdatedModels('Account', 2, 0, $timestamp);

            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals($account1->id, $models[0]->id);
            $this->assertEquals($account1->name, $models[0]->name);

            // Check pagination
            $timestamp = time();
            $account2 = AccountTestHelper::createAccountByNameForOwner('Second Test Update Account', Yii::app()->user->userModel);
            $account3 = AccountTestHelper::createAccountByNameForOwner('Third Test Update Account', Yii::app()->user->userModel);
            $account4 = AccountTestHelper::createAccountByNameForOwner('Forth Test Update Account', Yii::app()->user->userModel);
            sleep(5);
            $account2->name = 'Second Test Update Account Modified';
            $account3->name = 'Third Test Update Account Modified';
            $account4->name = 'Forth Test Update Account Modified';
            $this->assertTrue($account2->save());
            $this->assertTrue($account3->save());
            $this->assertTrue($account4->save());
            $models = ModelStateChangesSubscriptionUtil::getUpdatedModels('Account', 2, 0, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(2, count($models));
            $this->assertTrue($account2->id == $models[0]->id || $account2->id == $models[1]->id);
            $this->assertTrue($account2->name == $models[0]->name || $account2->name == $models[1]->name);
            $this->assertTrue($account3->id == $models[0]->id || $account3->id == $models[1]->id);
            $this->assertTrue($account3->name == $models[0]->name || $account3->name == $models[1]->name);

            $models = ModelStateChangesSubscriptionUtil::getUpdatedModels('Account', 2, 2, $timestamp);
            $this->assertTrue(is_array($models));
            $this->assertEquals(1, count($models));
            $this->assertEquals($account4->id, $models[0]->id);
            $this->assertEquals($account4->name, $models[0]->name);
        }
    }
?>
