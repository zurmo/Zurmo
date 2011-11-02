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
    * Designer Module Walkthrough of tasks.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also tests the creation of the customfileds, addition of custom fields to all the layouts.
    * This also tests creation, edit and delete of the tasks based on the custom fields.
    */
    class TasksDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();
            //Create a account for testing.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create a opportunity for testing.
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);

            //Create a two contacts for testing.
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact1', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);

            //Create a task for testing.
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('superTask', $super, $account);
        }

        public function testSuperUserTaskDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Load AttributesList for Task module.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for Task module.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'TasksModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertTrue(strlen($content) > 50); //approximate, but should definetely be larger than 50.

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'TasksModuleForm' => $this->createModuleEditGoodValidationPostData('task new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Now save successfully.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->setPostArray(array('save' => 'Save',
                'TasksModuleForm' => $this->createModuleEditGoodValidationPostData('task new name')));
            $this->runControllerWithRedirectExceptionAndGetContent('designer/default/moduleEdit');

            //Now confirm everything did in fact save correctly.
            $this->assertEquals('Task New Name',  TasksModule::getModuleLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Task New Names', TasksModule::getModuleLabelByTypeAndLanguage('Plural'));
            $this->assertEquals('task new name',  TasksModule::getModuleLabelByTypeAndLanguage('SingularLowerCase'));
            $this->assertEquals('task new names', TasksModule::getModuleLabelByTypeAndLanguage('PluralLowerCase'));

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForAccountRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForContactRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForOpportunityRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'TaskEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserTaskDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->setGetArray(array('moduleClassName' => 'TasksModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributeCreate');

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('TasksModule', 'checkbox');
            $this->createCurrencyValueCustomFieldByModule       ('TasksModule', 'currency');
            $this->createDateCustomFieldByModule                ('TasksModule', 'date');
            $this->createDateTimeCustomFieldByModule            ('TasksModule', 'datetime');
            $this->createDecimalCustomFieldByModule             ('TasksModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('TasksModule', 'picklist');
            $this->createIntegerCustomFieldByModule             ('TasksModule', 'integer');
            $this->createMultiSelectDropDownCustomFieldByModule ('TasksModule', 'multiselect');
            $this->createPhoneCustomFieldByModule               ('TasksModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('TasksModule', 'radio');
            $this->createTextCustomFieldByModule                ('TasksModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('TasksModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('TasksModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForTasksModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to TaskEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'TaskEditAndDetailsView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTaskEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            //Add all fields to OpenTasksForAccountRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForAccountRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForContactRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);

            $this->setGetArray(array('moduleClassName' => 'TasksModule',
                                     'viewClassName'   => 'OpenTasksForOpportunityRelatedListView'));
            $layout = TasksDesignerWalkthroughHelperUtil::getTasksRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertFalse(strpos($content, 'Layout saved successfully') === false);
        }

        /**
         * @depends testSuperUserAddCustomFieldsToLayoutsForTasksModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superTaskId = self::getModelIdByModelNameAndName ('Task', 'superTask');
            //Load create, edit, and details views.
            $this->setGetArray(array('id' => $superTaskId));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/edit');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/createFromRelation');
            //todo: more permutations from different relations.
        }

        /**
         * @depends testLayoutsLoadOkAfterCustomFieldsPlacedForTasksModule
         */
        public function testCreateAnTaskAfterTheCustomFieldsArePlacedForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId     = self::getModelIdByModelNameAndName('Contact', 'superContact1 superContact1son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());

            //Create a new task based on the custom fields.
            $this->setGetArray(array(   'relationAttributeName'  => 'Account',
                                        'relationModelId'        => $superAccount[0]->id,
                                        'relationModuleId'       => 'account',
                                        'redirectUrl'            => 'someRedirection'));

            $this->setPostArray(array('Task' => array(
                                            'name'                              => 'myNewTask',
                                            'dueDateTime'                       => $datetime,
                                            'completed'                         => '0',
                                            'completedDateTime'                 => '',
                                            'description'                       => 'This is task Description',
                                            'owner'                             => array('id' => $superUserId),
                                            'explicitReadWriteModelPermissions' => array('type' => null),
                                            'checkbox'                          => '1',
                                            'currency'                          => array('value'    => 45,
                                                                                         'currency' => array(
                                                                                         'id' => $baseCurrency->id)),
                                            'date'                              => $date,
                                            'datetime'                          => $datetime,
                                            'decimal'                           => '123',
                                            'picklist'                          => array('value' => 'a'),
                                            'integer'                           => '12',
                                            'phone'                             => '259-784-2169',
                                            'radio'                             => array('value' => 'd'),
                                            'text'                              => 'This is a test Text',
                                            'textarea'                          => 'This is a test TextArea',
                                            'url'                               => 'http://wwww.abc.com'),
                                      'ActivityItemForm' => array(
                                            'Account'     => array('id'  => $superAccount[0]->id),
                                            'Contact'     => array('id'  => $superContactId),
                                            'Opportunity' => array('id'  => $superOpportunityId))));
            $this->runControllerWithRedirectExceptionAndGetUrl('tasks/default/createFromRelation');

            //Check the details if they are saved properly for the custom fields.
            $task = Task::getByName('myNewTask');

            //Retrieve the permission of the task.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Task::getById($task[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($task[0]->name                             , 'myNewTask');
            $this->assertEquals($task[0]->dueDateTime                      , $datetimeAssert);
            $this->assertEquals($task[0]->completed                        , '0');
            $this->assertEquals($task[0]->completedDateTime                , '');
            $this->assertEquals($task[0]->description                      , 'This is task Description');
            $this->assertEquals($task[0]->owner->id                        , $superUserId);
            $this->assertEquals($task[0]->activityItems->count()           , 3);
            $this->assertEquals(0                                          , count($readWritePermitables));
            $this->assertEquals(0                                          , count($readOnlyPermitables));
            $this->assertEquals($task[0]->checkbox                         , '1');
            $this->assertEquals($task[0]->currency->value                  , 45);
            $this->assertEquals($task[0]->currency->currency->id           , $baseCurrency->id);
            $this->assertEquals($task[0]->date                             , $dateAssert);
            $this->assertEquals($task[0]->datetime                         , $datetimeAssert);
            $this->assertEquals($task[0]->decimal                          , '123');
            $this->assertEquals($task[0]->picklist->value                  , 'a');
            $this->assertEquals($task[0]->integer                          , 12);
            $this->assertEquals($task[0]->phone                            , '259-784-2169');
            $this->assertEquals($task[0]->radio->value                     , 'd');
            $this->assertEquals($task[0]->text                             , 'This is a test Text');
            $this->assertEquals($task[0]->textarea                         , 'This is a test TextArea');
            $this->assertEquals($task[0]->url                              , 'http://wwww.abc.com');
        }

        /**
         * @depends testCreateAnTaskAfterTheCustomFieldsArePlacedForTasksModule
         */
        public function testEditOfTheTaskForTheCustomFieldsPlacedForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the task Id.
            $task = Task::getByName('myNewTask');

            //Set the date and datetime variable values here.
            $date           = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateFormat(), time());
            $dateAssert     = date('Y-m-d');
            $datetime       = Yii::app()->dateFormatter->format(DateTimeUtil::getLocaleDateTimeFormat(), time());
            $datetimeAssert = date('Y-m-d H:i:')."00";

            //Get the super user, account, opportunity and contact id.
            $superUserId        = $super->id;
            $superAccount       = Account::getByName('superAccount');
            $superContactId     = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superOpportunityId = self::getModelIdByModelNameAndName('Opportunity', 'superOpp');
            $baseCurrency       = Currency::getByCode(Yii::app()->currencyHelper->getBaseCode());
            $explicitReadWriteModelPermission = ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP;

            //Edit the task based on the custom fields and the task id.
            $this->setGetArray (array('id' => $task[0]->id));
            $this->setPostArray(array('Task' => array(
                                'name'                              => 'myEditTask',
                                'dueDateTime'                       => $datetime,
                                'completed'                         => '1',
                                'completedDateTime'                 => $datetime,
                                'description'                       => 'This is edit task Description',
                                'owner'                             => array('id' => $superUserId),
                                'explicitReadWriteModelPermissions' => array('type' => $explicitReadWriteModelPermission),
                                'checkbox'                          => '0',
                                'currency'                          => array('value'   => 40,
                                                                             'currency' => array(
                                                                             'id' => $baseCurrency->id)),
                                'date'                              => $date,
                                'datetime'                          => $datetime,
                                'decimal'                           => '12',
                                'picklist'                          => array('value' => 'b'),
                                'integer'                           => '11',
                                'phone'                             => '259-784-2069',
                                'radio'                             => array('value' => 'e'),
                                'text'                              => 'This is a test Edit Text',
                                'textarea'                          => 'This is a test Edit TextArea',
                                'url'                               => 'http://wwww.abc-edit.com'),
                                'ActivityItemForm' => array(
                                'Account'     => array('id'  => $superAccount[0]->id),
                                'Contact'     => array('id'  => $superContactId),
                                'Opportunity' => array('id'  => $superOpportunityId)),
                                'save' => 'Save'));
            $this->runControllerWithRedirectExceptionAndGetUrl('tasks/default/edit');

             //Check the details if they are saved properly for the custom fields.
            $task = Task::getByName('myEditTask');

            //Retrieve the permission of the task.
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem(Task::getById($task[0]->id));
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $readOnlyPermitables               = $explicitReadWriteModelPermissions->getReadOnlyPermitables();

            $this->assertEquals($task[0]->name                             , 'myEditTask');
            $this->assertEquals($task[0]->dueDateTime                      , $datetimeAssert);
            $this->assertEquals($task[0]->completed                        , '1');
            $this->assertEquals($task[0]->completedDateTime                , $datetimeAssert);
            $this->assertEquals($task[0]->description                      , 'This is edit task Description');
            $this->assertEquals($task[0]->owner->id                        , $superUserId);
            $this->assertEquals($task[0]->activityItems->count()           , 3);
            $this->assertEquals(1                                          , count($readWritePermitables));
            $this->assertEquals(0                                          , count($readOnlyPermitables));
            $this->assertEquals($task[0]->checkbox                         , '0');
            $this->assertEquals($task[0]->currency->value                  , 40);
            $this->assertEquals($task[0]->currency->currency->id           , $baseCurrency->id);
            $this->assertEquals($task[0]->date                             , $dateAssert);
            $this->assertEquals($task[0]->datetime                         , $datetimeAssert);
            $this->assertEquals($task[0]->decimal                          , '12');
            $this->assertEquals($task[0]->picklist->value                  , 'b');
            $this->assertEquals($task[0]->integer                          , 11);
            $this->assertEquals($task[0]->phone                            , '259-784-2069');
            $this->assertEquals($task[0]->radio->value                     , 'e');
            $this->assertEquals($task[0]->text                             , 'This is a test Edit Text');
            $this->assertEquals($task[0]->textarea                         , 'This is a test Edit TextArea');
            $this->assertEquals($task[0]->url                              , 'http://wwww.abc-edit.com');
        }

        /**
         * @depends testEditOfTheTaskForTheCustomFieldsPlacedForTasksModule
         */
        public function testDeleteOfTheTaskForTheCustomFieldsPlacedForTasksModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Retrieve the task Id.
            $task = Task::getByName('myEditTask');

            //Set the task id so as to delete the task.
            $this->setGetArray(array('id' => $task[0]->id));
            $this->runControllerWithRedirectExceptionAndGetUrl('tasks/default/delete');

            //Check to confirm that the task is deleted.
            $task = Task::getByName('myEditTask');
            $this->assertEquals(0, count($task));
        }
    }
?>