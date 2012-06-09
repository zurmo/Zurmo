<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * Test class for polymorphic relationships. An example is files. You can have files related to a single item, could
     * be a note or a comment or conversation. But on the file itself, there are 2 columns, one to describe this relationship
     * and the other for the linking id.
     */
    class RedBeanModelPolyOneToManyTest extends BaseTest
    {
        public function testPolyOneToManyNotOwned()
        {
            $polySide = new TestPolyOneToManyPolySide();
            $polySide->name = 'polySideTest';

            $oneSide = new TestPolyOneToManyOneSide();
            $oneSide->name  = 'oneSideTest';
            $oneSide->polys->add($polySide);
            $this->assertTrue($oneSide->save());

            $polySideId = $polySide->id;
            $this->assertTrue($polySideId > 0);

            $oneSideId = $oneSide->id;
            $oneSide->forget();
            unset($oneSide);

            $polySide2 = new TestPolyOneToManyPolySide();
            $polySide2->name = 'polySideTest2';

            $oneSide2 = new TestPolyOneToManyOneSideTwo();
            $oneSide2->name  = 'oneSideTwoTest';
            $oneSide2->polysTwo->add($polySide2);
            $this->assertTrue($oneSide2->save());

            $polySide2Id = $polySide2->id;
            $this->assertTrue($polySide2Id > 0);

            $oneSide2Id = $oneSide2->id;
            $oneSide2->forget();
            unset($oneSide2);

            //Get oneSide and make sure it has one polySide that matches the appropriate id
            $oneSide = TestPolyOneToManyOneSide::getById($oneSideId);
            $this->assertEquals(1, $oneSide->polys->count());
            $this->assertEquals($polySideId, $oneSide->polys[0]->id);

            //Get oneSide2 and make sure it has one polySide2 that matches the appropriate id
            $oneSide2 = TestPolyOneToManyOneSideTwo::getById($oneSide2Id);
            $this->assertEquals(1, $oneSide2->polysTwo->count());
            $this->assertEquals($polySide2Id, $oneSide2->polysTwo[0]->id);

            //do a direct sql to get the row for polySide
            $row = R::getRow('select * from testpolyonetomanypolyside');
            $this->assertTrue(!isset($row['testpolyonetomanyoneside_id']));
            $this->assertTrue(!isset($row['testpolyonetomanyonesidetwo_id']));
            //Confirm the poly type and poly id columns are there.
            $this->assertTrue(isset($row['polytest_type']));
            $this->assertTrue(isset($row['polytest_id']));

            //test adding an extra PolySide to oneSide
            $polySide3 = new TestPolyOneToManyPolySide();
            $polySide3->name = 'polySideTest3';
            $oneSide->polys->add($polySide3);
            $this->assertTrue($oneSide->save());
            $polySide3Id = $polySide3->id;
            $oneSide->forget();
            unset($oneSide);

            //Now test there are 2 related polys
            $oneSide = TestPolyOneToManyOneSide::getById($oneSideId);
            $this->assertEquals(2, $oneSide->polys->count());
            $this->assertEquals($polySideId, $oneSide->polys[0]->id);
            $this->assertEquals($polySide3Id, $oneSide->polys[1]->id);

            //test disconnect a polySide
            $polySide = $oneSide->polys[0];
            $oneSide->polys->remove($polySide);
            $this->assertTrue($oneSide->save());

            //Now test there is 1 related polys
            $oneSide = TestPolyOneToManyOneSide::getById($oneSideId);
            $this->assertEquals(1, $oneSide->polys->count());
            $this->assertEquals($polySide3Id, $oneSide->polys[0]->id);

            //test delete the oneSide, polySide should remain
            $this->assertEquals(3, count(TestPolyOneToManyPolySide::getAll()));
            $this->assertTrue($oneSide->delete());
            $this->assertEquals(3, count(TestPolyOneToManyPolySide::getAll()));
            foreach (TestPolyOneToManyPolySide::getAll() as $poly)
            {
               $poly->delete();
            }
            $this->assertEquals(0, count(TestPolyOneToManyPolySide::getAll()));
        }

        /**
         * @depends testPolyOneToManyNotOwned
         */
        public function testPolyOneToManyOwned()
        {
            $this->assertEquals(0, count(TestPolyOneToManyPolySide::getAll()));

            $polySide = new TestPolyOneToManyPolySideOwned();
            $polySide->name = 'polySideTest';

            $oneSide = new TestPolyOneToManyOneSide();
            $oneSide->name  = 'oneSideTest';
            $oneSide->ownedPolys->add($polySide);
            $this->assertTrue($oneSide->save());

            $polySideId = $polySide->id;
            $this->assertTrue($polySideId > 0);

            $oneSideId = $oneSide->id;
            $oneSide->forget();
            unset($oneSide);

            $polySide2 = new TestPolyOneToManyPolySideOwned();
            $polySide2->name = 'polySideTest2';

            $oneSide2 = new TestPolyOneToManyOneSideTwo();
            $oneSide2->name  = 'oneSideTwoTest';
            $oneSide2->ownedPolysTwo->add($polySide2);
            $this->assertTrue($oneSide2->save());

            $polySide2Id = $polySide2->id;
            $this->assertTrue($polySide2Id > 0);

            $oneSide2Id = $oneSide2->id;
            $oneSide2->forget();
            unset($oneSide2);

            $this->assertEquals(0, count(TestPolyOneToManyPolySide::getAll()));
            $this->assertEquals(2, count(TestPolyOneToManyPolySideOwned::getAll()));

            //Get oneSide and make sure it has one polySide that matches the appropriate id
            $oneSide = TestPolyOneToManyOneSide::getById($oneSideId);
            $this->assertEquals(1, $oneSide->ownedPolys->count());
            $this->assertEquals($polySideId, $oneSide->ownedPolys[0]->id);

            //Get oneSide2 and make sure it has one polySide2 that matches the appropriate id
            $oneSide2 = TestPolyOneToManyOneSideTwo::getById($oneSide2Id);
            $this->assertEquals(1, $oneSide2->ownedPolysTwo->count());
            $this->assertEquals($polySide2Id, $oneSide2->ownedPolysTwo[0]->id);

            $this->assertTrue($oneSide->delete());
            $this->assertEquals(1, count(TestPolyOneToManyPolySideOwned::getAll()));
            $this->assertTrue($oneSide2->delete());
            $this->assertEquals(0, count(TestPolyOneToManyPolySideOwned::getAll()));
        }
    }
?>
