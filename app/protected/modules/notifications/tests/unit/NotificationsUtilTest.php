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

    class NotificationsUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function setup()
        {
            parent::setup();
        }

        public function teardown()
        {
            parent::setup();
        }

        public function testSubmitNonCritical()
        {
            $super                                    = User::getByUsername('super');
            $emailAddress                             = new Email();
            $emailAddress->emailAddress               = 'sometest@zurmoalerts.com';
            $super->primaryEmail                      = $emailAddress;
            $saved                                    = $super->save();
            $this->assertTrue($saved);
            $billy                                    = User::getByUsername('billy');
            $emailAddress                             = new Email();
            $emailAddress->emailAddress               = 'sometest2@zurmoalerts.com';
            $billy->primaryEmail                      = $emailAddress;
            $saved                                    = $billy->save();
            $this->assertTrue($saved);
            $notifications              = Notification::getAll();
            $this->assertEquals(0, count($notifications));
            $message                    = new NotificationMessage();
            $message->textContent       = 'text content';
            $message->htmlContent       = 'html content';
            $rules                      = new SimpleNotificationRules();
            $rules->addUser($super);
            $rules->addUser($billy);
            NotificationsUtil::submit($message, $rules);

            //It should not send an email because it is non-critical
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $notifications              = Notification::getAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }

        public function testSubmitCritical()
        {
            //todo:
            //setCritical($critical);
        }

        public function testSubmittingDuplicateNotifications()
        {
            //todo:
        }
    }
?>
