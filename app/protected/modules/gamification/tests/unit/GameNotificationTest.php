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

    class GameNotificationTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testCreateAndGetGameNotificationById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            //Level up notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $user;
            $gameNotification->setLevelChangeByNextLevelValue(2);
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);

            //New badge notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $user;
            $gameNotification->setNewBadgeByType('LoginUser');
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);

            //Badge grade up notification
            $gameNotification           = new GameNotification();
            $gameNotification->user     = $user;
            $gameNotification->setBadgeGradeChangeByTypeAndNewGrade('LoginUser', 5);
            $saved                      = $gameNotification->save();
            $this->assertTrue($saved);
        }

        /**
         * @depends testCreateAndGetGameNotificationById
         */
        public function testGetAllByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $notifications = GameNotification::getAllByUser(User::getByUsername('super'));
            $this->assertEquals(0, count($notifications));
            $notifications = GameNotification::getAllByUser(Yii::app()->user->userModel);
            $this->assertEquals(3, count($notifications));

            $unserializedData = $notifications[0]->getUnserializedData();
            $this->assertEquals(GameNotification::TYPE_LEVEL_CHANGE, $unserializedData['type']);
            $this->assertEquals(2, $unserializedData['levelValue']);

            $unserializedData = $notifications[1]->getUnserializedData();
            $this->assertEquals(GameNotification::TYPE_NEW_BADGE, $unserializedData['type']);
            $this->assertEquals('LoginUser', $unserializedData['badgeType']);

            $unserializedData = $notifications[2]->getUnserializedData();
            $this->assertEquals(GameNotification::TYPE_BADGE_GRADE_CHANGE, $unserializedData['type']);
            $this->assertEquals('LoginUser', $unserializedData['badgeType']);
            $this->assertEquals(5, $unserializedData['grade']);
        }

        /**
         * @depends testGetAllByUser
         */
        public function testGameNotificationToModalContentAdapter()
        {
            Yii::app()->user->userModel = User::getByUsername('steven');
            $notifications = GameNotification::getAllByUser(Yii::app()->user->userModel);
            $this->assertEquals(3, count($notifications));

            $adapter1 = new GameNotificationToModalContentAdapter($notifications[0]);
            $adapter2 = new GameNotificationToModalContentAdapter($notifications[1]);
            $adapter3 = new GameNotificationToModalContentAdapter($notifications[2]);

            $this->assertEquals('game-level-change', $adapter1->getIconCssName());
            $this->assertEquals('game-badge-LoginUser', $adapter2->getIconCssName());
            $this->assertEquals('game-badge-LoginUser', $adapter3->getIconCssName());

            $this->assertEquals('<h2>Congratulations!</h2><h3>You have reached level 2</h3>',
                                $adapter1->getMessageContent());
            $this->assertEquals('<h2>New Badge</h2><h3>1 Zurmo login</h3>',
                                $adapter2->getMessageContent());
            $this->assertEquals('<h2>New Badge</h2><h3>75 Zurmo logins</h3>',
                                $adapter3->getMessageContent());
        }
    }
?>
