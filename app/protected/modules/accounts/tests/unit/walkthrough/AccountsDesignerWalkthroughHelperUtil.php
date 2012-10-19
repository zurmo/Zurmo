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
     * Helper functions to assist with testing designer walkthroughs specifically for account layouts.
     */
    class AccountsDesignerWalkthroughHelperUtil
    {
        public static function getAccountEditAndDetailsViewLayoutWithAllCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'title' => 'Panel Title',
                            'panelDetailViewOnly' => 1,
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officePhone',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'industry',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officeFax',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'employees',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'annualRevenue',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'type',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'website',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'primaryEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'secondaryEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'billingAddress',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'shippingAddress',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'detailViewOnly' => true,
                                            'element' => 'DateTimeCreatedUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'detailViewOnly' => true,
                                            'element' => 'DateTimeModifiedUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'description',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkboxCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'currencyCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dateCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'datetimeCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimalCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'picklistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integerCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'multiselectCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'tagcloudCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'calcnumberCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dropdowndepCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dropdowndep2Cstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phoneCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'radioCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textareaCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'urlCstm',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }

        public static function getAccountsSearchViewLayoutWithAllCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'title' => 'Basic Search',
                            'panelDetailViewOnly' => 1,
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkboxCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'currencyCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dateCstm__Date',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'datetimeCstm__DateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimalCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'picklistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integerCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'multiselectCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'tagcloudCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'countrylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'statelistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'citylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phoneCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'radioCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textareaCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'urlCstm',
                                        ),
                                    )
                                ),
                            ),
                        ),
                        array(
                            'title' => 'Advanced Search',
                            'panelDetailViewOnly' => 1,
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officePhone',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'industry',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officeFax',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'employees',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'annualRevenue',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'type',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'website',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdDateTime__DateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedDateTime__DateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyCity',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyStreet',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyState',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyPostalCode',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyCountry',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyInvalidEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'anyOptOutEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'description',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }

        /**
         * Can be use for listView or relatedListView.
         */
        public static function getAccountsListViewLayoutWithAllStandardAndCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officePhone',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officeFax',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'employees',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'annualRevenue',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'website',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'industry',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'type',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'description',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedDateTime',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'createdByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'modifiedByUser',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'billingAddress',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'shippingAddress',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'primaryEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'secondaryEmail',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkboxCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'currencyCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dateCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'datetimeCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimalCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'picklistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integerCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'multiselectCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'tagcloudCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'calcnumberCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'countrylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'statelistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'citylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phoneCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'radioCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textareaCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'urlCstm',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }

        public static function getAccountsMassEditViewLayoutWithAllStandardAndCustomFieldsPlaced()
        {
            return array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'name',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'owner',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officePhone',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'officeFax',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'employees',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'annualRevenue',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'website',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'industry',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'type',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'checkboxCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'currencyCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'dateCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'datetimeCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'decimalCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'picklistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'integerCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'multiselectCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'tagcloudCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'countrylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'statelistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'citylistCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'phoneCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'radioCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'textCstm',
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'element' => 'urlCstm',
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }

        /**
         * This function returns the necessary get parameters for the account search form
         * based on the account edited data.
         */
        public static function fetchAccountsSearchFormGetData()
        {
            return array(
                            'name'               => 'myEditAccount',
                            'officePhone'        => '259-734-2169',
                            'type'               => array('value'  =>  'Customer'),
                            'officeFax'          => '299-825-7863',
                            'employees'          => '630',
                            'website'            => 'http://www.UnnamedEdit.com',
                            'annualRevenue'      => '472000000',
                            'anyCity'            => 'San Jose',
                            'anyState'           => 'CA',
                            'anyStreet'          => '26378 South Arlington Ave',
                            'anyPostalCode'      => '95131',
                            'anyCountry'         => 'USA',
                            'anyEmail'           => 'info@myEditAccount.com',
                            'anyOptOutEmail'     => array('value' => null),
                            'anyInvalidEmail'    => array('value' => ''),
                            'ownedItemsOnly'     => '1',
                            'industry'           => array('value' => 'Energy'),
                            'decimalCstm'        => '12',
                            'integerCstm'        => '11',
                            'phoneCstm'          => '259-784-2069',
                            'textCstm'           => 'This is a test Edit Text',
                            'textareaCstm'       => 'This is a test Edit TextArea',
                            'urlCstm'            => 'http://wwww.abc-edit.com',
                            'checkboxCstm'       => array('value'  => '0'),
                            'currencyCstm'       => array('value'  => 40),
                            'picklistCstm'       => array('value'  => 'b'),
                            'multiselectCstm'    => array('values' => array('gg', 'hh')),
                            'tagcloudCstm'       => array('values' => array('reading', 'surfing')),
                            'countrylistCstm'    => array('value'  => 'aaaa'),
                            'statelistCstm'      => array('value'  => 'aaa1'),
                            'citylistCstm'       => array('value'  => 'ab1'),
                            'radioCstm'          => array('value'  => 'e'),
                            'dateCstm__Date'     => array('type'   => 'Today'),
                            'datetimeCstm__DateTime' => array('type'   => 'Today'));
        }

        /**
         * This function returns the necessary get parameters for the account search form
         * based on the account edited data but sets the Multi-select to null value.
         */
        public static function fetchAccountsSearchFormGetDataWithMultiSelectValueSetToNull()
        {
            return array(
                            'name'               => 'myEditAccount',
                            'officePhone'        => '259-734-2169',
                            'type'               => array('value'  =>  'Customer'),
                            'officeFax'          => '299-825-7863',
                            'employees'          => '630',
                            'website'            => 'http://www.UnnamedEdit.com',
                            'annualRevenue'      => '472000000',
                            'anyCity'            => 'San Jose',
                            'anyState'           => 'CA',
                            'anyStreet'          => '26378 South Arlington Ave',
                            'anyPostalCode'      => '95131',
                            'anyCountry'         => 'USA',
                            'anyEmail'           => 'info@myEditAccount.com',
                            'anyOptOutEmail'     => array('value' => null),
                            'anyInvalidEmail'    => array('value' => ''),
                            'ownedItemsOnly'     => '1',
                            'industry'           => array('value' => 'Energy'),
                            'decimalCstm'        => '12',
                            'integerCstm'        => '11',
                            'phoneCstm'          => '259-784-2069',
                            'textCstm'           => 'This is a test Edit Text',
                            'textareaCstm'       => 'This is a test Edit TextArea',
                            'urlCstm'            => 'http://wwww.abc-edit.com',
                            'checkboxCstm'       => array('value'  => '0'),
                            'currencyCstm'       => array('value'  => 40),
                            'picklistCstm'       => array('value'  => 'b'),
                            'multiselectCstm'    => array('values' => array(0 => null)),
                            'tagcloudCstm'       => array('values' => array('reading', 'surfing')),
                            'countrylistCstm'    => array('value'  => 'aaaa'),
                            'statelistCstm'      => array('value'  => 'aaa1'),
                            'citylistCstm'       => array('value'  => 'ab1'),
                            'radioCstm'          => array('value'  => 'e'),
                            'dateCstm__Date'     => array('type'   => 'Today'),
                            'datetimeCstm__DateTime' => array('type'   => 'Today'));
        }
    }
?>