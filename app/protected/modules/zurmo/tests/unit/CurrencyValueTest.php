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

    class CurrencyValueTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testThatValidateCurrencyValueDoesntLeaveRateToBaseNull()
        {
            $currencyValue = new CurrencyValue();
            $currencyValue->value                = 69.0;
            $currencyValue->rateToBase           = 1.0;
            $currencyValue->currency->code       = 'BTC';
            $currencyValue->currency->rateToBase = 1.0;
            $currencyValue->validate();
            $this->assertNotNull($currencyValue->rateToBase);
        }

        public function testThatValidateOpportunityDoesntLeaveCurrencyValueRateToBaseNull()
        {
            $opportunity = new Opportunity();
            $opportunity->name                         = 'Thingo';
            $opportunity->stage->value                 = 'Starting Up';
            $opportunity->closeDate                    = '2008-10-05';
            $opportunity->amount->value                = 69.0;
            $opportunity->amount->currency->code       = 'BTC';
            $opportunity->amount->currency->rateToBase = 1.0;
            $opportunity->amount->validate();
            $this->assertNotNull($opportunity->amount->rateToBase);
        }

        public function testSetMemberCalledCurrencyToACurrencyDoesntMakeARowOfNulls()
        {
            $opportunity = new Opportunity();
            $opportunity->name                         = 'Thingo';
            $opportunity->stage->value                 = 'Starting Up';
            $opportunity->closeDate                    = '2008-10-05';
            $opportunity->amount->value                = 69.0;
            $opportunity->amount->currency->code       = 'BTC';
            $opportunity->amount->currency->rateToBase = 1.0;
            $this->assertTrue($opportunity->save());
            $opportunity->delete();
            $this->assertEquals(1, R::getCell('select count(*) from currency'));
            $currency = Currency::getByCode('BTC');
            $currency->delete();
            $currency->forget();
            $this->assertEquals(0, R::getCell('select count(*) from currency'));
        }

        public function testGetAndSetCurrencyValue()
        {
            $currencyHelper = Yii::app()->currencyHelper;
            $this->assertEquals('USD', $currencyHelper->getBaseCode());
            $this->assertEquals(0, Currency::getCount());
            $this->assertEquals(1, count(Currency::getAll()));
            $this->assertEquals(1, Currency::getCount());

            //create a currency value and confirm the rateToBase populates correctly.
            $opportunity = new Opportunity();
            $opportunity->name             = 'Tyfe';
            $opportunity->stage->value     = 'Starting Up';
            $opportunity->closeDate        = '2008-10-05';
            $opportunity->amount->value    = 456.78;

            //Setting the amount currency should not increase the currency table with a blank currency.
            $this->assertEquals(1, count(Currency::getAll()));
            $opportunity->amount->currency = Currency::getByCode('USD');
            $this->assertEquals(1, count(Currency::getAll()));

            $this->assertTrue($opportunity->save());
            $this->assertEquals(1, $opportunity->amount->rateToBase);
            $this->assertEquals(Currency::getByCode('USD'), $opportunity->amount->currency);
        }

        /**
         * @depends testGetAndSetCurrencyValue
         */
        public function testConstructDerivedWithUserDefaultCurrency()
        {
            $currentUser = Yii::app()->user->userModel;
            $currencyValue = new CurrencyValue();

            //Make a new currency and assign to the current user.
            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 1.5;
            $this->assertTrue($currency->save());
            $currentUser->currency = $currency;
            $this->assertTrue($currentUser->save());
            $this->assertEquals('EUR', Yii::app()->user->userModel->currency->code);

            $currencyHelper = Yii::app()->currencyHelper;
            $this->assertEquals('EUR', $currencyHelper->getCodeForCurrentUserForDisplay());

            $currencyValue = new CurrencyValue();
            $this->assertEquals('EUR', $currencyValue->currency->code);
        }

        /**
         * @depends testConstructDerivedWithUserDefaultCurrency
         */
        public function testIsCurrencyInUseByIdAndChangRateIfValueChangesOrCurrencyChanges()
        {
            $currencyHelper = Yii::app()->currencyHelper;
            $euro = Currency::getByCode('EUR');
            $this->assertFalse(CurrencyValue::isCurrencyInUseById($euro->id));
            $opportunity = new Opportunity();
            $opportunity->name             = 'Tyfe';
            $opportunity->stage->value     = 'Starting Up';
            $opportunity->closeDate        = '2008-10-05';
            $opportunity->amount->value    = 456.78;
            $opportunity->amount->currency = $euro;
            $this->assertTrue($opportunity->save());
            $this->assertEquals(1.5, $opportunity->amount->rateToBase);
            $this->assertTrue(CurrencyValue::isCurrencyInUseById($euro->id));

            //change the currency rate for the euro.
            $euro->rateToBase = 3;
            $this->assertTrue($euro->save());

            //Now test saving the opportunity again but not changing the amount value. The rate should stay the same.
            $id = $opportunity->id;
            unset($opportunity);
            $opportunity = Opportunity::getById($id);
            $this->assertEquals(456.78, $opportunity->amount->value);
            $opportunity->amount->value = 456.78;
            $this->assertTrue($opportunity->save());
            $this->assertEquals(456.78, $opportunity->amount->value);
            $this->assertEquals(1.5, $opportunity->amount->rateToBase);

            //Now change amount. the exchange rate should change.
            $opportunity->amount->value = 566.00;
            $this->assertTrue($opportunity->save());
            $this->assertEquals(3, $opportunity->amount->rateToBase);

            //Now change the currency only. should change rate.
            $id = $opportunity->id;
            unset($opportunity);
            $opportunity = Opportunity::getById($id);
            $opportunity->amount->currency = Currency::getByCode('USD');
            $this->assertTrue($opportunity->save());
            $this->assertEquals(1, $opportunity->amount->rateToBase);
        }
    }
?>
