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
     * Class to handle special functionality for any exceptions from GeoCoding
     * NOTE: This is not compatible with PHP 5.3 exceptions. I am overriding the
     *  constructor here and using the 3rd argument as a driver rather than the
     *  previous exception
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCode_Exception extends CException
    {
        /**
         * The driver associated with this exception
         * @var GeoCode_Driver
         */
        protected $driver = null;

        /**
         * Initialize the exception, saving the driver that was passed
         *
         * @param string $message
         * @param integer $code
         * @param GeoCode_Driver $driver
         * @return GeoCode_Exception
         */
        public function __construct($message = '', $code = 0, GeoCode_Driver $driver = null)
        {
            $this->driver = $driver;
            parent::__construct($message, $code);
        }

        /**
         * Get the GeoCode error associated with this GeoCode_Exception. This function
         * simply returns the value of {@link GeoCoder::getErrorMessage()} and passes
         * the error code
         *
         * Returns N/A when no driver is set
         *
         * @return string
         */
        public function getGeoCodeMessage()
        {
            // Return the GeoCoder error message associated with this excpetion.
            // If a valid code is not found, it will just return 'Unknown'
            // If no driver is specified, it will just return 'N/A'
            return ($this->driver === null) ? 'N/A' : $this->driver->getErrorMessage($this->code);
        }

        /**
         * Set the driver to use when resolving all internal requests
         *
         * @param GeoCode_Driver $driver
         */
        public function setDriver(GeoCode_Driver $driver)
        {
            $this->driver = $driver;
        }

        /**
         * Get the driver associated with this exception
         *
         * @return GeoCode_Driver
         */
        public function getDriver()
        {
            return $this->driver;
        }
    }
?>