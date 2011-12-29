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

    class ImportToMappingFormLayoutUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetMappableAttributeIndicesAndDerivedTypes()
        {
            $data = ImportModelTestItemImportRules::getMappableAttributeIndicesAndDerivedTypes();
            $compareData = array(
                'boolean'                      => 'Boolean',
                'createdByUser'                => 'Created By User',
                'createdDateTime'              => 'Created Date Time',
                'currencyValue'                => 'Currency Value',
                'date'                         => 'Date',
                'dateTime'                     => 'Date Time',
                'dropDown'                     => 'Drop Down',
                'firstName'                    => 'First Name',
                'float'                        => 'Float',
                'FullName'                     => 'Full Name',
                'hasOne'                       => 'Has One',
                'hasOneAlso'                   => 'Has One Also',
                'id'                           => 'Id',
                'ImportModelTestItem3Derived'  => 'ImportModelTestItem3',
                'integer'                      => 'Integer',
                'lastName'                     => 'Last Name',
                'modifiedByUser'               => 'Modified By User',
                'modifiedDateTime'             => 'Modified Date Time',
                'multiDropDown'                => 'Multi Drop Down',
                'owner'                        => 'Owner',
                'phone'                        => 'Phone',
                'primaryAddress__city'         => 'Primary Address - City',
                'primaryAddress__country'      => 'Primary Address - Country',
                'primaryAddress__invalid'      => 'Primary Address - Invalid',
                'primaryAddress__latitude'     => 'Primary Address - Latitude',
                'primaryAddress__longitude'    => 'Primary Address - Longitude',
                'primaryAddress__postalCode'   => 'Primary Address - Postal Code',
                'primaryAddress__state'        => 'Primary Address - State',
                'primaryAddress__street1'      => 'Primary Address - Street 1',
                'primaryAddress__street2'      => 'Primary Address - Street 2',
                'primaryEmail__emailAddress'   => 'Primary Email - Email Address',
                'primaryEmail__isInvalid'      => 'Primary Email - Is Invalid',
                'primaryEmail__optOut'         => 'Primary Email - Opt Out',
                'radioDropDown'                => 'Radio Drop Down',
                'secondaryEmail__emailAddress' => 'Secondary Email - Email Address',
                'secondaryEmail__isInvalid'    => 'Secondary Email - Is Invalid',
                'secondaryEmail__optOut'       => 'Secondary Email - Opt Out',
                'string'                       => 'String',
                'tagCloud'                     => 'Tag Cloud',
                'textArea'                     => 'Text Area',
                'url'                          => 'Url',
            );
            $this->assertEquals(serialize($compareData), serialize($data));
            $mappingFormLayoutUtil = ImportToMappingFormLayoutUtil::
                                     make('ImportModelTestItem', new ZurmoActiveForm(), 'ImportModelTestItem', $data);
            $this->assertEquals(serialize($compareData),
                                serialize($mappingFormLayoutUtil->getMappableAttributeIndicesAndDerivedTypesForImportColumns()));
            $compareData2 = array(
                'boolean'                      => 'Boolean',
                'currencyValue'                => 'Currency Value',
                'date'                         => 'Date',
                'dateTime'                     => 'Date Time',
                'dropDown'                     => 'Drop Down',
                'firstName'                    => 'First Name',
                'float'                        => 'Float',
                'FullName'                     => 'Full Name',
                'hasOne'                       => 'Has One',
                'hasOneAlso'                   => 'Has One Also',
                'ImportModelTestItem3Derived'  => 'ImportModelTestItem3',
                'integer'                      => 'Integer',
                'lastName'                     => 'Last Name',
                'multiDropDown'                => 'Multi Drop Down',
                'owner'                        => 'Owner',
                'phone'                        => 'Phone',
                'primaryAddress__city'         => 'Primary Address - City',
                'primaryAddress__country'      => 'Primary Address - Country',
                'primaryAddress__invalid'      => 'Primary Address - Invalid',
                'primaryAddress__latitude'     => 'Primary Address - Latitude',
                'primaryAddress__longitude'    => 'Primary Address - Longitude',
                'primaryAddress__postalCode'   => 'Primary Address - Postal Code',
                'primaryAddress__state'        => 'Primary Address - State',
                'primaryAddress__street1'      => 'Primary Address - Street 1',
                'primaryAddress__street2'      => 'Primary Address - Street 2',
                'primaryEmail__emailAddress'   => 'Primary Email - Email Address',
                'primaryEmail__isInvalid'      => 'Primary Email - Is Invalid',
                'primaryEmail__optOut'         => 'Primary Email - Opt Out',
                'radioDropDown'                => 'Radio Drop Down',
                'secondaryEmail__emailAddress' => 'Secondary Email - Email Address',
                'secondaryEmail__isInvalid'    => 'Secondary Email - Is Invalid',
                'secondaryEmail__optOut'       => 'Secondary Email - Opt Out',
                'string'                       => 'String',
                'tagCloud'                     => 'Tag Cloud',
                'textArea'                     => 'Text Area',
                'url'                          => 'Url',
            );
            $this->assertEquals(serialize($compareData2),
                                serialize($mappingFormLayoutUtil->getMappableAttributeIndicesAndDerivedTypesForExtraColumns()));
        }
    }
?>