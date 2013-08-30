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

    class WorkflowEmailMessagesUtilTest extends WorkflowBaseTest
    {
        public $freeze = false;

        protected static $savedWorkflow;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            $super = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            assert($super->save()); // Not Coding Standard
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow->save();
            assert($saved); // Not Coding Standard
            self::$savedWorkflow = $savedWorkflow;
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

        public function testProcessAfterSaveWhenSendIsInFuture()
        {
            $this->assertEquals(0, count(WorkflowMessageInQueue::getAll()));
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationInterval = 1;
            $emailMessageForm->sendAfterDurationType     = TimeDurationUtil::DURATION_TYPE_DAY;
            $recipients = array(array('type'              => WorkflowEmailMessageRecipientForm::
                                                             TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                       'audienceType'    => EmailMessageRecipient::TYPE_TO,
                                       'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                                             DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $emailMessageForm->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $workflow->addEmailMessage($emailMessageForm);
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            $compareDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 86400);
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $workflowMessageInQueues = WorkflowMessageInQueue::getAll();
            $this->assertEquals(1, count($workflowMessageInQueues));
            $this->assertTrue($workflowMessageInQueues[0]->savedWorkflow->isSame(self::$savedWorkflow));
            $this->assertTrue($workflowMessageInQueues[0]->triggeredByUser->isSame(Yii::app()->user->userModel));
            $this->assertEquals($model->getClassId('Item'), $workflowMessageInQueues[0]->modelItem->getClassId('Item'));
            $this->assertEquals('WorkflowModelTestItem',    $workflowMessageInQueues[0]->modelClassName);
            $this->assertEquals($compareDateTime,           $workflowMessageInQueues[0]->processDateTime);
            $emailMessageData = SavedWorkflowToWorkflowAdapter::
                                makeArrayFromEmailMessageForWorkflowFormAttributesData(array($emailMessageForm));
            $this->assertEquals(serialize($emailMessageData), $workflowMessageInQueues[0]->serializedData);
            $this->assertTrue($workflowMessageInQueues[0]->delete());
        }

        /**
         * @depends testProcessAfterSaveWhenSendIsInFuture
         */
        public function testProcessAfterSaveWhenSendIsImmediate()
        {
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->name           = 'the name';
            $emailTemplate->modelClassName = 'Account';
            $emailTemplate->textContent    = 'some content';
            $emailTemplate->type           = 2;
            $emailTemplate->subject        = 'subject';
            $saved                         = $emailTemplate->save();
            $this->assertTrue($saved);
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationInterval = 0;
            $emailMessageForm->sendAfterDurationType     = TimeDurationUtil::DURATION_TYPE_WEEK;
            $emailMessageForm->emailTemplateId = $emailTemplate->id;
            $emailMessageForm->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::
                                                            TYPE_DYNAMIC_TRIGGERED_BY_USER,
                                      'audienceType'    => EmailMessageRecipient::TYPE_TO));
            $emailMessageForm->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $workflow->addEmailMessage($emailMessageForm);
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }

        public function testProcessAfterSaveWhenSendIsImmediateAndToAContactThatIsTheTriggeredModel()
        {
            foreach (EmailMessage::getAll() as $emailMessage)
            {
                $emailMessage->delete();
            }
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->name           = 'the name';
            $emailTemplate->modelClassName = 'Account';
            $emailTemplate->textContent    = 'some content';
            $emailTemplate->type           = 2;
            $emailTemplate->subject        = 'subject';
            $saved                         = $emailTemplate->save();
            $this->assertTrue($saved);
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailMessageForWorkflowForm('Contact', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationInterval = 0;
            $emailMessageForm->sendAfterDurationType     = TimeDurationUtil::DURATION_TYPE_WEEK;
            $emailMessageForm->emailTemplateId = $emailTemplate->id;
            $emailMessageForm->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::
                                                            TYPE_DYNAMIC_TRIGGERED_MODEL,
                                      'audienceType'    => EmailMessageRecipient::TYPE_TO));
            $emailMessageForm->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $workflow->addEmailMessage($emailMessageForm);
            $model             = new Contact();
            $model->firstName  = 'Jason';
            $model->lastName   = 'Blue';
            $model->state      = ContactsUtil::getStartingState();
            $model->primaryEmail->emailAddress = 'jason@something.com';
            $this->assertTrue($model->save());
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(1, count($queuedEmailMessages));
            $this->assertEquals(1, count($queuedEmailMessages[0]->recipients));
            $this->assertEquals('Jason Blue' ,           $queuedEmailMessages[0]->recipients[0]->toName);
            $this->assertEquals('jason@something.com',   $queuedEmailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals($model->id,              $queuedEmailMessages[0]->recipients[0]->personOrAccount->id);
        }

        public function testProcessAfterSaveWhenSendIsImmediateAndToAContactThatIsRelatedToTheTriggeredModel()
        {
            foreach (EmailMessage::getAll() as $emailMessage)
            {
                $emailMessage->delete();
            }
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->name           = 'the name';
            $emailTemplate->modelClassName = 'Account';
            $emailTemplate->textContent    = 'some content';
            $emailTemplate->type           = 2;
            $emailTemplate->subject        = 'subject';
            $saved                         = $emailTemplate->save();
            $this->assertTrue($saved);
            $workflow         = new Workflow();
            $workflow->setId(self::$savedWorkflow->id);
            $workflow->type   = Workflow::TYPE_ON_SAVE;
            $emailMessageForm = new EmailMessageForWorkflowForm('Account', Workflow::TYPE_ON_SAVE);
            $emailMessageForm->sendAfterDurationInterval = 0;
            $emailMessageForm->sendAfterDurationType     = TimeDurationUtil::DURATION_TYPE_WEEK;
            $emailMessageForm->emailTemplateId = $emailTemplate->id;
            $emailMessageForm->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::
                                                            TYPE_DYNAMIC_TRIGGERED_MODEL_RELATION,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'relation'         => 'contacts'));
            $emailMessageForm->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $workflow->addEmailMessage($emailMessageForm);
            $model               = new Account();
            $model->name         = 'the account';
            $contact             = new Contact();
            $contact->firstName  = 'Jason';
            $contact->lastName   = 'Blue';
            $contact->state      = ContactsUtil::getStartingState();
            $contact->primaryEmail->emailAddress = 'jason@something.com';
            $this->assertTrue($contact->save());
            $contact2            = new Contact();
            $contact2->firstName = 'Laura';
            $contact2->lastName  = 'Blue';
            $contact2->state     = ContactsUtil::getStartingState();
            $contact2->primaryEmail->emailAddress = 'laura@something.com';
            $this->assertTrue($contact2->save());
            $model->contacts->add($contact);
            $model->contacts->add($contact2);
            $this->assertTrue($model->save());
            WorkflowEmailMessagesUtil::processAfterSave($workflow, $model, Yii::app()->user->userModel);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            $queuedEmailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(1, count($queuedEmailMessages));
            $this->assertEquals(2, count($queuedEmailMessages[0]->recipients));
            $this->assertEquals('Jason Blue' ,           $queuedEmailMessages[0]->recipients[0]->toName);
            $this->assertEquals('jason@something.com',   $queuedEmailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals($contact->id,            $queuedEmailMessages[0]->recipients[0]->personOrAccount->id);
            $this->assertEquals('Laura Blue' ,           $queuedEmailMessages[0]->recipients[1]->toName);
            $this->assertEquals('laura@something.com',   $queuedEmailMessages[0]->recipients[1]->toAddress);
            $this->assertEquals($contact2->id,           $queuedEmailMessages[0]->recipients[1]->personOrAccount->id);
        }

        public function testMakeEmailMessageForWorkflowFormByQueueModelAndWorkflow()
        {
            $model        = ContactTestHelper::createContactByNameForOwner('Jason', Yii::app()->user->userModel);
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'WorkflowsTestModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->order           = 1;
            $savedWorkflow->serializedData  = serialize(array('something'));
            $savedWorkflow->isActive        = true;
            $saved                          = $savedWorkflow->save();
            $this->assertTrue($saved);
            $emailMessage = null;
            $emailMessage['emailTemplateId'] = 5;
            $emailMessage['sendFromType']    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $emailMessage['sendAfterDurationType']    = TimeDurationUtil::DURATION_TYPE_DAY;
            $emailMessage['sendAfterDurationInterval'] = '44';
            $emailMessage[EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS] =
                                array(
                                    array('type'          => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL,
                                        'audienceType'    => EmailMessageRecipient::TYPE_TO),
                                );
            $workflowMessageInQueue = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($model, $savedWorkflow, serialize(array($emailMessage)));
            $workflow               = new Workflow();
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $emailMessageForWorkflowForm = WorkflowEmailMessagesUtil::
                                                makeEmailMessageForWorkflowFormByQueueModelAndWorkflow(
                                                $workflowMessageInQueue, $workflow);
            $this->assertEquals(5, $emailMessageForWorkflowForm->emailTemplateId);
            $this->assertEquals(EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT, $emailMessageForWorkflowForm->sendFromType);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_DAY, $emailMessageForWorkflowForm->sendAfterDurationType);
            $this->assertEquals(44, $emailMessageForWorkflowForm->sendAfterDurationInterval);
            $this->assertEquals(1, count($emailMessageForWorkflowForm->getEmailMessageRecipients()));
        }
    }
?>