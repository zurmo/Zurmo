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

    class MissionsMashableInboxRulesTest extends ZurmoWalkthroughBaseTest
    {
        private $rules;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            ReadPermissionsOptimizationUtil::rebuild();
            $steven = UserTestHelper::createBasicUser('steven');
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_ACCESS_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_CREATE_MISSIONS);
            $steven->setRight('MissionsModule', MissionsModule::RIGHT_DELETE_MISSIONS);
            $steven->save();
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $everyoneGroup->save();
        }

        public function setUp()
        {
            parent::setUp();
            $this->rules               = new MissionMashableInboxRules();
        }

        public function testListActionRenderListViewsForMission()
        {
            $this->setGetArray(array('modelClassName' => 'Mission'));
            $super   = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->deleteAllMissions();
            $this->createAndSaveNewMissionForUser($super);
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            $this->assertContains($this->rules->getListViewClassName(),   $content);
            $this->assertContains('list-view-markRead',                   $content);
            $this->assertContains('list-view-markUnread',                 $content);
        }

        public function testReadUnread()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllMissions();
            $createdMission             = $this->createAndSaveNewMissionForUser($super);
            $this->assertTrue($this->rules->hasCurrentUserReadLatest($createdMission->id));
            $this->rules->resolveMarkUnread($createdMission->id);
            $savedMission               = Mission::getById($createdMission->id);
            $this->assertFalse($this->rules->hasCurrentUserReadLatest($createdMission->id));
            $this->rules->resolveMarkRead($createdMission->id);
            $savedMission               = Mission::getById($createdMission->id);
            $this->assertTrue($this->rules->hasCurrentUserReadLatest($createdMission->id));
        }

        protected function deleteAllMissions()
        {
            foreach (Mission::getAll() as $mission)
            {
                $mission->delete();
            }
        }

        protected function createAndSaveNewMissionForUser(User $owner, $status = Mission::STATUS_AVAILABLE)
        {
            $mission                           = new Mission();
            $mission->owner                    = $owner;
            $mission->description              = 'My test mission description with status: ' . $status;
            $mission->status                   = $status;
            $this->assertTrue($mission->save());
            return $mission;
        }

        protected function resolveControlerActionListAndGetContent($filteredBy, $optionForModel)
        {
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $this->setGetArray(
                        array(
                            'modelClassName'    => 'Mission',
                            'ajax'              => 'list-view',
                            'MashableInboxForm' => array(
                                    'filteredBy'     => $filteredBy,
                                    'optionForModel' => $optionForModel
                                )
                        )
                    );
            $content = $this->runControllerWithNoExceptionsAndGetContent('mashableInbox/default/list');
            return $content;
        }

        public function testFilters()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->deleteAllMissions();
            $mission                    = $this->createAndSaveNewMissionForUser($super);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    MissionsListConfigurationForm::LIST_TYPE_CREATED);
            $this->assertContains($mission->description,        $content);
            $this->assertContains('1 result(s)',                $content);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    MissionsListConfigurationForm::LIST_TYPE_CREATED);
            $this->assertNotContains($mission->description,     $content);
            $this->assertNotContains('result(s)',               $content);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    MissionsListConfigurationForm::LIST_TYPE_AVAILABLE);
            $this->assertNotContains($mission->description,     $content);
            $this->assertNotContains('result(s)',               $content);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    MissionsListConfigurationForm::LIST_TYPE_CREATED);
            $this->assertNotContains($mission->description,     $content);
            $this->assertNotContains('result(s)',               $content);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_ALL,
                                                    MissionsListConfigurationForm::LIST_TYPE_MINE_TAKEN_BUT_NOT_ACCEPTED);
            $this->assertNotContains($mission->description,     $content);
            $this->assertNotContains('result(s)',               $content);
            $content                    = $this->resolveControlerActionListAndGetContent(
                                                    MashableInboxForm::FILTERED_BY_UNREAD,
                                                    MissionsListConfigurationForm::LIST_TYPE_CREATED);
            $this->assertNotContains($mission->description,     $content);
            $this->assertNotContains('result(s)',               $content);
        }

        public function testSearch()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllMissions();
            $this->createAndSaveNewMissionForUser($super);
            $searchAttributeData        = $this->rules->getSearchAttributeData();
            $dataProvider               = new RedBeanModelDataProvider('Mission', null, false, $searchAttributeData);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("mis");
            $dataProvider               = new RedBeanModelDataProvider('Mission', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(1, count($data));
            $metadataForSearch          = $this->rules->getSearchAttributeData("subject");
            $dataProvider               = new RedBeanModelDataProvider('Mission', null, false, $metadataForSearch);
            $data                       = $dataProvider->getData();
            $this->assertEquals(0, count($data));
        }
    }
?>