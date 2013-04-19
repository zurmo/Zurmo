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
        const STATUS_SUCCESS = 'OK';
        const STATUS_ZERO_RESULTS = 'ZERO_RESULTS';
        const STATUS_REQUEST_DENIED = 'REQUEST_DENIED';
        const STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR '; // same as MISSING_ADDRESS
        const STATUS_INVALID_REQUEST = 'INVALID_REQUEST';
        const STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';

        // Constants for accuracy values
        const ACCURACY_ROOFTOP = 'ROOFTOP';
        const ACCURACY_RANGE_INTERPOLATED = 'RANGE_INTERPOLATED';
        const ACCURACY_GEOMETRIC_CENTER = 'GEOMETRIC_CENTER';
        const ACCURACY_APPROXIMATE = 'APPROXIMATE';
        const ACCURACY_UNKNOWN = 'UNKNOWN';

        /**
         * The URL for the API calls
         * @var stromg
         */
        protected $api_url = 'http://maps.googleapis.com/maps/api/geocode/xml';       

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
            return $this->api_url . '?address=' . urlencode($query_str) . '&sensor=false';;
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
        }           
    }
?>