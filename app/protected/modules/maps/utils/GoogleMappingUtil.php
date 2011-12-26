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
     * MappingUtil class to handle map rendering given the query data.
     */
    class GoogleMappingUtil
    {
        /**
         * Render map into specified container using result geocoder object.
         * @param $apiKey           - google map api key.
         * @param $geoCodeQueryData - required query data in the form of array.
         */
        public static function renderMapByGeoCodeData($apiKey, $geoCodeQueryData)
        {
            assert('$apiKey == null || is_string($apiKey)');
            assert('is_array($geoCodeQueryData)');
            $geoCodeResult = self::getGeoCodeResultData($apiKey, $geoCodeQueryData);
            $geoCodeResult->renderMap('AddressGoogleMapModalView');
        }

        /**
         * Get the geocode result object from the geocoder object.
         * @param $apiKey           - google map api key.
         * @param $geoCodeQueryData - required query data in the form of array.
         * @return                  - geocoder result object.
         */
        public static function getGeoCodeResultData($apiKey, $geoCodeQueryData)
        {
            assert('$apiKey == null || is_string($apiKey)');
            assert('is_array($geoCodeQueryData)');
            Yii::import('application.extensions.geocoder.*');
            $geoCoder = new GeoCoder;
            $geoCoder->setApiKey($apiKey);
            $geoCoder->setApiDriver('Google');
            $geoCoder->init();
            if ($geoCodeQueryData['latitude'] == '' && $geoCodeQueryData['longitude'] == '')
            {
                return $geoCoder->query($geoCodeQueryData['query']);
            }
            else
            {
                $geoCodeDriver = GeoCode_Driver::factory($geoCoder->getApiDriver(), '');
                return new GeoCode_Result($geoCodeDriver, $geoCodeQueryData);
            }
        }

        public static function getMapScriptFiles()
        {
            return array('http://maps.google.com/maps?file = api&v = 2&sensor = false');
        }
    }
?>