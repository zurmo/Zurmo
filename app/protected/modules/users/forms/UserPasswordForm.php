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
     * Use this form when creating a new user or changing the password for an existing user.
     */
    class UserPasswordForm extends ModelForm
    {
        public $newPassword;
        public $newPassword_repeat;

        protected static function getRedBeanModelClassName()
        {
            return 'User';
        }

        public function __construct(User $model)
        {
            $this->model = $model;
        }

        public function rules()
        {
            return array(
                array('newPassword',        'required', 'on'   => 'createUser, changePassword'),
                array('newPassword',        'type',     'type' => 'string'),
                array('newPassword',        'compare',  'on'   => 'createUser, changePassword'),
                array('newPassword',        'validatePasswordLength',
                                                        'on'   => 'createUser, changePassword'),
                array('newPassword',        'validatePasswordStrength',
                                                        'on'   => 'createUser, changePassword'),
                array('newPassword_repeat', 'type',     'type' => 'string'),
                array('newPassword_repeat', 'required', 'on'   => 'createUser, changePassword'),
            );
        }

        public function attributeLabels()
        {
            return array_merge($this->model->attributeLabels(), array(
                'newPassword'            => Zurmo::t('UsersModule', 'Password'),
                'newPassword_repeat'     => Zurmo::t('UsersModule', 'Confirm Password'),
            ));
        }

        public function afterValidate()
        {
            parent::afterValidate();
            if ($this->getScenario() === 'createUser' || $this->getScenario() === 'changePassword')
            {
                $this->model->setPassword((string)$this->newPassword);
            }
        }

        public function validatePasswordLength($attribute, $params)
        {
            $minLength = $this->model->getEffectivePolicy('UsersModule',
                                    UsersModule::POLICY_MINIMUM_PASSWORD_LENGTH);

            if (strlen($this->$attribute) < $minLength)
            {
                $this->addError('newPassword',
                    Zurmo::t('UsersModule', 'The password is too short. Minimum length is {minimumLength}.', array('{minimumLength}' => $minLength)));
            }
        }

        public function validatePasswordStrength($attribute, $params)
        {
            if (Policy::YES == $this->model->getEffectivePolicy('UsersModule',
                                    UsersModule::POLICY_ENFORCE_STRONG_PASSWORDS))
            {
                if (strtolower($this->$attribute) == $this->$attribute)
                {
                    $this->addError('newPassword',
                        Zurmo::t('UsersModule', 'The password must have at least one uppercase letter'));
                }
                if (strtoupper($this->$attribute) == $this->$attribute)
                {
                    $this->addError('newPassword',
                        Zurmo::t('UsersModule', 'The password must have at least one lowercase letter'));
                }
                if (ctype_alpha($this->$attribute) || ctype_digit($this->$attribute))
                {
                    $this->addError('newPassword',
                        Zurmo::t('UsersModule', 'The password must have at least one number and one letter'));
                }
            }
        }
    }
?>