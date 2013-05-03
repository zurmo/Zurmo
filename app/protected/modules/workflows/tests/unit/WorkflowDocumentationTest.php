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

    class WorkflowDocumentationTest extends WorkflowBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist();
            ContactsModule::loadStartingData();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateARelatedContactOnAnOpportunityWhenOpportunityBecomesClosedWon()
        {
            $super = User::getByUsername('super');
            $contactStates = ContactState::getAll();
            //Create workflow
            $workflow = new Workflow();
            $workflow->setDescription    ('aDescription');
            $workflow->setIsActive       (true);
            $workflow->setOrder          (1);
            $workflow->setModuleClassName('OpportunitiesModule');
            $workflow->setName           ('myFirstWorkflow');
            $workflow->setTriggerOn      (Workflow::TRIGGER_ON_NEW_AND_EXISTING);
            $workflow->setType           (Workflow::TYPE_ON_SAVE);
            $workflow->setTriggersStructure('1');
            //Add Trigger
            $trigger     = new TriggerForWorkflowForm('OpportunitiesModule', 'Opportunity', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'stage';
            $trigger->value                       = 'Prospecting';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);
            //Add action
            $action                       = new ActionForWorkflowForm('Opportunity', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'contacts';
            $attributes                   = array(  'lastName' => array('shouldSetValue'    => '1',
                                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'  => 'smith'),
                                                    'firstName' => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => 'john'),
                                                    'owner__User'     => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => Yii::app()->user->userModel->id),
                                                    'state'       => array('shouldSetValue'    => '1',
                                                        'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'  => $contactStates[0]->id),
            );
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $workflow->addAction($action);
            //Create the saved Workflow
            $savedWorkflow = new SavedWorkflow();
            SavedWorkflowToWorkflowAdapter::resolveWorkflowToSavedWorkflow($workflow, $savedWorkflow);
            $saved = $savedWorkflow->save();
            $this->assertTrue($saved);

            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('some opp', $super);
            $this->assertTrue($opportunity->id > 0);
            $this->assertEquals(0, $opportunity->contacts->count());
            //Change opportunity to  Prospecting
            $opportunity->stage->value = 'Prospecting';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $opportunity));
            $saved = $opportunity->save();
            $this->assertTrue($saved);
            $this->assertEquals(1,       $opportunity->contacts->count());
            $this->assertEquals('smith', $opportunity->contacts[0]->lastName);
        }

        public function testAWorkflowProcess()
        {
            //todo: write a full set of tests to document workflow
        }
    }
?>