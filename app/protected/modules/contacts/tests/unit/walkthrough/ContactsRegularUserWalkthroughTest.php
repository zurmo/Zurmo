<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Contacts Module Walkthrough.
     *
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class ContactsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            //Setup test data owned by the super user.
            $super = Yii::app()->user->userModel;
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount',  $super);
            AccountTestHelper::createAccountByNameForOwner           ('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact',  $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact4', $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            //Make contact DetailsAndRelations portlets
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //todo: look at account regular user walkthrough for idea.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $contact = ContactTestHelper::createContactByNameForOwner('Switcheroo', $super);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            
            
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/edit');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/massEdit');
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 2));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/massEditProgressSave');
            
            //Autocomplete for Account should fail
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/autoComplete');

            //actionModalList should fail
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/modalList');
            
            //actionDelete should fail.
            $this->setGetArray(array('id' => $contact->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('contacts/default/delete');
            
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccountId = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test peon with elevated rights to contacts
            $nobody = User::getByUsername('nobody');
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_CREATE_CONTACTS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');
            
            $contact = ContactTestHelper::createContactByNameForOwner('Switcheroo', $nobody);
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            
            $this->setGetArray(array('id' => $contact->id));
            $this->resetPostArray();
            
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete',
                        Yii::app()->getUrlManager()->getBaseUrl() . '?r=contacts/default/index'); // Not Coding Standard
            
            //Autocomplete for Account should not_fail
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/autoComplete');

            //actionModalList should not_fail
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/modalList');
            
            //todo: more.
        }

        //todo: look at accounts regular user test for more ideas on what to test.
    }
?>