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

    class AccountsSearchForm extends OwnedSearchForm
    {
        public $anyCity;
        public $anyStreet;
        public $anyState;
        public $anyPostalCode;
        public $anyCountry;
        public $anyEmail;
        public $anyInvalidEmail;
        public $anyOptOutEmail;

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('anyCity', 'safe'),
                array('anyStreet', 'safe'),
                array('anyState', 'safe'),
                array('anyPostalCode', 'safe'),
                array('anyCountry', 'safe'),
                array('anyEmail', 'safe'),
                array('anyInvalidEmail', 'boolean'),
                array('anyOptOutEmail', 'boolean'),
            ));
        }

        public function attributeLabels()
        {
            return array_merge(parent::attributeLabels(), array(
                'anyCity'            => Zurmo::t('AccountsModule', 'Any City'),
                'anyStreet'          => Zurmo::t('AccountsModule', 'Any Street'),
                'anyState'           => Zurmo::t('AccountsModule', 'Any State'),
                'anyPostalCode'      => Zurmo::t('AccountsModule', 'Any Postal Code'),
                'anyCountry'         => Zurmo::t('AccountsModule', 'Any Country'),
                'anyEmail'           => Zurmo::t('AccountsModule', 'Any Email Address'),
                'anyInvalidEmail'    => Zurmo::t('AccountsModule', 'Any Invalid Email'),
                'anyOptOutEmail'     => Zurmo::t('AccountsModule', 'Any Opted Out Email'),
            ));
        }

        public function getAttributesMappedToRealAttributesMetadata()
        {
            return array_merge(parent::getAttributesMappedToRealAttributesMetadata(), array(
                'anyCity' => array(
                    array('billingAddress',  'city'),
                    array('shippingAddress', 'city'),
                ),
                'anyStreet' => array(
                    array('billingAddress',  'street1'),
                    array('shippingAddress', 'street1'),
                ),
                'anyState' => array(
                    array('billingAddress',  'state'),
                    array('shippingAddress', 'state'),
                ),
                'anyPostalCode' => array(
                    array('billingAddress',  'postalCode'),
                    array('shippingAddress', 'postalCode'),
                ),
                'anyCountry' => array(
                    array('billingAddress',  'country'),
                    array('shippingAddress', 'country'),
                ),
                'anyEmail' => array(
                    array('primaryEmail',   'emailAddress'),
                    array('secondaryEmail', 'emailAddress'),
                ),
                'anyInvalidEmail' => array(
                    array('primaryEmail',   'isInvalid'),
                    array('secondaryEmail', 'isInvalid'),
                ),
                'anyOptOutEmail' => array(
                    array('primaryEmail',   'optOut'),
                    array('secondaryEmail', 'optOut'),
                ),
            ));
        }
    }
?>