<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class GroupedAttributeCountUtilTest extends BaseTest
    {
        public function testGetCountData()
        {
            $zModels = ZZ::getAll();
            $this->assertEquals(0, count($zModels));
            $this->makeModel(1, 1);
            $this->makeModel(1, 1);
            $this->makeModel(2, 1);
            $this->makeModel(2, 1);
            $this->makeModel(3, 1);
            $this->makeModel(1, 2);
            $this->makeModel(2, 2);
            $this->makeModel(2, 2);
            $this->makeModel(3, 2);
            $this->makeModel(3, 2);
            $data = GroupedAttributeCountUtil::getCountData('ZZ', 'a');
            $compareData = array(
                1 => 3,
                2 => 4,
                3 => 3,
            );
            $this->assertEquals($compareData, $data);

            $data = GroupedAttributeCountUtil::getCountData('ZZ', 'a', 'b', 2);
            $compareData = array(
                1 => 1,
                2 => 2,
                3 => 2,
            );
            $this->assertEquals($compareData, $data);
        }

        public function testFilteringByAttributeInDifferentTable()
        {
            $this->makeJModel(3, 5);
            $this->makeJModel(3, 6);
            $this->makeJModel(4, 5);
            $this->makeJModel(4, 6);
            $this->makeJModel(5, 6);
            $this->makeJModel(5, 6);
            $data = GroupedAttributeCountUtil::getCountData('J', 'jMember', 'name', 6);
            $this->assertEquals(array(3 => 1, 4 => 1, 5 => 2), $data);
        }

        protected function makeModel($a, $b)
        {
            $zz = new ZZ();
            $zz->a = $a;
            $zz->b = $b;
            $this->assertTrue($zz->save());
        }

        protected function makeJModel($jMember, $name)
        {
            $j = new J();
            $j->jMember = $jMember;
            $j->name = $name;
            $this->assertTrue($j->save());
        }
    }
?>
