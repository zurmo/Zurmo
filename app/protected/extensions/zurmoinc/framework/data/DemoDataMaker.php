<?php
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
            if(strlen($name) > 15)
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
            if(!isset(self::$customFieldData[$name]))
            {
                $data = CustomFieldData::getByName('AccountTypes');
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