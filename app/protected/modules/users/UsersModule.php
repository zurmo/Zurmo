<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Users',
                        'descriptionLabel' => 'Manage Users',
                        'route'            => '/users/default',
                        'right'            => self::RIGHT_ACCESS_USERS,
                    ),
                ),
                'shortcutsMenuItems' => array(
                    array(
                        'label' => 'Users',
                        'url'   => array('/users/default'),
                        'right' => self::RIGHT_ACCESS_USERS,
                        'items' => array(
                            array(
                                'label' => 'Create User',
                                'url'   => array('/users/default/create'),
                                'right' => self::RIGHT_CREATE_USERS,
                            ),
                            array(
                                'label' => 'Users',
                                'url'   => array('/users/default'),
                                'right' => self::RIGHT_ACCESS_USERS,
                            ),
                        ),
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
                        return Yii::t('Default', $auditEvent->eventName);
                    }
                    else
                    {
                        $s .= strval($auditEvent);
                    }
                    break;
                case self::AUDIT_EVENT_USER_PASSWORD_CHANGED:
                    if ($format == 'short')
                    {
                        return Yii::t('Default', $auditEvent->eventName);
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

        public static function getDemoDataMakerClassName()
        {
            return 'UsersDemoDataMaker';
        }
    }
?>
