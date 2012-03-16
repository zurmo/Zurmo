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
     * Helper functionality for manipulation files.
     */
    class FileUtil
    {
        /**
         * Get files from directory, to be imported using Yii::import function
         * For WebApplication, we don't want to include files from test folders.
         * @param string $dir
         * @param string $basePath
         * @param string $beginAliasPath
         * @param boolean $includeTests
         * @return array
         */
        public static function getFilesFromDir($dir, $basePath, $beginAliasPath, $includeTests = false)
        {
            $files = array();
            if ($handle = opendir($dir))
            {
                while (false !== ($file = readdir($handle)))
                {
                    $includeFile = false;
                    if ($file != 'tests')
                    {
                        $includeFile = true;
                    }
                    elseif ($file == 'tests')
                    {
                        if ($includeTests)
                        {
                            $includeFile = true;
                        }
                        else
                        {
                            $includeFile = false;
                        }
                    }
                    if ($file != "." && $file != ".." && $includeFile)
                    {
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $file))
                        {
                            $dir2 = $dir . DIRECTORY_SEPARATOR . $file;
                            $files[] = self::getFilesFromDir($dir2, $basePath, $beginAliasPath, $includeTests);
                        }
                        elseif (substr(strrchr($file, '.'), 1) == 'php')
                        {
                            $tmp = $dir . DIRECTORY_SEPARATOR . $file;
                            $tmp = str_replace($basePath, $beginAliasPath, $tmp);
                            $tmp = str_replace(DIRECTORY_SEPARATOR, '.', $tmp);
                            $files[] = substr($tmp, 0, -4);
                        }
                    }
                }
                closedir($handle);
            }
            return ArrayUtil::flatten($files);
        }
    }
?>
