<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Task module walkthrough tests for a regular user.
     */
    class TasksRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function testRegularUserAllControllerActions()
        {
            //Now test all portlet controller actions

            //Now test peon with elevated rights to tabs /other available rights
            //such as convert lead

            //Now test peon with elevated permissions to models.
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/createFromRelation');
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');

            //actionDelete should fail.
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/delete');
        }

         /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to accounts
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $this->assertTrue($nobody->save());

            //create the account as nobody user as the owner
            $account = AccountTestHelper::createAccountByNameForOwner('accountOwnedByNobody', $nobody);

            //Now test peon with elevated rights to tasks
            $nobody->setRight('TasksModule', TasksModule::RIGHT_ACCESS_TASKS);
            $nobody->setRight('TasksModule', TasksModule::RIGHT_CREATE_TASKS);
            $nobody->setRight('TasksModule', TasksModule::RIGHT_DELETE_TASKS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $task = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('taskCreatedByNobody', $nobody, $account);

            //Test whether the nobody user is able to view the task details that he created
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //add related task for account using createFromRelation action
            $activityItemPostData = array('Account' => array('id' => $account->id));
            $this->setGetArray(array('relationAttributeName' => 'Account', 'relationModelId' => $account->id,
                                     'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Task' => array('name' => 'myTask')));
            $this->runControllerWithRedirectExceptionAndGetContent('tasks/default/createFromRelation');

            //Test nobody can delete an existing task he craeted and it redirects to index.
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('tasks/default/delete');
        }

         /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create superAccount owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccount = AccountTestHelper::createAccountByNameForOwner('AccountsForElevationToModelTest', $super);

            //Test nobody, access to edit and details of superAccount should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->setGetArray(array('id' => $superAccount->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $superAccount->addPermissions($nobody, Permission::READ);
            $this->assertTrue($superAccount->save());

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $superAccount->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create task for an superAccount using the super user
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('taskCreatedByNobody', $super, $superAccount);

            //Test nobody, access to edit and details of task should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();

            //give nobody access to details view only
            Yii::app()->user->userModel = $super;
            $task->addPermissions($nobody, Permission::READ);
            $this->assertTrue($task->save());

            //Now access to tasks view by Nobody should not fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //Now access to tasks edit by Nobody should fail
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //give nobody access to both details and edit view
            Yii::app()->user->userModel = $super;
            $task->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($task->save());

            //Now access to tasks view and edit by Nobody should not fail.
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');

            //revoke the permission from the nobody user to access the task
            Yii::app()->user->userModel = $super;
            $task->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($task->save());

            //Now nobodys, access to edit and details of tasks should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $task->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');

            //create some roles
            Yii::app()->user->userModel = $super;
            $parentRole = new Role();
            $parentRole->name = 'AAA';
            $this->assertTrue($parentRole->save());

            $childRole = new Role();
            $childRole->name = 'BBB';
            $this->assertTrue($childRole->save());

            $userInParentRole = User::getByUsername('confused');
            $userInChildRole = User::getByUsername('nobody');

            $childRole->users->add($userInChildRole);
            $this->assertTrue($childRole->save());
            $parentRole->users->add($userInParentRole);
            $parentRole->roles->add($childRole);
            $this->assertTrue($parentRole->save());

            //create account owned by super
            $account2 = AccountTestHelper::createAccountByNameForOwner('AccountsParentRolePermission', $super);

            //Test userInParentRole, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $account2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($account2->save());

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $account2->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create a task owned by super
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task2 = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('taskCreatedBySuperForRole', $super, $account2);

            //Test userInParentRole, access to tasks details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');

            //give userInChildRole access to READ permision for tasks
            Yii::app()->user->userModel = $super;
            $task2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($task2->save());

            //Test userInChildRole, access to tasks details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //Test userInParentRole, access to tasks details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //give userInChildRole access to read and write for the tasks
            Yii::app()->user->userModel = $super;
            $task2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($task2->save());

            //Test userInChildRole, access to tasks edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');

            //Test userInParentRole, access to tasks edit should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');

            //revoke userInChildRole access to read and write tasks
            Yii::app()->user->userModel = $super;
            $task2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($task2->save());

            //Test userInChildRole, access to detail and edit should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //Test userInParentRole, access to detail and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $task2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            //create some groups and assign users to groups
            Yii::app()->user->userModel = $super;
            $parentGroup = new Group();
            $parentGroup->name = 'AAA';
            $this->assertTrue($parentGroup->save());

            $childGroup = new Group();
            $childGroup->name = 'BBB';
            $this->assertTrue($childGroup->save());

            $userInChildGroup = User::getByUsername('confused');
            $userInParentGroup = User::getByUsername('nobody');

            $childGroup->users->add($userInChildGroup);
            $this->assertTrue($childGroup->save());
            $parentGroup->users->add($userInParentGroup);
            $parentGroup->groups->add($childGroup);
            $this->assertTrue($parentGroup->save());
            $parentGroup->forget();
            $childGroup->forget();
            $parentGroup = Group::getByName('AAA');
            $childGroup = Group::getByName('BBB');

            //Add access for the confused user to accounts and creation of accounts.
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $userInChildGroup->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $this->assertTrue($userInChildGroup->save());

            //create account owned by super
            $account3 = AccountTestHelper::createAccountByNameForOwner('testingAccountsParentGroupPermission', $super);

            //Test userInParentGroup, access to details should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //Test userInChildGroup, access to details should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $account3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account3->save());

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $account3->id));
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //create a task owned by super
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task3 = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('taskCreatedBySuperForGroup', $super, $account3);

            //Add access for the confused user to accounts and creation of accounts.
            $userInChildGroup->setRight('TasksModule', TasksModule::RIGHT_ACCESS_TASKS);
            $userInChildGroup->setRight('TasksModule', TasksModule::RIGHT_CREATE_TASKS);
            $this->assertTrue($userInChildGroup->save());

            //Test userInParentGroup, access to tasks details and edit should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $task3->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');

            //Test userInChildGroup, access to tasks details and edit should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $task3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($task3->save());

            //Test userInParentGroup, access to tasks details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //Test userInChildGroup, access to tasks details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/details');

            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $task3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($task3->save());

            //Test userInParentGroup, access to edit tasks should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');

            //Test userInChildGroup, access to edit tasks should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');

            //revoke parentGroup access to tasks read and write
            Yii::app()->user->userModel = $super;
            $task3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($task3->save());

            //Test userInChildGroup, access to tasks detail should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //Test userInParentGroup, access to tasks detail should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/details');
            $this->setGetArray(array('id' => $task3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $parentGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');
            $parentGroup                = Group::getByName('AAA');

            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }
    }
?>
