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

    class NotificationTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function testGetUnreadCountByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertEquals(0, Notification::getUnreadCountByUser(Yii::app()->user->userModel));
            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->owner  = Yii::app()->user->userModel;
            $notification->isRead = false;
            $this->assertTrue($notification->save());
            $this->assertEquals(1, Notification::getUnreadCountByUser(Yii::app()->user->userModel));
            $this->assertEquals(0, $notification->isRead);
            $notification->isRead = true;
            $this->assertTrue($notification->save());
            $notificationId = $notification->id;
            $notification->forget();

            //Retrieve again.
            $notification = Notification::getById($notificationId);
            $this->assertEquals('Simple', $notification->type);
            $this->assertEquals(1, $notification->isRead);
            $this->assertEquals(0, Notification::getUnreadCountByUser(Yii::app()->user->userModel));

            $notification->delete();
            $this->assertEquals(0, Notification::getUnreadCountByUser(Yii::app()->user->userModel));
        }

        /**
         * @depends testGetUnreadCountByUser
         */
        public function testNotification()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->owner  = Yii::app()->user->userModel;
            $notification->isRead = false;
            $this->assertTrue($notification->save());
            $this->assertEquals(0, $notification->isRead);
            $notification->isRead = true;
            $this->assertTrue($notification->save());
            $notificationId = $notification->id;
            $notification->forget();

            //Retrieve again.
            $notification = Notification::getById($notificationId);
            $this->assertEquals('Simple', $notification->type);
            $this->assertEquals(1, $notification->isRead);

            $notification->delete();
        }

        /**
         * @depends testNotification
         */
        public function testNotificationMessage()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $message              = new NotificationMessage();
            $message->textContent = 'text content';
            $message->htmlContent = 'html content';
            $this->assertTrue($message->save());

            $messageId = $message->id;
            $message->forget();

            //Retrieve again.
            $message = NotificationMessage::getById($messageId);
            $this->assertEquals('text content', $message->textContent);
            $this->assertEquals('html content', $message->htmlContent);
        }

        /**
         * @depends testNotificationMessage
         */
        public function testGetUnreadCountByTypeAndUser()
        {
            $super = User::getByUsername('super');
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(0, count(Notification::getAll()));

            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->owner  = $super;
            $notification->isRead = false;
            $this->assertTrue($notification->save());
            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->isRead = true;
            $notification->owner  = $super;
            $this->assertTrue($notification->save());

            //There are 2 notifications
            $this->assertEquals(2, count(Notification::getAll()));
            //But only 1 notification that is unread for super
            $this->assertEquals(1, Notification::getUnreadCountByTypeAndUser('Simple', $super));
            //And 0 notifications unread for billy
            $this->assertEquals(0, Notification::getUnreadCountByTypeAndUser('Simple', $billy));

            //Now add another super notification, but not simple.
            $notification         = new Notification();
            $notification->type   = 'Simple2Test';
            $notification->isRead = true;
            $notification->owner  = $super;
            $this->assertTrue($notification->save());
            //And there is still 1 unread notification for super
            $this->assertEquals(1, Notification::getUnreadCountByTypeAndUser('Simple', $super));

            //Add a notification for billy.
            $notification = new Notification();
            $notification->type = 'Simple';
            $notification->owner = $billy;
            $notification->isRead = false;
            $this->assertTrue($notification->save());
            //And there is still 1 unread notification for billy
            $this->assertEquals(1, Notification::getUnreadCountByTypeAndUser('Simple', $billy));
        }

        /**
         * @depends testGetUnreadCountByTypeAndUser
         */
        public function testNonAdminCanCreateNotificationsAndMessages()
        {
            $super = User::getByUsername('super');
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;

            //Billy can create a notification for billy
            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->owner  = $billy;
            $notification->isRead = false;
            $this->assertTrue($notification->save());

            //And Billy can create a notification for super
            $notification         = new Notification();
            $notification->type   = 'Simple';
            $notification->owner  = $super;
            $notification->isRead = false;
            $this->assertTrue($notification->save());

            //Same with a message.
            $message              = new NotificationMessage();
            $message->textContent = 'text content2';
            $message->htmlContent = 'html content2';
            $this->assertTrue($message->save());
        }

        /**
         * @depends testNonAdminCanCreateNotificationsAndMessages
         */
        public function testRelationsBetweenNotificationAndNotificationMessage()
        {
            $super = User::getByUsername('super');
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $super;

            //Make sure the relations between Notification and NotificationMessage is working.
            $message              = new NotificationMessage();
            $message->textContent = 'text content2';
            $message->htmlContent = 'html content2';
            $this->assertTrue($message->save());

            $notification = new Notification();
            $notification->type                = 'SimpleYTest';
            $notification->owner               = $billy;
            $notification->isRead              = false;
            $notification->notificationMessage = $message;
            $this->assertTrue($notification->save());

            //And Billy can create a notification for super
            $notification = new Notification();
            $notification->type                = 'SimpleZTest';
            $notification->owner               = $super;
            $notification->isRead              = false;
            $notification->notificationMessage = $message;
            $this->assertTrue($notification->save());

            //At this point the message should have 2 notifications associated with it
            $messageId = $message->id;
            $message->forget();
            $mesage = NotificationMessage::getById($messageId);

            $this->assertEquals(2, $message->notifications->count());
            $this->assertTrue($message->notifications[0]->type == 'SimpleYTest' ||
                              $message->notifications[0]->type == 'SimpleZTest');
            $this->assertTrue($message->notifications[1]->type == 'SimpleYTest' ||
                              $message->notifications[1]->type == 'SimpleZTest');

            /** - Add back in if it is possible to get the NotificationMessages to Notifications as RedBeanModel::OWNED
             * //Currently it is not working and cause $this->assertEquals(2, $message->notifications->count());
             * to return 0.
            //When removing a notificationMessage with notifications, the notifications should be
            //removed too.
            $this->assertEquals(8, count(Notification::getAll()));
            $message->delete();
            $this->assertEquals(3, count(Notification::getAll()));
            **/
        }
    }
?>