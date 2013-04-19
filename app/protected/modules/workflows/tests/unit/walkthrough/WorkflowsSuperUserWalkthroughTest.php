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

    /**
     * Workflows module walkthrough tests for super users.
     */
    class WorkflowsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
        }

        public function setUp()
        {
            parent::setUp();
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $this->runControllerWithNoExceptionsAndGetContent      ('workflows/default/list');
            $this->runControllerWithExitExceptionAndGetContent     ('workflows/default/create');
            $this->runControllerWithNoExceptionsAndGetContent      ('workflows/default/selectType');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testCreateAction()
        {
            $this->assertEquals(0, count(SavedWorkflow::getAll()));
            $content = $this->runControllerWithExitExceptionAndGetContent     ('workflows/default/create');
            $this->assertFalse(strpos($content, 'On-Save Workflow') === false);
            $this->assertFalse(strpos($content, 'Time-Based Workflow') === false);

            $this->setGetArray(array('type' => 'OnSave'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent     ('workflows/default/create');
            $this->assertFalse(strpos($content, 'Accounts') === false);

            $this->setGetArray(array('type' => 'OnSave'));
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array('description'       => 'someDescription',
                                                      'isActive'          => '0',
                                                      'name'              => 'someName',
                                                      'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                      'triggersStructure' => '1 AND 2',
                                                      'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $this->runControllerWithExitExceptionAndGetContent     ('workflows/default/save');
            $savedWorkflows = SavedWorkflow::getAll();
            $this->assertEquals(1, count($savedWorkflows));
            $this->setGetArray(array('id' => $savedWorkflows[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('workflows/default/details');
        }

        /**
         * @depends testCreateAction
         */
        public function testEditAction()
        {
            $savedWorkflows = SavedWorkflow::getAll();
            $this->setGetArray(array('id' => $savedWorkflows[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('workflows/default/edit');
            //Save existing workflow
            $this->setGetArray(array('type' => 'OnSave', 'id' => $savedWorkflows[0]->id));
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array('description'       => 'someDescription3',
                'isActive'          => '0',
                'name'              => 'someName',
                'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                'triggersStructure' => '1 AND 2',
                'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $this->runControllerWithExitExceptionAndGetContent('workflows/default/save');
            $savedWorkflows = SavedWorkflow::getAll();
            $this->assertEquals(1, count($savedWorkflows));
        }

        /**
         * @depends testEditAction
         */
        public function testManageOrder()
        {
            //Create a second workflow
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow2';
            $savedWorkflow->description     = 'description2';
            $savedWorkflow->moduleClassName = 'WorkflowsTestModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_ON_SAVE;
            $savedWorkflow->serializedData  = serialize(array('something'));
            $savedWorkflow->isActive        = true;
            $savedWorkflow->order           = 2;
            $saved                          = $savedWorkflow->save();
            $this->assertTrue($saved);

            //Go to manage order
            $this->resetGetArray();
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('workflows/default/manageOrder');
            //loadOrderByModule - where none exist
            $this->setGetArray(array('moduleClassName' => 'OpportunitiesModule'));
            $content = $this->runControllerWithExitExceptionAndGetContent('workflows/default/loadOrderByModule');
            $this->assertEquals('{"dataToOrder":"false"}', $content);

            //load orderByModule - where at least one exists
            $this->setGetArray(array('moduleClassName' => 'WorkflowsTestModule'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/loadOrderByModule');
            $this->assertNotEquals('{"dataToOrder":"false"}', $content);

            //saveOrder, switching order
            $savedWorkflows = SavedWorkflow::getAll('order');
            $this->assertEquals(2, count($savedWorkflows));
            $firstOrder  = $savedWorkflows[0]->order;
            $secondOrder = $savedWorkflows[1]->order;
            $this->resetGetArray();
            $this->setPostArray(array('SavedWorkflow' =>
                                    array('moduleClassName' => 'WorkflowsTestModule',
                                          'savedWorkflowIds' => array($savedWorkflows[1]->id, $savedWorkflows[0]->id)
            )));
            $content = $this->runControllerWithExitExceptionAndGetContent('workflows/default/saveOrder');
            $this->assertEquals('{"message":"Order saved successfully.","type":"message"}', $content); // Not Coding Standard
            $this->assertEquals($firstOrder,  SavedWorkflow::getById($savedWorkflows[1]->id)->order);
            $this->assertEquals($secondOrder, SavedWorkflow::getById($savedWorkflows[0]->id)->order);
        }

        /**
         * @depends testManageOrder
         */
        public function testActionRelationsAndAttributesTree()
        {
            $this->setGetArray(array('type'     => 'OnSave', 'treeType' => ComponentForWorkflowForm::TYPE_TRIGGERS));
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array(  'description'       => 'someDescription',
                                                        'isActive'          => '0',
                                                        'name'              => 'someName',
                                                        'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                        'triggersStructure' => '1 AND 2',
                                                        'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/relationsAndAttributesTree');
            $this->assertTrue(strpos($content, '<div class="WorkflowRelationsAndAttributesTreeView') !== false);
            //With node id
            $this->setGetArray(array('type'     => 'OnSave',
                                     'treeType' => ComponentForWorkflowForm::TYPE_TRIGGERS,
                                     'nodeId'   => 'Triggers_hasOne'));
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array(  'description'       => 'someDescription',
                                                        'isActive'          => '0',
                                                        'name'              => 'someName',
                                                        'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                        'triggersStructure' => '1 AND 2',
                                                        'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $content = $this->runControllerWithExitExceptionAndGetContent('workflows/default/relationsAndAttributesTree');
            $this->assertTrue(strpos($content, '{"id":"Triggers_hasOne___createdByUser__User",') !== false); // Not Coding Standard
        }

        /**
         * @depends testActionRelationsAndAttributesTree
         */
        public function testActionAddAttributeFromTree()
        {
            $this->setGetArray(array('type'      => 'OnSave',
                                     'treeType'  => ComponentForWorkflowForm::TYPE_TRIGGERS,
                                     'nodeId'    => 'Triggers_phone',
                                     'rowNumber' => 4));
            $data   = array();
            $data['OnSaveWorkflowWizardForm'] = array(  'description'       => 'someDescription',
                                                        'isActive'          => '0',
                                                        'name'              => 'someName',
                                                        'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                        'triggersStructure' => '1 AND 2',
                                                        'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/addAttributeFromTree');
            $this->assertTrue(strpos($content, '<option value="equals">Equals</option>') !== false);
        }

        /**
         * @depends testActionAddAttributeFromTree
         */
        public function testActionDelete()
        {
            $savedWorkflows = SavedWorkflow::getAll();
            $this->assertEquals(2, count($savedWorkflows));
            $this->setGetArray(array('id' => $savedWorkflows[0]->id));
            $this->runControllerWithRedirectExceptionAndGetContent('workflows/default/delete');
            $savedWorkflows = SavedWorkflow::getAll();
            $this->assertEquals(1, count($savedWorkflows));
        }

        /**
         * @depends testActionDelete
         */
        public function testActionGetAvailableAttributesForTimeTrigger()
        {
            $this->setGetArray(array('type'      => 'ByTime'));
            $data   = array();
            $data['ByTimeWorkflowWizardForm'] = array(  'description'       => 'someDescription',
                                                        'isActive'          => '0',
                                                        'name'              => 'someName',
                                                        'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                        'triggersStructure' => '1 AND 2',
                                                        'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/getAvailableAttributesForTimeTrigger');
            $this->assertTrue(strpos($content, '{"":"(None)","likeContactState":"A name for a state","boolean":"Boolean",') !== false); // Not Coding Standard
        }

        /**
         * @depends testActionGetAvailableAttributesForTimeTrigger
         */
        public function testActionAddOrChangeTimeTriggerAttribute()
        {
            $this->setGetArray(array('type'            => 'ByTime', 'attributeIndexOrDerivedType' => 'phone',
                                     'moduleClassName' => 'WorkflowsTestModule'));
            $data   = array();
            $data['ByTimeWorkflowWizardForm'] = array(  'description'       => 'someDescription',
                                                        'isActive'          => '0',
                                                        'name'              => 'someName',
                                                        'triggerOn'         => Workflow::TRIGGER_ON_NEW,
                                                        'triggersStructure' => '1 AND 2',
                                                        'moduleClassName'   => 'WorkflowsTestModule');
            $this->setPostArray($data);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/addOrChangeTimeTriggerAttribute');
            $this->assertTrue(strpos($content, '<option value="14400">for 4 hours</option>') !== false);
        }

        /**
         * @depends testActionAddOrChangeTimeTriggerAttribute
         */
        public function testActionChangeActionType()
        {
            $this->setGetArray(array('type' => 'OnSave', 'moduleClassName' => 'WorkflowsTestModule'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/changeActionType');
            $this->assertTrue(strpos($content, '<div class="hasDropDown"><span class="select-arrow"></span><select name="actionTypeRelatedModel"') !== false);
        }

        /**
         * @depends testActionChangeActionType
         */
        public function testActionChangeActionTypeRelatedModel()
        {
            $this->setGetArray(array('type' => 'OnSave', 'moduleClassName' => 'WorkflowsTestModule', 'relation' => 'hasOne'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/changeActionTypeRelatedModel');
            $this->assertTrue(strpos($content, '<option value="hasMany2">Has Many 2</option>') !== false);
        }

        /**
         * @depends testActionChangeActionTypeRelatedModel
         */
        public function testActionAddAction()
        {
            $this->setGetArray(array('type' => 'OnSave', 'moduleClassName' => 'WorkflowsTestModule',
                                     'actionType' => ActionForWorkflowForm::TYPE_CREATE,
                                     'rowNumber'  => 4,
                                     'relation'   => 'hasOne'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/addAction');
            $this->assertTrue(strpos($content, '<li id="OnSaveWorkflowWizardForm_Actions_4"') !== false);
        }

        /**
         * @depends testActionAddAction
         */
        public function testActionAddEmailMessage()
        {
            $this->setGetArray(array('type' => 'OnSave',
                                     'moduleClassName' => 'WorkflowsTestModule',
                                     'rowNumber'  => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/addEmailMessage');
            $this->assertTrue(strpos($content, '<option value="0">Immediately after workflow runs</option>') !== false);
        }

        /**
         * @depends testActionAddEmailMessage
         */
        public function testActionAddEmailMessageRecipient()
        {
            $this->setGetArray(array('type'               => 'OnSave',
                                     'moduleClassName'    => 'WorkflowsTestModule',
                                     'rowNumber'          => 4,
                                     'recipientType'      => WorkflowEmailMessageRecipientForm::TYPE_STATIC_USER,
                                     'recipientRowNumber' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/addEmailMessageRecipient');
            $this->assertTrue(strpos($content, '<li class="dynamic-sub-row"><div class="dynamic-sub-row') !== false);
        }

        /**
         * @depends testActionAddEmailMessageRecipient
         */
        public function testTimeQueueController()
        {
            $this->runControllerWithNoExceptionsAndGetContent      ('workflows/defaultTimeQueue/index');
            $this->runControllerWithNoExceptionsAndGetContent     ('workflows/defaultTimeQueue/list');
        }

        /**
         * @depends testTimeQueueController
         */
        public function testMessageQueueController()
        {
            $this->runControllerWithNoExceptionsAndGetContent      ('workflows/defaultMessageQueue/index');
            $this->runControllerWithNoExceptionsAndGetContent     ('workflows/defaultMessageQueue/list');
        }
    }
?>