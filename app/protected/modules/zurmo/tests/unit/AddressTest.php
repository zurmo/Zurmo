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

    class AddressTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            //if(Yii::app()->params['testGoogleGeoCodeApiKey'] != null)
            //{
            //    set the API key.
            //}
        }

        public function testStringify()
        {
            $address = new Address();
            $this->assertEquals('(None)',
                                strval($address));
            $address->street2   = 'Apartment 4b';
            $this->assertEquals('Apartment 4b',
                                strval($address));
            $address->street1   = '123 Knob Street';
            $this->assertEquals('123 Knob Street, Apartment 4b',
                                strval($address));
            $address->postalCode = '60606';
            $this->assertEquals('123 Knob Street, Apartment 4b, 60606',
                                strval($address));
            $address->state      = 'Illinois';
            $this->assertEquals('123 Knob Street, Apartment 4b, Illinois, 60606',
                                strval($address));
            $address->city       = 'Chicago';
            $this->assertEquals('123 Knob Street, Apartment 4b, Chicago, Illinois, 60606',
                                strval($address));
            $address->country    = 'USA';
            $this->assertEquals('123 Knob Street, Apartment 4b, Chicago, Illinois, 60606, USA',
                                strval($address));
        }

        public function testAddressLatitudeAndLongitude()
        {
            $address            = new Address();
            $address->latitude  = 123.145638;
            $this->assertEquals('123.145638',
                                $address->getLatitude());
            $address->longitude = 121.176129;
            $this->assertEquals('121.176129',
                                $address->getLongitude());
        }

        public function testAddressFetchLatitudeAndLongitude()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $address = array();
            $address['street1']       = '123 Knob Street';
            $address['street2']       = 'Apartment 4b';
            $address['city']          = 'Chicago';
            $address['state']         = 'Illinois';
            $address['postalCode']    = '60606';
            $address['country']       = 'USA';
            $account1                 = AddressTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address,$super);
            $accountId1               = $account1->id;
            unset($account1);

            $address = array();
            $address['street1']       = '1600 Amphitheatre Parkway';
            $address['street2']       = '';
            $address['city']          = 'Mountain View';
            $address['state']         = 'California';
            $address['postalCode']    = '94043';
            $address['country']       = 'USA';
            $account2                 = AddressTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address,$super);
            $accountId2               = $account2->id;
            unset($account2);

            $address = array();
            $address['street1']       = '36826 East Oak Road';
            $address['street2']       = '';
            $address['city']          = 'New York';
            $address['state']         = 'NY';
            $address['postalCode']    = '10001';
            $address['country']       = 'USA';
            $account3                 = AddressTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address,$super);
            $accountId3               = $account3->id;
            unset($account3);

            $address = array();
            $address['street1']       = '24948 West Thomas Trail';
            $address['street2']       = '';
            $address['city']          = 'Milwaukee';
            $address['state']         = 'WI';
            $address['postalCode']    = '53219';
            $address['country']       = '';
            $account4                 = AddressTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address,$super);
            $accountId4               = $account4->id;
            unset($account4);

            //Check lat/long and invalid values after address creation.
            $account1 = Account::getById($accountId1);
            $this->assertEquals(null,
                                $account1->billingAddress->latitude);
            $this->assertEquals(null,
                                $account1->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);

            AddressUtil::updateChangedAddress(2);

            $account1 = Account::getById($accountId1);
            $this->assertEquals('42.1153153',
                                $account1->billingAddress->latitude);
            $this->assertEquals('-87.9763703',
                                $account1->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);

            $account2 = Account::getById($accountId2);
            $this->assertEquals('37.4211444',
                                $account2->billingAddress->latitude);
            $this->assertEquals('-122.0853032',
                                $account2->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);

            $account3 = Account::getById($accountId3);
            $this->assertEquals(null,
                                $account3->billingAddress->latitude);
            $this->assertEquals(null,
                                $account3->billingAddress->longitude);
            $this->assertEquals(0,
                                $account3->billingAddress->invalid);

            $account4 = Account::getById($accountId4);
            $this->assertEquals(null,
                                $account4->billingAddress->latitude);
            $this->assertEquals(null,
                                $account4->billingAddress->longitude);
            $this->assertEquals(0,
                                $account4->billingAddress->invalid);

            AddressUtil::updateChangedAddress(2);

            $account3 = Account::getById($accountId3);
            $this->assertEquals('40.7274969',
                                $account3->billingAddress->latitude);
            $this->assertEquals('-73.9601597',
                                $account3->billingAddress->longitude);
            $this->assertEquals(0,
                                $account3->billingAddress->invalid);

            $account4 = Account::getById($accountId4);
            $this->assertEquals('43.06132',
                                $account4->billingAddress->latitude);
            $this->assertEquals('-87.8880352',
                                $account4->billingAddress->longitude);
            $this->assertEquals(0,
                                $account4->billingAddress->invalid);

            //Check after Modifying address lat / long set to null and flag to flase.
            $account1 = Account::getById($accountId1);
            $account1->billingAddress->street1       = 'xxxxxx';
            $account1->billingAddress->city          = 'xxxxxx';
            $account1->billingAddress->state         = 'xxxxxx';
            $account1->billingAddress->postalCode    = '00000';
            $account1->billingAddress->country       = '';
            $this->assertTrue($account1->save(false));

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null,
                                $account1->billingAddress->latitude);
            $this->assertEquals(null,
                                $account1->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);

            //Test for Invalid address and set invalid flag to true.
            AddressUtil::updateChangedAddress(2);

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null,
                                $account1->billingAddress->latitude);
            $this->assertEquals(null,
                                $account1->billingAddress->longitude);
            $this->assertEquals(1,
                                $account1->billingAddress->invalid);

            $account1 = Account::getById($accountId1);
            $account1->billingAddress->street1       = '123 Knob Street';
            $account1->billingAddress->street2       = 'Apartment 4b';
            $account1->billingAddress->city          = 'Chicago';
            $account1->billingAddress->state         = 'Illinois';
            $account1->billingAddress->postalCode    = '60606';
            $account1->billingAddress->country       = 'USA';
            $this->assertTrue($account1->save());

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null,
                                $account1->billingAddress->latitude);
            $this->assertEquals(null,
                                $account1->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);

            AddressUtil::updateChangedAddress(2);

            $account1 = Account::getById($accountId1);
            $this->assertEquals('42.1153153',
                                $account1->billingAddress->latitude);
            $this->assertEquals('-87.9763703',
                                $account1->billingAddress->longitude);
            $this->assertEquals(0,
                                $account1->billingAddress->invalid);
        }
    }
?>