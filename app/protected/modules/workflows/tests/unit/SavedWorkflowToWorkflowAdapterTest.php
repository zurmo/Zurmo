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

    class SavedWorkflowToWorkflowAdapterTest extends WorkflowBaseTest
    {
        public function testResolveWorkflowToSavedWorkflow()
        {
            $workflow      = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (5);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1 and 2 or 3 or 4');

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'string';
            $trigger->value                       = 'aValue';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'currencyValue';
            $trigger->value                       = 'aValue';
            $trigger->secondValue                 = 'bValue';
            $trigger->operator                    = 'between';
            $trigger->currencyIdForValue          = '4';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'owner__User';
            $trigger->value                       = 'aValue';
            $trigger->stringifiedModelForValue    = 'someName';
            $workflow->addTrigger($trigger);

            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'createdDateTime';
            $trigger->value                       = 'aValue';
            $trigger->secondValue                 = 'bValue';
            $trigger->operator                    = null;
            $trigger->currencyIdForValue          = null;
            $trigger->valueType                   = 'Between';
            $workflow->addTrigger($trigger);

            $trigger = new TimeTriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'date';
            $trigger->durationSeconds             = 500;
            $trigger->valueType                   = 'Is Time For';
            $workflow->setTimeTrigger($trigger);

            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);

            $message       = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $message->sendAfterDurationSeconds = 86400;
            $message->emailTemplateId          = 5;
            $message->sendFromType             = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type' => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $workflow->addEmailMessage($message);

            $savedWorkflow = new SavedWorkflow();
            $this->assertNull($savedWorkflow->serializedData);
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);

            $this->assertEquals('WorkflowsTestModule',         $savedWorkflow->moduleClassName);
            $this->assertEquals('1', $savedWorkflow->isActive);
            $this->assertEquals('myFirstWorkflow',               $savedWorkflow->name);
            $this->assertEquals('aDescription',                $savedWorkflow->description);
            $this->assertEquals(5,                             $savedWorkflow->order);
            $this->assertEquals(Workflow::TRIGGER_ON_NEW,      $savedWorkflow->triggerOn);
            $this->assertEquals(Workflow::TYPE_ON_SAVE,        $savedWorkflow->type);
            $this->assertEquals('1 and 2 or 3 or 4',           $workflow->getTriggersStructure());
            $compareData = array('Triggers' => array(
                array(
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'string',
                    'operator'                     => 'equals',
                    'relationFilter'               => TriggerForWorkflowForm::RELATION_FILTER_ANY
                ),
                array(
                    'currencyIdForValue'           => '4',
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'currencyValue',
                    'operator'                     => 'between',
                    'relationFilter'               => TriggerForWorkflowForm::RELATION_FILTER_ANY
                ),
                array(
                    'currencyIdForValue'           => null,
                    'value'                        => 'aValue',
                    'secondValue'                  => null,
                    'stringifiedModelForValue'     => 'someName',
                    'valueType'                    => null,
                    'attributeIndexOrDerivedType'  => 'owner__User',
                    'operator'                     => null,
                    'relationFilter'               => TriggerForWorkflowForm::RELATION_FILTER_ANY
                ),
                array(
                    'value'                        => 'aValue',
                    'secondValue'                  => 'bValue',
                    'stringifiedModelForValue'     => null,
                    'valueType'                    => 'Between',
                    'attributeIndexOrDerivedType'  => 'createdDateTime',
                    'operator'                     => null,
                    'currencyIdForValue'           => null,
                    'relationFilter'               => TriggerForWorkflowForm::RELATION_FILTER_ANY
                ),
            ));
            $compareData['Actions'] = array(array('type'           => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                                  'relation'       => null,
                                                  'relationFilter' => ActionForWorkflowForm::RELATION_FILTER_ALL,
                                                  'relatedModelRelation' => null,
                                                  'ActionAttributes' => array(
                                                       'string' => array(
                                                           'type'           => 'Static',
                                                           'value'          => 'jason',
                                                           'shouldSetValue' => 1,
                                                       ),
                                                  )));
            $compareData['EmailMessages'] = array(array('emailTemplateId' => 5,
                                                         'sendAfterDurationSeconds' => 86400,
                                                         'sendFromType' => 'Default',
                                                         'sendFromName' => null,
                                                         'sendFromAddress' => null,
                                                         'EmailMessageRecipients' =>
                                                            array(array(
                                                                'dynamicUserType' => 'CreatedByUser',
                                                                'type' => 'DynamicTriggeredModelUser',
                                                                'audienceType' => 1,
                                                            ))));
            $compareData['TimeTrigger'] = array('durationSeconds' => 500,
                                                'currencyIdForValue' => null,
                                                'value'              => null,
                                                'secondValue'        => null,
                                                'valueType'          => 'Is Time For',
                                                'relationFilter'     => 'RelationFilterAny',
                                                'attributeIndexOrDerivedType' => 'date',
                                                'operator' => null);
            $unserializedData = unserialize($savedWorkflow->serializedData);
            $this->assertEquals($compareData['Triggers'],                    $unserializedData['Triggers']);
            $this->assertEquals($compareData['Actions'],                     $unserializedData['Actions']);
            $this->assertEquals($compareData['EmailMessages'],               $unserializedData['EmailMessages']);
            $this->assertEquals($compareData['TimeTrigger'],                 $unserializedData['TimeTrigger']);
            $this->assertEquals('1 and 2 or 3 or 4',                         $unserializedData['triggersStructure']);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testResolveWorkflowToSavedWorkflow
         */
        public function testMakeWorkflowBySavedWorkflow()
        {
            $savedWorkflows               = SavedWorkflow::getAll();
            $this->assertEquals           (1, count($savedWorkflows));
            $savedWorkflow                = $savedWorkflows[0];
            $workflow                     = SavedWorkflowToWorkflowAdapter::makeWorkflowBySavedWorkflow($savedWorkflow);
            $triggers                    = $workflow->getTriggers();
            $this->assertEquals           ('WorkflowsTestModule',         $workflow->getModuleClassName());
            $this->assertEquals           ('myFirstWorkflow',               $workflow->getName());
            $this->assertEquals           ('aDescription',                $workflow->getDescription());
            $this->assertTrue             ($workflow->getIsActive());
            $this->assertEquals           (5,                             $workflow->getOrder());
            $this->assertEquals           (Workflow::TRIGGER_ON_NEW,      $workflow->getTriggerOn());
            $this->assertEquals           (Workflow::TYPE_ON_SAVE,        $workflow->getType());
            $this->assertEquals           ('1 and 2 or 3 or 4',           $workflow->getTriggersStructure());
            $this->assertCount            (4, $triggers);
        }
    }
?>