<?php
    /**
     * Helper class for CUploadedFile.
     */
    class UploadedFileUtil
    {
        /**
         * Get an instance of CuploadedFile. Catches errors and throws a BadFileUploadException
         * @var string $filesVariableName
         */
        public static function getByNameAndCatchError($filesVariableName)
        {
            $uploadedFile  = CUploadedFile::getInstanceByName($filesVariableName);
            if ($uploadedFile->getHasError())
            {
                $error = $file->getError();
                $messageParams = array('{file}'=> $uploadedFile->getName(), '{limit}'=> self::getSizeLimit());
                if ($error == UPLOAD_ERR_NO_FILE)
                {
                    $message = Yii::t('Default', 'The file did not exist');
                }
                elseif ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE)
                {
                    $message = Yii::t('yii','The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_PARTIAL)
                {
                    $message = Yii::t('yii','The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error==UPLOAD_ERR_NO_TMP_DIR)
                {
                    $message = Yii::t('yii', 'Missing the temporary folder to store the uploaded file "{file}".',
                                                 $messageParams);
                }
                elseif ($error==UPLOAD_ERR_CANT_WRITE)
                {
                    $message = Yii::t('yii','Failed to write the uploaded file "{file}" to disk.',
                                                 $messageParams);
                }
                elseif (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION)
                {
                    $message = Yii::t('yii','File upload was stopped by extension.');
                }
                else
                {
                    //Unsupported or unknown error.
                    $message = Yii::t('Default','There was an error uploading the file.');
                }
                throw new FailedFileUploadException();
            }
            elseif ($uploadedFile == null)
            {
                throw new FailedFileUploadException(Yii::t('Default', 'The file did not exist'));
            }
            else
            {
                return $uploadedFile;
            }
        }

        /**
         * Returns the maximum size allowed for uploaded files.
         * This is determined based on three factors:
         * <ul>
         * <li>'upload_max_filesize' in php.ini</li>
         * <li>'MAX_FILE_SIZE' hidden field</li>
         * <li>{@link maxSize}</li>
         * </ul>
         *
         * @return integer the size limit for uploaded files.
         */
        private function getSizeLimit()
        {
            $limit = ini_get('upload_max_filesize');
            $limit = self::sizeToBytes($limit);
            if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit)
            {
                $limit = $_POST['MAX_FILE_SIZE'];
            }
            return $limit;
        }

        /**
         * Converts php.ini style size to bytes
         *
         * @param string $sizeStr $sizeStr
         * @return int
         */
        private static function sizeToBytes($sizeStr)
        {
            switch (substr($sizeStr, -1))
            {
                case 'M': case 'm': return (int)$sizeStr * 1048576;
                case 'K': case 'k': return (int)$sizeStr * 1024;
                case 'G': case 'g': return (int)$sizeStr * 1073741824;
                default: return (int)$sizeStr;
            }
        }
    }
?>