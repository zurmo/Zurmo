<?php
    /**
     * Class that builds demo groups.
     */
    class GroupsDemoDataMaker extends DemoDataMaker
    {
        public static function getDependencies()
        {
            return array('zurmo');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');

            $group1 = new Group();
            $group1->name = 'East';
            $saved = $group1->save();
            assert('$saved');

            $group2 = new Group();
            $group2->name = 'West';
            $saved = $group2->save();
            assert('$saved');

            $group3 = new Group();
            $group3->name  = 'East Channel Sales';
            $group3->group = $group1;
            $saved = $group3->save();
            assert('$saved');

            $group4 = new Group();
            $group4->name  = 'West Channel Sales';
            $group4->group = $group2;
            $saved = $group4->save();
            assert('$saved');

            $group5 = new Group();
            $group5->name  = 'East Direct Sales';
            $group5->group = $group1;
            $saved = $group5->save();
            assert('$saved');

            $group6 = new Group();
            $group6->name  = 'West Direct Sales';
            $group6->group = $group2;
            $saved = $group6->save();
            assert('$saved');

            $demoDataHelper->setRangeByModelName('Group', $group1->id, $group6->id);
        }

        public function populateModel(& $model)
        {
            throw notImplementedException();
        }
    }
?>