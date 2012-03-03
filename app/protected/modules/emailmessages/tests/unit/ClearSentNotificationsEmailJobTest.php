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

    class ClearSentNotificationsEmailJobTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
        }

        public function testRun()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy                      = User::getByUsername('billy');

            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);

            //Create 2 sent notifications, and set one with a date over a week ago (8 days ago) for the modifiedDateTime
            $emailMessage = EmailMessageTestHelper::createEmailMessage('My Email Message', $super, $billy);

            $emailMessage->folder       = $folder;
            $saved                      = $emailMessage->save();
            $this->assertTrue($saved);

            $modifiedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (60 * 60 *24 * 8));
            $sql = "Update item set modifieddatetime = '" . $modifiedDateTime . "' where id = " .
                   $emailMessage->getClassId('Item');
            R::exec($sql);

            $emailMessage2 = EmailMessageTestHelper::createEmailMessage('My Email Message', $super, $billy);
            $emailMessage2->folder      = $folder;
            $saved                      = $emailMessage2->save();
            $this->assertTrue($saved);
            $this->assertEquals(2, count(EmailMessage::getAll()));

            $job = new ClearSentNotificationsEmailJob();
            $this->assertTrue($job->run());
            $emailMessages = EmailMessage::getAll();
            $this->assertEquals(1, count($emailMessages));
            $this->assertEquals($emailMessage2->id, $emailMessages[0]->id);
        }
    }
?>