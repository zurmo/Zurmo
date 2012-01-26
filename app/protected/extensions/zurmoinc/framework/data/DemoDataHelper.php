<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Demo Data Helper Class
     */
    class DemoDataHelper
    {
        protected $demoData = array();

        /**
         * Get id range for model
         * @param string $modelName
         * @return array
         */
        public function getRangeByModelName($modelName)
        {
            assert('array_key_exists($modelName, $this->demoData)');

            return array('startId' => $this->demoData[$modelName]['startId'],
                         'endId'   => $this->demoData[$modelName]['endId']);
        }

        /**
         * Set id range for model
         * @param string $modelName
         * @param int $startId
         * @param int $endId
         */
        public function setRangeByModelName($modelName, $startId, $endId)
        {
            assert('is_string($modelName)');
            assert('is_int($startId)');
            assert('is_int($endId)');
            assert('!array_key_exists($modelName, $this->demoData)');
            assert('$endId > $startId');

            if (!array_key_exists($modelName, $this->demoData))
            {
                $this->demoData[$modelName]['startId'] = $startId;
                $this->demoData[$modelName]['endId']   = $endId;
            }
        }

        /**
         * Get random model (from list of model available ids)
         * @param string $modelName
         * @return object $model
         */
        public function getRandomByModelName($modelName)
        {
            assert('is_string($modelName)');
            assert('is_int($this->demoData[$modelName]["startId"])');
            assert('is_int($this->demoData[$modelName]["endId"])');
            assert('$this->demoData[$modelName]["endId"] > $this->demoData[$modelName]["startId"]');
            $randomId = mt_rand($this->demoData[$modelName]["startId"], $this->demoData[$modelName]["endId"]);
            $model = $modelName::getById($randomId);
            assert('$model instanceof $modelName');
            return $model;
        }

        /**
         * Check if range is setup for module
         * @param string $modelName
         * @return boolean $isSet
         */
        public function isSetRange($modelName)
        {
            $isSet = isset($this->demoData[$modelName]['startId']) &&
                     isset($this->demoData[$modelName]['endId'])   &&
                     $this->demoData[$modelName]['startId'] > 0    &&
                     $this->demoData[$modelName]['endId'] > 0;

            return $isSet;
        }
    }
?>