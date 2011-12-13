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
     * The base class for all the GeoCode Drivers. It defines the
     * base functionality for the classes, as well as handles
     * the factory methods
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    abstract class GeoCode_Driver
    {
        /**
         * The api key for the driver
         *
         * @var string
         */
        protected $api_key = null;

        /**
         * Create a new instance of a driver
         *
         * @param string $driver
         * @param string $api_key
         * @return GeoCode_Driver
         */
        public static function factory($driver, $api_key = null)
        {
            // Rewrite the driver proper name if a dot is found
            if (strpos($driver, '.') !== false)
            {
                // Uppercase the word after each dot and replace the '.' with 'Dot'
                $driver = str_replace(' ', 'Dot', ucwords(str_replace('.', ' ', $driver)));
            }
            // Generate the class name for the driver
            $class = 'GeoCode_Driver_'.ucfirst( strtolower($driver) );

            // If we can't autoload the class, throw an exception
            if (class_exists($class, true) === false)
                throw new GeoCode_Exception("Invalid driver '{$driver}' specified");

            // Create and initialize the driver
            $driver = new $class($api_key);
            $driver->init();

            // Return the driver
            return $driver;
        }

        /**
         * Make the constructor protected to force everything to use
         * the factory method.
         *
         * @param string $api_key
         */
        protected function __construct($api_key = null)
        {
            // Set values
            $this->api_key = $api_key;
        }

        /**
         * Set the api key for the driver
         *
         * @param string $api_key
         */
        public function setApiKey($api_key)
        {
            $this->api_key = $api_key;
        }

        /**
         * Get the api key for the driver
         *
         * @return string
         */
        public function getApiKey()
        {
            return $this->api_key;
        }

        /**
         * Initialize the driver
         */
        abstract public function init();

        /**
         * Send a query to the geocode API
         *
         * @param mixed $query
         * @return GeoCode_Result
         */
        abstract public function query($query);

        /**
         * Get the name of the driver in use
         *
         * @return string
         */
        abstract public function getDriverName();

        /**
         * Get the last query that we ran
         *
         * @return string
         */
        abstract public function getLastQuery();

        /**
         * Get the raw response as returned by the API call
         *
         * @return string
         */
        abstract public function getRawResponse();

        /**
         * This method is used to translate a given status constant
         * into a human-readable and meaningful string
         *
         * @param integer $const
         * @return string
         */
        abstract public function getStatusString($const);

        /**
         * This method is used to translate a given accuracy constant
         * into a human-readable and meaningful string
         *
         * @param integer $const
         * @return string
         */
        abstract public function getAccuracyString($const);

        /**
         * This method is used to translate a given error or status constant
         * into a human-readable and meaningful error string that can be
         * displayed to the end user.
         *
         * @param integer $const
         * @return string
         */
        abstract public function getErrorMessage($const);
    }
?>