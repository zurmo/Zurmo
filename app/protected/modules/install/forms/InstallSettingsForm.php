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
     * Form used by installation to enter settings
     */
    class InstallSettingsForm extends CFormModel
    {
        public $databaseHostname = 'localhost';

        public $databaseAdminUsername;

        public $databaseAdminPassword;

        public $databaseName = 'zurmo';

        public $databaseUsername = 'zurmo';

        public $databasePassword;

        public $databasePort = 3306;

        public $superUserPassword;

        public $memcacheHostname = '127.0.0.1';

        public $memcachePortNumber = 11211;

        protected $memcacheAvailable = true;

        public $databaseType = 'mysql';

        public $removeExistingData;

        public $installDemoData = false;

        public $hostInfo = '';

        public $scriptUrl = '';

        public $submitCrashToSentry = true;

        public function rules()
        {
            return array(
                array('databaseHostname',      'required'),
                array('databaseName',          'required'),
                array('databaseUsername',      'required'),
                array('databasePassword',      'required'),
                array('databasePort',          'required'),
                array('superUserPassword',     'required'),
                array('hostInfo',              'required'),
                array('scriptUrl',             'required'),
                array('databaseHostname',      'type', 'type' => 'string'),
                array('databaseAdminUsername', 'type', 'type' => 'string'),
                array('databaseAdminPassword', 'type', 'type' => 'string'),
                array('databaseName',          'type', 'type' => 'string'),
                array('databaseUsername',      'type', 'type' => 'string'),
                array('databasePassword',      'type', 'type' => 'string'),
                array('superUserPassword',     'type', 'type' => 'string'),
                array('memcacheHostname',      'type', 'type' => 'string'),
                array('memcachePortNumber',    'type', 'type' => 'integer'),
                array('memcachePortNumber',    'numerical', 'min'  => 1024),
                array('removeExistingData',    'boolean'),
                array('installDemoData',       'boolean'),
                array('hostInfo',              'type', 'type' => 'string'),
                array('scriptUrl',             'type', 'type' => 'string'),
                array('submitCrashToSentry',   'boolean'),
            );
        }

        public function setMemcacheIsNotAvailable()
        {
            $this->memcacheAvailable  = false;
            $this->memcacheHostname   = null;
            $this->memcachePortNumber = null;
        }

        public function getIsMemcacheAvailable()
        {
            return $this->memcacheAvailable;
        }

        /**
         * After the standard validation is completed, check the database connections.
         * @see CModel::afterValidate()
         */
        public function afterValidate()
        {
            parent::afterValidate();
            if (count($this->getErrors()) == 0)
            {
                //check memcache first, since creating the db / user should be last.
                if ($this->memcacheHostname != null)
                {
                    if ($this->memcachePortNumber == null)
                    {
                        $this->addError('memcachePortNumber', Zurmo::t('InstallModule', 'Since you specified a memcache ' .
                        'hostname, you must specify a port.'));
                        return;
                    }
                    $memcacheResult = InstallUtil::checkMemcacheConnection($this->memcacheHostname,
                                                                           (int)$this->memcachePortNumber);
                    if ($memcacheResult !== true)
                    {
                        $this->addError('memcacheHostname', Zurmo::t('InstallModule', 'Error code:') . " " .
                        $memcacheResult[0] . '<br/>Message(Memcached): ' . $memcacheResult[1]);
                        return;
                    }
                }

                if (!$this->hostInfo)
                {
                    $this->addError('hostInfo', Zurmo::t('InstallModule', 'Please enter server IP or URL.'));
                    return;
                }
                else
                {
                    if ((strpos($this->hostInfo, 'http://') === false) && (strpos($this->hostInfo, 'https://') === false))
                    {
                        $this->addError('hostInfo', Zurmo::t('InstallModule', 'Host Info must start with "http://" or "https://".'));
                        return;
                    }
                }

                if ($this->databaseAdminUsername != null)
                {
                    if ($this->databaseAdminPassword == null)
                    {
                        $this->addError('databaseAdminPassword', Zurmo::t('InstallModule', 'Since you specified a database ' .
                        'admin username, you must enter a password'));
                        return;
                    }
                    $connectionResult = DatabaseCompatibilityUtil::checkDatabaseConnection($this->databaseType,
                                                                      $this->databaseHostname,
                                                                      $this->databaseAdminUsername,
                                                                      $this->databaseAdminPassword,
                                                                      (int)$this->databasePort);
                    if ($connectionResult !== true)
                    {
                        $this->addError('databaseAdminUsername', Zurmo::t('InstallModule', 'Error code:') . " " .
                        $connectionResult[0] . '<br/>Message: ' . $connectionResult[1]);
                        return;
                    }
                    $userExistsResult = DatabaseCompatibilityUtil::checkDatabaseUserExists($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseAdminUsername,
                                                                             $this->databaseAdminPassword,
                                                                             (int)$this->databasePort,
                                                                             $this->databaseUsername);
                    if ($userExistsResult === true)
                    {
                        $this->addError('databaseUsername', Zurmo::t('InstallModule', 'You have specified an existing user. ' .
                        'If you would like to use this user, then do not specify the database admin username and ' .
                        'password. Otherwise pick a database username that does not exist.'));
                        return;
                    }
                    $databaseExistsResult = DatabaseCompatibilityUtil::checkDatabaseExists($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseAdminUsername,
                                                                             $this->databaseAdminPassword,
                                                                             (int)$this->databasePort,
                                                                             $this->databaseName);
                    if ($databaseExistsResult === true)
                    {
                        $this->addError('databaseName', Zurmo::t('InstallModule', 'You have specified an existing database. ' .
                        'If you would like to use this database, then do not specify the database admin username and ' .
                        'password. Otherwise pick a database name that does not exist.'));
                        return;
                    }
                    $createDatabaseResult = DatabaseCompatibilityUtil::createDatabase($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseAdminUsername,
                                                                             $this->databaseAdminPassword,
                                                                             (int)$this->databasePort,
                                                                             $this->databaseName);
                    if ($createDatabaseResult === false)
                    {
                        $this->addError('databaseName', Zurmo::t('InstallModule', 'There was a problem creating the database ' .
                        'Error code:') . " " . $connectionResult[0] . '<br/>Message: ' . $connectionResult[1]);
                        return;
                    }
                    $createUserResult = DatabaseCompatibilityUtil::createDatabaseUser($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseAdminUsername,
                                                                             $this->databaseAdminPassword,
                                                                             (int)$this->databasePort,
                                                                             $this->databaseName,
                                                                             $this->databaseUsername,
                                                                             $this->databasePassword);
                    if ($createUserResult === false)
                    {
                        $this->addError('databaseUsername', Zurmo::t('InstallModule', 'There was a problem creating the user ' .
                        'Error code:') . " " . $connectionResult[0] . '<br/>Message: ' . $connectionResult[1]);
                        return;
                    }
                }
                else
                {
                    $connectionResult = DatabaseCompatibilityUtil::checkDatabaseConnection($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseUsername,
                                                                             $this->databasePassword,
                                                                             (int)$this->databasePort);
                    if ($connectionResult !== true)
                    {
                        $this->addError('databaseUsername', Zurmo::t('InstallModule', 'Error code:') . " " .
                        $connectionResult[0] . '<br/>Message: ' . $connectionResult[1]);
                        return;
                    }
                    $databaseExistsResult = DatabaseCompatibilityUtil::checkDatabaseExists($this->databaseType,
                                                                             $this->databaseHostname,
                                                                             $this->databaseUsername,
                                                                             $this->databasePassword,
                                                                             (int)$this->databasePort,
                                                                             $this->databaseName);
                    if ($databaseExistsResult !== true)
                    {
                        $this->addError('databaseName', Zurmo::t('InstallModule', 'The database name specified does not ' .
                        'exist or the user specified does not have access.') . '<br/>' .
                        Zurmo::t('InstallModule', 'Error code:') . " " . $databaseExistsResult[0] .
                        '<br/>Message: ' . $databaseExistsResult[1]);
                        return;
                    }
                    else
                    {
                        if ($this->removeExistingData == false)
                        {
                        $this->addError('removeExistingData', Zurmo::t('InstallModule', 'Since you specified an existing database ' .
                        'you must check this box in order to proceed. THIS WILL REMOVE ALL EXISTING DATA.'));
                        return;
                        }
                    }
                }
            }
        }
    }
?>