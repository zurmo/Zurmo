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

    class MappingRuleFormAndElementTypeUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeCollectionByAttributeImportRules()
        {
            $attributeImportRules = new PhoneAttributeImportRules(new ImportModelTestItem(), 'phone');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'phone', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Phone', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //User attribute
            $attributeImportRules = new UserAttributeImportRules(new ImportModelTestItem(), 'owner');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'owner', 'importColumn');
            $this->assertEquals(2, count($collection));
            $this->assertEquals('ImportMappingRuleDefaultModelNameId', $collection[0]['elementType']);
            $this->assertEquals('DefaultModelNameIdMappingRuleForm', get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('ImportMappingUserValueTypeDropDown', $collection[1]['elementType']);
            $this->assertEquals('UserValueTypeModelAttributeMappingRuleForm', get_class($collection[1]['mappingRuleForm']));

            //CurrencyValue attribute
            $attributeImportRules = new CurrencyValueAttributeImportRules(new ImportModelTestItem(), 'currencyValue');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'currencyValue', 'importColumn');
            $this->assertEquals(3, count($collection));
            $this->assertEquals('Decimal',              $collection[0]['elementType']);
            $this->assertEquals('CurrencyDropDownForm', $collection[1]['elementType']);
            $this->assertEquals('Decimal',              $collection[2]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm',
                                get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('CurrencyIdModelAttributeMappingRuleForm',
                                get_class($collection[1]['mappingRuleForm']));
            $this->assertEquals('CurrencyRateToBaseModelAttributeMappingRuleForm',
                                get_class($collection[2]['mappingRuleForm']));
        }

        public function testMakeFormsAndElementTypesByMappingDataAndImportRulesType()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mappingData = array(
                           'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'abc'))),
                           'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'lastName',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'def'))),
            );
            $data = MappingRuleFormAndElementTypeUtil::
                    makeFormsAndElementTypesByMappingDataAndImportRulesType($mappingData, 'ImportModelTestItem');
            $this->assertEquals(2, count($data));
            $this->assertEquals('abc',  $data['column_0'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Text', $data['column_0'][0]['elementType']);
            $this->assertEquals('def',  $data['column_1'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Text', $data['column_1'][0]['elementType']);
        }

        public function testValidateMappingRuleForms()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $stringDefaultValueMappingRuleForm   = new DefaultValueModelAttributeMappingRuleForm(
                                                   'ImportModelTestItem', 'string');
            $stringDefaultValueMappingRuleForm->defaultValue = 'abc';
            $lastNameDefaultValueMappingRuleForm = new DefaultValueModelAttributeMappingRuleForm(
                                                   'ImportModelTestItem', 'lastName');
            //Validate true because scenario is not extra column
            $mappingRuleFormsData = array('column_0' => array(
                                          array('mappingRuleForm' => $stringDefaultValueMappingRuleForm),
                                          array('mappingRuleForm' => $lastNameDefaultValueMappingRuleForm)));
            $validated = MappingRuleFormAndElementTypeUtil::validateMappingRuleForms($mappingRuleFormsData);
            $this->assertTrue($validated);

            //Now the scenario is extra column, so the lastName column will require validation.
            $lastNameDefaultValueMappingRuleForm = new DefaultValueModelAttributeMappingRuleForm(
                                                   'ImportModelTestItem', 'lastName');
            $lastNameDefaultValueMappingRuleForm->setScenario('extraColumn');
            $mappingRuleFormsData = array('column_0' => array(
                                          array('mappingRuleForm' => $stringDefaultValueMappingRuleForm),
                                          array('mappingRuleForm' => $lastNameDefaultValueMappingRuleForm)));
            $validated = MappingRuleFormAndElementTypeUtil::validateMappingRuleForms($mappingRuleFormsData);
            $this->assertFalse($validated);

            //Now will validate true because we are populating the default value.
            $lastNameDefaultValueMappingRuleForm->defaultValue = 'def';
            $validated = MappingRuleFormAndElementTypeUtil::validateMappingRuleForms($mappingRuleFormsData);
            $this->assertTrue($validated);
        }

        public function testResolveAttributeIndexAndTheFormsAreUsingTheCorrectModelClassNameAndAttributeName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $attributeImportRules = new EmailAttributeImportRules(new Email(), 'emailAddress');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                    'primaryEmail__emailAddress', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Text', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('Email',        static::getReflectedPropertyValue($collection[0]['mappingRuleForm'],
                                                'modelClassName'));
            $this->assertEquals('emailAddress', static::getReflectedPropertyValue($collection[0]['mappingRuleForm'],
                                                'modelAttributeName'));
        }
    }
?>