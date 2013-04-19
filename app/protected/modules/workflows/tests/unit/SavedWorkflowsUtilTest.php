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

    class SavedWorkflowsUtilTest extends WorkflowBaseTest
    {
        public $freeze = false;

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

        public function testResolveProcessDateTimeByWorkflowAndModel()
        {
            //Test Date
            $model    = new WorkflowModelTestItem();
            $model->date = '2007-02-02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-02-03 00:00:00', $processDateTime);

            //Test Date with negative duration
            $model    = new WorkflowModelTestItem();
            $model->date = '2007-02-02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, -86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-02-01 00:00:00', $processDateTime);

            //Test DateTime
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '2007-05-02 04:00:02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-05-03 04:00:02', $processDateTime);

            //Test DateTime with negative duration
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '2007-05-02 04:00:02';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, -86400);
            $processDateTime = SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
            $this->assertEquals('2007-05-01 04:00:02', $processDateTime);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModel
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithNullDate()
        {
            $model    = new WorkflowModelTestItem();
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithNullDate
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDate()
        {
            $model           = new WorkflowModelTestItem();
            $model->dateTime = '0000-00-00';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('date', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDate
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithNullDateTime()
        {
            $model    = new WorkflowModelTestItem();
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithNullDateTime
         * @expectedException ValueForProcessDateTimeIsNullException
         */
        public function testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDateTime()
        {
            $model    = new WorkflowModelTestItem();
            $model->dateTime = '0000-00-00 00:00:00';
            $workflow = WorkflowTriggersUtilBaseTest::
                        makeOnSaveWorkflowAndTimeTriggerForDateOrDateTime('dateTime', 'Is Time For', null, 86400);
            SavedWorkflowsUtil::resolveProcessDateTimeByWorkflowAndModel($workflow, $model);
        }

        /**
         * @depends testResolveProcessDateTimeByWorkflowAndModelWithPseudoNullDateTime
         */
        public function testResolveOrder()
        {
            $this->assertCount(0, SavedWorkflow::getAll());
            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId1 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 2';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(2, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId2 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 3';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(3, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId3 = $savedWorkflow->id;

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 4';
            $savedWorkflow->moduleClassName = 'ContactsModule';
            $savedWorkflow->serializedData  = serialize(array('some data'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId4 = $savedWorkflow->id;

            $savedWorkflow = SavedWorkflow::getById($savedWorkflowId2);
            $this->assertEquals(2, $savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(2, $savedWorkflow->order);

            //Change the moduleClassName to opportunities, it should show 1
            $savedWorkflow->moduleClassName = 'OpportunitiesModule';
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(1, $savedWorkflow->order);

            //Delete the workflow. When creating a new AccountsWorkflow, it should show order 4 since the max
            //is still 3.
            $deleted = $savedWorkflow->delete();
            $this->assertTrue($deleted);

            $savedWorkflow = new SavedWorkflow();
            $savedWorkflow->name            = 'the name 5';
            $savedWorkflow->moduleClassName = 'AccountsModule';
            $savedWorkflow->serializedData  = serialize(array('some data 2'));
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $this->assertNull($savedWorkflow->order);
            SavedWorkflowsUtil::resolveOrder($savedWorkflow);
            $this->assertEquals(4, $savedWorkflow->order);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveOrder
         */
        public function testResolveBeforeSaveByModel()
        {
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add trigger
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array('string' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Confirm that the workflow processes and the attribute gets updated
            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            SavedWorkflowsUtil::resolveBeforeSaveByModel($model, Yii::app()->user->userModel);
            $this->assertEquals('jason', $model->string);
            $this->assertTrue($model->id < 0);

            //Change the workflow to inactive
            $savedWorkflow->isActive = false;
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $model         = new WorkflowModelTestItem();
            $model->string = 'aValue';
            SavedWorkflowsUtil::resolveBeforeSaveByModel($model, Yii::app()->user->userModel);
            $this->assertEquals('aValue', $model->string);
            $this->assertTrue($model->id < 0);
        }

        /**
         * @depends testResolveBeforeSaveByModel
         */
        public function testResolveBeforeSaveByModelForByTime()
        {
            //Create workflow
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_BY_TIME);
            $workflow->setTriggersStructure('1');
            $workflow->setIsActive(true);
            //Add time trigger
            $trigger = new TimeTriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'date';
            $trigger->durationSeconds             = '500';
            $trigger->valueType                   = 'Is Time For';
            $workflow->setTimeTrigger($trigger);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Confirm that the workflow processes and the attribute gets updated
            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $model->date   = '2013-02-02';
            $this->assertEquals(0, count($model->getWorkflowsToProcessAfterSave()));
            SavedWorkflowsUtil::resolveBeforeSaveByModel($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count($model->getWorkflowsToProcessAfterSave()));
            $this->assertTrue($model->id < 0);
        }

        /**
         * @depends testResolveBeforeSaveByModelForByTime
         */
        public function testResolveAfterSaveByModel()
        {
            //Create workflow
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            $workflow->setIsActive(true);
            //Add trigger
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasOne';
            $attributes                   = array('name' => array('shouldSetValue'    => '1',
                                                   'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                   'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $model = new WorkflowModelTestItem();
            $model->string = 'aValue';
            $saved         = $savedWorkflow->save();
            $this->assertTrue($saved);

            $model->addWorkflowToProcessAfterSave($workflow);
            $this->assertEquals(0, count(WorkflowModelTestItem2::getAll()));
            SavedWorkflowsUtil::resolveAfterSaveByModel($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count(WorkflowModelTestItem2::getAll()));
        }

        /**
         * @depends testResolveAfterSaveByModel
         */
        public function testResolveAfterSaveByModelForByTime()
        {
            //Create workflow
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_BY_TIME);
            $workflow->setTriggersStructure('1');
            $workflow->setIsActive(true);
            //Add time trigger
            $trigger = new TimeTriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'date';
            $trigger->durationSeconds             = '500';
            $trigger->valueType                   = 'Is Time For';
            $workflow->setTimeTrigger($trigger);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
            $workflow->setId($savedWorkflow->id); //set Id back.

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'something';
            $model->string   = 'aValue';
            $model->date     = '2013-03-03';
            $saved           = $model->save();
            $this->assertTrue($saved);

            $model->addWorkflowToProcessAfterSave($workflow);
            $this->assertEquals(0, count(ByTimeWorkflowInQueue::getAll()));
            SavedWorkflowsUtil::resolveAfterSaveByModel($model, Yii::app()->user->userModel);
            $this->assertEquals(1, count(ByTimeWorkflowInQueue::getAll()));
        }
    }
?>