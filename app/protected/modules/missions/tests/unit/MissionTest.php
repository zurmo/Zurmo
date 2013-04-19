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
            $this->assertEquals($dueStamp,                        $mission->dueDateTime);
            $this->assertTrue(MissionsUtil::hasUserReadMissionLatest($mission,  $super));
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $steven));
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
            $super         = User::getByUsername('super');
            $latestStamp   = $mission->latestDateTime;

            //latestDateTime should not change when just saving the mission
            $this->assertTrue($mission->save());
            $this->assertEquals($latestStamp, $mission->latestDateTime);

            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Add comment, this should update the latestDateTime,
            //and also it should mark takenByUser as not read latest
            $comment              = new Comment();
            $comment->description = 'This is my first comment';
            $mission->comments->add($comment);
            $this->assertTrue($mission->save());
            $this->assertNotEquals($latestStamp, $mission->latestDateTime);
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $steven));
            //super made the comment, so this should remain the same.
            $this->assertTrue(MissionsUtil::hasUserReadMissionLatest($mission, $super));

            //have steven make the comment. Now the owner HasReadLatest,
            //and takenByUser HasNotReadLatest
            Yii::app()->user->userModel = $steven;
            $mission                    = Mission::getById($mission->id);
            $comment                    = new Comment();
            $comment->description       = 'This is steven`\s first comment';
            $mission->comments->add($comment);
            $this->assertTrue($mission->save());
            $this->assertFalse(MissionsUtil::hasUserReadMissionLatest($mission, $super));
        }

        /**
         * Test mission notifications
         * @depends testCreateAndGetMissionById
         */
        public function testMissionNotifications()
        {
            $super      = User::getByUsername('super');
            $steven     = User::getByUsername('steven');
            $this->assertEquals(0, Notification::getCountByUser($super));
            $this->assertEquals(0, Notification::getCountByUser($steven));
            $missions   = Mission::getAll();
            $mission = $missions[0];
            $mission->status = Mission::STATUS_TAKEN;
            $this->assertTrue($mission->save());
            $this->assertEquals(1, Notification::getCountByUser($super));
            $this->assertEquals(0, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_COMPLETED;
            $this->assertTrue($mission->save());
            $this->assertEquals(2, Notification::getCountByUser($super));
            $this->assertEquals(0, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_REJECTED;
            $this->assertTrue($mission->save());
            $this->assertEquals(2, Notification::getCountByUser($super));
            $this->assertEquals(1, Notification::getCountByUser($steven));
            $mission->status = Mission::STATUS_ACCEPTED;
            $this->assertTrue($mission->save());
            $this->assertEquals(2, Notification::getCountByUser($super));
            $this->assertEquals(2, Notification::getCountByUser($steven));
        }

        /**
         * @depends testAddingComments
         */
        public function testDeleteMission()
        {
            $missions                    = Mission::getAll();
            $comments                    = Comment::getAll();
            $personsWhoHaveNotReadLatest = PersonWhoHaveNotReadLatest::getAll();
            $this->assertGreaterThan(0, count($missions));
            $this->assertGreaterThan(0, count($comments));
            $this->assertGreaterThan(0, count($personsWhoHaveNotReadLatest));

            foreach ($missions as $mission)
            {
                $missionId = $mission->id;
                $mission->forget();
                $mission   = Mission::getById($missionId);
                $deleted   = $mission->delete();
                $this->assertTrue($deleted);
            }

            //check that all comments and personsWhoHaveNotReadLatest are removed, since they are owned.
            $comments = Comment::getAll();
            $this->assertEquals(0, count($comments));
            $missions = Mission::getAll();
            $this->assertEquals(0, count($missions));
            $personsWhoHaveNotReadLatest = PersonWhoHaveNotReadLatest::getAll();
            $this->assertEquals(0, count($personsWhoHaveNotReadLatest));
        }
    }
?>