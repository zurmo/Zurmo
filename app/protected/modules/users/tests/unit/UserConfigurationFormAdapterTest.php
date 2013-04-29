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

    class UserConfigurationFormAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('sally');
        }

        public function testMakeFormAndSetConfigurationFromForm()
        {
            $billy = User::getByUsername('billy');
            $sally = User::getByUsername('sally');
            Yii::app()->pagination->setGlobalValueByType('listPageSize',      50);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',   51);

            //Confirm sally's configuration is the defaults.
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($sally);
            $this->assertEquals(50,                 $form->listPageSize);
            $this->assertEquals(51,                 $form->subListPageSize);
            $this->assertEquals('blue',             Yii::app()->themeManager->resolveAndGetThemeColorValue($sally));
            $this->assertEquals(null,               Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($sally));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'hideWelcomeView'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'turnOffEmailNotifications'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'enableDesktopNotifications'));
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'defaultPermissionGroupSetting', false));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($sally), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            //Confirm billy's configuration is the defaults.
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $this->assertEquals(50,                 $form->listPageSize);
            $this->assertEquals(51,                 $form->subListPageSize);
            $this->assertEquals('blue',             Yii::app()->themeManager->resolveAndGetThemeColorValue($billy));
            $this->assertEquals(null,               Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($billy));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'hideWelcomeView'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'turnOffEmailNotifications'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'enableDesktopNotifications'));
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'defaultPermissionGroupSetting', false));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($billy), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            //Now change configuration for Billy.
            $form->listPageSize                     = 60;
            $form->subListPageSize                  = 61;
            $form->themeColor                       = 'lime';
            $form->backgroundTexture                = 'paper';
            $form->hideWelcomeView                  = true;
            $form->turnOffEmailNotifications        = true;
            $form->enableDesktopNotifications       = true;
            $form->defaultPermissionSetting         = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP;
            $form->defaultPermissionGroupSetting    = 6;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $billy);
            //Confirm billy's settings are changed correctly.
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $this->assertEquals(60,                 $form->listPageSize);
            $this->assertEquals(61,                 $form->subListPageSize);
            $this->assertEquals('lime',             Yii::app()->themeManager->resolveAndGetThemeColorValue($billy));
            $this->assertEquals('paper',            Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($billy));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'hideWelcomeView'));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'turnOffEmailNotifications'));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'enableDesktopNotifications'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'hideWelcomeView'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'turnOffEmailNotifications'));
            $this->assertFalse(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'enableDesktopNotifications'));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($billy), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'defaultPermissionGroupSetting', false), 6);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($sally), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'defaultPermissionGroupSetting', false));
            //Now set configuration settings for sally and confirm they are correct.
            Yii::app()->user->userModel = $sally;
            UserConfigurationFormAdapter::setConfigurationFromFormForCurrentUser($form);
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($sally);
            $this->assertEquals(60,                 $form->listPageSize);
            $this->assertEquals(61,                 $form->subListPageSize);
            $this->assertEquals('lime',             Yii::app()->themeManager->resolveAndGetThemeColorValue($sally));
            $this->assertEquals('paper',            Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($sally));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'hideWelcomeView'));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'turnOffEmailNotifications'));
            $this->assertTrue(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'enableDesktopNotifications'));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($sally), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetValue($sally, 'defaultPermissionGroupSetting', false), 6);
            //Now test that setting defaultPermissionSetting to owner makes the group settings null
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $form->defaultPermissionSetting         = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER;
            $form->defaultPermissionGroupSetting    = 4;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $billy);
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($billy), UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($billy, 'defaultPermissionGroupSetting', false));
        }

        public function testEditTabMenuOrderByMakeFormAndSetConfigurationFromForm()
        {
            $sally = User::getByUsername('sally');
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($sally);
            $this->assertEquals(count($form->selectedVisibleAndOrderedTabMenuItems), count(MenuUtil::getVisibleAndOrderedTabMenuByUser($sally)));
            $defaultOrderedTabMenuItems = $form->selectedVisibleAndOrderedTabMenuItems;
            $customOrderedTabMenuItems  = array_reverse($defaultOrderedTabMenuItems);
            $form->selectedVisibleAndOrderedTabMenuItems = $customOrderedTabMenuItems;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $sally);
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($sally);
            $this->assertEquals($form->selectedVisibleAndOrderedTabMenuItems, $customOrderedTabMenuItems);

            $billy = User::getByUsername('billy');
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $this->assertEquals($form->selectedVisibleAndOrderedTabMenuItems, $defaultOrderedTabMenuItems);
            $form->selectedVisibleAndOrderedTabMenuItems = $customOrderedTabMenuItems;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $billy);
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($billy);
            $this->assertEquals($form->selectedVisibleAndOrderedTabMenuItems, $customOrderedTabMenuItems);
        }

        public function testGetVisibleAndOrderedTabMenuItemsByUser()
        {
            $sally = User::getByUsername('sally');
            $sally->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($sally->save());
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($sally);
            $customOrderedTabMenuItems = UserConfigurationFormAdapter::getVisibleAndOrderedTabMenuItemsByUser($sally);
            $this->assertEquals(3, count($customOrderedTabMenuItems));
            $form->selectedVisibleAndOrderedTabMenuItems = $customOrderedTabMenuItems;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $sally);
            $sally->removeRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($sally->save());
            $customOrderedTabMenuItems = UserConfigurationFormAdapter::getVisibleAndOrderedTabMenuItemsByUser($sally);
            $this->assertEquals(2, count($customOrderedTabMenuItems));
        }
    }
?>