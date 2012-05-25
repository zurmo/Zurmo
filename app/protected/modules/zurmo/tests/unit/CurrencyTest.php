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

    class CurrencyTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetAllMakesBaseCurrency()
        {
            $this->assertEquals(0, count(Currency::getAll(null, false, null, false)));
            $currency = Yii::app()->currencyHelper;
            $this->assertEquals('USD', $currency->getBaseCode());
            $this->assertEquals(0, Currency::getCount());
            $this->assertEquals(1, count(Currency::getAll()));
            $this->assertEquals(1, Currency::getCount());
        }

        /**
         * @depends testGetAllMakesBaseCurrency
         */
        public function testGetByCode()
        {
            $currency = Currency::getByCode('USD');
            $this->assertEquals('USD', $currency->code);
            try
            {
                Currency::getByCode('XAT');
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        public function testCreateCurrencyAndIsActiveByDefaultAndSettingActiveToFalse()
        {
            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 2;
            $this->assertTrue($currency->save());
            $currency->forget();

            $currency = Currency::getByCode('EUR');
            $this->assertEquals(1, $currency->active);

            $currency->active     = false;
            $this->assertTrue($currency->save());
            $currency->forget();

            $currency = Currency::getByCode('EUR');
            $this->assertEquals(0, $currency->active);
        }
    }
?>
