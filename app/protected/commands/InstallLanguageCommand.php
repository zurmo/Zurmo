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
     * InstallLanguage command is used to install language translation files in your Zurmo environment.
     *
     * TODO: - Allow for other archive formats, e.g. gzip, bz2, etc.
     *       - More testing on other OS's (OS specific files might be included in archive that needs to be ignored)
     *       - Code is contained in this file; might need its own Utility class.
     *       - Check built in that ZIP file entries are loaded onto the '/app/...' filepath.
     *
     * COMPLETED:
     *       - Built in user interaction for overwrite existing files.
     *       - Validation that language packs contains language that are supported by Zurmo (i.e. Yii).
     *
     */
    class InstallLanguageCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            return <<<EOD
        USAGE
          zurmoc InstallLanguage <zipArchive>

        DESCRIPTION
          This command installs language files contained in the specified archive file.

        PARAMETERS
         * zipArchive: archive file containing message translations.
EOD;
        }

        /**
         * Execute the action.
         * @param array command line parameters specific for this command
         */
        public function run($args)
        {
            echo PHP_EOL;
            if (!isset($args[0]))
            {
                $this->usageError('A language pack archive file must be specified.');
            }

            // Start
            $msg_file = INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands' . DIRECTORY_SEPARATOR . $args[0];
            if (!file_exists($msg_file))
            {
                $this->usageError('The provided filename does not exist.');
            }

            $zip = new ZipArchive;
            if ($zip->open($msg_file) === true)
            {
                if ($zip->numFiles > 0)
                {
                    $overwriteAll = false;
                    $locales = CLocale::getLocaleIDs();
                    for ($i = 0; $i < $zip->numFiles; $i++)
                    {
                        $zip->renameIndex($i, substr($zip->getNameIndex($i), strpos($zip->getNameIndex($i), 'app/')));
                        $entry = $zip->getNameIndex($i);

                        if (preg_match('#(__MACOSX)#i', $entry)) continue;
                        if (preg_match('#\.(php)$#i', $entry))
                        {
                            $extractPath = substr(INSTANCE_ROOT, 0, -strlen('app/')) . DIRECTORY_SEPARATOR;
                            $file        = $extractPath . $entry;

                            if (is_file($file))
                            {
                                preg_match('#(.*)\/messages\/(.*)\/(.*)#i', $entry, $matches);
                                if (is_array($matches))
                                {
                                    $lang = $matches[2];
                                }

                                if (!in_array($lang, $locales))
                                {
                                    echo ' Message-file `' . $entry . '` ignored. Language `' . $lang . '` is not a supported language/locale.' . PHP_EOL;
                                    continue;
                                }

                                if ($overwriteAll)
                                {
                                    echo ' Message-file `' . $entry . '` overwritten.' . PHP_EOL;
                                }
                                else
                                {
                                    echo '  Message-file `' . $entry . '` already exists but different.' . PHP_EOL;
                                    $answer = $this->prompt('    ...Overwrite? [Yes|No|All|Quit] ');
                                    if (!strncasecmp($answer, 'q', 1))
                                    {
                                        return;
                                    }
                                    elseif (!strncasecmp($answer, 'y', 1))
                                    {
                                        echo ' Message-file `' . $entry . '` overwritten.' . PHP_EOL;
                                    }
                                    elseif (!strncasecmp($answer, 'a', 1))
                                    {
                                        echo ' Message-file `' . $entry . '` overwritten.' . PHP_EOL;
                                        $overwriteAll = true;
                                    }
                                    else
                                    {
                                        echo ' Message-file `' . $entry . '` skipped.' . PHP_EOL;
                                        continue;
                                    }
                                }
                            }

                            $res = $zip->extractTo($extractPath, array($entry));
                            if ($res)
                            {
                                echo ' Message-file `' . $entry . '` successfully extracted.' . PHP_EOL;
                            }
                        }
                    }
                }
                else
                {
                    $this->usageError('The ZIP archive contains no files.');
                }
                $zip->close();
                if (!is_writable($msg_file))
                {
                    echo 'Unable to remove ZIP Archive file. Please verify the file permissions.';
                }
                else
                {
                    unlink($msg_file);
                }
            }
            else
            {
                $this->usageError('Error opening the ZIP archive.');
            }
        }
    }
?>