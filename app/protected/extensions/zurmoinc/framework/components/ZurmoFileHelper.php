<?php
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