<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class WorkflowMessageInQueueJobTest extends WorkflowBaseTest
    {
        public function testWorkflowMessageInQueueProperlySavesWithoutTrashingRelatedModelItem()
        {
            $model                  = WorkflowTestHelper::createWorkflowModelTestItem('Jason', 'Green');
            $savedWorkflow          = WorkflowTestHelper::createByTimeSavedWorkflow();
            $workflowMessageInQueue = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow);
            $correctItemId          = $workflowMessageInQueue->getClassId('Item');
            $this->assertEquals((int)$correctItemId, (int)$workflowMessageInQueue->getClassId('Item'));
            $this->assertNotEquals((int)$model->getClassId('Item'), (int)$workflowMessageInQueue->getClassId('Item'));
            $modelId = $model->id;
            $queueId = $workflowMessageInQueue->id;

            RedBeanModelsCache::forgetAll(true); //simulates page change, required to confirm Item does not get trashed
            $workflowMessageInQueue = WorkflowMessageInQueue::getById($queueId);
            $deleted = $workflowMessageInQueue->delete();
            $this->assertTrue($deleted);
            $model = WorkflowModelTestItem::getById($modelId);
            $this->assertTrue($model->getClassId('Item') > 0);
        }

        /**
         * Test sending an email that should go out as a processing that this job would typically do.
         * Also tests that item does not get trashed when deleting the WorkflowMessageInQueue
         * @depends testWorkflowMessageInQueueProperlySavesWithoutTrashingRelatedModelItem
         */
        public function testRun()
        {
            Yii::app()->user->userModel    = User::getByUsername('super');
            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->name           = 'the name';
            $emailTemplate->modelClassName = 'Account';
            $emailTemplate->textContent    = 'some content';
            $emailTemplate->type           = 2;
            $emailTemplate->subject        = 'subject';
            $saved                         = $emailTemplate->save();
            $this->assertTrue($saved);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());

            $model       = ContactTestHelper::createContactByNameForOwner('Jason', Yii::app()->user->userModel);
            $model->primaryEmail->emailAddress = 'jason@zurmoland.com';
            $saved = $model->save();
            $this->assertTrue($saved);
            $modelId = $model->id;
            $model->forget();
            $model = Contact::getById($modelId);
            $trigger = array('attributeIndexOrDerivedType' => 'firstName',
                             'operator'                    => OperatorRules::TYPE_EQUALS,
                             'value'                       => 'jason');
            $actions     = array(array('type' => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                       ActionForWorkflowForm::ACTION_ATTRIBUTES =>
                                            array('description' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new description'))));
            $emailMessages   = array();
            $emailMessages[0]['emailTemplateId'] = $emailTemplate->id;
            $emailMessages[0]['sendFromType']    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $emailMessages[0]['sendAfterDurationSeconds'] = '0';
            $emailMessages[0][EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS] =
                array(
                    array('type'          => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL,
                        'audienceType'    => EmailMessageRecipient::TYPE_TO),
                );
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'ContactsModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW_AND_EXISTING;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $data[ComponentForWorkflowForm::TYPE_TRIGGERS]       = array($trigger);
            $data[ComponentForWorkflowForm::TYPE_ACTIONS]        = $actions;
            $data[ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES] = $emailMessages;
            $savedWorkflow->serializedData  = serialize($data);
            $savedWorkflow->isActive        = true;
            $saved                          = $savedWorkflow->save();
            $this->assertTrue($saved);
            WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow);

            RedBeanModelsCache::forgetAll(true); //simulates page change, required to confirm Item does not get trashed
            $this->assertEquals(1, count(WorkflowMessageInQueue::getAll()));
            $job = new WorkflowMessageInQueueJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));

            RedBeanModelsCache::forgetAll(true); //simulates page change, required to confirm Item does not get trashed
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
        }

        /**
         * @depends testRun
         */
        public function testRunAgainstWorkflowThatWasDeleted()
        {
            $model       = WorkflowTestHelper::createWorkflowModelTestItem('Green', '514');
            $timeTrigger = array('attributeIndexOrDerivedType' => 'string',
                                    'operator'                    => OperatorRules::TYPE_EQUALS,
                                    'value'                       => '514',
                                    'durationSeconds'             => '333');
            $actions     = array(array('type' => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                    ActionForWorkflowForm::ACTION_ATTRIBUTES =>
                                    array('string' => array('shouldSetValue'    => '1',
                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                        'value'  => 'jason'))));
            $savedWorkflow         = WorkflowTestHelper::createByTimeSavedWorkflow($timeTrigger, array(), $actions);
            WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow);

            //Now delete the old workflow
            $deleted = $savedWorkflow->delete();
            $this->assertTrue($deleted);

            $this->assertEquals(1, count(WorkflowMessageInQueue::getAll()));
            $job = new WorkflowMessageInQueueJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));
                        $model->forget();
        }
    }
?>