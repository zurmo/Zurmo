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
     * Form to all editing and viewing of outbound email configuration values in the user interface.
     */
    class OutboundEmailConfigurationForm extends ConfigurationForm
    {
        public $host;
        public $port = 25;
        public $username;
        public $password;
        public $userIdOfUserToSendNotificationsAs;
        public $aTestToAddress;

        public function rules()
        {
            return array(
                array('host',                              'required'),
                array('host',                              'type',      'type' => 'string'),
                array('host',                              'length',    'min'  => 1, 'max' => 64),
                array('port',                              'required'),
                array('port',                              'type',      'type' => 'integer'),
                array('port',                              'numerical', 'min'  => 1),
                array('username',                          'type',      'type' => 'string'),
                array('username',                          'length',    'min'  => 1, 'max' => 64),
                array('password',                          'type',      'type' => 'string'),
                array('password',                          'length',    'min'  => 1, 'max' => 64),
                array('userIdOfUserToSendNotificationsAs', 'type',      'type' => 'integer'),
                array('userIdOfUserToSendNotificationsAs', 'numerical', 'min'  => 1),
                array('aTestToAddress',                    'email'),
            );
        }

        public function attributeLabels()
        {
            return array(
                'host'                                 => Yii::t('Default', 'Host'),
                'port'                                 => Yii::t('Default', 'Port'),
                'username'                             => Yii::t('Default', 'Username'),
                'password'                             => Yii::t('Default', 'Password'),
                'userIdOfUserToSendNotificationsAs'    => Yii::t('Default', 'Send system notifications from'),
                'aTestToAddress'                       => Yii::t('Default', 'Send a test email to'),
            );
        }
    }
?>