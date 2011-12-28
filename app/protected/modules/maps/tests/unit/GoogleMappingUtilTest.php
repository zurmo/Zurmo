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

    class GoogleMappingUtilTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            if (Yii::app()->params['testGoogleGeoCodeApiKey'] != null)
            {
                ZurmoConfigurationUtil::setByModuleName('MapsModule',
                                                        'googleMapApiKey',
                                                        Yii::app()->params['testGoogleGeoCodeApiKey']);
            }
            Yii::app()->user->userModel = $super;
            AddressGeoCodeTestHelper::createAndRemoveAccountWithAddress($super);
        }

        public function testGeoCodeResultData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $address = array();
            $address['street1']    = '123 Knob Street';
            $address['street2']    = 'Apartment 4b';
            $address['city']       = 'Chicago';
            $address['state']      = 'Illinois';
            $address['postalCode'] = '60606';
            $address['country']    = 'USA';
            $account1              = AddressGeoCodeTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address, $super);
            $accountId1            = $account1->id;
            unset($account1);

            $address = array();
            $address['street1']    = '1600 Amphitheatre Parkway';
            $address['street2']    = '';
            $address['city']       = 'Mountain View';
            $address['state']      = 'California';
            $address['postalCode'] = '94043';
            $address['country']    = 'USA';
            $account2              = AddressGeoCodeTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address, $super);
            $accountId2            = $account2->id;
            unset($account2);

            $address = array();
            $address['street1']    = '36826 East Oak Road';
            $address['street2']    = '';
            $address['city']       = 'New York';
            $address['state']      = 'NY';
            $address['postalCode'] = '10001';
            $address['country']    = 'USA';
            $account3              = AddressGeoCodeTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address, $super);
            $accountId3            = $account3->id;
            unset($account3);

            $address = array();
            $address['street1']    = '24948 West Thomas Trail';
            $address['street2']    = '';
            $address['city']       = 'Milwaukee';
            $address['state']      = 'WI';
            $address['postalCode'] = '53219';
            $address['country']    = '';
            $account4              = AddressGeoCodeTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address, $super);
            $accountId4            = $account4->id;
            unset($account4);

            AddressMappingUtil::updateChangedAddresses(2);

            $account1 = Account::getById($accountId1);
            $this->assertEquals('42.1153153' , $account1->billingAddress->latitude);
            $this->assertEquals('-87.9763703', $account1->billingAddress->longitude);
            $this->assertEquals(0,             $account1->billingAddress->invalid);

            $account2 = Account::getById($accountId2);
            $this->assertEquals('37.4211444',   $account2->billingAddress->latitude);
            $this->assertEquals('-122.0853032', $account2->billingAddress->longitude);
            $this->assertEquals(0,              $account1->billingAddress->invalid);

            $account3 = Account::getById($accountId3);
            $this->assertEquals(null, $account3->billingAddress->latitude);
            $this->assertEquals(null, $account3->billingAddress->longitude);
            $this->assertEquals(0,    $account3->billingAddress->invalid);

            $account4 = Account::getById($accountId4);
            $this->assertEquals(null, $account4->billingAddress->latitude);
            $this->assertEquals(null, $account4->billingAddress->longitude);
            $this->assertEquals(0,    $account4->billingAddress->invalid);

            $account1          = Account::getById($accountId1);
            $geoCodeQueryData1 = array('query'     => $account1->billingAddress->makeAddress(),
                                       'latitude'  => $account1->billingAddress->latitude,
                                       'longitude' => $account1->billingAddress->longitude);

            $account2          = Account::getById($accountId2);
            $geoCodeQueryData2 = array('query'     => $account2->billingAddress->makeAddress(),
                                       'latitude'  => $account2->billingAddress->latitude,
                                       'longitude' => $account2->billingAddress->longitude);

            $account3          = Account::getById($accountId3);
            $geoCodeQueryData3 = array('query'     => $account3->billingAddress->makeAddress(),
                                       'latitude'  => $account3->billingAddress->latitude,
                                       'longitude' => $account3->billingAddress->longitude);

            $account4          = Account::getById($accountId4);
            $geoCodeQueryData4 = array('query'     => $account4->billingAddress->makeAddress(),
                                       'latitude'  => $account4->billingAddress->latitude,
                                       'longitude' => $account4->billingAddress->longitude);

            $apiKey = Yii::app()->params['testGoogleGeoCodeApiKey'];

            $geoCodeResultObj1 = GoogleMappingUtil::getGeoCodeResultByData($apiKey, $geoCodeQueryData1);
            $geoCodeResultObj2 = GoogleMappingUtil::getGeoCodeResultByData($apiKey, $geoCodeQueryData2);
            $geoCodeResultObj3 = GoogleMappingUtil::getGeoCodeResultByData($apiKey, $geoCodeQueryData3);
            $geoCodeResultObj4 = GoogleMappingUtil::getGeoCodeResultByData($apiKey, $geoCodeQueryData4);

            $this->assertEquals('42.1153153',   $geoCodeResultObj1->latitude);
            $this->assertEquals('-87.9763703',  $geoCodeResultObj1->longitude);
            $this->assertEquals('37.4211444',   $geoCodeResultObj2->latitude);
            $this->assertEquals('-122.0853032', $geoCodeResultObj2->longitude);
            $this->assertEquals('40.7274969',   $geoCodeResultObj3->latitude);
            $this->assertEquals('-73.9601597',  $geoCodeResultObj3->longitude);
            $this->assertEquals('43.06132',     $geoCodeResultObj4->latitude);
            $this->assertEquals('-87.8880352',  $geoCodeResultObj4->longitude);
        }
    }
?>