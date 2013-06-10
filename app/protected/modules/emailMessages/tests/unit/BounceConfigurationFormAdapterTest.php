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

    class BounceConfigurationFormAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeFormAndSetConfigurationFromForm()
        {
            $form       = BounceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertNull($form->imapHost);
            $this->assertNull($form->imapPort);
            $this->assertNull($form->imapUsername);
            $this->assertNull($form->imapPassword);
            $this->assertNull($form->imapSSL);
            $this->assertNull($form->imapFolder);
            $this->assertNull($form->returnPath);

            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapHost', 'bounce.com');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapPort', '420');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapSSL', '1');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapUsername', 'bouncy');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapPassword',
                                                                        ZurmoPasswordSecurityUtil::encrypt('bounces'));
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceImapFolder', 'BOUNCES');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceReturnPath', 'bounce@bounce.com');

            $form       = BounceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('bounce.com',           $form->imapHost);
            $this->assertEquals('420',                  $form->imapPort);
            $this->assertEquals('bouncy',               $form->imapUsername);
            $this->assertEquals('bounces',              $form->imapPassword);
            $this->assertEquals('1',                    $form->imapSSL);
            $this->assertEquals('BOUNCES',              $form->imapFolder);
            $this->assertEquals('bounce@bounce.com',    $form->returnPath);

            $form       = BounceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $form->imapHost         = 'example.com';
            $form->imapPort         = '111';
            $form->imapSSL          = '0';
            $form->imapUsername     = 'user';
            $form->imapPassword     = 'password';
            $form->imapFolder       = 'folder';
            $form->returnPath       = 'path';
            BounceConfigurationFormAdapter::setConfigurationFromForm($form);
            $form       = BounceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('example.com',  $form->imapHost);
            $this->assertEquals('111',          $form->imapPort);
            $this->assertEquals('user',         $form->imapUsername);
            $this->assertEquals('password',     $form->imapPassword);
            $this->assertEquals('0',            $form->imapSSL);
            $this->assertEquals('folder',       $form->imapFolder);
            $this->assertEquals('path',         $form->returnPath);
        }
    }
?>