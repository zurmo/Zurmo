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
     * Maps module walkthrough tests for a regular user.
     */
    class MapsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);
            AddressGeoCodeTestHelper::createAndRemoveAccountWithAddress($super);
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            $super        = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $superAccount = AccountTestHelper::createAccountByNameForOwner('accountOwnedBySuper', $super);

            //Create address array for the account owned by super user.
            $address = array('street1'    => '123 Knob Street',
                             'street2'    => 'Apartment 4b',
                             'city'       => 'Chicago',
                             'state'      => 'Illinois',
                             'postalCode' => '60606',
                             'country'    => 'USA'
                       );

            //Assign Address to the user account.
            AddressGeoCodeTestHelper::updateTestAccountsWithBillingAddress($superAccount->id, $address, $super);

            //Fetch Latitute and Longitude values for address and save in Address.
            AddressMappingUtil::updateChangedAddresses();

            $accounts = Account::getByName('accountOwnedBySuper');
            $this->assertEquals(1, count($accounts));

            $this->assertEquals('42.1153153',  $accounts[0]->billingAddress->latitude);
            $this->assertEquals('-87.9763703', $accounts[0]->billingAddress->longitude);
            $this->assertEquals(0,             $accounts[0]->billingAddress->invalid);

            $addressString = $accounts[0]->billingAddress->makeAddress();
            $this->setGetArray(array('addressString' => $addressString,
                                     'latitude'      => $accounts[0]->billingAddress->latitude,
                                     'longitude'     => $accounts[0]->billingAddress->longitude));

            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test account details portlet controller actions
            $this->setGetArray(array('id' => $superAccount->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('accounts/default/details');

            //The map should always be available.  Not controlled by rights.
            $this->setGetArray(array('addressString' => 'anAddress String', 'latitude' => '45.00', 'longitude' => '45.00'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('maps/default/mapAndPoint');
            $this->assertFalse(strpos($content, 'Access Failure') > 0);
        }
    }
?>
