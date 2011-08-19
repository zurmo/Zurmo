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
            assert('is_string($filesVariableName)');
            $uploadedFile  = CUploadedFile::getInstanceByName($filesVariableName);
            if ($uploadedFile == null)
            {
                throw new FailedFileUploadException(Yii::t('Default', 'The file did not exist'));
            }
            elseif($uploadedFile->getHasError())
            {
                $error = $file->getError();
                $messageParams = array('{file}' => $uploadedFile->getName(), '{limit}' => self::getSizeLimit());
                if ($error == UPLOAD_ERR_NO_FILE)
                {
                    $message = Yii::t('Default', 'The file did not exist');
                }
                elseif ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE)
                {
                    $message = Yii::t('yii', 'The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_PARTIAL)
                {
                    $message = Yii::t('yii', 'The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_NO_TMP_DIR)
                {
                    $message = Yii::t('yii', 'Missing the temporary folder to store the uploaded file "{file}".',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_CANT_WRITE)
                {
                    $message = Yii::t('yii', 'Failed to write the uploaded file "{file}" to disk.',
                                                 $messageParams);
                }
                elseif (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION)
                {
                    $message = Yii::t('yii', 'File upload was stopped by extension.');
                }
                else
                {
                    //Unsupported or unknown error.
                    $message = Yii::t('Default', 'There was an error uploading the file.');
                }
                throw new FailedFileUploadException($message);
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