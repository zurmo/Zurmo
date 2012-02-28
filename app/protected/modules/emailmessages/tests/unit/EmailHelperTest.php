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

    class EmailHelperTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
            $someoneSuper = UserTestHelper::createBasicUser('someoneSuper');

            $group = Group::getByName('Super Administrators');
            $group->users->add($someoneSuper);
            $saved = $group->save();
            assert($saved); // Not Coding Standard
        }

        public function testSetAndGetUserToSendNotificationAs()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //It should default to the first super user available.
            $user = Yii::app()->emailHelper->getUserToSendNotifiactionsAs();
            $this->assertEqual($user, $super);

            //Set a differnt super admin user, then make sure it correctly retrieves it.
            $anotherSuper = User::getByUsername('someoneSuper');
            Yii::app()->emailHelper->setUserToSendNotifiactionsAs($anotherSuper);
            $user = Yii::app()->emailHelper->getUserToSendNotifiactionsAs();
            $this->assertEqual($user, $anotherSuper);
        }

        /**
         * @depends testSetAndGetUserToSendNotificationAs
         * @expectedException SomeException
         */
        public function testSetAndGetUserToSendNotificationAsLoggedInAsNonSuper()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $anotherSuper               = User::getByUsername('someoneSuper');
            $user                       = Yii::app()->emailHelper->getUserToSendNotifiactionsAs();
            $this->assertEqual($user, $anotherSuper);
        }

        /**
         * @depends LoggedAsNonSupertestSetAndGetUserToSendNotificationAs
         * @expectedException SomeException
         */
        public function testSetUserToSendNotificationsAsWhoIsNotASuperAdmin()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            Yii::app()->emailHelper->setUserToSendNotifiactionsAs($billy);

        }

        /**
         * @depends testSetUserToSendNotificationsAsWhoIsNotASuperAdmin
         */
        public function testSend()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueueCount());
            //todo: make an email message helper to make a test email message
            Yii::app()->emailHelper->send($emailMessage);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueueCount());
        }

        /**
         * @depends testSend
         */
        public function testSendQueued()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(1, Yii::app()->emailHelper->getQueueCount());
            Yii::app()->emailHelper->sendQueued();
            //todo: in the test override for EmailHelper, need a way to show that the emails are sent? or maybe this
            //can normally be in emailHelper. TBD
            $this->assertEquals(0, Yii::app()->emailHelper->getQueueCount());
        }

        /**
         * @depends testSendQueued
         */
        public function testSendImmediately()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueueCount());
            //todo: make an email message helper to make a test email message
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueueCount());
            //todo: again, figure out how to show a signal that this was 'sent' and not just queued...
            //maybe a getSentCount(), and a resetSent()?
        }
    }
?>