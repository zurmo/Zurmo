<?php
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
                return null;
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