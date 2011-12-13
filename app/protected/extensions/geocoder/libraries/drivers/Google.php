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
     * Driver to handle geocoding using the google API
     *
     * An API Key is obtained from http://code.google.com/apis/maps/signup.html
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCode_Driver_Google extends GeoCode_Driver
    {
        // Docs for what google returns can be found at the following URL:
        // http://code.google.com/apis/maps/documentation/geocoding/index.html

        // xAL Address Standard (what google 'supposedly returns')
        // http://www.oasis-open.org/committees/ciq/Downloads/xNAL/xAL/Versions/xALv2_0/

        // Constants for the errors
        const ERROR_BAD_RESPONSE = 10;

        // Constants for the status values
        const STATUS_SUCCESS = 200;
        const STATUS_BAD_REQUEST = 400;
        const STATUS_SERVER_ERROR = 500;
        const STATUS_MISSING_QUERY = 601; // same as MISSING_ADDRESS
        const STATUS_UNKNOWN_ADDRESS = 602;
        const STATUS_UNAVAILABLE_ADDRESS = 603;
        const STATUS_BAD_KEY = 610;
        const STATUS_TOO_MANY_QUERIES = 620;

        // Constants for accuracy values
        const ACCURACY_UNKNOWN = 0;
        const ACCURACY_COUNTRY = 1;
        const ACCURACY_REGION = 2;
        const ACCURACY_SUB_REGION = 3;
        const ACCURACY_TOWN = 4;
        const ACCURACY_ZIP_CODE = 5;
        const ACCURACY_STREET = 6;
        const ACCURACY_INTERSECTION = 7;
        const ACCURACY_ADDRESS = 8;
        const ACCURACY_PREMISE = 9;

        /**
         * The URL for the API calls
         * @var stromg
         */
        protected $api_url = 'http://maps.google.com/maps/geo?&output=xml&key=';

        /**
         * The Google GeoCoder Parser
         * @var GeoCode_Driver_Google_Parser
         */
        protected $parser = null;

        /**
         * The variable to hold the last query string that we used
         * @var string
         */
        protected $query_str = null;

        /**
         * The variable to hold the raw_response for the last query
         * @var string
         */
        protected $raw_response = null;

        /**
         * Initialize the Google geocode driver
         */
        public function init()
        {
            // Initialize the parser
            $this->parser = new GeoCode_Driver_Google_Parser();
        }

        /**
         * Send a query to the google geocode API
         *
         * @param string $query
         * @return GeoCode_Result
         */
        public function query($query)
        {
            // Clean the query string, make sure no double spaces are there
            $this->query_str = preg_replace('/\s{2,}/', ' ', $query);

            // Generate the URL for our API query
            $url = $this->createUrl($this->query_str);

            // Run the query
            $this->raw_response = file_get_contents($url);

            try
            {
                // Parse the result
                $data = $this->parser->process($this->raw_response);

                // Initialize and return the result
                return new GeoCode_Result($this, $data);
            }
            // Catch any geocode exception
            catch (GeoCode_Exception $e)
            {
                // Set that we are the driver associated with
                //  this exception
                $e->setDriver($this);
                // Re-throw
                throw $e;
            }
        }

        /**
         * Create the URL to be used for our geocode query
         *
         * @param string $query_str
         * @return string
         */
        protected function createUrl($query_str)
        {
            // Construct the url, encoding the query string
            return $this->api_url . $this->api_key . '&q=' . urlencode($query_str);
        }

        /**
         * Get the driver name
         *
         * @return string
         */
        public function getDriverName()
        {
            return 'Google';
        }

        /**
         * Get the last query that we ran
         *
         * @return string
         */
        public function getLastQuery()
        {
            return $this->query_str;
        }

        /**
         * Get the raw response as returned by the API call
         *
         * @return string
         */
        public function getRawResponse()
        {
            return $this->raw_response;
        }

        /**
         * This method is used to translate a given status constant
         * into a human-readable and meaningful string
         *
         * @param integer $const
         * @return string
         */
        public function getStatusString($const)
        {
            $str = '';

            switch ($const)
            {
                case self::STATUS_SUCCESS:
                    $str = 'Success';
                    break;

                case self::STATUS_BAD_REQUEST:
                    $str = 'Bad Request';
                    break;

                case self::STATUS_SERVER_ERROR:
                    $str = 'Server Error';
                    break;

                // Same as MISSING_ADDRESS
                case self::STATUS_MISSING_QUERY:
                    $str = 'Missing Query';
                    break;

                case self::STATUS_UNKNOWN_ADDRESS:
                    $str = 'Unknown Address';
                    break;

                case self::STATUS_UNAVAILABLE_ADDRESS:
                    $str = 'Unavailable Address';
                    break;

                case self::STATUS_BAD_KEY:
                    $str = 'Bad API Key';
                    break;

                case self::STATUS_TOO_MANY_QUERIES:
                    $str = 'Too Many Queries';
                    break;

                default:
                    $str = 'Unknown';
                    break;
            }

            return $str;
        }

        /**
         * This method is used to translate a given accuracy constant
         * into a human-readable and meaningful string
         *
         * @param integer $const
         * @return string
         */
        public function getAccuracyString($const)
        {
            $str = '';

            switch ($const)
            {
                case self::ACCURACY_UNKNOWN:
                    $str = 'Unknown';
                    break;

                case self::ACCURACY_COUNTRY:
                    $str = 'Country';
                    break;

                case self::ACCURACY_REGION:
                    $str = 'Region';
                    break;

                case self::ACCURACY_SUB_REGION:
                    $str = 'Sub-Region';
                    break;

                case self::ACCURACY_TOWN:
                    $str = 'Town';
                    break;

                case self::ACCURACY_ZIP_CODE:
                    $str = 'Zip Code';
                    break;

                case self::ACCURACY_STREET:
                    $str = 'Street';
                    break;

                case self::ACCURACY_INTERSECTION:
                    $str = 'Intersection';
                    break;

                case self::ACCURACY_ADDRESS:
                    $str = 'Address';
                    break;

                case self::ACCURACY_PREMISE:
                    $str = 'Premise';
                    break;

                default:
                    $str = 'Unknown';
                    break;
            }

            return $str;
        }

        /**
         * This method is used to translate a given error or status constant
         * into a human-readable and meaningful error string that can be
         * displayed to the end user.
         *
         * @param integer $const
         * @return string
         */
        public function getErrorMessage($const)
        {
            $str = '';

            switch ($const)
            {
                case self::ERROR_BAD_RESPONSE:
                    $str = 'Bad Response from Server';
                    break;

                case self::STATUS_BAD_REQUEST:
                    $str = 'Bad GeoCode Request';
                    break;

                case self::STATUS_SERVER_ERROR:
                    $str = 'Server Error';
                    break;

                case self::STATUS_MISSING_QUERY:
                    $str = 'Missing Address';
                    break;

                case self::STATUS_UNKNOWN_ADDRESS:
                    $str = 'Unknown Address';
                    break;

                case self::STATUS_UNAVAILABLE_ADDRESS:
                    $str = 'Unavailable Address';
                    break;

                case self::STATUS_BAD_KEY:
                    $str = 'Bad GeoLocation Key';
                    break;

                case self::STATUS_TOO_MANY_QUERIES:
                    $str = 'Too many queries';
                    break;

                default:
                    $str = 'Unknown Error Code: '.$const;
                    break;
            }

            return $str;
        }
    }
?>