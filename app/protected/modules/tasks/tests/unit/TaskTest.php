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

    class TaskTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }


        public function testCreateTaskWithZerosStampAndEditAgain()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $task                       = new Task();
            $task->name                 = 'My Task';
            $task->owner                = Yii::app()->user->userModel;
            $task->completedDateTime    = '0000-00-00 00:00:00';
            $saved = $task->save();
            $this->assertTrue($saved);
            $taskId = $task->id;
            $task->forget();
            unset($task);

            $task       = Task::getById($taskId);
            $task->name ='something new';
            $saved      = $task->save();
            $this->assertTrue($saved);

            $task->delete();
        }

        /**
         * @depends testCreateTaskWithZerosStampAndEditAgain
         */
        public function testCreateAndGetTaskById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');

            $user                   = UserTestHelper::createBasicUser('Billy');
            $dueStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $completedStamp         = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 9000);
            $task                   = new Task();
            $task->name             = 'MyTask';
            $task->owner            = $user;
            $task->dueDateTime       = $dueStamp;
            $task->completedDateTime = $completedStamp;
            $task->description      = 'my test description';
            $task->activityItems->add($accounts[0]);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('MyTask',              $task->name);
            $this->assertEquals($dueStamp,             $task->dueDateTime);
            $this->assertEquals($completedStamp,       $task->completedDateTime);
            $this->assertEquals('my test description', $task->description);
            $this->assertEquals($user,                 $task->owner);
            $this->assertEquals(1, $task->activityItems->count());
            $this->assertEquals($accounts[0], $task->activityItems->offsetGet(0));
            foreach ($task->activityItems as $existingItem)
            {
                $castedDownModel = $existingItem->castDown(array('Account')); //this should not fail
            }
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testAddingActivityItemThatShouldCastDownAndThrowException()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');
            $accountId = $accounts[0]->id;
            $accounts[0]->forget();
            $task = new Task();
            $task->activityItems->add(Account::getById($accountId));
            foreach ($task->activityItems as $existingItem)
            {
                try
                {
                    $castedDownModel = $existingItem->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Account'))); //this should not fail
                }
                catch (NotFoundException $e)
                {
                    $this->fail();
                }
            }
            foreach ($task->activityItems as $existingItem)
            {
                try
                {
                    $castedDownModel = $existingItem->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact'))); //this should fail
                    $this->fail();
                }
                catch (NotFoundException $e)
                {
                }
            }
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getByName('MyTask');
            $this->assertEquals(1, count($tasks));
            $this->assertEquals('Task',   $tasks[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Tasks',  $tasks[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetLabel
         */
        public function testGetTasksByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getByName('Test Task 69');
            $this->assertEquals(0, count($tasks));
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testUpdateTaskFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('billy');
            $tasks = Task::getByName('MyTask');
            $task = $tasks[0];
            $this->assertEquals($task->name, 'MyTask');
            $postData = array(
                'owner' => array(
                    'id' => $user->id,
                ),
                'name' => 'New Name',
                'dueDateTime' => '', //setting dueDate to a blank value.
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel($task, $postData);
            $task->setAttributes($sanitizedPostData);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('New Name', $task->name);
            $this->assertEquals(null,     $task->dueDateTime);

            //create new task from scratch where the DateTime attributes are not populated. It should let you save.
            $task = new Task();
            $postData = array(
                'owner' => array(
                    'id' => $user->id,
                ),
                'name' => 'Lamazing',
                'dueDateTime' => '', //setting dueDate to a blank value.
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel($task, $postData);
            $task->setAttributes($sanitizedPostData);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('Lamazing', $task->name);
            $this->assertEquals(null,     $task->dueDateTime);
        }

        /**
         * @depends testUpdateTaskFromForm
         */
        public function testDeleteTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getAll();
            $this->assertEquals(2, count($tasks));
            $tasks[0]->delete();
            $tasks = Task::getAll();
            $this->assertEquals(1, count($tasks));
        }

        public function testManyToManyRelationInTheMiddleOfTheInheritanceHierarchy()
        {
            if (!RedBeanDatabase::isFrozen())
            {
                // This test uses TestManyManyRelationToItemModel
                // which is not created in freeze land.
                Yii::app()->user->userModel = User::getByUsername('super');
                $accounts = Account::getByName('anAccount');

                $possibleDerivationPaths = array(
                                               array('SecurableItem', 'OwnedSecurableItem', 'Account'),
                                               array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact'),
                                               array('SecurableItem', 'OwnedSecurableItem', 'Opportunity'),
                                           );

                $model = new TestManyManyRelationToItemModel();
                $model->items->add($accounts[0]);
                $this->assertTrue($model->save());

                $item = Item::getById($model->items[0]->getClassId('Item'));
                $this->assertTrue ($item instanceof Item);
                $this->assertFalse($item instanceof Account);
                $this->assertTrue ($item->isSame($accounts[0]));
                $account2 = $item->castDown($possibleDerivationPaths);
                $this->assertTrue ($account2->isSame($accounts[0]));

                $id = $model->id;
                unset($model);
                RedBeanModel::forgetAll();

                $model = TestManyManyRelationToItemModel::getById($id);
                $this->assertEquals(1, $model->items->count());
                $this->assertTrue ($model->items[0] instanceof Item);
                $this->assertFalse($model->items[0] instanceof Account);
                $this->assertTrue ($model->items[0]->isSame($accounts[0]));
                $account3 = $model->items[0]->castDown($possibleDerivationPaths);
                $this->assertTrue ($account3->isSame($accounts[0]));
            }
        }

        /**
         * @depends testDeleteTask
         */
        public function testAutomatedCompletedDateTimeAndLatestDateTimeChanges()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            //Creating a new task that is not completed. LatestDateTime should default to now, and
            //completedDateTime should be null.
            $task = new Task();
            $task->name = 'aTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());
            $this->assertEquals(null, $task->completedDateTime);
            $this->assertEquals($nowStamp, $task->latestDateTime);

            //Modify the task. Complete the task. The CompletedDateTime should show as now.
            $task = Task::getById($task->id);
            $this->assertNull($task->completed);
            $task->completed = true;
            $this->assertEquals($nowStamp, $task->latestDateTime);
            $completedStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 1);
            $this->assertNotEquals($nowStamp, $completedStamp);
            sleep(1); //Some servers are too fast and the test will fail if we don't have this.
            $this->assertTrue($task->save());
            $this->assertNotEquals($nowStamp, $task->completedDateTime);
            $this->assertNotEquals($nowStamp, $task->latestDateTime);
            $this->assertTrue($task->completedDateTime == $task->latestDateTime);
            $existingStamp = $task->completedDateTime;

            //Modify the task. CompletedDateTime and LatestDateTime should remain the same.
            $newStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 1);
            $this->assertNotEquals($existingStamp, $newStamp);
            $task = Task::getById($task->id);
            $task->name = 'aNewName';
            sleep(1); //Some servers are too fast and the test will fail if we don't have this.
            $this->assertTrue($task->save());
            $this->assertEquals($existingStamp, $task->completedDateTime);
            $this->assertEquals($existingStamp, $task->latestDateTime);
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = TasksModule::getModelClassNames();
            $this->assertEquals(1, count($modelClassNames));
            $this->assertEquals('Task', $modelClassNames[0]);
        }
    }
?>
