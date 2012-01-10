<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * Util class to handle geocoding using the google api key.
     */
    class GoogleGeoCodeUtil
    {
        private static $geoCoder;

        /**
         * Get the resultset from the geocode object.
         * @param $apiKey        - google map api key.
         * @param $addressString - address string for the geocoder request.
         * @return               - array containing lat / long values.
         */
        public static function getLatitudeLongitude($apiKey, $addressString)
        {
            assert('is_string($addressString)');
            self::getGeoCoder($apiKey);
            $geoCodeGoogleCodeObj   = self::$geoCoder->query($addressString);
            $latitude               = $geoCodeGoogleCodeObj->__get('latitude');  // Not Coding Standard
            $longitude              = $geoCodeGoogleCodeObj->__get('longitude'); // Not Coding Standard
            return array('latitude' => $latitude, 'longitude' => $longitude);
        }

        /**
         * Sets the geocoder object, and sets the key and driver for api.
         * @param $apiKey        - google map api key.
         */
        private static function getGeoCoder($apiKey)
        {
            if (!isset(self::$geoCoder))
            {
                Yii::import('application.extensions.geocoder.*');
                self::$geoCoder = new GeoCoder;
                self::$geoCoder->setApiKey($apiKey);
                self::$geoCoder->setApiDriver('Google');
                self::$geoCoder->init();
            }
        }
    }
?>