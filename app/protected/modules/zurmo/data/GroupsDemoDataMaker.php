<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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