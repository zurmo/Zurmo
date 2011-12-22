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
    class AddressMappingUtil
    {
        public static function updateChangedAddress($count = 500)
        {
            assert('is_int($count)');
            $changedAddresses = self::fetchChangedAddressCollection($count);
            foreach ($changedAddresses as $address)
            {
                if($address->makeAddress() != '')
                {
                    try
                    {
                        assert('is_string($address->makeAddress())');
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

                if ($latitudeLongitudeCoordinates != null)
                {
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

        public static function fetchChangedAddressCollection($count)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'latitude',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                2 => array(
                    'attributeName'        => 'latitude',
                    'operatorType'         => 'equals',
                    'value'                => '',
                ),
                3 => array(
                    'attributeName'        => 'longitude',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                4 => array(
                    'attributeName'        => 'longitude',
                    'operatorType'         => 'equals',
                    'value'                => '',
                ),
                5 => array(
                    'attributeName'        => 'invalid',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => (bool)1,
                ),
            );

            //$searchAttributeData['structure'] = '(1 or 2) and (3 or 4) and 5';
            $searchAttributeData['structure']   = '((`address`.`latitude` is null) or 2) and ((`address`.`longitude` is null) or 4) and 5';
            $joinTablesAdapter                  = new RedBeanModelJoinTablesQueryAdapter('Address');
            $where                              = RedBeanModelDataProvider::makeWhere('Address',
                                                                                      $searchAttributeData,
                                                                                      $joinTablesAdapter);
            $addressCollection                  = Address::getSubset($joinTablesAdapter, null, $count, $where, null);
            return $addressCollection;
        }

        public static function fetchGeocodeForAddress($addressString)
        {
            assert('is_string($addressString)');
            return Yii::app()->mappingHelper->getGeoCodes($addressString);
        }
    }
?>