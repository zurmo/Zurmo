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
     * Testing the views for configuring Ldap server
     */
    class LdapConfigurationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/ldap/configurationEditLdap');
        }

        public function testSuperUserModifyLdapConfiguration()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Change Ldap settings
            $this->resetGetArray();
            $this->setPostArray(array('LdapConfigurationForm' => array(
                                      'host'                  =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapHost'],
                                      'port'                  =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapPort'],
                                      'bindRegisteredDomain'  =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindRegisteredDomain'],
                                      'bindPassword'          =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindPassword'],
                                      'baseDomain'            =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBaseDomain'],
                                      'enabled'               =>
                                      Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapEnabled'])));
            $this->runControllerWithRedirectExceptionAndGetContent('zurmo/ldap/configurationEditLdap');
            $this->assertEquals('Ldap Configuration saved successfully.', Yii::app()->user->getFlash('notification'));

            //Confirm the setting did in fact change correctly
            $authenticationHelper = new ZurmoAuthenticationHelper;
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapHost'],
                                Yii::app()->authenticationHelper->ldapHost);
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapPort'],
                                Yii::app()->authenticationHelper->ldapPort);
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindRegisteredDomain'],
                                Yii::app()->authenticationHelper->ldapBindRegisteredDomain);
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindPassword'],
                                Yii::app()->authenticationHelper->ldapBindPassword);
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBaseDomain'],
                                Yii::app()->authenticationHelper->ldapBaseDomain);
            $this->assertEquals(Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapEnabled'],
                                Yii::app()->authenticationHelper->ldapEnabled);
        }

        /*
        *@depends testSuperUserModifyLdapConfiguration
        */
        public function testSuperUserTestLdapConnection()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //check Ldap connection
            $this->resetGetArray();
            $this->setPostArray(array('LdapConfigurationForm' => array(
                                      'host'                              => Yii::app()->authenticationHelper->ldapHost,
                                      'port'                              => Yii::app()->authenticationHelper->ldapPort,
                                      'bindRegisteredDomain'              => Yii::app()->authenticationHelper->ldapBindRegisteredDomain,
                                      'bindPassword'                      => Yii::app()->authenticationHelper->ldapBindPassword,
                                      'baseDomain'                        => Yii::app()->authenticationHelper->ldapBaseDomain,
                                      'enabled'                           => Yii::app()->authenticationHelper->ldapEnabled)));
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/ldap/testConnection');
            $this->assertTrue(strpos($content, "Successfully Connected to Ldap Server") > 0);
        }
    }
?>