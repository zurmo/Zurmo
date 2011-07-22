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
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ContactsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner           ('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact4', $super, $account);
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist     ();
            OpportunityTestHelper::createOpportunityWithAccountByNameForOwner('superOpp', $super, $account);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            //Make contact DetailsAndRelations portlets
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/create');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $contacts = Contact::getAll();
            $this->assertEquals(4, count($contacts));
            $superContactId     = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $superContactId2    = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');
            $superContactId3    = self::getModelIdByModelNameAndName('Contact', 'superContact3 superContact3son');
            $superContactId4    = self::getModelIdByModelNameAndName('Contact', 'superContact4 superContact4son');
            $superOpportunityId = self::getModelIdByModelNameAndName ('Opportunity', 'superOpp');
            $this->setGetArray(array('id' => $superContactId));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            //Save contact.
            $superContact = Contact::getById($superContactId);
            $this->assertEquals(null, $superContact->officePhone);
            $this->setPostArray(array('Contact' => array('officePhone' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/edit');
            $superContact = Contact::getById($superContactId);
            $this->assertEquals('456765421', $superContact->officePhone);
            //Test having a failed validation on the contact during save.
            $this->setGetArray (array('id'      => $superContactId));
            $this->setPostArray(array('Contact' => array('lastName' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superContactId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8,9', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>6</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;records selected for updating') === false);

            //save Model MassEdit for selected Ids
            //Test that the 4 contacts do not have the office phone number we are populating them with.
            $contact1 = Contact::getById($superContactId);
            $contact2 = Contact::getById($superContactId2);
            $contact3 = Contact::getById($superContactId3);
            $contact4 = Contact::getById($superContactId4);
            $this->assertNotEquals('7788', $contact1->officePhone);
            $this->assertNotEquals('7788', $contact2->officePhone);
            $this->assertNotEquals('7788', $contact3->officePhone);
            $this->assertNotEquals('7788', $contact4->officePhone);
            $this->setGetArray(array(
                'selectedIds'  => $superContactId . ',' . $superContactId2, // Not Coding Standard
                'selectAll'    => '',
                'Contact_page' => 1));
            $this->setPostArray(array(
                'Contact'      => array('officePhone' => '7788'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/massEdit');
            //Test that the 2 contacts have the new office phone number and the other contacts do not.
            $contact1 = Contact::getById($superContactId);
            $contact2 = Contact::getById($superContactId2);
            $contact3 = Contact::getById($superContactId3);
            $contact4 = Contact::getById($superContactId4);
            $this->assertEquals   ('7788', $contact1->officePhone);
            $this->assertEquals   ('7788', $contact2->officePhone);
            $this->assertNotEquals('7788', $contact3->officePhone);
            $this->assertNotEquals('7788', $contact4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll'    => '1',
                'Contact_page' => 1));
            $this->setPostArray(array(
                'Contact'      => array('officePhone' => '1234'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/massEdit');
            //Test that all accounts have the new phone number.
            $contact1 = Contact::getById($superContactId);
            $contact2 = Contact::getById($superContactId2);
            $contact3 = Contact::getById($superContactId3);
            $contact4 = Contact::getById($superContactId4);
            $this->assertEquals   ('1234', $contact1->officePhone);
            $this->assertEquals   ('1234', $contact2->officePhone);
            $this->assertEquals   ('1234', $contact3->officePhone);
            $this->assertEquals   ('1234', $contact4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('contacts/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":50') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":75') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('contacts/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":100') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for Contact
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/autoComplete');
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/variableContactState/autoCompleteAllContacts');
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/variableContactState/autoCompleteAllContactsForMultiSelectAutoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/modalList');

            //actionModalListAllContacts
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('contacts/variableContactState/modalListAllContacts');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $superContactId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/auditEventsModalList');

            //Select a related Opportunity for this contact. Go to the select screen.
            $contact1->forget();
            $contact1 = Contact::getById($superContactId);
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals(1, count($portlets));
            $this->assertEquals(2, count($portlets[1]));
            $opportunity = Opportunity::getById($superOpportunityId);
            $this->assertEquals(0, $contact1->opportunities->count());
            $this->assertEquals(0, $opportunity->contacts->count());
            $this->setGetArray(array(   'portletId'             => $portlets[1][1]->id, //Doesnt matter which portlet we are using
                                        'relationAttributeName' => 'contacts',
                                        'relationModuleId'      => 'contacts',
                                        'relationModelId'       => $superContactId,
                                        'uniqueLayoutId'        => 'ContactDetailsAndRelationsViewLeftBottomView_' .
                                                                    $portlets[1][1]->id)
            );

            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('opportunities/default/SelectFromRelatedList');
            //Now add an opportunity to a contact via the select from related list action.
            $this->setGetArray(array(   'portletId'             => $portlets[1][1]->id,
                                        'modelId'               => $superOpportunityId,
                                        'relationAttributeName' => 'contacts',
                                        'relationModuleId'      => 'contacts',
                                        'relationModelId'       => $superContactId,
                                        'uniqueLayoutId'        => 'ContactDetailsAndRelationsViewLeftBottomView_' .
                                                                    $portlets[1][1]->id)
            );
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('opportunities/defaultPortlet/SelectFromRelatedListSave');
            //Run forget in order to refresh the contact and opportunity showing the new relation
            $contact1->forget();
            $opportunity->forget();
            $contact     = Contact::getById($superContactId);
            $opportunity = Opportunity::getById($superOpportunityId);
            $this->assertEquals(1,                $opportunity->contacts->count());
            $this->assertEquals($contact,         $opportunity->contacts[0]);
            $this->assertEquals(1,                $contact->opportunities->count());
            $this->assertEquals($opportunity->id, $contact->opportunities[0]->id);
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superContactId2 = self::getModelIdByModelNameAndName('Contact', 'superContact2 superContact2son');

            //Save a layout change. Collapse all portlets in the Contact Details View.
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (2, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['ContactDetailsAndRelationsViewLeftBottomView_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'ContactDetailsAndRelationsViewLeftBottomView_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 2 portlets.
            $this->assertEquals(2, $portletCount);
            $this->resetGetArray();
            $this->setPostArray(array(
                'portletLayoutConfiguration' => array(
                    'portlets' => $portletPostData,
                    'uniqueLayoutId' => 'ContactDetailsAndRelationsViewLeftBottomView',
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed and moved to the first column.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            'ContactDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (2, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            foreach ($portlets as $column => $columns)
            {
                foreach ($columns as $position => $positionPortlets)
                {
                    $this->assertEquals('1', $positionPortlets->collapsed);
                }
            }
            //Load Details View again to make sure everything is ok after the layout change.
            $this->setGetArray(array('id' => $superContactId2));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
        }

        /**
         * @depends testSuperUserDefaultPortletControllerActions
         */
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superContactId4 = self::getModelIdByModelNameAndName ('Contact', 'superContact4 superContact4son');
            //Delete a contact.
            $this->setGetArray(array('id' => $superContactId4));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/delete');
            $contacts = Contact::getAll();
            $this->assertEquals(3, count($contacts));
            try
            {
                Contact::getById($superContactId4);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Confirm the starting states exist
            $this->assertEquals(6, count(ContactState::GetAll()));
            $startingState = ContactsUtil::getStartingState();
            //Create a new contact.
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                                            'firstName'        => 'myNewContact',
                                            'lastName'         => 'myNewContactson',
                                            'officePhone'      => '456765421',
                                            'state'            => array('id' => $startingState->id)
                                            )
                                      )
                                );
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/create');
            $contacts = Contact::getByName('myNewContact myNewContactson');
            $this->assertEquals(1, count($contacts));
            $this->assertTrue($contacts[0]->id > 0);
            $this->assertTrue($contacts[0]->owner == $super);
            $this->assertTrue($contacts[0]->state == $startingState);
            $this->assertEquals('456765421', $contacts[0]->officePhone);
            $contacts = Contact::getAll();
            $this->assertEquals(4, count($contacts));

            //todo: test save with account.
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserCreateFromRelationAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $startingState = ContactsUtil::getStartingState();
            $contacts      = Contact::getAll();
            $this->assertEquals(4, count($contacts));
            $account       = Account::getByName('superAccount2');
            $opportunity   = OpportunityTestHelper::createOpportunityWithAccountByNameForOwner(
                                'superOpp', $super, $account[0]);

            //Create a new contact from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                                        'relationModelId'       => $account[0]->id,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('Contact' => array(
                                        'firstName'         => 'another',
                                        'lastName'          => 'anotherson',
                                        'officePhone'       => '456765421',
                                        'state'             => array('id' => $startingState->id))));
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/createFromRelation');
            $contacts = Contact::getByName('another anotherson');
            $this->assertEquals(1, count($contacts));
            $this->assertTrue($contacts[0]->id > 0);
            $this->assertTrue($contacts[0]->owner   == $super);
            $this->assertTrue($contacts[0]->account == $account[0]);
            $this->assertTrue($contacts[0]->state   == $startingState);
            $this->assertEquals('456765421', $contacts[0]->officePhone);
            $contacts = Contact::getAll();
            $this->assertEquals(5, count($contacts));

            //Create a new contact from a related opportunity
            $this->setGetArray(array(   'relationAttributeName' => 'opportunities',
                                        'relationModelId'       => $opportunity->id,
                                        'relationModuleId'      => 'opportunities',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('Contact' => array(
                                        'firstName'         => 'bnother',
                                        'lastName'          => 'bnotherson',
                                        'officePhone'       => '456765421',
                                        'state'             => array('id' => $startingState->id))));
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/createFromRelation');
            $contacts = Contact::getByName('bnother bnotherson');
            $this->assertEquals(1, count($contacts));
            $this->assertTrue($contacts[0]->id > 0);
            $this->assertTrue($contacts[0]->owner   == $super);
            $this->assertEquals(1, $contacts[0]->opportunities->count());
            $this->assertTrue($contacts[0]->opportunities[0] == $opportunity);
            $this->assertTrue($contacts[0]->state   == $startingState);
            $this->assertEquals('456765421', $contacts[0]->officePhone);
            $contacts = Contact::getAll();
            $this->assertEquals(6, count($contacts));

            //todo: test save with account.
        }
    }
?>