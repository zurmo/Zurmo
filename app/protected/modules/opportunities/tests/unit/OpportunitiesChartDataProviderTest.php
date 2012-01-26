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

    class OpportunitiesChartDataProviderTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            UserTestHelper::createBasicUser('jim');
            ReadPermissionsOptimizationUtil::rebuild();
            OpportunityTestHelper::createOpportunityStagesIfDoesNotExist();
            OpportunityTestHelper::createOpportunitySourcesIfDoesNotExist();
            $currencies    = Currency::getAll();
            //Make opportunities for testing chart data.
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 200;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner          = $super;
            $opportunity->name           = 'abc1';
            $opportunity->amount         = $currencyValue;
            $opportunity->closeDate      = '2011-01-01';
            $opportunity->stage->value   = 'Negotiating';
            $opportunity->source->value  = 'Outbound';
            assert($opportunity->save()); // Not Coding Standard
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 350;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner          = $super;
            $opportunity->name           = 'abc2';
            $opportunity->amount         = $currencyValue;
            $opportunity->closeDate      = '2011-01-01';
            $opportunity->stage->value   = 'Negotiating';
            $opportunity->source->value  = 'Trade Show';
            assert($opportunity->save()); // Not Coding Standard
            $currencyValue = new CurrencyValue();
            $currencyValue->value = 100;
            $currencyValue->currency = $currencies[0];
            $opportunity = new Opportunity();
            $opportunity->owner          = $super;
            $opportunity->name           = 'abc2';
            $opportunity->amount         = $currencyValue;
            $opportunity->closeDate      = '2011-01-01';
            $opportunity->stage->value   = 'Verbal';
            $opportunity->source->value  = 'Trade Show';
            assert($opportunity->save()); // Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetChartData()
        {
            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesByStage');
            $chartData             = $chartDataProvider->getChartData();
            $compareData           = array( array('value' => 550, 'displayLabel' => 'Negotiating'),
                                            array('value' => 100, 'displayLabel' => 'Verbal'));
            $this->assertEquals($compareData, $chartData);

            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesBySource');
            $chartData             = $chartDataProvider->getChartData();
            $compareData           = array( array('value' => 200, 'displayLabel' => 'Outbound'),
                                            array('value' => 450, 'displayLabel' => 'Trade Show'));
            $this->assertEquals($compareData, $chartData);
        }

        /**
         * @depends testGetChartData
         */
        public function testGetChartDataUsingReadOptimization()
        {
            $jim                        = User::getByUsername('jim');
            Yii::app()->user->userModel = $jim;
            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesByStage');
            $chartData             = $chartDataProvider->getChartData();
            $this->assertEquals(array(), $chartData);

            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesBySource');
            $chartData             = $chartDataProvider->getChartData();
            $this->assertEquals(array(), $chartData);
        }

        /**
         * @depends testGetChartDataUsingReadOptimization
         */
        public function testGetChartDataConvertedToNewCurrency()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertNull($super->currency->code);

            //Make a new currency and assign to the current user.
            $currency             = new Currency();
            $currency->code       =  'EUR';
            $currency->rateToBase = .5; //I wish...
            $this->assertTrue($currency->save());
            $super->currency = $currency;
            $this->assertTrue($super->save());

            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesByStage');
            $chartData             = $chartDataProvider->getChartData();
            $compareData           = array( array('value' => 1100, 'displayLabel' => 'Negotiating'),
                                            array('value' => 200, 'displayLabel' => 'Verbal'));
            $this->assertEquals($compareData, $chartData);

            $chartDataProvider     = ChartDataProviderFactory::createByType('OpportunitiesBySource');
            $chartData             = $chartDataProvider->getChartData();
            $compareData           = array( array('value' => 400, 'displayLabel' => 'Outbound'),
                                            array('value' => 900, 'displayLabel' => 'Trade Show'));
            $this->assertEquals($compareData, $chartData);
        }
    }
?>
