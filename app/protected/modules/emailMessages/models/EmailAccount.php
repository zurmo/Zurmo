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
     * Model for user's email accounts
     */
    class EmailAccount extends Item
    {
        const DEFAULT_NAME    = 'Default';

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Zurmo::t('EmailMessagesModule', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        /**
         * @param User $user
         * @param mixed $name null or String representing the email account name
         */
        public static function getByUserAndName(User $user, $name = null)
        {
            if ($name == null)
            {
                $name = self::DEFAULT_NAME;
            }
            else
            {
                //For now Zurmo does not support multiple email accounts
                throw new NotSupportedException();
            }
            assert('is_string($name)');
            $bean = R::findOne(EmailAccount::getTableName('EmailAccount'),
                               "_user_id = ? AND name = ?", array($user->id, $name));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            else
            {
                $emailAccount = self::makeModel($bean);
            }
            return $emailAccount;
        }

        /**
         * Attempt to get the email account for a given user. If it does not exist, make a default EmailAccount
         * and return it.
         * @param User $user
         * @param mixed $name null or String representing the email account name
         * @return EmailAccount
         */
        public static function resolveAndGetByUserAndName(User $user, $name = null)
        {
            try
            {
                $emailAccount = static::getByUserAndName($user, $name);
            }
            catch (NotFoundException $e)
            {
                $emailAccount                    = new EmailAccount();
                $emailAccount->user              = $user;
                $emailAccount->name              = self::DEFAULT_NAME;
                $emailAccount->fromName          = $user->getFullName();
                if ($user->primaryEmail->id > 0 && $user->primaryEmail->emailAddress != null)
                {
                    $emailAccount->fromAddress       = $user->primaryEmail->emailAddress;
                }
                $emailAccount->useCustomOutboundSettings = false;
                $emailAccount->outboundPort              = '25';
                $emailAccount->outboundType              = EmailHelper::OUTBOUND_TYPE_SMTP;
            }
            return $emailAccount;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'fromAddress',
                    'fromName',
                    'replyToName',
                    'replyToAddress',
                    'useCustomOutboundSettings',
                    'outboundType',
                    'outboundHost',
                    'outboundPort',
                    'outboundUsername',
                    'outboundPassword',
                    'outboundSecurity',
                ),
                'relations' => array(
                    'messages' => array(RedBeanModel::HAS_MANY, 'EmailMessage'),
                    'user'     => array(RedBeanModel::HAS_ONE,  'User'),
                ),
                'rules'     => array(
                                  array('fromName',                  'required'),
                                  array('fromAddress',               'required'),
                                  array('name',                      'type',      'type' => 'string'),
                                  array('fromName',                  'type',      'type' => 'string'),
                                  array('replyToName',               'type',      'type' => 'string'),
                                  array('outboundHost',              'type',      'type' => 'string'),
                                  array('outboundUsername',          'type',      'type' => 'string'),
                                  array('outboundPassword',          'type',      'type' => 'string'),
                                  array('outboundSecurity',          'type',      'type' => 'string'),
                                  array('outboundType',              'type',      'type' => 'string'),
                                  array('outboundPort',              'type',      'type' => 'integer'),
                                  array('useCustomOutboundSettings', 'boolean'),
                                  array('fromName',                  'length',    'max' => 64),
                                  array('replyToName',               'length',    'max' => 64),
                                  array('outboundType',              'length',    'max' => 4),
                                  array('outboundHost',              'length',    'max' => 64),
                                  array('outboundUsername',          'length',    'max' => 64),
                                  array('outboundPassword',          'length',    'max' => 64),
                                  array('outboundSecurity',          'length',    'max' => 3),
                                  array('fromAddress',               'email'),
                                  array('replyToAddress',            'email'),
                                  array('useCustomOutboundSettings', 'validateCustomOutboundSettings',
                                                                     'requiredAttributes' => array(   'outboundHost',
                                                                                                      'outboundPort',
                                                                                                      'outboundUsername',
                                                                                                      'outboundPassword'))
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Account', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email Accounts', array(), null, $language);
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'fromAddress'       => Zurmo::t('EmailMessagesModule', 'From Address',  array(), null, $language),
                    'fromName'          => Zurmo::t('EmailMessagesModule', 'From Name',  array(), null, $language),
                    'messages'          => Zurmo::t('EmailMessagesModule', 'Messages',  array(), null, $language),
                    'name'              => Zurmo::t('ZurmoModule',         'Name',  array(), null, $language),
                    'outboundHost'      => Zurmo::t('EmailMessagesModule', 'Outbound Host',  array(), null, $language),
                    'outboundPassword'  => Zurmo::t('EmailMessagesModule', 'Outbound Password',  array(), null, $language),
                    'outboundPort'      => Zurmo::t('EmailMessagesModule', 'Outbound Port',  array(), null, $language),
                    'outboundSecurity'  => Zurmo::t('EmailMessagesModule', 'Outbound Security',  array(), null, $language),
                    'outboundType'      => Zurmo::t('EmailMessagesModule', 'Outbound Type',  array(), null, $language),
                    'outboundUsername'  => Zurmo::t('EmailMessagesModule', 'Outbound Username',  array(), null, $language),
                    'replyToAddress'    => Zurmo::t('EmailMessagesModule', 'Reply To Address',  array(), null, $language),
                    'replyToName'       => Zurmo::t('EmailMessagesModule', 'Reply To Name',  array(), null, $language),
                    'useCustomOutboundSettings' => Zurmo::t('EmailMessagesModule', 'Use Custom Outbound Settings',  array(), null, $language),
                    'user'                      => Zurmo::t('UsersModule',         'User',  array(), null, $language),
                )
            );
        }

        /**
         * When the useCustomOutboundSettings is checked, then other attributes become required
         * @param string $attribute
         * @param array $params
         */
        public function validateCustomOutboundSettings($attribute, $params)
        {
            if ($this->$attribute)
            {
                $haveError = false;
                foreach ($params['requiredAttributes'] as $attribute)
                {
                    if ($this->$attribute == null)
                    {
                        $this->addError($attribute, Zurmo::t('EmailMessagesModule', 'This field is required'));
                        $haveError = true;
                    }
                }
            }
        }
    }
?>
