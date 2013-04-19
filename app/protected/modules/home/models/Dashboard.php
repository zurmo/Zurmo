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

    class Dashboard extends OwnedSecurableItem
    {
        const DEFAULT_USER_LAYOUT_ID = 1;

        public static function getByLayoutIdAndUser($layoutId, $user)
        {
            assert('is_integer($layoutId) && $layoutId >= 1');
            assert('$user instanceof User && $user->id > 0');
            $sql = 'select dashboard.id id '            .
                   'from dashboard, ownedsecurableitem '                          .
                   'where ownedsecurableitem.owner__user_id = ' . $user->id       .
                   ' and dashboard.ownedsecurableitem_id = ownedsecurableitem.id '.
                   ' and layoutid = ' . $layoutId                                 .
                   ' order by layoutId;';
            $ids = R::getCol($sql);
            assert('count($ids) <= 1');
            if (count($ids) == 0)
            {
                if ($layoutId == Dashboard::DEFAULT_USER_LAYOUT_ID)
                {
                    return Dashboard::setDefaultDashboardForUser($user);
                }
                throw new NotFoundException();
            }
            $bean = R::load(RedBeanModel::getTableName('Dashboard'), $ids[0]);
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public static function getRowsByUserId($userId)
        {
            assert('is_integer($userId) && $userId >= 1');
            $sql = 'select dashboard.id id, dashboard.name name, layoutid layoutId ' .
                   'from dashboard, ownedsecurableitem '                             .
                   'where ownedsecurableitem.owner__user_id = ' . $userId            .
                   ' and dashboard.ownedsecurableitem_id = ownedsecurableitem.id '   .
                   'order by layoutId;';
            return R::getAll($sql);
        }

        public static function getNextLayoutId()
        {
            return max(2, (int)R::getCell('select max(layoutId) + 1 from dashboard'));
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'layoutId'   => Zurmo::t('HomeModule',  'Layout Id',   array(), null, $language),
                    'layoutType' => Zurmo::t('HomeModule',  'Layout Type', array(), null, $language),
                    'isDefault'  => Zurmo::t('HomeModule',  'Is Default',  array(), null, $language),
                    'name'       => Zurmo::t('ZurmoModule', 'Name',        array(), null, $language),
                )
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('HomeModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'layoutId',
                    'layoutType',
                    'isDefault',
                    'name',
                ),
                'rules' => array(
                    array('isDefault',  'boolean'),
                    array('layoutId',   'required'),
                    array('layoutId',   'type',   'type' => 'integer'),
                    array('layoutType', 'required'),
                    array('layoutType', 'type',   'type' => 'string'),
                    array('layoutType', 'length', 'max' => 10),
                    array('name',       'required'),
                    array('name',       'type',   'type' => 'string'),
                    array('name',       'length', 'min' => 3, 'max' => 64),
                ),
                'defaultSortAttribute' => 'name'
            );
            return $metadata;
        }

        /**
         * Used to set the default dashboard information
         * for dashboard layoutId=1 for each user
         * @return Dashboard model.
         */
        private static function setDefaultDashboardForUser($user)
        {
            assert('$user instanceof User && $user->id > 0');
            $dashboard             = new Dashboard();
            $dashboard->name       = Zurmo::t('HomeModule', 'Dashboard');
            $dashboard->layoutId   = Dashboard::DEFAULT_USER_LAYOUT_ID;
            $dashboard->owner      = $user;
            $dashboard->layoutType = '50,50'; // Not Coding Standard
            $dashboard->isDefault  = true;
            $saved                 = $dashboard->save();
            assert('$saved'); // TODO - deal with the properly.
            return $dashboard;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'HomeModule';
        }

        /**
         * BEFORE ADDING TO THIS ARRAY - Remember to change the assertion in JuiPortlets:::init()
         */
        public static function getLayoutTypesData()
        {
            return array(
                '100'   => Zurmo::t('HomeModule', '1 Column'),
                '50,50' => Zurmo::t('HomeModule', '2 Columns'), // Not Coding Standard
                '75,25' => Zurmo::t('HomeModule', '2 Columns Left Strong'), // Not Coding Standard
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('HomeModule', 'Dashboard', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('HomeModule', 'Dashboards', array(), null, $language);
        }
    }
?>
