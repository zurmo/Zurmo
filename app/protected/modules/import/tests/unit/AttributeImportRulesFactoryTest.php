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