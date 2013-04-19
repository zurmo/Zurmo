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
        public static function establishConnection($serverType, $host, $port, $bindRegisteredDomain, $bindPassword, $baseDomain)
        {
            assert('is_string($host)');
            assert('is_int($port)');
            assert('is_string($bindRegisteredDomain)');
            assert('is_string($bindPassword)');
            assert('is_string($baseDomain)');
            $ldapConnection = self::makeConnection($host, $port);
            //checking server type
            if ($serverType == ZurmoAuthenticationHelper::SERVER_TYPE_OPEN_LDAP)
            {
                $bindRegisteredDomain = 'cn=' . $bindRegisteredDomain . ',' . $baseDomain; // Not Coding Standard
            }
            elseif ($serverType == ZurmoAuthenticationHelper::SERVER_TYPE_ACTIVE_DIRECTORY)
            {
                $bindRegisteredDomain = self::resolveBindRegisteredDomain($bindRegisteredDomain, $baseDomain);
            }
            else
            {
                throw new NotSupportedException();
            }
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

        /*
        * Resolving Base Registered Domain for LDAP Server Type
        */
        public static function resolveBindRegisteredDomain($bindRegisteredDomain, $baseDomain)
        {
            assert('is_string($bindRegisteredDomain)');
            assert('is_string($baseDomain)');
            $baseDomain            = str_replace(',', '', $baseDomain); // Not Coding Standard
            $domainControllers     = explode('dc=', $baseDomain);
            $bindRegisteredDomain  = $bindRegisteredDomain . '@' . $domainControllers[1] . '.' .
                                         $domainControllers[2];
            return $bindRegisteredDomain;
        }
    }