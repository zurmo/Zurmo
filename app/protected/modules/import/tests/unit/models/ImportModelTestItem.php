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

    class ImportModelTestItem extends OwnedSecurableItem
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'boolean',
                    'date',
                    'dateTime',
                    'float',
                    'integer',
                    'phone',
                    'string',
                    'textArea',
                    'url',
            ),
                'relations' => array(
                    'currencyValue'    => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED),
                    'dropDown'         => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED),
                    'hasOne'           => array(RedBeanModel::HAS_ONE,   'ImportModelTestItem2'),
                    'hasMany'          => array(RedBeanModel::MANY_MANY, 'ImportModelTestItem3'),
                    'primaryEmail'     => array(RedBeanModel::HAS_ONE,   'Email', RedBeanModel::OWNED),
                    'primaryAddress'   => array(RedBeanModel::HAS_ONE,   'Address', RedBeanModel::OWNED),

                ),
                'rules' => array(
                    array('boolean',  'boolean'),
                    array('date',     'type', 'type' => 'date'),
                    array('dateTime', 'type', 'type' => 'datetime'),
                    array('float',    'type',    'type' => 'float'),
                    array('integer',  'type',    'type' => 'integer'),
                    array('phone',    'type',    'type' => 'string'),
                    array('phone',    'length',  'min'  => 1, 'max' => 14),
                    array('string',   'required'),
                    array('string',   'type',  'type' => 'string'),
                    array('string',   'length',  'min'  => 3, 'max' => 64),
                    array('textArea', 'type',    'type' => 'string'),
                    array('url',      'url'),

                    ),
                'elements' => array(
                    'currencyValue'    => 'CurrencyValue',
                    'date'             => 'Date',
                    'DateTime'         => 'DateTime',
                    'hasOne'           => 'ImportModelTestItem2',
                    'phone'            => 'Phone',
                    'primaryEmail'     => 'EmailAddressInformation',
                    'primaryAddress'   => 'Address',
                    'textArea'         => 'TextArea',
                ),
                'customFields' => array(
                    'dropDown'   => 'ImportTestDropDown',
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
