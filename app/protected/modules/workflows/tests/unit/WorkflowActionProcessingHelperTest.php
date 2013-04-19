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

    class WorkflowActionProcessingHelperTest extends WorkflowBaseTest
    {
        public $freeze = false;

        protected static $superUserId;

        protected static $bobbyUserId;

        protected static $sarahUserId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = User::getByUsername('super');
            $bobby = UserTestHelper::createBasicUser('bobby');
            $sarah = UserTestHelper::createBasicUser('sarah');
            self::$superUserId = $super->id;
            self::$bobbyUserId = $bobby->id;
            self::$sarahUserId = $sarah->id;
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

        public function testUpdateSelf()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_SELF;
            $attributes                   = array('string' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem();
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processUpdateSelectAction();
            $this->assertEquals('jason', $model->string);
            $this->assertTrue($model->id < 0);
        }

        /**
         * @depends testUpdateSelf
         */
        public function testUpdateRelatedHasOneNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'hasOne';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new better name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem();
            $model->hasOne       = new WorkflowModelTestItem2();
            $model->hasOne->name = 'some old name';
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new better name', $model->hasOne->name);
            $this->assertTrue($model->id < 0);
            $this->assertTrue($model->hasOne->id > 0);
        }

        /**
         * @depends testUpdateRelatedHasOneNonOwned
         */
        public function testUpdateRelatedHasManyNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'hasMany';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new better name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem();
            $relatedModel = new WorkflowModelTestItem3();
            $relatedModel->name = 'some old name';
            $relatedModel2 = new WorkflowModelTestItem3();
            $relatedModel2->name = 'some old name 2';
            $model->hasMany->add($relatedModel);
            $model->hasMany->add($relatedModel2);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new better name', $model->hasMany[0]->name);
            $this->assertEquals('some new better name', $model->hasMany[1]->name);
            $this->assertTrue($model->id < 0);
            $this->assertTrue($model->hasMany[0]->id > 0);
            $this->assertTrue($model->hasMany[1]->id > 0);
        }

        /**
         * @depends testUpdateRelatedHasManyNonOwned
         */
        public function testUpdateRelatedManyManyNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'hasMany3';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new better name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem2();
            $relatedModel = new WorkflowModelTestItem3();
            $relatedModel->name = 'some old name';
            $relatedModel2 = new WorkflowModelTestItem3();
            $relatedModel2->name = 'some old name 2';
            $model->hasMany3->add($relatedModel);
            $model->hasMany3->add($relatedModel2);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new better name', $model->hasMany3[0]->name);
            $this->assertEquals('some new better name', $model->hasMany3[1]->name);
            $this->assertTrue($model->id < 0);
            $this->assertTrue($model->hasMany3[0]->id > 0);
            $this->assertTrue($model->hasMany3[1]->id > 0);
        }

        /**
         * @depends testUpdateRelatedManyManyNonOwned
         */
        public function testCreateHasOneNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasOne';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new model'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->hasOne->id < 0);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new model', $model->hasOne->name);
            $this->assertTrue($model->id > 0);
            $this->assertTrue($model->hasOne->id > 0);
        }

        /**
         * @depends testCreateHasOneNonOwned
         */
        public function testCreateHasManyNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasMany';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new better name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $relatedModel = new WorkflowModelTestItem3();
            $relatedModel->name = 'some old name';
            $model->hasMany->add($relatedModel);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some old name', $model->hasMany[0]->name);
            $this->assertEquals('some new better name', $model->hasMany[1]->name);
            $this->assertTrue($model->id > 0);
            $this->assertEquals(2, $model->hasMany->count());
            $this->assertTrue($model->hasMany[0]->id > 0);
            $this->assertTrue($model->hasMany[1]->id > 0);
        }

        /**
         * @depends testCreateHasManyNonOwned
         */
        public function testCreateManyManyNonOwned()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'hasMany3';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'some new better name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem2();
            $relatedModel = new WorkflowModelTestItem3();
            $relatedModel->name = 'some old name';
            $model->hasMany3->add($relatedModel);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some old name', $model->hasMany3[0]->name);
            $this->assertEquals('some new better name', $model->hasMany3[1]->name);
            $this->assertTrue($model->id > 0);
            $this->assertTrue($model->hasMany3[0]->id > 0);
            $this->assertTrue($model->hasMany3[1]->id > 0);
        }

        /**
         * @depends testCreateManyManyNonOwned
         */
        public function testCreateRelatedHasOnesHasOneNonOwned()
        {
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem9', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'hasOne';
            $action->relatedModelRelation   = 'hasOne';
            $attributes                     = array('name'   => array('shouldSetValue'    => '1',
                                                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'  => 'some new model 2'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem9();
            $relatedModel = new WorkflowModelTestItem();
            $relatedModel->lastName = 'lastName';
            $relatedModel->string   = 'string';
            $model->hasOne = $relatedModel;
            $this->assertTrue($model->hasOne->id < 0);
            $this->assertTrue($model->hasOne->hasOne->id < 0);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new model 2', $model->hasOne->hasOne->name);
            $this->assertTrue($model->id < 0);
            $this->assertTrue($model->hasOne->id > 0);
            $this->assertTrue($model->hasOne->hasOne->id > 0);
        }

        /**
         * @depends testCreateRelatedHasOnesHasOneNonOwned
         */
        public function testCreateRelatedHasManysHasManyNonOwned()
        {
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem2', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'hasMany2';
            $action->relatedModelRelation   = 'hasMany';
            $attributes                     = array('name'   => array('shouldSetValue'    => '1',
                                                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'  => 'some new model'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem2();
            $relatedModel = new WorkflowModelTestItem();
            $relatedModel->lastName = 'lastName';
            $relatedModel->string   = 'string';
            $model->hasMany2->add($relatedModel);
            $this->assertTrue($model->hasMany2->count() == 1);
            $this->assertTrue($model->hasMany2[0]->id < 0);
            $this->assertTrue($model->hasMany2[0]->hasMany->count() == 0);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals('some new model', $model->hasMany2[0]->hasMany[0]->name);
            $this->assertTrue($model->id < 0);
            $this->assertTrue($model->hasMany2->count() == 1);
            $this->assertTrue($model->hasMany2[0]->id > 0);
            $this->assertTrue($model->hasMany2[0]->hasMany->count() == 1);
            $this->assertTrue($model->hasMany2[0]->hasMany[0]->id > 0);
        }

        /**
         * @depends testCreateRelatedHasManysHasManyNonOwned
         * Similar to an account updating all related tasks.
         */
        public function testUpdateRelatedDerived()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'model5ViaItem';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'a new derived name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $saved           = $model->save();
            $this->assertTrue($saved);
            $derivedModel = new WorkflowModelTestItem5();
            $derivedModel->name = 'some old name';
            $derivedModel->workflowItems->add($model);
            $saved = $derivedModel->save();
            $this->assertTrue($saved);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $derivedModelId = $derivedModel->id;
            $derivedModel->forget();
            $derivedModel   = WorkflowModelTestItem5::getById($derivedModelId);
            $this->assertEquals('a new derived name', $derivedModel->name);

            //Test where there are no derived models
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertEquals(1, count(WorkflowModelTestItem5::getAll()));
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $derivedModels = WorkflowModelTestItem5::getAll();
            $this->assertEquals(1, count($derivedModels));
            $deleted = $derivedModels[0]->delete();
            $this->assertTrue($deleted);
        }

        /**
         * @depends testUpdateRelatedDerived
         * Similar to an account updating all related tasks.
         */
        public function testCreateDerived()
        {
            $this->assertEquals(0, count(WorkflowModelTestItem5::getAll()));
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'model5ViaItem';
            $attributes                   = array('name'   => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'a new derived name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $saved           = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $derivedModels = WorkflowModelTestItem5::getAll();
            $this->assertEquals(1, count($derivedModels));
            $this->assertEquals('a new derived name', $derivedModels[0]->name);
            $this->assertEquals(1, $derivedModels[0]->workflowItems->count());
            $this->assertEquals($derivedModels[0]->workflowItems[0]->getClassId('Item'), $model->getClassId('Item'));
            $deleted = $derivedModels[0]->delete();
            $this->assertTrue($deleted);
        }

        /**
         * @depends testCreateDerived
         * Similar to an account updating all related tasks.
         */
        public function testCreateRelatedHasOnesDerivedNonOwned()
        {
            $this->assertEquals(0, count(WorkflowModelTestItem5::getAll()));
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem9', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'hasOne';
            $action->relatedModelRelation   = 'model5ViaItem';
            $attributes                     = array('name'   => array('shouldSetValue'    => '1',
                                              'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                              'value'  => 'some new model 2'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem9();
            $saved = $model->save();
            $this->assertTrue($saved);
            $relatedModel = new WorkflowModelTestItem();
            $relatedModel->lastName = 'lastName';
            $relatedModel->string   = 'string';
            $saved = $relatedModel->save();
            $this->assertTrue($saved);
            $model->hasOne          = $relatedModel;
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $derivedModels = WorkflowModelTestItem5::getAll();
            $this->assertEquals(1, count($derivedModels));
            $this->assertEquals('some new model 2', $derivedModels[0]->name);
            $this->assertEquals(1, $derivedModels[0]->workflowItems->count());
            $this->assertEquals($derivedModels[0]->workflowItems[0]->getClassId('Item'), $relatedModel->getClassId('Item'));
            $deleted = $derivedModels[0]->delete();
            $this->assertTrue($deleted);
        }

        /**
         * @depends testCreateRelatedHasOnesDerivedNonOwned
         * Similar to an account updating all related tasks.
         */
        public function testCreateRelatedDerivedsHasOneNonOwned()
        {
            $this->assertEquals(0, count(WorkflowModelTestItem5::getAll()));
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'model5ViaItem';
            $action->relatedModelRelation   = 'hasOne';
            $attributes                     = array('name'   => array('shouldSetValue'    => '1',
                                                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'  => 'some new model 2'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $saved = $model->save();
            $this->assertTrue($saved);
            $relatedModel       = new WorkflowModelTestItem5();
            $relatedModel->name = 'my derived model';
            $relatedModel->workflowItems->add($model);
            $saved = $relatedModel->save();
            $this->assertTrue($saved);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $derivedModels = WorkflowModelTestItem5::getAll();
            $this->assertEquals(1, count($derivedModels));
            $this->assertEquals('some new model 2', $derivedModels[0]->hasOne->name);
            $deleted = $derivedModels[0]->delete();
            $this->assertTrue($deleted);
        }

        /**
         * @depends testCreateRelatedDerivedsHasOneNonOwned
         * Similar to a meeting updating its related contacts
         */
        public function testUpdateRelatedInferred()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem5', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_UPDATE_RELATED;
            $action->relation             = 'WorkflowModelTestItem__workflowItems__Inferred';
            $attributes                   = array('string'   => array('shouldSetValue'    => '1',
                                                  'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'    => 'a new derived name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $inferredModel           = new WorkflowModelTestItem();
            $inferredModel->lastName = 'lastName';
            $inferredModel->string   = 'string';
            $saved           = $inferredModel->save();
            $this->assertTrue($saved);
            $inferredModelId = $inferredModel->id;
            $model = new WorkflowModelTestItem5();
            $model->workflowItems->add($inferredModel);
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals(1, $model->workflowItems->count());
            $inferredModel->forget();
            $inferredModel = WorkflowModelTestItem::getById($inferredModelId);
            $this->assertEquals('a new derived name', $inferredModel->string);
            $this->assertTrue($model->delete());
        }

        /**
         * @depends testCreateRelatedDerivedsHasOneNonOwned
         * Similar to a meeting updating its related contacts
         */
        public function testCreateInferred()
        {
            $action                       = new ActionForWorkflowForm('WorkflowModelTestItem5', Workflow::TYPE_ON_SAVE);
            $action->type                 = ActionForWorkflowForm::TYPE_CREATE;
            $action->relation             = 'WorkflowModelTestItem__workflowItems__Inferred';
            $attributes                   = array(  'string'   => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => 'a new derived name'),
                                                    'lastName'   => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => 'a new last name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem5();
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals(1, $model->workflowItems->count());
            $this->assertEquals('a new derived name', $model->workflowItems[0]->string);
            $this->assertTrue($model->id > 0);
            $this->assertTrue($model->delete());
        }

        /**
         * @depends testCreateInferred
         * Similar to a meeting updating its related contacts
         */
        public function testCreateRelatedHasOnesInferredNonOwned()
        {
            $this->assertEquals(0, count(WorkflowModelTestItem5::getAll()));
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem9', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'hasOne2';
            $action->relatedModelRelation   = 'WorkflowModelTestItem__workflowItems__Inferred';
            $attributes                   = array(  'string'   => array('shouldSetValue'    => '1',
                                                    'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'    => 'a new derived name'),
                                                    'lastName'   => array('shouldSetValue'    => '1',
                                                        'type'     => WorkflowActionAttributeForm::TYPE_STATIC,
                                                        'value'    => 'a new last name'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model = new WorkflowModelTestItem9();
            $saved = $model->save();
            $this->assertTrue($saved);
            $relatedModel = new WorkflowModelTestItem5();
            $saved = $relatedModel->save();
            $this->assertTrue($saved);
            $model->hasOne2 = $relatedModel;
            $saved = $model->save();
            $this->assertTrue($saved);
            $this->assertEquals(0, $model->hasOne2->workflowItems->count());
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertEquals(1, $model->hasOne2->workflowItems->count());
            $this->assertEquals('a new derived name', $model->hasOne2->workflowItems[0]->string);
        }

        /**
         * @depends testCreateRelatedHasOnesInferredNonOwned
         * Similar to a meeting updating its related contacts
         */
        public function testCreateRelatedInferredsHasOneNonOwned()
        {
            $action                         = new ActionForWorkflowForm('WorkflowModelTestItem5', Workflow::TYPE_ON_SAVE);
            $action->type                   = ActionForWorkflowForm::TYPE_CREATE_RELATED;
            $action->relation               = 'WorkflowModelTestItem__workflowItems__Inferred';
            $action->relatedModelRelation   = 'hasOne';
            $attributes                     = array('name'   => array('shouldSetValue'    => '1',
                                                    'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                    'value'  => 'some new model 2'));
            $action->setAttributes(array(ActionForWorkflowForm::ACTION_ATTRIBUTES => $attributes));
            $model                  = new WorkflowModelTestItem5();
            $relatedModel           = new WorkflowModelTestItem();
            $relatedModel->lastName = 'lastName';
            $relatedModel->string   = 'string';
            $saved                  = $relatedModel->save();
            $this->assertTrue($saved);
            $model->workflowItems->add($relatedModel);
            $saved                  = $model->save();
            $this->assertTrue($saved);
            $this->assertTrue($model->workflowItems[0]->hasOne->id < 0);
            $helper = new WorkflowActionProcessingHelper($action, $model, Yii::app()->user->userModel);
            $helper->processNonUpdateSelfAction();
            $this->assertTrue($model->workflowItems[0]->hasOne->id > 0);
            $this->assertEquals('some new model 2', $model->workflowItems[0]->hasOne->name);
        }
    }
?>