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

    class EmailBoxTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
        }

        /**
         * @expectedException NotFoundException
         */
        public function testGetByNameNotificationsBoxDoesNotExist()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $boxes = EmailBox::getAll();
            $this->assertEquals(0, count($boxes));
            $box = EmailBox::getByName(EmailBox::NOTIFICATIONS_NAME);
        }

        /**
         * @depends testGetByNameNotificationsBoxDoesNotExist
         */
        public function testNotificationsBoxResolvesCorrectly()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $boxes = EmailBox::getAll();
            $this->assertEquals(0, count($boxes));
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $this->assertEquals(EmailBox::NOTIFICATIONS_NAME, $box->name);
            $this->assertEquals(4, $box->folders->count());
            $this->assertFalse($box->isDeletable());
            $this->assertTrue($box->id > 0);

            //After it saves, it should create a Sent folder and an Outbox folder
            $box = EmailBox::getByName(EmailBox::NOTIFICATIONS_NAME);
            $this->assertEquals(4, $box->folders->count());
            $folder1 = $box->folders->offsetGet(0);
            $folder2 = $box->folders->offsetGet(1);
            $folder3 = $box->folders->offsetGet(2);
            $folder4 = $box->folders->offsetGet(3);
            $this->assertTrue($folder1->name == EmailFolder::getDefaultSentName() ||
                              $folder1->name == EmailFolder::getDefaultOutboxName() ||
                              $folder1->name == EmailFolder::getDefaultDraftName() ||
                              $folder1->name == EmailFolder::getDefaultOutboxErrorName());
            $this->assertTrue($folder2->name == EmailFolder::getDefaultSentName() ||
                              $folder2->name == EmailFolder::getDefaultOutboxName() ||
                              $folder2->name == EmailFolder::getDefaultDraftName() ||
                              $folder2->name == EmailFolder::getDefaultOutboxErrorName());
            $this->assertTrue($folder3->name == EmailFolder::getDefaultSentName() ||
                              $folder3->name == EmailFolder::getDefaultOutboxName() ||
                              $folder3->name == EmailFolder::getDefaultDraftName() ||
                              $folder3->name == EmailFolder::getDefaultOutboxErrorName());
            $this->assertTrue($folder4->name == EmailFolder::getDefaultSentName() ||
                              $folder4->name == EmailFolder::getDefaultOutboxName() ||
                              $folder4->name == EmailFolder::getDefaultDraftName() ||
                              $folder4->name == EmailFolder::getDefaultOutboxErrorName());
            $this->assertNotEquals($folder1->name, $folder2->name);
            $this->assertNotEquals($folder1->name, $folder3->name);
            $this->assertNotEquals($folder1->name, $folder4->name);
            $this->assertNotEquals($folder2->name, $folder3->name);
            $this->assertNotEquals($folder2->name, $folder4->name);
            $this->assertNotEquals($folder3->name, $folder4->name);

            $boxes = EmailBox::getAll();
            $this->assertEquals(1, count($boxes));
            $this->assertTrue($boxes[0]->user->id < 0);
        }

        /**
         * @depends testNotificationsBoxResolvesCorrectly
         */
        public function testSetAndGetMailbox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $boxes = EmailBox::getAll();
            $this->assertEquals(1, count($boxes));

            $box = new EmailBox();
            $box->name = 'Some new mailbox';
            $saved     = $box->save();
            $this->assertTrue($saved);
            $this->assertEquals(0, $box->folders->count());
            $this->assertTrue($box->isDeletable());

            //Now try deleting the box
            $boxes = EmailBox::getAll();
            $this->assertEquals(2, count($boxes));
            $box->delete();
            $boxes = EmailBox::getAll();
            $this->assertEquals(1, count($boxes));
        }

        /**
         * @expectedException NotSupportedException
         * @depends testSetAndGetMailbox
         */
        public function testTryDeletingTheNotificationsBox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $box = EmailBox::getByName(EmailBox::NOTIFICATIONS_NAME);
            $box->delete();
        }
    }
?>