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

    class WorkflowMessageInQueueTest extends WorkflowBaseTest
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

        public function testSetAndGet()
        {
            $model = new WorkflowModelTestItem();
            $model->lastName = 'Green';
            $model->string   = 'string';
            $saved = $model->save();
            $this->assertTrue($saved);

            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow->save();
            $this->assertTrue($saved);

            $workflowMessageInQueue                  = new WorkflowMessageInQueue();
            $workflowMessageInQueue->modelClassName  = get_class($model);
            $workflowMessageInQueue->modelItem       = $model;
            $workflowMessageInQueue->processDateTime = '2007-02-02 00:00:00';
            $workflowMessageInQueue->savedWorkflow   = $savedWorkflow;
            $workflowMessageInQueue->triggeredByUser = Yii::app()->user->userModel;
            $workflowMessageInQueue->serializedData  = serialize(array('something'));
            $saved = $workflowMessageInQueue->save();
            $this->assertTrue($saved);
            $id = $workflowMessageInQueue->id;
            $workflowMessageInQueue->forget();

            //Retrieve and compare
            $workflowMessageInQueue = WorkflowMessageInQueue::getById($id);
            $this->assertEquals('WorkflowModelTestItem',    $workflowMessageInQueue->modelClassName);
            $this->assertTrue  ($workflowMessageInQueue->modelItem->isSame($model));
            $this->assertEquals('2007-02-02 00:00:00',      $workflowMessageInQueue->processDateTime);
            $this->assertTrue  ($workflowMessageInQueue->savedWorkflow->isSame($savedWorkflow));
            $this->assertTrue  ($workflowMessageInQueue->triggeredByUser->isSame(Yii::app()->user->userModel));
            $this->assertEquals(serialize(array('something')), $workflowMessageInQueue->serializedData);
        }

        /**
         * @depends testSetAndGet
         */
        public function testGetModelsToProcess($pageSize)
        {
            $this->assertEquals(1, count(WorkflowMessageInQueue::getAll()));
            $models = WorkflowMessageInQueue::getModelsToProcess(10);
            $this->assertEquals(1, count($models));

            //Now have one that is not ready for processing. It should still only get 1
            $model = new WorkflowModelTestItem();
            $model->lastName = 'Green2';
            $model->string   = 'string2';
            $saved = $model->save();
            $this->assertTrue($saved);

            $savedWorkflows = SavedWorkflow::getByName('some workflow');

            $workflowMessageInQueue                  = new WorkflowMessageInQueue();
            $workflowMessageInQueue->modelClassName  = get_class($model);
            $workflowMessageInQueue->modelItem       = $model;
            $workflowMessageInQueue->processDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 86400);
            $workflowMessageInQueue->savedWorkflow   = $savedWorkflows[0];
            $workflowMessageInQueue->serializedData  = serialize(array('something'));
            $saved = $workflowMessageInQueue->save();
            $this->assertTrue($saved);

            $models = WorkflowMessageInQueue::getModelsToProcess(10);
            $this->assertEquals(1, count($models));
        }
    }
?>