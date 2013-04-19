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

    class WorkflowTest extends WorkflowBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            UserTestHelper::createBasicUser('nobody');
            $somebody = UserTestHelper::createBasicUser('somebody');
            $somebody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $somebody->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            assert($somebody->save()); // Not Coding Standard
        }

        public function testGetTypeDropDownArray()
        {
            $dropDownArray = Workflow::getTypeDropDownArray();
            $this->assertCount(2, $dropDownArray);
        }

        /**
         * @depends testGetTypeDropDownArray
         */
        public function testGetWorkflowSupportedModulesAndLabelsForCurrentUser()
        {
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(6, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(0, $modulesAndLabels);
            Yii::app()->user->userModel = User::getByUsername('somebody');
            $modulesAndLabels = Workflow::getWorkflowSupportedModulesAndLabelsForCurrentUser();
            $this->assertCount(1, $modulesAndLabels);
        }

        /**
         * @depends testGetWorkflowSupportedModulesAndLabelsForCurrentUser
         */
        public function testGetWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo()
        {
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(6, $moduleClassNames);
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(0, $moduleClassNames);
            Yii::app()->user->userModel = User::getByUsername('somebody');
            $moduleClassNames = Workflow::getWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo();
            $this->assertCount(1, $moduleClassNames);
        }

        /**
         * @depends testGetWorkflowSupportedModulesClassNamesCurrentUserHasAccessTo
         */
        public function testSetAndGetWorkflow()
        {
            $timeTrigger = new TimeTriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action      = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $emailMessage  = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $trigger     = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $workflow = new Workflow();
            $workflow->setModuleClassName('SomeModule');
            $workflow->setDescription('a description');
            $workflow->setTriggersStructure('1 AND 2');
            $workflow->setTimeTriggerAttribute('something');
            $workflow->setId(5);
            $workflow->setIsActive(true);
            $workflow->setOrder(6);
            $workflow->setName('my workflow rule');
            $workflow->setTriggerOn(Workflow::TRIGGER_ON_NEW);
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setTimeTrigger($timeTrigger);
            $workflow->addTrigger($trigger);
            $workflow->addAction($action);
            $workflow->addEmailMessage($emailMessage);

            $this->assertEquals('SomeModule',             $workflow->getModuleClassName());
            $this->assertEquals('a description',          $workflow->getDescription());
            $this->assertEquals('1 AND 2',                $workflow->getTriggersStructure());
            $this->assertEquals('something',              $workflow->getTimeTriggerAttribute());
            $this->assertEquals(5,                        $workflow->getId());
            $this->assertTrue  ($workflow->getIsActive());
            $this->assertEquals(6,                        $workflow->getOrder());
            $this->assertEquals('my workflow rule',       $workflow->getName());
            $this->assertEquals(Workflow::TRIGGER_ON_NEW, $workflow->getTriggerOn());
            $this->assertEquals(Workflow::TYPE_ON_SAVE,   $workflow->getType());
            $this->assertEquals($timeTrigger,             $workflow->getTimeTrigger());
            $actions = $workflow->getActions();
            $this->assertEquals($action,                $actions[0]);
            $this->assertCount(1,                       $actions);
            $emailMessages = $workflow->getEmailMessages();
            $this->assertEquals($emailMessage,          $emailMessages[0]);
            $this->assertCount(1,                       $emailMessages);
            $triggers = $workflow->getTriggers();
            $this->assertEquals($trigger,               $triggers[0]);
            $this->assertCount(1,                       $triggers);

            $workflow->removeAllActions();
            $actions = $workflow->getActions();
            $this->assertCount(0,                       $actions);

            $workflow->removeAllEmailMessages();
            $emailMessages = $workflow->getEmailMessages();
            $this->assertCount(0,                       $emailMessages);

            $workflow->removeAllTriggers();
            $triggers = $workflow->getTriggers();
            $this->assertCount(0,                       $triggers);

            $workflow->removeTimeTrigger();
            $this->assertNull($workflow->getTimeTrigger());
        }
    }
?>