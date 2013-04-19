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

    class EmailFolderTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
        }

        public function testGetByBoxAndTypeForNotificationsBox()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $boxes = EmailBox::getAll();
            $this->assertEquals(0, count($boxes));
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $this->assertEquals(EmailBox::NOTIFICATIONS_NAME, $box->name);
            $this->assertEquals(7, $box->folders->count());
            $this->assertTrue($box->id > 0);

            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(EmailFolder::getDefaultOutboxName(), $folder->name);
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $folder->type);

            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);
            $this->assertEquals(EmailFolder::getDefaultSentName(), $folder->name);
            $this->assertEquals(EmailFolder::TYPE_SENT, $folder->type);

            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_ARCHIVED);
            $this->assertEquals(EmailFolder::getDefaultArchivedName(), $folder->name);
            $this->assertEquals(EmailFolder::TYPE_ARCHIVED, $folder->type);
        }

        /**
         * @depends testGetByBoxAndTypeForNotificationsBox
         */
        public function testSetAndGetFolder()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;

            $folders = EmailFolder::getAll();
            $this->assertEquals(7, count($folders));

            $box = new EmailBox();
            $box->name = 'Some new mailbox';
            $saved     = $box->save();
            $this->assertTrue($saved);
            $this->assertEquals(0, $box->folders->count());

            $folder = new EmailFolder();
            $folder->name = 'Billy\'s Inbox';
            $folder->type = EmailFolder::TYPE_INBOX;
            $saved = $folder->save();
            //Missing 'box', so it should not save
            $this->assertFalse($saved);

            $folder->emailBox = $box;
            $saved            = $folder->save();
            $this->assertTrue($saved);
            $folderId     = $folder->id;
            $folder->forget();
            unset($folder);
            $folder       = EmailFolder::getById($folderId);
            $this->assertEquals($box->id, $folder->emailBox->id);

            //Now check the box has the correct folder related to it
            $boxId = $box->id;
            $box->forget();
            unset($box);
            $box = EmailBox::getById($boxId);
            $this->assertEquals(1, $box->folders->count());
            $this->assertEquals('Billy\'s Inbox', $box->folders[0]->name);
            $this->assertEquals(EmailFolder::TYPE_INBOX, $box->folders[0]->type);

            $folders = EmailFolder::getAll();
            $this->assertEquals(8, count($folders));
            //Now delete billy's inbox
            $folder->delete();

            $folders = EmailFolder::getAll();
            $this->assertEquals(7, count($folders));

            $box->forget();
            unset($box);
            $box = EmailBox::getById($boxId);
            $this->assertEquals(0, $box->folders->count());
        }

        /**
         * @expectedException NotSupportedException
         * @depends testSetAndGetFolder
         */
        public function testFailureDeletingASpecialFolder()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Try deleting a folder that is in a reserved box like Notifications.
            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $folder->delete();
        }

        /**
         * @depends testFailureDeletingASpecialFolder
         */
        public function testCreatingABoxWithAllRequiredFoldersAutomaticallyCreated()
        {
            //todo: what about creating a box with the automatic folders...
        }

        /**
         * @depends testCreatingABoxWithAllRequiredFoldersAutomaticallyCreated
         */
        public function testAttemptingToDeleteFoldersWithEmailMessagesConnectedToThem()
        {
            //todo: what happens if you try to delete a folder with a message in it? What should happen?
        }
    }
?>
