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

    class FormLayoutSecurityUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            Yii::app()->user->userModel = SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveElementForEditableRender()
        {
            $nullElementInformation = array(
                'attributeName' => null,
                'type'          => 'Null' // Not Coding Standard
            );
            $betty = User::getByUsername('betty');
            $billy = User::getByUsername('billy');
            $contactForBetty = ContactTestHelper::createContactByNameForOwner("betty's contact", $betty);
            $contactForBilly = ContactTestHelper::createContactByNameForOwner("betty's contact", $billy);

            //Testing a non ModelElement.
            $elementInformation = array(
                'attributeName' => 'something',
                'type'          => 'Text'
            );
            $referenceElementInformation = $elementInformation;
           FormLayoutSecurityUtil::resolveElementForEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //Testing a AccountElement when Betty cannot access accounts module.
            $elementInformation = array(
                'attributeName' => 'account',
                'type'          => 'Account'
            );
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($nullElementInformation, $referenceElementInformation);

            //Testing ok access for Betty.
            $betty->setRight   ('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::ALLOW);
            $this->assertTrue($betty->save());
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //Testing UserElement.
            $elementInformation = array(
                'attributeName' => 'owner',
                'type'          => 'User'
            );
            //Super can see related user picker without any problem.
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForEditableRender($contactForBetty, $referenceElementInformation, User::getByUsername('super'));
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //Betty can also see related user picker without problem, even though betty has no access to user tab.
            $referenceElementInformation = $elementInformation;
            $this->assertEquals(Right::DENY, $betty->getEffectiveRight('UsersModule', UsersModule::RIGHT_ACCESS_USERS));
            FormLayoutSecurityUtil::resolveElementForEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($elementInformation, $referenceElementInformation);
        }

        /**
         * @depends testResolveElementForEditableRender
         */
        public function testResolveElementForNonEditableRender()
        {
            $betty = User::getByUsername('betty');
            $billy = User::getByUsername('billy');
            $contactForBetty = ContactTestHelper::createContactByNameForOwner("betty's contact2", $betty);
            $contactForBetty->account = AccountTestHelper::createAccountByNameForOwner('BillyCompany', $billy);
            $this->assertTrue($contactForBetty->save());
            $accountId = $contactForBetty->account->id;
            $nullElementInformation = array(
                'attributeName' => null,
                'type'          => 'Null' // Not Coding Standard
            );
           //test non ModelElement, should pass through without modification.
            $elementInformation = array(
                'attributeName' => 'something',
                'type'          => 'Text'
            );
            $referenceElementInformation = $elementInformation;
           FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //test Acc ModelElement
            //Betty will see a nullified Element because Betty cannot access read the related account
            $elementInformation = array(
                'attributeName' => 'account',
                'type'          => 'Account'
            );
            $noLinkElementInformation = array(
                'attributeName' => 'account',
                'type'          => 'Account',
                'noLink'        => true,
            );
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($nullElementInformation, $referenceElementInformation);

            $this->assertEquals(Right::ALLOW, $betty->getEffectiveRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS));

            //Betty can see the account with a link, because she has been added for Permission::READ on the account.
            //and she has access to the accounts tab.
            $account = Account::getById($accountId);
            $account->addPermissions($betty, Permission::READ);
            $this->assertTrue($account->save());
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //Removing Betty's access to the accounts tab means she will see the element, but without a link
            $betty->setRight   ('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS, Right::DENY);
            $this->assertTrue($betty->save());
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($noLinkElementInformation, $referenceElementInformation);

            //Testing UserElement
            $elementInformation = array(
                'attributeName' => 'owner',
                'type'          => 'User'
            );
            $noLinkElementInformation = array(
                'attributeName' => 'owner',
                'type'          => 'User',
                'noLink'        => true,
            );
            //Super can see related user picker link without a problem.
            $referenceElementInformation = $elementInformation;
            FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, User::getByUsername('super'));
            $this->assertEquals($elementInformation, $referenceElementInformation);

            //Betty can also see related user name, but not a link.
            $referenceElementInformation = $elementInformation;
            $this->assertEquals(Right::DENY, $betty->getEffectiveRight('UsersModule', UsersModule::RIGHT_ACCESS_USERS));
            FormLayoutSecurityUtil::resolveElementForNonEditableRender($contactForBetty, $referenceElementInformation, $betty);
            $this->assertEquals($noLinkElementInformation, $referenceElementInformation);
        }
    }
?>
