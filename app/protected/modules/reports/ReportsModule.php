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
     * Module used to create and run reports
     */
    class ReportsModule extends SecurableModule
    {
        const RIGHT_CREATE_REPORTS = 'Create Reports';

        const RIGHT_DELETE_REPORTS = 'Delete Reports';

        const RIGHT_ACCESS_REPORTS = 'Access Reports Tab';

        public static function getTranslatedRightsLabels()
        {
            $labels                             = array();
            $labels[self::RIGHT_CREATE_REPORTS] = Zurmo::t('ReportsModule', 'Create Reports');
            $labels[self::RIGHT_DELETE_REPORTS] = Zurmo::t('ReportsModule', 'Delete Reports');
            $labels[self::RIGHT_ACCESS_REPORTS] = Zurmo::t('ReportsModule', 'Access Reports Tab');
            return $labels;
        }

        /**
         * @return array
         */
        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        /**
         * @return array
         */
        public function getRootModelNames()
        {
            return array('SavedReport');
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'globalSearchAttributeNames' => array(
                    'name',
                ),
                'tabMenuItems' => array(
                    array(
                        'label'  => 'Reports',
                        'url'    => array('/reports/default'),
                        'right'  => self::RIGHT_ACCESS_REPORTS,
                        'mobile' => false,
                    ),
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label'  => 'Report',
                        'url'    => array('/reports/default/selectType'),
                        'right'  => self::RIGHT_CREATE_REPORTS,
                        'mobile' => false,
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * @return string
         */
        public static function getPrimaryModelName()
        {
            return 'SavedReport';
        }

        /**
         * @return string
         */
        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_REPORTS;
        }

        /**
         * @return string
         */
        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_REPORTS;
        }

        /**
         * @return string
         */
        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_REPORTS;
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('ReportsDemoDataMaker');
        }

        /**
         * Even though reports are never globally searched, the search form can still be used by a specific
         * search view for a module.  Either this module or a related module.  This is why a class is returned.
         * @see modelsAreNeverGloballySearched controls it not being searchable though in the global search.
         */
        public static function getGlobalSearchFormClassName()
        {
            return 'ReportsSearchForm';
        }

        /**
         * @return bool
         */
        public static function modelsAreNeverGloballySearched()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasPermissions()
        {
            return true;
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('ReportsModule', 'Report', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('ReportsModule', 'Reports', array(), null, $language);
        }
    }
?>
