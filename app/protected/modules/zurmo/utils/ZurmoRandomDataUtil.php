<?php
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