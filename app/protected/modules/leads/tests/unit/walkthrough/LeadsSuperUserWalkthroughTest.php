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
     * Leads Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class LeadsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead',  $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead2', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead3', $super);
            LeadTestHelper::createLeadbyNameForOwner                 ('superLead4', $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser                          (Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('leads/default');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/create');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $leads = Contact::getAll();
            $this->assertEquals(4, count($leads));
            $superLeadId  = self::getModelIdByModelNameAndName('Contact', 'superLead superLeadson');
            $superLeadId2 = self::getModelIdByModelNameAndName('Contact', 'superLead2 superLead2son');
            $superLeadId3 = self::getModelIdByModelNameAndName('Contact', 'superLead3 superLead3son');
            $superLeadId4 = self::getModelIdByModelNameAndName('Contact', 'superLead4 superLead4son');
            $this->setGetArray(array('id' => $superLeadId));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');
            //Save lead.
            $superLead = Contact::getById($superLeadId);
            $this->assertEquals(null, $superLead->officePhone);
            $this->setPostArray(array('Contact' => array('officePhone' => '456765421')));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/edit');
            $superLead = Contact::getById($superLeadId);
            $this->assertEquals('456765421', $superLead->officePhone);
            //Test having a failed validation on the lead during save.
            $this->setGetArray (array('id'      => $superLeadId));
            $this->setPostArray(array('Contact' => array('lastName' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superLeadId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8,9', 'selectAll' => '')); // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>6</strong>&#160;records selected for updating') === false);

            //MassEdit view for all result selected ids
            $leads = Contact::getAll();
            $this->assertEquals(4, count($leads));
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEdit');
            $this->assertFalse(strpos($content, '<strong>4</strong>&#160;records selected for updating') === false);
            //save Model MassEdit for selected Ids
            //Test that the 4 leads do not have the office phone number we are populating them with.
            $lead1 = Contact::getById($superLeadId);
            $lead2 = Contact::getById($superLeadId2);
            $lead3 = Contact::getById($superLeadId3);
            $lead4 = Contact::getById($superLeadId4);
            $this->assertNotEquals('7788', $lead1->officePhone);
            $this->assertNotEquals('7788', $lead2->officePhone);
            $this->assertNotEquals('7788', $lead3->officePhone);
            $this->assertNotEquals('7788', $lead4->officePhone);
            $this->setGetArray(array(
                'selectedIds'  => $superLeadId . ',' . $superLeadId2, // Not Coding Standard
                'selectAll'    => '',
                'Contact_page' => 1));
            $this->setPostArray(array(
                'Contact'      => array('officePhone' => '7788'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/massEdit');
            //Test that the 2 leads have the new office phone number and the other contacts do not.
            $lead1 = Contact::getById($superLeadId);
            $lead2 = Contact::getById($superLeadId2);
            $lead3 = Contact::getById($superLeadId3);
            $lead4 = Contact::getById($superLeadId4);
            $this->assertEquals   ('7788', $lead1->officePhone);
            $this->assertEquals   ('7788', $lead2->officePhone);
            $this->assertNotEquals('7788', $lead3->officePhone);
            $this->assertNotEquals('7788', $lead4->officePhone);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll'    => '1',
                'Contact_page' => 1));
            $this->setPostArray(array(
                'Contact'      => array('officePhone' => '1234'),
                'MassEdit'     => array('officePhone' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/massEdit');
            //Test that all accounts have the new phone number.
            $lead1 = Contact::getById($superLeadId);
            $lead2 = Contact::getById($superLeadId2);
            $lead3 = Contact::getById($superLeadId3);
            $lead4 = Contact::getById($superLeadId4);
            $this->assertEquals('1234', $lead1->officePhone);
            $this->assertEquals('1234', $lead2->officePhone);
            $this->assertEquals('1234', $lead3->officePhone);
            $this->assertEquals('1234', $lead4->officePhone);

            //Run Mass Update using progress save.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 1);
            //The page size is smaller than the result set, so it should exit.
            $this->runControllerWithExitExceptionAndGetContent('leads/default/massEdit');
            //save Modal MassEdit using progress load for page 2, 3 and 4.
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 2));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":50') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 3));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":75') === false);
            $this->setGetArray(array('selectAll' => '1', 'Contact_page' => 4));
            $content = $this->runControllerWithNoExceptionsAndGetContent('leads/default/massEditProgressSave');
            $this->assertFalse(strpos($content, '"value":100') === false);
            //Set page size back to old value.
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);

            //Autocomplete for Lead
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/modalList');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $superLeadId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/auditEventsModalList');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superLeadId4 = self::getModelIdByModelNameAndName('Contact', 'superLead4 superLead4son');

            //Save a layout change. Collapse all portlets in the Lead Details View.
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                        'LeadDetailsAndRelationsViewLeftBottomView', $super->id, array());
            $this->assertEquals (2, count($portlets[1])         );
            $this->assertFalse  (array_key_exists(2, $portlets) );
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['LeadDetailsAndRelationsViewLeftBottomView_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'LeadDetailsAndRelationsViewLeftBottomView_' . $portlet->id,
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
                    'uniqueLayoutId' => 'LeadDetailsAndRelationsViewLeftBottomView',
                )
            ));
            $this->runControllerWithNoExceptionsAndGetContent('home/defaultPortlet/saveLayout', true);
            //Now test that all the portlets are collapsed and moved to the first column.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                            'LeadDetailsAndRelationsViewLeftBottomView', $super->id, array());
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
            $this->setGetArray(array('id' => $superLeadId4));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/details');
        }

        /**
         * @depends testSuperUserDefaultPortletControllerActions
         */
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superLeadId4 = self::getModelIdByModelNameAndName('Contact', 'superLead4 superLead4son');
            //Delete a lead.
            $this->setGetArray(array('id' => $superLeadId4));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete');
            $leads = Contact::getAll();
            $this->assertEquals(3, count($leads));
            try
            {
                Contact::getById($superLeadId4);
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
            $startingState = LeadsUtil::getStartingState();
            //Create a new contact.
            $this->resetGetArray();
            $this->setPostArray(array('Contact' => array(
                                            'firstName'        => 'myNewLead',
                                            'lastName'         => 'myNewLeadson',
                                            'officePhone'      => '456765421',
                                            'state'            => array('id' => $startingState->id)
                                            )
                                      )
                                );
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/default/create');
            $leads = Contact::getByName('myNewLead myNewLeadson');
            $this->assertEquals(1, count($leads));
            $this->assertTrue($leads[0]->id > 0);
            $this->assertTrue($leads[0]->owner == $super);
            $this->assertTrue($leads[0]->state == $startingState);
            $this->assertEquals('456765421', $leads[0]->officePhone);
            $leads = Contact::getAll();
            $this->assertEquals(4, count($leads));
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserConvertAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $startingLeadState    = LeadsUtil::getStartingState();
            $startingContactState = ContactsUtil::getStartingState();
            $leads = Contact::getByName('myNewLead myNewLeadson');
            $this->assertEquals(1, count($leads));
            $lead = $leads[0];
            $this->assertTrue($lead->state == $startingLeadState);
            //Test just going to the convert page.
            $this->setGetArray(array('id' => $lead->id));
            $this->resetPostArray();

            //Test trying to convert by skipping account creation
            $this->runControllerWithNoExceptionsAndGetContent('leads/default/convert');
            $this->setGetArray(array('id' => $lead->id));
            $this->setPostArray(array('AccountSkip' => 'Not Used'));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert');
            $leadId = $lead->id;
            $lead->forget();
            $contact = Contact::getById($leadId);
            $this->assertTrue($contact->state == $startingContactState);

            //Test trying to convert by creating a new account.
            $lead5 = LeadTestHelper::createLeadbyNameForOwner('superLead5', $super);
            $this->assertTrue($lead5->state == $startingLeadState);
            $this->setGetArray(array('id' => $lead5->id));
            $this->setPostArray(array('Account' => array('name' => 'someAccountName')));
            $this->assertEquals(0, count(Account::getAll()));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert');
            $this->assertEquals(1, count(Account::getAll()));
            $lead5Id = $lead5->id;
            $lead5->forget();
            $contact5 = Contact::getById($lead5Id);
            $this->assertTrue($contact5->state == $startingContactState);
            $this->assertEquals('someAccountName', $contact5->account->name);

            //Test trying to convert by selecting an existing account
            $account = AccountTestHelper::createAccountbyNameForOwner('someNewAccount', $super);
            $lead6 = LeadTestHelper::createLeadbyNameForOwner('superLead6', $super);
            $this->assertTrue($lead6->state == $startingLeadState);
            $this->setGetArray(array('id' => $lead6->id));
            $this->setPostArray(array('AccountSelectForm' => array('accountId' => $account->id,
                                                                   'accountName' => 'someNewAccount')));
            $this->assertEquals(2, count(Account::getAll()));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert');
            $this->assertEquals(2, count(Account::getAll()));
            $lead6Id = $lead6->id;
            $lead6->forget();
            $contact6 = Contact::getById($lead6Id);
            $this->assertTrue($contact6->state == $startingContactState);
            $this->assertEquals($account, $contact6->account);
        }

        public function testAccessingContactNotLeadWillRedirectToContacts()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $contact = ContactTestHelper::createContactbyNameForOwner('ContactNotLead',  $super);
            $this->setGetArray(array('id' => $contact->id));
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/edit',
                                                                   Yii::app()->createUrl('contacts/default/edit',    array('id' => $contact->id)), true);
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/details',
                                                                   Yii::app()->createUrl('contacts/default/details', array('id' => $contact->id)), true);
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/delete',
                                                                   Yii::app()->createUrl('contacts/default/delete',  array('id' => $contact->id)), true);
            $this->runControllerWithRedirectExceptionAndGetContent('leads/default/convert',
                                                                   Yii::app()->createUrl('contacts/default/details', array('id' => $contact->id)), true);
        }
    }
?>