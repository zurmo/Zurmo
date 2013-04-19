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

    class DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientFormTest extends WorkflowBaseTest
    {
        public $freeze = false;

        protected static $super;

        protected static $bobby;

        protected static $sarah;

        protected static $jimmy;

        protected static $jimmy2;

        protected static $jimmy3;

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

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super  = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            assert($super->save()); // Not Coding Standard
            $bobby  = UserTestHelper::createBasicUserWithEmailAddress('bobby');
            $sarah  = UserTestHelper::createBasicUserWithEmailAddress('sarah');
            $jimmy  = UserTestHelper::createBasicUserWithEmailAddress('jimmy');
            $jimmy2 = UserTestHelper::createBasicUserWithEmailAddress('jimmy2');
            $jimmy3 = UserTestHelper::createBasicUserWithEmailAddress('jimmy3');
            self::$super  = $super;
            self::$bobby  = $bobby;
            self::$sarah  = $sarah;
            self::$jimmy  = $jimmy;
            self::$jimmy2 = $jimmy2;
            self::$jimmy3 = $jimmy3;
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveRecipientsAsUniquePeopleInvalid()
        {
            $existingRecipient = new EmailMessageRecipient();
            $existingRecipient->personOrAccount  = self::$sarah;
            $existingRecipient2 = new EmailMessageRecipient();
            $existingRecipient2->personOrAccount = self::$sarah;
            $existingRecipients = array($existingRecipient, $existingRecipient2);
            $newRecipient = new EmailMessageRecipient();
            $newRecipient->personOrAccount = self::$bobby;
            $newRecipients = array($newRecipient);
            DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                resolveRecipientsAsUniquePeople($existingRecipients, $newRecipients);
        }

        /**
         * @depends testResolveRecipientsAsUniquePeopleInvalid
         */
        public function testResolveRecipientsAsUniquePeople()
        {
            $existingRecipient = new EmailMessageRecipient();
            $existingRecipient->personOrAccount  = self::$sarah;
            $existingRecipient2 = new EmailMessageRecipient();
            $existingRecipient2->personOrAccount = self::$jimmy;
            $existingRecipients = array($existingRecipient, $existingRecipient2);
            $newRecipient = new EmailMessageRecipient();
            $newRecipient->personOrAccount = self::$bobby;
            $newRecipient2 = new EmailMessageRecipient();
            $newRecipient2->personOrAccount = self::$jimmy2;
            $newRecipients = array($newRecipient, $newRecipient2);
            $recipients    = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                             resolveRecipientsAsUniquePeople($existingRecipients, $newRecipients);
            $this->assertEquals(4, count($recipients));
            $this->assertEquals(self::$sarah->id,  $recipients[0]->personOrAccount->id);
            $this->assertEquals(self::$jimmy->id,  $recipients[1]->personOrAccount->id);
            $this->assertEquals(self::$bobby->id,  $recipients[2]->personOrAccount->id);
            $this->assertEquals(self::$jimmy2->id, $recipients[3]->personOrAccount->id);

            //Now see when there is one duplicate, sarah
            $existingRecipient = new EmailMessageRecipient();
            $existingRecipient->personOrAccount  = self::$sarah;
            $existingRecipient2 = new EmailMessageRecipient();
            $existingRecipient2->personOrAccount = self::$jimmy;
            $existingRecipients = array($existingRecipient, $existingRecipient2);
            $newRecipient = new EmailMessageRecipient();
            $newRecipient->personOrAccount = self::$bobby;
            $newRecipient2 = new EmailMessageRecipient();
            $newRecipient2->personOrAccount = self::$sarah;
            $newRecipients = array($newRecipient, $newRecipient2);
            $recipients    = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                             resolveRecipientsAsUniquePeople($existingRecipients, $newRecipients);
            $this->assertEquals(3, count($recipients));
            $this->assertEquals(self::$sarah->id,  $recipients[0]->personOrAccount->id);
            $this->assertEquals(self::$jimmy->id,  $recipients[1]->personOrAccount->id);
            $this->assertEquals(self::$bobby->id,  $recipients[2]->personOrAccount->id);
        }

        /**
         * @depends testResolveRecipientsAsUniquePeople
         */
        public function testGetRelationValuesAndLabels()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $relationValuesAndLabels = $form->getRelationValuesAndLabels();
            $this->assertEquals(5, count($relationValuesAndLabels));
        }

        /**
         * @depends testGetRelationValuesAndLabels
         */
        public function testMakeRecipientsForADerivedRelation()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem',
                        Workflow::TYPE_ON_SAVE);
            $form->relation        = 'model5ViaItem';
            $form->dynamicUserType = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                                     DYNAMIC_USER_TYPE_OWNER;

            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            //Test without any related model
            $recipients = $form->makeRecipients($model, self::$sarah);
            $this->assertEquals(0, count($recipients));

            //Test with a related model
            $model5 = new WorkflowModelTestItem5();
            $model5->name  = 'model 5';
            $model5->owner = self::$sarah;
            $model5->workflowItems->add($model);
            $this->assertTrue($model5->save());
            $this->assertTrue($model5->id > 0);
            $recipients = $form->makeRecipients($model, self::$sarah);
            $this->assertEquals(1, count($recipients));
            $this->assertTrue($recipients[0]->personOrAccount->isSame(self::$sarah));
        }

        /**
         * @depends testMakeRecipientsForADerivedRelation
         */
        public function testMakeRecipientsForAnInferredRelation()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem5',
                        Workflow::TYPE_ON_SAVE);
            $form->relation        = 'WorkflowModelTestItem__workflowItems__Inferred';
            $form->dynamicUserType = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                                     DYNAMIC_USER_TYPE_OWNER;

            //Test without any related model
            $recipients = $form->makeRecipients(new WorkflowModelTestItem5(), self::$sarah);
            $this->assertEquals(0, count($recipients));

            //Test with a related model
            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            $model5 = new WorkflowModelTestItem5();
            $model5->name  = 'model 5';
            $model5->owner = self::$sarah;
            $model5->workflowItems->add($model);
            $this->assertTrue($model5->save());
            $this->assertTrue($model5->id > 0);
            $recipients = $form->makeRecipients($model5, self::$sarah);
            $this->assertEquals(1, count($recipients));
            $this->assertTrue($recipients[0]->personOrAccount->isSame(self::$super));
        }

        /**
         * @depends testMakeRecipientsForAnInferredRelation
         */
        public function testMakeRecipientsForAHasManyRelation()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem',
                        Workflow::TYPE_ON_SAVE);
            $form->relation        = 'hasMany';
            $form->dynamicUserType = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                                     DYNAMIC_USER_TYPE_OWNER;

            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            //Test without any related model
            $recipients = $form->makeRecipients($model, self::$sarah);
            $this->assertEquals(0, count($recipients));

            //Test with a related model
            $model3 = new WorkflowModelTestItem3();
            $model3->name  = 'model 3';
            $model3->owner = self::$bobby;
            $this->assertTrue($model3->save());
            $model->hasMany->add($model3);
            $this->assertTrue($model->save());
            $recipients = $form->makeRecipients($model, self::$bobby);
            $this->assertEquals(1, count($recipients));
            $this->assertTrue($recipients[0]->personOrAccount->isSame(self::$bobby));
        }

        /**
         * @depends testMakeRecipientsForAHasManyRelation
         */
        public function testMakeRecipientsForAHasOneRelation()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem',
                        Workflow::TYPE_ON_SAVE);
            $form->relation        = 'hasOne';
            $form->dynamicUserType = DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm::
                                     DYNAMIC_USER_TYPE_OWNER;

            $model = new WorkflowModelTestItem();
            $model->lastName = 'lastName';
            $model->string   = 'string';
            $this->assertTrue($model->save());
            //Test without any related model
            $recipients = $form->makeRecipients($model, self::$sarah);
            $this->assertEquals(0, count($recipients));

            //Test with a related model
            $model2 = new WorkflowModelTestItem2();
            $model2->name = 'model 2';
            $this->assertTrue($model2->save());
            $this->assertTrue($model2->id > 0);
            $model->hasOne = $model2;
            $this->assertTrue($model->save());
            $this->assertTrue($model->hasOne->id > 0);
            $recipients = $form->makeRecipients($model, self::$sarah);
            $this->assertEquals(1, count($recipients));
            $this->assertTrue($recipients[0]->personOrAccount->isSame(self::$super));
        }

        /**
         * Test various relations to get full coverage on resolveModelClassName()
         * @depends testMakeRecipientsForAHasOneRelation
         */
        public function testGetDynamicUserTypesAndLabels()
        {
            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem',
                    Workflow::TYPE_ON_SAVE);
            $form->relation = 'hasOne';
            $this->assertEquals(6, count($form->getDynamicUserTypesAndLabels()));

            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem5',
                Workflow::TYPE_ON_SAVE);
            $form->relation        = 'WorkflowModelTestItem__workflowItems__Inferred';
            $this->assertEquals(6, count($form->getDynamicUserTypesAndLabels()));

            $form = new DynamicTriggeredModelRelationUserWorkflowEmailMessageRecipientForm('WorkflowModelTestItem',
                Workflow::TYPE_ON_SAVE);
            $form->relation        = 'model5ViaItem';
            $this->assertEquals(6, count($form->getDynamicUserTypesAndLabels()));
        }
    }
?>