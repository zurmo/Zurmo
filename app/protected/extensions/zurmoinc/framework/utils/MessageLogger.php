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
     * Helper utility to capture information and error messages during the execution of functions.
     */
    class MessageLogger
    {
        /**
         * Error message type.
         */
        const ERROR = 1;

        /**
         * Info message type.
         */
        const INFO = 2;

        protected $errorMessagePresent = false;

        protected $messages = array();

        protected $messageStreamer;

        /**
         * Specify a MessageStreamer if desired.  A message streamer can allow messages to be streamed to the user
         * interface or command line as they are generated instead of waiting for the entire output to be finished.
         * @param object $messageStreamer MessageStreamer or null
         * @see MessageStreamer class
         */
        public function __construct($messageStreamer = null)
        {
            assert('$messageStreamer == null || $messageStreamer instanceof MessageStreamer');
            $this->messageStreamer = $messageStreamer;
        }

        /**
         * Add an informational message.
         * @param string $message
         */
        public function addInfoMessage($message)
        {
            $this->add(array(MessageLogger::INFO, $message));
        }

        /**
         * Add an error message.
         * @param string $message
         */
        public function addErrorMessage($message)
        {
            $this->errorMessagePresent = true;
            $this->add(array(MessageLogger::ERROR, $message));
        }

        protected function add($message)
        {
            assert('is_array($message)');
            $this->messages[] = $message;
            if ($this->messageStreamer != null)
            {
                $this->messageStreamer->add(static::getTypeLabel($message[0]) . ' - ' . $message[1]);
            }
        }

        public function getMessages()
        {
            return $this->messages;
        }

        /**
         * Print messages.  If $return is true, then the @return value is a string representing the message content.
         * @param boolean $return
         * @param boolean $errorOnly - Only print the error messages.
         */
        public function printMessages($return = false, $errorOnly = false)
        {
            $content = '';
            foreach ($this->messages as $messageInfo)
            {
                if (!$errorOnly || ($errorOnly && $messageInfo[0] == MessageLogger::ERROR))
                {
                    $content .= static::getTypeLabel($messageInfo[0]) . ' - ' . $messageInfo[1] . "\n";
                }
            }
            if ($return)
            {
                return $content;
            }
            echo $content;
        }

        /**
         * Given a message type, get the corresponding translated display label.
         * @param integer $type
         */
        public static function getTypeLabel($type)
        {
            assert('$type == MessageLogger::ERROR || $type == MessageLogger::INFO');
            if ($type == MessageLogger::ERROR)
            {
                return Yii::t('Default', 'Error');
            }
            else
            {
                return Yii::t('Default', 'Info');
            }
        }

        /**
         * @return boolean true if at least one error message is present.
         */
        public function isErrorMessagePresent()
        {
            return $this->errorMessagePresent;
        }
    }
?>