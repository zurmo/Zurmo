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

    class UploadedFileUtilTest extends BaseTest
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

        public function testGetByNameAndCatchError()
        {
            $this->loadTestFileIntoFiles();
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
            $this->assertTrue($uploadedFile instanceof CUploadedFile);
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorFileNotExist()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_NO_FILE;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorFileTooBigIniSize()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_INI_SIZE;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorFileTooBigFormSize()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_FORM_SIZE;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorFileTooBigPartial()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_PARTIAL;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorTempFolderNotExist()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_NO_TMP_DIR;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorFileNotWriteable()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_CANT_WRITE;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testGetByNameAndCatchErrorErrorExtension()
        {
            $this->loadTestFileIntoFiles();
            $_FILES['test']['error'] = UPLOAD_ERR_EXTENSION;
            $uploadedFile = UploadedFileUtil::getByNameAndCatchError('test');
        }

        protected function loadTestFileIntoFiles()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            self::resetAndPopulateFilesArrayByFilePathAndName('test', $filePath, 'testNote.txt');
        }
    }
?>
