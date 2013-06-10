<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Testing the views for configuring outbound email
     */
    class EmailConfigurationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $user = new User();
            $user->username           = 'super2';
            $user->title->value       = 'Mr.';
            $user->firstName          = 'Clark2';
            $user->lastName           = 'Kent2';
            $user->setPassword('super2');
            $saved = $user->save();
            assert($saved); // Not Coding Standard

            $group = Group::getByName('Super Administrators');
            $group->users->add($user);
            $saved = $group->save();
            assert($saved); // Not Coding Standard
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEdit');
        }

        public function testSuperUserModifyEmailSmtpConfiguration()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $super2 = User::getByUsername('super2');

            //Change email settings
            $this->resetGetArray();
            $this->resetPostArray();
            $this->setPostArray(array('EmailSmtpConfigurationForm' => array(
                                    'host'                              => 'abc',
                                    'port'                              => '565',
                                    'username'                          => 'myuser',
                                    'password'                          => 'apassword',
                                    'security'                          => 'ssl')));
            $this->runControllerWithRedirectExceptionAndGetContent('emailMessages/default/configurationEditOutbound');
            $this->assertEquals('Email configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            //Confirm the setting did in fact change correctly
            $emailHelper = new EmailHelper;
            $this->assertEquals('smtp',         Yii::app()->emailHelper->outboundType);
            $this->assertEquals('abc',          Yii::app()->emailHelper->outboundHost);
            $this->assertEquals('565',          Yii::app()->emailHelper->outboundPort);
            $this->assertEquals('myuser',       Yii::app()->emailHelper->outboundUsername);
            $this->assertEquals('apassword',    Yii::app()->emailHelper->outboundPassword);
        }

        public function testActionConfigurationEditImapDefaultTypeIsArchiving()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertTrue(strpos($content, '<div id="EmailArchivingConfigurationEditAndDetailsView" '.
                                                'class="AdministrativeArea AppContent ImapConfigurationEditAndDetails' .
                                                'View EditAndDetailsView DetailsView ModelView '.
                                                'ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title">') !== false);
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">Email Configuration</span>') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<div class="attributesContainer">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width">') !== false);
            $this->assertTrue(strpos($content, '<div class="panel">') !== false);
            $this->assertTrue(strpos($content, '<div class="panelTitle">Email Archiving '.
                                                'Configuration (IMAP)</div>') !== false);
            $this->assertTrue(strpos($content, '<label for="EmailArchivingConfigurationForm_imapHost" class="required"' .
                                                '>Host <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailArchivingConfigurationForm_imapHost" ' .
                                                'name="EmailArchivingConfigurationForm[imapHost]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapPort" ' .
                                                'class="required">Port <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_imapPort" ' .
                                                'name="EmailArchivingConfigurationForm[imapPort]" ' .
                                                'type="text" value="143"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapUsername" ' .
                                                'class="required">Username <span class="required">*' .
                                                '</span></label>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_' .
                                                'imapUsername" name="EmailArchivingConfigurationForm[imapUsername]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapPassword" ' .
                                                'class="required">Password <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input name="EmailArchivingConfigurationForm' .
                                                '[imapPassword]" id="EmailArchivingConfigurationForm_imapPassword" ' .
                                                'type="password" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapSSL">SSL ' .
                                                'connection</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytEmailArchivingConfigurationForm_imapSSL" ' .
                                                'type="hidden" value="0" name="EmailArchivingConfigurationForm' .
                                                '[imapSSL]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox">') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailArchivingConfigurationForm_imapSSL" ' .
                                                'name="EmailArchivingConfigurationForm[imapSSL]" ' .
                                                'value="1" type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapFolder" ' .
                                                'class="required">Folder <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_imapFolder" ' .
                                                'name="EmailArchivingConfigurationForm[imapFolder]" ' .
                                                'type="text" maxlength="64" value="INBOX"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_testImapConnection"' .
                                                '>Test IMAP connection</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><span><a id="testImapConnection" ' .
                                                'class="EmailTestingButton z-button" href="#"><span class="z-label">' .
                                                'Test Connection</span></a></span></td>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar">') !== false);
            $this->assertTrue(strpos($content, '<div class="view-toolbar-container clearfix dock">') !== false);
            $this->assertTrue(strpos($content, '<div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Return to Admin Menu</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer-edit-form">') !== false);
            $this->assertTrue(strpos($content, '<div id="FlashMessageView">') !== false);
            $this->assertTrue(strpos($content, '<div id = "FlashMessageBar">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalGameNotificationContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="FooterView">') !== false);
            $this->assertTrue(strpos($content, '<div class="ui-chooser">') !== false);
        }

        /**
         * @expectedException CHttpException
         */
        public function testActionConfigurationEditImapThrowsExceptionOnTypeLessThanZero()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 0));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
        }

        /**
         * @expectedException CHttpException
         */
        public function testActionConfigurationEditImapThrowsExceptionOnTypeGreaterThanTwo()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 3));
            $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
        }

        public function testActionConfigurationEditImapTypeOneIsArchiving()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 1));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertTrue(strpos($content, '<div id="EmailArchivingConfigurationEditAndDetailsView" '.
                                                'class="AdministrativeArea AppContent ImapConfigurationEditAndDetails' .
                                                'View EditAndDetailsView DetailsView ModelView '.
                                                'ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title">') !== false);
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">Email Configuration</span>') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<div class="attributesContainer">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width">') !== false);
            $this->assertTrue(strpos($content, '<div class="panel">') !== false);
            $this->assertTrue(strpos($content, '<div class="panelTitle">Email Archiving '.
                                                'Configuration (IMAP)</div>') !== false);
            $this->assertTrue(strpos($content, '<label for="EmailArchivingConfigurationForm_imapHost" class="required"' .
                                                '>Host <span class="required">*</span></label>') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailArchivingConfigurationForm_imapHost" ' .
                                                'name="EmailArchivingConfigurationForm[imapHost]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapPort" ' .
                                                'class="required">Port <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_imapPort" ' .
                                                'name="EmailArchivingConfigurationForm[imapPort]" ' .
                                                'type="text" value="143"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapUsername" ' .
                                                'class="required">Username <span class="required">*' .
                                                '</span></label>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_' .
                                                'imapUsername" name="EmailArchivingConfigurationForm[imapUsername]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapPassword" ' .
                                                'class="required">Password <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input name="EmailArchivingConfigurationForm' .
                                                '[imapPassword]" id="EmailArchivingConfigurationForm_imapPassword" ' .
                                                'type="password" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapSSL">SSL ' .
                                                'connection</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytEmailArchivingConfigurationForm_imapSSL" ' .
                                                'type="hidden" value="0" name="EmailArchivingConfigurationForm' .
                                                '[imapSSL]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox">') !== false);
            $this->assertTrue(strpos($content, '<input id="EmailArchivingConfigurationForm_imapSSL" ' .
                                                'name="EmailArchivingConfigurationForm[imapSSL]" ' .
                                                'value="1" type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_imapFolder" ' .
                                                'class="required">Folder <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="EmailArchivingConfigurationForm_imapFolder" ' .
                                                'name="EmailArchivingConfigurationForm[imapFolder]" ' .
                                                'type="text" maxlength="64" value="INBOX"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="EmailArchivingConfigurationForm_testImapConnection"' .
                                                '>Test IMAP connection</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><span><a id="testImapConnection" ' .
                                                'class="EmailTestingButton z-button" href="#"><span class="z-label">' .
                                                'Test Connection</span></a></span></td>') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar">') !== false);
            $this->assertTrue(strpos($content, '<div class="view-toolbar-container clearfix dock">') !== false);
            $this->assertTrue(strpos($content, '<div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Return to Admin Menu</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer-edit-form">') !== false);
            $this->assertTrue(strpos($content, '<div id="FlashMessageView">') !== false);
            $this->assertTrue(strpos($content, '<div id = "FlashMessageBar">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalGameNotificationContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="FooterView">') !== false);
            $this->assertTrue(strpos($content, '<div class="ui-chooser">') !== false);
        }

        public function testActionConfigurationEditImapTypeTwoIsBounce()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertTrue(strpos($content, '<div id="BounceConfigurationEditAndDetailsView" ' .
                                                'class="AdministrativeArea AppContent ImapConfigurationEditAndDetails' .
                                                'View EditAndDetailsView DetailsView ModelView ' .
                                                'ConfigurableMetadataView MetadataView">') !== false);
            $this->assertTrue(strpos($content, '<div class="wrapper">') !== false);
            $this->assertTrue(strpos($content, '<h1><span class="truncated-title">') !== false);
            $this->assertTrue(strpos($content, '<span class="ellipsis-content">Email Configuration</span>') !== false);
            $this->assertTrue(strpos($content, '<div class="wide form">') !== false);
            $this->assertTrue(strpos($content, '<div class="attributesContainer">') !== false);
            $this->assertTrue(strpos($content, '<div class="left-column full-width">') !== false);
            $this->assertTrue(strpos($content, '<div class="panel">') !== false);
            $this->assertTrue(strpos($content, '<div class="panelTitle">Bounce Configuration (IMAP)</div>') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapHost" class="required">' .
                                                'Host <span class="required">*</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="BounceConfigurationForm_imapHost" ' .
                                                'name="BounceConfigurationForm[imapHost]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapPort" ' .
                                                'class="required">Port <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="BounceConfigurationForm_imapPort" ' .
                                                'name="BounceConfigurationForm[imapPort]" type="text"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapUsername" ' .
                                                'class="required">Username <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="BounceConfigurationForm_imapUsername" ' .
                                                'name="BounceConfigurationForm[imapUsername]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapPassword" ' .
                                                'class="required">Password <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input name="BounceConfigurationForm[imapPassword]" ' .
                                                'id="BounceConfigurationForm_imapPassword" ' .
                                                'type="password" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapSSL">SSL connection' .
                                                '</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="ytBounceConfigurationForm_imapSSL" ' .
                                                'type="hidden" value="0" ' .
                                                'name="BounceConfigurationForm[imapSSL]"') !== false);
            $this->assertTrue(strpos($content, '<label class="hasCheckBox">') !== false);
            $this->assertTrue(strpos($content, '<input id="BounceConfigurationForm_imapSSL" ' .
                                                'name="BounceConfigurationForm[imapSSL]" value="1" ' .
                                                'type="checkbox"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_imapFolder" ' .
                                                'class="required">Folder <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="BounceConfigurationForm_imapFolder" ' .
                                                'name="BounceConfigurationForm[imapFolder]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<th><label for="BounceConfigurationForm_testImapConnection">' .
                                                'Test IMAP connection</label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><span><a id="testImapConnection" ' .
                                                'class="EmailTestingButton z-button" href="#">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Test Connection</span>') !== false);
            $this->assertTrue(strpos($content, '<label for="BounceConfigurationForm_returnPath" ' .
                                                'class="required">Return Path <span class="required">*' .
                                                '</span></label></th>') !== false);
            $this->assertTrue(strpos($content, '<td colspan="1"><input id="BounceConfigurationForm_returnPath" ' .
                                                'name="BounceConfigurationForm[returnPath]" ' .
                                                'type="text" maxlength="64"') !== false);
            $this->assertTrue(strpos($content, '<div class="float-bar">') !== false);
            $this->assertTrue(strpos($content, '<div class="view-toolbar-container clearfix dock">') !== false);
            $this->assertTrue(strpos($content, '<div class="form-toolbar">') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Return to Admin Menu</span></a>') !== false);
            $this->assertTrue(strpos($content, '<span class="z-label">Save</span>') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer-edit-form">') !== false);
            $this->assertTrue(strpos($content, '<div id="FlashMessageView">') !== false);
            $this->assertTrue(strpos($content, '<div id = "FlashMessageBar">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="modalContainer">') !== false);
            $this->assertTrue(strpos($content, '<div id="ModalGameNotificationContainerView">') !== false);
            $this->assertTrue(strpos($content, '<div id="FooterView">') !== false);
            $this->assertTrue(strpos($content, '<div class="ui-chooser">') !== false);
        }

        public function testActionConfigurationEditImapModifyBounce()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('type' => 2));
            $this->setPostArray(array('BounceConfigurationForm' => array(
                'imapHost'                          => 'mail.example.com',
                'imapUsername'                      => 'test@example.com',
                'imapPassword'                      => 'abcd',
                'imapPort'                          => '143',
                'imapSSL'                           => '0',
                'imapFolder'                        => 'INBOX',
                'returnPath'                        => 'bounce@zurmo.com')));
            $this->runControllerWithRedirectExceptionAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertEquals('Bounce configuration saved successfully.', Yii::app()->user->getFlash('notification'));
            $this->assertEquals('mail.example.com',
                                    ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapHost'));
            $this->assertEquals('test@example.com',
                                    ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapUsername'));
            $this->assertEquals('abcd',
                                    ZurmoPasswordSecurityUtil::decrypt(
                                ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapPassword')));
            $this->assertEquals('143',
                                    ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapPort'));
            $this->assertEquals('0', ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapSSL'));
            $this->assertEquals('INBOX',
                                    ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceImapFolder'));
            $this->assertEquals('bounce@zurmo.com',
                                    ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'bounceReturnPath'));
        }

        public function testActionConfigurationEditImapModifyBounceWithValidation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //checking with blank values for required fields
            $this->resetGetArray();
            $this->setGetArray(array('type' => 2));
            $this->setPostArray(array('BounceConfigurationForm' => array(
                'imapHost'                          => '',
                'imapUsername'                      => '',
                'imapPassword'                      => '',
                'imapPort'                          => '',
                'imapSSL'                           => '0',
                'imapFolder'                        => '',
                'returnPath'                        => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertFalse(strpos($content, 'Host cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Username cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Password cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Port cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Folder cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Return Path cannot be blank.') === false);
        }

        public function testSuperUserModifyEmailArchivingConfiguration()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $super2 = User::getByUsername('super2');

            //Change email settings
            $this->resetGetArray();
            $this->resetPostArray();
            $this->setGetArray(array('type' => 1));
            $this->setPostArray(array('EmailArchivingConfigurationForm' => array(
                                    'imapHost'                          => 'mail.example.com',
                                    'imapUsername'                      => 'test@example.com',
                                    'imapPassword'                      => 'abcd',
                                    'imapPort'                          => '143',
                                    'imapSSL'                           => '0',
                                    'imapFolder'                        => 'INBOX')));
            $this->runControllerWithRedirectExceptionAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertEquals('Email archiving configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            $this->assertEquals('mail.example.com',     Yii::app()->imap->imapHost);
            $this->assertEquals('test@example.com',     Yii::app()->imap->imapUsername);
            $this->assertEquals('abcd',                 Yii::app()->imap->imapPassword);
            $this->assertEquals('143',                  Yii::app()->imap->imapPort);
            $this->assertEquals('0',                    Yii::app()->imap->imapSSL);
            $this->assertEquals('INBOX',                Yii::app()->imap->imapFolder);
        }

        public function testSuperUserModifyEmailArchivingConfigurationImapWithValidation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //checking with blank values for required fields
            $this->resetGetArray();
            $this->setGetArray(array('type' => 1));
            $this->setPostArray(array('EmailArchivingConfigurationForm' => array(
                                    'imapHost'                          => '',
                                    'imapUsername'                      => '',
                                    'imapPassword'                      => '',
                                    'imapPort'                          => '',
                                    'imapSSL'                           => '0',
                                    'imapFolder'                        => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditImap');
            $this->assertFalse(strpos($content, 'Host cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Username cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Password cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Port cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Folder cannot be blank.') === false);
        }

        public function testSuperUserModifyEmailSMTPConfigurationOutboundWithValidation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $super2 = User::getByUsername('super2');

            //checking with blank values for required fields
            $this->resetGetArray();
            $this->setPostArray(array('EmailSmtpConfigurationForm' => array(
                                    'host'                              => '',
                                    'port'                              => '',
                                    'username'                          => 'myuser',
                                    'password'                          => 'apassword')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailMessages/default/configurationEditOutbound');
            $this->assertFalse(strpos($content, 'Host cannot be blank.') === false);
            $this->assertFalse(strpos($content, 'Port cannot be blank.') === false);
        }
    }
?>