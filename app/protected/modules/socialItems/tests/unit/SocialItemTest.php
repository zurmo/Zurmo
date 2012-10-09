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

    class SocialItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetSocialItemById()
        {
            $super                     = User::getByUsername('super');
            $fileModel                 = ZurmoTestHelper::createFileModel();
            $accounts                  = Account::getByName('anAccount');
            $steven                    = UserTestHelper::createBasicUser('steven');
            $note                      = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('aNote', $super, $accounts[0]);

            $socialItem              = new SocialItem();
            $socialItem->owner       = $super;
            $socialItem->description = 'My test description';
            $socialItem->note        = $note;
            $socialItem->files->add($fileModel);
            $socialItem->toUser      = $steven;
            $saved                   = $socialItem->save();
            $this->assertTrue($saved);

            $id = $socialItem->id;
            $socialItem->forget();
            unset($socialItem);

            $socialItem = SocialItem::getById($id);
            $this->assertEquals($super,                           $socialItem->owner);
            $this->assertEquals('My test description',            $socialItem->description);
            $this->assertEquals($super,                           $socialItem->createdByUser);
            $this->assertEquals($note,                                $socialItem->note);
            $this->assertEquals(1,                                $socialItem->files->count());
            $this->assertEquals($fileModel,                       $socialItem->files->offsetGet(0));
            $this->assertEquals($steven,                          $socialItem->toUser);
        }

        /**
         * @depends testCreateAndGetSocialItemById
         */
        public function testAddingComments()
        {
            $socialItems = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $socialItem  = $socialItems[0];
            $steven        = User::getByUserName('steven');
            $latestStamp   = $socialItem->latestDateTime;

            //latestDateTime should not change when just saving the social item
            $this->assertTrue($socialItem->save());
            $this->assertEquals($latestStamp, $socialItem->latestDateTime);

            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Add comment, this should update the latestDateTime,
            $comment              = new Comment();
            $comment->description = 'This is my first comment';
            $socialItem->comments->add($comment);
            $this->assertTrue($socialItem->save());
            $this->assertNotEquals($latestStamp, $socialItem->latestDateTime);
        }

        /**
         * @depends testAddingComments
         */
        public function testDeleteSocialItem()
        {
            $socialItems = SocialItem::getAll();
            $this->assertEquals(1, count($socialItems));
            $comments    = Comment::getAll();
            $this->assertEquals(1, count($comments));
            $fileModels  = FileModel::getAll();
            $this->assertEquals(1, count($fileModels));

            foreach ($socialItems as $socialItem)
            {
                $socialItemId = $socialItem->id;
                $socialItem->forget();
                $socialItem   = SocialItem::getById($socialItemId);
                $deleted        = $socialItem->delete();
                $this->assertTrue($deleted);
            }

            $socialItems = SocialItem::getAll();
            $this->assertEquals(0, count($socialItems));
            //check that all comments are removed, since they are owned.
            $comments    = Comment::getAll();
            $this->assertEquals(0, count($comments));
            $fileModels  = FileModel::getAll();
            $this->assertEquals(0, count($fileModels));
        }

        public function testAddingNoteAndDeletingNoteAndThenTheSocialItemsAreRemoved()
        {
            $super                     = User::getByUsername('super');
            $this->assertEquals(0, count(SocialItem::getAll()));
            $accounts                  = Account::getByName('anAccount');
            $note                      = NoteTestHelper::createNoteWithOwnerAndRelatedAccount('aNote', $super, $accounts[0]);

            $socialItem              = new SocialItem();
            $socialItem->description = 'My test description';
            $socialItem->note        = $note;
            $saved                   = $socialItem->save();
            $this->assertTrue($saved);
            $socialItemId            = $socialItem->id;
            $noteId                  = $note->id;
            $note->forget();
            $this->assertEquals(1, count(SocialItem::getAll()));
            $note                    = Note::getById($noteId);
            $deleted = $note->delete();
            $this->assertTrue($deleted);
            $this->assertEquals(0, count(SocialItem::getAll()));
        }
    }
?>