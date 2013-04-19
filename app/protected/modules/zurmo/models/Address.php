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

    class Address extends OwnedModel
    {
        public function __toString()
        {
            $address = $this->makeAddress();
            if ($address == '')
            {
                return Zurmo::t('Core', '(None)');
            }
            return $address;
        }

        public function makeAddress()
        {
            $address = array();
            if ($this->street1 != '')
            {
                $address[] = $this->street1;
            }
            if ($this->street2 != '')
            {
                $address[] = $this->street2;
            }
            if ($this->city != '')
            {
                $address[] = $this->city;
            }
            if ($this->state != '')
            {
                $address[] = $this->state;
            }
            if ($this->postalCode != '')
            {
                $address[] = $this->postalCode;
            }
            if ($this->country != '')
            {
                $address[] = $this->country;
            }
            return join(', ' , $address);
        }

        public function getLatitude()
        {
            return $this->latitude;
        }

        public function getLongitude()
        {
            return $this->longitude;
        }

        public function getInvalid()
        {
            return $this->invalid;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Address', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Addresses', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'city',
                    'country',
                    'invalid',
                    'latitude',
                    'longitude',
                    'postalCode',
                    'street1',
                    'street2',
                    'state',
                ),
                'rules' => array(
                    array('city',       'type',      'type'      => 'string'),
                    array('city',       'length',    'max'       => 32),
                    array('country',    'type',      'type'      => 'string'),
                    array('country',    'length',    'max'       => 32),
                    array('invalid',    'boolean'),
                    array('latitude',   'type',      'type'      => 'float'),
                    array('latitude',   'length',    'max'       => 11),
                    array('latitude',   'numerical', 'precision' => 7),
                    array('longitude',  'type',      'type'      => 'float'),
                    array('longitude',  'length',    'max'       => 11),
                    array('longitude',  'numerical', 'precision' => 7),
                    array('postalCode', 'type',      'type'      => 'string'),
                    array('postalCode', 'length',    'max'       => 16),
                    array('street1',    'type',      'type'      => 'string'),
                    array('street1',    'length',    'max'       => 128),
                    array('street2',    'type',      'type'      => 'string'),
                    array('street2',    'length',    'max'       => 128),
                    array('state',      'type',      'type'      => 'string'),
                    array('state',      'length',    'max'       => 32),
                ),
            );
            return $metadata;
        }

        /**
         * Address model when edited and saved beforeSave method is called
         * before saving the changes to database to check if specific address
         * fields have changed.If the address is changed we set lat/long to
         * null and invalid flag to false and then saved else saved directly.
         * in this way we can figure out which address were modified.
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                $isAddressChanged   = false;
                $addressCheckFields = array('street1', 'street2', 'city', 'state', 'country', 'postalCode');
                foreach ($addressCheckFields as $addressField)
                {
                    if (array_key_exists($addressField, $this->originalAttributeValues))
                    {
                        if ($this->$addressField != $this->originalAttributeValues[$addressField])
                        {
                            $isAddressChanged = true;
                            break;
                        }
                    }
                }

                if ($isAddressChanged && ($this->getScenario() !== 'autoBuildDatabase'))
                {
                    $this->latitude     = null;
                    $this->longitude    = null;
                    $this->invalid      = false;
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'ZurmoModule';
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'city'        => Zurmo::t('ZurmoModule', 'City',         array(), null, $language),
                    'country'     => Zurmo::t('ZurmoModule', 'Country',      array(), null, $language),
                    'invalid'     => Zurmo::t('ZurmoModule', 'Invalid',      array(), null, $language),
                    'latitude'    => Zurmo::t('ZurmoModule', 'Latitude',     array(), null, $language),
                    'longitude'   => Zurmo::t('ZurmoModule', 'Longitude',    array(), null, $language),
                    'postalCode'  => Zurmo::t('ZurmoModule', 'Postal Code',  array(), null, $language),
                    'street1'     => Zurmo::t('ZurmoModule', 'Street 1',     array(), null, $language),
                    'street2'     => Zurmo::t('ZurmoModule', 'Street 2',     array(), null, $language),
                    'state'       => Zurmo::t('ZurmoModule', 'State',        array(), null, $language),
                )
            );
        }
    }
?>
