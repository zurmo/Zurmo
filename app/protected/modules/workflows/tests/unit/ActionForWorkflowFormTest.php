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

    class ActionForWorkflowFormTest extends WorkflowBaseTest
    {
        public function testGetDisplayLabel()
        {
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $label = $form->getDisplayLabel();
            $this->assertEquals('Update', $label);

            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'hasMany2';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Update Related Workflows Tests', $label);

            //Test update a derived related model (this is like account's meetings)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'model5ViaItem';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Update Related WorkflowModelTestItem5s', $label);

            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_CREATE;
            $form->relation        = 'hasMany2';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Create Workflows Test', $label);

            //Test update a inferred related model (this is like a meeting's accounts)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem5', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'WorkflowModelTestItem__workflowItems__Inferred';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Update Related Workflows Tests', $label);

            //Test create a related, derived related model (this is like account's meetings)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type                  = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $form->relation              = 'hasMany2';
            $form->relatedModelRelation  = 'model5ViaItem';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Create Related Workflows Tests WorkflowModelTestItem5', $label);

            //Test create a related, inferred related model (this is like a meeting's accounts)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem7', Workflow::TYPE_ON_SAVE);
            $form->type                 = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $form->relation             ='model5';
            $form->relatedModelRelation = 'WorkflowModelTestItem__workflowItems__Inferred';
            $label = $form->getDisplayLabel();
            $this->assertEquals('Create Related WorkflowModelTestItem5s Workflows Test', $label);
        }

        /**
         * @depends testGetDisplayLabel
         */
        public function testSetAndGetActionForUpdateAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));

            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_SELF, $action->type);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForUpdateAction
         */
        public function testSetAndGetActionForUpdateRelatedAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'hasMany2';
            $action->relationFilter       = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $attributes                   = array(
                                            'string'     => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));

            $this->assertEquals(ActionForWorkflowForm::TYPE_UPDATE_RELATED,     $action->type);
            $this->assertEquals('hasMany2',                        $action->relation );
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL,     $action->relationFilter);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForUpdateRelatedAction
         */
        public function testSetAndGetActionForCreateAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasMany2';
            $attributes                   = array(
                                            'string'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));

            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE,     $action->type);
            $this->assertEquals('hasMany2',                $action->relation );
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('string') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('string')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('string')->value);
        }

        /**
         * @depends testSetAndGetActionForCreateAction
         */
        public function testSetAndGetActionForCreatingRelatedAction()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation             = 'hasMany2';
            $action->relationFilter       = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $action->relatedModelRelation = 'hasMany';
            $attributes                   = array(
                                            'name'        => array('shouldSetValue'    => '1',
                                                'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));

            $this->assertEquals(ActionForWorkflowForm::TYPE_CREATE_RELATED,     $action->type);
            $this->assertEquals('hasMany2',  $action->relation );
            $this->assertEquals(ActionForWorkflowForm::RELATION_FILTER_ALL,     $action->relationFilter);
            $this->assertEquals('hasMany',   $action->relatedModelRelation);
            $this->assertEquals(1, $action->getActionAttributeFormsCount());

            $this->assertTrue($action->getActionAttributeFormByName('name') instanceof TextWorkflowActionAttributeForm);
            $this->assertEquals('Static', $action->getActionAttributeFormByName('name')->type);
            $this->assertEquals('jason',  $action->getActionAttributeFormByName('name')->value);
        }

        /**
         * @depends testSetAndGetActionForCreatingRelatedAction
         */
        public function testValidate()
        {
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('type'  => array('Type cannot be blank.', 'Invalid Type'));
            $this->assertEquals($compareErrors, $errors);
            //Update type does not require any related information
            $action->type                         = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $validated                           = $action->validate();
            $this->assertTrue($validated);

            //When the type is update_related, related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->relationFilter              = 'somethingInvalid';
            $action->type                        = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relation'        => array('Relation cannot be blank.'),
                                                         'relationFilter'  => array('Invalid Relation Filter'));
            $this->assertEquals($compareErrors, $errors);
            $action->relation                    = 'hasMany2';
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated                           = $action->validate();
            $this->assertTrue($validated);

            //When the type is create, related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                        = ActionForWorkflowForm::TYPE_CREATE;
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relation'  => array('Relation cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $action->relation                    = 'hasMany2';
            $validated                           = $action->validate();
            $this->assertTrue($validated);

            //When the type is create related, additional related information is required
            $action                              = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                        = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation                    = 'hasMany2';
            $action->relationFilter              = ActionForWorkflowForm::RELATION_FILTER_ALL;
            $validated = $action->validate();
            $this->assertFalse($validated);
            $errors                              = $action->getErrors();
            $compareErrors                       = array('relatedModelRelation'  => array('Related Model Relation cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $action->relatedModelRelation        = 'hasOne';
            $validated                           = $action->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidate
         */
        public function testResolveAllActionAttributeFormsAndLabelsAndSort()
        {
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'hasMany2';
            $data = $form->resolveAllActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(41, count($data));
        }

        /**
         * @depends testResolveAllActionAttributeFormsAndLabelsAndSort
         */
        public function testResolveAllRequiredActionAttributeFormsAndLabelsAndSort()
        {
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_CREATE;
            $form->relation        = 'hasMany2';
            $data = $form->resolveAllRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(3, count($data));
        }

        /**
         * @depends testResolveAllRequiredActionAttributeFormsAndLabelsAndSort
         */
        public function testResolveAllNonRequiredActionAttributeFormsAndLabelsAndSort()
        {
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'hasMany2';
            $data = $form->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(38, count($data));

            //Test update a derived related model (this is like account's meetings)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'model5ViaItem';
            $data = $form->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(2, count($data));

            //Test update a inferred related model (this is like a meeting's accounts)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem5', Workflow::TYPE_ON_SAVE);
            $form->type            = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $form->relation        = 'WorkflowModelTestItem__workflowItems__Inferred';
            $data = $form->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(38, count($data));

            //Test create a related, derived related model (this is like account's meetings)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $form->type                  = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $form->relation              = 'hasMany2';
            $form->relatedModelRelation  = 'model5ViaItem';
            $data = $form->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(2, count($data));

            //Test create a related, inferred related model (this is like a meeting's accounts)
            $form = new ActionForWorkflowForm('WorkflowModelTestItem7', Workflow::TYPE_ON_SAVE);
            $form->type                 = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $form->relation             ='model5';
            $form->relatedModelRelation = 'WorkflowModelTestItem__workflowItems__Inferred';
            $data = $form->resolveAllNonRequiredActionAttributeFormsAndLabelsAndSort();
            $this->assertEquals(38, count($data));
        }
    }
?>