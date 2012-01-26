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
     * Helper class that overrides CFileHelper with some additional functionality for handling various file operations.
     */
    class ZurmoFileHelper extends CFileHelper
    {
        /**
         * Override to handle custom MimeType file if no mime extension can be found using built in methods.
         * Checks first the zurmo provided mime type database.  This is done first to ensure that extensions are not
         * incorrectly reported.
         * @see CFileHandler::getMimeType
         */
        public static function getMimeType($file, $magicFile = null, $checkExtension = true)
        {
            if ($checkExtension)
            {
                $mimeType = static::getMimeTypeByExtension($file);
                if ($mimeType != null)
                {
                    return $mimeType;
                }
            }

            if (function_exists('finfo_open'))
            {
                if (defined('FILEINFO_MIME_TYPE'))
                {
                    $options = FILEINFO_MIME_TYPE;
                }
                else
                {
                    $options = FILEINFO_MIME;
                }

                if ($magicFile === null)
                {
                    $info =  finfo_open($options);
                }
                else
                {
                    $info = finfo_open($options, $magicFile);
                }

                if ($info && ($result = finfo_file($info, $file)) !== false)
                {
                    return $result;
                }
            }

            if (function_exists('mime_content_type') && ($result = mime_content_type($file)) !== false)
            {
                return $result;
            }
        }

        /**
         * Override to handle custom MimeType file if no mime extension can be found using built in methods.
         * @see CFileHandler::getMimeTypeByExtension
         */
        public static function getMimeTypeByExtension($file, $magicFile = null)
        {
            static $extensions;
            if ($extensions === null)
            {
                if ($magicFile === null)
                {
                    $extensions = require(Yii::getPathOfAlias('ext.zurmoinc.framework.utils.ZurmoMimeTypes') . '.php');
                }
                else
                {
                    $extensions = $magicFile;
                }
            }
            if (($ext = pathinfo($file, PATHINFO_EXTENSION)) !== '')
            {
                $ext = strtolower($ext);
                if (isset($extensions[$ext]))
                {
                    return $extensions[$ext];
                }
            }
            return null;
        }
    }
?>