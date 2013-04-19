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
                'accounts',
                'contacts',
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array();
        }

        public static function getTranslatedRightsLabels()
        {
            $params                            = LabelUtil::getTranslationParamsForAllModules();
            $labels                            = array();
            $labels[self::RIGHT_CREATE_LEADS]  = Zurmo::t('LeadsModule', 'Create LeadsModulePluralLabel',     $params);
            $labels[self::RIGHT_DELETE_LEADS]  = Zurmo::t('LeadsModule', 'Delete LeadsModulePluralLabel',     $params);
            $labels[self::RIGHT_ACCESS_LEADS]  = Zurmo::t('LeadsModule', 'Access LeadsModulePluralLabel Tab', $params);
            $labels[self::RIGHT_CONVERT_LEADS] = Zurmo::t('LeadsModule', 'Convert LeadsModulePluralLabel',    $params);
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'convertToAccountSetting' => LeadsModule::CONVERT_ACCOUNT_NOT_REQUIRED,
                'convertToAccountAttributesMapping' => array(
                    'industry'         => 'industry',
                    'website'          => 'website',
                    'primaryAddress'   => 'billingAddress',
                    'secondaryAddress' => 'shippingAddress',
                    'officePhone'      => 'officePhone',
                    'officeFax'        => 'officeFax',
                    'companyName'      => 'name',
                ),
                'designerMenuItems' => array(
                    'showFieldsLink'  => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink'   => true,
                ),
                'globalSearchAttributeNames' => array(
                    'fullName',
                    'anyEmail',
                    'officePhone',
                    'mobilePhone',
                    'companyName'
                ),
                'tabMenuItems' => array(
                    array(
                        'label'  => "eval:Zurmo::t('LeadsModule', 'LeadsModulePluralLabel', \$translationParams)",
                        'url'    => array('/leads/default'),
                        'right'  => self::RIGHT_ACCESS_LEADS,
                        'mobile' => true,
                    ),
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label'  => "eval:Zurmo::t('LeadsModule', 'LeadsModuleSingularLabel', \$translationParams)",
                        'url'    => array('/leads/default/create'),
                        'right'  => self::RIGHT_CREATE_LEADS,
                        'mobile' => true,
                    ),
                ),
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

        public static function getDemoDataMakerClassNames()
        {
            return array('LeadsDemoDataMaker');
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
