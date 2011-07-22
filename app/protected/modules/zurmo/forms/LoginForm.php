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

    class LoginForm extends CFormModel
    {
        public $username;
        public $password;
        public $rememberMe;

        private $_identity;

        public function rules()
        {
            return array(
                array('username, password', 'required'),
                array('rememberMe', 'boolean'),
                array('password', 'authenticate', 'skipOnError' => true),
            );
        }

        public function attributeLabels()
        {
            return array(
                'username'   => Yii::t('Default', 'Username'),
                'password'   => Yii::t('Default', 'Password'),
                'rememberMe' => Yii::t('Default', 'Remember me next time'),
            );
        }

        public function authenticate($attribute, $params)
        {
            $this->_identity = new UserIdentity($this->username, $this->password);
            if (!$this->_identity->authenticate())
            {
                $this->addError('password', Yii::t('Default', 'Incorrect username or password.'));
            }
        }

        public function login()
        {
            if ($this->_identity === null)
            {
                $this->_identity = new UserIdentity($this->username, $this->password);
                $this->_identity->authenticate();
            }
            if ($this->_identity->errorCode == UserIdentity::ERROR_NONE)
            {
                $duration = $this->rememberMe ? 3600 * 24 * 30 : 0; // 30 days
                Yii::app()->user->login($this->_identity, $duration);
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>
