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
     * Module used to manage email messages, folders, and boxes.
     */
    class EmailMessagesModule extends SecurableModule
    {
        const RIGHT_ACCESS_CONFIGURATION         = 'Access Email Configuration';
        const RIGHT_CREATE_EMAIL_MESSAGES        = 'Create Emails';
        const RIGHT_DELETE_EMAIL_MESSAGES        = 'Delete Emails';
        const RIGHT_ACCESS_EMAIL_MESSAGES        = 'Access Emails Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('EmailBox',
                         'EmailFolder',
                         'EmailMessageContent',
                         'EmailMessage',
                         'EmailMessageSender',
                         'EmailMessageRecipient',
                         'EmailMessageSendError'
                         );
        }

        public static function getTranslatedRightsLabels()
        {
            $labels                                    = array();
            $labels[self::RIGHT_ACCESS_CONFIGURATION]  = Zurmo::t('EmailMessagesModule', 'Access Email Configuration');
            $labels[self::RIGHT_CREATE_EMAIL_MESSAGES] = Zurmo::t('EmailMessagesModule', 'Create Emails');
            $labels[self::RIGHT_DELETE_EMAIL_MESSAGES] = Zurmo::t('EmailMessagesModule', 'Delete Emails');
            $labels[self::RIGHT_ACCESS_EMAIL_MESSAGES] = Zurmo::t('EmailMessagesModule', 'Access Emails Tab');
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('EmailMessagesModule', 'Email Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('EmailMessagesModule', 'Manage Email Configuration')",
                        'route'            => '/emailMessages/default/configurationEdit',
                        'right'            => self::RIGHT_ACCESS_CONFIGURATION,
                    ),
                ),
                'userHeaderMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('EmailMessagesModule', 'Data Cleanup')",
                        'url' => array('/emailMessages/default/matchingList'),
                        'right' => self::RIGHT_ACCESS_EMAIL_MESSAGES,
                        'order' => 3,
                    ),
                ),
                'configureSubMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('EmailMessagesModule', 'Email SMTP Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('EmailMessagesModule', 'Manage Email SMTP Configuration')",
                        'route'            => '/emailMessages/default/configurationEditOutbound',
                        'right'            => self::RIGHT_ACCESS_CONFIGURATION,
                    ),
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('EmailMessagesModule', 'Email Archiving Configuration')",
                        'descriptionLabel' => "eval:Zurmo::t('EmailMessagesModule', 'Manage Email Archiving Configuration')",
                        'route'            => '/emailMessages/default/configurationEditArchiving',
                        'right'            => self::RIGHT_ACCESS_CONFIGURATION,
                    ),
                )
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'EmailMessage';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_EMAIL_MESSAGES;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_EMAIL_MESSAGES;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_EMAIL_MESSAGES;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'EmailMessagesDefaultDataMaker';
        }

        public static function hasPermissions()
        {
            return true;
        }

        /**
        * Get last Zurmo Stable version from global configuration property.
        */
        public static function getLastImapDropboxCheckTime()
        {
            $lastImapDropboxCheckTime = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', 'lastImapDropboxCheckTime');
            return $lastImapDropboxCheckTime;
        }

        /**
         * Set lastZurmoStableVersion global pconfiguration property.
         * @param string $zurmoVersion
         */
        public static function setLastImapDropboxCheckTime($lastImapDropboxCheckTime)
        {
            assert('isset($lastImapDropboxCheckTime)');
            assert('$lastImapDropboxCheckTime != ""');
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'lastImapDropboxCheckTime', $lastImapDropboxCheckTime);
        }
    }
?>