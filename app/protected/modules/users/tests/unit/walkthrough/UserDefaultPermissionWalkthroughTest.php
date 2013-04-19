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

    class UserDefaultPermissionWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $testGroup1        = new Group();
            $testGroup1->name  = 'testGroup1';
            assert($testGroup1->save()); // Not Coding Standard
            $testGroup2        = new Group();
            $testGroup2->name  = 'testGroup2';
            assert($testGroup2->save()); // Not Coding Standard
        }

        public function testUserCanSaveDefaultPermissions()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $group          = Group::getByName('testGroup2');

            // set permission setting to 'everyone' and permission group settings to 'testGroup2'
            $this->setGetArray(array('id' => $super->id));
            $postData = array('defaultPermissionSetting' => UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE,
                                'defaultPermissionGroupSetting' => $group->id);
            $this->setPostArray(array('UserConfigurationForm' => $postData));
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit',
                                Yii::app()->createUrl('users/default/details', array('id' => $super->id)));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($super, 'defaultPermissionGroupSetting',
                                false));

            // set permission setting to 'users and group', set permission group settings to 'testGroup2'
            $this->resetGetArray();
            $this->resetPostArray();
            $this->setGetArray(array('id' => $super->id));
            $postData = array('defaultPermissionSetting' => UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP,
                                'defaultPermissionGroupSetting' => $group->id);
            $this->setPostArray(array('UserConfigurationForm' => $postData));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('users/default/configurationEdit',
                                Yii::app()->createUrl('users/default/details', array('id' => $super->id)));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetValue($super, 'defaultPermissionGroupSetting',
                                false), $group->id);
        }

        /**
         * @depends testUserCanSaveDefaultPermissions
         */
        public function testUserDefaultPermissionsLoadedOnCreate()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $group          = Group::getByName('testGroup2');
            $content        = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');
            // test that 'Owner and users in' radio button is checked with testGroup2 selected
            $this->assertEquals(preg_match('%<input id="Account_explicitReadWriteModelPermissions_type_1" value="2"'.
                ' checked="checked" type="radio" name="Account\[explicitReadWriteModelPermissions\]\[type\]" />%',
                $content), 1);
            $this->assertEquals(preg_match('%<option value="' . $group->id .
                '" selected="selected">' . $group->name . '</option>%', $content), 1);
        }

        /**
         * @depends testUserDefaultPermissionsLoadedOnCreate
         */
        public function testUserDefaultPermissionsLoadedOnlyOnCreate()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $testGroup1     = Group::getByName('testGroup1');
            $testGroup2     = Group::getByName('testGroup2');
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $this->setGetArray(array('id' => $superAccountId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/edit');
            // test that 'Owner and users in' radio button is not checked.
            $this->assertEquals(preg_match('%<input id="Account_explicitReadWriteModelPermissions_type_1" value="2" '.
                'checked="checked" type="radio" name="Account\[explicitReadWriteModelPermissions\]\[type\]" />%',
                $content), 0);
            // test that 'Owner' radio button is checked, which is default for AccountTestHelper
            $this->assertEquals(preg_match('%<input id="Account_explicitReadWriteModelPermissions_type_0" value="" '.
                    'checked="checked" type="radio" name="Account\[explicitReadWriteModelPermissions\]\[type\]" />%',
                $content), 1);
            // test that no dropdown item is selected
            $this->assertEquals(preg_match('%<option value="(\d+)" selected="selected">(.*)</option>%', $content), 0); // Not Coding Standard
        }

        public function testGlobalDefaultsLoadedOnCreateInAbsenceOfUserDefaultPermissions()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            UserConfigurationFormAdapter::setValue($super, null, 'defaultPermissionSetting', false);
            UserConfigurationFormAdapter::setDefaultPermissionGroupSetting($super, null, null);
            $content        = $this->runControllerWithNoExceptionsAndGetContent('accounts/default/create');
            // test that 'everyone' radio button is checked
            $this->assertEquals(preg_match('%<input id="Account_explicitReadWriteModelPermissions_type_2" value="1" '.
                    'checked="checked" type="radio" name="Account\[explicitReadWriteModelPermissions\]\[type\]" />%',
                $content), 1);
            // test that no downdown item is selected
            $this->assertEquals(preg_match('%<option value="(\d+)" selected="selected">(.*)</option>%', $content), 0); // Not Coding Standard
        }
    }
?>