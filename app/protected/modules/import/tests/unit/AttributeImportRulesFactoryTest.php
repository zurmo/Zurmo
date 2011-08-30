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

    class AttributeImportRulesFactoryTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testMakeByImportRulesTypeAndAttributeIndexOrDerivedType()
        {
            //Make a non-derived attributeImportRules object.
            $attributeImportRules = AttributeImportRulesFactory::
                                    makeByImportRulesTypeAndAttributeIndexOrDerivedType('ImportModelTestItem', 'string');
            $this->assertTrue($attributeImportRules instanceof TextAttributeImportRules);

            //Make a derived attributeImportRules object.
            $attributeImportRules = AttributeImportRulesFactory::
                                    makeByImportRulesTypeAndAttributeIndexOrDerivedType('ImportModelTestItem', 'FullName');
            $this->assertTrue($attributeImportRules instanceof FullNameAttributeImportRules);
        }

        public function testMakeCollection()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $collection = AttributeImportRulesFactory::makeCollection('ImportModelTestItem', array());
            $this->assertEquals(array(), $collection);

            $collection = AttributeImportRulesFactory::
                          makeCollection('ImportModelTestItem', array('string', 'boolean', 'date'));
            $this->assertEquals(3, count($collection));
            $this->assertTrue($collection['string']  instanceof TextAttributeImportRules);
            $this->assertTrue($collection['boolean'] instanceof CheckBoxAttributeImportRules);
            $this->assertTrue($collection['date']    instanceof DateAttributeImportRules);
        }

        public function testGetAttributeNameFromAttributeNameByAttributeIndexOrDerivedType()
        {
            $attributeName = AttributeImportRulesFactory::
                             getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType('string');
            $this->assertEquals('string', $attributeName);
            $attributeName = AttributeImportRulesFactory::
                             getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType('something_string');
            $this->assertEquals('something_string', $attributeName);
            $attributeName = AttributeImportRulesFactory::
                             getAttributeNameFromAttributeNameByAttributeIndexOrDerivedType('something__string');
            $this->assertEquals('something', $attributeName);
        }

        public function testResolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType()
        {
            $modelClassName = 'ImportModelTestItem';
            $attributeName  = AttributeImportRulesFactory::
                              resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                              $modelClassName, 'primaryAddress__city');
            $this->assertEquals('Address', $modelClassName);
            $this->assertEquals('city',    $attributeName);

            $modelClassName = 'ImportModelTestItem';
            $attributeName  = AttributeImportRulesFactory::
                              resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                              $modelClassName, 'string');
            $this->assertEquals('ImportModelTestItem', $modelClassName);
            $this->assertEquals('string',              $attributeName);

            $modelClassName = 'ImportModelTestItem';
            $attributeName  = AttributeImportRulesFactory::
                              resolveModelClassNameAndAttributeNameByAttributeIndexOrDerivedType(
                              $modelClassName, 'FullName');
            $this->assertEquals('ImportModelTestItem', $modelClassName);
            $this->assertEquals('FullName',            $attributeName);
        }
    }
?>