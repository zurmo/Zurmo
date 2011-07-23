<?php
    /**
     * Class that builds demo groups.
     */
    class GroupsDemoDataMaker extends DemoDataMaker
    {
        protected $quantity;

        public static function getDependencies()
        {
            return array('zurmo');
        }

        public function makeAll(& $demoDataByModelClassName)
        {
            assert('is_array($demoDataByModelClassName)');


            $group1 = new Group();
            $group1->name = 'East';
            $saved = $group1->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group1;

            $group2 = new Group();
            $group2->name = 'West';
            $saved = $group2->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group2;

            $group3 = new Group();
            $group3->name  = 'East Channel Sales';
            $group3->group = $group1;
            $saved = $group3->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group3;

            $group4 = new Group();
            $group4->name  = 'West Channel Sales';
            $group4->group = $group2;
            $saved = $group4->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group4;

            $group5 = new Group();
            $group5->name  = 'East Direct Sales';
            $group5->group = $group1;
            $saved = $group5->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group5;

            $group6 = new Group();
            $group6->name  = 'West Direct Sales';
            $group6->group = $group2;
            $saved = $group6->save();
            assert('$saved');
            $demoDataByModelClassName['Group'][] = $group6;
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