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

    class Address extends OwnedModel
    {
        public function __toString()
        {
            $address = $this->makeAddress();
            if ($address == '')
            {
                return Yii::t('Default', '(None)');
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

        protected static function getPluralLabel()
        {
            return 'Addresses';
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'street1',
                    'street2',
                    'city',
                    'state',
                    'postalCode',
                    // Todo: make these relations.
                    'country',
                ),
                'rules' => array(
                    array('street1',    'type', 'type' => 'string'),
                    array('street1',    'length', 'max' => 128),
                    array('street2',    'type', 'type' => 'string'),
                    array('street2',    'length', 'max' => 128),
                    array('city',       'type', 'type' => 'string'),
                    array('city',       'length', 'max' => 32),
                    array('state',      'type', 'type' => 'string'),
                    array('state',      'length', 'max' => 32),
                    array('country',    'type', 'type' => 'string'),
                    array('country',    'length', 'max' => 32),
                    array('postalCode', 'type', 'type' => 'string'),
                    array('postalCode', 'length', 'max' => 16),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>
