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
     * Class to adapt a user's configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class UserConfigurationFormAdapter
    {
        /**
         * @return UserConfigurationForm
         */
        public static function makeFormFromUserConfigurationByUser(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            $form                                   = new UserConfigurationForm($user);
            $form->listPageSize                     = Yii::app()->pagination->getByUserAndType($user, 'listPageSize');
            $form->subListPageSize                  = Yii::app()->pagination->getByUserAndType($user, 'subListPageSize');
            $form->themeColor                       = Yii::app()->themeManager->resolveAndGetThemeColorValue($user);
            $form->backgroundTexture                = Yii::app()->themeManager->resolveAndGetBackgroundTextureValue($user);
            $form->hideWelcomeView                  = static::resolveAndGetValue($user, 'hideWelcomeView');
            $form->turnOffEmailNotifications        = static::resolveAndGetValue($user, 'turnOffEmailNotifications');
            $form->enableDesktopNotifications       = static::resolveAndGetValue($user, 'enableDesktopNotifications');
            $form->defaultPermissionGroupSetting    = static::resolveAndGetValue($user, 'defaultPermissionGroupSetting', false);
            $form->defaultPermissionSetting         = static::resolveAndGetDefaultPermissionSetting($user);
            $form->visibleAndOrderedTabMenuItems    = static::getVisibleAndOrderedTabMenuItemsByUser($user);
            $form->selectedVisibleAndOrderedTabMenuItems = static::getVisibleAndOrderedTabMenuItemsByUser($user, true);
            return $form;
        }

        /**
         * Given a UserConfigurationForm and user, save the configuration values for the specified user.
         */
        public static function setConfigurationFromForm(UserConfigurationForm $form, User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            Yii::app()->pagination->setByUserAndType($user, 'listPageSize', (int)$form->listPageSize);
            Yii::app()->pagination->setByUserAndType($user, 'subListPageSize', (int)$form->subListPageSize);
            Yii::app()->themeManager->setThemeColorValue($user, $form->themeColor);
            Yii::app()->themeManager->setBackgroundTextureValue ($user, $form->backgroundTexture);
            static::setValue($user, (bool)$form->hideWelcomeView, 'hideWelcomeView');
            static::setValue($user, (bool)$form->turnOffEmailNotifications, 'turnOffEmailNotifications');
            static::setValue($user, (bool)$form->enableDesktopNotifications, 'enableDesktopNotifications');
            static::setValue($user, (int)$form->defaultPermissionSetting, 'defaultPermissionSetting', false);
            static::setDefaultPermissionGroupSetting($user, (int)$form->defaultPermissionGroupSetting,
                                                    (int)$form->defaultPermissionSetting);
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'VisibleAndOrderedTabMenuItems',
                                                           serialize($form->selectedVisibleAndOrderedTabMenuItems));
            MenuUtil::forgetCacheEntryForTabMenuByUser($user);
        }

        /**
         * Given a UserConfigurationForm save the configuration values for the current user
         * and load values as active.
         */
        public static function setConfigurationFromFormForCurrentUser(UserConfigurationForm $form)
        {
            $user = Yii::app()->user->userModel;
            static::setConfigurationFromForm($form, $user);
            Yii::app()->user->setState('listPageSize', (int)$form->listPageSize);
            Yii::app()->user->setState('subListPageSize', (int)$form->subListPageSize);
        }

        public static function resolveAndGetValue(User $user, $key, $returnBoolean = true)
        {
            assert('$user instanceOf User && $user->id > 0');
            assert('is_string($key)');
            assert('is_bool($returnBoolean)');
            $value = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', $key);
            return ($returnBoolean)? (bool) $value : $value;
        }

        public static function setValue(User $user, $value, $key, $saveBoolean = true)
        {
            assert('is_bool($saveBoolean)');
            assert('is_string($key)');
            $value = ($saveBoolean)? (bool) $value : $value;
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', $key, $value);
        }

        public static function resolveAndGetDefaultPermissionSetting(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $defaultPermission = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule',
                            'defaultPermissionSetting'))
            {
                return $defaultPermission;
            }
            else
            {
                return UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE;
            }
        }

        public static function setDefaultPermissionGroupSetting(User $user, $value, $defaultPermissionSetting)
        {
            assert('$value === null || is_int($value)');
            assert('$defaultPermissionSetting === null || is_int($defaultPermissionSetting)');
            if ($defaultPermissionSetting == UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP)
            {
                ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionGroupSetting',
                    $value);
            }
            else
            {
                ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'defaultPermissionGroupSetting',
                    null);
            }
        }

        public static function getVisibleAndOrderedTabMenuItemsByUser($user, $selected = false)
        {
            $visibleAndOrderedTabMenuItems = array();
            $tabMenuItems = MenuUtil::getVisibleAndOrderedTabMenuByUser($user);
            foreach ($tabMenuItems as $menuItem)
            {
                if ($selected === true)
                {
                    $visibleAndOrderedTabMenuItems[] = $menuItem['moduleId'];
                }
                else
                {
                    $moduleId = $menuItem['moduleId'];
                    $visibleAndOrderedTabMenuItems[$moduleId] = $menuItem['label'];
                }
            }
            return $visibleAndOrderedTabMenuItems;
        }
    }
?>