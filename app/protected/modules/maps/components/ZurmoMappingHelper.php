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
     * Mapping helper class used to fetch map data and map redering
     * using mapping util and geocode util.
     */
    class ZurmoMappingHelper extends MappingHelper
    {
        /**
         * Creates the url link for map link.
         * @param array $mapRenderData - geocoder query data.
         * @return string modal map render url.
         */
        public function getMappingLinkContentForElement($mapRenderData)
        {
            assert('is_array($addressData)');
            return Yii::app()->createUrl('maps/default/renderAddressMapView/', array_merge($_GET, $mapRenderData));
        }

        /**
         * Gets the rendered map content for the map view.
         * @param array  $geoCodeQueryData - geocoder query data.
         * @param string $containerId      - containerid for map rendering.
         * @return rendered map content.
         */
        public static function renderMapContentForView($geoCodeQueryData, $containerId)
        {
            assert('is_array($geoCodeQueryData)');
            assert('is_string($containerId)');
            return GoogleMappingUtil::renderMapByGeoCodeData(self::getGeoCodeApi(), $geoCodeQueryData, $containerId);
        }

        /**
         * Register the required api javascript files.
         */
        public static function registerMapScriptFiles()
        {
            $mapScriptFiles = GoogleMappingUtil::getMapScriptFiles();
            foreach ($mapScriptFiles as $scriptFile)
            {
                Yii::app()->getClientScript()->registerScriptFile($scriptFile, CClientScript::POS_END);
            }
        }

        /**
         * Gets the geocode coordinate data for address.
         * @param string $addressString - geocoder query data.
         * @return - lat / long array.
         */
        public static function getGeoCodes($addressString)
        {
            assert('is_string($addressString)');
            return GoogleGeoCodeUtil::getLatitudeLongitude(self::getGeoCodeApi(), $addressString);
        }

        /**
         * Gets the geocode api key from the cofig table.
         * @return string $apiKey or null - geocode Api Key.
         */
        public static function getGeoCodeApi()
        {
            if (null != $apiKey = ZurmoConfigurationUtil::getByModuleName('MapsModule', 'googleMapApiKey'))
            {
                return $apiKey;
            }
            else
            {
                return null;
            }
        }
    }
?>