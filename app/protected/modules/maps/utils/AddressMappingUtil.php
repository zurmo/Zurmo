<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Class to fetch geocode for addresses and update the latitude and longitude for the corresponding addresses.
     */
    class AddressMappingUtil
    {
        /**
         * Gets lat/long values for changed address and saves to address model.
         * @param int $count - number of changed address to update per function call.
         */
        public static function updateChangedAddresses($count = 500)
        {
            assert('is_int($count)');
            $changedAddresses = self::fetchChangedAddressCollection($count);
            foreach ($changedAddresses as $address)
            {
                if ($address->makeAddress() != '')
                {
                    assert('is_string($address->makeAddress())');
                    try
                    {
                        $latitudeLongitudeCoordinates    = self::fetchGeocodeForAddress($address->makeAddress());
                    }
                    catch (GeoCode_Exception $e)
                    {
                        $latitudeLongitudeCoordinates    = null;
                    }
                }
                else
                {
                    $latitudeLongitudeCoordinates        = null;
                }

                if ($latitudeLongitudeCoordinates != null && (!empty($latitudeLongitudeCoordinates['latitude']) && !empty($latitudeLongitudeCoordinates['longitude'])))
                {
                    assert('is_array($latitudeLongitudeCoordinates)');
                    $address->latitude     = (double)$latitudeLongitudeCoordinates['latitude'];
                    $address->longitude    = (double)$latitudeLongitudeCoordinates['longitude'];
                    $address->invalid      = false;
                }
                else
                {
                    $address->invalid      = true;
                }
                $address->unrestrictedSave(false);
            }
        }

        /**
         * Gets a subset of changed address object.
         * @param int $count - number of changed address to fetch.
         * @return object - address collection object.
         */
        public static function fetchChangedAddressCollection($count)
        {
            assert('is_int($count)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'latitude',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                2 => array(
                    'attributeName'        => 'latitude',
                    'operatorType'         => 'isEmpty',
                    'value'                => null,
                ),
                3 => array(
                    'attributeName'        => 'longitude',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                4 => array(
                    'attributeName'        => 'longitude',
                    'operatorType'         => 'isEmpty',
                    'value'                => null,
                ),
                5 => array(
                    'attributeName'        => 'invalid',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );

            $searchAttributeData['structure']   = '(1 or 2) and (3 or 4) and 5';
            $joinTablesAdapter                  = new RedBeanModelJoinTablesQueryAdapter('Address');
            $where                              = RedBeanModelDataProvider::makeWhere('Address',
                                                                                      $searchAttributeData,
                                                                                      $joinTablesAdapter);
            $addressCollection                  = Address::getSubset($joinTablesAdapter, null, $count, $where, null);
            return $addressCollection;
        }

        /**
         * Gets the lat/long coordinates for address string.
         * @param string $addressString - address string for geocode query.
         * @return array lat/long coordinates.
         */
        public static function fetchGeocodeForAddress($addressString)
        {
            assert('is_string($addressString)');
            return Yii::app()->mappingHelper->getGeoCodesByAddressString($addressString);
        }
    }
?>