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

        protected static function getRedBeanModelClassName()
        {
            return 'Account';
        }

        public function __construct(Account $model)
        {
            parent::__construct($model);
        }

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