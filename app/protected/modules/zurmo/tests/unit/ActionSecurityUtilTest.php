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

    class ActionSecurityUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            SecurityTestHelper::createUsers();
            SecurityTestHelper::createGroups();
            SecurityTestHelper::createAccounts();
        }

        public function testCanCurrentUserPerformAction()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts                   = Account::getByName('Supermart');
            $betty                      = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;
            $this->assertEquals(1, count($accounts));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction(null, $accounts[0]));
            $this->assertEquals(Permission::NONE, $accounts[0]->getEffectivePermissions($betty));
            $this->assertFalse(ActionSecurityUtil::canCurrentUserPerformAction('Details', $accounts[0]));
            $this->assertFalse(ActionSecurityUtil::canCurrentUserPerformAction('Edit', $accounts[0]));
            $this->assertFalse(ActionSecurityUtil::canCurrentUserPerformAction('Delete', $accounts[0]));
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Details', $accounts[0]));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Edit', $accounts[0]));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Delete', $accounts[0]));
            $aUser = User::getByUsername('billy');
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Details', $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Edit',    $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Delete',  $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('UsersModalList',  $aUser));
            Yii::app()->user->userModel = User::getByUsername('betty');
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Details', $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Edit',    $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('Delete',  $aUser));
            $this->assertTrue (ActionSecurityUtil::canCurrentUserPerformAction('UsersModalList',  $aUser));
        }

        /**
         * @depends testCanCurrentUserPerformAction
         */
        public function testResolveLinkToModelForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts                   = Account::getByName('Supermart');
            $betty = User::getByUsername('betty');
            Yii::app()->user->userModel = $betty;
            $bettyAccount = AccountTestHelper::createAccountByNameForOwner('bopbeebop', $betty);
            $link = ActionSecurityUtil::resolveLinkToModelForCurrentUser(
                                    'bpoboo',
                                    $bettyAccount,
                                    'AccountsModule',
                                    'accounts/default/details');
            $this->assertEquals('bpoboo', $link);
            $betty->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $this->assertTrue($betty->save());
            $link = ActionSecurityUtil::resolveLinkToModelForCurrentUser(
                                    'bpoboo',
                                    $bettyAccount,
                                    'AccountsModule',
                                    'accounts/default/details');
            $this->assertFalse(strpos($link, 'accounts/default/details') === false);
            $this->assertEquals(1, count($accounts));
            $link = ActionSecurityUtil::resolveLinkToModelForCurrentUser(
                                    'bpoboo',
                                    $accounts[0],
                                    'AccountsModule',
                                    'accounts/default/details');
            $this->assertEquals(null, $link);
        }
    }
?>
