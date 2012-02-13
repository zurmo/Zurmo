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

    class AttributeImportRulesTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeByImportRulesTypeAndAttributeIndexOrDerivedType()
        {
            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'boolean');
            $this->assertTrue($attributeImportRules instanceof CheckBoxAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'date');
            $this->assertTrue($attributeImportRules instanceof DateAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'dateTime');
            $this->assertTrue($attributeImportRules instanceof DateTimeAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'float');
            $this->assertTrue($attributeImportRules instanceof DecimalAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'integer');
            $this->assertTrue($attributeImportRules instanceof IntegerAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'phone');
            $this->assertTrue($attributeImportRules instanceof PhoneAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'string');
            $this->assertTrue($attributeImportRules instanceof TextAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'textArea');
            $this->assertTrue($attributeImportRules instanceof TextAreaAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'url');
            $this->assertTrue($attributeImportRules instanceof UrlAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'FullName');
            $this->assertTrue($attributeImportRules instanceof FullNameAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'currencyValue');
            $this->assertTrue($attributeImportRules instanceof CurrencyValueAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'hasOne');
            $this->assertTrue($attributeImportRules instanceof ImportModelTestItem2AttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'dropDown');
            $this->assertTrue($attributeImportRules instanceof DropDownAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'radioDropDown');
            $this->assertTrue($attributeImportRules instanceof RadioDropDownAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'primaryEmail__emailAddress');
            $this->assertTrue($attributeImportRules instanceof EmailAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'primaryAddress__street1');
            $this->assertTrue($attributeImportRules instanceof TextAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'createdByUser');
            $this->assertTrue($attributeImportRules instanceof CreatedByUserAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'modifiedByUser');
            $this->assertTrue($attributeImportRules instanceof ModifiedByUserAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'createdDateTime');
            $this->assertTrue($attributeImportRules instanceof CreatedDateTimeAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'modifiedDateTime');
            $this->assertTrue($attributeImportRules instanceof ModifiedDateTimeAttributeImportRules);
        }

        public function testGetExtraColumnUsableCountOfModelAttributeMappingRuleFormTypesAndElementTypes()
        {
            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'modifiedByUser');
            $count = $attributeImportRules->getExtraColumnUsableCountOfModelAttributeMappingRuleFormTypesAndElementTypes();
            $this->assertEquals(0, $count);
            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeIndexOrDerivedType(
                                    'ImportModelTestItem', 'dropDown');
            $count = $attributeImportRules->getExtraColumnUsableCountOfModelAttributeMappingRuleFormTypesAndElementTypes();
            $this->assertEquals(1, $count);
        }
    }
?>