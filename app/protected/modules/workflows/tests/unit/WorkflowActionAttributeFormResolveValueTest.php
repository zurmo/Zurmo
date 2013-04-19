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

    class WorkflowActionAttributeFormResolveValueTest extends WorkflowBaseTest
    {
        public $freeze = false;

        protected static $baseCurrencyId;

        protected static $eurCurrencyId;

        protected static $newState;

        protected static $inProgressState;

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
            $currency = Currency::makeBaseCurrency();
            assert($currency->code == 'USD'); // Not Coding Standard
            self::$baseCurrencyId = $currency->id;
            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 2;
            assert($currency->save()); // Not Coding Standard
            self::$eurCurrencyId = $currency->id;

            $values = array(
                'A1',
                'B2',
                'C3',
                'D4',
                'E5',
                'F6',
            );
            $fieldData = CustomFieldData::getByName('WorkflowTestDropDown');
            $fieldData->serializedData   = serialize($values);
            $saved = $fieldData->save();
            assert($saved); // Not Coding Standard

            $values = array(
                'A1',
                'B2',
                'C3',
                'D4',
                'E5',
                'F6',
            );
            $fieldData = CustomFieldData::getByName('WorkflowTestRadioDropDown');
            $fieldData->serializedData   = serialize($values);
            $saved = $fieldData->save();
            assert($saved); // Not Coding Standard

            $values = array(
                'M1',
                'M2',
                'M3',
                'M4',
                'M5',
                'M6',
            );
            $fieldData = CustomFieldData::getByName('WorkflowTestMultiDropDown');
            $fieldData->serializedData   = serialize($values);
            $saved = $fieldData->save();
            assert($saved); // Not Coding Standard

            $values = array(
                'M1',
                'M2',
                'M3',
                'M4',
                'M5',
                'M6',
            );
            $fieldData = CustomFieldData::getByName('WorkflowTestTagCloud');
            $fieldData->serializedData   = serialize($values);
            $saved = $fieldData->save();
            assert($saved); // Not Coding Standard

            $loaded = ContactsModule::loadStartingData();
            assert($loaded); // Not Coding Standard
            $contactStates          = ContactState::getByName('New');
            self::$newState         = $contactStates[0];
            $contactStates          = ContactState::getByName('In progress');
            self::$inProgressState  = $contactStates[0];
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

        public function testCheckBoxResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new CheckBoxWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = CheckBoxWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '1';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->boolean);
            $form->resolveValueAndSetToModel($adapter, 'boolean');
            $this->assertEquals('1', $model->boolean);

            $form->value = '0';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->boolean);
            $form->resolveValueAndSetToModel($adapter, 'boolean');
            $this->assertEquals('0', $model->boolean);
        }

        public function testCurrencyValueResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new CurrencyValueWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DecimalWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '54';
            $form->currencyId = self::$eurCurrencyId;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertEquals(0, $model->currencyValue->value);
            $this->assertEquals('USD', $model->currencyValue->currency->code);
            $form->resolveValueAndSetToModel($adapter, 'currencyValue');
            $this->assertEquals('54', $model->currencyValue->value);
            $this->assertEquals('EUR', $model->currencyValue->currency->code);
        }

        public function testDateResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new DateWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '1980-06-03';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->date);
            $form->resolveValueAndSetToModel($adapter, 'date');
            $this->assertEquals('1980-06-03', $model->date);
        }

        public function testDateResolveValueAndSetToModelUpdateAsDynamicFromTriggeredDate()
        {
            $form        = new DateWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE;
            $form->value = '86400';
            $model       = new WorkflowModelTestItem();
            $model->date = '1980-06-03';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'date');
            $this->assertEquals(DateTimeUtil::convertTimestampToDbFormatDate(time() + 86400), $model->date);
        }

        public function testDateResolveValueAndSetToModelUpdateAsDynamicFromExistingDate()
        {
            $form        = new DateWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATE;
            $form->value = '86400';
            $model       = new WorkflowModelTestItem();
            $model->date = '1980-01-05';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'date');
            $this->assertEquals('1980-01-06', $model->date);
        }

        public function testDateTimeResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new DateTimeWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateTimeWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '1980-06-03 04:00:00';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->dateTime);
            $form->resolveValueAndSetToModel($adapter, 'dateTime');
            $this->assertEquals('1980-06-03 04:00:00', $model->dateTime);
        }

        public function testDateTimeResolveValueAndSetToModelUpdateAsDynamicFromTriggeredDate()
        {
            $form        = new DateTimeWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME;
            $form->value = '86400';
            $model       = new WorkflowModelTestItem();
            $model->date = '1980-06-03 04:00:00';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dateTime');
            $this->assertEquals(DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 86400), $model->dateTime);
        }

        public function testDateTimeResolveValueAndSetToModelUpdateAsDynamicFromExistingDate()
        {
            $form        = new DateTimeWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_EXISTING_DATETIME;
            $form->value = '86400';
            $model       = new WorkflowModelTestItem();
            $model->dateTime = '1980-01-05 04:00:00';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dateTime');
            $this->assertEquals('1980-01-06 04:00:00', $model->dateTime);
        }

        public function testDecimalResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new DecimalWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DecimalWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '54.22';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->float);
            $form->resolveValueAndSetToModel($adapter, 'float');
            $this->assertEquals('54.22', $model->float);
        }

        public function testDropDownResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new DropDownWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DropDownWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'Abc';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->dropDown->value);
            $form->resolveValueAndSetToModel($adapter, 'dropDown');
            $this->assertEquals('Abc', $model->dropDown->value);
        }

        public function testDropDownResolveValueAndSetToModelUpdateSteppingForwardAndBackward()
        {
            $form        = new DropDownWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS;
            $form->value = '2';

            //Step forward where the new forward step is valid
            $model       = new WorkflowModelTestItem();
            $model->dropDown->value = 'A1';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dropDown');
            $this->assertEquals('C3', $model->dropDown->value);

            //Step forward where the new forwardstep is invalid
            $model       = new WorkflowModelTestItem();
            $model->dropDown->value = 'E5';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dropDown');
            $this->assertEquals('E5', $model->dropDown->value);

            //Step forward where the new backward step is valid
            $form->value = '-1';
            $model       = new WorkflowModelTestItem();
            $model->dropDown->value = 'C3';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dropDown');
            $this->assertEquals('B2', $model->dropDown->value);

            //Step forward where the new backward step is invalid
            $form->value = '-1';
            $model       = new WorkflowModelTestItem();
            $model->dropDown->value = 'A1';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'dropDown');
            $this->assertEquals('A1', $model->dropDown->value);
        }

        public function testEmailResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new EmailWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = EmailWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'test@zurmo.com';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model->primaryEmail, Yii::app()->user->userModel);
            $this->assertNull($model->primaryEmail->emailAddress);
            $form->resolveValueAndSetToModel($adapter, 'emailAddress');
            $this->assertEquals('test@zurmo.com', $model->primaryEmail->emailAddress);
        }

        public function testEmailResolveValueAndSetToModelUpdateAsNull()
        {
            $form          = new EmailWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type    = EmailWorkflowActionAttributeForm::TYPE_STATIC_NULL;
            $model         = new WorkflowModelTestItem();
            $model->primaryEmail->emailAddress = 'test@zurmo.com';
            $adapter       = new WorkflowActionProcessingModelAdapter($model->primaryEmail, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'emailAddress');
            $this->assertNull($model->primaryEmail->emailAddress);
        }

        public function testIntegerResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new IntegerWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = IntegerWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = '54';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->integer);
            $form->resolveValueAndSetToModel($adapter, 'integer');
            $this->assertEquals('54', $model->integer);
        }

        public function testLikeContactStateResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new ContactStateWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = ContactStateWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = self::$inProgressState->id;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertTrue($model->likeContactState->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'likeContactState');
            $this->assertEquals(self::$inProgressState->id, $model->likeContactState->id);
        }

        public function testMultiSelectDropDownResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new MultiSelectDropDownWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = MultiSelectDropDownWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'M2';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertEquals(0, $model->multiDropDown->values->count());
            $form->resolveValueAndSetToModel($adapter, 'multiDropDown');
            $this->assertEquals(1, $model->multiDropDown->values->count());
            $this->assertEquals('M2', $model->multiDropDown->values[0]->value);

            //Now replace M2 with M3
            $form->value = 'M3';
            $form->resolveValueAndSetToModel($adapter, 'multiDropDown');
            $this->assertEquals(1, $model->multiDropDown->values->count());
            $this->assertEquals('M3', $model->multiDropDown->values[0]->value);
        }

        public function testPhoneResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new PhoneWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = PhoneWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'abc';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->phone);
            $form->resolveValueAndSetToModel($adapter, 'phone');
            $this->assertEquals('abc', $model->phone);
        }

        public function testPhoneResolveValueAndSetToModelUpdateAsNull()
        {
            $form          = new PhoneWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type    = PhoneWorkflowActionAttributeForm::TYPE_STATIC_NULL;
            $model         = new WorkflowModelTestItem();
            $model->phone = 'abc';
            $adapter       = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'phone');
            $this->assertNull($model->phone);
        }

        public function testRadioDownResolveValueAndSetToModelUpdateSteppingForwardAndBackward()
        {
            $form        = new RadioDropDownWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = RadioDropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS;
            $form->value = '2';

            //Step forward where the new forward step is valid
            $model       = new WorkflowModelTestItem();
            $model->radioDropDown->value = 'A1';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'radioDropDown');
            $this->assertEquals('C3', $model->radioDropDown->value);

            //Step forward where the new forwardstep is invalid
            $model       = new WorkflowModelTestItem();
            $model->radioDropDown->value = 'E5';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'radioDropDown');
            $this->assertEquals('E5', $model->radioDropDown->value);

            //Step forward where the new backward step is valid
            $form->value = '-1';
            $model       = new WorkflowModelTestItem();
            $model->radioDropDown->value = 'C3';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'radioDropDown');
            $this->assertEquals('B2', $model->radioDropDown->value);

            //Step forward where the new backward step is invalid
            $form->value = '-1';
            $model       = new WorkflowModelTestItem();
            $model->radioDropDown->value = 'A1';
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'radioDropDown');
            $this->assertEquals('A1', $model->radioDropDown->value);
        }

        public function testTagCloudResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new TagCloudWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = TagCloudWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'M2';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertEquals(0, $model->tagCloud->values->count());
            $form->resolveValueAndSetToModel($adapter, 'tagCloud');
            $this->assertEquals(1, $model->tagCloud->values->count());
            $this->assertEquals('M2', $model->tagCloud->values[0]->value);

            //Now replace M2 with M3
            $form->value = 'M3';
            $form->resolveValueAndSetToModel($adapter, 'tagCloud');
            $this->assertEquals(1, $model->tagCloud->values->count());
            $this->assertEquals('M3', $model->tagCloud->values[0]->value);
        }

        public function testTextResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new TextWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = TextWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'abc';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->string);
            $form->resolveValueAndSetToModel($adapter, 'string');
            $this->assertEquals('abc', $model->string);
        }

        public function testTextResolveValueAndSetToModelUpdateAsNull()
        {
            $form          = new TextWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type    = TextWorkflowActionAttributeForm::TYPE_STATIC_NULL;
            $model         = new WorkflowModelTestItem();
            $model->string = 'abc';
            $adapter       = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'string');
            $this->assertNull($model->string);
        }

        public function testTextAreaResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new TextAreaWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = TextAreaWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'abc';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->textArea);
            $form->resolveValueAndSetToModel($adapter, 'textArea');
            $this->assertEquals('abc', $model->textArea);
        }

        public function testTextAreaResolveValueAndSetToModelUpdateAsNull()
        {
            $form          = new TextAreaWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type    = TextAreaWorkflowActionAttributeForm::TYPE_STATIC_NULL;
            $model         = new WorkflowModelTestItem();
            $model->textArea = 'abc';
            $adapter       = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'textArea');
            $this->assertNull($model->textArea);
        }

        public function testUrlResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new UrlWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UrlWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = 'www.zurmo.com';
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertNull($model->url);
            $form->resolveValueAndSetToModel($adapter, 'url');
            $this->assertEquals('www.zurmo.com', $model->url);
        }

        public function testUrlResolveValueAndSetToModelUpdateAsNull()
        {
            $form          = new UrlWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type    = UrlWorkflowActionAttributeForm::TYPE_STATIC_NULL;
            $model         = new WorkflowModelTestItem();
            $model->url    = 'www.zurmo.com';
            $adapter       = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $form->resolveValueAndSetToModel($adapter, 'url');
            $this->assertNull($model->url);
        }

        public function testUserResolveValueAndSetToModelUpdateAsStatic()
        {
            $form        = new UserWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UserWorkflowActionAttributeForm::TYPE_STATIC;
            $form->value = self::$bobbyUserId;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel);
            $this->assertTrue($model->user->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'user');
            $this->assertEquals(self::$bobbyUserId, $model->user->id);
        }

        public function testUserResolveValueAndSetToModelUpdateAsDynamicCreatedByUser()
        {
            //Setup a triggered model that has Sarah creating and owning it.
            $super                      = Yii::app()->user->userModel;
            Yii::app()->user->userModel = User::getByUsername('sarah');
            $triggeredModel             = new WorkflowModelTestItem();
            $triggeredModel->lastName   = 'test';
            $triggeredModel->string     = 'test';
            $saved                      = $triggeredModel->save();
            $this->assertTrue($saved);
            Yii::app()->user->userModel = $super;
            //Now the super is who modified it
            $triggeredModel->string     = 'test2';
            $saved                      = $triggeredModel->save();
            $this->assertTrue($saved);

            //Test created by user
            $form        = new UserWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UserWorkflowActionAttributeForm::TYPE_DYNAMIC_CREATED_BY_USER;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, Yii::app()->user->userModel, $triggeredModel);
            $this->assertTrue($model->user->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'user');
            $this->assertEquals(self::$sarahUserId, $model->user->id);

            //Test modified by user
            $form        = new UserWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UserWorkflowActionAttributeForm::TYPE_DYNAMIC_MODIFIED_BY_USER;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, User::getByUsername('bobby'), $triggeredModel);
            $this->assertTrue($model->user->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'user');
            $this->assertEquals(self::$superUserId, $model->user->id);

            //Test triggered by user
            $form        = new UserWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UserWorkflowActionAttributeForm::TYPE_DYNAMIC_TRIGGERED_BY_USER;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, User::getByUsername('bobby'), $triggeredModel);
            $this->assertTrue($model->user->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'user');
            $this->assertEquals(self::$bobbyUserId, $model->user->id);

            //Test owner of triggered model
            $form        = new UserWorkflowActionAttributeForm('WorkflowsTestModule', 'WorkflowModelTestItem');
            $form->type  = UserWorkflowActionAttributeForm::TYPE_DYNAMIC_OWNER_OF_TRIGGERED_MODEL;
            $model       = new WorkflowModelTestItem();
            $adapter     = new WorkflowActionProcessingModelAdapter($model, User::getByUsername('bobby'), $triggeredModel);
            $this->assertTrue($model->user->id < 0);
            $form->resolveValueAndSetToModel($adapter, 'user');
            $this->assertEquals(self::$sarahUserId, $model->user->id);
        }
    }
?>