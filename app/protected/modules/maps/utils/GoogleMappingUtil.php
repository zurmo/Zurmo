<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * MappingUtil class to handle map rendering given the query data.
     */
    class GoogleMappingUtil
    {
        /**
         * Render map into specified container using result geocoder object.
         * @param string $apiKey           - google map api key.
         * @param array  $geoCodeQueryData - required query data in the form of array.
         * @param string $containerId      - containerid to render the map.
         */
        public static function renderMapByGeoCodeData($apiKey, $geoCodeQueryData, $containerId)
        {
            assert('$apiKey == null || is_string($apiKey)');
            assert('is_array($geoCodeQueryData)');
            assert('is_string($containerId)');
            $geoCodeResult = self::getGeoCodeResultByData($apiKey, $geoCodeQueryData);
            static::renderMapAndPoint($containerId, $apiKey, $geoCodeResult->query,
                    $geoCodeResult->latitude, $geoCodeResult->longitude);
        }

        /**
         * Get the geocode result object from the geocoder object.
         * @param string $apiKey           - google map api key.
         * @param array  $geoCodeQueryData - required query data in the form of array.
         * @return object                  - geocoder result object.
         */
        public static function getGeoCodeResultByData($apiKey, $geoCodeQueryData)
        {
            assert('$apiKey == null || is_string($apiKey)');
            assert('is_array($geoCodeQueryData)');
            Yii::import('application.extensions.geocoder.*');
            $geoCoder = new GeoCoder;
            $geoCoder->setApiKey($apiKey);
            $geoCoder->setApiDriver('Google');
            $geoCoder->init();
            $geoCodeDriver = GeoCode_Driver::factory($geoCoder->getApiDriver(), $apiKey);
            if ($geoCodeQueryData['latitude'] == null && $geoCodeQueryData['longitude'] == null)
            {
                $geoCodeResult                 = $geoCoder->query($geoCodeQueryData['query']);
                $geoCodeQueryData['latitude']  = $geoCodeResult->latitude;
                $geoCodeQueryData['longitude'] = $geoCodeResult->longitude;
                return new GeoCode_Result($geoCodeDriver, $geoCodeQueryData);
            }
            else
            {
                return new GeoCode_Result($geoCodeDriver, $geoCodeQueryData);
            }
        }

        protected static function renderMapAndPoint($containerId, $apiKey, $address, $latitude, $longitude)
        {
            assert('is_string($containerId)');
            assert('is_string($apiKey) || $apiKey == null');
            assert('is_string($address) || $address == null');
            assert('is_numeric($latitude) || $latitude == null');
            assert('is_numeric($longitude) || $longitude == null');

            $marker_text = "<strong>Location:</strong> <br />$address";
            $mapScript = "
            function plotMap()
            {
                var latlng = new google.maps.LatLng($latitude, $longitude);
                var myOptions =
                {
                    zoom: 14,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                  }
                var map = new google.maps.Map(document.getElementById('$containerId'), myOptions);

                var marker = new google.maps.Marker(
                {
                  position: latlng,
                  map: map
                });
                var infowindow = new google.maps.InfoWindow(
                {
                 content:  '$marker_text'
                });

                google.maps.event.addListener(marker, 'click', function()
                {
                    infowindow.open(map, marker);
                });
            }
            function loadGoogleMap()
            {
              var script  = document.createElement('script');
              script.type = 'text/javascript';
              if ('$apiKey' !== null)
              {
              script.src = 'http://maps.googleapis.com/maps/api/js?key=" . $apiKey . "&sensor=false&callback=plotMap';". // Not Coding Standard
              "document.body.appendChild(script);
              }
              else
              {
              script.src = 'http://maps.googleapis.com/maps/api/js?sensor=false&callback=plotMap';". // Not Coding Standard
              "document.body.appendChild(script);
              }
            }
            $(document).ready(loadGoogleMap);
            ";
            // Register the javascripts
            Yii::app()->getClientScript()->registerScript("GoogleMapScript". $containerId, $mapScript, CClientScript::POS_READY);
        }
    }
?>