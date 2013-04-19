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

    class WorkflowEmailMessageRecipientFormTest extends WorkflowBaseTest
    {
        public $freeze = false;

        protected static $superUserId;

        protected static $bobbyUserId;

        protected static $sarahUserId;

        protected static $superBossUserId;

        protected static $bobbyBossUserId;

        protected static $sarahBossUserId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $bobbyBoss = UserTestHelper::createBasicUserWithEmailAddress('bobbyBoss');
            $sarahBoss = UserTestHelper::createBasicUserWithEmailAddress('sarahBoss');
            $superBoss = UserTestHelper::createBasicUserWithEmailAddress('superBoss');
            $super = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            $super->manager = $superBoss;
            assert($super->save()); // Not Coding Standard
            $bobby = UserTestHelper::createBasicUserWithEmailAddress('bobby');
            $bobby->manager = $bobbyBoss;
            assert($bobby->save()); // Not Coding Standard
            $sarah = UserTestHelper::createBasicUserWithEmailAddress('sarah');
            $sarah->manager = $sarahBoss;
            assert($sarah->save()); // Not Coding Standard
            self::$superUserId = $super->id;
            self::$bobbyUserId = $bobby->id;
            self::$sarahUserId = $sarah->id;
            self::$superBossUserId = $superBoss->id;
            self::$bobbyBossUserId = $bobbyBoss->id;
            self::$sarahBossUserId = $sarahBoss->id;
        }

        public function setup()
        {
            parent::setUp();
            $freeze = false;
            if (RedBeanDatabase::isFrozen())
            {
                RedBeanDatabase::unfreeze();
                $freeze = true;
            }
            $this->freeze = $freeze;
        }

        public function teardown()
        {
            if ($this->freeze)
            {
                RedBeanDatabase::freeze();
            }
            parent::teardown();
        }

        public function testStringifiedModelForValue()
        {
             $form = new StaticUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
             $form->userId = Yii::app()->user->userModel->id;
             $this->assertEquals('Clark Kent', $form->stringifiedModelForValue);

             //Now switch userId, and the stringifiedModelForValue should clear out.
             $bobby = User::getByUsername('bobby');
             $form->userId = $bobby->id;
             $this->assertEquals('bobby bobbyson', $form->stringifiedModelForValue);
             //test setting via setAttributes, it should ignore it.
             $form->setAttributes(array('stringifiedModelForValue' => 'should not set'));
             $this->assertEquals('bobby bobbyson', $form->stringifiedModelForValue);
        }

        public function testMakeRecipientsForStaticAddress()
        {
            $form  = new StaticAddressWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->toName = 'someName';
            $form->toAddress = 'someone@zurmo.com';
            $model = new WorkflowModelTestItem();
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('someName'         , $recipients[0]->toName);
            $this->assertEquals('someone@zurmo.com', $recipients[0]->toAddress);
            $this->assertTrue  ($recipients[0]->personOrAccount->id < 0);
        }

        public function testMakeRecipientsForStaticUser()
        {
            $form  = new StaticUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->userId = self::$bobbyUserId;
            $model = new WorkflowModelTestItem();
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('bobby bobbyson' ,   $recipients[0]->toName);
            $this->assertEquals('bobby@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$bobbyUserId,  $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForStaticRole()
        {
            $role  = new Role();
            $role->name = 'some group';
            $role->users->add(User::getById(self::$sarahUserId));
            $role->users->add(User::getById(self::$bobbyUserId));
            $saved = $role->save();
            $this->assertTrue($saved);
            $form  = new StaticRoleWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->roleId = $role->id;
            $model = new WorkflowModelTestItem();
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(2, count($recipients));
            $this->assertEquals('sarah sarahson' ,   $recipients[0]->toName);
            $this->assertEquals('sarah@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$sarahUserId,  $recipients[0]->personOrAccount->id);
            $this->assertEquals('bobby bobbyson' ,   $recipients[1]->toName);
            $this->assertEquals('bobby@zurmo.com',   $recipients[1]->toAddress);
            $this->assertEquals(self::$bobbyUserId,  $recipients[1]->personOrAccount->id);
        }

        public function testMakeRecipientsForStaticGroup()
        {
            $group = new Group();
            $group->name = 'some group';
            $group->users->add(User::getById(self::$sarahUserId));
            $group->users->add(User::getById(self::$bobbyUserId));
            $saved = $group->save();
            $this->assertTrue($saved);
            $form  = new StaticGroupWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->groupId = $group->id;
            $model = new WorkflowModelTestItem();
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(2, count($recipients));
            $this->assertEquals('sarah sarahson' ,   $recipients[0]->toName);
            $this->assertEquals('sarah@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$sarahUserId,  $recipients[0]->personOrAccount->id);
            $this->assertEquals('bobby bobbyson' ,   $recipients[1]->toName);
            $this->assertEquals('bobby@zurmo.com',   $recipients[1]->toAddress);
            $this->assertEquals(self::$bobbyUserId,  $recipients[1]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredUser()
        {
            $form  = new DynamicTriggeredByUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $model = new WorkflowModelTestItem();
            $recipients = $form->makeRecipients($model, User::getById(self::$bobbyUserId));
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('bobby bobbyson' ,   $recipients[0]->toName);
            $this->assertEquals('bobby@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$bobbyUserId,  $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserCreatedByUser()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_CREATED_BY_USER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('bobby bobbyson' ,   $recipients[0]->toName);
            $this->assertEquals('bobby@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$bobbyUserId,  $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserManagerOfCreatedByUser()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_CREATED_BY_USER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('bobbyBoss bobbyBossson', $recipients[0]->toName);
            $this->assertEquals('bobbyBoss@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$bobbyBossUserId,   $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserModifiedByUser()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_MODIFIED_BY_USER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('sarah sarahson' ,  $recipients[0]->toName);
            $this->assertEquals('sarah@zurmo.com',  $recipients[0]->toAddress);
            $this->assertEquals(self::$sarahUserId, $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserManagerOfModifiedByUser()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_MODIFIED_BY_USER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('sarahBoss sarahBossson', $recipients[0]->toName);
            $this->assertEquals('sarahBoss@zurmo.com',   $recipients[0]->toAddress);
            $this->assertEquals(self::$sarahBossUserId,   $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserOwner()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_OWNER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('Clark Kent' ,                   $recipients[0]->toName);
            $this->assertEquals('super@zurmo.com',               $recipients[0]->toAddress);
            $this->assertEquals(Yii::app()->user->userModel->id, $recipients[0]->personOrAccount->id);
        }

        public function testMakeRecipientsForDynamicTriggeredModelUserManagerOfOwner()
        {
            $form  = new DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->dynamicUserType = DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::DYNAMIC_USER_TYPE_MANAGER_OF_OWNER;
            $model = new WorkflowModelTestItem();
            $model->setScenario('importModel');
            $model->lastName      = 'lastName';
            $model->string        = 'string';
            $model->createdByUser = User::getById(self::$bobbyUserId);
            $model->modifiedByUser = User::getById(self::$sarahUserId);
            $this->assertTrue($model->save());
            $modelId = $model->id;
            $model->forget();
            $model   = WorkflowModelTestItem::getById($modelId);
            $recipients = $form->makeRecipients($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($recipients));
            $this->assertEquals('superBoss superBossson' , $recipients[0]->toName);
            $this->assertEquals('superBoss@zurmo.com',    $recipients[0]->toAddress);
            $this->assertEquals(self::$superBossUserId,    $recipients[0]->personOrAccount->id);
        }
    }
?>