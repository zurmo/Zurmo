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
    * Testing creating mixed attributes (multi-select and tag cloud), while already having an existing account
    * and making sure things work correctly with retrieval and save.
    */
    class AccountsSuperUserMixedAttributeCreationWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        /**
         * @see MissingBeanException (Related to why we needed this test in the first place.)  Since superAccount
         * is created before the attributes, was causing a problem where some parts of the relatedModel beans are not
         * present.  Catching the MissingBeanException in the RedBeanModel solved this problem.
         */
        public function testSuperUserSavingAccountCreatedBeforeThreeoRequiredCustomAttributesAreCreated()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //First create an account before you create multiselect and tagcloud attributes
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            $account->forget();

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountsModule'));

            //Create 2 custom attributes that are required.
            $this->createDropDownCustomFieldByModule            ('AccountsModule', 'dropdown');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('AccountsModule', 'tagcloud');

            //Save the account again.  Everything is fine.
            $account       = Account::getByName('superAccount');

            $account[0]->save(false);
            $account[0]->forget();

            //Retrieving the account again at this point should retrieve ok.
            $account       = Account::getByName('superAccount');
            $account[0]->save(false);
            $account[0]->forget();
        }
    }
?>
