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
     * UserLdapIdentity represents the data needed to identity a user using ldap server
	 * authentication.
     */
    class UserLdapIdentity extends UserIdentity
    {
        const ERROR_NO_RIGHT_WEB_LOGIN = 3;

        /**
         * Authenticates a user against ldap server.
         * @return boolean whether authentication succeeds.
         */
        public function authenticate()
        {
            try
            {
                $host                      = Yii::app()->authenticationHelper->ldapHost;
                $port                      = Yii::app()->authenticationHelper->ldapPort;
                $baseDomain                = Yii::app()->authenticationHelper->ldapBaseDomain;
                $bindPassword              = Yii::app()->authenticationHelper->ldapBindPassword;
                $bindRegisteredDomain      = Yii::app()->authenticationHelper->ldapBindRegisteredDomain;
                $ldapConnection            = LdapUtil::establishConnection($host, $port, $bindRegisteredDomain,
                                                                           $bindPassword, $baseDomain);
                if ($ldapConnection)
                {
                    $ldapFilter              = '(|(cn=' . $this->username . ')(&(uid=' . $this->username . ')))';
                    $ldapResults             = ldap_search($ldapConnection, $baseDomain, $ldapFilter);
                    $ldapResultsCount        = ldap_count_entries($ldapConnection, $ldapResults);
                    if ($ldapResultsCount > 0)
                    {
                        $result = @ldap_get_entries($ldapConnection, $ldapResults);
                        $zurmoLogin = parent::authenticate();
                        if (!$zurmoLogin)
                        {
                           if ($result[0] && @ldap_bind($ldapConnection, $result[0]['dn'], $this->password))
                            {
                              if ($this->errorCode != 1)
                              {
                                 $this->setState('username', $this->username);
                                 $this->errorCode = self::ERROR_NONE;
                                 return true;
                              }
                            }
                        }
                        else
                        {
                            $this->setState('username', $this->username);
                            $this->errorCode = self::ERROR_NONE;
                            return true;
                        }
                    }
                    else
                    {
                        return parent::authenticate();
                    }
                }
                else
                {
                    return parent::authenticate();
                }
            }
            catch (NotFoundException $e)
            {
                $this->errorCode = self::ERROR_USERNAME_INVALID;
            }
            catch (BadPasswordException $e)
            {
                $this->errorCode = self::ERROR_PASSWORD_INVALID;
            }
            catch (NoRightWebLoginException $e)
            {
                $this->errorCode = self::ERROR_NO_RIGHT_WEB_LOGIN;
            }
            return false;
        }
    }
?>
