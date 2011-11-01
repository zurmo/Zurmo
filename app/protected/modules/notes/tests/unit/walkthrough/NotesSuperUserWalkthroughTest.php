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
     * Note module walkthrough tests for super users.
     */
    class NotesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
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
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $superAccountId  = self::getModelIdByModelNameAndName ('Account', 'superAccount');
            $superAccountId2 = self::getModelIdByModelNameAndName ('Account', 'superAccount2');
            $superContactId  = self::getModelIdByModelNameAndName ('Contact', 'superContact superContactson');
            $account  = Account::getById($superAccountId);
            $account2 = Account::getById($superAccountId2);
            $contact  = Contact::getById($superContactId);

            //confirm no existing activities exist
            $activities = Activity::getAll();
            $this->assertEquals(0, count($activities));

            //Test just going to the create from relation view.
            $this->setGetArray(array(   'relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                        'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/createFromRelation');

            //add related note for account using createFromRelation action
            $activityItemPostData = array('account' => array('id' => $superAccountId));
            $this->setGetArray(array('relationAttributeName' => 'Account', 'relationModelId' => $superAccountId,
                                     'relationModuleId'      => 'accounts', 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Note' => array('description' => 'myNote')));
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/createFromRelation');

            //now test that the new note exists, and is related to the account.
            $notes = Note::getAll();
            $this->assertEquals(1, count($notes));
            $this->assertEquals('myNote', $notes[0]->description);
            $this->assertEquals(1, $notes[0]->activityItems->count());
            $activityItem1 = $notes[0]->activityItems->offsetGet(0);
            $this->assertEquals($account, $activityItem1);

            //test viewing the existing note in a details view
            $this->setGetArray(array('id' => $notes[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/details');

            //test editing an existing note and saving.
            //First just go to the edit view and confirm it loads ok.
            $this->setGetArray(array('id' => $notes[0]->id, 'redirectUrl' => 'someRedirect'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('notes/default/edit');
            //Save changes via edit action.
            $activityItemPostData = array('Account' => array('id' => $superAccountId),
                                          'Contact' => array('id' => $superContactId));
            $this->setGetArray(array('id' => $notes[0]->id, 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Note' => array('description' => 'myNoteX')));
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/edit');
            //Confirm changes applied correctly.
            $notes = Note::getAll();
            $this->assertEquals(1, count($notes));
            $this->assertEquals('myNoteX', $notes[0]->description);
            $this->assertEquals(2, $notes[0]->activityItems->count());
            $activityItem1 = $notes[0]->activityItems->offsetGet(0);
            $activityItem2 = $notes[0]->activityItems->offsetGet(1);
            $this->assertEquals($account, $activityItem1);
            $this->assertEquals($contact, $activityItem2);

            //Remove contact relation.  Switch account relation to a different account.
            $activityItemPostData = array('Account' => array('id' => $superAccountId2));
            $this->setGetArray(array('id' => $notes[0]->id));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData, 'Note' => array('description' => 'myNoteX')));
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/edit');
            //Confirm changes applied correctly.
            $notes = Note::getAll();
            $this->assertEquals(1, count($notes));
            $this->assertEquals('myNoteX', $notes[0]->description);
            $this->assertEquals(1, $notes[0]->activityItems->count());
            $activityItem1 = $notes[0]->activityItems->offsetGet(0);
            $this->assertEquals($account2, $activityItem1);

            //Test validating an existing note via the inline edit validation (failed Validation)
            $activityItemPostData = array('Account' => array('id' => $superAccountId),
                                          'Contact' => array('id' => $superContactId));
            $this->setGetArray(array('id' => $notes[0]->id, 'redirectUrl' => 'someRedirect'));
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'ajax' => 'inline-edit-form',
                                      'Note' => array('description' => '')));
            $content = $this->runControllerWithExitExceptionAndGetContent('notes/default/inlineCreateSave');
            $this->assertTrue(strlen($content) > 20); //approximate, but should definetely be larger than 20.

            //Test validating an existing note via the inline edit validation (Success)
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'ajax' => 'inline-edit-form',
                                      'Note' => array('description' => 'a Valid Name of a Note')));
            $content = $this->runControllerWithExitExceptionAndGetContent('notes/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //Test saving an existing note via the inline edit validation
            $this->setPostArray(array('ActivityItemForm' => $activityItemPostData,
                                      'Note' => array('description' => 'a Valid Name of a Note')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('notes/default/inlineCreateSave');
            //Confirm changes applied correctly.
            $notes = Note::getAll();
            $this->assertEquals(2, count($notes));
            $this->assertEquals('a Valid Name of a Note', $notes[1]->description);

            //test removing a note.
            $this->setGetArray(array('id' => $notes[1]->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('notes/default/delete');
            //Confirm no more notes exist.
            $notes = Note::getAll();
            $this->assertEquals(1, count($notes));
        }
    }
?>