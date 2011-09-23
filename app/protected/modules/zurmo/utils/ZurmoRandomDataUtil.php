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
     * Helper class to organize random data arrays for different models.
     */
    class ZurmoRandomDataUtil extends RandomDataUtil
    {
        /**
         * Make an Address object.
         */
        public static function makeRandomAddress()
        {
            $addressRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('ZurmoModule', 'Address');
            $streetName = RandomDataUtil::getRandomValueFromArray($addressRandomData['streetNames']);
            $direction  = RandomDataUtil::getRandomValueFromArray($addressRandomData['directions']);
            $streetSuffix = RandomDataUtil::getRandomValueFromArray($addressRandomData['streetSuffixes']);
            $cityStatePostalCodeData = RandomDataUtil::getRandomValueFromArray($addressRandomData['cityStatePostalCode']);
            $address = new Address();
            $address->street1    = mt_rand(1000, 40000) . ' ' . $direction . ' ' . $streetName . ' ' . $streetSuffix;
            $address->city       = $cityStatePostalCodeData[0];
            $address->state      = $cityStatePostalCodeData[1];
            $address->postalCode = RandomDataUtil::getRandomValueFromArray($cityStatePostalCodeData[2]);
            return $address;
        }
    }
?>