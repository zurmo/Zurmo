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

    class LeadsModule extends SecurableModule
    {
        const CONVERT_NO_ACCOUNT           = 1;
        const CONVERT_ACCOUNT_NOT_REQUIRED = 2;
        const CONVERT_ACCOUNT_REQUIRED     = 3;

        const RIGHT_CREATE_LEADS  = 'Create Leads';
        const RIGHT_DELETE_LEADS  = 'Delete Leads';
        const RIGHT_ACCESS_LEADS  = 'Access Leads Tab';
        const RIGHT_CONVERT_LEADS = 'Convert Leads';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
                'accounts',
                'contacts',
            );
        }

        public function getRootModelNames()
        {
            return array('LeadsFilteredList');
        }

        public static function getUntranslatedRightsLabels()
        {
            $labels                            = array();
            $labels[self::RIGHT_CREATE_LEADS]  = 'Create LeadsModulePluralLabel';
            $labels[self::RIGHT_DELETE_LEADS]  = 'Delete LeadsModulePluralLabel';
            $labels[self::RIGHT_ACCESS_LEADS]  = 'Access LeadsModulePluralLabel Tab';
            $labels[self::RIGHT_CONVERT_LEADS] = 'Convert LeadsModulePluralLabel';
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'tabMenuItems' => array(
                    array(
                        'label' => 'LeadsModulePluralLabel',
                        'url'   => array('/leads/default'),
                        'right' => self::RIGHT_ACCESS_LEADS,
                        'items' => array(
                            array(
                                'label' => 'Create LeadsModuleSingularLabel',
                                'url'   => array('/leads/default/create'),
                                'right' => self::RIGHT_CREATE_LEADS
                            ),
                            array(
                                'label' => 'LeadsModulePluralLabel',
                                'url'   => array('/leads/default'),
                                'right' => self::RIGHT_ACCESS_LEADS
                            ),
                        )
                    ),
                ),
                'designerMenuItems' => array(
                    'showFieldsLink'  => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink'   => true,
                ),
                'convertToAccountSetting' => LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED,
                'convertToAccountAttributesMapping' => array(
                    'industry'         => 'industry',
                    'website'          => 'website',
                    'primaryAddress'   => 'billingAddress',
                    'secondaryAddress' => 'shippingAddress',
                    'owner'            => 'owner',
                    'officePhone'      => 'officePhone',
                    'officeFax'        => 'officeFax',
                    'companyName'      => 'name',
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label' => 'LeadsModuleSingularLabel',
                        'url'   => array('/leads/default/create'),
                        'right' => self::RIGHT_CREATE_LEADS
                    ),
                ),
                'globalSearchAttributeNames' => array(
                    'fullName',
                    'anyEmail',
                    'officePhone',
                    'mobilePhone',
                    'companyName'
                )
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Contact';
        }

        public static function getConvertToAccountSetting()
        {
            $metadata = LeadsModule::getMetadata();
            return $metadata['global']['convertToAccountSetting'];
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_LEADS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_LEADS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_LEADS;
        }

        public static function getDemoDataMakerClassName()
        {
            return 'LeadsDemoDataMaker';
        }

        public static function getStateMetadataAdapterClassName()
        {
            return 'LeadsStateMetadataAdapter';
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'LeadsSearchForm';
        }
    }
?>
