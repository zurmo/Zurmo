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

    class DynamicSearchFormTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        /**
         * Test valid structure uses
         */
        public function testValidStructure ()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'),
                                    array('structurePosition' => '3'));
            $searchForm->dynamicStructure = '(1 AND 2 )';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 AND 2 ';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 and 2 ';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 oR 2 ';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 OR 2 AND 3';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '(1 OR 2 )AND 3';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 OR (2 )AND 3';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '(1 AND 2 AND 3)';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test valid uses if no clauses
         */
        public function testValidUseIfNoClauses()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array();
            $searchForm->dynamicStructure = 'a';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = null;
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = 'jim';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = 'jim and me';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test invalide use of parenthesis
         */
        public function testInvalidParenthesisInStructure()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = '1 ( 2';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 ) 2 )';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 ( 2 ())))';
            $searchForm->validateDynamicStructure('dynamicStructure', '');
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test if its used a number that isnt a structurePosition number
         */
        public function testInvalidNumberInStructurePosition()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = '1 AND 3';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 AND 10';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 AND -5';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 AND 0';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 OR 1.4';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 OR 1,4'; // Not Coding Standard
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test invalid use of operators
         */
        public function testInvalidOperatorInStructurePosition()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = '1 + 2';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 A* 2';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 * 2 AND 3';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test other invalid expressions
         */
        public function testOtherInvalidExpressionsInStructurePosition()
        {
            $searchForm = new AAASearchFormTestModel(new A());
            $searchForm->dynamicClauses   = array(
                                    array('structurePosition' => '1'),
                                    array('structurePosition' => '2'));
            $searchForm->dynamicStructure = '1 OR OR 2';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '1 AND ( 2 ) 2';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = 'OR 2 AND 1';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = 'OR 2 AND 1';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
            $searchForm->dynamicStructure = '5 AND';
            $searchForm->validateDynamicStructure('dynamicStructure', array());
            $this->assertTrue($searchForm->hasErrors());
            $searchForm->clearErrors();
        }

        /**
         * Test validating against a MixedRelationsModel
         */
        public function testValidateDynamicClauses()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $searchForm = new MixedRelationsModelSearchFormTestModel(new MixedRelationsModel());
            $searchForm->dynamicClauses   = array(
                                                array('structurePosition'           => '1',
                                                      'attributeIndexOrDerivedType' => 'primaryA',
                                                      'primaryA' => array('name' => 'xtz')));
            $searchForm->dynamicStructure = '1';
            $searchForm->validateDynamicClauses('dynamicClauses', array());
            $this->assertFalse($searchForm->hasErrors());
            $searchForm->clearErrors();
        }
    }
?>