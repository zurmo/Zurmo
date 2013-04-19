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

    class ModelRelationsAndAttributesToWorkflowAdapterTest extends WorkflowBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $attributeName = 'calculated';
            $attributeForm = new CalculatedNumberAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array('en' => 'Test Calculated');
            $attributeForm->formula          = 'integer + float';
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new WorkflowModelTestItem());
            $adapter->setAttributeMetadataFromForm($attributeForm);
        }

        public function testGetAllRelations()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules(); //WorkflowsTestModule rules
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getAllRelationsData();
            $this->assertEquals(22, count($relations));
        }

        /**
         * @depends testGetAllRelations
         * Make sure HAS_MANY_BELONGS_TO relations show up
         */
        public function testGetHasManyBelongsToRelations()
        {
            $model              = new WorkflowModelTestItem9();
            $rules              = new WorkflowsTestWorkflowRules(); //WorkflowsTestModule rules
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules,
                                  $workflow->getType());
            $relations          = $adapter->getSelectableRelationsData();
            $this->assertEquals(9, count($relations));
            $compareData        = array('label' => 'Workflow Model Test Item 9');
            $this->assertEquals($compareData, $relations['workflowModelTestItem9']);
            $compareData        = array('label' => 'Workflow Model Test Item 9s');
            $this->assertEquals($compareData, $relations['workflowModelTestItem9s']);
        }

        /**
         * @depends testGetHasManyBelongsToRelations
         */
        public function testPassingPrecedingRelationThatHasAssumptiveLinkIsProperlyHandled()
        {
            $model              = new WorkflowModelTestItem3();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData(new WorkflowModelTestItem(), 'hasMany');
            $this->assertFalse(isset($relations['hasMany1']));
        }

        /**
         * @depends testPassingPrecedingRelationThatHasAssumptiveLinkIsProperlyHandled
         */
        public function testGetAllWorkflowableRelations()
        {
            //WorkflowModelTestItem has hasOne, hasMany, and hasOneAlso.  In addition it has a
            //derivedRelationsViaCastedUpModel to WorkflowModelTestItem5.
            //Excludes any customField relations and relationsWorkflowedOnAsAttributes
            //Also excludes any non-workflowable relations
            //Get relations through adapter and confirm everything matches up as expected
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(13, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * When retrieving available relations, make sure it does not give a relation based on what model it is coming
         * from.  If you are in a Contact and the parent relation is account, then Contact should not return the account
         * as an available relation.
         * @depends testGetAllWorkflowableRelations
         */
        public function testGetAvailableRelationsDoesNotCauseFeedbackLoop()
        {
            $model              = new WorkflowModelTestItem2();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'Has Many 2');
            $this->assertEquals($compareData, $relations['hasMany2']);
            $compareData        = array('label' => 'Has Many 3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            $precedingModel     = new WorkflowModelTestItem();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'hasOne');
            $this->assertEquals(4, count($relations));
            $compareData        = array('label' => 'Has Many 3');
            $this->assertEquals($compareData, $relations['hasMany3']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetAvailableRelationsDoesNotCauseFeedbackLoop
         */
        public function testGetUsableAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $attributes = $adapter->getAttributesIncludingDerivedAttributesData();
            $this->assertEquals(36, count($attributes));
            $compareData        = array('label' => 'Id');
            $this->assertEquals($compareData, $attributes['id']);
            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Includes derived attributes as well
            $compareData        = array('label' => 'Test Calculated', 'derivedAttributeType' => 'CalculatedNumber');
            $this->assertEquals($compareData, $attributes['calculated']);
            $compareData        = array('label' => 'Full Name',       'derivedAttributeType' => 'FullName');
            $this->assertEquals($compareData, $attributes['FullName']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetUsableAttributes
         * Testing where a model relates to another model via something like Item. An example is notes which connects
         * to accounts via activityItems MANY_MANY through Items.  On Notes we need to be able to show  these relations
         * as selectable in workflowing.
         *
         * In this example WorkflowModelTestItem5 connects to WorkflowModelTestItem and WorkflowModelTestItem2
         * via MANY_MANY through Item using the workflowItems relation
         * Known as viaRelations: model5ViaItem on WorkflowModelItem and model5ViaItem on WorkflowModelItem2
         */
        public function testGetInferredRelationsData()
        {
            $model              = new WorkflowModelTestItem5();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getInferredRelationsData();
            $this->assertEquals(2, count($relations));
            $compareData        = array('label' => 'Workflows Tests');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem__workflowItems__Inferred']);
            $compareData        = array('label' => 'WorkflowModelTestItem2s');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem2__workflowItems__Inferred']);

            //Getting all selectable relations. Should yield all 7 relations
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData();
            $this->assertEquals(7, count($relations));
            $compareData        = array('label' => 'Workflows Tests');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem__workflowItems__Inferred']);
            $compareData        = array('label' => 'WorkflowModelTestItem2s');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem2__workflowItems__Inferred']);
            $compareData        = array('label' => 'Workflow Items');
            $this->assertEquals($compareData, $relations['workflowItems']);
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetInferredRelationsData
         */
        public function testGetInferredRelationsDataWithPrecedingModel()
        {
            $model              = new WorkflowModelTestItem5();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $precedingModel     = new WorkflowModelTestItem7();
            $workflow->setModuleClassName('WorkflowsTestModule');
            //Test calling on model 5 with a preceding model that is NOT part of workflowItems
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'model5');
            $this->assertEquals(7, count($relations));
            $compareData        = array('label' => 'Workflows Tests');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem__workflowItems__Inferred']);
            $compareData        = array('label' => 'WorkflowModelTestItem2s');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem2__workflowItems__Inferred']);
            $compareData        = array('label' => 'Workflow Items');
            $this->assertEquals($compareData, $relations['workflowItems']);
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            //Test calling on model 5 with a preceding model that is one of the workflowItem models
            $precedingModel     = new WorkflowModelTestItem();
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'model5ViaItem');
            $this->assertEquals(6, count($relations));
            $compareData        = array('label' => 'WorkflowModelTestItem2s');
            $this->assertEquals($compareData, $relations['WorkflowModelTestItem2__workflowItems__Inferred']);
            $compareData        = array('label' => 'Workflow Items');
            $this->assertEquals($compareData, $relations['workflowItems']);
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetInferredRelationsDataWithPrecedingModel
         */
        public function testGetDerivedRelationsViaCastedUpModelDataWithPrecedingModel()
        {
            //test with preceding model that is not the via relation
            $model              = new WorkflowModelTestItem();
            $precedingModel     = new WorkflowModelTestItem5();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'cannotTrigger2');
            $this->assertEquals(13, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);

            //test with preceding model that is the via relation
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations = $adapter->getSelectableRelationsData($precedingModel, 'workflowItems');
            $this->assertEquals(12, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetDerivedRelationsViaCastedUpModelDataWithPrecedingModel
         */
        public function testGetAllAvailableOnSaveTriggersRelations()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsData();
            $this->assertEquals(13, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetAllAvailableOnSaveTriggersRelations
         */
        public function testGetAllAvailableOnSaveTriggersRelationsWithPreceding()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsData(new WorkflowModelTestItem(), 'hasMany');
            $this->assertEquals(3, count($relations));
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
        }

        /**
         * @depends testGetAllAvailableOnSaveTriggersRelationsWithPreceding
         */
        public function testGetAvailableAttributesForOnSaveTriggers()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $attributes = $adapter->getAttributesForTriggers();
            $this->assertEquals(33, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForOnSaveTriggers
         */
        public function testGetAvailableAttributesForOnSaveCreateActionAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());

            $attributes = $adapter->getRequiredAttributesForActions();
            $this->assertEquals(3, count($attributes));
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);

            $attributes = $adapter->getNonRequiredAttributesForActions();
            $this->assertEquals(38, count($attributes));

            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add in owned primaryAddress, and primaryEmail, and secondaryEmail attributes
            $compareData        = array('label' => 'Primary Address >> City');
            $this->assertEquals($compareData, $attributes['primaryAddress___city']);
            $compareData        = array('label' => 'Primary Address >> Country');
            $this->assertEquals($compareData, $attributes['primaryAddress___country']);
            $compareData        = array('label' => 'Primary Address >> Postal Code');
            $this->assertEquals($compareData, $attributes['primaryAddress___postalCode']);
            $compareData        = array('label' => 'Primary Address >> State');
            $this->assertEquals($compareData, $attributes['primaryAddress___state']);
            $compareData        = array('label' => 'Primary Address >> Street 1');
            $this->assertEquals($compareData, $attributes['primaryAddress___street1']);
            $compareData        = array('label' => 'Primary Address >> Street 2');
            $this->assertEquals($compareData, $attributes['primaryAddress___street2']);
            //Email fields
            $compareData        = array('label' => 'Primary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['primaryEmail___emailAddress']);
            $compareData        = array('label' => 'Primary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['primaryEmail___optOut']);
            $compareData        = array('label' => 'Primary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['primaryEmail___isInvalid']);
            $compareData        = array('label' => 'Secondary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['secondaryEmail___emailAddress']);
            $compareData        = array('label' => 'Secondary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['secondaryEmail___optOut']);
            $compareData        = array('label' => 'Secondary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['secondaryEmail___isInvalid']);
        }

        /**
         * @depends testGetAvailableAttributesForOnSaveCreateActionAttributes
         */
        public function testGetAvailableAttributesForOnSaveUpdateActionAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());

            $attributes = $adapter->getAllAttributesForActions();
            $this->assertEquals(41, count($attributes));
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add in owned primaryAddress, and primaryEmail, and secondaryEmail attributes
            $compareData        = array('label' => 'Primary Address >> City');
            $this->assertEquals($compareData, $attributes['primaryAddress___city']);
            $compareData        = array('label' => 'Primary Address >> Country');
            $this->assertEquals($compareData, $attributes['primaryAddress___country']);
            $compareData        = array('label' => 'Primary Address >> Postal Code');
            $this->assertEquals($compareData, $attributes['primaryAddress___postalCode']);
            $compareData        = array('label' => 'Primary Address >> State');
            $this->assertEquals($compareData, $attributes['primaryAddress___state']);
            $compareData        = array('label' => 'Primary Address >> Street 1');
            $this->assertEquals($compareData, $attributes['primaryAddress___street1']);
            $compareData        = array('label' => 'Primary Address >> Street 2');
            $this->assertEquals($compareData, $attributes['primaryAddress___street2']);
            //Email fields
            $compareData        = array('label' => 'Primary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['primaryEmail___emailAddress']);
            $compareData        = array('label' => 'Primary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['primaryEmail___optOut']);
            $compareData        = array('label' => 'Primary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['primaryEmail___isInvalid']);
            $compareData        = array('label' => 'Secondary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['secondaryEmail___emailAddress']);
            $compareData        = array('label' => 'Secondary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['secondaryEmail___optOut']);
            $compareData        = array('label' => 'Secondary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['secondaryEmail___isInvalid']);
        }

        /**
         * @depends testGetAvailableAttributesForOnSaveUpdateActionAttributes
         */
        public function testGetAllAvailableOnSaveRelationsForActionTypeRelation()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsDataForActionTypeRelation();
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
        }

        /**
         * @depends testGetAllAvailableOnSaveRelationsForActionTypeRelation
         */
        public function testGetAllAvailableByTimeTriggersRelations()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsData();
            $this->assertEquals(13, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $relations['owner']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $relations['createdByUser']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $relations['modifiedByUser']);
        }

        /**
         * @depends testGetAllAvailableByTimeTriggersRelations
         */
        public function testGetAllAvailableByTimeTriggersRelationsWithPreceding()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsData(new WorkflowModelTestItem(), 'hasMany');
            $this->assertEquals(3, count($relations));
            $compareData        = array('label' => 'Primary Email');
            $this->assertEquals($compareData, $relations['primaryEmail']);
            $compareData        = array('label' => 'Primary Address');
            $this->assertEquals($compareData, $relations['primaryAddress']);
            $compareData        = array('label' => 'Secondary Email');
            $this->assertEquals($compareData, $relations['secondaryEmail']);
        }

        /**
         * @depends testGetAllAvailableByTimeTriggersRelationsWithPreceding
         */
        public function testGetAvailableAttributesForByTimeTriggers()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules, $workflow->getType());
            $attributes = $adapter->getAttributesForTriggers();
            $this->assertEquals(33, count($attributes));

            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add Dynamically Derived Attributes
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
        }

        /**
         * @depends testGetAvailableAttributesForByTimeTriggers
         */
        public function testGetAvailableAttributesForByTimeTimeTrigger()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules, $workflow->getType());
            $attributes = $adapter->getAttributesForTimeTrigger();
            $this->assertEquals(45, count($attributes));
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'Modified By User');
            $this->assertEquals($compareData, $attributes['modifiedByUser__User']);
            $compareData        = array('label' => 'Created By User');
            $this->assertEquals($compareData, $attributes['createdByUser__User']);
            $compareData        = array('label' => 'User');
            $this->assertEquals($compareData, $attributes['user__User']);
            $compareData        = array('label' => 'User 2');
            $this->assertEquals($compareData, $attributes['user2__User']);
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date 2');
            $this->assertEquals($compareData, $attributes['date2']);
            $compareData        = array('label' => 'Date 3');
            $this->assertEquals($compareData, $attributes['date3']);
            $compareData        = array('label' => 'Date 4');
            $this->assertEquals($compareData, $attributes['date4']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Date Time 2');
            $this->assertEquals($compareData, $attributes['dateTime2']);
            $compareData        = array('label' => 'Date Time 3');
            $this->assertEquals($compareData, $attributes['dateTime3']);
            $compareData        = array('label' => 'Date Time 4');
            $this->assertEquals($compareData, $attributes['dateTime4']);
            $compareData        = array('label' => 'Created Date Time');
            $this->assertEquals($compareData, $attributes['createdDateTime']);
            $compareData        = array('label' => 'Modified Date Time');
            $this->assertEquals($compareData, $attributes['modifiedDateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add in owned primaryAddress, and primaryEmail, and secondaryEmail attributes
            $compareData        = array('label' => 'Primary Address >> City');
            $this->assertEquals($compareData, $attributes['primaryAddress___city']);
            $compareData        = array('label' => 'Primary Address >> Country');
            $this->assertEquals($compareData, $attributes['primaryAddress___country']);
            $compareData        = array('label' => 'Primary Address >> Postal Code');
            $this->assertEquals($compareData, $attributes['primaryAddress___postalCode']);
            $compareData        = array('label' => 'Primary Address >> State');
            $this->assertEquals($compareData, $attributes['primaryAddress___state']);
            $compareData        = array('label' => 'Primary Address >> Street 1');
            $this->assertEquals($compareData, $attributes['primaryAddress___street1']);
            $compareData        = array('label' => 'Primary Address >> Street 2');
            $this->assertEquals($compareData, $attributes['primaryAddress___street2']);
            //Email fields
            $compareData        = array('label' => 'Primary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['primaryEmail___emailAddress']);
            $compareData        = array('label' => 'Primary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['primaryEmail___optOut']);
            $compareData        = array('label' => 'Primary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['primaryEmail___isInvalid']);
            $compareData        = array('label' => 'Secondary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['secondaryEmail___emailAddress']);
            $compareData        = array('label' => 'Secondary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['secondaryEmail___optOut']);
            $compareData        = array('label' => 'Secondary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['secondaryEmail___isInvalid']);
        }

        /**
         * @depends testGetAvailableAttributesForByTimeTimeTrigger
         */
        public function testGetAvailableAttributesForByTimeCreateActionAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());

            $attributes = $adapter->getRequiredAttributesForActions();
            $this->assertEquals(3, count($attributes));
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);

            $attributes = $adapter->getNonRequiredAttributesForActions();
            $this->assertEquals(38, count($attributes));

            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add in owned primaryAddress, and primaryEmail, and secondaryEmail attributes
            $compareData        = array('label' => 'Primary Address >> City');
            $this->assertEquals($compareData, $attributes['primaryAddress___city']);
            $compareData        = array('label' => 'Primary Address >> Country');
            $this->assertEquals($compareData, $attributes['primaryAddress___country']);
            $compareData        = array('label' => 'Primary Address >> Postal Code');
            $this->assertEquals($compareData, $attributes['primaryAddress___postalCode']);
            $compareData        = array('label' => 'Primary Address >> State');
            $this->assertEquals($compareData, $attributes['primaryAddress___state']);
            $compareData        = array('label' => 'Primary Address >> Street 1');
            $this->assertEquals($compareData, $attributes['primaryAddress___street1']);
            $compareData        = array('label' => 'Primary Address >> Street 2');
            $this->assertEquals($compareData, $attributes['primaryAddress___street2']);
            //Email fields
            $compareData        = array('label' => 'Primary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['primaryEmail___emailAddress']);
            $compareData        = array('label' => 'Primary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['primaryEmail___optOut']);
            $compareData        = array('label' => 'Primary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['primaryEmail___isInvalid']);
            $compareData        = array('label' => 'Secondary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['secondaryEmail___emailAddress']);
            $compareData        = array('label' => 'Secondary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['secondaryEmail___optOut']);
            $compareData        = array('label' => 'Secondary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['secondaryEmail___isInvalid']);
        }

        /**
         * @depends testGetAvailableAttributesForByTimeCreateActionAttributes
         */
        public function testGetAvailableAttributesForByTimeUpdateActionAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());

            $attributes = $adapter->getAllAttributesForActions();
            $this->assertEquals(41, count($attributes));
            $compareData        = array('label' => 'Last Name');
            $this->assertEquals($compareData, $attributes['lastName']);
            $compareData        = array('label' => 'String');
            $this->assertEquals($compareData, $attributes['string']);
            $compareData        = array('label' => 'Owner');
            $this->assertEquals($compareData, $attributes['owner__User']);
            $compareData        = array('label' => 'First Name');
            $this->assertEquals($compareData, $attributes['firstName']);
            $compareData        = array('label' => 'Boolean');
            $this->assertEquals($compareData, $attributes['boolean']);
            $compareData        = array('label' => 'Date');
            $this->assertEquals($compareData, $attributes['date']);
            $compareData        = array('label' => 'Date Time');
            $this->assertEquals($compareData, $attributes['dateTime']);
            $compareData        = array('label' => 'Float');
            $this->assertEquals($compareData, $attributes['float']);
            $compareData        = array('label' => 'Integer');
            $this->assertEquals($compareData, $attributes['integer']);
            $compareData        = array('label' => 'Phone');
            $this->assertEquals($compareData, $attributes['phone']);
            $compareData        = array('label' => 'Text Area');
            $this->assertEquals($compareData, $attributes['textArea']);
            $compareData        = array('label' => 'Url');
            $this->assertEquals($compareData, $attributes['url']);
            $compareData        = array('label' => 'Drop Down');
            $this->assertEquals($compareData, $attributes['dropDown']);
            $compareData        = array('label' => 'Drop Down 2');
            $this->assertEquals($compareData, $attributes['dropDown2']);
            $compareData        = array('label' => 'Radio Drop Down');
            $this->assertEquals($compareData, $attributes['radioDropDown']);
            $compareData        = array('label' => 'Multi Drop Down');
            $this->assertEquals($compareData, $attributes['multiDropDown']);
            $compareData        = array('label' => 'Tag Cloud');
            $this->assertEquals($compareData, $attributes['tagCloud']);
            $compareData        = array('label' => 'Used As Attribute');
            $this->assertEquals($compareData, $attributes['usedAsAttribute']);
            //Currency is treated as a relation workflowed as an attribute just like drop downs
            $compareData        = array('label' => 'Currency Value');
            $this->assertEquals($compareData, $attributes['currencyValue']);
            //likeContactState is a relation workflowed as attribute.
            //Makes sure the label is using the proper label translation via attributeLabels
            $compareData        = array('label' => 'A name for a state');
            $this->assertEquals($compareData, $attributes['likeContactState']);
            //Add in owned primaryAddress, and primaryEmail, and secondaryEmail attributes
            $compareData        = array('label' => 'Primary Address >> City');
            $this->assertEquals($compareData, $attributes['primaryAddress___city']);
            $compareData        = array('label' => 'Primary Address >> Country');
            $this->assertEquals($compareData, $attributes['primaryAddress___country']);
            $compareData        = array('label' => 'Primary Address >> Postal Code');
            $this->assertEquals($compareData, $attributes['primaryAddress___postalCode']);
            $compareData        = array('label' => 'Primary Address >> State');
            $this->assertEquals($compareData, $attributes['primaryAddress___state']);
            $compareData        = array('label' => 'Primary Address >> Street 1');
            $this->assertEquals($compareData, $attributes['primaryAddress___street1']);
            $compareData        = array('label' => 'Primary Address >> Street 2');
            $this->assertEquals($compareData, $attributes['primaryAddress___street2']);
            //Email fields
            $compareData        = array('label' => 'Primary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['primaryEmail___emailAddress']);
            $compareData        = array('label' => 'Primary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['primaryEmail___optOut']);
            $compareData        = array('label' => 'Primary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['primaryEmail___isInvalid']);
            $compareData        = array('label' => 'Secondary Email >> Email Address');
            $this->assertEquals($compareData, $attributes['secondaryEmail___emailAddress']);
            $compareData        = array('label' => 'Secondary Email >> Opt Out');
            $this->assertEquals($compareData, $attributes['secondaryEmail___optOut']);
            $compareData        = array('label' => 'Secondary Email >> Is Invalid');
            $this->assertEquals($compareData, $attributes['secondaryEmail___isInvalid']);
        }

        /**
         * @depends testGetAvailableAttributesForByTimeUpdateActionAttributes
         */
        public function testGetAllAvailableByTimeRelationsForActionTypeRelation()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_BY_TIME);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToByTimeWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsDataForActionTypeRelation();
            $this->assertEquals(5, count($relations));
            $compareData        = array('label' => 'Has One');
            $this->assertEquals($compareData, $relations['hasOne']);
            $compareData        = array('label' => 'Has One Again');
            $this->assertEquals($compareData, $relations['hasOneAgain']);
            $compareData        = array('label' => 'Has Many');
            $this->assertEquals($compareData, $relations['hasMany']);
            $compareData        = array('label' => 'Has One Also');
            $this->assertEquals($compareData, $relations['hasOneAlso']);
            $compareData        = array('label' => 'Model 5 Via Item');
            $this->assertEquals($compareData, $relations['model5ViaItem']);
        }

        /**
         * @depends testGetAllAvailableByTimeRelationsForActionTypeRelation
         */
        public function testIsRelation()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $this->assertTrue($adapter->isUsedAsARelation('hasOne'));
            $this->assertFalse($adapter->isUsedAsARelation('garbage'));
            $this->assertFalse($adapter->isUsedAsARelation('float'));
            $this->assertFalse($adapter->isUsedAsARelation('firstname'));
            $this->assertFalse($adapter->isUsedAsARelation('createdByUser__User'));
            $this->assertTrue($adapter->isUsedAsARelation('modifiedByUser'));
            $this->assertTrue($adapter->isUsedAsARelation('model5ViaItem'));
            $this->assertTrue($adapter->isUsedAsARelation('primaryEmail'));
            $this->assertFalse($adapter->isUsedAsARelation('dropDown'));

            $model              = new WorkflowModelTestItem5();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $this->assertTrue ($adapter->isUsedAsARelation('WorkflowModelTestItem2__workflowItems__Inferred'));
            $this->assertTrue ($adapter->isUsedAsARelation('WorkflowModelTestItem__workflowItems__Inferred'));
            $this->assertTrue ($adapter->isUsedAsARelation('WorkflowModelTestItem__workflowItems__Inferred'));
        }

        /**
         * @depends testIsRelation
         */
        public function testIsRelationASingularRelation()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $this->assertTrue($adapter->isRelationASingularRelation('hasOne'));
            $this->assertFalse($adapter->isRelationASingularRelation('hasMany'));

            $model              = new WorkflowModelTestItem5();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToOnSaveWorkflowAdapter($model, $rules, $workflow->getType());
            $this->assertFalse($adapter->isRelationASingularRelation('WorkflowModelTestItem2__workflowItems__Inferred'));
            $this->assertFalse($adapter->isRelationASingularRelation('WorkflowModelTestItem__workflowItems__Inferred'));
        }

        /**
         * @depends testIsRelationASingularRelation
         */
        public function testGetTriggerValueElementTypeForNonStatefulAttributes()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('BooleanForWizardStaticDropDown', $adapter->getTriggerValueElementType('boolean'));
            $this->assertEquals('MixedCurrencyValueTypes',        $adapter->getTriggerValueElementType('currencyValue'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date2'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date3'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date4'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime2'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime3'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime4'));
            $this->assertEquals('StaticDropDownForWorkflow',        $adapter->getTriggerValueElementType('dropDown'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getTriggerValueElementType('float'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getTriggerValueElementType('integer'));
            $this->assertEquals('StaticMultiSelectDropDownForWorkflow', $adapter->getTriggerValueElementType('multiDropDown'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('phone'));
            $this->assertEquals('StaticDropDownForWorkflow',        $adapter->getTriggerValueElementType('radioDropDown'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('string'));
            $this->assertEquals('StaticMultiSelectDropDownForWorkflow', $adapter->getTriggerValueElementType('tagCloud'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('textArea'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('url'));
        }

        /**
         * @depends testGetTriggerValueElementTypeForNonStatefulAttributes
         */
        public function testGetTriggerValueElementTypeForAStatefulAttribute()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getTriggerValueElementType('likeContactState'));

            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsAlternateStateTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('LeadStateStaticDropDownForWorkflow', $adapter->getTriggerValueElementType('likeContactState'));
        }

        /**
         * @depends testGetTriggerValueElementTypeForAStatefulAttribute
         */
        public function testGetTriggerValueElementType()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('BooleanForWizardStaticDropDown', $adapter->getTriggerValueElementType('boolean'));
            $this->assertEquals('MixedCurrencyValueTypes',        $adapter->getTriggerValueElementType('currencyValue'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date2'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date3'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('date4'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime2'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime3'));
            $this->assertEquals('MixedDateTypesForWorkflow',        $adapter->getTriggerValueElementType('dateTime4'));
            $this->assertEquals('StaticDropDownForWorkflow',        $adapter->getTriggerValueElementType('dropDown'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getTriggerValueElementType('float'));
            $this->assertEquals('MixedNumberTypes',               $adapter->getTriggerValueElementType('integer'));
            $this->assertEquals('StaticMultiSelectDropDownForWorkflow', $adapter->getTriggerValueElementType('multiDropDown'));
            $this->assertEquals('UserNameId',                     $adapter->getTriggerValueElementType('owner__User'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('phone'));
            $this->assertEquals('StaticDropDownForWorkflow',        $adapter->getTriggerValueElementType('radioDropDown'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('string'));
            $this->assertEquals('StaticMultiSelectDropDownForWorkflow', $adapter->getTriggerValueElementType('tagCloud'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('textArea'));
            $this->assertEquals('Text',                           $adapter->getTriggerValueElementType('url'));

            $this->assertEquals('AllContactStatesStaticDropDownForWizardModel', $adapter->getTriggerValueElementType('likeContactState'));
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsAlternateStateTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('LeadStateStaticDropDownForWorkflow', $adapter->getTriggerValueElementType('likeContactState'));
        }

        /**
         * @depends testGetTriggerValueElementType
         */
        public function testGetAvailableOperatorsType()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('string'));

            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals('Boolean', $adapter->getAvailableOperatorsType('boolean'));
            $this->assertEquals('CurrencyValue', $adapter->getAvailableOperatorsType('currencyValue'));
            $this->assertNull($adapter->getAvailableOperatorsType('date'));
            $this->assertNull($adapter->getAvailableOperatorsType('date2'));
            $this->assertNull($adapter->getAvailableOperatorsType('date3'));
            $this->assertNull($adapter->getAvailableOperatorsType('date4'));
            $this->assertNull($adapter->getAvailableOperatorsType('dateTime'));
            $this->assertNull($adapter->getAvailableOperatorsType('dateTime2'));
            $this->assertNull($adapter->getAvailableOperatorsType('dateTime3'));
            $this->assertNull($adapter->getAvailableOperatorsType('dateTime4'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('dropDown'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                $adapter->getAvailableOperatorsType('float'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_NUMBER,
                                $adapter->getAvailableOperatorsType('integer'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('multiDropDown'));
            $this->assertEquals('HasOne', $adapter->getAvailableOperatorsType('owner__User'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('phone'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
            $adapter->getAvailableOperatorsType('radioDropDown'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('string'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('tagCloud'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('textArea'));
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_STRING,
                                $adapter->getAvailableOperatorsType('url'));

            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('likeContactState'));
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsAlternateStateTestWorkflowRules();
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, Workflow::TYPE_ON_SAVE);
            $this->assertEquals(ModelAttributeToOperatorTypeUtil::AVAILABLE_OPERATORS_TYPE_DROPDOWN,
                                $adapter->getAvailableOperatorsType('likeContactState'));
        }

        /**
         * @depends testGetAvailableOperatorsType
         */
        public function testGetSelectableRelationsDataForEmailMessageRecipientModelRelation()
        {
            $model              = new WorkflowModelTestItem();
            $rules              = new WorkflowsTestWorkflowRules();
            $workflow             = new Workflow();
            $workflow->setType(Workflow::TYPE_ON_SAVE);
            $workflow->setModuleClassName('WorkflowsTestModule');
            $adapter            = new ModelRelationsAndAttributesToWorkflowAdapter($model, $rules, $workflow->getType());
            $relations          = $adapter->getSelectableRelationsDataForEmailMessageRecipientModelRelation();
            $this->assertEquals(5, count($relations));
        }
    }
?>