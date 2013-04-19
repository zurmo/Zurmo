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

    class MashableInboxUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        private $modelsWithMashableInboxInterface;

        public function setup()
        {
            parent::setUp();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->modelsWithMashableInboxInterface =
                array_keys(MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface'));
        }

        public function testListActionRenderListViewsForMashableInboxAndModels()
        {
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default');
            $this->assertContains('MashableInboxListView',  $content);
            $this->assertContains('list-view-markRead',     $content);
            $this->assertContains('list-view-markUnread',   $content);
            foreach ($this->modelsWithMashableInboxInterface as $modelClassName)
            {
                $this->setGetArray(array('modelClassName' => $modelClassName));
                $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
                $mashableRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
                if ($mashableRules->getZeroModelViewClassName() !== null)
                {
                    $this->assertContains($mashableRules->getZeroModelViewClassName(), $content);
                }
                else
                {
                    $this->assertContains($mashableRules->getListViewClassName(),   $content);
                    $this->assertContains('list-view-markRead',                     $content);
                    $this->assertContains('list-view-markUnread',                   $content);
                    foreach (array_keys($mashableRules->getMassOptions()) as $massAction)
                    {
                        $this->assertContains('list-view-' . $massAction, $content);
                    }
                }
            }
        }

        public function testMarkReadUnreadMassAction()
        {
            $super                     = User::getByUsername('super');
            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test conversation subject';
            $conversation->description = 'My test conversation description';
            $this->assertTrue($conversation->save());
            $conversationId            = $conversation->id;
            $mission                   = new Mission();
            $mission->owner            = $super;
            $mission->description      = 'My test mission description';
            $mission->status           = Mission::STATUS_AVAILABLE;
            $this->assertTrue($mission->save());
            $missionId                 = $mission->id;
            $this->assertTrue((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));
            $this->assertTrue((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //Mark conversation and mission as unread
            $selectedIds               = get_class($conversation) . '_' . $conversationId;
            $selectedIds              .= ',' . get_class($mission) . '_' . $missionId; // Not Coding Standard
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markUnread',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $conversation   = Conversation::getById($conversationId);
            $this->assertFalse((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));
            $mission        = Mission::getById($missionId);
            $this->assertFalse((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //Mark conversation and mission as read
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markRead',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $conversation   = Conversation::getById($conversationId);
            $this->assertTrue((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));
            $mission        = Mission::getById($missionId);
            $this->assertTrue((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //Mark conversation as unread
            $selectedIds    = get_class($conversation) . '_' . $conversationId;
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markUnread',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $conversation   = Conversation::getById($conversationId);
            $this->assertFalse((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));
            $mission        = Mission::getById($missionId);
            $this->assertTrue((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));
        }

        public function testMarkReadUnreadMassActionByModel()
        {
            $super                     = User::getByUsername('super');

            //Conversation model
            $conversation              = new Conversation();
            $conversation->owner       = $super;
            $conversation->subject     = 'My test conversation subject';
            $conversation->description = 'My test conversation description';
            $this->assertTrue($conversation->save());
            $conversationId            = $conversation->id;
            $this->assertTrue((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));

            //Mark conversation as unread
            $selectedIds               = $conversationId;
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'modelClassName'    => 'Conversation',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markUnread',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $conversation   = Conversation::getById($conversationId);
            $this->assertFalse((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));

            //Mark conversation as read
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'modelClassName'    => 'Conversation',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markRead',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content        = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $conversation   = Conversation::getById($conversationId);
            $this->assertTrue((bool)ConversationsUtil::hasUserReadConversationLatest($conversation, $super));

            //Mission model
            $mission                   = new Mission();
            $mission->owner            = $super;
            $mission->description      = 'My test mission description';
            $mission->status           = Mission::STATUS_AVAILABLE;
            $this->assertTrue($mission->save());
            $missionId                 = $mission->id;
            $this->assertTrue((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //Mark mission as unread
            $selectedIds               = $missionId;
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'modelClassName'    => 'Mission',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markUnread',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content  = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $mission  = Mission::getById($missionId);
            $this->assertFalse((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //Mark mission as read
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'modelClassName'    => 'Mission',
                            'MashableInboxForm' => array(
                                    'massAction'     => 'markRead',
                                    'selectedIds'    => $selectedIds,
                                )
                        )
                    );
            $content  = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $mission  = Mission::getById($missionId);
            $this->assertTrue((bool)MissionsUtil::hasUserReadMissionLatest($mission, $super));
        }

        public function testModuleSecurityAccess()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $nobody                     = UserTestHelper::createBasicUser('nobody');
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default');
            foreach ($this->modelsWithMashableInboxInterface as $modelClassName)
            {
                $this->setGetArray(array('modelClassName' => $modelClassName));
                $moduleClassName = $modelClassName::getModuleClassName();
                if (is_subclass_of($moduleClassName, 'SecurableModule'))
                {
                    $this->runControllerShouldResultInAccessFailureAndGetContent('mashableInbox/default/list');
                }
                else
                {
                    $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
                }
                $mashableRules  = MashableUtil::createMashableInboxRulesByModel($modelClassName);
            }
        }

        public function testSaveStickyOption()
        {
            $super                     = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->assertContains('value="all" checked="checked"', $content);
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'filteredBy'     => 'unread',
                                )
                        )
                    );
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->resetGetArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->assertContains('value="unread" checked="checked"', $content);
        }
    }
?>