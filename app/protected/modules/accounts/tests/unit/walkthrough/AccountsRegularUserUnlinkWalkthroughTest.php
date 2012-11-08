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
     * Unlink walkthrough tests.
     */
    class AccountsRegularUserUnlinkWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            ReadPermissionsOptimizationUtil::rebuild();
            $simpleUser = UserTestHelper::createBasicUser('simpleUser');
        }

        public function testUnlinkContactForAccount()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $simpleUser = User::getByUsername('simpleUser');
            Yii::app()->user->userModel = $simpleUser;
            $simpleUser->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $simpleUser->setRight('AccountsModule', AccountsModule::RIGHT_CREATE_ACCOUNTS);
            $simpleUser->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $simpleUser->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $this->assertTrue($simpleUser->save());
            $account = AccountTestHelper::createAccountByNameForOwner('simpleUserAccount', $simpleUser);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('simpleUserContact', $simpleUser, $account);
            $accounts      = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $contacts      = Contact::getAll();
            $this->assertEquals(1, count($contacts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'simpleUserAccount');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $contactId = self::getModelIdByModelNameAndName ('Contact', 'simpleUserContact simpleUserContactson');
            //unlinking the contact
            $this->setGetArray(array(   'id' => $contactId,
                                        'relationModelClassName'       => 'Account',
                                        'relationModelId'              => $superAccountId,
                                        'relationModelRelationName'    => 'contacts'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/unlink', true);
            $accounts      = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $contacts      = Contact::getAll();
            $contactId = $contacts[0]->id;
            $contacts[0]->forget();
            $contact = Contact::getById($contactId);
            $this->assertTrue($contact->account->id < 0);
        }
    }
?>
