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

    class EmailMessageForWorkflowFormTest extends WorkflowBaseTest
    {
        public function testValidate()
        {
            $message       = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $validated     = $message->validate();
            $this->assertFalse($validated);
            $errors        = $message->getErrors();
            $compareErrors = array('recipientsValidation'  => array('At least one recipient must be added'));
            $this->assertEquals($compareErrors, $errors);
            //Ensure validation will pass
            $message->sendAfterDurationSeconds = 86400;
            $message->emailTemplateId          = 5;
            $message->sendFromType             = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type' => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $validated = $message->validate();
            $errors    = $message->getErrors();
            $this->assertTrue($validated);
            //Test validation with SEND_FROM_TYPE_CUSTOM
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type' => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = 5;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $validated     = $message->validate();
            $this->assertFalse($validated);
            $errors        = $message->getErrors();
            $compareErrors = array('sendFromName'  => array('From Name cannot be blank.'),
                                   'sendFromAddress' => array('From Email Address cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $validated     = $message->validate();
            $errors        = $message->getErrors();
            $this->assertTrue($validated);
        }

        /**
         * @depends testValidate
         */
        public function testMissingEmailTemplateWillNotValidate()
        {
            $message = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $message->sendAfterDurationSeconds = 86400;
            $message->sendFromType             = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $recipients = array(array('type' => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));
            $validated = $message->validate();
            $errors    = $message->getErrors();
            $this->assertFalse($validated);
            $compareErrors = array('emailTemplateId'  => array('Template cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);
            //Now fill in emailTemplateId and it should pass
            $message->emailTemplateId = 5;
            $validated                = $message->validate();
            $this->assertTrue($validated);
        }
    }
?>