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

    class ExportJobTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        /**
         * Test if background export job generated csv file,
         * check if content of this csv file is correct, and
         * finally check if user got notification message that
         * his downloads are completed.
         */
        public function testRun()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $numberOfUserNotifications = Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel);

            $account = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());

            $account = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account 2';
            $account->officePhone = '1234567899';
            $this->assertTrue($account->save());

            $account = new Account(false);
            $searchForm = new AccountsSearchForm($account);
            $dataProvider = ExportTestHelper::makeRedBeanDataProvider(
                $searchForm,
                'Account',
                0,
                Yii::app()->user->userModel->id
            );

            $totalItems = $dataProvider->getTotalItemCount();
            $this->assertEquals(2, $totalItems);

            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
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
            $this->assertEquals('test', $exportItem->exportFileName);
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
            $output = ExportItemToCsvFileUtil::export($data, $headerData, 'test.csv', false);
            $this->assertEquals($output, $fileModel->fileContent->content);

            // Check if user got notification message, and if its type is ExportProcessCompleted
            $this->assertEquals($numberOfUserNotifications + 1, Notification::getCountByTypeAndUser('ExportProcessCompleted', Yii::app()->user->userModel));
        }
    }
?>