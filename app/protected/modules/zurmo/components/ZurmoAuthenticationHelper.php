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
     * Component for working with authentication configuration
     */
    class ZurmoAuthenticationHelper extends CApplicationComponent
    {
        /**
         * Defines a OpenLDAP authentication server type.
         */
        const SERVER_TYPE_OPEN_LDAP        = 'OpenLDAP';

        /**
         * Defines a Active Directory authentication server type.
         */
        const SERVER_TYPE_ACTIVE_DIRECTORY = 'ActiveDirectory';

        /**
         * Ldap server type. Example OpenLDAP
         * @var string
         */
        public $ldapServerType;

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
            'ldapServerType',
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