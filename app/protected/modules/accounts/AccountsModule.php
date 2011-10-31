<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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

    class AccountsModule extends SecurableModule
    {
        const RIGHT_CREATE_ACCOUNTS = 'Create Accounts';
        const RIGHT_DELETE_ACCOUNTS = 'Delete Accounts';
        const RIGHT_ACCESS_ACCOUNTS = 'Access Accounts Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Account', 'AccountsFilteredList');
        }

        public static function getUntranslatedRightsLabels()
        {
            $labels                              = array();
            $labels[self::RIGHT_CREATE_ACCOUNTS] = 'Create AccountsModulePluralLabel';
            $labels[self::RIGHT_DELETE_ACCOUNTS] = 'Delete AccountsModulePluralLabel';
            $labels[self::RIGHT_ACCESS_ACCOUNTS] = 'Access AccountsModulePluralLabel Tab';
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'tabMenuItems' => array(
                    array(
                        'label' => 'AccountsModulePluralLabel',
                        'url'   => array('/accounts/default'),
                        'right' => self::RIGHT_ACCESS_ACCOUNTS,
                        'items' => array(
                            array(
                                'label' => 'Create AccountsModuleSingularLabel',
                                'url'   => array('/accounts/default/create'),
                                'right' => self::RIGHT_CREATE_ACCOUNTS
                            ),
                            array(
                                'label' => 'AccountsModulePluralLabel',
                                'url'   => array('/accounts/default'),
                                'right' => self::RIGHT_ACCESS_ACCOUNTS
                            ),
                        ),
                    ),
                ),
                'designerMenuItems' => array(
                    'showFieldsLink' => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink' => true,
                ),
                'globalSearchAttributeNames' => array(
                    'name',
                    'anyEmail',
                    'officePhone',
                )
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Account';
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_ACCOUNTS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_ACCOUNTS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_ACCOUNTS;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'AccountsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassName()
        {
            return 'AccountsDemoDataMaker';
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'AccountsSearchForm';
        }
    }
?>
