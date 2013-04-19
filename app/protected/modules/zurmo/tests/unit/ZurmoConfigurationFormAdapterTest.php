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

    class ZurmoConfigurationFormAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
            $group = Group::getByName('Super Administrators');
            $group->users->add($billy);
            $saved = $group->save();
            assert($saved); // Not Coding Standard
            UserTestHelper::createBasicUser('sally');
        }

        public function testMakeFormAndSetConfigurationFromForm()
        {
            $billy = User::getByUsername('billy');
            Yii::app()->timeZoneHelper->setTimeZone           ('America/New_York');
            Yii::app()->pagination->setGlobalValueByType('listPageSize',          50);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',       51);
            Yii::app()->pagination->setGlobalValueByType('modalListPageSize',     52);
            Yii::app()->pagination->setGlobalValueByType('dashboardListPageSize', 53);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', 'demoCompany');
            $logoFileName = 'testImage.png';
            $logoFilePath = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName;
            ZurmoConfigurationFormAdapter::resizeLogoImageFile($logoFilePath, $logoFilePath, null, ZurmoConfigurationForm::DEFAULT_LOGO_HEIGHT);
            $logoFileId   = ZurmoConfigurationFormAdapter::saveLogoFile($logoFileName, $logoFilePath, 'logoFileModelId');
            ZurmoConfigurationFormAdapter::publishLogo($logoFileName, $logoFilePath);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', $logoFileId);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', $logoFileId);
            $form = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('America/New_York',              $form->timeZone);
            $this->assertEquals(50,                              $form->listPageSize);
            $this->assertEquals(51,                              $form->subListPageSize);
            $this->assertEquals(52,                              $form->modalListPageSize);
            $this->assertEquals(53,                              $form->dashboardListPageSize);
            $this->assertEquals('demoCompany',                   $form->applicationName);
            $this->assertEquals(Yii::app()->user->userModel->id, $form->userIdOfUserToRunWorkflowsAs);
            $this->assertEquals($logoFileName,                   $form->logoFileData['name']);
            $form->timeZone              = 'America/Chicago';
            $form->listPageSize          = 60;
            $form->subListPageSize       = 61;
            $form->modalListPageSize     = 62;
            $form->dashboardListPageSize = 63;
            $form->applicationName       = 'demoCompany2';
            $form->userIdOfUserToRunWorkflowsAs = $billy->id;
            $logoFileName2               = 'testLogo.png';
            $logoFilePath2               = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName2;
            copy($logoFilePath2, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $logoFileName2);
            copy($logoFilePath2, sys_get_temp_dir() . DIRECTORY_SEPARATOR . ZurmoConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX . $logoFileName2);
            Yii::app()->user->setState('logoFileName', $logoFileName2);
            ZurmoConfigurationFormAdapter::setConfigurationFromForm($form);
            $form = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('America/Chicago',  $form->timeZone);
            $this->assertEquals(60,                 $form->listPageSize);
            $this->assertEquals(61,                 $form->subListPageSize);
            $this->assertEquals(62,                 $form->modalListPageSize);
            $this->assertEquals(63,                 $form->dashboardListPageSize);
            $this->assertEquals('demoCompany2',     $form->applicationName);
            $this->assertEquals($billy->id,         $form->userIdOfUserToRunWorkflowsAs);
            $this->assertEquals($logoFileName2,     $form->logoFileData['name']);
        }
    }
?>