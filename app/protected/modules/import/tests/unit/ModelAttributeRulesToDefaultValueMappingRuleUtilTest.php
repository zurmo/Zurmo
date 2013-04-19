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

    class ModelAttributeRulesToDefaultValueMappingRuleUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetApplicableRulesByModelClassNameAndAttributeName()
        {
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'boolean', 'defaultValue');
            $compareData = array(array('defaultValue',  'boolean'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'date', 'defaultValue');
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'date'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'dateTime', 'defaultValue');
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'datetime'));
            $this->assertEquals($compareData, $rules);

            //Test multiple variations of dateTime.  Required/Not-Required. Also when having a dependency
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem5', 'requiredDateTime', 'defaultValue', true);
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'datetime'),
                                 array('defaultValue',  'required'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem5', 'startDateTime', 'defaultValue', true);
            $compareData = array(array('defaultValue',  'required'),
                                 array('defaultValue',  'TypeValidator', 'type' => 'datetime'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem5', 'endDateTime', 'defaultValue');
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'datetime'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'float', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'float'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'integer', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'integer'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'phone', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 1, 'max' => 14));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'string', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'textArea', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'url', 'defaultValue');
            $compareData = array(array('defaultValue',  'url'));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'firstName', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 1, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'lastName', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 2, 'max' => 32));
            $this->assertEquals($compareData, $rules);

            //Now test lastName and string with required as applicable.
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'string', 'defaultValue', true);
            $compareData = array(array('defaultValue',  'required'),
                                 array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 3, 'max' => 64));
            $this->assertEquals($compareData, $rules);

            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('ImportModelTestItem', 'lastName', 'defaultValue', true);
            $compareData = array(array('defaultValue',  'required'),
                                 array('defaultValue',  'type', 'type' => 'string'),
                                 array('defaultValue',  'length',  'min'  => 2, 'max' => 32));
            $this->assertEquals($compareData, $rules);
        }

        public function testGetApplicableRulesByModelClassNameAndAttributeNameForCustomCreatedTypes()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $import = new Import();
            $import->serializedData = serialize(array('importRulesType' => 'Accounts'));
            $this->assertTrue($import->save());

            ModulesSearchWithDataProviderTestHelper::createDateAttribute(new Account(), 'date');
            ModulesSearchWithDataProviderTestHelper::createDateTimeAttribute(new Account(), 'dateTime');
            ModulesSearchWithDataProviderTestHelper::createCheckBoxAttribute(new Account(), 'checkbox');
            ModulesSearchWithDataProviderTestHelper::createDecimalAttribute(new Account(), 'decimal');
            ModulesSearchWithDataProviderTestHelper::createIntegerAttribute(new Account(), 'integer');
            ModulesSearchWithDataProviderTestHelper::createPhoneAttribute(new Account(), 'phone');
            ModulesSearchWithDataProviderTestHelper::createTextAttribute(new Account(), 'text');
            ModulesSearchWithDataProviderTestHelper::createTextAreaAttribute(new Account(), 'textArea');
            ModulesSearchWithDataProviderTestHelper::createUrlAttribute(new Account(), 'url');

            //Test All custom created types since their rules could vary
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'dateCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'date'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'dateTimeCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'TypeValidator', 'type' => 'datetime'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'checkboxCstm', 'defaultValue');
            $compareData = array(array('defaultValue', 'boolean'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'decimalCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'length',  'max'  => 6),
                                 array('defaultValue',  'RedBeanModelNumberValidator', 'precision' => 2),
                                 array('defaultValue',  'type',  'type' => 'float'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'integerCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'length',  'max'  => 11),
                                 array('defaultValue',  'numerical', 'min'  => -500000, 'max'  => 500000),
                                 array('defaultValue',  'type',  'type' => 'integer'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'phoneCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'length',  'max' => 20),
                                array('defaultValue',  'type', 'type' => 'string'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'textCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'length',  'max' => 50),
                                array('defaultValue',  'type', 'type' => 'string'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'textAreaCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'type', 'type' => 'string'));
            $this->assertEquals($compareData, $rules);
            $rules = ModelAttributeRulesToDefaultValueMappingRuleUtil::
                     getApplicableRulesByModelClassNameAndAttributeName('Account', 'urlCstm', 'defaultValue');
            $compareData = array(array('defaultValue',  'length',  'max' => 50),
                                 array('defaultValue',  'url',     'defaultScheme' => 'http'));
            $this->assertEquals($compareData, $rules);
            //todo: add the rest of the custom field types that are importable
        }
    }
?>