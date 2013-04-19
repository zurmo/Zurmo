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
    class EmailTemplateTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetEmailTemplateById()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Test Email Template';
            $emailTemplate->htmlContent     = 'Test html Content';
            $emailTemplate->textContent     = 'Test text Content';
            $this->assertTrue($emailTemplate->save());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,    $emailTemplate->type);
            $this->assertEquals('Test subject',                 $emailTemplate->subject);
            $this->assertEquals('Test Email Template',          $emailTemplate->name);
            $this->assertEquals('Test html Content',            $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content',            $emailTemplate->textContent);
            $this->assertEquals(1, count(EmailTemplate::getAll()));
        }

        public function testAtLeastOneContentFieldIsRequired()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Another Test Email Template';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(2, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertTrue(array_key_exists('htmlContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals(1, count($errorMessages['htmlContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['htmlContent'][0]);
        }

        public function testModelClassNameExists()
        {
            // test against a class name that doesn't exist
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->modelClassName  = 'RaNdOmTeXt';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name does not exist.', $errorMessages['modelClassName'][0]);
            // test against a class name thats not a model
            $emailTemplate->modelClassName  = 'TestSuite';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name is not a valid Model class.', $errorMessages['modelClassName'][0]);
            // test against a model that is indeed a class
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(2, count(EmailTemplate::getAll()));
        }

        public function testMergeTagsValidation()
        {
            // test against a invalid merge tags
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content [[TEXT__INVALID^MERGE^TAG]]';
            $emailTemplate->htmlContent     = 'Html Content [[HTMLINVALIDMERGETAG]]';
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(2, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertTrue(array_key_exists('htmlContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals(1, count($errorMessages['htmlContent']));
            $this->assertTrue(strpos($errorMessages['textContent'][0], 'TEXT__INVALID^MERGE^TAG') !== false);
            $this->assertTrue(strpos($errorMessages['htmlContent'][0], 'HTMLINVALIDMERGETAG') !== false);
            // test with no merge tags
            $emailTemplate->textContent    = 'Text Content without tags';
            $emailTemplate->htmlContent    = 'Html Content without tags';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(3, count(EmailTemplate::getAll()));
            // test with valid merge tags
            $emailTemplate->textContent    = 'Name : [[FIRST^NAME]] [[LAST^NAME]]';
            $emailTemplate->htmlContent    = '<b>Name : [[FIRST^NAME]] [[LAST^NAME]]</b>';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(3, count(EmailTemplate::getAll()));
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetEmailTemplateByName()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Test Email Template', $emailTemplate[0]->name);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetLabel()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Email Template',  $emailTemplate[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Email Templates', $emailTemplate[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /*
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDeleteEmailTemplate()
        {
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(3, count($emailTemplates));
            $emailTemplates[0]->delete();
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
        }
    }
?>