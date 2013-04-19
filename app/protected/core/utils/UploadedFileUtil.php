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
                throw new FailedFileUploadException(Zurmo::t('Core', 'The file did not exist'));
            }
            elseif ($uploadedFile->getHasError())
            {
                $error = $uploadedFile->getError();
                $messageParams = array('{file}' => $uploadedFile->getName(), '{limit}' => self::getSizeLimit());
                if ($error == UPLOAD_ERR_NO_FILE)
                {
                    $message = Zurmo::t('Core', 'The file did not exist');
                }
                elseif ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE)
                {
                    $message = Zurmo::t('yii', 'The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_PARTIAL)
                {
                    $message = Zurmo::t('yii', 'The file "{file}" is too large. Its size cannot exceed {limit} bytes.',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_NO_TMP_DIR)
                {
                    $message = Zurmo::t('yii', 'Missing the temporary folder to store the uploaded file "{file}".',
                                                 $messageParams);
                }
                elseif ($error == UPLOAD_ERR_CANT_WRITE)
                {
                    $message = Zurmo::t('yii', 'Failed to write the uploaded file "{file}" to disk.',
                                                 $messageParams);
                }
                elseif (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION)
                {
                    $message = Zurmo::t('yii', 'File upload was stopped by extension.');
                }
                else
                {
                    //Unsupported or unknown error.
                    $message = Zurmo::t('Core', 'There was an error uploading the file.');
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
        private static function getSizeLimit()
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