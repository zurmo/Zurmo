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

    class UserLdapTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            if (ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                Yii::app()->authenticationHelper->ldapServerType           =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapServerType'];

                Yii::app()->authenticationHelper->ldapHost                 =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapHost'];

                Yii::app()->authenticationHelper->ldapPort                 =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapPort'];

                Yii::app()->authenticationHelper->ldapBindRegisteredDomain =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindRegisteredDomain'];

                Yii::app()->authenticationHelper->ldapBindPassword         =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBindPassword'];

                Yii::app()->authenticationHelper->ldapBaseDomain           =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapBaseDomain'];

                Yii::app()->authenticationHelper->ldapEnabled              =
                    Yii::app()->params['authenticationTestSettings']['ldapSettings']['ldapEnabled'];

                Yii::app()->authenticationHelper->setLdapSettings();
                Yii::app()->authenticationHelper->init();
            }
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        /**
        * Check if user exists in Zurmo users but not on ldap server
        */
        public function testUserExitsInZurmoButNotOnldap()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            $user               = new User();
            $user->username     = 'abcdefg';
            $user->title->value = 'Mr.';
            $user->firstName    = 'abcdefg';
            $user->lastName     = 'abcdefg';
            $user->setPassword('abcdefgN4');
            $this->assertTrue($user->save());

            // Now attempt to login as bill a user in zurmo but not on ldap
            $bill               = User::getByUsername('abcdefg');
            $this->assertEquals(md5('abcdefgN4'), $bill->hash);
            $bill->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB, RIGHT::ALLOW);
            $this->assertTrue($bill->save());

            // For normal user
            $identity           = new UserIdentity('abcdefg', 'abcdefgN4');
            $authenticated      = $identity->authenticate();
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);
            $bill->forget();
        }

        /**
         * Test case when user exists in Zurmo users and on ldap server, but when the password is wrong for ldap,
         * and correct for Zurmo user.
         *
         */
        public function testUserExitsInBothButWrongPasswordForldap()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            Yii::app()->user->userModel = User::getByUsername('super');

            // Create same user as on ldap server, but with different password
            $admin = new User();
            $admin->username           = 'admin';
            $admin->title->value       = 'Mr.';
            $admin->firstName          = 'admin';
            $admin->lastName           = 'admin';
            $admin->setPassword('test123');
            $this->assertTrue($admin->save());
            $admin->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB, RIGHT::ALLOW);
            $this->assertTrue($admin->save());
            $username = Yii::app()->authenticationHelper->ldapBindRegisteredDomain;
            $password = Yii::app()->authenticationHelper->ldapBindPassword;
            $identity = new UserLdapIdentity($username, 'test123');
            $authenticated = $identity->authenticate(true);
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);
        }

        /**
        * Test case when user exists in ldap server but not in Zurmo users
        */
        public function testUserExitsInldapNotInZurmo()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            Yii::app()->user->userModel = User::getByUsername('super');
            $identity                   = new UserLdapIdentity('john', 'johnldap');
            $authenticated              = $identity->authenticate(true);
            $this->assertEquals(1, $identity->errorCode);
            $this->assertFalse($authenticated);
        }

        /**
        * Test case when same user exists in ldap server and in Zurmo users
        */
        public function testUserExitsInldapAndZurmo()
        {
            if (!ZurmoTestHelper::isAuthenticationLdapTestConfigurationSet())
            {
                $this->markTestSkipped(Zurmo::t('Default', 'Test Ldap settings are not configured in perInstanceTest.php file.'));
            }
            Yii::app()->user->userModel = User::getByUsername('super');
            $username                   = Yii::app()->authenticationHelper->ldapBindRegisteredDomain;
            $password                   = Yii::app()->authenticationHelper->ldapBindPassword;
            $identity                   = new UserLdapIdentity($username, $password);
            $authenticated              = $identity->authenticate(true);
            $this->assertEquals(0, $identity->errorCode);
            $this->assertTrue($authenticated);
        }
    }
?>
