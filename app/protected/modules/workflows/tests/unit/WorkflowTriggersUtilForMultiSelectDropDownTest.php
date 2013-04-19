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
     * Test dropDown attribute types for all various operatorTypes and important scenarios
     *
     * #1 - Test each operator type against attribute on model
     */
    class WorkflowTriggersUtilForMultiSelectDropDownTest extends WorkflowTriggersUtilBaseTest
    {
        public function testTimeTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTimeTriggerWithoutValueType('multiDropDown', 'equals', array('aValue'), 500);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'something';
            //At this point the model has not changed, so it should not fire
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            //At this point the model has changed, but has not changed into the correct value
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //Now the model has changed into the correct value, so it should return true.
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            //The model has not changed, so it should not fire.
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //The model has changed but to the wrong value, so it should fire.
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            //the model has changed, and to the correct value
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTimeTriggerBeforeSaveEquals
         */
        public function testTimeTriggerBeforeSaveEqualsWithANonTimeTrigger()
        {
            $workflow = self::makeOnSaveWorkflowAndTimeTriggerWithoutValueType('multiDropDown', 'equals', array('aValue'), 500);
            $trigger = new TriggerForWorkflowForm('WorkflowsTestModule', 'WorkflowModelTestItem', $workflow->getType());
            $trigger->attributeIndexOrDerivedType = 'lastName';
            $trigger->value                       = 'Green';
            $trigger->operator                    = 'equals';
            $workflow->addTrigger($trigger);

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'something';
            //At this point the value has changed, but the normal trigger is not satisfied
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            //Now the normal trigger is satisfied
            $model->lastName = 'Green';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTimeTriggerBeforeSaveEqualsWithANonTimeTrigger
         */
        public function testTimeTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTimeTriggerWithoutValueType('multiDropDown', 'doesNotChange', null, 500);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'something';
            //At this point the model has not changed, so it actually shouldn't fire, since it needs a change event
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            //At this point the model has changed, so it should fire as true, so it can create or update the queue model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTimeTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveEquals()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'equals', array('aValue'));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model        = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveEquals
         */
        public function testTriggerBeforeSaveOneOf()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'oneOf', array('b', 'c', 'd'));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $value        = new CustomFieldValue();
            $value->value = 'b';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'dd';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'c';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveOneOf
         */
        public function testTriggerBeforeSaveDoesNotEqual()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'doesNotEqual', array('aValue'));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value2        = new CustomFieldValue();
            $value2->value = 'bValue';
            $model->multiDropDown->values->add($value2);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->multiDropDown->values->remove($value2);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotEqual
         */
        public function testTriggerBeforeSaveBecomes()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'becomes', array('aValue'));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $value        = new CustomFieldValue();
            $value->value = 'cValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'becomes' aValue
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $model->multiDropDown->resolveOriginalCustomFieldValuesDataForNewData();
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveBecomes
         */
        public function testTriggerBeforeSaveWas()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'was', array('aValue'));
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $model->multiDropDown->values->removeAll();
            $value        = new CustomFieldValue();
            $value->value = 'aValue';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'was' aValue and is now bValue
            $value        = new CustomFieldValue();
            $value->value = 'bValue';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveWas
         */
        public function testTriggerBeforeSaveChanges()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'changes', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $value        = new CustomFieldValue();
            $value->value = 'd';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $value        = new CustomFieldValue();
            $value->value = 'a';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveChanges
         */
        public function testTriggerBeforeSaveDoesNotChange()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'doesNotChange', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));

            $value        = new CustomFieldValue();
            $value->value = 'b';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //check existing model
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model = self::saveAndReloadModel($model);

            //Now it should be true because it 'changes'
            $value        = new CustomFieldValue();
            $value->value = 'c';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveDoesNotChange
         */
        public function testTriggerBeforeSaveIsEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'isEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'd';
            $model->multiDropDown->values->add($value);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->multiDropDown->values->removeAll();
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }

        /**
         * @depends testTriggerBeforeSaveIsEmpty
         */
        public function testTriggerBeforeSaveIsNotEmpty()
        {
            $workflow = self::makeOnSaveWorkflowAndTriggerWithoutValueType('multiDropDown', 'isNotEmpty', null);
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'someLastName';
            $model->string   = 'someValue';
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $value        = new CustomFieldValue();
            $value->value = 'd';
            $model->multiDropDown->values->add($value);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model         = self::saveAndReloadModel($model);
            $this->assertTrue(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
            $model->multiDropDown->values->removeAll();
            $this->assertFalse(WorkflowTriggersUtil::areTriggersTrueBeforeSave($workflow, $model));
        }
    }
?>