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

    class MappingRuleFormAndElementTypeUtilTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeCollectionByAttributeImportRules()
        {
            //CheckBox
            $attributeImportRules = new CheckBoxAttributeImportRules(new ImportModelTestItem(), 'checkBox');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'checkBox', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('CheckBox', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //CreateByUser
            $attributeImportRules = new CreatedByUserAttributeImportRules(new ImportModelTestItem(), 'createdByUser');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'createdByUser', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingUserValueTypeDropDown', $collection[0]['elementType']);
            $this->assertEquals('UserValueTypeModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //CreatedDateTime
            $attributeImportRules = new CreatedDateTimeAttributeImportRules(new ImportModelTestItem(), 'createdDatetime');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'createdDatetime', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingRuleDateTimeFormatDropDown', $collection[0]['elementType']);
            $this->assertEquals('ValueFormatMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //CurrencyValue attribute
            $attributeImportRules = new CurrencyValueAttributeImportRules(new ImportModelTestItem(), 'currencyValue');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'currencyValue', 'importColumn');
            $this->assertEquals(3,                      count($collection));
            $this->assertEquals('Decimal',              $collection[0]['elementType']);
            $this->assertEquals('CurrencyDropDownForm', $collection[1]['elementType']);
            $this->assertEquals('Decimal',              $collection[2]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm',
                                get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('CurrencyIdModelAttributeMappingRuleForm',
                                get_class($collection[1]['mappingRuleForm']));
            $this->assertEquals('CurrencyRateToBaseModelAttributeMappingRuleForm',
                                get_class($collection[2]['mappingRuleForm']));

            //Date
            $attributeImportRules = new DateAttributeImportRules(new ImportModelTestItem(), 'date');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'date', 'importColumn');
            $this->assertEquals(2, count($collection));
            $this->assertEquals('Date', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('ImportMappingRuleDateFormatDropDown', $collection[1]['elementType']);
            $this->assertEquals('ValueFormatMappingRuleForm', get_class($collection[1]['mappingRuleForm']));

            //DateTime
            $attributeImportRules = new DateTimeAttributeImportRules(new ImportModelTestItem(), 'dateTime');
            $collection           = MappingRuleFormAndElementTypeUtil::
            makeCollectionByAttributeImportRules($attributeImportRules,
                                                                                     'dateTime', 'importColumn');
            $this->assertEquals(2, count($collection));

            $this->assertEquals('DateTime', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));
            $this->assertEquals('ImportMappingRuleDateTimeFormatDropDown', $collection[1]['elementType']);
            $this->assertEquals('ValueFormatMappingRuleForm', get_class($collection[1]['mappingRuleForm']));

            //Decimal
            $attributeImportRules = new DecimalAttributeImportRules(new ImportModelTestItem(), 'decimal');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'decimal', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Decimal', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //DropDown
            $attributeImportRules = new DropDownAttributeImportRules(new ImportModelTestItem(), 'dropDown');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'dropDown', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingRuleDefaultDropDownForm', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueDropDownModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Email
            $attributeImportRules = new EmailAttributeImportRules(new ImportModelTestItem(), 'email');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'email', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Text', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //FullName
            $attributeImportRules = new FullNameAttributeImportRules(new ImportModelTestItem(), 'fullName');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'fullName', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Text', $collection[0]['elementType']);
            $this->assertEquals('FullNameDefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Id
            $attributeImportRules = new IdAttributeImportRules(new ImportModelTestItem(), 'id');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'id', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingModelIdValueTypeDropDown', $collection[0]['elementType']);
            $this->assertEquals('IdValueTypeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Integer attribute
            $attributeImportRules = new IntegerAttributeImportRules(new ImportModelTestItem(), 'integer');
            $collection           = MappingRuleFormAndElementTypeUtil::
            makeCollectionByAttributeImportRules($attributeImportRules,
                                                                                     'integer', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Integer', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //ModifiedByUser
            $attributeImportRules = new ModifiedByUserAttributeImportRules(new ImportModelTestItem(), 'modifiedbyUser');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'modifiedbyUser', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingUserValueTypeDropDown', $collection[0]['elementType']);
            $this->assertEquals('UserValueTypeModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //ModifiedDateTime
            $attributeImportRules = new ModifiedDateTimeAttributeImportRules(new ImportModelTestItem(), 'modifiedDateTime');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'modifiedDateTime', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingRuleDateTimeFormatDropDown', $collection[0]['elementType']);
            $this->assertEquals('ValueFormatMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Phone
            $attributeImportRules = new PhoneAttributeImportRules(new ImportModelTestItem(), 'phone');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'phone', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Phone', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //RadioDropDown
            $attributeImportRules = new RadioDropDownAttributeImportRules(new ImportModelTestItem(), 'radioDropDown');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'radioDropDown', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('ImportMappingRuleDefaultDropDownForm', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueDropDownModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //TextArea
            $attributeImportRules = new TextAreaAttributeImportRules(new ImportModelTestItem(), 'textArea');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'textArea', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('TextArea', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Text
            $attributeImportRules = new TextAttributeImportRules(new ImportModelTestItem(), 'text');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'text', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Text', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));

            //Url
            $attributeImportRules = new UrlAttributeImportRules(new ImportModelTestItem(), 'url');
            $collection           = MappingRuleFormAndElementTypeUtil::
                                    makeCollectionByAttributeImportRules($attributeImportRules,
                                                                         'url', 'importColumn');
            $this->assertEquals(1, count($collection));
            $this->assertEquals('Url', $collection[0]['elementType']);
            $this->assertEquals('DefaultValueModelAttributeMappingRuleForm', get_class($collection[0]['mappingRuleForm']));
        }

        public function testMakeFormsAndElementTypesByMappingDataAndImportRulesType()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mappingData = array(
                           'column_0' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'date',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => '2012-01-12'))),
                           'column_1' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'dateTime',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => '2012-01-12 00:45'))),
                           'column_2' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'lastName',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'def'))),
                           'column_3' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'decimal',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => '1.45'))),
                           'column_4' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'integer',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 1))),
                           'column_5' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'phone',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => '7844121541'))),
                           'column_6' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'textArea',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'testTextArea'))),
                           'column_7' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'url',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'http://www.test.com'))),
                           'column_8' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'string',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'testString'))),
                           'column_9' => array('type' => 'importColumn', 'attributeIndexOrDerivedType' => 'firstName',
                           'mappingRulesData' =>
                           array('DefaultValueModelAttributeMappingRuleForm' => array('defaultValue' => 'testfirstName'))),
            );
            $data = MappingRuleFormAndElementTypeUtil::
                    makeFormsAndElementTypesByMappingDataAndImportRulesType($mappingData, 'ImportModelTestItem');
            $this->assertEquals(10, count($data));
            $this->assertEquals('2012-01-12',          $data['column_0'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Date',                $data['column_0'][0]['elementType']);
            $this->assertEquals('2012-01-12 00:45',    $data['column_1'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('DateTime',            $data['column_1'][0]['elementType']);
            $this->assertEquals('def',                 $data['column_2'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Text',                $data['column_2'][0]['elementType']);
            $this->assertEquals('1.45',                $data['column_3'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Decimal',             $data['column_3'][0]['elementType']);
            $this->assertEquals(1,                     $data['column_4'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Integer',             $data['column_4'][0]['elementType']);
            $this->assertEquals('7844121541',          $data['column_5'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Phone',               $data['column_5'][0]['elementType']);
            $this->assertEquals('testTextArea',        $data['column_6'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('TextArea',            $data['column_6'][0]['elementType']);
            $this->assertEquals('http://www.test.com', $data['column_7'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Url',                 $data['column_7'][0]['elementType']);
            $this->assertEquals('testString',          $data['column_8'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Text',                $data['column_8'][0]['elementType']);
            $this->assertEquals('testfirstName',       $data['column_9'][0]['mappingRuleForm']->defaultValue);
            $this->assertEquals('Text',                $data['column_9'][0]['elementType']);
        }

        public function testValidateMappingRuleForms()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $stringDefaultValueMappingRuleForm   = new DefaultValueModelAttributeMappingRuleForm(
                                                   'ImportModelTestItem', 'string');
            $stringDefaultValueMappingRuleForm->defaultValue = 'abc';
            $lastNameDefaultValueMappingRuleForm   = new DefaultValueModelAttributeMappingRuleForm(
                                                     'ImportModelTestItem', 'lastName');
            $numericalDefaultValueMappingRuleForm  = new DefaultValueModelAttributeMappingRuleForm(
                                                     'ImportModelTestItem', 'numerical');
            $precisionDefaultValueMappingRuleForm  = new DefaultValueModelAttributeMappingRuleForm(
                                                     'ImportModelTestItem', 'decimal');
            //Validate true because scenario is not extra column
            $mappingRuleFormsData = array('column_0' => array(
                      array('mappingRuleForm' => $stringDefaultValueMappingRuleForm, 'elementType' => 'Text')),
                      'column_1' => array(
                      array('mappingRuleForm' => $lastNameDefaultValueMappingRuleForm, 'elementType' => 'Text')),
                      'column_2' => array(
                      array('mappingRuleForm' => $numericalDefaultValueMappingRuleForm, 'elementType' => 'Integer')),
                      'column_3' => array(
                      array('mappingRuleForm' => $precisionDefaultValueMappingRuleForm, 'elementType' => 'Decimal')));
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