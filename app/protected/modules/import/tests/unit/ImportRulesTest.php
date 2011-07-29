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

    class ImportRulesTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }
        public function testGetMappableAttributeNamesAndDerivedTypes()
        {
            $data = ImportModelTestItemImportRules::getMappableAttributeNamesAndDerivedTypes();
            $compareData = array(
                'CreatedByUser'				  => 'Created By User',
                'CreatedDateTime'			  => 'Created Date Time',
                'FullName'					  => 'Full Name',
                'ModifiedByUser'		      => 'Modified By User',
                'ModifiedDateTime'		      => 'Modified Date Time',
                'boolean'                     => 'Boolean',
                'currencyValue'			      => 'Currency Value',
                'date'                        => 'Date',
                'dateTime'                    => 'Date Time',
                'dropDown'                    => 'Drop Down',
                'firstName'					  => 'First Name',
                'float'                       => 'Float',
                'hasOne'					  => 'Has One',
                'id'                          => 'Id',
                'integer'                     => 'Integer',
                'lastName'					  => 'Last Name',
                'owner' 				      => 'Owner',
                'phone'                       => 'Phone',
                'primaryAddress__city'        => 'City',
                'primaryAddress__country'     => 'Country',
                'primaryAddress__postalCode'  => 'Postal Code',
                'primaryAddress__state'       => 'State',
                'primaryAddress__street1'     => 'Street 1',
                'primaryAddress__street2'     => 'Street 2',
                'primaryEmail__emailAddress'  => 'Email Address',
                'primaryEmail__isInvalid'     => 'Is Invalid',
                'primaryEmail__optOut'        => 'Opt Out',
                'radioDropDown'				  => 'Radio Drop Down',
                'string'                      => 'String',
                'textArea'                    => 'Text Area',
                'url'                         => 'Url',
            );
            $this->assertEquals($compareData, $data);
        }

        public function testGetModelClassNameByAttributeNameOrDerivedType()
        {
            $modelClassName = ImportModelTestItemImportRules::
                              getModelClassNameByAttributeNameOrDerivedType('boolean');
            $this->assertEquals('ImportModelTestItem', $modelClassName);
        }


        public function testGetAttributeImportRulesTypeBy()
        {
            Yii::app()->user->userModel     = User::getByUsername('super');
            $attributeImportRulesType       = ImportModelTestItemImportRules::getAttributeImportRulesType('boolean');
            $this->assertEquals('CheckBox',   $attributeImportRulesType);
        }

        public function testGetImportRulesTypesForCurrentUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $data = ImportRulesUtil::getImportRulesTypesForCurrentUser();
            $compareData = array(
                'AccountImportRules' => 'Accounts',
            );
            $this->assertEquals($compareData, $data);
        }
    }
?>