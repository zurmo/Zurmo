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

    /**
     * Export module walkthrough tests.
     */
    class AccountsSuperUserExportWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $asynchronusThreshold;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);

            self::$asynchronusThreshold = ExportModule::$asynchronusThreshold;
            ExportModule::$asynchronusThreshold = 3;
        }

        public static function tearDownAfterClass()
        {
            ExportModule::$asynchronusThreshold = self::$asynchronusThreshold;
            parent::tearDownAfterClass();
        }

        /**
         * Walkthrough test for synchronous download
         */
        public function testDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts = array();
            for ($i = 0; $i < 2; $i++)
            {
                $accounts[] = AccountTestHelper::createAccountByNameForOwner('superAccount' . $i, $super);
            }

            // Provide no ids and without selectALl options.
            // This should be result with error and redirect to module page.
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/list');
            $this->setGetArray(array(
                'Account_page' => '1',
                'export' => '',
                'ajax' => '',
                'selectAll' => '',
                'selectedIds' => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/export');
            $this->assertTrue(strstr($response, 'accounts/default/index') !== false);

            $this->setGetArray(array(
                'AccountsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'name'                    => 'superAccount',
                    'officePhone'             => ''
                ),
                'multiselect_AccountsSearchForm_anyMixedAttributesScope' => 'All',
                'selectAll' => '1',
                'selectedIds' => '',
                'Account_page'   => '1',
                'export'         => '',
                'ajax'           => '')
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('accounts/default/export');
            $this->assertEquals('Testing download.', $response);

            $this->setGetArray(array(
                'AccountsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'name'                    => '',
                    'officePhone'             => ''
                ),
                'multiselect_AccountsSearchForm_anyMixedAttributesScope' => 'All',
                'selectAll' => '',
                'selectedIds' => "{$accounts[0]->id}, {$accounts[1]->id}",
                'Account_page'   => '1',
                'export'         => '',
                'ajax'           => '')
            );
            $response = $this->runControllerWithExitExceptionAndGetContent('accounts/default/export');
            $this->assertEquals('Testing download.', $response);

            // No matches
            $this->setGetArray(array(
                'AccountsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'name'                    => 'missingName',
                    'officePhone'             => ''
                ),
                'multiselect_AccountsSearchForm_anyMixedAttributesScope' => 'All',
                'Account_page' => '1',
                'selectAll' => '1',
                'selectedIds' => '',
                'export'       => '',
                'ajax'         => '')
            );
            $response = $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/export');
            $this->assertTrue(strstr($response, 'accounts/default/index') !== false);
        }

        /**
        * Walkthrough test for asynchronous download
        */
        public function testAsynchronousDownloadDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $notificationsBeforeCount        = count(Notification::getAll());
            $notificationMessagesBeforeCount = count(NotificationMessage::getAll());
            $accounts = Account::getAll();
            if (count($accounts))
            {
                foreach ($accounts as $account)
                {
                    $account->delete();
                }
            }
            $accounts = array();
            for ($i = 0; $i <= (ExportModule::$asynchronusThreshold + 1); $i++)
            {
                $accounts[] = AccountTestHelper::createAccountByNameForOwner('superAccount' . $i, $super);
            }

            $this->setGetArray(array(
                'Account_page' => '1',
                'export' => '',
                'selectAll' => '1',
                'selectedIds' => '',
                'ajax' => ''));

            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/export');

            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('accounts', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals($notificationsBeforeCount + 1, count(Notification::getAll()));
            $this->assertEquals($notificationMessagesBeforeCount + 1, count(NotificationMessage::getAll()));

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
            foreach ($accounts as $account)
            {
                $selectedIds .= $account->id . ","; // Not Coding Standard
            }
            $this->setGetArray(array(
                'AccountsSearchForm' => array(
                    'anyMixedAttributesScope' => array(0 => 'All'),
                    'anyMixedAttributes'      => '',
                    'name'                    => '',
                    'officePhone'             => ''
                ),
                'multiselect_AccountsSearchForm_anyMixedAttributesScope' => 'All',
                'selectAll' => '',
                'selectedIds' => "$selectedIds",
                'Account_page'   => '1',
                'export'         => '',
                'ajax'           => '')
            );

            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/export');
            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('accounts', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $this->assertEquals($notificationsBeforeCount + 1, count(Notification::getAll()));
            $this->assertEquals($notificationMessagesBeforeCount + 1, count(NotificationMessage::getAll()));
        }

        public function testExportWithSelectAllForMoreThan10Records()
        {
            $super    = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accounts = Account::getAll();
            foreach ($accounts as $account)
            {
                $account->delete();
            }
            $exportItems = ExportItem::getAll();
            foreach ($exportItems as $exportItem)
            {
                $exportItem->delete();
            }
            $numberOfRecords    = rand (12, 100);
            ExportModule::$asynchronusThreshold = $numberOfRecords - 1;
            for ($i = 1; $i <= $numberOfRecords; $i++)
            {
                $randomData = RandomDataUtil::getRandomDataByModuleAndModelClassNames('AccountsModule', 'Account');
                AccountTestHelper::createAccountByNameForOwner($randomData['names'][$i], $super);
            }
            $this->setGetArray(array(
                'Account_page' => '1',
                'export' => '',
                'selectAll' => '1',
                'selectedIds' => '',
                'ajax' => ''));

            $this->runControllerWithRedirectExceptionAndGetUrl('accounts/default/export');

            // Start background job
            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $fileModel = $exportItems[0]->exportFileModel;
            $this->assertEquals(1, $exportItems[0]->isCompleted);
            $this->assertEquals('csv', $exportItems[0]->exportFileType);
            $this->assertEquals('accounts', $exportItems[0]->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);
            for ($i = 1; $i <= $numberOfRecords; $i++)
            {
                $this->assertContains($randomData['names'][$i], $fileModel->fileContent->content);
            }
        }
    }
?>