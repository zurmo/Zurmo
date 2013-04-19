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
     * Base class for module demo data that can optional be created.
     */
    abstract class DemoDataMaker
    {
        private static $customFieldData;

        /**
         * Defines the ratio of a models quantity to the load size. 1 is the baseline.  If you set the quantity to 100
         * and the ratioToLoad is 1.5, then 150 models will be created for that particular module's demo data.
         * @see $loadMagnitude
         * @var integer
         */
        protected $ratioToLoad = 1;

        /**
         * Load magnitude defines the quantity to load. If you set this 100 and the ratioToLoad is 2 for a module
         * then that module will create 200 demo data models.
         * @see $ratioToLoad;
         * @var integer
         */
        protected $loadMagnitude = 10;

        /**
         * Given an array of existing data models, make all the demo data for this module.
         * @param array $demoDataByModelClassName
         */
        abstract public function makeAll(& $demoDataByModelClassName);

        /**
         * Can be used when you want to just populate the model with random data, but not save it.
         * @param RedBeanModel $model
         */
        public function populateModel(& $model)
        {
            assert('$model instanceOf RedBeanModel');
        }

        /**
         * Returns an array of module class names. These modules must have their demo data built first.
         */
        public static function getDependencies()
        {
            return array();
        }

        public function setLoadMagnitude($loadMagnitude)
        {
            assert('is_int($loadMagnitude) && $loadMagnitude > 0');
            $this->loadMagnitude = $loadMagnitude;
        }

        protected static function makeDomainByName($name)
        {
            assert('is_string($name)');
            $name = $new_string = preg_replace('/[^a-zA-Z0-9]/', '', $name);
            if (strlen($name) > 15)
            {
                $name = substr($name, 0, 15);
            }
            return $name . '.com';
        }

        protected static function makeUrlByDomainName($domainName)
        {
            assert('is_string($domainName)');
            return 'http://www.' . $domainName;
        }

        public function getCustomFieldDataByName($name)
        {
            assert('is_string($name)');
            if (!isset(self::$customFieldData[$name]))
            {
                $data = CustomFieldData::getByName($name);
                $values = unserialize($data->serializedData);
                self::$customFieldData[$name] = $values;
            }
            return self::$customFieldData[$name];
        }

        protected function resolveQuantityToLoad()
        {
            $quantity = round($this->ratioToLoad * $this->loadMagnitude);
            assert('$quantity > 0');
            return $quantity;
        }
    }
?>