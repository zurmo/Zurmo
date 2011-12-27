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
     * Driver to handle geocoding using the geocoder.us API
     *
     * If you are going to use this driver for commercial purposes, you must obtain
     * a commercial license from by signing up at http://geocoder.us/user/signup
     *
     * Use your username and password as the API key in this format 'username:password'
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCode_Driver_GeocoderDotUs extends GeoCode_Driver
    {
        // Error constants
        const ERROR_BAD_RESPONSE = 10;

        // Status constants
        const STATUS_BAD_REQUEST = 400;

        /**
         * The URL for the API calls
         * @var stromg
         */
        protected $api_url = 'geocoder.us/service/csv?';

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
         * The error message returned by the server
         * @var string
         */
        protected $error_message = null;

        /**
         * Initialize the driver
         */
        public function init()
        {
            // Nothing to do
        }

        /**
         * Send a query to the geocode API
         *
         * @param mixed $query
         * @return GeoCode_Result
         */
        public function query($query)
        {
            // Reset the error message
            $this->error_message = null;

            // Force the query to an array if it isn't already
            if (!is_array($query))
            {
                $query = array('address' => $query);
            }

            // Generate the URL for our API query
            $url = $this->createUrl($query);

            // Run the query
            $this->raw_response = file_get_contents($url);

            // Default result (error)
            $result = null;

            // Try to parse the response if valid
            if ($this->raw_response !== false)
            {
                // If we had a server-side error
                if (substr($this->raw_response, 0, 2) == '2:')
                {
                    $this->error_message = trim(substr($this->raw_response, 2));
                }
                // If we had a query error
                elseif (strpos($this->raw_response, ',') === false)
                {
                    $this->error_message = $this->raw_response;
                }
                // No errors
                else
                {
                    // Break apart the CSV
                    list($lat,$lon,$addr,$city,$state,$zip) = explode(',', trim($this->raw_response));
                    // Create the result
                    $result = new GeoCode_Result($this, array(
                        'latitude' => $lat,
                        'longitude' => $lon,
                        'query' => $query,
                        'clean_query' => "{$addr}, {$city}, {$state} {$zip}",
                        'state' => $state,
                        'city' => $city,
                        'zip' => $zip,
                        'street' => $addr,
                        'country' => 'US'
                    ));
                }
            }

            // If we don't have a result, there was an error
            if ($result === null)
            {
                // Construct and throw the exception
                $status = self::ERROR_BAD_RESPONSE;
                $errMsg = $this->getErrorMessage($status);
                throw new GeoCode_Exception($errMsg, $status, $this);
            }

            return $result;
        }

        /**
         * Create the URL to be used for our geocode query
         *
         * @param array $query
         * @return string
         */
        protected function createUrl(array $query)
        {
            $query_str = '';
            foreach ($query as $key => $value)
            {
                $value = preg_replace('/\s{2,}/', ' ', $value);
                // Clean the query string, make sure no double spaces are there
                $query_str .= urlencode($key).'='.urlencode($value).'&';
            }

            // Save the query string
            $this->query_str = trim($query_str, '&');

            $prefix = 'http://';
            // Add in the un/pw if it exists
            if (!empty($this->api_key))
            {
                $prefix .= $this->api_key.'@';
            }
            // Construct the url, encoding the query string
            return $prefix . $this->api_url . $this->query_str;// . '&parse_address=1';
        }

        /**
         * Get the name of the driver in use
         *
         * @return string
         */
        public function getDriverName()
        {
            return 'geocoder.us';
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
                case self::STATUS_BAD_REQUEST:
                    $str = 'Bad Request';
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
            // We don't have accuracy with this driver
            return 'Unknown';
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
            $msg = '';

            switch ($const)
            {
                case self::ERROR_BAD_RESPONSE:
                    $msg = 'Bad Response from Server';
                    if ($this->error_message !== null)
                        $msg .= ': '.$this->error_message;
                    break;

                case self::STATUS_BAD_REQUEST:
                    $msg = 'Bad GeoCode Request';
                    break;

                default:
                    $msg = 'Unknown Error Code: '.$const;
                    break;
            }

            return $msg;
        }
    }
?>