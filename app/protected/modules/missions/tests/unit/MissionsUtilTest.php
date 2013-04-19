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

    class MissionsUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ReadPermissionsOptimizationUtil::rebuild();
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();
            $super                = User::getByUsername('super');
            $steven               = UserTestHelper::createBasicUser('steven');
            $mission              = new Mission();
            $mission->owner       = $super;
            $mission->takenByUser = $steven;
            $mission->description = 'My test description';
            $mission->reward      = 'My test reward';
            $mission->status      = Mission::STATUS_AVAILABLE;
            $mission->addPermissions($everyoneGroup, Permission::READ_WRITE);
            assert($mission->save()); // Not Coding Standard
            ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($mission, $everyoneGroup);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testRenderDescriptionAndLatestForDisplayView()
        {
            $missions = Mission::getAll();
            $content = MissionsUtil::renderDescriptionAndLatestForDisplayView($missions[0]);
            $this->assertNotNull($content);
        }

        public function testMarkUserHasReadLatestAndMarkHasUserUnreadLatest()
        {
            $super                              = User::getByUsername('super');
            Yii::app()->user->userModel         = $super;
            $steven                             = User::getByUsername('steven');
            $missions                           = Mission::getAll();
            $mission                            = $missions[0];
            $this->assertTrue($mission->save());

            $this->assertEquals(1, MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertEquals(0, MissionsUtil::hasUserReadMissionLatest($mission, $steven));

            MissionsUtil::markUserHasUnreadLatest($mission, Yii::app()->user->userModel);
            $missions = Mission::getAll();
            $mission  = $missions[0];
            $this->assertEquals(0, MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertEquals(0, MissionsUtil::hasUserReadMissionLatest($mission, $steven));

            MissionsUtil::markUserHasReadLatest($mission, Yii::app()->user->userModel);
            $missions = Mission::getAll();
            $mission  = $missions[0];
            $this->assertEquals(1, MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertEquals(0, MissionsUtil::hasUserReadMissionLatest($mission, $steven));

            Yii::app()->user->userModel = User::getByUsername('steven');
            MissionsUtil::markUserHasReadLatest($mission, Yii::app()->user->userModel);
            $missions = Mission::getAll();
            $mission  = $missions[0];
            $this->assertEquals(1, MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertEquals(1, MissionsUtil::hasUserReadMissionLatest($mission, $steven));

            MissionsUtil::markUserHasUnreadLatest($mission, Yii::app()->user->userModel);
            $missions = Mission::getAll();
            $mission  = $missions[0];
            $this->assertEquals(1, MissionsUtil::hasUserReadMissionLatest($mission, $super));
            $this->assertEquals(0, MissionsUtil::hasUserReadMissionLatest($mission, $steven));
        }

        public function testMakeActiveActionElementType()
        {
            $this->assertEquals('MissionsAvailableLink',
                    MissionsUtil::makeActiveActionElementType(null));
            $this->assertEquals('MissionsAvailableLink',
                    MissionsUtil::makeActiveActionElementType(MissionsListConfigurationForm::LIST_TYPE_AVAILABLE));
            $this->assertEquals('MissionsCreatedLink',
                    MissionsUtil::makeActiveActionElementType(MissionsListConfigurationForm::LIST_TYPE_CREATED));
            $this->assertEquals('MissionsMineTakenButNotAcceptedLink',
                    MissionsUtil::makeActiveActionElementType(MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED));
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testMakeActiveActionElementTypeNotSupportedType()
        {
            MissionsUtil::makeActiveActionElementType(55);
        }

        public function testMakeDataProviderByType()
        {
            $missions = Mission::getAll();
            $mission  = $missions[0];
            $dataProvider = MissionsUtil::makeDataProviderByType($mission, null, 55);
            $this->assertTrue($dataProvider instanceof RedBeanModelDataProvider);
        }

        public function testResolvePeopleToSendNotificationToOnNewComment()
        {
            $super                              = User::getByUsername('super');
            Yii::app()->user->userModel         = $super;
            $steven                             = User::getByUsername('steven');
            $missions                           = Mission::getAll();
            $mission                            = $missions[0];
            $super->primaryEmail->emailAddress  = 'super@zurmo.org';
            $this->assertTrue($super->save());
            $steven->primaryEmail->emailAddress = 'steven@zurmo.org';
            $this->assertTrue($steven->save());
            // super updated mission
            $participants                       = MissionsUtil::
                    resolvePeopleToSendNotificationToOnNewComment($mission, $super);
            $this->assertEquals(1, count($participants));
            $this->assertEquals($participants[0], $steven);
            // steven updated mission
            $participants                       = MissionsUtil::
                    resolvePeopleToSendNotificationToOnNewComment($mission, $steven);
            $this->assertEquals(1, count($participants));
            $this->assertEquals($participants[0], $super);
        }

        public function testResolvePeopleToSendNotificationToOnNewMission()
        {
            $super                              = User::getByUsername('super');
            Yii::app()->user->userModel         = $super;
            $steven                             = User::getByUsername('steven');
            $mary                               = UserTestHelper::createBasicUser('mary');
            $missions                           = Mission::getAll();
            $mission                            = $missions[0];
            $people                             = MissionsUtil::resolvePeopleToSendNotificationToOnNewMission($mission);
            $this->assertNotContains($super,  $people);
            $this->assertContains   ($steven, $people);
            $this->assertContains   ($mary,   $people);
        }
    }
?>