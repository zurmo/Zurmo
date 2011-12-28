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
     * Zurmo extended version of the GeoCode_Result class.
     *
     */
    class ZurmoGeoCodeResult extends GeoCode_Result
    {
        public function renderMapAndPoint($containerId, $apiKey)
        {
            assert('is_string($containerId)');
            assert('is_string($apiKey) || $apiKey == null');
            $mapScript = "
            function plotMap() {
                var latlng = new google.maps.LatLng($this->latitude, $this->longitude);
                var myOptions = {
                    zoom: 14,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                  }
                var map = new google.maps.Map(document.getElementById('$containerId'), myOptions);

                var marker = new google.maps.Marker({
                  position: latlng,
                  map: map
                });
            }";
            // Register the javascripts
            Yii::app()->getClientScript()->registerScriptFile('http://maps.googleapis.com/maps/api/js?key=' . $apiKey . '&sensor=false&callback=plotMap');
            Yii::app()->getClientScript()->registerScript("GoogleMapScript". $containerId, $mapScript, CClientScript::POS_READY);
        }
    }
?>