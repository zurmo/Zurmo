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

    class ZurmoCurrencyHelperTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testGetConversionRateToBase()
        {
            $currency = Yii::app()->currencyHelper;
            $this->assertEquals('USD', $currency->getBaseCode());
            $rate = $currency->getConversionRateToBase('EUR');
            $this->assertNull($currency->getWebServiceErrorMessage());
            $this->assertNull($currency->getWebServiceErrorCode());
            $this->assertWithinTolerance($rate, 1, 2);

            //Now test with an invalid currency
            $this->assertEquals('USD', $currency->getBaseCode());
            $rate = $currency->getConversionRateToBase('ACODETHATDOESNTEXIST');
            $this->assertNotNull($currency->getWebServiceErrorMessage());
            $this->assertEquals($currency::ERROR_INVALID_CODE, $currency->getWebServiceErrorCode());
            $this->assertEquals(1, 1);
        }

        /**
         * @depends testGetConversionRateToBase
         */
        public function testGetCodeForCurrentUserForDisplay()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertNull($super->currency->code);
            $currencyHelper = Yii::app()->currencyHelper;
            $this->assertEquals('USD', $currencyHelper->getCodeForCurrentUserForDisplay());

            //Make a new currency and assign to the current user.
            $currency             = new Currency();
            $currency->code       =  'EUR';
            $currency->rateToBase = 1.5;
            $this->assertTrue($currency->save());
            $super->currency = $currency;
            $this->assertTrue($super->save());
            $this->assertEquals('EUR', $currencyHelper->getCodeForCurrentUserForDisplay());
        }

        /**
         * @depends testGetCodeForCurrentUserForDisplay
         */
        public function testGetActiveCurrenciesOrSelectedCurrenciesData()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Make a second currency for this test.
            $currency             = new Currency();
            $currency->code       = 'USD';
            $currency->rateToBase = 1;
            $this->assertTrue($currency->save());
            $super->currency = $currency;
            $this->assertTrue($super->save());

            $currencies = Currency::getAll();
            $this->assertEquals(2, count($currencies));
            $activeCurrencies = Yii::app()->currencyHelper->getActiveCurrenciesOrSelectedCurrenciesData(null);
            $this->assertEquals(2, count($activeCurrencies));

            $currency = Currency::getByCode('EUR');
            $currency->active = 0;
            $this->assertTrue($currency->save());

            //There should only be one active currency at this point.
            $activeCurrencies = Yii::app()->currencyHelper->getActiveCurrenciesOrSelectedCurrenciesData(null);
            $this->assertEquals(1, count($activeCurrencies));

            //Confirm that there are 2 active currencies when specifying an inactive one as the selected currency.
            $activeCurrencies = Yii::app()->currencyHelper->getActiveCurrenciesOrSelectedCurrenciesData($currency->id);
            $this->assertEquals(2, count($activeCurrencies));
        }

        /**
         * @depends testGetActiveCurrenciesOrSelectedCurrenciesData
         */
        public function testGetActiveCurrencyForCurrentUser()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $activeCurrency             = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $usdCurrency                = Currency::getByCode('USD');
            $this->assertTrue($activeCurrency->isSame($usdCurrency));

            $eurCurrency                = Currency::getByCode('EUR');
            $super->currency            = $eurCurrency;
            $this->assertTrue($super->save());
            $activeCurrency             = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $this->assertTrue($activeCurrency->isSame($eurCurrency));

            $super->currency            = null;
            $this->assertTrue($super->save());
            $activeCurrency             = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $this->assertTrue($activeCurrency->isSame($usdCurrency));
        }
    }
?>