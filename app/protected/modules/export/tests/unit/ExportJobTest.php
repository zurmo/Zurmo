<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class ExportJobTest extends ZurmoBaseTest
    {
        protected static $asynchronousPageSize;

        protected static $asynchronousMaximumModelsToProcess;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            static::$asynchronousPageSize = ExportModule::$asynchronousPageSize;
            static::$asynchronousMaximumModelsToProcess = ExportModule::$asynchronousMaximumModelsToProcess;
        }

        public function tearDown()
        {
            ExportModule::$asynchronousPageSize = static::$asynchronousPageSize;
            ExportModule::$asynchronousMaximumModelsToProcess = static::$asynchronousMaximumModelsToProcess;
        }

        public function testExportByModelIds()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $idsToExport = array();

            $account1 = new Account();
            $account1->owner       = $super;
            $account1->name        = 'Test Account';
            $account1->officePhone = '1234567890';
            $this->assertTrue($account1->save());

            $account2 = new Account();
            $account2->owner       = $super;
            $account2->name        = 'Test Account 2';
            $account2->officePhone = '1234567899';
            $this->assertTrue($account2->save());
            $idsToExport[]  = $account2->id;

            $account3 = new Account();
            $account3->owner       = $super;
            $account3->name        = 'Test Account 3';
            $account3->officePhone = '987654321';
            $this->assertTrue($account3->save());
            $idsToExport[] = $account3->id;

            $account4 = new Account();
            $account4->owner       = $super;
            $account4->name        = 'Test Account 4';
            $account4->officePhone = '198765432';
            $this->assertTrue($account4->save());

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($idsToExport);
            $this->assertTrue($exportItem->save());

            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel  = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            // Get csv string via regular csv export process(directly, not in background)
            // We suppose that csv generated thisway is corrected, this function itself
            // is tested in another test.
            $data                  = array();
            $modelToExportAdapter  = new ModelToExportAdapter($account2);
            $headerData            = $modelToExportAdapter->getHeaderData();
            $data[]                = $modelToExportAdapter->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($account3);
            $data[]                = $modelToExportAdapter->getData();
            $output                = ExportItemToCsvFileUtil::export($data, $headerData, 'test.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportRedBeanDataProviderWithSinglePageOfData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $account      = new Account(false);
            $searchForm   = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                Yii::app()->user->userModel->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(4, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test2';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $this->assertTrue($exportItem->save());

            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test2', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            // Get csv string via regular csv export process(directly, not in background)
            // We suppose that csv generated thisway is corrected, this function itself
            // is tested in another test.
            $data                  = array();
            $rows                  = $dataProvider->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $headerData            = $modelToExportAdapter->getHeaderData();
            foreach ($rows as $model)
            {
                $modelToExportAdapter  = new ModelToExportAdapter($model);
                $data[] = $modelToExportAdapter->getData();
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test2.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportRedBeanDataProviderWithMultiplePagesOfData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $account      = new Account(false);
            $searchForm   = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                Yii::app()->user->userModel->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(4, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test3';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $this->assertTrue($exportItem->save());

            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            ExportModule::$asynchronousPageSize = 2;

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test3', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            // Get csv string via regular csv export process(directly, not in background)
            // We suppose that csv generated thisway is corrected, this function itself
            // is tested in another test.
            $data                  = array();
            $rows                  = $dataProvider->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $headerData            = $modelToExportAdapter->getHeaderData();
            foreach ($rows as $model)
            {
                $modelToExportAdapter  = new ModelToExportAdapter($model);
                $data[] = $modelToExportAdapter->getData();
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test3.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportRedBeanDataProviderGoesOverMaximumProcessingCountLimit()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $account      = new Account(false);
            $searchForm   = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                Yii::app()->user->userModel->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(4, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test4';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $this->assertTrue($exportItem->save());

            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            ExportModule::$asynchronousPageSize = 2;
            ExportModule::$asynchronousMaximumModelsToProcess = 3;

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(0, $exportItem->isCompleted);
            $this->assertEquals(2, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test4', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            // Get csv string via regular csv export process(directly, not in background)
            // We suppose that csv generated thisway is corrected, this function itself
            // is tested in another test.
            $data                  = array();
            $rows                  = $dataProvider->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $headerData            = $modelToExportAdapter->getHeaderData();

            //Only 2 rows were processed in the first run
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $data[] = $modelToExportAdapter->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[1]);
            $data[] = $modelToExportAdapter->getData();

            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test4.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check that user got no notification message
            $this->assertEquals($numberOfUserNotifications,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));

            //Second run will finish the job
            $this->assertTrue($job->run());
            //Third run is need to mark the exportItem as complete
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(4, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test4', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            //The last 2 rows were processed
            $modelToExportAdapter  = new ModelToExportAdapter($rows[2]);
            $data[] = $modelToExportAdapter->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[3]);
            $data[] = $modelToExportAdapter->getData();

            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test4.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

             // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportRedBeanDataProviderGoesOverMaximumProcessingCountLimitGlobally()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $account      = new Account(false);
            $searchForm   = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                Yii::app()->user->userModel->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(4, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test5';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $this->assertTrue($exportItem->save());
            $id1 = $exportItem->id;

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test6';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $this->assertTrue($exportItem->save());
            $id2 = $exportItem->id;

            $exportItem->forget();
            unset($exportItem);

            ExportModule::$asynchronousMaximumModelsToProcess = 6;

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id1);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(0, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test5', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $data                  = array();
            $rows                  = $dataProvider->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $headerData            = $modelToExportAdapter->getHeaderData();
            foreach ($rows as $model)
            {
                $modelToExportAdapter  = new ModelToExportAdapter($model);
                $data[] = $modelToExportAdapter->getData();
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test5.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));

            //The second item was not processed
            $exportItem = ExportItem::getById($id2);
            $fileModel  = $exportItem->exportFileModel;
            $this->assertNull($fileModel->fileContent->content);

            $this->assertTrue($job->run());

            //The second item is processed
            $exportItem = ExportItem::getById($id2);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(0, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test6', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test6.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 2,
                                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        public function testSecurityExceptionThrownDuringExport()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            SecurityTestHelper::createAccounts();
            $billy = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            ReadPermissionsOptimizationUtil::rebuild();

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', $billy);

            $account      = new Account(false);
            $searchForm   = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                $billy->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(3, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test7';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($dataProvider);
            $exportItem->owner          = $billy;
            $this->assertTrue($exportItem->save());

            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            $accounts = Account::getByName('Microsoft');
            $account  = $accounts[0];
            $account->owner = $super;
            $this->assertTrue($account->save());

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('test7', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $data                  = array();
            $rows                  = $dataProvider->getData();
            $modelToExportAdapter  = new ModelToExportAdapter($rows[0]);
            $headerData            = $modelToExportAdapter->getHeaderData();
            foreach ($rows as $model)
            {
                //billy lost access to Microsoft account
                if ($model->id != $account->id)
                {
                    $modelToExportAdapter  = new ModelToExportAdapter($model);
                    $data[] = $modelToExportAdapter->getData();
                }
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test7.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportReportWithSinglePageOfData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                        Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');
            $displayAttribute->attributeIndexOrDerivedType = 'name';
            $report->addDisplayAttribute($displayAttribute);

            $dataProvider                = new RowsAndColumnsReportDataProvider($report);
            $exportItem                  = new ExportItem();
            $exportItem->isCompleted     = 0;
            $exportItem->exportFileType  = 'csv';
            $exportItem->exportFileName  = 'rowAndColumnsTest1';
            $exportItem->modelClassName  = 'SavedReport';
            $exportItem->serializedData  = ExportUtil::getSerializedDataForExport($dataProvider);
            $this->assertTrue($exportItem->save());
            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(0, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('rowAndColumnsTest1', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $accounts              = Account::getAll();
            $headerData            = array('Name');
            $data                  = array();
            foreach ($accounts as $account)
            {
                $data[]                = array($account->name);
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'rowAndColumnsTest1.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }

        /**
         * @depends testExportByModelIds
         */
        public function testExportReportWithMultiplePagesOfData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('AccountsModule');
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                        Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');
            $displayAttribute->attributeIndexOrDerivedType = 'name';
            $report->addDisplayAttribute($displayAttribute);

            $dataProvider                = new RowsAndColumnsReportDataProvider($report);
            $exportItem                  = new ExportItem();
            $exportItem->isCompleted     = 0;
            $exportItem->exportFileType  = 'csv';
            $exportItem->exportFileName  = 'rowAndColumnsTest2';
            $exportItem->modelClassName  = 'SavedReport';
            $exportItem->serializedData  = ExportUtil::getSerializedDataForExport($dataProvider);
            $this->assertTrue($exportItem->save());
            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            ExportModule::$asynchronousPageSize = 2;

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(0, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('rowAndColumnsTest2', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);

            $accounts              = Account::getAll();
            $headerData            = array('Name');
            $data                  = array();
            foreach ($accounts as $account)
            {
                $data[]                = array($account->name);
            }
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'rowAndColumnsTest2.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1,
                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));

            //Matrix report should not paginate
            $report = new Report();
            $report->setType(Report::TYPE_MATRIX);
            $report->setModuleClassName('AccountsModule');
            $report->setFiltersStructure('');

            $displayAttribute = new DisplayAttributeForReportForm('AccountsModule', 'Account',
                                        Report::TYPE_MATRIX);
            $displayAttribute->setModelAliasUsingTableAliasName('model1');
            $displayAttribute->attributeIndexOrDerivedType = 'Count';
            $report->addDisplayAttribute($displayAttribute);

            $groupBy           = new GroupByForReportForm('AccountsModule', 'Account',
                                        Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'name';
            $groupBy->axis = 'y';
            $report->addGroupBy($groupBy);

            $groupBy           = new GroupByForReportForm('AccountsModule', 'Account',
                                        Report::TYPE_MATRIX);
            $groupBy->attributeIndexOrDerivedType = 'officePhone';
            $report->addGroupBy($groupBy);

            $dataProvider                = new MatrixReportDataProvider($report);
            $exportItem                  = new ExportItem();
            $exportItem->isCompleted     = 0;
            $exportItem->exportFileType  = 'csv';
            $exportItem->exportFileName  = 'matrixTest1';
            $exportItem->modelClassName  = 'SavedReport';
            $exportItem->serializedData  = ExportUtil::getSerializedDataForExport($dataProvider);
            $this->assertTrue($exportItem->save());
            $id = $exportItem->id;
            $exportItem->forget();
            unset($exportItem);

            ExportModule::$asynchronousPageSize = 2;

            $job = new ExportJob();
            $this->assertTrue($job->run());

            $exportItem = ExportItem::getById($id);
            $fileModel = $exportItem->exportFileModel;

            $this->assertEquals(1, $exportItem->isCompleted);
            $this->assertEquals(0, $exportItem->processOffset);
            $this->assertEquals('csv', $exportItem->exportFileType);
            $this->assertEquals('matrixTest1', $exportItem->exportFileName);
            $this->assertTrue($fileModel instanceOf ExportFileModel);
            $fileContent = $fileModel->fileContent->content;
            $this->assertContains('Test Account',   $fileContent);
            $this->assertContains('Test Account 2', $fileContent);
            $this->assertContains('Test Account 3', $fileContent);
            $this->assertContains('Test Account 4', $fileContent);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 2,
                Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }
    }
?>