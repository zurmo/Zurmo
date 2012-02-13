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

    class ModelAttributesImportMappingAdapterTest extends ImportBaseTest
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
                    'attributeImportRulesType' => 'Id',
                    'isRequired' => false,
                  ),
              'createdDateTime' =>
                  array (
                    'attributeLabel' => 'Created Date Time',
                    'attributeName' => 'createdDateTime',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'CreatedDateTime',
                    'isRequired' => true,
                  ),
              'modifiedDateTime' =>
                  array (
                    'attributeLabel' => 'Modified Date Time',
                    'attributeName' => 'modifiedDateTime',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'ModifiedDateTime',
                    'isRequired' => true,
                  ),
              'createdByUser' =>
                  array (
                    'attributeLabel' => 'Created By User',
                    'attributeName' => 'createdByUser',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'CreatedByUser',
                    'isRequired' => false,
                  ),
              'firstName' =>
                  array (
                    'attributeLabel' => 'First Name',
                    'attributeName' => 'firstName',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'lastName' =>
                  array (
                    'attributeLabel' => 'Last Name',
                    'attributeName' => 'lastName',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => true,
                  ),
              'modifiedByUser' =>
                  array (
                    'attributeLabel' => 'Modified By User',
                    'attributeName' => 'modifiedByUser',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'ModifiedByUser',
                    'isRequired' => false,
                  ),
              'owner' =>
                  array (
                    'attributeLabel' => 'Owner',
                    'attributeName' => 'owner',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'User',
                    'isRequired' => true,
                  ),
             'boolean' =>
                  array (
                    'attributeLabel' => 'Boolean',
                    'attributeName' => 'boolean',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'date' =>
                  array (
                    'attributeLabel' => 'Date',
                    'attributeName' => 'date',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Date',
                    'isRequired' => false,
                  ),
              'dateTime' =>
                  array (
                    'attributeLabel' => 'Date Time',
                    'attributeName' => 'dateTime',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'DateTime',
                    'isRequired' => false,
                  ),
              'float' =>
                  array (
                    'attributeLabel' => 'Float',
                    'attributeName' => 'float',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Decimal',
                    'isRequired' => false,
                  ),
              'integer' =>
                  array (
                    'attributeLabel' => 'Integer',
                    'attributeName' => 'integer',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Integer',
                    'isRequired' => false,
                  ),
              'phone' =>
                  array (
                    'attributeLabel' => 'Phone',
                    'attributeName' => 'phone',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Phone',
                    'isRequired' => false,
                  ),
              'string' =>
                  array (
                    'attributeLabel' => 'String',
                    'attributeName' => 'string',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => true,
                  ),
              'tagCloud' =>
                  array (
                    'attributeLabel' => 'Tag Cloud',
                    'attributeName' => 'tagCloud',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'TagCloud',
                    'isRequired' => false,
                  ),
              'textArea' =>
                  array (
                    'attributeLabel' => 'Text Area',
                    'attributeName' => 'textArea',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'TextArea',
                    'isRequired' => false,
                  ),
              'url' =>
                  array (
                    'attributeLabel' => 'Url',
                    'attributeName' => 'url',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'Url',
                    'isRequired' => false,
                  ),
              'currencyValue' =>
                  array (
                    'attributeLabel' => 'Currency Value',
                    'attributeName' => 'currencyValue',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'CurrencyValue',
                    'isRequired' => false,
                  ),
              'dropDown' =>
                  array (
                    'attributeLabel' => 'Drop Down',
                    'attributeName' => 'dropDown',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'DropDown',
                    'isRequired' => false,
                  ),
              'multiDropDown' =>
                  array (
                    'attributeLabel' => 'Multi Drop Down',
                    'attributeName' => 'multiDropDown',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'MultiSelectDropDown',
                    'isRequired' => false,
                  ),
              'radioDropDown' =>
                  array (
                    'attributeLabel' => 'Radio Drop Down',
                    'attributeName' => 'radioDropDown',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'RadioDropDown',
                    'isRequired' => false,
                  ),
              'hasOne' =>
                  array (
                    'attributeLabel' => 'Has One',
                    'attributeName' => 'hasOne',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'ImportModelTestItem2',
                    'isRequired' => false,
                  ),
              'hasOneAlso' =>
                  array (
                    'attributeLabel' => 'Has One Also',
                    'attributeName' => 'hasOneAlso',
                    'relationAttributeName' => null,
                    'attributeImportRulesType' => 'ImportModelTestItem4',
                    'isRequired' => false,
                  ),
              'primaryEmail__emailAddress' =>
                  array (
                    'attributeLabel' => 'Primary Email - Email Address',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'emailAddress',
                    'attributeImportRulesType' => 'Email',
                    'isRequired' => false,
                  ),
              'primaryEmail__optOut' =>
                  array (
                    'attributeLabel' => 'Primary Email - Opt Out',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'optOut',
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'primaryEmail__isInvalid' =>
                  array (
                    'attributeLabel' => 'Primary Email - Is Invalid',
                    'attributeName' => 'primaryEmail',
                    'relationAttributeName' => 'isInvalid',
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'primaryAddress__street1' =>
                  array (
                    'attributeLabel' => 'Primary Address - Street 1',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'street1',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__street2' =>
                  array (
                    'attributeLabel' => 'Primary Address - Street 2',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'street2',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__city' =>
                  array (
                    'attributeLabel' => 'Primary Address - City',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'city',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__state' =>
                  array (
                    'attributeLabel' => 'Primary Address - State',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'state',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__postalCode' =>
                  array (
                    'attributeLabel' => 'Primary Address - Postal Code',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'postalCode',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__country' =>
                  array (
                    'attributeLabel' => 'Primary Address - Country',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'country',
                    'attributeImportRulesType' => 'Text',
                    'isRequired' => false,
                  ),
              'primaryAddress__invalid' =>
                  array (
                    'attributeLabel' => 'Primary Address - Invalid',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'invalid',
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'primaryAddress__latitude' =>
                  array (
                    'attributeLabel' => 'Primary Address - Latitude',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'latitude',
                    'attributeImportRulesType' => 'Decimal',
                    'isRequired' => false,
                  ),
              'primaryAddress__longitude' =>
                  array (
                    'attributeLabel' => 'Primary Address - Longitude',
                    'attributeName' => 'primaryAddress',
                    'relationAttributeName' => 'longitude',
                    'attributeImportRulesType' => 'Decimal',
                    'isRequired' => false,
                  ),
              'secondaryEmail__emailAddress' =>
                  array (
                    'attributeLabel' => 'Secondary Email - Email Address',
                    'attributeName' => 'secondaryEmail',
                    'relationAttributeName' => 'emailAddress',
                    'attributeImportRulesType' => 'Email',
                    'isRequired' => false,
                  ),
              'secondaryEmail__optOut' =>
                  array (
                    'attributeLabel' => 'Secondary Email - Opt Out',
                    'attributeName' => 'secondaryEmail',
                    'relationAttributeName' => 'optOut',
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
              'secondaryEmail__isInvalid' =>
                  array (
                    'attributeLabel' => 'Secondary Email - Is Invalid',
                    'attributeName' => 'secondaryEmail',
                    'relationAttributeName' => 'isInvalid',
                    'attributeImportRulesType' => 'CheckBox',
                    'isRequired' => false,
                  ),
            );
            $this->assertEquals($compareData, $attributesCollection);
        }
    }
?>