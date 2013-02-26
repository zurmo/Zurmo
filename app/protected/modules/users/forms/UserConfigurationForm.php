<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Form to all editing and viewing of a user's configuration values in the user interface.
     */
    class UserConfigurationForm extends ConfigurationForm
    {
        /**
         * Is set in order to properly route action elements in view.
         */
        private $user;

        public $listPageSize;

        public $subListPageSize;

        public $themeColor;

        public $backgroundTexture;

        public $hideWelcomeView = false;

        public $turnOffEmailNotifications = false;

        public $enableDesktopNotifications = true;

        const DEFAULT_PERMISSIONS_SETTING_OWNER = 1;
        const DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP = 2;
        const DEFAULT_PERMISSIONS_SETTING_EVERYONE = 3;

        public $defaultPermissionSetting;

        public $defaultPermissionGroupSetting;

        public function __construct($user)
        {
            assert('$user instanceof User');
            assert('is_int($user->id) && $user->id > 0');
            $this->user = $user;
        }

        public function getUser()
        {
            return $this->user;
        }

        /**
         * When getId is called, it is looking for the user model id for the user
         * who's configuration values are being edited.
         */
        public function getId()
        {
            return $this->user->id;
        }

        public function rules()
        {
            return array(
                array('listPageSize',              'required'),
                array('listPageSize',              'type',      'type' => 'integer'),
                array('listPageSize',              'numerical', 'min' => 1),
                array('subListPageSize',           'required'),
                array('subListPageSize',           'type',      'type' => 'integer'),
                array('subListPageSize',           'numerical', 'min' => 1),
                array('themeColor',                'required'),
                array('themeColor',                'type',      'type' => 'string'),
                array('backgroundTexture',         'type',      'type' => 'string'),
                array('hideWelcomeView',           'boolean'),
                array('turnOffEmailNotifications', 'boolean'),
                array('enableDesktopNotifications', 'boolean'),
                array('defaultPermissionSetting',   'numerical', 'min' => self::DEFAULT_PERMISSIONS_SETTING_OWNER,
                    'max' => self::DEFAULT_PERMISSIONS_SETTING_EVERYONE),
                array('defaultPermissionGroupSetting', 'numerical', 'min' => 1)
            );
        }

        public function attributeLabels()
        {
            return array(
                'listPageSize'                  => Zurmo::t('UsersModule', 'List page size'),
                'subListPageSize'               => Zurmo::t('UsersModule', 'Sublist page size'),
                'themeColor'                    => Zurmo::t('UsersModule', 'Theme'),
                'backgroundTexture'             => Zurmo::t('UsersModule', 'Texture'),
                'hideWelcomeView'               => Zurmo::t('UsersModule', 'Hide welcome page'),
                'turnOffEmailNotifications'     => Zurmo::t('UsersModule', 'Turn off email notifications'),
                'enableDesktopNotifications'    => Zurmo::t('UsersModule', 'Enable Desktop notifications')
            );
        }

        public static function getAllDefaultPermissionTypes()
        {
            return array(
                static::DEFAULT_PERMISSIONS_SETTING_OWNER                       => Zurmo::t('ZurmoModule', 'Owner'),
                static::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP    => Zurmo::t('ZurmoModule', 'Owner and users in'),
                static::DEFAULT_PERMISSIONS_SETTING_EVERYONE                    => Zurmo::t('ZurmoModule', 'Everyone'),
            );
        }
    }
?>