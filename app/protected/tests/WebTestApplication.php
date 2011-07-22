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
     * Application to be used during unit testing
     */
    class WebTestApplication extends WebApplication
    {
        private $configLanguageValue;
        private $configTimeZoneValue;

        /**
         * Override for walkthrough tests. Need to store the config data so certain values can
         *  be reset to the original config value when resetting the application to run another walkthrough.
         */
        public function __construct($config = null)
        {
            parent::__construct($config);
            $this->configLanguageValue = $this->language;
            $this->configTimeZoneValue = $this->timeZoneHelper->getTimeZone();
        }

        /**
         * Override because when testing, we always want to raise the event
         * instead of only raising it once.  This is because using phpunit and
         * unit tests, it is possible we will have the application execute ->end
         * multiple times during testing.
         * Raised right AFTER the application processes the request.
         * @param CEvent $event the event parameter
         */
        public function onEndRequest($event)
        {
            $this->raiseEvent('onEndRequest', $event);
        }

        public function getConfigLanguageValue()
        {
            return $this->configLanguageValue;
        }

        public function getConfigTimeZoneValue()
        {
            return $this->configTimeZoneValue;
        }
    }
?>
