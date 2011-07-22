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
     * Meeting module walkthrough tests for a super user.
     */
    class MeetingsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact2', $super, $account);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact3', $super, $account);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $superAccountId  = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superAccountId2 = self::getModelIdByModelNameAndName ('Account', 'superAccount2');
            $superContactId  = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $superContactId2  = self::getModelIdByModelNameAndName ('Contact', 'superContact2 superContact2son');
            $superContactId3  = self::getModelIdByModelNameAndName ('Contact', 'superContact3 superContact3son');
            $account  = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $contact  = Contact::getById($superContactId);
            $contact2  = Contact::getById($superContactId2);
            $contact3  = Contact::getById($superContactId3);

            //confirm no existing activities exist
            $activities = Activity::getAll();
            $this->assertEquals(0, count($activities));

            //Test just going to the create from relation view.
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/createFromRelation');

            //add related meeting for account using createFromRelation action
            $activityItemPostData = array('Account' => array('id' => $superAccountId));
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'Meeting' => array('name' => 'myMeeting', 'startDateTime' => '11/1/11 7:45 PM')));
            $this->runControllerWithRedirectExceptionAndGetContent('meetings/default/createFromRelation');

            //now test that the new meeting exists, and is related to the account.
            $meetings = Meeting::getAll();
            $this->assertEquals(1, count($meetings));
            $this->assertEquals('myMeeting', $meetings[0]->name);
            $this->assertEquals(1, $meetings[0]->activityItems->count());
            $activityItem1 = $meetings[0]->activityItems->offsetGet(0);
            $this->assertEquals($account, $activityItem1);

            //test viewing the existing meeting in a details view
            $this->setGetArray(array('id' => $meetings[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/details');


            //test editing an existing meeting and saving. Add a second relation, to a contact.
            //First just go to the edit view and confirm it loads ok.
            $this->setGetArray(array('id' => $meetings[0]->id, 'redirectUrl' => 'someRedirect'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('meetings/default/edit');
            //Save changes via edit action.
            $activityItemPostData = array(  'Account' => array('id' => $superAccountId), 'Contact' => array('id' => $superContactId));
            $this->setGetArray(array('id' => $meetings[0]->id, 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Meeting' => array('name' => 'myMeetingX')));
            $this->runControllerWithRedirectExceptionAndGetContent('meetings/default/edit');
            //Confirm changes applied correctly.
            $meetings = Meeting::getAll();
            $this->assertEquals(1, count($meetings));
            $this->assertEquals('myMeetingX', $meetings[0]->name);
            $this->assertEquals(2, $meetings[0]->activityItems->count());
            $activityItem1 = $meetings[0]->activityItems->offsetGet(0);
            $activityItem2 = $meetings[0]->activityItems->offsetGet(1);
            $this->assertEquals($account, $activityItem1);
            $this->assertEquals($contact, $activityItem2);

            //Remove contact relation.  Switch account relation to a different account.
            $activityItemPostData = array('Account' => array('id' => $superAccountId2));
            $this->setGetArray(array('id' => $meetings[0]->id));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Meeting' => array('name' => 'myMeetingX')));
            $this->runControllerWithRedirectExceptionAndGetContent('meetings/default/edit');
            //Confirm changes applied correctly.
            $meetings = Meeting::getAll();
            $this->assertEquals(1, count($meetings));
            $this->assertEquals('myMeetingX', $meetings[0]->name);
            $this->assertEquals(1, $meetings[0]->activityItems->count());
            $activityItem1 = $meetings[0]->activityItems->offsetGet(0);
            $this->assertEquals($account2, $activityItem1);

            //test removing a meeting.
            $this->setGetArray(array('id' => $meetings[0]->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('meetings/default/delete');
            //Confirm no more meetings exist.
            $meetings = Meeting::getAll();
            $this->assertEquals(0, count($meetings));

            //Test adding a meeting with multiple contacts
            $activityItemPostData = array('Account' => array('id' => $superAccountId),
                                          'Contact' => array('ids' =>
                                                $superContactId . ',' . $superContactId2 . ',' . $superContactId3)); // Not Coding Standard
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'Meeting' => array('name' => 'myMeeting2', 'startDateTime' => '11/1/11 7:45 PM')));
            $this->runControllerWithRedirectExceptionAndGetContent('meetings/default/createFromRelation');

            //now test that the new meeting exists, and is related to the account.
            $meetings = Meeting::getAll();
            $this->assertEquals(1, count($meetings));
            $this->assertEquals('myMeeting2', $meetings[0]->name);
            $this->assertEquals(4, $meetings[0]->activityItems->count());
            $activityItem1 = $meetings[0]->activityItems->offsetGet(0);
            $this->assertEquals($account, $activityItem1);
        }
    }
?>