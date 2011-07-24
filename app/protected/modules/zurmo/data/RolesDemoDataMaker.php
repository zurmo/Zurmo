<?php
    /**
     * Class that builds base demo roles.
     */
    class RolesDemoDataMaker extends DemoDataMaker
    {
        public static function getDependencies()
        {
            return array('zurmo');
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
    }
?>