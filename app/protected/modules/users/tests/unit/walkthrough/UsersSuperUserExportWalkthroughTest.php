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
     * Export module walkthrough tests.
     */
    class UsersSuperUserExportWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $asynchronusTreshold;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            self::$asynchronusTreshold = ExportModule::$asynchronusTreshold;
            ExportModule::$asynchronusTreshold = 5;
        }

        public static function tearDownAfterClass()
        {
            ExportModule::$asynchronusTreshold = self::$asynchronusTreshold;
            parent::tearDownAfterClass();
        }

        /**
         * Walkthrough test for synchronous download
         */
        public function testDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $users = User::getAll();
            if (count($users))
            {
                foreach ($users as $user)
                {
                    if ($user->id !== $super->id)
                    {
                        $user->delete();
                    }
                }
            }
            $users = array();
            for ($i = 0; $i <= (ExportModule::$asynchronusTreshold - 3); $i++)
            {
                $users[] = UserTestHelper::createBasicUser('aUser' . $i);
            }

            $this->runControllerWithNoExceptionsAndGetContent('users/default/list');
            $this->setGetArray(array(
                'User_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('users/default/export');
            $this->assertTrue(strstr($response, 'users/default/index') !== false);

            $this->setGetArray(array(
                'UsersSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                ),
                'User_page'      => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('users/default/export');
            $this->assertEquals('Testing download.', $response);

            $this->setGetArray(array(
                'UsersSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                ),
                'User_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "{$users[0]->id}, {$users[1]->id}")
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('users/default/export');
            $this->assertEquals('Testing download.', $response);

            // No mathces
            $this->setGetArray(array(
                'UsersSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => 'missingOne',
                ),
                'User_page' => '1',
                'export'       => '',
                'ajax'         => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('users/default/export');
            $this->assertTrue(strstr($response, 'users/default/index') !== false);
        }

        /**
        * Walkthrough test for synchronous download
        */
        public function testAsynchronousDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $users = User::getAll();
            if (count($users))
            {
                foreach ($users as $user)
                {
                    if ($user->id !== $super->id)
                    {
                        $user->delete();
                    }
                }
            }
            $users = array();
            for ($i = 0; $i <= (ExportModule::$asynchronusTreshold + 1); $i++)
            {
                $users[] = UserTestHelper::createBasicUser('aUser' . $i);
            }

            $this->setGetArray(array(
                'User_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '1',
                'selectedIds' => '')
            );
            $this->runControllerWithRedirectExceptionAndGetUrl('users/default/export');

            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('users', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals(1, count(Notification::getAll()));
            $this->assertEquals(1, count(NotificationMessage::getAll()));

            // Check export job, when many ids are selected.
            // This will probably never happen, but we need test for this case too.
            $notificationsBeforeCount        = count(Notification::getAll());
            $notificationMessagesBeforeCount = count(NotificationMessage::getAll());

            // Now test case when multiple ids are selected
            $exportItems = ExportItem::getAll();
            if (count($exportItems))
            {
                foreach ($exportItems as $exportItem)
                {
                    $exportItem->delete();
                }
            }

            $selectedIds = "";
            foreach ($users as $user)
            {
                $selectedIds .= $user->id . ","; // Not Coding Standard
            }
            $this->setGetArray(array(
                'UsersSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                ),
                'User_page'   => '1',
                'export'         => '',
                'ajax'           => '',
                'selectAll' => '',
                'selectedIds' => "$selectedIds")
            );

            $this->runControllerWithRedirectExceptionAndGetUrl('users/default/export');
            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('users', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals($notificationsBeforeCount + 1, count(Notification::getAll()));
            $this->assertEquals($notificationMessagesBeforeCount + 1, count(NotificationMessage::getAll()));
        }
    }
?>