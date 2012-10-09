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

    class MissionTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetMissionById()
        {
            $super                     = User::getByUsername('super');
            $fileModel                 = ZurmoTestHelper::createFileModel();
            $steven                    = UserTestHelper::createBasicUser('steven');
            $dueStamp                  = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);

            $mission              = new Mission();
            $mission->owner       = $super;
            $mission->takenByUser = $steven;
            $mission->dueDateTime = $dueStamp;
            $mission->description = 'My test description';
            $mission->reward      = 'My test reward';
            $mission->status      = Mission::STATUS_AVAILABLE;
            $mission->files->add($fileModel);
            $mission->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE);
            $this->assertTrue($mission->save());
            $id = $mission->id;
            $mission->forget();
            unset($mission);

            $mission = Mission::getById($id);
            $this->assertEquals('My test description',            $mission->description);
            $this->assertEquals('My test reward',                 $mission->reward);
            $this->assertEquals(Mission::STATUS_AVAILABLE,        $mission->status);
            $this->assertEquals($super,                           $mission->owner);
            $this->assertEquals($steven,                          $mission->takenByUser);
            $this->assertEquals(1,                                $mission->files->count());
            $this->assertEquals($fileModel,                       $mission->files->offsetGet(0));
            $this->assertEquals(1,                                $mission->ownerHasReadLatest);
            $this->assertEquals(0,                                $mission->takenByUserHasReadLatest);
            $this->assertEquals($dueStamp,                        $mission->dueDateTime);
        }

        /**
         * @depends testCreateAndGetMissionById
         */
        public function testAddingComments()
        {
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $mission  = $missions[0];
            $steven        = User::getByUserName('steven');
            $latestStamp   = $mission->latestDateTime;

            //latestDateTime should not change when just saving the mission
            $mission->takenByUserHasReadLatest = true;
            $mission->ownerHasReadLatest       = true;
            $this->assertTrue($mission->save());
            $this->assertEquals($latestStamp, $mission->latestDateTime);
            $this->assertEquals(1, $mission->ownerHasReadLatest);
            $this->assertEquals(1, $mission->takenByUserHasReadLatest);

            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Add comment, this should update the latestDateTime,
            //and also it should reset takenByUserHasReadLatest on mission participants
            $comment              = new Comment();
            $comment->description = 'This is my first comment';
            $mission->comments->add($comment);
            $this->assertTrue($mission->save());
            $this->assertNotEquals($latestStamp, $mission->latestDateTime);
            $this->assertEquals(0, $mission->takenByUserHasReadLatest);
            //super made the comment, so this should remain the same.
            $this->assertEquals(1, $mission->ownerHasReadLatest);

            //set it to read latest
            $mission->takenByUserHasReadLatest = true;
            $this->assertTrue($mission->save());

            //have steven make the comment. Now the ownerHasReadLatest should set to false,
            //and takenByUserHasReadLatest should remain true
            Yii::app()->user->userModel = $steven;
            $mission                    = Mission::getById($mission->id);
            $comment                    = new Comment();
            $comment->description       = 'This is steven`\s first comment';
            $mission->comments->add($comment);
            $this->assertTrue($mission->save());
            $this->assertEquals(1, $mission->takenByUserHasReadLatest);
            $this->assertEquals(0, $mission->ownerHasReadLatest);
            //todo: test also takenByUserHasReadLatest
        }

        /**
         * Test mission notifications
         * @depends testCreateAndGetMissionById
         */
        public function testMissionNotifications()
        {
            $super      = User::getByUsername('super');
            $steven     = User::getByUsername('steven');
            $this->assertEquals(1, Notification::getCountByUser($super));
            $this->assertEquals(1, Notification::getCountByUser($steven));
            $missions   = Mission::getAll();
            $mission = $missions[0];
            $mission->status = Mission::STATUS_TAKEN;
            $this->assertTrue($mission->save());
            $this->assertEquals(2, Notification::getCountByUser($super));
            $this->assertEquals(1, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_COMPLETED;
            $this->assertTrue($mission->save());
            $this->assertEquals(3, Notification::getCountByUser($super));
            $this->assertEquals(1, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_REJECTED;
            $this->assertTrue($mission->save());
            $this->assertEquals(3, Notification::getCountByUser($super));
            $this->assertEquals(2, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_ACCEPTED;
            $this->assertTrue($mission->save());
            $this->assertEquals(3, Notification::getCountByUser($super));
            $this->assertEquals(3, Notification::getCountByUser($steven));
        }

        /**
         * @depends testAddingComments
         */
        public function testDeleteMission()
        {
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $comments = Comment::getAll();
            $this->assertEquals(2, count($comments));

            foreach ($missions as $mission)
            {
                $missionId = $mission->id;
                $mission->forget();
                $mission   = Mission::getById($missionId);
                $deleted   = $mission->delete();
                $this->assertTrue($deleted);
            }

            //check that all comments are removed, since they are owned.
            $comments = Comment::getAll();
            $this->assertEquals(0, count($comments));
            $missions = Mission::getAll();
            $this->assertEquals(0, count($missions));
        }
    }
?>