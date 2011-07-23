<?php
    /**
     * Class that builds demo roles.
     */
    class RolesDemoDataMaker extends DemoDataMaker
    {
        protected $quantity;

        public static function getDependencies()
        {
            return array('zurmo');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');

            $currency = new Currency();
            $currency->code       = 'EUR';
            $currency->rateToBase = 1.5;
            $saved = $currency->save();
            $this->assertTrue($saved);
            $currency = new Currency();
            $currency->code       = 'CAD';
            $currency->rateToBase = 1.1;
            $saved = $currency->save();
            $this->assertTrue($saved);
            $currency = new Currency();
            $currency->code       = 'YEN';
            $currency->rateToBase = .75;
            $saved = $currency->save();
            $this->assertTrue($saved);
            $demoDataByModelClassName['Currency'] = Currencies::getAll();
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }

        public function setQuantity($quantity)
        {
            throw notImplementedException();
        }
    }
?>