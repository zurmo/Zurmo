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

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $executive = new Role();
            $executive->name = 'Executive';
            $saved = $executive->save();
            assert('$saved');

            $manager = new Role();
            $manager->name = 'Manager';
            $manager->role = $executive;
            $saved = $manager->save();
            assert('$saved');

            $associate = new Role();
            $associate->name = 'Associate';
            $associate->role = $manager;
            $saved = $associate->save();
            assert('$saved');

            $demoDataHelper->setRangeByModelName('Role', $executive->id, $associate->id);
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }
    }
?>