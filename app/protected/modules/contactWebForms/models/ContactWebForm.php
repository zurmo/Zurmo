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

    class ContactWebForm extends OwnedSecurableItem
    {
        /**
         * @param string $name
         * @return model
         */
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ContactWebFormsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * @param $language
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name'              => Zurmo::t('ContactWebFormsModule', 'Name', array(), null, $language),
                    'redirectUrl'       => Zurmo::t('ContactWebFormsModule', 'Redirect Url',  array(), null, $language),
                    'submitButtonLabel' => Zurmo::t('ContactWebFormsModule', 'Submit Button Label',  array(), null, $language),
                    'defaultState'      => Zurmo::t('ContactWebFormsModule', 'Default Status',  array(), null, $language),
                    'excludeStyles'     => Zurmo::t('ContactWebFormsModule', 'Exclude Styles',  array(), null, $language),
                    'language'          => Zurmo::t('ZurmoModule',           'Language',        array(), null, $language),
                )
            );
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'ContactWebFormsModule';
        }

        /**
         * @param $language
         * @return string
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Form', array(), null, $language);
        }

        /**
         * @param $language
         * @return string, display name for plural of the model class.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Forms', array(), null, $language);
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'redirectUrl',
                    'submitButtonLabel',
                    'serializedData',
                    'excludeStyles',
                    'language',
                ),
                'relations' => array(
                    'defaultState'     => array(RedBeanModel::HAS_ONE,   'ContactState', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'defaultState'),
                    'entries'          => array(RedBeanModel::HAS_MANY, 'ContactWebFormEntry', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'entries'),
                    'defaultOwner'     => array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'defaultOwner'),
                ),
                'rules' => array(
                    array('name',              'required'),
                    array('name',              'type', 'type' => 'string'),
                    array('redirectUrl',       'required'),
                    array('redirectUrl',       'url', 'defaultScheme' => 'http'),
                    array('submitButtonLabel', 'required'),
                    array('submitButtonLabel', 'type', 'type' => 'string'),
                    array('submitButtonLabel', 'default', 'value' => 'Submit'),
                    array('defaultState',      'required'),
                    array('serializedData',    'required'),
                    array('serializedData',    'type', 'type' => 'string'),
                    array('defaultOwner',      'required'),
                    array('excludeStyles',     'type', 'type' => 'boolean'),
                    array('excludeStyles',     'default', 'value' => 0),
                    array('language',          'type',    'type'  => 'string'),
                    array('language',          'length',  'max'   => 10),
                ),
                'elements' => array(
                    'name'              => 'Text',
                    'redirectUrl'       => 'Text',
                    'submitButtonLabel' => 'Text',
                    'defaultState'      => 'ContactState',
                    'defaultOwner'      => 'User',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array('serializedData', 'entries'),
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getRollUpRulesType()
        {
            return 'ContactWebForm';
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }
    }
?>