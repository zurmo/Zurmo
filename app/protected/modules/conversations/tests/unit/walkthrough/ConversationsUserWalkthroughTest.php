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
     * Conversations Module User Walkthrough.
     * Walkthrough for the users of all possible controller actions.
     */
    class ConversationsUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ReadPermissionsOptimizationUtil::rebuild();

            //Setup test data owned by the super user.
            AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            //Create test users
            $steven                             = UserTestHelper::createBasicUser('steven');
            $steven->primaryEmail->emailAddress = 'steven@testzurmo.com';
            $sally                              = UserTestHelper::createBasicUser('sally');
            $sally->primaryEmail->emailAddress  = 'sally@testzurmo.com';
            $mary                               = UserTestHelper::createBasicUser('mary');
            $mary->primaryEmail->emailAddress  = 'mary@testzurmo.com';

            //give 3 users access, create, delete for conversation rights.
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $steven->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $sally->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $sally->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $mary->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $saved = $mary->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function testSuperUserAllSimpleControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/create');
        }

        /**
         * @depends testSuperUserAllSimpleControllerActions
         */
        public function testSuperUserCreateConversation()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            //Confirm no email notifications are sitting in the queue
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Test creating conversation via POST, invite Mary
            $conversations = Conversation::getAll();
            $this->assertEquals(0, count($conversations));
            $itemPostData = array('Account' => array('id' => $superAccountId));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item')),
                                      'ConversationItemForm'         => $itemPostData,
                                      'Conversation'                 => array('subject' => 'TestSubject',
                                                                          'description' => 'TestDescription')));
            $this->runControllerWithRedirectExceptionAndGetContent('conversations/default/create');

            //Confirm conversation saved.
            $conversations = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            //Confirm conversation is connected to the related account.
            $this->assertEquals(1, $conversations[0]->conversationItems->count());
            $this->assertEquals($accounts[0], $conversations[0]->conversationItems->offsetGet(0));

            //Confirm Mary is invited.
            $this->assertEquals(1,     $conversations[0]->conversationParticipants->count());
            $this->assertEquals($mary, $conversations[0]->conversationParticipants->offsetGet(0)->person);
            $this->assertEquals(0,     $conversations[0]->conversationParticipants->offsetGet(0)->hasReadLatest);

            //Confirm Mary got the email invite and it was correctly setup with a valid conversation id
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(1, count($emailMessages));
            $this->assertfalse(strpos($emailMessages[0]->content->textContent,
                                       'conversations/default/details?id=' . $conversations[0]->id . '">') === true);

            //Confirm Mary is the only one with explicit permissions on the conversation
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$mary->id]));
        }

        /**
         * @depends testSuperUserCreateConversation
         */
        public function testInvitingAndUnivitingUsersOnExistingConversation()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //Test inviting steven and sally (via detailview)
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item') . ',' . // Not Coding Standard
                                                                                           $steven->getClassId('Item') . ',' . // Not Coding Standard
                                                                                           $sally->getClassId('Item'))));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());

            //should be 2 explicits read/write
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(3, count($readWritePermitables));

            //Uninvite mary (via detailview)
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $steven->getClassId('Item') . ',' . // Not Coding Standard
                                                                                           $sally->getClassId('Item'))));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            //should be 2 explicits read/write
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversations[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(2, count($readWritePermitables));
        }

        /**
         * @depends testInvitingAndUnivitingUsersOnExistingConversation
         */
        public function testAddingCommentsAndUpdatingActivityStampsOnConversation()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->assertEquals(0, $conversations[0]->comments->count());
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $oldStamp        = $conversations[0]->latestDateTime;

            //Validate comment
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'comment-inline-edit-form',
                                      'Comment' => array('description' => 'a ValidComment Name')));

            $content = $this->runControllerWithExitExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //Now save that comment.
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $conversations[0]->id;
            $conversations[0]->forget();
            $conversation = Conversation::getById($id);
            $this->assertEquals(1, $conversation->comments->count());

            //should update latest activity stamp
            $this->assertNotEquals($oldStamp, $conversations[0]->latestDateTime);
            $newStamp = $conversations[0]->latestDateTime;
            sleep(2); // Sleeps are bad in tests, but I need some time to pass
            //Mary is not a participant, so she should not be able to add a comment
            $mary = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithAccessDeniedSecurityExceptionAndGetContent('comments/default/inlineCreateSave');

            //Add mary as a participant.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item'))));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);

            //Mary can add comment ok
            $mary = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $conversations[0]->id;
            $conversations[0]->forget();
            $conversation = Conversation::getById($id);
            $this->assertEquals(2, $conversation->comments->count());
            $this->assertNotEquals($newStamp, $conversation->latestDateTime);

            //Remove mary as a participant. should get redirect content
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants');
            $this->assertEquals('redirectToList', $content);
        }

        /**
         * @depends testAddingCommentsAndUpdatingActivityStampsOnConversation
         */
        public function testUserEditAndDeletePermissions()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->assertEquals(2, $conversations[0]->comments->count());

            //Add mary back as a participant.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->setPostArray(array('ConversationParticipantsForm' => array('itemIds' => $mary->getClassId('Item'))));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/updateParticipants', true);

            //new test - mary, as a participant can edit the conversation
            $mary           = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/edit');

            //new test - mary can delete a comment she wrote
            $maryCommentId = $conversations[0]->comments->offsetGet(1)->id;
            $this->assertEquals($conversations[0]->comments->offsetGet(1)->createdByUser->id, $mary->id);
            $superCommentId = $conversations[0]->comments->offsetGet(0)->id;
            $this->assertEquals($conversations[0]->comments->offsetGet(0)->createdByUser->id, $super->id);
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $maryCommentId));
            $this->runControllerWithNoExceptionsAndGetContent('comments/default/deleteViaAjax', true);
            $conversationId  = $conversations[0]->id;
            $conversations[0]->forget();
            $conversation = Conversation::getById($conversationId);
            $this->assertEquals(1, $conversation->comments->count());

            //new test - mary cannot delete a comment she did not write.
            $this->setGetArray(array('relatedModelId'             => $conversations[0]->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $superCommentId));
            $this->runControllerShouldResultInAjaxAccessFailureAndGetContent('comments/default/deleteViaAjax');
            $conversationId  = $conversations[0]->id;
            $conversations[0]->forget();
            $conversation = Conversation::getById($conversationId);
            $this->assertEquals(1, $conversation->comments->count());
            $this->assertEquals(1, $conversation->comments->count());

            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //new test , super can view and edit the conversation
            $this->setGetArray(array('id' => $conversation->id));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/edit');

            //new test , super can delete the conversation
            $this->setGetArray(array('id' => $conversation->id));
            $this->runControllerWithRedirectExceptionAndGetContent('conversations/default/delete');

            $conversations  = Conversation::getAll();
            $this->assertEquals(0, count($conversations));
        }

        /**
         * @depends testUserEditAndDeletePermissions
         */
        public function testDetailViewPortletFilteringOnConversations()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            //Load Details view to generate the portlets.
            $this->setGetArray(array('id' => $superAccountId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');

            //Find the LatestActivity portlet.
            $portletToUse = null;
            $portlets     = Portlet::getAll();
            foreach ($portlets as $portlet)
            {
                if ($portlet->viewType == 'AccountLatestActivitiesForPortlet')
                {
                    $portletToUse = $portlet;
                    break;
                }
            }
            $this->assertNotNull($portletToUse);
            $this->assertEquals('AccountLatestActivitiesForPortletView', get_class($portletToUse->getView()));

            //Load the portlet details for latest activity
            $getData = array('id' => $superAccountId,
                             'portletId' => $portletToUse->id,
                             'uniqueLayoutId' => 'AccountDetailsAndRelationsView_2',
                             'LatestActivitiesConfigurationForm' => array(
                                'filteredByModelName' => 'all',
                                'rollup' => false
                             ));
            $this->setGetArray($getData);
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Now add roll up
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
            //Now filter by conversation
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Conversation';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');

            //Now do the same thing with filtering but turn off rollup.
            $getData['LatestActivitiesConfigurationForm']['rollup'] = true;
            $getData['LatestActivitiesConfigurationForm']['filteredByModelName'] = 'Conversation';
            $this->setGetArray($getData);
            $content = $this->runControllerWithNoExceptionsAndGetContent('accounts/defaultPortlet/details');
        }

        /**
         * @depends testDetailViewPortletFilteringOnConversations
         */
        public function testListViewFiltering()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
            $this->setGetArray(array(
                'type' => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
            $this->setGetArray(array(
                'type' => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
            $this->setGetArray(array(
                'type' => ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('conversations/default/list');
            $this->assertfalse(strpos($content, 'Conversations') === false);
        }

        /**
         * @depends testListViewFiltering
         */
        public function testCreateFromModel()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts       = Account::getByName('superAccount');
            $superAccountId = $accounts[0]->id;

            $conversations  = Conversation::getAll();
            $this->assertEquals(0, count($conversations));

            //First just go to the createFromRelation action. Make sure it comes up right
            $this->setGetArray(array(   'relationAttributeName'  => 'notUsed',
                                        'relationModelClassName' => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'accounts',
                                        'redirectUrl'            => 'someRedirection'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/createFromRelation');

            //add related note for account using createFromRelation action
            $conversationItemPostData = array('account' => array('id' => $accounts[0]->id));
            $this->setGetArray(array(   'relationAttributeName'  => 'notUsed',
                                        'relationModelClassName' => 'Account',
                                        'relationModelId'        => $superAccountId,
                                        'relationModuleId'       => 'accounts',
                                        'redirectUrl'            => 'someRedirection'));
            $this->setPostArray(array('ConversationItemForm' => $conversationItemPostData,
                                      'Conversation' => array('subject' => 'Conversation Subject', 'description' => 'A description')));
            $this->runControllerWithRedirectExceptionAndGetContent('conversations/default/createFromRelation');

            $conversations = Conversation::getAll();
            $this->assertEquals(1,            count($conversations));
            $this->assertEquals(1,            $conversations[0]->conversationItems->count());
            $this->assertEquals($accounts[0]->getClassId('Item'), $conversations[0]->conversationItems->offsetGet(0)->getClassId('Item'));
        }

        /**
         * @depends testCreateFromModel
         */
        public function testCommentsAjaxListForRelatedModel()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $conversations  = Conversation::getAll();
            $this->assertEquals(1, count($conversations));
            $this->setGetArray(array('relatedModelId' => $conversations[0]->id, 'relatedModelClassName' => 'Conversation',
                                     'relatedModelRelationName' => 'comments'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('comments/default/ajaxListForRelatedModel');
        }

        /**
         * @depends testCommentsAjaxListForRelatedModel
         */
        public function testClosingConversations()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $conversation               = new Conversation();
            $conversation->owner        = $super;
            $conversation->subject      = "Test closed";
            $conversation->description  = "This is just to make the isClosed column in conversations table";
            $conversation->save();
            $conversations              = Conversation::getAll();
            $this->assertEquals(2, count($conversations));
            $this->assertEquals($super->id, $conversations[0]->owner->id);
            //Conversation is opened
            $this->assertEquals(0, $conversations[0]->resolveIsClosedForNull());
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/changeIsClosed');
            //Conversation is closed
            $this->assertEquals(1, $conversations[0]->resolveIsClosedForNull());
            $this->setGetArray(array('id' => $conversations[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('conversations/default/changeIsClosed');
            //Conversation is Re-opened
            $this->assertEquals(0, $conversations[0]->resolveIsClosedForNull());
        }

        /**
         * @depends testClosingConversations
         */
        public function testSendEmailInNewComment()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super                                  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven                                 = User::getByUsername('steven');
            $sally                                  = User::getByUsername('sally');
            $mary                                   = User::getByUsername('mary');
            $conversations                          = Conversation::getAll();
            $this->assertEquals(2, count($conversations));
            $this->assertEquals(0, $conversations[0]->comments->count());
            foreach (EmailMessage::getAll() as $emailMessage)
            {
                $emailMessage->delete();
            }
            $initalQueued                           = 0;
            $conversation                           = $conversations[0];
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $steven;
            $conversation->conversationParticipants->add($conversationParticipant);
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $sally;
            $conversation->conversationParticipants->add($conversationParticipant);
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $mary;
            $conversation->conversationParticipants->add($conversationParticipant);
            UserConfigurationFormAdapter::setValue($mary, true, 'turnOffEmailNotifications');
            //Save a new comment
            $this->setGetArray(array('relatedModelId'             => $conversation->id,
                                     'relatedModelClassName'      => 'Conversation',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals(1, $conversation->comments->count());
            $this->assertEquals($initalQueued + 1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages                          = EmailMessage::getAll();
            $emailMessage                           = $emailMessages[$initalQueued];
            $this->assertEquals(2, count($emailMessage->recipients));
            $this->assertContains('conversation', $emailMessage->subject);
            $this->assertContains(strval($conversation), $emailMessage->subject);
            $this->assertContains(strval($conversation->comments[0]), $emailMessage->content->htmlContent);
            $this->assertContains(strval($conversation->comments[0]), $emailMessage->content->textContent);
        }
    }
?>