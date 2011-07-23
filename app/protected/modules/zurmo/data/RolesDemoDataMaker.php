<?php
    /**
     * Class that builds demo roles.
     */
    class RolesDemoDataMaker extends DemoDataMaker
    {
        protected $quantity;

        public static function getDependencies()
        {
            return array();
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');

            $executive = new Role();
            $executive->name = 'Executive';
            $saved = $executive->save();
            assert('$saved');
            $demoDataByModelClassName['Role'][] = $executive;

            $manager = new Role();
            $manager->name = 'Manager';
            $manager->role = $executive;
            $saved = $manager->save();
            assert('$saved');
            $demoDataByModelClassName['Role'][] = $manager;

            $associate = new Role();
            $associate->name = 'Associate';
            $associate->role = $manager;
            $saved = $associate->save();
            assert('$saved');
            $demoDataByModelClassName['Role'][] = $associate;
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