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

    class Dashboard extends OwnedSecurableItem
    {
        const DEFAULT_USER_LAYOUT_ID = 1;

        public static function getByLayoutId($layoutId)
        {
            assert('is_integer($layoutId) && $layoutId >= 1');
            $bean = R::findOne('dashboard', "layoutid = $layoutId");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

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
            return max(2, R::getCell('select max(layoutId) + 1 from dashboard'));
        }

        public function __toString()
        {
            if (trim($this->name) == '')
            {
                return yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'layoutId',
                    'layoutType',
                    'isDefault',
                ),
                'rules' => array(
                    array('name',       'required'),
                    array('name',       'type',   'type' => 'string'),
                    array('name',       'length', 'min' => 3, 'max' => 64),
                    array('layoutId',   'required'),
                    array('layoutId',   'type',   'type' => 'number'),
                    array('layoutType', 'required'),
                    array('layoutType', 'type',   'type' => 'string'),
                    array('layoutType', 'length', 'max' => 10),
                    array('isDefault',  'boolean'),
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
            $dashboard->name       = yii::t('Default', 'Dashboard');
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
    }
?>
