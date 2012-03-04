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

    class EmailFolderTest extends BaseTest
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
            $this->assertEquals(2, $box->folders->count());
            $this->assertTrue($box->id > 0);

            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $this->assertEquals(EmailFolder::getDefaultOutboxName(), $folder->name);
            $this->assertEquals(EmailFolder::TYPE_OUTBOX, $folder->type);

            $folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);
            $this->assertEquals(EmailFolder::getDefaultSentName(), $folder->name);
            $this->assertEquals(EmailFolder::TYPE_SENT, $folder->type);
        }


        /**
         * @expects NotSupportedException
         * @depends testGetByBoxAndTypeForNotificationsBox
         */
        public function testCreatingAFolderWithAnInvalidType()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $folder = new EmailFolder();
            $folder->name = 'Some Folder';
            $folder->type = 'A folder type that does not exist';
        }

        /**
         * @depends testCreatingAFolderWithAnInvalidType
         */
        public function testSetAndGetFolder()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;

            $folders = EmailFolder::getAll();
            $this->assertEquals(2, count($folders));

            $box = new EmailBox();
            $box->name = 'Some new mailbox';
            $saved     = $box->save();
            $this->assertTrue($saved);
            $this->assertEquals(0, $box->folders->count());

            $folder = new EmailFolder();
            $folder->name = 'Billy\'s Inbox';
            $folder->type = EmailFolder::TYPE_INBOX;
            $folder->user = $billy;
            $saved = $folder->save();
            //Missing 'box', so it should not save
            $this->assertFalse($saved);

            $folder->box = $box;

            $this->assertTrue($saved);
            $folderId     = $folder->id;
            $folder->forget();
            unset($folder);
            $folder       = EmailFolder::getById($folderId);
            $this->assertEquals($box->id, $folder->box->id);

            //Now check the box has the correct folder related to it
            $boxId = $box->id;
            $box->forget();
            unset($box);
            $box = Box::getById($boxId);
            $this->assertEquals(1, $box->folders->count());
            $this->assertEquals('Billy\'s Inbox', $box->folders[0]->name);
            $this->assertEquals(EmailFolder::TYPE_INBOX, $box->folders[0]->type);

            $folders = EmailFolder::getAll();
            $this->assertEquals(3, count($folders));
            //Now delete billy's inbox
            $folder->delete();

            $folders = EmailFolder::getAll();
            $this->assertEquals(2, count($folders));

            $box->forget();
            unset($box);
            $box = Box::getById($boxId);
            $this->assertEquals(0, $box->folders->count());
        }

        /**
         * @expects NotSupportedException
         * @depends testSetAndGetFolder
         */
        public function testFailureDeletingASpecialFolder()
        {
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
            $this->assertFail();
            //todo: what about creating a box with the automatic folders...
        }

        /**
         * @depends testCreatingABoxWithAllRequiredFoldersAutomaticallyCreated
         */
        public function testAttemptingToDeleteFoldersWithEmailMessagesConnectedToThem()
        {
            $this->assertFail();
            //todo: what happens if you try to delete a folder with a message in it? What should happen?
        }
    }
?>