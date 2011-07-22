<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class FileModelDisplayUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public static function tearDownAfterClass()
        {
            parent::tearDownAfterClass();
            $_FILES = null;
        }

        public function testConvertSizeToHumanReadableAndGet()
        {
            $readableSize = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(5000);
            $this->assertEquals('4.88Kb', $readableSize);
            $readableSize = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(500000);
            $this->assertEquals('488.28Kb', $readableSize);
            $readableSize = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(5000000);
            $this->assertEquals('4.77Mb', $readableSize);
            $readableSize = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(500000000);
            $this->assertEquals('476.84Mb', $readableSize);
            $readableSize = FileModelDisplayUtil::convertSizeToHumanReadableAndGet(5000000000);
            $this->assertEquals('4.66Gb', $readableSize);
        }

        public function testRenderDownloadLinkContentByRelationModelAndFileModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $model     = new ModelWithAttachmentTestItem();
            $fileModel = ZurmoTestHelper::createFileModel();
            $link      = FileModelDisplayUtil::renderDownloadLinkContentByRelationModelAndFileModel($model, $fileModel);
            $this->assertNotNull($link);
        }
    }
?>