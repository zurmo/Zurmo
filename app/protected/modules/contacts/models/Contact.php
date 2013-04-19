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

    class Contact extends Person
    {
        public static function getByName($name)
        {
            return ZurmoModelSearch::getModelsByFullName('Contact', $name);
        }

        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(

                    'account'          => Zurmo::t('AccountsModule', 'AccountsModuleSingularLabel',    $params, null, $language),
                    'companyName'      => Zurmo::t('ContactsModule', 'Company Name',  array(), null, $language),
                    'description'      => Zurmo::t('ZurmoModule',    'Description',  array(), null, $language),
                    'industry'         => Zurmo::t('ZurmoModule',    'Industry',  array(), null, $language),
                    'meetings'         => Zurmo::t('MeetingsModule', 'Meetings',  array(), null, $language),
                    'notes'            => Zurmo::t('NotesModule',    'Notes',  array(), null, $language),
                    'opportunities'    => Zurmo::t('OpportunitiesModule', 'OpportunitiesModulePluralLabel', $params, null, $language),
                    'secondaryAddress' => Zurmo::t('ZurmoModule',    'Secondary Address',  array(), null, $language),
                    'secondaryEmail'   => Zurmo::t('ZurmoModule',    'Secondary Email',  array(), null, $language),
                    'source'           => Zurmo::t('ContactsModule', 'Source', $params, null, $language),
                    'state'            => Zurmo::t('ContactsModule', 'Status', $params, null, $language),
                    'tasks'            => Zurmo::t('TasksModule',    'Tasks',  array(), null, $language),
                    'website'          => Zurmo::t('ZurmoModule',    'Website',  array(), null, $language),
                )
            );
        }

        public static function getModuleClassName()
        {
            return 'ContactsModule';
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
                    'companyName',
                    'description',
                    'website',
                ),
                'relations' => array(
                    'account'          => array(RedBeanModel::HAS_ONE,   'Account'),
                    'industry'         => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'industry'),
                    'opportunities'    => array(RedBeanModel::MANY_MANY, 'Opportunity'),
                    'secondaryAddress' => array(RedBeanModel::HAS_ONE,   'Address',          RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'secondaryAddress'),
                    'secondaryEmail'   => array(RedBeanModel::HAS_ONE,   'Email',            RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'secondaryEmail'),
                    'source'           => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'source'),
                    'state'            => array(RedBeanModel::HAS_ONE,   'ContactState', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'state'),
                ),
                'derivedRelationsViaCastedUpModel' => array(
                    'meetings' => array(RedBeanModel::MANY_MANY, 'Meeting', 'activityItems'),
                    'notes'    => array(RedBeanModel::MANY_MANY, 'Note',    'activityItems'),
                    'tasks'    => array(RedBeanModel::MANY_MANY, 'Task',    'activityItems'),
                ),
                'rules' => array(
                    array('companyName',      'type',    'type' => 'string'),
                    array('companyName',      'length',  'min'  => 3, 'max' => 64),
                    array('description',      'type',    'type' => 'string'),
                    array('state',            'required'),
                    array('website',          'url'),
                ),
                'elements' => array(
                    'account'          => 'Account',
                    'description'      => 'TextArea',
                    'secondaryEmail'   => 'EmailAddressInformation',
                    'secondaryAddress' => 'Address',
                    'state'            => 'ContactState',
                ),
                'customFields' => array(
                    'industry' => 'Industries',
                    'source'   => 'LeadSources',
                ),
                'defaultSortAttribute' => 'lastName',
                'rollupRelations' => array(
                    'opportunities',
                ),
                'noAudit' => array(
                    'description',
                    'website'
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
            return 'Contact';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'ContactGamification';
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Singular', $language);
            }
            return get_called_class();
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getPluralLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Plural', $language);
            }
            return static::getLabel($language) . 's';
        }
    }
?>
