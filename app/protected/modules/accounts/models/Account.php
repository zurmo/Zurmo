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

    class Account extends OwnedSecurableItem
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('AccountsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'AccountsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'annualRevenue',
                    'description',
                    'employees',
                    'name',
                    'officePhone',
                    'officeFax',
                    'website',
                ),
                'relations' => array(
                    'account'          => array(RedBeanModel::HAS_MANY_BELONGS_TO,  'Account'),
                    'accounts'         => array(RedBeanModel::HAS_MANY,             'Account'),
                    'billingAddress'   => array(RedBeanModel::HAS_ONE,              'Address',          RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'billingAddress'),
                    'contacts'         => array(RedBeanModel::HAS_MANY,             'Contact'),
                    'industry'         => array(RedBeanModel::HAS_ONE,              'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'industry'),
                    'opportunities'    => array(RedBeanModel::HAS_MANY,             'Opportunity'),
                    'primaryEmail'     => array(RedBeanModel::HAS_ONE,              'Email',            RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'primaryEmail'),
                    'secondaryEmail'   => array(RedBeanModel::HAS_ONE,              'Email',            RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'secondaryEmail'),
                    'shippingAddress'  => array(RedBeanModel::HAS_ONE,              'Address',          RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'shippingAddress'),
                    'type'             => array(RedBeanModel::HAS_ONE,              'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'type'),
                ),
                'derivedRelationsViaCastedUpModel' => array(
                    'meetings' => array(RedBeanModel::MANY_MANY, 'Meeting', 'activityItems'),
                    'notes'    => array(RedBeanModel::MANY_MANY, 'Note',    'activityItems'),
                    'tasks'    => array(RedBeanModel::MANY_MANY, 'Task',    'activityItems'),
                ),
                'rules' => array(
                    array('annualRevenue', 'type',    'type' => 'float'),
                    array('description',   'type',    'type' => 'string'),
                    array('employees',     'type',    'type' => 'integer'),
                    array('name',          'required'),
                    array('name',          'type',    'type' => 'string'),
                    array('name',          'length',  'min'  => 3, 'max' => 64),
                    array('officePhone',   'type',    'type' => 'string'),
                    array('officePhone',   'length',  'min'  => 1, 'max' => 24),
                    array('officeFax',     'type',    'type' => 'string'),
                    array('officeFax',     'length',  'min'  => 1, 'max' => 24),
                    array('website',       'url',     'defaultScheme' => 'http'),
                ),
                'elements' => array(
                    'account'         => 'Account',
                    'billingAddress'  => 'Address',
                    'description'     => 'TextArea',
                    'officePhone'     => 'Phone',
                    'officeFax'       => 'Phone',
                    'primaryEmail'    => 'EmailAddressInformation',
                    'secondaryEmail'  => 'EmailAddressInformation',
                    'shippingAddress' => 'Address',
                ),
                'customFields' => array(
                    'industry' => 'Industries',
                    'type'     => 'AccountTypes',
                ),
                'defaultSortAttribute' => 'name',
                'rollupRelations' => array(
                    'accounts' => array('contacts', 'opportunities'),
                    'contacts',
                    'opportunities'
                ),
                'noAudit' => array(
                    'annualRevenue',
                    'description',
                    'employees',
                    'website',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'Account';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'AccountGamification';
        }

        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'account'         => Zurmo::t('AccountsModule', 'Parent AccountsModuleSingularLabel',  $params, null, $language),
                    'accounts'        => Zurmo::t('AccountsModule', 'AccountsModulePluralLabel',           $params, null, $language),
                    'annualRevenue'   => Zurmo::t('AccountsModule', 'Annual Revenue',  array(), null, $language),
                    'billingAddress'  => Zurmo::t('AccountsModule', 'Billing Address',  array(), null, $language),
                    'contacts'        => Zurmo::t('ContactsModule', 'ContactsModulePluralLabel',           $params, null, $language),
                    'description'     => Zurmo::t('ZurmoModule',    'Description', array(), null, $language),
                    'employees'       => Zurmo::t('AccountsModule', 'Employees',  array(), null, $language),
                    'industry'        => Zurmo::t('ZurmoModule',    'Industry',  array(), null, $language),
                    'meetings'        => Zurmo::t('MeetingsModule', 'Meetings',  array(), null, $language),
                    'name'            => Zurmo::t('ZurmoModule',    'Name',  array(), null, $language),
                    'notes'           => Zurmo::t('NotesModule',    'Notes',  array(), null, $language),
                    'officePhone'     => Zurmo::t('AccountsModule', 'Office Phone',  array(), null, $language),
                    'officeFax'       => Zurmo::t('AccountsModule', 'Office Fax',  array(), null, $language),
                    'opportunities'   => Zurmo::t('OpportunitiesModule', 'OpportunitiesModulePluralLabel', $params, null, $language),
                    'primaryEmail'    => Zurmo::t('ZurmoModule',    'Primary Email',  array(), null, $language),
                    'secondaryEmail'  => Zurmo::t('ZurmoModule',    'Secondary Email',  array(), null, $language),
                    'shippingAddress' => Zurmo::t('AccountsModule', 'Shipping Address',  array(), null, $language),
                    'tasks'           => Zurmo::t('TasksModule',    'Tasks',  array(), null, $language),
                    'type'            => Zurmo::t('AccountsModule', 'Type',  array(), null, $language),
                    'website'         => Zurmo::t('ZurmoModule',    'Website',  array(), null, $language),
                )
            );
        }
    }
?>