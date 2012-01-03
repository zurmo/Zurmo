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
     * Class to handle parsing the XML result returned by the Google GeoCode API
     *
     * @package GeoCoder
     * @author Brian Armstrong <brian@barmstrongconsulting.com>
     * @copyright (C) 2009 Brian Armstrong
     * @link http://barmstrongconsulting.com/
     * @version 1.0
     */
    class GeoCode_Driver_Google_Parser
    {
        /**
         * Initialize the GeoCode Parser
         */
        public function __construct()
        {
            // If we don't have the SimpleXML extension, fail
            if (!extension_loaded('simpleXML'))
                // NOTE: Not using GeoCode_Exception because this isn't a geocode error
                throw new CException("simpleXML extension not loaded");
        }

        /**
         * Parse the XML result
         * The return array returns the following information
         *
         *      'accuracy' - the Accuracy constant as found in {@link GeoCode_Driver_Google}
         *      'latdeg'   - the latitude in degrees
         *      'londeg'   - the longitude in degrees
         *      'latrad'   - the latitude in radians
         *      'lonrad'   - the longitude in radians
         *      'query'    - the query string that was sent to goggle
         *      'clean_query' - the query as cleaned and standardized by google
         *      'state'    - the state result
         *      'city'     - the city result
         *      'zip'      - the zip result
         *      'street'   - the street result
         *      'country'  - the country result
         *
         * @param string $xml
         * @return array
         */
        public function process($xml)
        {
            // Load the XML, making sure it is all valid utf8
            $xml = @simplexml_load_string( utf8_encode($xml) );
            if ($xml === false)
            {
                // Construct and throw the exception
                $status = GeoCode_Driver_Google::ERROR_BAD_RESPONSE;
                $errMsg = GeoCode_Driver_Google::getErrorMessage($status);
                throw new GeoCode_Exception($errMsg, $status);
            }

            // Get the success status
            $response = $xml->Response;
            $status = (int)$response->Status->code;

            // If the goecoding was not successfull, error out
            if ($status != GeoCode_Driver_Google::STATUS_SUCCESS)
            {
                throw new GeoCode_Exception("GeoCode Query Failed", $status);
            }

            # Process the data #

            // Get the coordinates (always returned)
            $placemark = $response->Placemark;
            list($lon,$lat,$altitude) = explode(',', (string)$placemark->Point->coordinates);

            // Initialize the return array
            $data = array(
                'accuracy' => GeoCode_Driver_Google::ACCURACY_UNKNOWN,
                'latitude' => (float)$lat,
                'longitude' => (float)$lon,
                'query' => (string)$response->name,
                'clean_query' => (string)$placemark->address,
                'state' => '',
                'city' => '',
                'zip' => '',
                'street' => '',
                'country' => ''
            );

            // Parse the placemarker
            $this->parsePlacemark($placemark, $data);

            // Return our data
            return $data;
        }

        /**
         * Parse the xAL placemark as returned by google
         *
         * @param SimpleXMLObject $placemark
         * @param array& $data
         * @return void
         */
        protected function parsePlacemark($placemark, &$data)
        {
            // Get the accuracy of the geocoding
            $details = $placemark->AddressDetails;
            $accuracy = (int)$details->attributes()->Accuracy;

            // If we have no accuracy, just return
            if ($accuracy == GeoCode_Driver_Google::ACCURACY_UNKNOWN)
                return;

            // Set the accuracy
            $data['accuracy'] = $accuracy;

            // Get the country name
            $data['country'] = (string)$details->Country->CountryNameCode;

            // If we have an administrative area, parse it
            if (isset($details->Country->AdministrativeArea))
            {
                $this->parseAdministrativeArea($details->Country->AdministrativeArea, $data, $accuracy);
            }
        }

        /**
         * Parse the xAL administrative area as returned by google
         *
         * @param SimpleXMLObject $aa
         * @param array& $data
         * @param int $accuracy
         * @return void
         */
        protected function parseAdministrativeArea($aa, &$data, $accuracy)
        {
            // Get the state name
            $data['state'] = (string)$aa->AdministrativeAreaName;

            // If we have a locality, parse it
            if (isset($aa->Locality))
            {
                $this->parseLocality($aa->Locality, $data, $accuracy);
            }
            // If we have a subadministrative area
            elseif (isset($aa->SubAdministrativeArea))
            {
                $this->parseSubAdministrativeArea($aa->SubAdministrativeArea, $data, $accuracy);
            }
            elseif (isset($aa->DependentLocality))
            {
                $data['city'] = (string)$aa->DependentLocality->DependentLocalityName;
            }
            // If we just have an address line
            else
            {
                $data['city'] = (string)$aa->AddressLine;
            }
        }

        /**
         * Parse the xAL locality as returned by google
         *
         * @param SimpleXMLObject $loc
         * @param array& $data
         * @param int $accuracy
         * @return void
         */
        protected function parseLocality($loc, &$data, $accuracy)
        {
            $data['city'] = (string)$loc->LocalityName;

            // If we have a street, use it
            if (isset($loc->Thoroughfare))
            {
                $data['street'] = (string)$loc->Thoroughfare->ThoroughfareName;
            }

            // If we have a postal region, use it
            if (isset($loc->PostalCode))
            {
                $data['zip'] = (string)$loc->PostalCode->PostalCodeNumber;
            }
        }

        /**
         * Parse the xAL SubAdministrative Area as returned by google
         *
         * @param SimpleXMLObject $sub
         * @param array& $data
         * @param int $accuracy
         * @return void
         */
        protected function parseSubAdministrativeArea($sub, &$data, $accuracy)
        {
            // Get the county/region name
            // Note: we don't use this, so just ignore it
            $data['county'] = (string)$sub->SubAdministrativeAreaName;

            // If we have a locality, parse it
            if (isset($sub->Locality))
            {
                $this->parseLocality($sub->Locality, $data, $accuracy);
            }
            // If we have a dependent locality
            elseif (isset($sub->DependentLocality))
            {
                $data['city'] = (string)$sub->DependentLocality->DependentLocalityName;
            }
            elseif (isset($sub->AddressLine))
            {
                $data['city'] = (string)$sub->AddressLine;
            }
            // If we just have data
            else
            {
                // If we have a street, use it
                if (isset($sub->Thoroughfare))
                {
                    $data['street'] = (string)$sub->Thoroughfare->ThoroughfareName;
                }

                // If we have a postal region, use it
                if (isset($sub->PostalCode))
                {
                    $data['zip'] = (string)$sub->PostalCode->PostalCodeNumber;
                }
            }
        }
    }
?>