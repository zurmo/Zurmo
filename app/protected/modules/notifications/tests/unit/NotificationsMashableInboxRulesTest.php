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

    class NotificationsMashableInboxRulesTest extends ZurmoWalkthroughBaseTest
    {
        private $rules;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function setUp()
        {
            parent::setUp();
            $this->rules               = new NotificationMashableInboxRules();
        }

        public function testListActionRenderListViewsForNotification()
        {
            $this->setGetArray(array('modelClassName' => 'Notification'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->deleteAllNotifications();
            $this->createAndSaveNewNotificationForUser($super);
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->assertContains($this->rules->getListViewClassName(),   $content);
            $this->assertContains('list-view-markRead',                   $content);
            $this->assertContains('list-view-markUnread',                 $content);
            $this->assertContains('list-view-deleteSelected',             $content);
            $this->assertContains('list-view-deleteAll',                  $content);
        }

        public function testReadUnread()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllNotifications();
            $createdNotification        = $this->createAndSaveNewNotificationForUser($super);
            $this->assertEquals(1, $this->rules->getUnreadCountForCurrentUser(), 0);
            $this->rules->resolveMarkRead($createdNotification->id);
            $savedNotification          = Notification::getById($createdNotification->id);
            $this->assertTrue((bool)$savedNotification->ownerHasReadLatest);
            $this->assertTrue((bool)$this->rules->hasCurrentUserReadLatest($createdNotification->id));
            $this->rules->resolveMarkUnread($createdNotification->id);
            $savedNotification          = Notification::getById($createdNotification->id);
            $this->assertFalse((bool)$savedNotification->ownerHasReadLatest);
            $this->assertFalse((bool)$this->rules->hasCurrentUserReadLatest($createdNotification->id));
        }

        public function testDeleteSelected()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllNotifications();
            $createdNotification1       = $this->createAndSaveNewNotificationForUser($super);
            $createdNotification2       = $this->createAndSaveNewNotificationForUser($super);
            $createdNotification3       = $this->createAndSaveNewNotificationForUser($super);
            $this->rules->resolveDeleteSelected($createdNotification1->id);
            $allNotifications           = Notification::getAll();
            $this->assertNotContains($createdNotification1, $allNotifications);
            $this->assertContains   ($createdNotification2, $allNotifications);
            $this->assertContains   ($createdNotification3, $allNotifications);
        }

        public function testDeleteAll()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllNotifications();
            $createdNotification1       = $this->createAndSaveNewNotificationForUser($super);
            $createdNotification2       = $this->createAndSaveNewNotificationForUser($super);
            $createdNotification3       = $this->createAndSaveNewNotificationForUser($super);
            $this->rules->resolveDeleteAll();
            $allNotifications           = Notification::getAll();
            $this->assertNotContains($createdNotification1, $allNotifications);
            $this->assertNotContains($createdNotification2, $allNotifications);
            $this->assertNotContains($createdNotification3, $allNotifications);
        }

        protected function deleteAllNotifications()
        {
            foreach (Notification::getAll() as $notification)
            {
                $notification->delete();
            }
        }

        protected function createAndSaveNewNotificationForUser(User $owner)
        {
            $message              = new NotificationMessage();
            $message->textContent = 'text content';
            $message->htmlContent = 'html content';
            $notification         = new Notification();
            $notification->type                = 'SimpleYTest';
            $notification->owner               = $owner;
            $notification->notificationMessage = $message;
            $this->assertTrue($notification->save());
            return $notification;
        }

        protected function resolveControlerActionListAndGetContent($filteredBy, $optionForModel)
        {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'modelClassName'    => 'Notification',
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
            $super                              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->deleteAllNotifications();
            $notification                       = $this->createAndSaveNewNotificationForUser($super);
            $content                            = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    null);
            $this->assertContains(strval($notification),        $content);
            $this->assertContains('1 result(s)',                $content);
            $content                            = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    null);
            $this->assertContains(strval($notification),        $content);
            $this->assertContains('1 result(s)',                $content);
            $notification->ownerHasReadLatest   = true;
            $this->assertTrue($notification->save());
            $content                            = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    null);
            $this->assertContains(strval($notification),        $content);
            $this->assertContains('1 result(s)',                $content);
            $content                            = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    null);
            $this->assertNotContains(strval($notification),     $content);
            $this->assertNotContains('result(s)',               $content);
        }

        public function testSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllNotifications();
            $this->createAndSaveNewNotificationForUser($super);
            $searchAttributeData        = $this->rules->getSearchAttributeData();
            $dataProvider               = new RedBeanModelDataProvider('Notification', null, false, $searchAttributeData);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("tex");
            $dataProvider               = new RedBeanModelDataProvider('Notification', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("html");
            $dataProvider               = new RedBeanModelDataProvider('Notification', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("subject");
            $dataProvider               = new RedBeanModelDataProvider('Notification', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(0, count($data));
        }
    }
?>
