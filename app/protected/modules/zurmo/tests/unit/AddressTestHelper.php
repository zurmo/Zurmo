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

    class AddressTestHelper
    {
        public static function createTestAccountsWithBillingAddressAndGetAccount($address)
        {
            $account                                = new Account();
            $account->name                          = "Account";
            $account->officePhone                   = rand(10000000, 90000000);
            $account->officeFax                     = rand(10000000, 90000000);
            $account->employees                     = rand(1, 100);
            $account->website                       = "http://www.account.com";
            $account->annualRevenue                 = rand(10000, 10000000);
            $account->description                   = "An account for some company called Account.";
            $account->primaryEmail->emailAddress    = "info@account.com";
            $account->primaryEmail->optOut          = false;
            $account->primaryEmail->isInvalid       = false;
            $account->billingAddress->street1       = $address['street1'];
            $account->billingAddress->street2       = $address['street2'];
            $account->billingAddress->city          = $address['city'];
            $account->billingAddress->state         = $address['state'];
            $account->billingAddress->postalCode    = $address['postalCode'];
            $account->billingAddress->country       = $address['country'];
            $account->billingAddress->latitude      = 0.0;
            $account->billingAddress->longitude     = 0.0;
            $account->billingAddress->invalid       = false;
            $account->save();
            return $account;
        }
    }
?>