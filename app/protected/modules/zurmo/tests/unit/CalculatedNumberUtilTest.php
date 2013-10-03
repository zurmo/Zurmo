<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class CalculatedNumberUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
        }

        public function testCalculateByFormulaAndModel()
        {
            $model  = new TestOperatorTypeModel();
            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(4 + 5)', $model, $formatType, $currencyCode);
            $this->assertEquals(9, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_INTEGER, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(4 + 5)', $model);
            $this->assertEquals(9, $result);

            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + 5)', $model, $formatType,
                                                                       $currencyCode);
            $this->assertEquals(5, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_INTEGER, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + 5)', $model);
            $this->assertEquals(5, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + integerS)', $model);
            $this->assertEquals(0, $result);

            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + floatStandard)', $model,
                                                                       $formatType, $currencyCode);
            $this->assertEquals(0, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_DECIMAL, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + floatStandard)', $model);
            $this->assertEquals(0, $result);

            //Make attributes have actual values.
            $model->integerS        = 1000;
            $model->integerStandard = 3000;
            $model->floatStandard   = 6000.39;
            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(4 + 5)', $model, $formatType, $currencyCode);
            $this->assertEquals(9, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_INTEGER, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(4 + 5)', $model);
            $this->assertEquals(9, $result);

            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + 5)', $model, $formatType,
                                                                       $currencyCode);
            $this->assertEquals(3005, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_INTEGER, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + 5)', $model);
            $this->assertEquals('3,005', $result); // Not Coding Standard
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + integerS)', $model);
            $this->assertEquals('4,000', $result); // Not Coding Standard

            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + floatStandard)', $model,
                                                                       $formatType, $currencyCode);
            $this->assertEquals(9000.39, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_DECIMAL, $formatType);
            $this->assertNull($currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(integerStandard + floatStandard)', $model);
            $this->assertEquals('9,000.39', $result); // Not Coding Standard
        }

        public function testCalculateByFormulaAndModelAndResolveFormatForIfStatement()
        {
            $model  = new TestOperatorTypeModel();
            $formatType   = null;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 == 1; "true"; "false")', // Not Coding Standard
                                                                       $model);
            $this->assertEquals("true", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 > 1; "true"; "false")',  // Not Coding Standard
                                                                       $model);
            $this->assertEquals("false", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 >= 1; "true"; "false")', // Not Coding Standard
                                                                       $model);
            $this->assertEquals("true", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 < 1; "true"; "false")',  // Not Coding Standard
                                                                       $model);
            $this->assertEquals("false", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 <= 1; "true"; "false")', // Not Coding Standard
                                                                       $model);
            $this->assertEquals("true", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 != 1; "true"; "false")', // Not Coding Standard
                                                                       $model);
            $this->assertEquals("false", $result);

            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('IF(1 == 1; 12.5; 0.65)',      // Not Coding Standard
                                                                       $model);
            $this->assertEquals(12.5, $result);

            //Make attributes have actual values.
            $model->integerStandard = 1000;
            $model->floatStandard   = 1000.01;
            $model->booleanStandard = true;
            $model->urlStandard     = 'http://www.zurmo.com';

            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat(
                                                'IF(integerStandard > 1; urlStandard; "false")', // Not Coding Standard
                                                $model);
            $this->assertEquals($model->urlStandard, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat(
                                                'IF(booleanStandard; urlStandard; "false")',     // Not Coding Standard
                                                $model);
            $this->assertEquals("false", $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat(
                                                'IF(urlStandard != "zurmo.org"; floatStandard; integerStandard)', // Not Coding Standard
                                                $model);
            $this->assertEquals(1000.01, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat(
                                                'IF(urlStandard == "zurmo.org"; floatStandard; integerStandard)', // Not Coding Standard
                                                $model);
            $this->assertEquals(1000, $result);
        }

        public function testCurrencyValuesInFormula()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $item = new CurrencyValueTestItem();
            $formatType   = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode = null;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(amount + 5)', $item, $formatType, $currencyCode);
            $this->assertEquals(5, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_CURRENCY_VALUE, $formatType);
            $this->assertEquals('USD', $currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(amount + 5)', $item);
            $this->assertEquals('$5.00', $result);

            //Now put a value for the currency amount
            $currencyValue        = new CurrencyValue();
            $currencyValue->value = 10000.45;
            $item->amount         = $currencyValue;
            $formatType           = CalculatedNumberUtil::FORMAT_TYPE_INTEGER;
            $currencyCode         = null;
            $result               = CalculatedNumberUtil::calculateByFormulaAndModel('(amount * 5)', $item, $formatType,
                                                                                     $currencyCode);
            $this->assertEquals(50002.25, $result);
            $this->assertEquals(CalculatedNumberUtil::FORMAT_TYPE_CURRENCY_VALUE, $formatType);
            $this->assertEquals('USD', $currencyCode);
            $result = CalculatedNumberUtil::calculateByFormulaAndModelAndResolveFormat('(amount * 5)', $item);
            $this->assertEquals('$50,002.25', $result); // Not Coding Standard
        }

        public function testIsFormulaValid()
        {
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('IF(4 + 5;"string";"")', 'TestOperatorTypeModel'));   // Not Coding Standard
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('IF(4 >=! 5;"string";"")', 'TestOperatorTypeModel')); // Not Coding Standard
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('IF(4>=5;>string";"")', 'TestOperatorTypeModel'));    // Not Coding Standard
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('IF(4>=5;"string":"")', 'TestOperatorTypeModel'));    // Not Coding Standard
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('IF(4 == 5;"true string";"false string")', 'TestOperatorTypeModel')); // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid("IF(4 == 5;'true string';'false string')", 'TestOperatorTypeModel'));  // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('IF(4 <= 5;emailStandard;urlStandard)', 'TestOperatorTypeModel'));     // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('IF(4 < 5;emailStandard;urlStandard)', 'TestOperatorTypeModel'));      // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('IF(4 > 5;emailStandard;urlStandard)', 'TestOperatorTypeModel'));      // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('IF(4 >= 5;emailStandard;urlStandard)', 'TestOperatorTypeModel'));     // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('IF(4 != 5;emailStandard;urlStandard)', 'TestOperatorTypeModel'));     // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid("IF(emailStandard == 'email';emailStandard;urlStandard)", 'TestOperatorTypeModel')); // Not Coding Standard
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(4 + 5)', 'TestOperatorTypeModel'));
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(integerStandard + 5)', 'TestOperatorTypeModel'));
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(integerStandard + floatStandard)', 'TestOperatorTypeModel'));
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('(integerStandard + floatStandard + jj)', 'TestOperatorTypeModel'));
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid(')4(', 'TestOperatorTypeModel'));
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(integerStandard + integerS)', 'TestOperatorTypeModel'));
        }
    }
?>
