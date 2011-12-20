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
     * Class to fetch GeoCode for Addresses and update the latitude and longitude for the corresponding addresses.
     * 
     */
    class AddressUtil
    {
        public static function updateChangedAddress($count = 500)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'latitude',
                    'operatorType'         => 'isnull',
                    'value'                => null,
                ),
                2 => array(
                    'attributeName'        => 'longitude',
                    'operatorType'         => 'isnull',
                    'value'                => null,
                ),
                3 => array(
                    'attributeName'        => 'invalid',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );

            $searchAttributeData['structure'] = '1 and 2 and 3';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Address');
            $where = RedBeanModelDataProvider::makeWhere('Address', $searchAttributeData, $joinTablesAdapter);
            //$addressCollection = Address::getSubset($joinTablesAdapter, null, $count, $where, null);
            $addressCollection = Address::getSubset(null, null, $count);

            foreach ($addressCollection as $addressCollectionRow)
            {
                $address = strval($addressCollectionRow);

                try
                {
                    $latitudeLongitudeCordinates     = self::fetchGeocodeForAddress($address);
                }
                catch (GeoCode_Exception $e)
                {
                    $latitudeLongitudeCordinates     = null;
                }

                if ($latitudeLongitudeCordinates != null)
                {
                    $addressCollectionRow->latitude     = (double)$latitudeLongitudeCordinates['latitude'];
                    $addressCollectionRow->longitude    = (double)$latitudeLongitudeCordinates['longitude'];
                    $addressCollectionRow->invalid      = false;
                }
                else
                {
                    $addressCollectionRow->invalid      = true;
                }
                $addressCollectionRow->unrestrictedSave(false);
            }
        }

        public static function fetchGeocodeForAddress($address)
        {
            return ZurmoMappingHelper::getGeoCodes($address);
        }
    }
?>