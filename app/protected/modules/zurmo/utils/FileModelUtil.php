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
     * Model to handle file attachments. This model stores name, type, and size information. It has a related
     * FileContent model that has the actual file content.
     */
    class FileModelUtil
    {
        /**
         *
         * @param string $filePath
         * @return $fileModel or false on failure
         */
        public static function makeByFilePathAndName($filePath, $fileName)
        {
            assert('is_string($filePath) && $filePath !=""');
            assert('is_string($fileName) && $fileName !=""');
            $contents = file_get_contents($filePath);
            if ($contents === false)
            {
                return false;
            }
            $fileContent          = new FileContent();
            $fileContent->content = $contents;
            $file                 = new FileModel();
            $file->fileContent    = $fileContent;
            $file->name           = $fileName;
            $file->type           = ZurmoFileHelper::getMimeType($filePath);
            $file->size           = filesize($filePath);
            if (!$file->save())
            {
                return false;
            }
            return $file;
        }

        /**
         * Given an instance of a CUploadedFile, make a FileModel, save it, and return it.
         * If the file is empty, an exception is thrown otherwise the fileModel is returned.
         * @param object $uploadedFile CUploadedFile
         */
        public static function makeByUploadedFile($uploadedFile)
        {
            assert('$uploadedFile instanceof CUploadedFile');
            $fileContent          = new FileContent();
            $fileContent->content = file_get_contents($uploadedFile->getTempName());
            $file                 = new FileModel();
            $file->fileContent    = $fileContent;
            $file->name           = $uploadedFile->getName();
            $file->type           = $uploadedFile->getType();
            $file->size           = $uploadedFile->getSize();
            if (!$file->save())
            {
                throw new FailedFileUploadException(Yii::t('Default', 'File failed to upload. The file is empty.'));
            }
            return $file;
        }

        public static function resolveModelsHasManyFilesFromPost(& $model, $relationName, $postDataVariableName)
        {
            assert('$model instanceof RedBeanModel');
            assert('$model->isRelation($relationName)');
            $relationModelClassName     = $model->getRelationModelClassName($relationName);
            assert('$relationModelClassName == "FileModel" || is_subclass_of($relationModelClassName, "FileModel")');
            if (isset($_POST[$postDataVariableName]))
            {
                $newFileModelsIndexedById = array();
                foreach ($_POST[$postDataVariableName] as $notUsed => $fileModelId)
                {
                    $fileModel = FileModel::getById((int)$fileModelId);
                    $newFileModelsIndexedById[$fileModel->id] = $fileModel;
                }
                if ($model->{$relationName}->count() > 0)
                {
                    $fileModelsToRemove = array();
                    foreach ($model->{$relationName} as $index => $existingFileModel)
                    {
                        if (!isset($newFileModelsIndexedById[$existingFileModel->id]))
                        {
                            $fileModelsToRemove[] = $existingFileModel;
                        }
                        else
                        {
                            unset($newFileModelsIndexedById[$existingFileModel->id]);
                        }
                    }
                    foreach ($fileModelsToRemove as $fileModelToRemove)
                    {
                        $model->{$relationName}->remove($fileModelToRemove);
                    }
                }
                //Now add missing fileModels
                foreach ($newFileModelsIndexedById as $fileModel)
                {
                    $model->{$relationName}->add($fileModel);
                }
            }
        }
    }
?>