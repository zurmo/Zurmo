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
            $status = $xml->status;
           
            // If the goecoding was not successfull, error out
            if ($status != GeoCode_Driver_Google::STATUS_SUCCESS)
            {                
                throw new GeoCode_Exception($status);
            }

            # Process the data #

            // Get the location (always returned)            
            $lng  = $xml->result->geometry->location->lng;
            $lat  = $xml->result->geometry->location->lat;
            //list($lon,$lat) = explode(',', (string)$result->geometry->location->coordinates);

            // Initialize the return array
            $data = array(
                'accuracy' => GeoCode_Driver_Google::ACCURACY_UNKNOWN,
                'latitude' => (float)$lat,
                'longitude' => (float)$lng,
                'query' => (string)$xml->result->formatted_address,
                'clean_query' => (string)$xml->result->formatted_address,
                'state' => '',
                'city' => '',
                'zip' => '',
                'street' => '',
                'country' => ''
            );

            // Parse the addressComponent
            $this->parseAddressComponent($xml->result->address_component, $data);

            // Set the accuracy
            if(isset($xml->result->geometry->location_type))
            $data['accuracy'] = (string)$xml->result->geometry->location_type;

            // Return our data
            return $data;
        }

        /**
         * Parse the address_component as returned by google
         *
         * @param SimpleXMLObject $addressComponents
         * @param array  &$data
         * @return void
         */
        protected function parseAddressComponent($addressComponents, &$data)
        {        
            $addressParts = array( 'route','locality','administrative_area_level_2','administrative_area_level_1',
                            'country','postal_code');    
            foreach($addressComponents as $addressComponent)
            {
               if(in_array($addressComponent->type,$addressParts))
               {
                    $type = (string)$addressComponent->type;
                    $addressResolved[$type] =   $addressComponent->short_name;
               }
            }

            // Get the country name 
            if(isset($addressResolved['country']))            
            $data['country'] = (string)$addressResolved['country'];           

            // Get the city name
            if(isset($addressResolved['locality']))            
            $data['city'] = (string)$addressResolved['locality']; 
            
            
            // Get the state name
            if(isset($addressResolved['administrative_area_level_1']))            
            $data['state'] = (string)$addressResolved['administrative_area_level_1']; 
            
            // Get the zip
            if(isset($addressResolved['postal_code']))            
            $data['zip'] = (string)$addressResolved['postal_code']; 
            
            // Get the street name 
            if(isset($addressResolved['route']))            
            $data['street'] = (string)$addressResolved['route'];
 
        }
    }
?>