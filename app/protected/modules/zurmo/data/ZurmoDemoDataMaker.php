<?php
    /**
     * Class that builds zurmo demo data models.
     */
    class ZurmoDemoDataMaker extends DemoDataMaker
    {
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');

            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 1.5;
            $saved = $currency->save();
            assert('$saved');
            $currency = new Currency();
            $currency->code       = 'CAD';
            $currency->rateToBase = 1.1;
            $saved = $currency->save();
            assert('$saved');
            $currency = new Currency();
            $currency->code       = 'YEN';
            $currency->rateToBase = .75;
            $saved = $currency->save();
            assert('$saved');

            $currencies = Currency::getAll('id');
            $demoDataHelper->setRangeByModelName('Currency', $currencies[0]->id, $currencies[count($currencies)-1]->id);
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }
    }
?>