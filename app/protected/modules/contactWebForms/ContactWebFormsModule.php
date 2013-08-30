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

    class ContactWebFormsModule extends SecurableModule
    {
        const RIGHT_CREATE_CONTACT_WEB_FORMS = 'Create Contact Web Forms';

        const RIGHT_DELETE_CONTACT_WEB_FORMS = 'Delete Contact Web Forms';

        const RIGHT_ACCESS_CONTACT_WEB_FORMS = 'Access Contact Web Forms Tab';

        /**
         * @return array
         */
        public function getDependencies()
        {
            return array(
                'contacts',
            );
        }

        /**
         * @return array
         */
        public function getRootModelNames()
        {
            return array('ContactWebForm', 'ContactWebFormEntry');
        }

        /**
         * @return array
         */
        public static function getTranslatedRightsLabels()
        {
            $params                                       = LabelUtil::getTranslationParamsForAllModules();
            $labels                                       = array();
            $labels[self::RIGHT_CREATE_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule',
                                                            'Create ContactsModuleSingularLabel Web Forms',     $params);
            $labels[self::RIGHT_DELETE_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule',
                                                            'Delete ContactsModuleSingularLabel Web Forms',     $params);
            $labels[self::RIGHT_ACCESS_CONTACT_WEB_FORMS] = Zurmo::t('ContactWebFormsModule',
                                                            'Access ContactsModuleSingularLabel Web Forms Tab', $params);
            return $labels;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'designerMenuItems' => array(),
                'globalSearchAttributeNames' => array('webFormName'),
                'adminTabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ContactWebFormsModule', 'Web Forms')",
                        'url'   => array('/contactWebForms/default'),
                        'right' => self::RIGHT_ACCESS_CONTACT_WEB_FORMS,
                    ),
                ),
                'configureMenuItems' => array(
                    array(
                        'category'         => ZurmoModule::ADMINISTRATION_CATEGORY_GENERAL,
                        'titleLabel'       => "eval:Zurmo::t('ContactWebFormsModule', 'Web Forms')",
                        'descriptionLabel' => "eval:Zurmo::t('ContactWebFormsModule', 'Manage Web Forms')",
                        'route'            => '/contactWebForms/default',
                        'right'            => self::RIGHT_CREATE_CONTACT_WEB_FORMS,
                    ),
                )
            );
            return $metadata;
        }

        /**
         * @return string|void
         */
        public static function getPrimaryModelName()
        {
            return 'ContactWebForm';
        }

        /**
         * @return null|string
         */
        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CONTACT_WEB_FORMS;
        }

        /**
         * @return null|string
         */
        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CONTACT_WEB_FORMS;
        }

        /**
         * @return null|string
         */
        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_CONTACT_WEB_FORMS;
        }

        /**
         * @return null|string
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'ContactWebFormsSearchForm';
        }

        /**
         * @return bool
         */
        public static function hasPermissions()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        /**
         * @return array|void
         */
        public static function getDemoDataMakerClassNames()
        {
            return array('ContactWebFormDemoDataMaker', 'ContactWebFormEntryDemoDataMaker');
        }

        /**
         * @param string $language
         * @return string
         */
        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Form', array(), null, $language);
        }

        /**
         * @param string $language
         * @return string
         */
        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('ContactWebFormsModule', 'Contact Web Forms', array(), null, $language);
        }
    }
?>