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
     * Model for user's email accounts
     */
    class EmailAccount extends Item
    {
        const DEFAULT_NAME    = 'Default';

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
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
                                  array('useCustomOutboundSettings', 'type',      'type' => 'boolean'),
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
                        $this->addError($attribute, Yii::t('Default', 'This field is required'));
                        $haveError = true;
                    }
                }
            }
        }
    }
?>
