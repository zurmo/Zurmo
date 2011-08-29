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

    class PerformanceMeasurement extends CApplicationComponent
    {
        protected $startTime;

        /**
         * Can be used during development to benchmark certain areas of code for how long they take to execute.  It is
         * recommeded to use something like xdebug, although this can be helpful as well.
         * @var array
         */
        protected $timings = array();

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
            if(isset($this->timings[$identifer]))
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
    }
?>
