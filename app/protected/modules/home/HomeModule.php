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

    class HomeModule extends SecurableModule
    {
        const RIGHT_CREATE_DASHBOARDS = 'Create Dashboards';
        const RIGHT_DELETE_DASHBOARDS = 'Delete Dashboards';
        const RIGHT_ACCESS_DASHBOARDS = 'Access Dashboards';

        public function getDependencies()
        {
            return array(
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Dashboard');
        }

        public static function getTabMenuItems($user = null)
        {
            $dashboards = null;
            $tabMenuItems = parent::getTabMenuItems();
            if ($user instanceof User && $user->id > 0)
            {
                $dashboards = Dashboard::getRowsByUserId($user->id);
            }
            if (!empty($dashboards))
            {
                $foundSavedDefaultDashboard = false;
                 $tabMenuItems = array(
                    array(
                        'label' => 'Home',
                        'url'   => array('/home/default'),
                    ),
                );
                foreach ($dashboards as $dashboard)
                {
                    if ($dashboard['layoutId'] == 1)
                    {
                        $foundSavedDefaultDashboard = true;
                    }
                    $menuItems[] = array(
                        'label' => $dashboard['name'],
                        'url'   => array('/home/default/dashboardDetails&id=' . $dashboard['id']),
                        'right' => self::RIGHT_ACCESS_DASHBOARDS
                    );
                }
                if (!$foundSavedDefaultDashboard)
                {
                    array_unshift($menuItems, array(
                            'label' => 'Dashboard',
                            'url'   => array('/home/default/dashboardDetails'),
                            'right' => self::RIGHT_ACCESS_DASHBOARDS
                        )
                    );
                }
                    $menuItems[] = array(
                        'label' => 'Create Dashboard',
                        'url'   => array('/home/default/createDashboard'),
                        'right' => self::RIGHT_CREATE_DASHBOARDS
                    );
                $tabMenuItems[0]['items'] = $menuItems;
            }
            return $tabMenuItems;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'tabMenuItems' => array(
                    array(
                        'label' => 'Home',
                        'url'   => array('/home/default'),
                        'items' => array(
                            array(
                                'label' => 'Dashboard',
                                'url'   => array('/home/default/dashboardDetails'),
                                'right' => self::RIGHT_ACCESS_DASHBOARDS
                            ),
                            array(
                                'label' => 'Create Dashboard',
                                'url'   => array('/home/default/createDashboard'),
                                'right' => self::RIGHT_CREATE_DASHBOARDS
                            ),
                        ),
                    ),

                ),
            );
            return $metadata;
        }

        protected static function getSingularModuleLabel()
        {
            return 'Home';
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_DASHBOARDS;
        }
    }
?>
