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
    }
?>