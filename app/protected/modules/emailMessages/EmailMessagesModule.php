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
     * Module used to manage email messages, folders, and boxes.
     */
    class EmailMessagesModule extends SecurableModule
    {
        const RIGHT_ACCESS_CONFIGURATION         = 'Access Email Configuration';
        const RIGHT_CREATE_EMAIL_MESSAGES        = 'Create Email Messages';
        const RIGHT_DELETE_EMAIL_MESSAGES        = 'Delete Email Messages';
        const RIGHT_ACCESS_EMAIL_MESSAGES        = 'Access Email Messages Tab';

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
                         'EmailFileModel',
                         'EmailFolder',
                         'EmailMessageContent',
                         'EmailMessage',
                         'EmailMessageSender',
                         'EmailMessageRecipient',
                         'EmailMessageSendError'
                         );
        }

        public static function getUntranslatedRightsLabels()
        {
            $labels                                    = array();
            $labels[self::RIGHT_ACCESS_CONFIGURATION]  = 'Access Email Configuration';
            $labels[self::RIGHT_CREATE_EMAIL_MESSAGES] = 'Create EmailMessagesModulePluralLabel';
            $labels[self::RIGHT_DELETE_EMAIL_MESSAGES] = 'Delete EmailMessagesModulePluralLabel';
            $labels[self::RIGHT_ACCESS_EMAIL_MESSAGES] = 'Access EmailMessagesModulePluralLabel Tab';
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => 'Email Configuration',
                        'descriptionLabel' => 'Manage Email Configuration',
                        'route'            => '/emailMessages/default/configurationEdit',
                        'right'            => self::RIGHT_ACCESS_CONFIGURATION,
                    ),
                ),
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
    }
?>