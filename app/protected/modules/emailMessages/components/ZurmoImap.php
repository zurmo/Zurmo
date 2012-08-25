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
     * Helper class to manage access to IMAP server
     */
    class ZurmoImap extends CApplicationComponent
    {
        /*
         * IMAP host
         */
        public $imapHost;

        /*
         * IMAP username
         */
        public $imapUsername;

        /*
         * IMAP password
         */
        public $imapPassword;

        /**
         * IMAP port
         */
        public $imapPort = 143;

        /**
         * Does IMAP server require secure connection
         */
        public $imapSSL = false;

        /**
         * IMAP folder
         */
        public $imapFolder = 'INBOX';

        /**
         * IMAP stream. It is setup after connection to IMAP server established.
         */
        protected $imapStream;

        /**
        * Contains array of settings to load during initialization from the configuration table.
        * @see loadInboundSettings
        * @var array
        */
        protected $settingsToLoad = array(
            'imapHost',
            'imapUsername',
            'imapPassword',
            'imapPort',
            'imapSSL',
            'imapFolder'
        );

        /**
        * Called once per page load, will load up outbound settings from the database if available.
        * (non-PHPdoc)
        * @see CApplicationComponent::init()
        */
        public function init()
        {
            parent::init();
            $this->loadInboundSettings();
        }

        /**
         * Load inbound settings from the database.
         */
        public function loadInboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                if (null !== $keyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName))
                {
                    $this->$keyName = $keyValue;
                }
            }
        }

        /**
        * Set inbound settings into the database.
        */
        public function setInboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $this->$keyName);
            }
        }

        /**
         * Connect to imap server
         * @throws CException
         * @return bool
         */
        public function connect()
        {
            $errorReporting = error_reporting();
            error_reporting(0);
            // Clear previous imap errors from stack
            imap_errors();

            if ($this->imapSSL)
            {
                $ssl = "/ssl";
            }
            else
            {
                $ssl = "";
            }
            // To-Do: What to do with novalidate-cert???
            $hostname = "{" . $this->imapHost . ":" . $this->imapPort . "/imap" . $ssl . "/novalidate-cert}" . $this->imapFolder;

            if (is_resource($this->imapStream))
            {
                imap_close($this->imapStream);
                $this->imapStream = null;
            }

            $resource = imap_open($hostname, $this->imapUsername, $this->imapPassword, null, 1);

            $errors = imap_errors();
            error_reporting($errorReporting);
            if (!$errors && is_resource($resource))
            {
                $this->imapStream = $resource;
                return true;
            }
            else
            {
                $this->imapStream = null;
                return false;
            }
        }

        /**
         * Get detailed info about imap mail box
         */
        public function getMessageBoxStatsDetailed()
        {
            return imap_mailboxmsginfo($this->imapStream);
        }

        /**
        * Get info about imap mail box
        */
        public function getMessageBoxStats()
        {
            return imap_check($this->imapStream);
        }

        /**
        * Get email with attachments
        * @param int messageNumbers - message number
        * @param Object $mailHeaderInfo
        * @return array the email info
        */
        protected function getMessage($messageNumber, $mailHeaderInfo)
        {
            $imapMessage = new ImapMessage();
            $structure = imap_fetchstructure($this->imapStream, $messageNumber);
            foreach ($mailHeaderInfo->to as $key => $to)
            {
                if (isset($to->personal))
                {
                    $imapMessage->to[$key]['name'] = $to->personal;
                }
                else
                {
                    $imapMessage->to[$key]['name'] = $to->mailbox;
                }
                $imapMessage->to[$key]['email'] = $to->mailbox . '@' . $to->host;
            }

            if (isset($mailHeaderInfo->cc))
            {
                foreach ($mailHeaderInfo->cc as $key => $cc)
                {
                    if (isset($cc->personal))
                    {
                        $imapMessage->cc[$key]['name'] = $cc->personal;
                    }
                    else
                    {
                        $imapMessage->cc[$key]['name'] = $cc->mailbox;
                    }
                    $imapMessage->cc[$key]['email'] = $cc->mailbox . '@' . $cc->host;
                }
            }

            if (isset($mailHeaderInfo->from[0]->personal))
            {
                $imapMessage->fromName = $mailHeaderInfo->from[0]->personal;
            }
            else
            {
                $imapMessage->fromName = $mailHeaderInfo->from[0]->mailbox;
            }
            $imapMessage->fromEmail = $mailHeaderInfo->from[0]->mailbox . '@' . $mailHeaderInfo->from[0]->host;

            if (isset($mailHeaderInfo->sender))
            {
                if (isset($mailHeaderInfo->sender[0]->personal))
                {
                    $imapMessage->senderName = $mailHeaderInfo->sender[0]->personal;
                }
                $imapMessage->senderEmail = $mailHeaderInfo->sender[0]->mailbox . '@' . $mailHeaderInfo->from[0]->host;
            }
            else
            {
                if (isset($imapMessage->fromName))
                {
                    $imapMessage->senderName = $imapMessage->fromName;
                }
                $imapMessage->senderEmail = $imapMessage->fromName;
            }

            $imapMessage->subject = $mailHeaderInfo->subject;
            $imapMessage->textBody = $this->getPart($messageNumber, 'TEXT/PLAIN', $structure);
            $imapMessage->htmlBody = $this->getPart($messageNumber, 'TEXT/HTML', $structure);
            $imapMessage->attachments = $this->getAttachments($structure, $messageNumber);
            $imapMessage->createdDate = $mailHeaderInfo->date;
            $imapMessage->uid = $this->getMessageUId($mailHeaderInfo->Msgno);
            $imapMessage->msgNumber = $mailHeaderInfo->Msgno;
            $imapMessage->msgId = $mailHeaderInfo->message_id;

            return $imapMessage;
        }

        /**
         * Get all messages, that satisfy some criteria, for example: 'ALL', 'UNSEEN', 'SUBJECT "Hello"'
         * @param array $searchCriteria the find conditions and params
         * @param int $messagesSinceTimestamp
         * @return array the messages that was found
         */
        public function getMessages($searchCriteria = 'ALL', $messagesSinceTimestamp = 0)
        {
            $messages = array();
            $imapInfo = $this->getMessageBoxStats();
            $messageNumbers = imap_search($this->imapStream, $searchCriteria);

            if (is_array($messageNumbers) && count($messageNumbers) > 0)
            {
                foreach ($messageNumbers as $messageNumber)
                {
                    $mailHeaderInfo = imap_headerinfo($this->imapStream, $messageNumber);
                    if (strtotime($mailHeaderInfo->date) > $messagesSinceTimestamp)
                    {
                        $messages[] = $this->getMessage($messageNumber, $mailHeaderInfo);
                    }
                }
            }
            return $messages;
        }

        /**
         * Expunge all messages on IMAP server
         */
        public function expungeMessages()
        {
            imap_expunge($this->imapStream);
            return true;
        }

        /**
         * Delete all messages on IMAP server
         */
        public function deleteMessages($expunge = false)
        {
            $messages = $this->getMessages();
            if (!empty($messages))
            {
                foreach ($messages as $message)
                {
                    $this->deleteMessage($message->uid);
                }
            }
            if ($expunge)
            {
                $this->expungeMessages();
            }
            return true;
        }

        /**
         * Delete message on IMAP server
         * @param int $msgUid
         */
        public function deleteMessage($msgUid)
        {
            imap_delete($this->imapStream, $msgUid, FT_UID);
        }

        /**
         * Get all message attachments
         * @param object $structure
         * @param int $messageId
         */
        protected function getAttachments($structure, $messageId)
        {
            $attachments = array();
            if (isset($structure->parts) && count($structure->parts))
            {
                for ($i = 0; $i < count($structure->parts); $i++)
                {
                    $attachment = array(
                          'is_attachment' => false,
                          'filename' => '',
                          'name' => '',
                          'attachment' => '');

                    if ($structure->parts[$i]->ifdparameters)
                    {
                        foreach ($structure->parts[$i]->dparameters as $object)
                        {
                            if (strtolower($object->attribute) == 'filename')
                            {
                                $attachment['is_attachment'] = true;
                                $attachment['filename'] = $object->value;
                            }
                        }
                    }

                    if ($structure->parts[$i]->ifparameters)
                    {
                        foreach ($structure->parts[$i]->parameters as $object)
                        {
                            if (strtolower($object->attribute) == 'name')
                            {
                                $attachment['is_attachment'] = true;
                                $attachment['name'] = $object->value;
                            }
                        }
                    }

                    if ($attachment['is_attachment'])
                    {
                        $attachment['attachment'] = imap_fetchbody($this->imapStream, $messageId, $i + 1);
                        if ($structure->parts[$i]->encoding == 3)
                        {
                            // 3 = BASE64
                            $attachment['attachment'] = base64_decode($attachment['attachment']);
                        }
                        elseif ($structure->parts[$i]->encoding == 4)
                        {
                            // 4 = QUOTED-PRINTABLE
                            $attachment['attachment'] = quoted_printable_decode($attachment['attachment']);
                        }
                        $attachments[] = $attachment;
                    }
                }
            }
            return $attachments;
        }

        /**
         * Get a sequenced message id
         *
         * @param string $msgNo in the format <.*@.*> from the email
         *
         * @return mixed on imap its the unique id (int) and for others its a base64_encoded string
         */
        protected function getMessageUId($msgNo)
        {
            return imap_uid($this->imapStream, $msgNo);
        }

        /**
         * get the count of mails for the given conditions and params
         *
         * @todo conditions / order other find params
         *
         * @param array $query conditions for the query
         * @return int the number of emails found
         */
        protected function mailCount($query)
        {
            return imap_num_msg($this->imapStream);
        }

        /**
         *
         * Get mime type.
         * @param object $structure
         */
        protected function getMimeType($structure)
        {
            $primaryMimeType = array('TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER');
            if ($structure->subtype)
            {
                return $primaryMimeType[(int) $structure->type] . '/' . $structure->subtype;
            }

            return 'TEXT/PLAIN';
        }

        /**
         *
         * Get message part
         * @param int $msgNumber
         * @param string $mimeType
         * @param structure $structure
         * @param int $partNumber
         */
        protected function getPart($msgNumber, $mimeType, $structure = null, $partNumber = false)
        {
            $prefix = null;
            if (!$structure)
            {
                return false;
            }

            if ($mimeType == $this->getMimeType($structure))
            {
                $partNumber = ($partNumber > 0) ? $partNumber : 1;

                return imap_fetchbody($this->imapStream, $msgNumber, $partNumber);
            }

            /* multipart */
            if ($structure->type == 1)
            {
                foreach ($structure->parts as $index => $subStructure)
                {
                    if ($partNumber)
                    {
                        $prefix = $partNumber . '.';
                    }

                    $data = $this->getPart($msgNumber, $mimeType, $subStructure, $prefix . ($index + 1));
                    if ($data)
                    {
                        return quoted_printable_decode($data);
                    }
                }
            }
        }
    }
?>