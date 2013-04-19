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
     * Represents a PO Gettext message file
     */
    class ZurmoGettextPoFile
    {
        protected $fileSource;

        protected $fileObject;

        protected $messages = array();

        public function __construct($fileSource = null)
        {
            if ($fileSource)
            {
                $this->fileSource = $fileSource;
            }
        }

        protected function openFile($fileSource = null)
        {
            if (!$fileSource)
            {
                $fileSource = $this->fileSource;
            }

            try
            {
                $this->fileObject = new SplFileObject($fileSource);
            }
            catch (Exception $e)
            {
                throw new FileNotReadableException(Zurmo::t('Core', 'Can not open the file.'));
            }
        }

        /**
         * Reads a PO file to the protected $messages class variable
         *
         * @param string $file file path
         * @param bool $skipEmptyContext If set to true, then all messages without
         *                               or with empty context will be skiped
         * @return array message translations
         */
        public function read($skipEmptyContext = true)
        {
            $this->messages = array();

            if (!$this->fileObject)
            {
                $this->openFile();
            }

            try
            {
                $context = 'COMMENT';
                $current = array();

                while ($this->fileObject->valid())
                {
                    $line = $this->fileObject->getCurrentLine();

                    if ($this->fileObject->key() == 0)
                    {
                        $line = str_replace("\xEF\xBB\xBF", '', $line);
                    }

                    $line = trim(strtr($line, array("\\\n" => "")));

                    if (!strncmp('#', $line, 1))
                    {
                        switch ($context)
                        {
                            case 'COMMENT':
                                $current['#'][] = substr($line, 1);
                                break;
                            case 'MSGSTR':
                                $this->addMessage($current, $skipEmptyContext);
                                $current = array();
                                $current['#'][]  = substr($line, 1);
                                $context = 'COMMENT';
                                break;
                            default:
                                throw new FailedParseGettextException(
                                    Zurmo::t('Core', 'Failed parsing {fileSource}: "msgstr" was expected on line {lineNumber}.',
                                        array(
                                            '{fileSource}' => $this->fileObject->getFilename(),
                                            '{lineNumber}' => $this->fileObject->key()
                                        )
                                    )
                                );
                        }
                    }
                    elseif (!strncmp('msgid', $line, 5))
                    {
                        switch ($context)
                        {
                            case 'MSGSTR':
                                $this->addMessage($current, $skipEmptyContext);
                                $current = array();
                                break;
                            case 'MSGID':
                                throw new FailedParseGettextException(
                                    Zurmo::t('Core', 'Failed parsing {fileSource}: "msgid" is unexpected on line {lineNumber}.',
                                        array(
                                            '{fileSource}' => $this->fileObject->getFilename(),
                                            '{lineNumber}' => $this->fileObject->key()
                                        )
                                    )
                                );
                                break;
                        }

                        $line = trim(substr($line, 5));

                        $quoted = $this->parseQuotedString($line);

                        $current['msgid'] = $quoted;
                        $context = 'MSGID';
                    }
                    elseif (!strncmp('msgctxt', $line, 7))
                    {
                        if ($context == 'MSGSTR')
                        {
                            $this->addMessage($current, $skipEmptyContext);
                            $current = array();
                        }
                        elseif (!empty($current['msgctxt']))
                        {
                            throw new FailedParseGettextException(
                                Zurmo::t('Core', 'Failed parsing {fileSource}: "msgctxt" is unexpected on line {lineNumber}.',
                                    array(
                                        '{fileSource}' => $this->fileObject->getFilename(),
                                        '{lineNumber}' => $this->fileObject->key()
                                    )
                                )
                            );
                        }

                        $line = trim(substr($line, 7));

                        $quoted = $this->parseQuotedString($line);

                        $current['msgctxt'] = $quoted;
                        $context = 'MSGCTXT';
                    }
                    elseif (!strncmp('msgstr', $line, 6))
                    {
                        if (($context != 'MSGID') && ($context != 'MSGCTXT'))
                        {
                            throw new FailedParseGettextException(
                                Zurmo::t('Core', 'Failed parsing {fileSource}: "msgstr" is unexpected on line {lineNumber}.',
                                    array(
                                        '{fileSource}' => $this->fileObject->getFilename(),
                                        '{lineNumber}' => $this->fileObject->key()
                                    )
                                )
                            );
                        }

                        $line = trim(substr($line, 6));

                        $quoted = $this->parseQuotedString($line);

                        $current['msgstr'] = $quoted;

                        $context = 'MSGSTR';
                    }
                    elseif ($line != '')
                    {
                        $quoted = $this->parseQuotedString($line);

                        switch ($context)
                        {
                            case 'MSGID':
                                $current['msgid'] .= $quoted;
                                break;
                            case 'MSGCTXT':
                                $current['msgctxt'] .= $quoted;
                                break;
                            case 'MSGSTR':
                                $current['msgstr'] .= $quoted;
                                break;
                            default:
                                throw new FailedParseGettextException(
                                    Zurmo::t('Core', 'Failed parsing {fileSource}: there is an unexpected string on line {lineNumber}.',
                                        array(
                                            '{fileSource}' => $this->fileObject->getFilename(),
                                            '{lineNumber}' => $this->fileObject->key()
                                        )
                                    )
                                );
                        }
                    }
                }

                if ($context == 'MSGSTR')
                {
                    $this->addMessage($current, $skipEmptyContext);
                }
                elseif ($context != 'COMMENT')
                {
                    throw new FailedParseGettextException(Zurmo::t('Core', ''));
                }
            }
            catch (RuntimeException $e)
            {
                throw new FailedParseGettextException(Zurmo::t('Core', 'Failed to read the file'));
            }

            return $this->getMessages();
        }

        public function getMessages()
        {
            return $this->messages;
        }

        protected function addMessage($message, $skipEmptyContext = true)
        {
            if ($skipEmptyContext && empty($message['msgctxt']))
            {
                return;
            }

            $this->messages[$message['msgid']] = $message;
        }

        protected function parseQuotedString($string)
        {
            if (substr($string, 0, 1) != substr($string, -1, 1))
            {
                throw new FailedParseGettextException(
                    Zurmo::t('Core', 'Failed parsing {fileSource}: syntax error on line {lineNumber}',
                        array(
                            '{fileSource}' => $this->fileObject->getFilename(),
                            '{lineNumber}' => $this->fileObject->key()
                        )
                    )
                );
            }

            $quote = substr($string, 0, 1);
            $string = substr($string, 1, -1);

            switch ($quote)
            {
                case '"':
                    return stripcslashes($string);
                    break;
                case "'":
                    return $string;
                    break;
                default:
                    throw new FailedParseGettextException(
                        Zurmo::t('Core', 'Failed parsing {fileSource}: syntax error on line {lineNumber}',
                            array(
                                '{fileSource}' => $this->fileObject->getFilename(),
                                '{lineNumber}' => $this->fileObject->key()
                            )
                        )
                    );
            }
        }
    }
?>