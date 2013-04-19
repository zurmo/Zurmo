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

    class ConversationsMashableInboxRulesTest extends ZurmoWalkthroughBaseTest
    {
        private $rules;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            ReadPermissionsOptimizationUtil::rebuild();
            $steven = UserTestHelper::createBasicUser('steven');
            //Give user access, create, delete for conversation rights.
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_ACCESS_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_CREATE_CONVERSATIONS);
            $steven->setRight('ConversationsModule', ConversationsModule::RIGHT_DELETE_CONVERSATIONS);
            $steven->save();
        }

        public function setUp()
        {
            parent::setUp();
            $this->rules               = new ConversationMashableInboxRules();
        }

        public function testListActionRenderListViewsForConversation()
        {
            $this->setGetArray(array('modelClassName' => 'Conversation'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven  = User::getByUsername('steven');
            $this->createAndSaveNewConversationForUser($super, $steven);
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->assertContains($this->rules->getListViewClassName(),   $content);
            $this->assertContains('list-view-markRead',                   $content);
            $this->assertContains('list-view-markUnread',                 $content);
            $this->assertContains('list-view-closeSelected',              $content);
        }

        public function testReadUnread()
        {
            $super                      = User::getByUsername('super');
            $steven                     = User::getByUsername('steven');
            Yii::app()->user->userModel = $super;
            $createdConversation        = $this->createAndSaveNewConversationForUser($super, $steven);
            $this->assertTrue((bool)$createdConversation->ownerHasReadLatest);
            $this->assertTrue((bool)$this->rules->hasCurrentUserReadLatest($createdConversation->id));
            $this->rules->resolveMarkUnread($createdConversation->id);
            $savedConversation          = Conversation::getById($createdConversation->id);
            $this->assertFalse((bool)$savedConversation->ownerHasReadLatest);
            $this->assertFalse((bool)$this->rules->hasCurrentUserReadLatest($createdConversation->id));
            $this->rules->resolveMarkRead($createdConversation->id);
            $savedConversation          = Conversation::getById($createdConversation->id);
            $this->assertTrue((bool)$savedConversation->ownerHasReadLatest);
            $this->assertTrue((bool)$this->rules->hasCurrentUserReadLatest($createdConversation->id));
        }

        public function testResolveCloseSelected()
        {
            $super                      = User::getByUsername('super');
            $steven                     = User::getByUsername('steven');
            Yii::app()->user->userModel = $super;
            $createdConversation        = $this->createAndSaveNewConversationForUser($super, $steven);
            $this->rules->resolveCloseSelected($createdConversation->id);
            $conversation               = Conversation::getById($createdConversation->id);
            $this->assertTrue((bool)$conversation->isClosed);
        }

        protected function createAndSaveNewConversationForUser(User $owner, User $participant)
        {
            foreach (Conversation::getAll() as $conversation)
            {
                $conversation->delete();
            }
            $conversation                           = new Conversation();
            $conversation->owner                    = $owner;
            $conversation->subject                  = 'My test conversation subject';
            $conversation->description              = 'My test conversation description';
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $participant;
            $conversation->conversationParticipants->add($conversationParticipant);
            $this->assertTrue($conversation->save());
            return $conversation;
        }

        protected function resolveControlerActionListAndGetContent($filteredBy, $optionForModel)
        {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'modelClassName'    => 'Conversation',
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'filteredBy'     => $filteredBy,
                                    'optionForModel' => $optionForModel
                                )
                        )
                    );
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            return $content;
        }

        public function testFilters()
        {
            $steven                           = User::getByUsername('steven');
            $super                            = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $conversation                     = $this->createAndSaveNewConversationForUser($super, $steven);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED);
            $this->assertContains($conversation->subject,       $content);
            $this->assertContains('1 result(s)',                $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
            $this->assertContains($conversation->subject,       $content);
            $this->assertContains('1 result(s)',                $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);

            //Testing with closed conversation
            $conversation->isClosed = true;
            $conversation->save();
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CREATED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_PARTICIPANT);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED);
            $this->assertContains($conversation->subject,       $content);
            $this->assertContains('1 result(s)',                $content);
            $content                          = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    ConversationsSearchDataProviderMetadataAdapter::LIST_TYPE_CLOSED);
            $this->assertNotContains($conversation->subject,    $content);
            $this->assertNotContains('result(s)',               $content);
        }

        public function testSearch()
        {
            $super                      = User::getByUsername('super');
            $steven                     = User::getByUsername('steven');
            Yii::app()->user->userModel = $super;
            $createdConversation        = $this->createAndSaveNewConversationForUser($super, $steven);
            $metadataForSearch          = $this->rules->getSearchAttributeData();
            $dataProvider               = new RedBeanModelDataProvider('Conversation', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("sub");
            $dataProvider               = new RedBeanModelDataProvider('Conversation', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("description");
            $dataProvider               = new RedBeanModelDataProvider('Conversation', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(0, count($data));
        }
    }
?>