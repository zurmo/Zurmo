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

    class ConversationsUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testMarkUserHasReadLatest()
        {
            $super                     = User::getByUsername('super');
            $steven                    = UserTestHelper::createBasicUser('steven');

            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test subject';
            $conversation->description = 'My test description';
            $this->assertTrue($conversation->save());
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversation);
            $postData = array();
            $postData['itemIds'] = $steven->getClassId('Item');
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            $this->assertTrue($conversation->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);

            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);

            $conversation = Conversation::getById($id);
            $this->assertEquals(1, $conversation->ownerHasReadLatest);
            $this->assertEquals(0, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            //After running for super, nothing will change.
            ConversationsUtil::markUserHasReadLatest($conversation, $super);
            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);
            $conversation = Conversation::getById($id);
            $this->assertEquals(1, $conversation->ownerHasReadLatest);
            $this->assertEquals(0, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            //After running for steven, it will show read for him.
            ConversationsUtil::markUserHasReadLatest($conversation, $steven);
            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);
            $conversation = Conversation::getById($id);
            $this->assertEquals(1, $conversation->ownerHasReadLatest);
            $this->assertEquals(1, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            $conversation->ownerHasReadLatest = false;
            $this->assertTrue($conversation->save());
            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);
            $conversation = Conversation::getById($id);
            $this->assertEquals(0, $conversation->ownerHasReadLatest);
            $this->assertEquals(1, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            //Now try for Steven, nothing changes
            ConversationsUtil::markUserHasReadLatest($conversation, $steven);
            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);
            $conversation = Conversation::getById($id);
            $this->assertEquals(0, $conversation->ownerHasReadLatest);
            $this->assertEquals(1, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);

            //Now try for super, should change
            ConversationsUtil::markUserHasReadLatest($conversation, $super);
            $id = $conversation->id;
            $conversation->forget();
            unset($conversation);
            $conversation = Conversation::getById($id);
            $this->assertEquals(1, $conversation->ownerHasReadLatest);
            $this->assertEquals(1, $conversation->conversationParticipants->offsetGet(0)->hasReadLatest);
        }

        public function testResolvePeopleOnConversation()
        {
            $super                                  = Yii::app()->user->userModel;
            $jack                                   = UserTestHelper::createBasicUser('jack');
            $steven                                 = User::getByUsername('steven');
            $conversation                           = new Conversation();
            $conversation->owner                    = Yii::app()->user->userModel;
            $conversation->subject                  = 'Test People On Conversation';
            $conversation->description              = 'This is a test conversation';
            $people                                 = ConversationsUtil::
                                                        resolvePeopleOnConversation($conversation);
            $this->assertEquals(1, count($people));
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $jack;
            $conversation->conversationParticipants->add($conversationParticipant);
            $people                                 = ConversationsUtil::
                                                        resolvePeopleOnConversation($conversation);
            $this->assertEquals(2, count($people));
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $steven;
            $conversation->conversationParticipants->add($conversationParticipant);
            $people                                 = ConversationsUtil::
                                                        resolvePeopleOnConversation($conversation);
            $this->assertEquals(3, count($people));
            $this->assertTrue($conversation->save());
        }

        /**
         * @depends testResolvePeopleOnConversation
         */
        public function testResolvePeopleToSendNotificationToOnNewComment()
        {
            $super                                  = Yii::app()->user->userModel;
            $jack                                   = User::getByUsername('jack');
            $steven                                 = User::getByUsername('steven');
            $conversation                           = new Conversation();
            $conversation->owner                    = Yii::app()->user->userModel;
            $conversation->subject                  = 'Test People To Send Notification';
            $conversation->description              = 'This is a test conversation';
            $peopleToSendNotification               = ConversationsUtil::
                                                        resolvePeopleToSendNotificationToOnNewComment($conversation, $super);
            $this->assertEquals(0, count($peopleToSendNotification));
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $jack;
            $conversation->conversationParticipants->add($conversationParticipant);
            $peopleToSendNotification               = ConversationsUtil::
                                                        resolvePeopleToSendNotificationToOnNewComment($conversation, $super);
            $this->assertEquals(1, count($peopleToSendNotification));
            $this->assertEquals($jack, $peopleToSendNotification[0]);
        }
    }
?>