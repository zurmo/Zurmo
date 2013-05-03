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

    class UsersModule extends SecurableModule
    {
        const RIGHT_CHANGE_USER_PASSWORDS       = 'Change User Passwords';
        const RIGHT_LOGIN_VIA_WEB               = 'Login Via Web';
        const RIGHT_LOGIN_VIA_MOBILE            = 'Login Via Mobile';
        const RIGHT_LOGIN_VIA_WEB_API           = 'Login Via Web API';
        const RIGHT_CREATE_USERS                = 'Create Users';
        const RIGHT_ACCESS_USERS                = 'Access Users Tab';

        const POLICY_ENFORCE_STRONG_PASSWORDS   = 'Enforce Strong Passwords';
        const POLICY_MINIMUM_PASSWORD_LENGTH    = 'Minimum Password Length';
        const POLICY_MINIMUM_USERNAME_LENGTH    = 'Minimum Username Length';
        const POLICY_PASSWORD_EXPIRES           = 'Password Expires';
        const POLICY_PASSWORD_EXPIRY_DAYS       = 'Password Expiry Days';

        const AUDIT_EVENT_USER_LOGGED_IN        = 'User Logged In';
        const AUDIT_EVENT_USER_LOGGED_OUT       = 'User Logged Out';
        const AUDIT_EVENT_USER_PASSWORD_CHANGED = 'User Password Changed';

        protected static $policyDefaults = array(
            self::POLICY_ENFORCE_STRONG_PASSWORDS => null,
            self::POLICY_MINIMUM_PASSWORD_LENGTH  => 5,
            self::POLICY_MINIMUM_USERNAME_LENGTH  => 3,
            self::POLICY_PASSWORD_EXPIRES         => null,
            self::POLICY_PASSWORD_EXPIRY_DAYS     => null,
        );

        public function canDisable()
        {
            return false;
        }

        public function getDependencies()
        {
            return array(
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('User');
        }

        public static function getTranslatedRightsLabels()
        {
            $labels                                = array();

            $labels[self::RIGHT_CHANGE_USER_PASSWORDS]  = Zurmo::t('UsersModule', 'Change User Passwords');
            $labels[self::RIGHT_LOGIN_VIA_WEB]          = Zurmo::t('UsersModule', 'Sign in Via Web');
            $labels[self::RIGHT_LOGIN_VIA_MOBILE]       = Zurmo::t('UsersModule', 'Sign in Via Mobile');
            $labels[self::RIGHT_LOGIN_VIA_WEB_API]      = Zurmo::t('UsersModule', 'Sign in Via Web API');
            $labels[self::RIGHT_CREATE_USERS]           = Zurmo::t('UsersModule', 'Create Users');
            $labels[self::RIGHT_ACCESS_USERS]           = Zurmo::t('UsersModule', 'Access Users Tab');
            return $labels;
        }

        public static function getTranslatedPolicyLabels()
        {
            $labels                                          = array();
            $labels[self::POLICY_ENFORCE_STRONG_PASSWORDS]   = Zurmo::t('UsersModule', 'Enforce Strong Passwords');
            $labels[self::POLICY_MINIMUM_PASSWORD_LENGTH]    = Zurmo::t('UsersModule', 'Minimum Password Length');
            $labels[self::POLICY_MINIMUM_USERNAME_LENGTH]    = Zurmo::t('UsersModule', 'Minimum Username Length');
            $labels[self::POLICY_PASSWORD_EXPIRES]           = Zurmo::t('UsersModule', 'Password Expires');
            $labels[self::POLICY_PASSWORD_EXPIRY_DAYS]       = Zurmo::t('UsersModule', 'Password Expiry Days');
            return $labels;
        }

        public static function getStrongerPolicy($policyName, array $values)
        {
            assert('is_string($policyName) && $policyName != ""');
            switch ($policyName)
            {
                case self::POLICY_ENFORCE_STRONG_PASSWORDS:
                case self::POLICY_PASSWORD_EXPIRES:
                    assert('AssertUtil::all($values, "isPolicyYesNo", "UsersModule")');
                    return max($values);

                case self::POLICY_MINIMUM_PASSWORD_LENGTH:
                case self::POLICY_MINIMUM_USERNAME_LENGTH:
                    assert('AssertUtil::all($values, "is_numeric")');
                    return max($values);

                case self::POLICY_PASSWORD_EXPIRY_DAYS:
                    assert('AssertUtil::all($values, "is_numeric")');
                    return min($values);

                default:
                    throw new NotSupportedException();
            }
        }

        public static function getPolicyRulesTypes()
        {
            $userPolicyRulesTypes = array(
               'POLICY_ENFORCE_STRONG_PASSWORDS' => 'YesNo',
               'POLICY_MINIMUM_PASSWORD_LENGTH'  => 'Integer',
               'POLICY_MINIMUM_USERNAME_LENGTH'  => 'Integer',
               'POLICY_PASSWORD_EXPIRES'         => 'PasswordExpires',
               'POLICY_PASSWORD_EXPIRY_DAYS'     => 'PasswordExpiry',
            );
            return array_merge(parent::getPolicyNames(), $userPolicyRulesTypes);
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'adminTabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('UsersModule', 'Users')",
                        'url'   => array('/users/default'),
                        'right' => self::RIGHT_ACCESS_USERS,
                        'items' => array(
                            array(
                                'label' => "eval:Zurmo::t('UsersModule', 'Create User')",
                                'url'   => array('/users/default/create'),
                                'right' => self::RIGHT_CREATE_USERS
                            ),
                            array(
                                'label' => "eval:Zurmo::t('UsersModule', 'Users')",
                                'url'   => array('/users/default'),
                                'right' => self::RIGHT_ACCESS_USERS
                            ),
                        ),
                    ),
                ),
                'globalSearchAttributeNames' => array(
                    'fullName',
                    'anyEmail',
                    'username',
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('UsersModule', 'Users')",
                        'descriptionLabel' => "eval:Zurmo::t('UsersModule', 'Manage Users')",
                        'route'            => '/users/default',
                        'right'            => self::RIGHT_ACCESS_USERS,
                    ),
                ),
                'headerMenuItems' => array(
                    array(
                        'label'  => "eval:Zurmo::t('UsersModule', 'Users')",
                        'url'    => array('/users/default'),
                        'right'  => self::RIGHT_ACCESS_USERS,
                        'order'  => 4,
                        'mobile' => false,
                    ),
                ),
                'userHeaderMenuItems' => array(
                        array(
                            'label' => "eval:Zurmo::t('UsersModule', 'My Profile')",
                            'url' => array('/users/default/profile'),
                            'order' => 1,
                        ),
                        array(
                            'label' => "eval:Zurmo::t('UsersModule', 'Sign out')",
                            'url' => array('/zurmo/default/logout'),
                            'order' => 4,
                        ),
                ),
                'designerMenuItems' => array(
                    'showFieldsLink' => false,
                    'showGeneralLink' => false,
                    'showLayoutsLink' => true,
                    'showMenusLink' => false,
                ),
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'User';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_USERS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_USERS;
        }

        public static function stringifyAuditEvent(AuditEvent $auditEvent, $format = 'long')
        {
            assert('$format == "long" || $format == "short"');
            $s = null;
            switch ($auditEvent->eventName)
            {
                case self::AUDIT_EVENT_USER_LOGGED_IN:
                case self::AUDIT_EVENT_USER_LOGGED_OUT:
                    if ($format == 'short')
                    {
                        return Zurmo::t('UsersModule', $auditEvent->eventName);
                    }
                    else
                    {
                        $s .= strval($auditEvent);
                    }
                    break;
                case self::AUDIT_EVENT_USER_PASSWORD_CHANGED:
                    if ($format == 'short')
                    {
                        return Zurmo::t('UsersModule', $auditEvent->eventName);
                    }
                    $s       .= strval($auditEvent);
                    $username = unserialize($auditEvent->serializedData);
                    try
                    {
                        if ($auditEvent->modelClassName == 'User')
                        {
                            $user = User::getById((int)$auditEvent->modelId);
                            $s .= ", $user";
                        }
                        else
                        {
                            throw new NotSupporteException();
                        }
                    }
                    catch (NotFoundException $e)
                    {
                        $s .= ", $username";
                    }
                    break;
            }
            return $s;
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('UsersDemoDataMaker');
        }

        /**
         * Even though users are never globally searched, the search form can still be used by a specific
         * search view for a module.  Either this module or a related module.  This is why a class is returned.
         * @see modelsAreNeverGloballySearched controls it not being searchable though in the global search.
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'UsersSearchForm';
        }

        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('UsersModule', 'User', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('UsersModule', 'Users', array(), null, $language);
        }
    }
?>
