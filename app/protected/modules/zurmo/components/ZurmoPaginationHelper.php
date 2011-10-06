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

    /**
     * Helper class for handling pagination settings in the application.
     * Pagination settings by module will default to the general 'type'pageSize setting if its
     * configuration value is not found.  This component is available via Yii::app()->pagination.
     */
    class ZurmoPaginationHelper extends CApplicationComponent
    {
        /**
         * general list page size.
         */
        protected $_listPageSize;

        /**
         * Detailview's sub view lists page size.
         */
        protected $_subListPageSize;

        /**
         * Popup/Modal list page size.
         */
        protected $_modalListPageSize;

        /**
         * Dashboard portlets list page size.
         */
        protected $_dashboardListPageSize;

        /**
         * When a mass edit must complete using a progress bar, this is how many are processed at once.
         */
        protected $_massEditProgressPageSize;

        /**
         * How many records to import at one time.
         */
        protected $_importPageSize;

        /**
         * When using the auto complete functionality on an input field, this is how many values are returned
         * per search.
         */
        protected $_autoCompleteListPageSize;

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setListPageSize($value)
        {
            $this->_listPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setSubListPageSize($value)
        {
            $this->_subListPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setModalListPageSize($value)
        {
            $this->_modalListPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setDashboardListPageSize($value)
        {
            $this->_dashboardListPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setMassEditProgressPageSize($value)
        {
            $this->_massEditProgressPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setImportPageSize($value)
        {
            $this->_importPageSize = $value;
        }

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setAutoCompleteListPageSize($value)
        {
            $this->_autoCompleteListPageSize = $value;
        }

        /**
         * Call method to get the active value for a particular pagination type. If the active value doesnt exist
         * as a state on the currenet user, set the active value from the configuration
         * setting and return the active value.
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         * @return $pageSize - integer.
         */
        public function resolveActiveForCurrentUserByType($type, $moduleName = null)
        {
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName = $this->getKeyByTypeAndModuleName($type);
            if ( null == $pageSize = Yii::app()->user->getState($keyName))
            {
                $pageSize = $this->getForCurrentUserByType($type, $moduleName);
                Yii::app()->user->setState($keyName, $pageSize);
            }
            return $pageSize;
        }

        /**
         * Get the pagination value for the current user by pagination type.
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         * @return $pageSize - integer.
         */
        public function getForCurrentUserByType($type, $moduleName = null)
        {
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName = $this->getKeyByTypeAndModuleName($type);
            if ( null != $pageSize = ZurmoConfigurationUtil::getForCurrentUserByModuleName('ZurmoModule', $keyName))
            {
                return $pageSize;
            }
            return $this->{'_' . $type};
        }

        /**
         * Set the pagination value for the current user by pagination type.
         * Also sets value as active state value by key.
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         * @return $pageSize - integer.
         */
        public function setForCurrentUserByType($type, $value, $moduleName = null)
        {
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('is_int($value) && $value > 0');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName = $this->getKeyByTypeAndModuleName($type);
            ZurmoConfigurationUtil::setForCurrentUserByModuleName('ZurmoModule', $keyName, $value);
            Yii::app()->user->setState($keyName, $value);
        }

        /**
         * Get the pagination value for the specified user by pagination type.
         * @param $user - user model
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         * @return $pageSize - integer.
         */
        public function getByUserAndType($user, $type, $moduleName = null)
        {
            assert('$user instanceOf User && $user->id > 0');
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName = $this->getKeyByTypeAndModuleName($type);
            if (null != $pageSize = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', $keyName))
            {
                return $pageSize;
            }
            return $this->{'_' . $type};
        }

        /**
         * Set the pagination value for the specified user by pagination type.
         * @param $user - user model
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         */
        public function setByUserAndType($user, $type, $value, $moduleName = null)
        {
            assert('$user instanceOf User && $user->id > 0');
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('is_int($value) && $value > 0');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName = $this->getKeyByTypeAndModuleName($type);
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', $keyName, $value);
        }

        /**
         * Get the global pagination value by pagination type.
         * @param $type - pagination type
         * @param $moduleName - optional. Module class name.
         * @return $pageSize - integer.
         */
        public function getGlobalValueByType($type, $moduleName = null)
        {
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName  = $this->getKeyByTypeAndModuleName($type);
            if (null != $pageSize = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $keyName))
            {
                return $pageSize;
            }
            return $this->{'_' . $type};
        }

        /**
         * Set the global pagination value by pagination type.
         * @param $type - pagination type
         * @param $value - integer pagination value.
         * @param $moduleName - optional. Module class name.
         */
        public function setGlobalValueByType($type, $value, $moduleName = null)
        {
            assert('in_array($type, static::getAvailablePageSizeNames()) == true');
            assert('is_int($value) && $value > 0');
            assert('$moduleName == null || is_string($moduleName)');
            $keyName  = $this->getKeyByTypeAndModuleName($type);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', $keyName, $value);
        }

        protected function getKeyByTypeAndModuleName($type, $moduleName = null)
        {
            return $type . $moduleName;
        }

        protected static function getAvailablePageSizeNames()
        {
            return array('listPageSize', 'subListPageSize', 'modalListPageSize', 'massEditProgressPageSize',
                         'autoCompleteListPageSize', 'importPageSize', 'dashboardListPageSize');
        }
    }
?>