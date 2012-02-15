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

    class CalculatedNumberUtilTest extends BaseTest
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
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(4 + 5)', $model);
            $this->assertEquals(9, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + 5)', $model);
            $this->assertEquals(5, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + floatStandard)', $model);
            $this->assertEquals(0, $result);

            //Make attributes have actual values.
            $model->integerStandard = 3;
            $model->floatStandard   = 6;
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(4 + 5)', $model);
            $this->assertEquals(9, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + 5)', $model);
            $this->assertEquals(8, $result);
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(integerStandard + floatStandard)', $model);
            $this->assertEquals(9, $result);
        }

        public function testCurrencyValuesInFormula()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $item = new CurrencyValueTestItem();
            $result = CalculatedNumberUtil::calculateByFormulaAndModel('(amount + 5)', $item);
            $this->assertEquals(5, $result);

            //Now put a value for the currency amount
            $currencyValue        = new CurrencyValue();
            $currencyValue->value = 100;
            $item->amount         = $currencyValue;
            $result               = CalculatedNumberUtil::calculateByFormulaAndModel('(amount * 5)', $item);
            $this->assertEquals(500, $result);
        }

        public function testIsFormulaValid()
        {
            $model          = new TestOperatorTypeModel();
            $adapter        = new ModelNumberOrCurrencyAttributesAdapter($model);
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(4 + 5)', $adapter));
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(integerStandard + 5)', $adapter));
            $this->assertTrue(CalculatedNumberUtil::isFormulaValid('(integerStandard + floatStandard)', $adapter));
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid('(integerStandard + floatStandard + jj)', $adapter));
            $this->assertFalse(CalculatedNumberUtil::isFormulaValid(')4(', $adapter));
        }
    }
?>
