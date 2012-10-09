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

    /**
     * Missions Module User Walkthrough.
     * Walkthrough for the users of all possible controller actions.
     */
    class MissionsUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ReadPermissionsOptimizationUtil::rebuild();

            //create everyone group
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();

            //Create test users
            $steven                             = UserTestHelper::createBasicUser('steven');
            $steven->primaryEmail->emailAddress = 'steven@testzurmo.com';
            $sally                              = UserTestHelper::createBasicUser('sally');
            $sally->primaryEmail->emailAddress  = 'sally@testzurmo.com';
            $mary                               = UserTestHelper::createBasicUser('mary');
            $mary->primaryEmail->emailAddress  = 'mary@testzurmo.com';

            //give 3 users access, create, delete for mission rights.
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $steven->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $sally->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $sally->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $mary->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $saved = $mary->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
        }

        public function testSuperUserAllSimpleControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('missions/default');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/create');
        }

        /**
         * @depends testSuperUserAllSimpleControllerActions
         */
        public function testSuperUserCreateMission()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');

            $missions = Mission::getAll();
            $this->assertEquals(0, count($missions));
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');

            //Confirm mission saved.
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals('TestDescription', $missions[0]->description);
            $this->assertEquals(Mission::STATUS_AVAILABLE,        $missions[0]->status);

            //Confirm everyone has read/write
            $everyoneGroup                     = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($missions[0]);
            $readWritePermitables              = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertTrue(isset($readWritePermitables[$everyoneGroup->id]));
        }

        /**
         * @depends testSuperUserCreateMission
         */
        public function testAddingCommentsAndUpdatingActivityStampsOnMission()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $steven         = User::getByUsername('steven');
            $sally          = User::getByUsername('sally');
            $mary           = User::getByUsername('mary');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(0, $missions[0]->comments->count());
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $oldStamp        = $missions[0]->latestDateTime;

            //Validate comment
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('ajax' => 'comment-inline-edit-form',
                                      'Comment' => array('description' => 'a ValidComment Name')));

            $content = $this->runControllerWithExitExceptionAndGetContent('comments/default/inlineCreateSave');
            $this->assertEquals('[]', $content);

            //Now save that comment.
            sleep(2); //to force some time to pass.
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($id);
            $this->assertEquals(1, $mission->comments->count());

            //should update latest activity stamp
            $this->assertNotEquals($oldStamp, $missions[0]->latestDateTime);
            $newStamp = $missions[0]->latestDateTime;
            sleep(2); // Sleeps are bad in tests, but I need some time to pass

            //Mary should be able to add a comment because everyone can do this on a mission
            $mary = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'redirectUrl'                => 'someRedirect'));
            $this->setPostArray(array('Comment'          => array('description' => 'a ValidComment Name 2')));
            $content = $this->runControllerWithRedirectExceptionAndGetContent('comments/default/inlineCreateSave');
            $id = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($id);
            $this->assertEquals(2, $mission->comments->count());
            $this->assertNotEquals($newStamp, $mission->latestDateTime);
        }

        /**
         * @depends testAddingCommentsAndUpdatingActivityStampsOnMission
         */
        public function testUsersCanReadAndWriteMissionsOkThatAreNotOwnerOrTakenByUser()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            //todo; we stll need to test that other users can get to the missions.
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $mary           = User::getByUsername('mary');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(2, $missions[0]->comments->count());

            //Mary should not be able to edit the mission
            $mary           = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('id' => $missions[0]->id));
            $this->runControllerWithExitExceptionAndGetContent('missions/default/edit');

            //new test - mary can delete a comment she wrote
            $maryCommentId = $missions[0]->comments->offsetGet(1)->id;
            $this->assertEquals($missions[0]->comments->offsetGet(1)->createdByUser->id, $mary->id);
            $superCommentId = $missions[0]->comments->offsetGet(0)->id;
            $this->assertEquals($missions[0]->comments->offsetGet(0)->createdByUser->id, $super->id);
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $maryCommentId));
            $this->runControllerWithNoExceptionsAndGetContent('comments/default/deleteViaAjax', true);
            $missionId  = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($missionId);
            $this->assertEquals(1, $mission->comments->count());

            //new test - mary cannot delete a comment she did not write.
            $this->setGetArray(array('relatedModelId'             => $missions[0]->id,
                                     'relatedModelClassName'      => 'Mission',
                                     'relatedModelRelationName'   => 'comments',
                                     'id'                         => $superCommentId));
            $this->runControllerShouldResultInAjaxAccessFailureAndGetContent('comments/default/deleteViaAjax');
            $missionId  = $missions[0]->id;
            $missions[0]->forget();
            $mission = Mission::getById($missionId);
            $this->assertEquals(1, $mission->comments->count());
            $this->assertEquals(1, $mission->comments->count());

            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->assertTrue($mission->owner->isSame($super));

            //new test , super can view and edit the mission
            $this->setGetArray(array('id' => $mission->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/edit');

            //new test , super can delete the mission
            $this->setGetArray(array('id' => $mission->id));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/delete');

            $missions  = Mission::getAll();
            $this->assertEquals(0, count($missions));
        }

        /**
         * @depends testUsersCanReadAndWriteMissionsOkThatAreNotOwnerOrTakenByUser
         */
        public function testListViewFiltering()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_CREATED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_AVAILABLE));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
            $this->setGetArray(array(
                'type' => MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED));
            $content = $this->runControllerWithNoExceptionsAndGetContent('missions/default/list');
            $this->assertFalse(strpos($content, 'Missions') === false);
        }

        /**
         * @depends testListViewFiltering
         */
        public function testCommentsAjaxListForRelatedModel()
        {
            if (!SECURITY_OPTIMIZED) //bug prevents this from running correctly
            {
                return;
            }
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $missions  = Mission::getAll();
            $this->assertEquals(0, count($missions));

            //Create a new mission
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');
            $missions  = Mission::getAll();
            $this->assertEquals(1, count($missions));

            $this->setGetArray(array('relatedModelId' => $missions[0]->id, 'relatedModelClassName' => 'Mission',
                                     'relatedModelRelationName' => 'comments'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content = $this->runControllerWithNoExceptionsAndGetContent('comments/default/ajaxListForRelatedModel');
        }

        /**
         * @depends testCommentsAjaxListForRelatedModel
         */
        public function testAjaxChangeStatus()
        {
            $super          = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $missions[0]->delete();

            //Create a new mission
            $this->setPostArray(array('Mission'                 => array('description' => 'TestDescription',
                                                                         'reward'      => 'Reward')));
            $this->runControllerWithRedirectExceptionAndGetContent('missions/default/create');

            //Confirm mission saved.
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_AVAILABLE,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->id < 0);

            //change status to taken
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_TAKEN,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_TAKEN,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));

            //Change status to complete
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_COMPLETED,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_COMPLETED,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));

            //Change status to accepted
            $mary         = $this->logoutCurrentUserLoginNewUserAndGetByUsername('mary');
            $this->setGetArray(array('status'         => Mission::STATUS_ACCEPTED,
                                     'id'             => $missions[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('missions/default/ajaxChangeStatus');
            $missions[0]->forget();
            $missions = Mission::getAll();
            $this->assertEquals(1, count($missions));
            $this->assertEquals(Mission::STATUS_ACCEPTED,        $missions[0]->status);
            $this->assertTrue($missions[0]->takenByUser->isSame($mary));
        }
    }
?>