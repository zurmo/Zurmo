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
     * Component for working with authentication configuration
     */
    class ZurmoAuthenticationHelper extends CApplicationComponent
    {
        /**
         * Ldap server host name. Example someDomain.com
         * @var string
         */
        public $ldapHost;

        /**
         * Ldap server port number. Default to 389, but it can be set to something different.
         * @var integer
         */
        public $ldapPort = 389;

        /**
         * Ldap server username.
         * @var string
         */
        public $ldapBindRegisteredDomain;

        /**
         * Ldap server password.
         * @var string
         */
        public $ldapBindPassword;

        /**
         * Ldap server domain name.
         * @var string
         */
        public $ldapBaseDomain;

         /**
         * Ldap server authentication feature turn on.
         * @var boolean
         */
        public $ldapEnabled;

        /**
         * Contains array of settings to load during initialization from the configuration table.
         * @see loadLdapSettings
         * @var array
         */
        protected $settingsToLoad = array(
            'ldapHost',
            'ldapPort',
            'ldapBindRegisteredDomain',
            'ldapBindPassword',
            'ldapBaseDomain',
            'ldapEnabled'
        );

        /**
         * Called once per page load, will load up Ldap settings from the database if available.
         * (non-PHPdoc)
         * @see CApplicationComponent::init()
         */
        public function init()
        {
            $this->loadLdapSettings();
        }

        public function loadLdapSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                if (null !== $keyValue = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $keyName))
                {
                    $this->$keyName = $keyValue;
                }
            }
        }

        /**
         * Set Ldap settings into the database.
         */
        public function setLdapSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                ZurmoConfigurationUtil::setByModuleName('ZurmoModule', $keyName, $this->$keyName);
            }
        }

        /**
        * for Login authentication
        */
        public function makeIdentity($username, $password)
        {
          //checking Ldap option enable
          $ldapEnabled = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'ldapEnabled');
          if ($ldapEnabled)
          {
             return new UserLdapIdentity($username, $password);
          }
          else
          {
             return new UserIdentity($username, $password);
          }
        }
    }
?>