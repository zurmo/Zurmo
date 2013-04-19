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

    class ConversationParticipantsUtilTest extends ZurmoBaseTest
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

        public function testIsUserAParticipant()
        {
            $steven   = UserTestHelper::createBasicUser('steven');
            $conversation              = new Conversation();
            $conversation->owner       = Yii::app()->user->userModel;
            $conversation->subject     = 'My test subject2';
            $conversation->description = 'My test description2';
            $this->assertTrue($conversation->save());
            $this->assertFalse(ConversationParticipantsUtil::isUserAParticipant($conversation, $steven));

            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $steven;
            $conversation->conversationParticipants->add($conversationParticipant);
            $this->assertTrue($conversation->save());
            $this->assertTrue(ConversationParticipantsUtil::isUserAParticipant($conversation, $steven));
        }

        public function testResolveConversationHasManyParticipantsFromPost()
        {
            $super                     = Yii::app()->user->userModel;
            $mary                      = UserTestHelper::createBasicUser('mary');
            $steven                    = User::getByUsername('steven');
            $conversation              = new Conversation();
            $conversation->owner       = Yii::app()->user->userModel;
            $conversation->subject     = 'My test subject2';
            $conversation->description = 'My test description2';
            $this->assertTrue($conversation->save());

            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversation);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));

            //test no existing participants. Do not add owner of conversation
            $postData            = array();
            $postData['itemIds'] = $super->getClassId('Item');
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, $conversation->conversationParticipants->count());

            //test adding 2 more participants
            $postData            = array();
            $postData['itemIds'] = $super->getClassId('Item'). ',' . $steven->getClassId('Item') . ',' . $mary->getClassId('Item'); // Not Coding Standard
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(2, count($readWritePermitables));
            $this->assertEquals(2, $conversation->conversationParticipants->count());

            $this->assertTrue($conversation->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);

            //Just making sure the readWrite count is still 2
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversation);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(2, count($readWritePermitables));

            //test removing a participant (remove steven)
            $postData            = array();
            $postData['itemIds'] = $super->getClassId('Item') . ',' . $mary->getClassId('Item'); // Not Coding Standard
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            $this->assertTrue($conversation->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);

            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversation);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(1, $conversation->conversationParticipants->count());
            $this->assertEquals($mary, $conversation->conversationParticipants[0]->person);

            //test removing all participants.
            $postData             = array();
            $postData['itemIds']  = '';
            ConversationParticipantsUtil::resolveConversationHasManyParticipantsFromPost(
                                            $conversation, $postData, $explicitReadWriteModelPermissions);
            $this->assertTrue($conversation->save());
            $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($conversation, $explicitReadWriteModelPermissions);
            $this->assertTrue($success);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($conversation);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(0, count($readWritePermitables));
            $this->assertEquals(0, $conversation->conversationParticipants->count());
        }

        /**
         * @depends testIsUserAParticipant
         */
        public function testResolveConversationParticipants()
        {
            $super                                  = Yii::app()->user->userModel;
            $jack                                   = UserTestHelper::createBasicUser('jack');
            $steven                                 = User::getByUsername('steven');
            $conversation                           = new Conversation();
            $conversation->owner                    = Yii::app()->user->userModel;
            $conversation->subject                  = 'Test Resolve Conversation Participants';
            $conversation->description              = 'This is for testing conversation participants.';
            $this->assertTrue($conversation->save());
            $participants                           = ConversationParticipantsUtil::
                                                        getConversationParticipants($conversation);
            $this->assertEquals(0, count($participants));
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $jack;
            $conversation->conversationParticipants->add($conversationParticipant);
            $this->assertEquals(0, count($participants));
            $participants                           = ConversationParticipantsUtil::
                                                        getConversationParticipants($conversation);
            $this->assertEquals(1, count($participants));
            $conversationParticipant                = new ConversationParticipant();
            $conversationParticipant->person        = $steven;
            $conversation->conversationParticipants->add($conversationParticipant);
            $participants                           = ConversationParticipantsUtil::
                                                        getConversationParticipants($conversation);
            $this->assertEquals(2, count($participants));
        }
    }
?>