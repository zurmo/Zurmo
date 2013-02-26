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
     * Helper class to create a connection object and test connection for Ldap.
     */
    class LdapUtil
    {
        /**
         * Given an host and port, a LdapConnection is created and returned.
         * @param string $host
         * @param string $port
         * @return bool $ldapConnection
         */
        public static function makeConnection($host, $port)
        {
            assert('is_string($host)');
            assert('is_int($port)');
            $ldapConnection = ldap_connect($host, $port);
            ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);
            return $ldapConnection;
        }

        /**
         * Send a connection Request.  Can use to determine if the Ldap settings are configured correctly.
         * @param ZurmoAuthenticationHelper $zurmoAuthenticationHelper
         * @param server $host
         * @param username $bindRegisteredDomain
         * @param password $bindPassword,
         * @param base domain $baseDomain
         */
        public static function establishConnection($host, $port, $bindRegisteredDomain, $bindPassword, $baseDomain)
        {
            assert('is_string($host)');
            assert('is_int($port)');
            assert('is_string($bindRegisteredDomain)');
            assert('is_string($bindPassword)');
            assert('is_string($baseDomain)');
            $ldapConnection = self::makeConnection($host, $port);
            //checking user type
            $bindRegisteredDomain = 'cn=' . $bindRegisteredDomain . ',' . $baseDomain; // Not Coding Standard
            // bind with appropriate dn to give update access
            if (@ldap_bind($ldapConnection, $bindRegisteredDomain, $bindPassword))
            {
               return $ldapConnection;
            }
            else
            {
               return false;
            }
        }
    }