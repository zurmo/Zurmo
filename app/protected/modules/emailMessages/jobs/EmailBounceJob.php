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
     * A job for retrieving emails from dropbox(catch-all) folder
     */
    class EmailBounceJob extends ImapBaseJob
    {
        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           return Zurmo::t('EmailMessagesModule', 'Process Bounce Email Job');
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'EmailBounce';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('EmailMessagesModule', 'Every 5 minutes.');
        }

        protected function processMessage(ImapMessage $message)
        {
            return $this->createBounceActivity($message);
        }

        protected function getLastImapDropboxCheckTime()
        {
            return EmailMessagesModule::getLastBounceImapDropboxCheckTime();
        }

        protected function setLastImapDropboxCheckTime($time)
        {
            EmailMessagesModule::setLastBounceImapDropboxCheckTime($time);
        }

        protected function resolveImapObject()
        {
            if (!isset($this->imapManager))
            {
                $this->imapManager  = new ZurmoBounce();
                $this->imapManager->init();
            }
            return $this->imapManager;
        }

        protected function createBounceActivity(ImapMessage $message)
        {
            $zurmoItemClass = null;
            $zurmoItemId    = null;
            $zurmoPersonId  = null;
            $headerTags     = array('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            $headers        = EmailBounceUtil::resolveCustomHeadersFromTextBody($headerTags, $message->textBody);
            $this->deleteMessage($message);
            if ($headers === false)
            {
                return false;
            }
            extract($headers);
            assert('$zurmoItemClass === "AutoresponderItem" || $zurmoItemClass === "CampaignItem"');
            assert('$zurmoItemId > 0');
            assert('$zurmoPersonId > 0');
            $activityClassName          = EmailMessageActivityUtil::resolveModelClassNameByModelType($zurmoItemClass);
            $activityUtilClassName      = $activityClassName . 'Util';
            $type                       = $activityClassName::TYPE_BOUNCE;
            $activityData               = array('modelId'   => $zurmoItemId,
                                                'modelType' => $zurmoItemClass,
                                                'personId'  => $zurmoPersonId,
                                                'url'       => null,
                                                'type'      => $type);
            $activityCreatedOrUpdated   = $activityUtilClassName::createOrUpdateActivity($activityData);
            try
            {
                if (!$activityCreatedOrUpdated)
                {
                    throw new NotSupportedException();
                }
            }
            catch (NotSupportedException $e)
            {
                return false;
            }
            return true;
        }

        protected function deleteMessage(ImapMessage $message)
        {
            if (isset($message->uid)) // For tests uid will not be setup
            {
                $this->imapManager->deleteMessage($message->uid);
            }
        }
    }
?>