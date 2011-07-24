<?php
    /**
     * Class that builds zurmo demo data models.
     */
    class ZurmoDemoDataMaker extends DemoDataMaker
    {
        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');

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
            $demoDataByModelClassName['Currency'] = Currency::getAll();
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }
    }
?>