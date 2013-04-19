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
     * EmailTemplates Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class EmailTemplatesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $super;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            // Setup test data owned by the super user.
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_WORKFLOW, 'Test Subject', 'Contact',
                                                                    'Test Name', 'Test HtmlContent', 'Test TextContent');
            EmailTemplateTestHelper::createEmailTemplateByName(EmailTemplate::TYPE_CONTACT, 'Test Subject1', 'Contact',
                                                                    'Test Name1', 'Test HtmlContent1', 'Test TextContent1');
        }

        public function setUp()
        {
            parent::setUp();
            $this->super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            // Test all default controller actions that do not require any POST/GET variables to be passed.
            // This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/index');
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserListForMarketingAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name1'), 1);
            $this->assertEquals (substr_count($content, 'Clark Kent'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $this->assertEquals (1,                     count($emailTemplates));
        }

        /**
         * @depends testSuperUserListForMarketingAction
         */
        public function testSuperUserListForWorkflowAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->assertTrue   (strpos($content,       'Email Templates</title></head>') !== false);
            $this->assertTrue   (strpos($content,       '1 result') !== false);
            $this->assertEquals (substr_count($content, 'Test Name'), 1);
            $this->assertEquals (substr_count($content, 'Clark Kent'), 1);
            $emailTemplates = EmailTemplate::getByType(EmailTemplate::TYPE_WORKFLOW);
            $this->assertEquals (1,                     count($emailTemplates));
        }

        /**
         * @depends testSuperUserListForWorkflowAction
         */
        public function testSuperUserCreateActionForWorkflow()
        {
            // Create a new emailTemplate and test validator.
            $this->setGetArray(array('type' => EmailTemplate::TYPE_WORKFLOW));
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'name'              => 'New Test Workflow EmailTemplate',
                'subject'           => 'New Test Subject')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, '<select name="EmailTemplate[modelClassName]" id="EmailTemplate_modelClassName_value"') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            $this->assertTrue(strpos($content, 'Module cannot be blank.') !== false);

            // Create a new emailTemplate and test merge tags validator.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'modelClassName'    => 'Meeting',
                'name'              => 'New Test Workflow EmailTemplate',
                'subject'           => 'New Test Subject',
                'textContent'       => 'This is text content [[INVALID^TAG]]',
                'htmlContent'       => 'This is Html content [[INVALIDTAG]]',
            )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, '<select name="EmailTemplate[modelClassName]" id="EmailTemplate_modelClassName_value">') !== false);
            $this->assertTrue(strpos($content, '<option value="Meeting" selected="selected">Meetings</option>') !== false);
            $this->assertTrue(strpos($content, 'INVALID^TAG') !== false);
            $this->assertTrue(strpos($content, 'INVALIDTAG') !== false);
            $this->assertEquals(2, substr_count($content, 'INVALID^TAG'));
            $this->assertEquals(2, substr_count($content, 'INVALIDTAG'));

            // Create a new emailTemplate and save it.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_WORKFLOW,
                'name'              => 'New Test Workflow EmailTemplate',
                'modelClassName'    => 'Contact',
                'subject'           => 'New Test Subject [[FIRST^NAME]]',
                'textContent'       => 'New Text Content [[FIRST^NAME]]')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertTrue  ($emailTemplate->id > 0);
            $this->assertEquals('New Test Subject [[FIRST^NAME]]', $emailTemplate->subject);
            $this->assertEquals('New Text Content [[FIRST^NAME]]', $emailTemplate->textContent);
            $this->assertTrue  ($emailTemplate->owner == $this->super);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplate->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
        }

        /**
         * @depends testSuperUserCreateActionForWorkflow
         */
        public function testSuperUserCreateActionForMarketing()
        {
            // Create a new emailTemplate and test validator.
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'name'              => 'New Test EmailTemplate',
                'subject'           => 'New Test Subject')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);
            $this->assertFalse(strpos($content, 'Model Class Name cannot be blank.') !== false);

            // Create a new emailTemplate and test merge tags validator.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'modelClassName'    => 'Contact',
                'name'              => 'New Test EmailTemplate',
                'subject'           => 'New Test Subject',
                'textContent'       => 'This is text content [[INVALID^TAG]]',
                'htmlContent'       => 'This is Html content [[INVALIDTAG]]',
                )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->assertTrue(strpos($content, 'Create Email Template') !== false);
            $this->assertFalse(strpos($content, '<select name="EmailTemplate[type]" id="EmailTemplate_type">') !== false);
            $this->assertTrue(strpos($content, 'INVALID^TAG') !== false);
            $this->assertTrue(strpos($content, 'INVALIDTAG') !== false);
            $this->assertEquals(2, substr_count($content, 'INVALID^TAG'));
            $this->assertEquals(2, substr_count($content, 'INVALIDTAG'));

            // Create a new emailTemplate and save it.
            $this->setPostArray(array('EmailTemplate' => array(
                'type'              => EmailTemplate::TYPE_CONTACT,
                'name'              => 'New Test EmailTemplate',
                'modelClassName'    => 'Contact',
                'subject'           => 'New Test Subject [[FIRST^NAME]]',
                'textContent'       => 'New Text Content [[FIRST^NAME]]')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplateId    = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test EmailTemplate');
            $emailTemplate      = EmailTemplate::getById($emailTemplateId);
            $this->assertTrue  ($emailTemplateId > 0);
            $this->assertEquals('New Test Subject [[FIRST^NAME]]', $emailTemplate->subject);
            $this->assertEquals('New Text Content [[FIRST^NAME]]', $emailTemplate->textContent);
            $this->assertTrue  ($emailTemplate->owner == $this->super);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplateId));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(4, count($emailTemplates));
        }

        /**
         * @depends testSuperUserCreateActionForMarketing
         */
        public function testSuperUserEditActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_name" name="EmailTemplate[name]"' .
                                        ' type="text" maxlength="64" value="'. $emailTemplate->name . '" />') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_subject" name="EmailTemplate[subject]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->subject . '" />') !== false);
            $this->assertTrue(strpos($content, '<textarea id="EmailTemplate_textContent" name="EmailTemplate[textContent]"' .
                ' rows="6" cols="50">'. $emailTemplate->textContent . '</textarea>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'EmailTemplate_htmlContent\' name=\'EmailTemplate[htmlContent]\'>' .
                $emailTemplate->htmlContent . '</textarea>') !== false);

            // Test having a failed validation on the emailTemplate during save.
            $this->setGetArray (array('id' => $emailTemplateId));
            $this->setPostArray(array('EmailTemplate' => array('name' => '', 'htmlContent' => '', 'textContent' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, 'Name cannot be blank') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);

            // Send a valid post and verify saved data.
            $this->setPostArray(array('EmailTemplate' => array(
                                    'name' => 'New Test Email Template 00',
                                    'subject' => 'New Subject 00',
                                    'type' => EmailTemplate::TYPE_CONTACT,
                                    'htmlContent' => 'New HTML Content 00',
                                    'textContent' => 'New Text Content 00')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject 00', $emailTemplate->subject);
            $this->assertEquals('New Test Email Template 00', $emailTemplate->name);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT, $emailTemplate->type);
            $this->assertEquals('New Text Content 00', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content 00', $emailTemplate->htmlContent);
        }

        /**
         * @depends testSuperUserCreateActionForMarketing
         */
        public function testSuperUserEditActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow EmailTemplate');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_name" name="EmailTemplate[name]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->name . '" />') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailTemplate_subject" name="EmailTemplate[subject]"' .
                ' type="text" maxlength="64" value="'. $emailTemplate->subject . '" />') !== false);
            $this->assertTrue(strpos($content, '<textarea id="EmailTemplate_textContent" name="EmailTemplate[textContent]"' .
                ' rows="6" cols="50">'. $emailTemplate->textContent . '</textarea>') !== false);
            $this->assertTrue(strpos($content, '<textarea id=\'EmailTemplate_htmlContent\' name=\'EmailTemplate[htmlContent]\'>' .
                $emailTemplate->htmlContent . '</textarea>') !== false);

            // Test having a failed validation on the emailTemplate during save.
            $this->setGetArray (array('id' => $emailTemplateId));
            $this->setPostArray(array('EmailTemplate' => array('name' => '', 'htmlContent' => '', 'textContent' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertTrue(strpos($content, 'Name cannot be blank') !== false);
            $this->assertTrue(strpos($content, 'Please provide at least one of the contents field.') !== false);

            // Send a valid post and verify saved data.
            $this->setPostArray(array('EmailTemplate' => array(
                'name' => 'New Test Workflow Email Template 00',
                'subject' => 'New Subject 00',
                'type' => EmailTemplate::TYPE_WORKFLOW,
                'htmlContent' => 'New HTML Content 00',
                'textContent' => 'New Text Content 00')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/edit');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals('New Subject 00', $emailTemplate->subject);
            $this->assertEquals('New Test Workflow Email Template 00', $emailTemplate->name);
            $this->assertEquals(EmailTemplate::TYPE_WORKFLOW, $emailTemplate->type);
            $this->assertEquals('New Text Content 00', $emailTemplate->textContent);
            $this->assertEquals('New HTML Content 00', $emailTemplate->htmlContent);
        }

        /**
         * @depends testSuperUserEditActionForMarketing
         */
        public function testSuperUserDetailsActionForMarketing()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Email Template 00');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $types = EmailTemplate::getTypeDropDownArray();
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a class="active-tab" href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">') !== false);
        }

        /**
         * @depends testSuperUserEditActionForWorkflow
         */
        public function testSuperUserDetailsActionForWorkflow()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $types = EmailTemplate::getTypeDropDownArray();
            $this->setGetArray(array('id' => $emailTemplateId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">' . $emailTemplate->name . '</span>') !== false);
            $this->assertTrue(strpos($content, '<span>Options</span>') !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/edit?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, 'emailTemplates/default/delete?id=' . $emailTemplateId) !== false);
            $this->assertTrue(strpos($content, '<th>Name</th><td colspan="1">'. $emailTemplate->name . '</td>') !== false);
            $this->assertTrue(strpos($content, '<th>Subject</th><td colspan="1">'. $emailTemplate->subject . '</td>') !== false);
            $this->assertTrue(strpos($content, '<div class="tabs-nav"><a class="active-tab" href="#tab1">') !== false);
            $this->assertTrue(strpos($content, '<a href="#tab2">') !== false);
        }

        /**
         * @depends testSuperUserDetailsActionForMarketing
         */
        public function testSuperUserDeleteAction()
        {
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Email Template 00');
            // Delete an emailTemplate.
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $content = $this->runControllerWithRedirectExceptionAndGetContent('emailTemplates/default/delete');
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'New Test Workflow Email Template 00');
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $content = $this->runControllerWithRedirectExceptionAndGetContent('emailTemplates/default/delete');
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
        }
    }
?>
