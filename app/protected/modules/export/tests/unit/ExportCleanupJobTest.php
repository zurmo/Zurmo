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

    class ExportCleanupJobTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testRun()
        {
            $quote = DatabaseCompatibilityUtil::getQuote();
            //Create 2 export items, and set one with a date over a week ago (8 days ago) for the modifiedDateTime
            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize(array('test', 'test2'));
            $this->assertTrue($exportItem->save());

            $fileContent          = new FileContent();
            $fileContent->content = 'test';

            $exportFileModel = new ExportFileModel();
            $exportFileModel->fileContent = $fileContent;
            $exportFileModel->name = $exportItem->exportFileName . ".csv";
            $exportFileModel->type    = 'application/octet-stream';
            $exportFileModel->size    = strlen($fileContent->content);

            $this->assertTrue($exportFileModel->save());
            $exportFileModel1Id = $exportFileModel->id;

            $exportItem->exportFileModel = $exportFileModel;
            $this->assertTrue($exportItem->save());

            $modifiedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - (60 * 60 *24 * 8));
            $sql = "Update item set modifieddatetime = '" . $modifiedDateTime . "' where id = " .
                $exportItem->getClassId('Item');
            R::exec($sql);

            // Second exportItem, that shouldn't be deleted.
            $exportItem2 = new ExportItem();
            $exportItem2->isCompleted = 0;
            $exportItem2->exportFileType = 'csv';
            $exportItem2->exportFileName = 'test';
            $exportItem2->modelClassName = 'Account';
            $exportItem2->serializedData = serialize(array('test', 'test2'));
            $this->assertTrue($exportItem2->save());

            $fileContent2          = new FileContent();
            $fileContent2->content = 'test';

            $exportFileModel2 = new ExportFileModel();
            $exportFileModel2->fileContent = $fileContent2;
            $exportFileModel2->name = $exportItem->exportFileName . ".csv";
            $exportFileModel2->type    = 'application/octet-stream';
            $exportFileModel2->size    = strlen($fileContent->content);

            $this->assertTrue($exportFileModel2->save());
            $exportFileModel2Id = $exportFileModel2->id;

            $exportItem2->exportFileModel = $exportFileModel2;
            $this->assertTrue($exportItem2->save());

            $job = new ExportCleanupJob();
            $this->assertTrue($job->run());

            $exportItems = ExportItem::getAll();
            $this->assertEquals(1, count($exportItems));
            $this->assertEquals($exportItem2->id, $exportItems[0]->id);
        }
    }
?>