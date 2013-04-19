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

    class WorkflowAttributeFormTest extends WorkflowBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            Currency::getAll(); //make base currency
            UserTestHelper::createBasicUser('bobby');
        }

        public function testCheckBoxWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new CheckBoxWorkflowActionAttributeForm('WorkflowModelTestItem', 'boolean');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = true;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = true;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = 'invalid';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            $form->shouldSetValue = false;
            $form->value          = 'invalid, but not required to be set';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            $form->shouldSetValue = false;
            $form->value          = null;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testCheckBoxWorkflowAttributeFormSetGetAndValidate
         */
        public function testContactStateWorkflowAttributeFormSetGetAndValidate()
        {
            $contactStates        = ContactState::getAll();
            $this->assertTrue($contactStates[0]->id > 0);
            $contactState         = $contactStates[0];
            $form                 = new ContactStateWorkflowActionAttributeForm('WorkflowModelTestItem', 'likeContactState');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = $contactState->id;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testContactStateWorkflowAttributeFormSetGetAndValidate
         */
        public function testCurrencyValueWorkflowAttributeFormSetGetAndValidate()
        {
            $currency             = Currency::getByCode('USD');
            $form                 = new CurrencyValueWorkflowActionAttributeForm('WorkflowModelTestItem', 'currencyValue');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 362.24;
            $form->currencyId     = $currency->id;
            $form->currencyIdType = CurrencyValueWorkflowActionAttributeForm::CURRENCY_ID_TYPE_STATIC;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->currencyId     = null;
            $validated            = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testCurrencyValueWorkflowAttributeFormSetGetAndValidate
         */
        public function testDateWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new DateWorkflowActionAttributeForm('WorkflowModelTestItem', 'date');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = '2012-02-24';
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //Test invalid date
            $form->value          = 'invalid date';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //test valid date, but not correct format for dynamic type
            $form->type           = DateWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATE;
            $form->value          = '2012-02-24';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //Test valid date and valid format for dynamic type
            $form->value          = -8600;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = 3000;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testDateWorkflowAttributeFormSetGetAndValidate
         */
        public function testDateTimeWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new DateTimeWorkflowActionAttributeForm('WorkflowModelTestItem', 'dateTime');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = '2012-02-24 03:00:04';
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //Test invalid dateTime
            $form->value          = 'invalid date';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //test valid date, but not correct format for dynamic type
            $form->type           = DateTimeWorkflowActionAttributeForm::TYPE_DYNAMIC_FROM_TRIGGERED_DATETIME;
            $form->value          = '2012-02-24 03:00:04';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //Test valid date and valid format for dynamic type
            $form->value          = -8600;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = 3000;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testDateTimeWorkflowAttributeFormSetGetAndValidate
         */
        public function testDecimalWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new DecimalWorkflowActionAttributeForm('WorkflowModelTestItem', 'float');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 444.12;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //set the value with a length too short
            $form->value = 4;
            $validated   = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testDecimalWorkflowAttributeFormSetGetAndValidate
         */
        public function testDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new DropDownWorkflowActionAttributeForm('WorkflowModelTestItem', 'dropDown');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 'Static 1';
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //Test invalid dropDown value
            $form->value          = 123123;
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //test valid date, but not correct format for dynamic type
            $form->type           = DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS;
            $form->value          = 'Static 1';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //Test valid date and valid format for dynamic type
            $form->value          = -8600;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = 3000;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testDropDownWorkflowAttributeFormSetGetAndValidate
         */
        public function testEmailWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new EmailWorkflowActionAttributeForm('Email', 'emailAddress');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value = 'info@zurmo.com';
            $validated   = $form->validate();
            $this->assertTrue($validated);

            //try with an invalid email address
            $form->value = 'somethingNotAnEmail';
            $validated   = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testEmailWorkflowAttributeFormSetGetAndValidate
         */
        public function testIntegerWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new IntegerWorkflowActionAttributeForm('WorkflowModelTestItem', 'integer');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value = 12412;
            $validated   = $form->validate();
            $this->assertTrue($validated);

            //set the value with a length too short
            $form->value = 4;
            $validated   = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testIntegerWorkflowAttributeFormSetGetAndValidate
         */
        public function testMultiSelectDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new MultiSelectDropDownWorkflowActionAttributeForm('WorkflowModelTestItem', 'radioDropDown');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value = array('Multi Value 1', 'Multi Value 2');
            $validated   = $form->validate();
            $this->assertTrue($validated);

            //invalid string, needs to be array
            $form->value = 'should be an array';
            $validated   = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testMultiSelectDropDownWorkflowAttributeFormSetGetAndValidate
         */
        public function testPhoneWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new PhoneWorkflowActionAttributeForm('WorkflowModelTestItem', 'radioDropDown');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value = '1112223344';
            $validated   = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testPhoneWorkflowAttributeFormSetGetAndValidate
         */
        public function testRadioDropDownWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new RadioDropDownWorkflowActionAttributeForm('WorkflowModelTestItem', 'radioDropDown');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 'Radio Static 1';
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //Test invalid radioDropDown value
            $form->value          = 123123;
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //test valid date, but not correct format for dynamic type
            $form->type           = DropDownWorkflowActionAttributeForm::TYPE_DYNAMIC_STEP_FORWARD_OR_BACKWARDS;
            $form->value          = 'Static 1';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //Test valid date and valid format for dynamic type
            $form->value          = -8600;
            $validated            = $form->validate();
            $this->assertTrue($validated);

            $form->value          = 3000;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testRadioDropDownWorkflowAttributeFormSetGetAndValidate
         */
        public function testTagCloudWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new TagCloudWorkflowActionAttributeForm('WorkflowModelTestItem', 'tagCloud');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = array('Tag Value 1', 'Tag Value 2');
            $validated            = $form->validate();
            $this->assertTrue($validated);

            //invalid string, needs to be array
            $form->value = 'should be an array';
            $validated   = $form->validate();
            $this->assertFalse($validated);
        }

        /**
         * @depends testTagCloudWorkflowAttributeFormSetGetAndValidate
         */
        public function testTextWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new TextWorkflowActionAttributeForm('WorkflowModelTestItem', 'string');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 'jason';
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testTextWorkflowAttributeFormSetGetAndValidate
         */
        public function testUserWorkflowAttributeFormSetGetAndValidate()
        {
            $bobby                           = User::getByUsername('bobby');
            $form                            = new UserWorkflowActionAttributeForm('WorkflowModelTestItem', 'owner');
            $form->type                      = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue            = true;
            $form->value                     = $bobby->id;
            $validated                       = $form->validate();
            $this->assertTrue($validated);

            //Test invalid value
            $form->value          = 'invalid value, should be integer';
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //test with a value still, it shouldn't validate because with dynamic user, it doesn't need a value
            $form->type           = UserWorkflowActionAttributeForm::TYPE_DYNAMIC_CREATED_BY_USER;
            $validated            = $form->validate();
            $this->assertFalse($validated);

            //Test without a value and it should pass
            $form->value          = null;
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testUserWorkflowAttributeFormSetGetAndValidate
         */
        public function testTextAreaWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new TextAreaWorkflowActionAttributeForm('WorkflowModelTestItem', 'textArea');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 'a description';
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }

        /**
         * @depends testTextAreaWorkflowAttributeFormSetGetAndValidate
         */
        public function testUrlWorkflowAttributeFormSetGetAndValidate()
        {
            $form                 = new UrlWorkflowActionAttributeForm('WorkflowModelTestItem', 'url');
            $form->type           = WorkflowActionAttributeForm::TYPE_STATIC;
            $form->shouldSetValue = true;
            $form->value          = 'http://www.zurmo.com';
            $validated            = $form->validate();
            $this->assertTrue($validated);
        }
    }
?>