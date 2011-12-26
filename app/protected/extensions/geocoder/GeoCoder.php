<?php
    /**
     * Copyright (c) 2009 Brian Armstrong
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in
     * all copies or substantial portions of the Software.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
     * THE SOFTWARE.
     */

    /**
     * Class to handle GeoCode requests
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCoder extends CApplicationComponent
    {
        /**
         * The name of the API driver to use for this GeoCoder
         * Available Drivers: google,yahoo
         * @var string
         */
        protected $api_driver = null;

        /**
         * The API key to use for our queries
         * See your driver for more information about obtaining an API key
         * @var string
         */
        protected $api_key = null;

        /**
         * The variable to hold the last query string that we used
         * @var string
         */
        protected $query_str = null;

        /**
         * The variable to hold the instance of our GeoCode_Parser
         * @var GeoCode_Driver
         */
        private $_driver = null;

        /**
         * Handle the autoloading functionality for this extension
         * @param mixed $class_name
         */
        public static function autoload($class_name)
        {
            if (class_exists($class_name, false))
                return true;

            // If our class starts with GeoCode_, it is ours
            if (preg_match('/^geocode_/i', $class_name))
            {
                if ($path = self::getClassPath($class_name))
                {
                    // Include the file
                    require_once($path);
                    return true;
                }
            }

            return false;
        }

        /**
         * Get the path to a class file inside our system
         * @param string $class
         * @return mixed the path or FALSE on failure
         */
        protected static function getClassPath($class)
        {
            // Variable to store our path string
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR;

            // Split out the parts
            $parts = explode('_', strtolower($class));

            // Get the file part
            $file = array_pop($parts);

            // Process each part of the string for it's directory equivalent
            foreach ($parts as $part)
            {
                // Custom handling. This is for special cases
                //  that would make good looking class names
                //  line up with a good looking direcotry structure.
                switch ($part)
                {
                    case 'geocode':
                        $part = 'libraries';
                        break;

                    case 'driver':
                        $part = 'drivers';
                        break;
                }

                // Append the path
                $path .= $part . DIRECTORY_SEPARATOR;
            }

            // Append the file name and extension
            $path .= ucfirst($file) . '.php';

            // Return the path
            return (file_exists($path)) ? $path : false;
        }

        /**
         * Initialize the plugin
         */
        public function init()
        {
            // Only init once
            if (!$this->getIsInitialized())
            {
                // Register the autoloader
                Yii::registerAutoloader(array('GeoCoder', 'autoload'));
                // Initialize the driver
                $this->setDriver($this->api_driver);

                // Cascade
                parent::init();
            }
        }

        /**
         * Send a query to the geocode API
         * See {@link GeoCode_Parser::process} for more information on the return value
         * @param mixed $query
         * @return GeoCode_Result
         */
        public function query($query)
        {
            // If we don't have a valid driver, exit
            if ($this->_driver === null)
                throw new GeoCode_Exception("No GeoCode Driver Specified");

            return $this->_driver->query($query);
        }

        /**
         * Change the api driver string
         * @param string $driver
         */
        public function setApiDriver($driver)
        {
            // If we are initialized, set the driver as well
            if ($this->getIsInitialized())
            {
                $this->setDriver($driver);
            }
            // Otherwise, just set the string that will be used
            //   when we initialize the component
            else
            {
                $this->api_driver = $driver;
            }
        }

        /**
         * Get the api driver string
         * @return string
         */
        public function getApiDriver()
        {
            return $this->api_driver;
        }

        /**
         * Set the API key for this system
         * @param mixed $key
         */
        public function setApiKey($key)
        {
            $this->api_key = $key;
            // Save it to the driver as well
            if ($this->_driver !== null)
                $this->_driver->setApiKey($this->api_key);
        }

        /**
         * Get the api key
         * @return string
         */
        public function getApiKey()
        {
            return $this->api_key;
        }

        /**
         * Set the driver for the GeoCode requests
         * @param string $driver
         * @return boolean
         */
        public function setDriver($driver)
        {
            // Lowercase for comparisons
            $driver = strtolower($driver);

            // If this driver is the same as the current driver, skip
            if ($this->_driver !== null && $this->api_driver == $driver)
                return true;

            // Make sure the driver exists
            if (!empty($driver))
            {
                // Load the new driver
                $this->_driver = GeoCode_Driver::factory($driver, $this->api_key);

                // Save the driver string
                $this->api_driver = $driver;
                return true;
            }
            return false;
        }

        /**
         * Magic call method. If the method is not accessible in the class,
         * then we check if it exists on the driver.
         * @param string $name
         * @param array $parameters
         * @return mixed
         */
        public function __call($name, $parameters)
        {
            if (method_exists($this->_driver, $name))
            {
                return call_user_func_array(array($this->_driver, $name), $parameters);
            }
            parent::__call($name, $parameters);
        }

        /**
         * Magic get method. If a get<Name> method exists, then it is called.
         * Otherwise, sent to parent
         * @param string $name
         * @return mixed
         */
        public function __get($name)
        {
            $name = str_replace('_', '', $name);
            return parent::__get($name);
        }

        /**
         * Magic set method. If a set<Name> method exists, then it is called.
         * Otherwise, pass to the parent
         * @param string $name
         * @param mixed $value
         */
        public function __set($name, $value)
        {
            $name = str_replace('_', '', $name);
            parent::__set($name, $value);
        }

        /**
         * Magic isset function. Checks if an attribute is set
         * @param string $name
         * @return boolean
         */
        public function __isset($name)
        {
            $name = str_replace('_', '', $name);
            return parent::__isset($name);
        }

        /**
         * Magic unset function. Removes a given attribute
         * @param string $name
         */
        public function __unset($name)
        {
            $name = str_replace('_', '', $name);
            parent::__unset($name);
        }
    }
?>