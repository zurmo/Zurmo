<?php
    /**
     * Base class for module demo data that can optional be created.
     */
    abstract class DemoDataMaker
    {
        private static $customFieldData;
        /**
         * Defines how many of a particular model to make.
         * @var integer
         */
        abstract protected $quantity;

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
        public function getDependencies()
        {
            return array();
        }

        public function setQuantity($quantity)
        {
            assert('is_int($quantity)');
            $this->quantity = $quantity;
        }

        protected static function resolveModelAttributeValue(& $model, $attributeName, $value)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($attributeName)');
            assert('$value != null');
            if($model->$attributeName == null)
            {
                $model->$attributeName = $value;
            }
        }

        protected static function makeDomainByName($name)
        {
            assert('is_string($name)');
            $name = $new_string = preg_replace('/[^a-zA-Z0-9]/', '', $name);
            if(strlen($name) > 15)
            {
                $name = substr($name, 0, 15);
            }
            return $name . 'com';
        }

        protected static function makeUrlByDomainName($domainName)
        {
            assert('is_string($domainName)');
            return 'http://' . $domainName;
        }

        public function getCustomFieldDataByName($name)
        {
            assert('is_string($name)');
            if(self::$customFieldData[$name] == null)
            {
                $data = CustomFieldData::getByName('AccountTypes');
                $values = unserializeserialize($data->serializedData);
                self::$customFieldData[$name] = $values;
            }
            return $customFieldData[$name];
        }
    }
?>