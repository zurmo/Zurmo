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
     * Unlink walkthrough tests.
     */
    class AccountsSuperUserUnlinkWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $contact = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            $contactWithNoAccount = ContactTestHelper::createContactByNameForOwner('noAccountContact', $super);
        }

        public function testUnlinkContactForAccount()
        {
            $accounts      = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $contacts      = Contact::getAll();
            $this->assertEquals(2, count($contacts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $contactId = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
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

        public function testActionUnlinkWithNoRelationModelClassName()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts      = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $contacts      = Contact::getAll();
            $this->assertEquals(2, count($contacts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $contactId = self::getModelIdByModelNameAndName ('Contact', 'noAccountContact noAccountContactson');
            $this->setGetArray(array(   'id' => $contactId,
                                        'relationModelClassName'       => null,
                                        'relationModelId'              => $superAccountId,
                                        'relationModelRelationName'    => 'contacts'));
             $content = $this->runControllerWithNotSupportedExceptionAndGetContent('contacts/default/unlink');
        }

        public function testActionUnlinkWithNoHasManyAndManyManyRelation()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts      = Account::getAll();
            $this->assertEquals(1, count($accounts));
            $contacts      = Contact::getAll();
            $this->assertEquals(2, count($contacts));
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $contactId = self::getModelIdByModelNameAndName ('Contact', 'noAccountContact noAccountContactson');
            $this->setGetArray(array(   'id' => $contactId,
                                        'relationModelClassName'       => 'Account',
                                        'relationModelId'              => $superAccountId,
                                        'relationModelRelationName'    => 'billingAddress'));
            $content = $this->runControllerWithNotSupportedExceptionAndGetContent('contacts/default/unlink');
        }
    }
?>
