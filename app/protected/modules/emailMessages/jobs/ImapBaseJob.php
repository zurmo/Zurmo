<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * A job for processing emails over imap
     */
    abstract class ImapBaseJob extends BaseJob
    {
        protected $imapManager;

        abstract protected function resolveImapObject();
        abstract protected function getLastImapDropboxCheckTime();
        abstract protected function setLastImapDropboxCheckTime($time);
        abstract protected function processMessage(ImapMessage $message);

        /**
        * @returns the threshold for how long a job is allowed to run. This is the 'threshold'. If a job
        * is running longer than the threshold, the monitor job might take action on it since it would be
        * considered 'stuck'.
        */
        public static function getRunTimeThresholdInSeconds()
        {
            return 300;
        }

        /**
         *
         * (non-PHPdoc)
         * @see BaseJob::run()
         */
        public function run()
        {
            $this->resolveImapObject();
            if ($this->imapManager->connect())
            {
                $lastImapCheckTime     = $this->getLastImapDropboxCheckTime();
                if (isset($lastImapCheckTime) && $lastImapCheckTime != '')
                {
                   $criteria = "SINCE \"{$lastImapCheckTime}\" UNDELETED";
                   $lastImapCheckTimeStamp = strtotime($lastImapCheckTime);
                }
                else
                {
                    $criteria = "ALL UNDELETED";
                    $lastImapCheckTimeStamp = 0;
                }
                $messages = $this->imapManager->getMessages($criteria, $lastImapCheckTimeStamp);

                $lastCheckTime = null;
                if (count($messages))
                {
                   foreach ($messages as $message)
                   {
                       $lastMessageCreatedTime = strtotime($message->createdDate);
                       if (strtotime($message->createdDate) > strtotime($lastCheckTime))
                       {
                           $lastCheckTime = $message->createdDate;
                       }
                       $this->processMessage($message);
                   }
                   $this->imapManager->expungeMessages();
                   if ($lastCheckTime != '')
                   {
                       $this->setLastImapDropboxCheckTime($lastCheckTime);
                   }
                }
                return true;
            }
            else
            {
                $messageContent     = Zurmo::t('EmailMessagesModule', 'Failed to connect to mailbox');
                $this->errorMessage = $messageContent;
                return false;
            }
        }
    }
?>
