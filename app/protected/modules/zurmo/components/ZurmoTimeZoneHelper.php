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
     * Application loaded component at run time. @see BeginBehavior - calls load() method.
     * Defaults time zone to configuration set in common configuration 'timeZone' setting.
     */
    class ZurmoTimeZoneHelper extends CApplicationComponent
    {
        /**
         * Systemwide time zone.
         */
        protected $_timeZone;

        /**
         * This is set from the value in the application common config file. It is used as the final fall back
         * if no other configuration settings are found.
         */
        public function setTimeZone($value)
        {
            assert('is_string($value)');
            assert('new DateTimeZone($value) !== false');
            $this->_timeZone = $value;
        }

        //USE FOR TESTING ONLY.
        public function getTimeZone()
        {
            return $this->_timeZone;
        }

       /**
         * Loads time zone for current user.  This is called by BeginBehavior.
         */
        public function load()
        {
            Yii::app()->setTimeZone($this->getForCurrentUser());
        }

        /**
         * Get the time zone value for the current user
         * @return $timeZone - string.
         */
        public function getForCurrentUser()
        {
            if ( Yii::app()->user->userModel != null && Yii::app()->user->userModel->timeZone != null)
            {
                return Yii::app()->user->userModel->timeZone;
            }
            return $this->_timeZone;
        }

        /**
         * Get the global configuration value.
         * @return string - time zone.
         */
        public function getGlobalValue()
        {
            if (null != $timeZone = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'timeZone'))
            {
                return $timeZone;
            }
            return $this->_timeZone;
        }

        /**
         * Set the global time zone configuration value.
         */
        public static function setGlobalValue($timeZone)
        {
            assert('is_string($timeZone)');
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'timeZone', $timeZone);
        }

        /**
         * Given a utc time stamp, convert the time stamp to a timezone adjusted time stamp.
         * The time zone is based on the current user's time zone.
         */
        public function convertFromUtcTimeStampForCurrentUser($utcTimeStamp)
        {
            assert('is_int($utcTimeStamp)');
            $timeZone = $this->getForCurrentUser();
            return DateTimeUtil::convertFromUtcUnixStampByTimeZone($utcTimeStamp, $timeZone);
        }
    }
?>