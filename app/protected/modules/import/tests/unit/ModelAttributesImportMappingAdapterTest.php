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

    class ModelAttributesImportMappingAdapterTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }
        public function testGetAttributes()
        {
            $modelAttributesAdapter = new ModelAttributesImportMappingAdapter(new ImportModelTestItem(false));
            $attributesCollection   = $modelAttributesAdapter->getAttributes();
            $compareData = array(
              'id' =>
                  array (
                    'attributeLabel' => 'Id',
                    'attributeName' => 'id',
                    'relationAttributeName' => null,
                    'mappingType' => 'Id',
                    'isRequired' => false,
                  ),
              'createdDateTime' =>
                  array (
                    'attributeLabel' => 'Created Date Time',
                    'attributeName' => 'createdDateTime',
                    'relationAttributeName' => null,
                    'mappingType' => 'DateTime',
                    'isRequired' => true,
                  ),
              'modifiedDateTime' =>
                  array (
                    'attributeLabel' => 'Modified Date Time',
                    'attributeName' => 'modifiedDateTime',
                    'relationAttributeName' => null,
                    'mappingType' => 'DateTime',
                    'isRequired' => true,
                  ),
              'createdByUser' =>
                  array (
                    'attributeLabel' => 'Created By User',
                    'attributeName' => 'createdByUser',
                    'relationAttributeName' => null,
                    'mappingType' => 'User',
                    'isRequired' => false,
                  ),
              'modifiedByUser' =>
                  array (
                    'attributeLabel' => 'Modified By User',
                    'attributeName' => 'modifiedByUser',
                    'relationAttributeName' => null,
                    'mappingType' => 'User',
                    'isRequired' => false,
                  ),
              'owner' =>
                  array (
                    'attributeLabel' => 'Owner',
                    'attributeName' => 'owner',
                    'relationAttributeName' => null,
                    'mappingType' => 'User',
                    'isRequired' => true,
                  ),
             'boolean' =>
                  array (
                    'attributeLabel' => 'Boolean',
                    'attributeName' => 'boolean',
                    'relationAttributeName' => null,
                    'mappingType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'date' =>
                  array (
                    'attributeLabel' => 'Date',
                    'attributeName' => 'date',
                    'relationAttributeName' => null,
                    'mappingType' => 'Date',
                    'isRequired' => false,
                  ),
              'dateTime' =>
                  array (
                    'attributeLabel' => 'Date Time',
                    'attributeName' => 'dateTime',
                    'relationAttributeName' => null,
                    'mappingType' => 'DateTime',
                    'isRequired' => false,
                  ),
              'float' =>
                  array (
                    'attributeLabel' => 'Float',
                    'attributeName' => 'float',
                    'relationAttributeName' => null,
                    'mappingType' => 'Decimal',
                    'isRequired' => false,
                  ),
              'integer' =>
                  array (
                    'attributeLabel' => 'Integer',
                    'attributeName' => 'integer',
                    'relationAttributeName' => null,
                    'mappingType' => 'Integer',
                    'isRequired' => false,
                  ),
              'phone' =>
                  array (
                    'attributeLabel' => 'Phone',
                    'attributeName' => 'phone',
                    'relationAttributeName' => null,
                    'mappingType' => 'Phone',
                    'isRequired' => false,
                  ),
              'string' =>
                  array (
                    'attributeLabel' => 'String',
                    'attributeName' => 'string',
                    'relationAttributeName' => null,
                    'mappingType' => 'Text',
                    'isRequired' => true,
                  ),
              'textArea' =>
                  array (
                    'attributeLabel' => 'Text Area',
                    'attributeName' => 'textArea',
                    'relationAttributeName' => null,
                    'mappingType' => 'TextArea',
                    'isRequired' => false,
                  ),
              'url' =>
                  array (
                    'attributeLabel' => 'Url',
                    'attributeName' => 'url',
                    'relationAttributeName' => null,
                    'mappingType' => 'Url',
                    'isRequired' => false,
                  ),
              'currencyValue' =>
                  array (
                    'attributeLabel' => 'Currency Value',
                    'attributeName' => 'currencyValue',
                    'relationAttributeName' => null,
                    'mappingType' => 'CurrencyValue',
                    'isRequired' => false,
                  ),
              'dropDown' =>
                  array (
                    'attributeLabel' => 'Drop Down',
                    'attributeName' => 'dropDown',
                    'relationAttributeName' => null,
                    'mappingType' => 'DropDown',
                    'isRequired' => false,
                  ),
              'hasOne' =>
                  array (
                    'attributeLabel' => 'Has One',
                    'attributeName' => 'hasOne',
                    'relationAttributeName' => null,
                    'mappingType' => 'ImportModelTestItem2',
                    'isRequired' => false,
                  ),
              'primaryEmail__emailAddress' =>
                  array (
                    'attributeLabel' => 'Email Address',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'emailAddress',
                    'mappingType' => 'Email',
                    'isRequired' => false,
                  ),
              'primaryEmail__optOut' =>
                  array (
                    'attributeLabel' => 'Opt Out',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'optOut',
                    'mappingType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'primaryEmail__isInvalid' =>
                  array (
                    'attributeLabel' => 'Is Invalid',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'isInvalid',
                    'mappingType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'primaryAddress__street1' =>
                  array (
                    'attributeLabel' => 'Street 1',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'street1',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__street2' =>
                  array (
                    'attributeLabel' => 'Street 2',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'street2',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__city' =>
                  array (
                    'attributeLabel' => 'City',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'city',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__state' =>
                  array (
                    'attributeLabel' => 'State',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'state',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__postalCode' =>
                  array (
                    'attributeLabel' => 'Postal Code',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'postalCode',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__country' =>
                  array (
                    'attributeLabel' => 'Country',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'country',
                    'mappingType' => 'Text',
                    'isRequired' => false,
                  ),
            );
            $this->assertEquals($compareData, $attributesCollection);
        }
    }
?>