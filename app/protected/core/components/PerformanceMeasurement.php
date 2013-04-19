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

    class PerformanceMeasurement extends CApplicationComponent
    {
        protected $startTime;

        protected $memoryUsage;

        /**
         * Can be used during development to benchmark certain areas of code for how long they take to execute.  It is
         * recommeded to use something like xdebug, although this can be helpful as well.
         * @var array
         */
        protected $timings = array();

        /**
         * Instance of Zurmo_RedBean_Plugin_QueryLogger.
         * @var Zurmo_RedBean_Plugin_QueryLogger
         */
        protected $redBeanQueryLogger;

        public function startClock()
        {
            $this->startTime = microtime(true);
        }

        public function endClockAndGet()
        {
            $endTime = microtime(true);
            return $endTime - $this->startTime;
        }

        /**
         * Given a time in seconds and an indentifier, add the time to the existing timings array data. This will add to
         * the existing value.
         * @param string $identifer
         * @param number $time
         */
        public function addTimingById($identifer, $time)
        {
            if (isset($this->timings[$identifer]))
            {
                $this->timings[$identifer] = $this->timings[$identifer] + $time;
            }
            else
            {
                $this->timings[$identifer] = $time;
            }
        }

        /**
         * @return array of timings data.
         */
        public function getTimings()
        {
            return $this->timings;
        }

        /**
         * Set the query logger during the RedBeanDatabase setup.  This can then be used after the page is rendered
         * to gather information about query counts, duplicate queries, and other query information.
         * @param Zurmo_RedBean_Plugin_QueryLogger $queryLogger
         */
        public function setRedBeanQueryLogger(ZurmoRedBeanPluginQueryLogger $redBeanQueryLogger)
        {
            $this->redBeanQueryLogger =  $redBeanQueryLogger;
        }

        public function getRedBeanQueryLogger()
        {
            return $this->redBeanQueryLogger;
        }

        /**
         * Start a capture of how much memory is being used.
         */
        public function startMemoryUsageMarker()
        {
            $this->memoryUsage = memory_get_usage();
        }

        /**
         * @returns integer - the difference in memory used at the start part until now.  This is the differential amount.
         */
        public function getMemoryMarkerUsage()
        {
            return memory_get_usage() - $this->memoryUsage;
        }

        /**
         * @return integer - Memory usage
         */
        public function getMemoryUsage()
        {
            return memory_get_usage();
        }
    }
?>
