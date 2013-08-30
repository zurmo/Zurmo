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

    class ReadPermissionsSubscriptionUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testFindReadSubscriptionModelClassNames()
        {
            $modelClassNames = ReadPermissionsSubscriptionUtil::findReadSubscriptionModelClassNames();
            $compareData = array('Account', 'Contact', 'Meeting', 'Task');
            $this->assertEquals($compareData, $modelClassNames);
            $modelClassNames2 = ReadPermissionsSubscriptionUtil::findReadSubscriptionModelClassNames();
            $this->assertEquals($modelClassNames, $modelClassNames2);
            $modelClassNames3 = ReadPermissionsSubscriptionUtil::findReadSubscriptionModelClassNames();
            $this->assertEquals($modelClassNames2, $modelClassNames3);
        }

        public function testGetReadSubscriptionModelClassNames()
        {
            $modelClassNames = ReadPermissionsSubscriptionUtil::getReadSubscriptionModelClassNames();
            $compareData = array('Account', 'Contact', 'Meeting', 'Task');
            $this->assertEquals($compareData, $modelClassNames);

            // Now test with caching
            $modelClassNames = ReadPermissionsSubscriptionUtil::getReadSubscriptionModelClassNames();
            $this->assertEquals($compareData, $modelClassNames);
        }

        public function testGetSubscriptionTableName()
        {
            $subscriptionTableName = ReadPermissionsSubscriptionUtil::getSubscriptionTableName('Account');
            $this->assertEquals('account_read_subscription', $subscriptionTableName);
        }

        public function testRecreateTable()
        {
            ReadPermissionsSubscriptionUtil::recreateTable('account_read_subscription');

            $sql = 'INSERT INTO account_read_subscription VALUES (null, \'1\', \'2\', \'2013-05-03 15:16:06\', \'1\')';
            R::exec($sql);
            $accountReadSubscription = R::getRow("SELECT * FROM account_read_subscription");
            $this->assertTrue($accountReadSubscription['id'] > 0);
            $this->assertEquals(1, $accountReadSubscription['userid']);
            $this->assertEquals(2, $accountReadSubscription['modelid']);
            $this->assertEquals('2013-05-03 15:16:06', $accountReadSubscription['modifieddatetime']);
            $this->assertEquals(1, $accountReadSubscription['subscriptiontype']);
            $sql = 'DELETE FROM account_read_subscription';
            R::exec($sql);
        }

        public function testRebuild()
        {
            ReadPermissionsSubscriptionUtil::buildTables();
            $sql = "SHOW TABLES LIKE '%_read_subscription'";
            $allSubscriptionTableRows = R::getAll($sql);
            $this->assertEquals(4, count($allSubscriptionTableRows));

            $readSubscriptionTables = array();
            foreach ($allSubscriptionTableRows as $subscriptionTableRow)
            {
                foreach ($subscriptionTableRow as $subscriptionTable)
                {
                    $readSubscriptionTables[] = $subscriptionTable;
                }
            }
            $this->assertEquals($readSubscriptionTables,
                array('account_read_subscription', 'contact_read_subscription', 'meeting_read_subscription', 'task_read_subscription'));
        }

        public function testUpdateModelsInReadSubscriptionTable()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $contact1 = ContactTestHelper::createContactByNameForOwner('Mike', $super);
            sleep(1);
            $contact2 = ContactTestHelper::createContactByNameForOwner('Jake', $super);

            $sql = "SELECT * FROM contact_read_subscription WHERE userid = " . Yii::app()->user->userModel->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(0, count($permissionTableRows));

            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Contact', Yii::app()->user->userModel, true, true);
            $sql = "SELECT * FROM contact_read_subscription  order by modifieddatetime ASC, modelid  ASC";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(2, count($permissionTableRows));
            $this->assertEquals($contact1->id, $permissionTableRows[0]['modelid']);
            $this->assertEquals(Yii::app()->user->userModel->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);
            $this->assertEquals($contact2->id, $permissionTableRows[1]['modelid']);
            $this->assertEquals(Yii::app()->user->userModel->id, $permissionTableRows[1]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[1]['subscriptiontype']);

            sleep(1);
            $nowDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $contact3 = ContactTestHelper::createContactByNameForOwner('Jimmy',  $super);
            sleep(1);
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Contact', Yii::app()->user->userModel, true, true);
            $sql = "SELECT * FROM contact_read_subscription";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(3, count($permissionTableRows));

            $sql = "SELECT * FROM contact_read_subscription WHERE modifieddatetime>='" . $nowDateTime . "'";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($contact3->id, $permissionTableRows[0]['modelid']);
            $this->assertEquals(Yii::app()->user->userModel->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);

            // Now test deletion
            sleep(1);
            $deletedContactId = $contact1->id;
            $contact1->delete();
            $contact1->forgetAll();
            $nowDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $contact4 = ContactTestHelper::createContactByNameForOwner('Jill',  $super);
            sleep(1);
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Contact', Yii::app()->user->userModel, true, true);
            $sql = "SELECT * FROM contact_read_subscription WHERE userid = " . Yii::app()->user->userModel->id .
                " AND subscriptiontype = " . ReadPermissionsSubscriptionUtil::TYPE_ADD . " order by modifieddatetime ASC, modelid  ASC";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(3, count($permissionTableRows));
            $this->assertEquals($contact2->id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($contact3->id, $permissionTableRows[1]['modelid']);
            $this->assertEquals($contact4->id, $permissionTableRows[2]['modelid']);

            $sql = "SELECT * FROM contact_read_subscription WHERE userid = " . Yii::app()->user->userModel->id .
                " AND subscriptiontype = " . ReadPermissionsSubscriptionUtil::TYPE_DELETE . " order by modifieddatetime ASC, modelid  ASC";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($deletedContactId, $permissionTableRows[0]['modelid']);
            $this->assertEquals(Yii::app()->user->userModel->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $permissionTableRows[0]['subscriptiontype']);
        }

        public function testUpdateModelsInReadSubscriptionTableWithPrivilegeEscalations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $steven = UserTestHelper::createBasicUser('Steven');

            $account1 = AccountTestHelper::createAccountByNameForOwner('First Account', $super);
            ReadPermissionsOptimizationUtil::rebuild();
            sleep(1);
            Yii::app()->user->userModel = $steven;
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Account', Yii::app()->user->userModel, false, false);
            $sql = "SELECT * FROM account_read_subscription";
            $this->assertTrue(empty($permissionTableRows));

            // Add user to everyone group
            Yii::app()->user->userModel = $super;
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $account1->addPermissions($everyoneGroup, Permission::READ);
            $this->assertTrue($account1->save());
            $account1Id = $account1->id;
            $account1->forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            Yii::app()->user->userModel = $steven;
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Account', Yii::app()->user->userModel, false, false);
            $sql = "SELECT * FROM account_read_subscription";
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($account1Id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($steven->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);

            // Test as super
            Yii::app()->user->userModel = $super;
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Account', Yii::app()->user->userModel, false, false);
            $sql = "SELECT * FROM account_read_subscription WHERE userid = " . Yii::app()->user->userModel->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($account1Id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($super->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);

            // Remove account from everyone group
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $account1 = Account::getById($account1Id);
            $account1->removePermissions($everyoneGroup, Permission::READ);
            $this->assertTrue($account1->save());
            $account1->forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            Yii::app()->user->userModel = $steven;
            ReadPermissionsSubscriptionUtil::updateReadSubscriptionTableByModelClassnameAndUser('Account', Yii::app()->user->userModel, false, false);
            $sql = "SELECT * FROM account_read_subscription WHERE userid = " . Yii::app()->user->userModel->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($account1Id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($steven->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $permissionTableRows[0]['subscriptiontype']);
        }

        public function testUpdateReadSubscriptionTableForAllUsersAndModels()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $steven = User::getByUsername('steven');

            $sql = "DELETE FROM account_read_subscription";
            R::exec($sql);

            $accounts = Account::getAll();
            foreach ($accounts as $account)
            {
                $account->delete();
                $account->forgetAll();
            }

            $account1 = AccountTestHelper::createAccountByNameForOwner('First Account', $super);
            sleep(1);
            $account2 = AccountTestHelper::createAccountByNameForOwner('First Account', $steven);
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables(false);

            $sql = "SELECT * FROM account_read_subscription WHERE userid = " . $super->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(2, count($permissionTableRows));
            $this->assertEquals($account1->id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($super->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);
            $this->assertEquals($account2->id, $permissionTableRows[1]['modelid']);
            $this->assertEquals($super->id, $permissionTableRows[1]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[1]['subscriptiontype']);

            $sql = "SELECT * FROM account_read_subscription WHERE userid = " . $steven->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));
            $this->assertEquals($account2->id, $permissionTableRows[0]['modelid']);
            $this->assertEquals($steven->id, $permissionTableRows[0]['userid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $permissionTableRows[0]['subscriptiontype']);
        }

        public function testGetAddedOrDeletedModelsFromReadSubscriptionTable()
        {
            ReadPermissionsSubscriptionUtil::buildTables();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $task = TaskTestHelper::createTaskByNameForOwner('Test Task', $super);
            ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables(false);
            $sql = "SELECT * FROM task_read_subscription WHERE userid = " . $super->id;
            $permissionTableRows = R::getAll($sql);
            $this->assertEquals(1, count($permissionTableRows));

            $addedModelIds = ReadPermissionsSubscriptionUtil::getAddedOrDeletedModelsFromReadSubscriptionTable(
                'TestService', 'Task', 0, ReadPermissionsSubscriptionUtil::TYPE_ADD, $super);
            $this->asserttrue(is_array($addedModelIds));
            $this->assertEquals(1, count($addedModelIds));

            ModelCreationApiSyncUtil::insertItem('TestService', $task->id, 'Task', '2013-05-03 15:16:06');
            $addedModelIds = ReadPermissionsSubscriptionUtil::getAddedOrDeletedModelsFromReadSubscriptionTable(
                'TestService', 'Task', 0, ReadPermissionsSubscriptionUtil::TYPE_ADD, $super);
            $this->asserttrue(is_array($addedModelIds));
            $this->assertEquals(0, count($addedModelIds));
        }
    }
?>
