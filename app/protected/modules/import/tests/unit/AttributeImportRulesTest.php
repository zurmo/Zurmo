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

    class AttributeImportRulesTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }
        public function testMakeByImportRulesTypeAndAttributeNameOrDerivedType()
        {
            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'boolean');
            $this->assertTrue($attributeImportRules instanceof CheckBoxAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'date');
            $this->assertTrue($attributeImportRules instanceof DateAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'dateTime');
            $this->assertTrue($attributeImportRules instanceof DateTimeAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'float');
            $this->assertTrue($attributeImportRules instanceof DecimalAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'integer');
            $this->assertTrue($attributeImportRules instanceof IntegerAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'phone');
            $this->assertTrue($attributeImportRules instanceof PhoneAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'string');
            $this->assertTrue($attributeImportRules instanceof TextAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'textArea');
            $this->assertTrue($attributeImportRules instanceof TextAreaAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'url');
            $this->assertTrue($attributeImportRules instanceof UrlAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'FullName');
            $this->assertTrue($attributeImportRules instanceof FullNameAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'currencyValue');
            $this->assertTrue($attributeImportRules instanceof CurrencyValueAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'hasOne');
            $this->assertTrue($attributeImportRules instanceof ImportModelTestItem2AttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'dropDown');
            $this->assertTrue($attributeImportRules instanceof DropDownAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'primaryEmail__emailAddress');
            $this->assertTrue($attributeImportRules instanceof EmailAttributeImportRules);

            $attributeImportRules = AttributeImportRulesFactory::makeByImportRulesTypeAndAttributeNameOrDerivedType(
                                    'ImportModelTestItem', 'primaryAddress__street1');
            $this->assertTrue($attributeImportRules instanceof TextAttributeImportRules);
        }
    }
?>