<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class AddressGeoCodeTest extends ZurmoBaseTest
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

        public function testAddressFetchLatitudeAndLongitude()
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
            $address['street1']    = '18367 South Oak Creek';
            $address['street2']    = '';
            $address['city']       = 'San Jose';
            $address['state']      = 'California';
            $address['postalCode'] = '95131';
            $address['country']    = 'USA';
            $account2              = AddressGeoCodeTestHelper::createTestAccountsWithBillingAddressAndGetAccount($address, $super);
            $accountId2            = $account2->id;
            unset($account2);

            $address = array();
            $address['street1']    = '9570 West Michigan Street';
            $address['street2']    = '';
            $address['city']       = 'New York';
            $address['state']      = 'NY';
            $address['postalCode'] = '10169';
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

            //Check lat/long and invalid values after address creation.
            $account1 = Account::getById($accountId1);
            $this->assertEquals(null, $account1->billingAddress->latitude);
            $this->assertEquals(null, $account1->billingAddress->longitude);
            $this->assertEquals(0,    $account1->billingAddress->invalid);
            unset($account1);

            AddressMappingUtil::updateChangedAddresses(2);

            $account1 = Account::getById($accountId1);
            $this->assertEquals(round('42.1153153', 4),  round($account1->billingAddress->latitude, 4));
            $this->assertEquals(round('-87.9763703', 4), round($account1->billingAddress->longitude, 4));
            $this->assertEquals(0,             $account1->billingAddress->invalid);
            unset($account1);

            $account2 = Account::getById($accountId2);
            $this->assertEquals(round('37.39680',   4), round($account2->billingAddress->latitude,  4));
            $this->assertEquals(round('-121.87794', 4), round($account2->billingAddress->longitude, 4));
            $this->assertEquals(0,             $account2->billingAddress->invalid);
            unset($account2);

            $account3 = Account::getById($accountId3);
            $this->assertEquals(null, $account3->billingAddress->latitude);
            $this->assertEquals(null, $account3->billingAddress->longitude);
            $this->assertEquals(0,    $account3->billingAddress->invalid);
            unset($account3);

            $account4 = Account::getById($accountId4);
            $this->assertEquals(null, $account4->billingAddress->latitude);
            $this->assertEquals(null, $account4->billingAddress->longitude);
            $this->assertEquals(0,    $account4->billingAddress->invalid);
            unset($account4);

            AddressMappingUtil::updateChangedAddresses();

            $account3 = Account::getById($accountId3);
            $this->assertNotNull($account3->billingAddress->latitude);
            $this->assertNotNull($account3->billingAddress->longitude);
            $this->assertEquals(0,           $account3->billingAddress->invalid);

            $account4 = Account::getById($accountId4);
            $this->assertNotNull($account4->billingAddress->latitude);
            $this->assertNotNull($account4->billingAddress->longitude);
            $this->assertNotEquals($account3->billingAddress->latitude, $account4->billingAddress->latitude);
            $this->assertNotEquals($account3->billingAddress->longitude, $account4->billingAddress->longitude);
            $this->assertEquals(0,             $account4->billingAddress->invalid);
            unset($account3);
            unset($account4);

            //Check after Modifying address lat / long set to null and flag to flase.
            $account1 = Account::getById($accountId1);
            $account1->billingAddress->street1    = 'xxxxxx';
            $account1->billingAddress->city       = 'xxxxxx';
            $account1->billingAddress->state      = 'xxxxxx';
            $account1->billingAddress->postalCode = '00000';
            $account1->billingAddress->country    = '';
            $this->assertTrue($account1->save(false));
            unset($account1);

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null, $account1->billingAddress->latitude);
            $this->assertEquals(null, $account1->billingAddress->longitude);
            $this->assertEquals(0,    $account1->billingAddress->invalid);
            unset($account1);

            //Test for Invalid address and set invalid flag to true.
            AddressMappingUtil::updateChangedAddresses();

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null, $account1->billingAddress->latitude);
            $this->assertEquals(null, $account1->billingAddress->longitude);
            $this->assertEquals(1,    $account1->billingAddress->invalid);
            unset($account1);

            $account1 = Account::getById($accountId1);
            $account1->billingAddress->street1    = '123 Knob Street';
            $account1->billingAddress->street2    = 'Apartment 4b';
            $account1->billingAddress->city       = 'Chicago';
            $account1->billingAddress->state      = 'Illinois';
            $account1->billingAddress->postalCode = '60606';
            $account1->billingAddress->country    = 'USA';
            $this->assertTrue($account1->save());
            unset($account1);

            $account1 = Account::getById($accountId1);
            $this->assertEquals(null, $account1->billingAddress->latitude);
            $this->assertEquals(null, $account1->billingAddress->longitude);
            $this->assertEquals(0,    $account1->billingAddress->invalid);
            $accountId1 = $account1->id;
            unset($account1);

            AddressMappingUtil::updateChangedAddresses();

            $account1 = Account::getById($accountId1);
            $this->assertEquals(round('42.1153153', 4),  round($account1->billingAddress->latitude, 4));
            $this->assertEquals(round('-87.9763703', 4), round($account1->billingAddress->longitude, 4));
            $this->assertEquals(0,             $account1->billingAddress->invalid);
        }
    }
?>
