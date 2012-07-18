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
     * Form to all editing and viewing of email archiving configuration values in the user interface.
     */
    class EmailArchivingConfigurationForm extends ConfigurationForm
    {
        public $imapHost;
        public $imapUsername;
        public $imapPassword;
        public $imapPort;
        public $imapSSL;
        public $imapFolder;
        public $testImapConnection;

        public function rules()
        {
            return array(
                array('imapHost',                          'required'),
                array('imapHost',                          'type',      'type' => 'string'),
                array('imapHost',                          'length',    'min'  => 1, 'max' => 64),
                array('imapUsername',                      'required'),
                array('imapUsername',                      'type',      'type' => 'string'),
                array('imapUsername',                      'length',    'min'  => 1, 'max' => 64),
                array('imapPassword',                      'required'),
                array('imapPassword',                      'type',      'type' => 'string'),
                array('imapPassword',                      'length',    'min'  => 1, 'max' => 64),
                array('imapPort',                          'required'),
                array('imapPort',                          'type',      'type' => 'integer'),
                array('imapPort',                          'numerical', 'min'  => 1),
                array('imapSSL',                           'boolean'),
                array('imapFolder',                        'required'),
                array('imapFolder',                        'type',      'type' => 'string'),
                array('imapFolder',                        'length',    'min'  => 1, 'max' => 64),
            );
        }

        public function attributeLabels()
        {
            return array(
                'imapHost'                             => Yii::t('Default', 'Host'),
                'imapUsername'                         => Yii::t('Default', 'Username'),
                'imapPassword'                         => Yii::t('Default', 'Password'),
                'imapPort'                             => Yii::t('Default', 'Port'),
                'imapSSL'                              => Yii::t('Default', 'SSL connection'),
                'imapFolder'                           => Yii::t('Default', 'Folder'),
                'testImapConnection'                   => Yii::t('Default', 'Test IMAP connection'),
            );
        }
    }
?>