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

    /**
     * Class that adds common objects to all marketing related models.
     */
    abstract class MarketingDemoDataMaker extends DemoDataMaker
    {
        protected $files = array('testDocument.docx',
                                    'testImage.png',
                                    'testLogo.png',
                                    'testNote.txt',
                                    'testPDF.pdf',
                                    'testZip.zip');

        public function populateMarketingModelWithFiles($marketingModel, $count = null)
        {
            $pathToFiles            = Yii::getPathOfAlias('application.modules.marketing.data.files');
            $numberOfFilesToAttach  = $count;
            if (!isset($numberOfFilesToAttach))
            {
                $numberOfFilesToAttach  = rand(1, 5);
            }
            $this->populateWithFiles($marketingModel, $numberOfFilesToAttach, $pathToFiles);
        }

        protected function populateWithFiles($model, $numberOfFilesToAttach, $pathToFiles)
        {
            assert('$model instanceof EmailTemplate  || $model instanceof Autoresponder || $model instanceof Campaign');
            for ($i = 0; $i < $numberOfFilesToAttach; $i++)
            {
                $fileName               = $this->files[array_rand($this->files)];
                $filePath               = $pathToFiles . DIRECTORY_SEPARATOR . $fileName;
                $contents               = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . $fileName);
                $fileContent            = new FileContent();
                $fileContent->content   = $contents;
                $file                   = new FileModel();
                $file->fileContent      = $fileContent;
                $file->name             = $fileName;
                $file->type             = ZurmoFileHelper::getMimeType($pathToFiles . DIRECTORY_SEPARATOR . $fileName);
                $file->size             = filesize($filePath);
                $saved                  = $file->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                $model->files->add($file);
            }
        }
    }
?>