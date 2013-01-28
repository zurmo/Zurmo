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
    * Test model for API: ApiTestModelItem
    */
    class ApiTestModelItem extends OwnedSecurableItem
    {
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('firstName', $name);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
                    'fullName' => 'Name',
                )
            );
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'firstName',
                    'lastName',
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
                    'apiTestModelItem'  => array(RedBeanModel::HAS_MANY_BELONGS_TO,  'ApiTestModelItem'),
                    'modelItems'        => array(RedBeanModel::HAS_MANY,             'ApiTestModelItem'),
                    'currencyValue'     => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED),
                    'dropDown'          => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED),
                    'radioDropDown'     => array(RedBeanModel::HAS_ONE,   'OwnedCustomField', RedBeanModel::OWNED),
                    'multiDropDown'     => array(RedBeanModel::HAS_ONE,   'OwnedMultipleValuesCustomField', RedBeanModel::OWNED),
                    'tagCloud'          => array(RedBeanModel::HAS_ONE,   'OwnedMultipleValuesCustomField', RedBeanModel::OWNED),
                    'modelItem2'        => array(RedBeanModel::HAS_ONE,   'ApiTestModelItem2'),
                    'modelItems3'       => array(RedBeanModel::MANY_MANY, 'ApiTestModelItem3'),
                    'modelItems4'       => array(RedBeanModel::HAS_MANY,  'ApiTestModelItem4'),
                    'primaryEmail'      => array(RedBeanModel::HAS_ONE,   'Email', RedBeanModel::OWNED),
                    'primaryAddress'    => array(RedBeanModel::HAS_ONE,   'Address', RedBeanModel::OWNED),
                    'secondaryEmail'    => array(RedBeanModel::HAS_ONE,   'Email', RedBeanModel::OWNED),
                ),
                'rules' => array(
                    array('firstName', 'type',   'type' => 'string'),
                    array('firstName', 'length', 'min'  => 1, 'max' => 32),
                    array('lastName',  'required'),
                    array('lastName',  'type',   'type' => 'string'),
                    array('lastName',  'length', 'min'  => 2, 'max' => 32),
                    array('boolean',   'boolean'),
                    array('date',      'type', 'type' => 'date'),
                    array('dateTime',  'type', 'type' => 'datetime'),
                    array('float',     'type',    'type' => 'float'),
                    array('integer',   'type',    'type' => 'integer'),
                    array('phone',     'type',    'type' => 'string'),
                    array('phone',     'length',  'min'  => 1, 'max' => 14),
                    array('string',    'required'),
                    array('string',    'type',  'type' => 'string'),
                    array('string',    'length',  'min'  => 3, 'max' => 64),
                    array('textArea',  'type',    'type' => 'string'),
                    array('url',       'url'),
                ),
                'elements' => array(
                    'apiTestModelItem' => 'ApiModelTestItem',
                    'currencyValue'    => 'CurrencyValue',
                    'date'             => 'Date',
                    'dateTime'         => 'DateTime',
                    'modelItem2'       => 'ApiModelTestItem2',
                    'phone'            => 'Phone',
                    'primaryEmail'     => 'EmailAddressInformation',
                    'secondaryEmail'   => 'EmailAddressInformation',
                    'primaryAddress'   => 'Address',
                    'textArea'         => 'TextArea',
                    'radioDropDown'    => 'RadioDropDown',
                    'multiDropDown'    => 'MultiSelectDropDown',
                    'tagCloud'         => 'TagCloud',
                ),
                'customFields' => array(
                    'dropDown'        => 'ApiTestDropDown',
                    'radioDropDown'   => 'ApiTestRadioDropDown',
                    'multiDropDown'   => 'ApiTestMultiDropDown',
                    'tagCloud'        => 'ApiTestTagCloud',
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
