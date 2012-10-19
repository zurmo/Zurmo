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

    /*
        These tests use classes in the tests/models directory which
        have this inheritance structure to test the RedBeanModel class.

        H has a name and is-a RedBeanModel.
        I, J, K, L are Hs.
        I has-a J.
        I has-many Ks.
        I is many-many with L.
    */

    class RedBeanModelRelationsTest extends BaseTest
    {
        const MANY_COUNT = 10;

        public function testSetupAllKindsOfRelations()
        {
            // Give an I... a J, a bunch of Ks, and a bunch of Ls.
            $firstI = new I();
            $firstI->name = 'The First I!';
            $firstI->j = new J();
            $firstI->j->name = 'The J!';
            for ($i = 0; $i < self::MANY_COUNT; $i++)
            {
                $k = new K();
                $l = new L();
                $k->name = "K-$i";
                $l->name = "L-$i";
                $firstI->ks->add($k);
                $firstI->ls->add($l);
                // Both sides of a MANY MANY need to be saved.
                $this->assertTrue($l->save());
            }
            // The ks, being on a ONE MANY get saved along with the ONE.
            $this->assertTrue($firstI->save());

            // Give a second I... one of the Ls that the I has.
            $secondI = new I();
            $secondI->name = 'The Second I!';
            $secondI->ls->add($firstI->ls[0]);
            $this->assertTrue($secondI->save());

            // So the L is related to two Is - because it is a M:N.
        }

        /**
         * @depends testSetupAllKindsOfRelations
         */
        public function testOneToOneAndBelongsToRelations()
        {
            $firstI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals ('The First I!', $firstI->name);
            $this->assertNotNull(                $firstI->j);
            $this->assertEquals ('The J!',       $firstI->j->name);
            $this->assertNotNull(                $firstI->j->i);
            $this->assertTrue   (                $firstI->isSame($firstI->j->i));
            $this->assertEquals ('The First I!', $firstI->j->i->name);
            $this->assertTrue   ($firstI ===     $firstI->j->i);
        }

        /**
         * @depends testOneToOneAndBelongsToRelations
         */
        public function testGetErrorsOnOneToOneRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertEquals(array(), $theI->j->getErrors());
        }

        /**
         * @depends testOneToOneAndBelongsToRelations
         */
        public function testValidateAndGetErrorsOnOneToOneRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue($theI->validate());
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertTrue($theI->j->validate());
            $this->assertEquals(array(), $theI->j->getErrors());
        }

        /**
         * @depends testValidateAndGetErrorsOnOneToOneRelationsWhenThereAreNoErrors
         */
        public function testValidateAndGetErrorsOnOneToOneRelationsWhenThereAreErrorsOnBothSides()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->validate());
            $this->assertEquals(
                array('name' => array('Name is too long (maximum is 15 characters).')),
                $theI->getErrors());
            $this->assertTrue ($theI->j->validate());
            $this->assertEquals(array(), $theI->j->getErrors());
            $theI->name = 'The First I!';
            $theI->j->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->validate());
            $this->assertEquals(
                array('j' => array('name' => array('Name is too long (maximum is 15 characters).'))),
                $theI->getErrors());
            $this->assertFalse($theI->j->validate());
            $this->assertEquals(
                array('name' => array('Name is too long (maximum is 15 characters).')),
                $theI->j->getErrors());
            $theI->j->name = 'The J!';
            $this->assertTrue($theI->validate());
            $this->assertTrue($theI->j->validate());
        }

        /**
         * @depends testValidateAndGetErrorsOnOneToOneRelationsWhenThereAreErrorsOnBothSides
         */
        public function testSaveOnOneToOneRelationsWithAndWithoutErrorsAndValidation()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue ($theI->save());

            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->save());
            $this->assertTrue ($theI->save(false));
            $theI->name = 'The First I!';
            $this->assertTrue ($theI->save());

            $theI->j->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->save());
            $this->assertTrue ($theI->save(false));
            $theI->j->name = 'The J!';
            $this->assertTrue ($theI->save());

            $theI->name = 'Tooooooo looooooong';
            $this->assertTrue ($theI->j->save());
            $this->assertTrue ($theI->j->save(false));
            $theI->name = 'The First I!';
            $this->assertTrue ($theI->j->save());
            $this->assertTrue ($theI->j->save(false));
        }

        /**
         * @depends testSetupAllKindsOfRelations
         */
        public function testOneToManyAndBelongsToRelations()
        {
            $firstI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals(self::MANY_COUNT, $firstI->ks->count());
            for ($i = 0; $i < self::MANY_COUNT; $i++)
            {
                $this->assertEquals("K-$i",         $firstI->ks[$i]->name);
                $this->assertEquals("The First I!", $firstI->ks[$i]->i->name);
                $this->assertTrue  ($firstI->isSame($firstI->ks[$i]->i));
                $this->assertTrue  ($firstI ===     $firstI->ks[$i]->i);
            }
        }

        /**
         * @depends testOneToManyAndBelongsToRelations
         */
        public function testGetErrorsOnOneToManyRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertEquals(array(), $theI->ks->getErrors());
        }

        /**
         * @depends testOneToManyAndBelongsToRelations
         */
        public function testValidateAndGetErrorsOnOneToManyRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue($theI->validate());
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertTrue($theI->ks->validate());
            $this->assertEquals(array(), $theI->ks->getErrors());
        }

        /**
         * @depends testValidateAndGetErrorsOnOneToManyRelationsWhenThereAreNoErrors
         */
        public function testValidateAndGetErrorsOnOneToManyRelationsWhenThereAreErrorsOnBothSides()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->validate());
            $this->assertEquals(
                array('name' => array('Name is too long (maximum is 15 characters).')),
                $theI->getErrors());
            $this->assertTrue ($theI->ks->validate());
            $this->assertEquals(array(), $theI->ks->getErrors());
            $theI->name = 'The First I!';
            $theI->ks[0]->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->validate());
            $this->assertEquals(
                array('ks' => array(0 => array('name' => array('Name is too long (maximum is 15 characters).')))),
                $theI->getErrors());
            $this->assertFalse($theI->ks[0]->validate());
            $this->assertEquals(
                array(0 => array('name' => array('Name is too long (maximum is 15 characters).'))),
                $theI->ks->getErrors());
            $theI->ks[0]->name = 'K-0';
            $this->assertTrue($theI->validate());
            $this->assertTrue($theI->ks->validate());
        }

        /**
         * @depends testValidateAndGetErrorsOnOneToManyRelationsWhenThereAreErrorsOnBothSides
         */
        public function testSaveOnOneToManyRelationsWithAndWithoutErrorsAndValidation()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue ($theI->save());

            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->save());
            $this->assertTrue ($theI->save(false));
            $theI->name = 'The First I!';
            $this->assertTrue ($theI->save());

            $theI->ks[0]->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->save());
            $this->assertTrue ($theI->save(false));
            $theI->ks[0]->name = 'K-0';
            $this->assertTrue ($theI->save());

            $this->assertTrue ($theI->ks->save());
        }

        /**
         * @depends testSetupAllKindsOfRelations
         */
        public function testManyToManyRelations()
        {
            $firstI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals(self::MANY_COUNT, $firstI->ls->count());
            for ($i = 0; $i < self::MANY_COUNT; $i++)
            {
                $this->assertEquals("L-$i", $firstI->ls[$i]->name);
            }
            for ($i = 1; $i < self::MANY_COUNT; $i++)
            {
                $this->assertEquals(1, $firstI->ls[$i]->is->count());
            }
            $this->assertEquals(2, $firstI->ls[0]->is->count());

            $secondI = H::getByName('The Second I!')->castDown(array('I'));
            $this->assertEquals(1,               $secondI->ls->count());
            $this->assertEquals("L-0",           $secondI->ls[0]->name);
            $this->assertEquals(2,               $secondI->ls[0]->is->count());
            $this->assertEquals("The First I!",  $secondI->ls[0]->is[0]->name);
            $this->assertEquals("The Second I!", $secondI->ls[0]->is[1]->name);
            $this->assertTrue  ($firstI ->isSame($secondI->ls[0]->is[0]));
            $this->assertTrue  ($secondI->isSame($secondI->ls[0]->is[1]));
            $this->assertTrue  ($firstI  ===     $secondI->ls[0]->is[0]);
            $this->assertTrue  ($secondI ===     $secondI->ls[0]->is[1]);
        }

        /**
         * @depends testManyToManyRelations
         */
        public function testGetErrorsOnManyToManyRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertEquals(array(), $theI->ls->getErrors());
        }

        /**
         * @depends testManyToManyRelations
         */
        public function testValidateAndGetErrorsOnManyToManyRelationsWhenThereAreNoErrors()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue($theI->validate());
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertTrue($theI->ls->validate());
            $this->assertEquals(array(), $theI->ks->getErrors());
        }

        /**
         * @depends testValidateAndGetErrorsOnManyToManyRelationsWhenThereAreNoErrors
         */
        public function testValidateAndGetErrorsOnManyToManyRelationsWhenThereAreErrorsOnBothSides()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->validate());
            $this->assertEquals(
                array('name' => array('Name is too long (maximum is 15 characters).')),
                $theI->getErrors());
            $this->assertTrue ($theI->ls->validate());
            $this->assertEquals(array(), $theI->ls->getErrors());
            $theI->name = 'The First I!';
            $theI->ls[0]->name = 'Tooooooo looooooong';
            $this->assertTrue ($theI->validate());     // Validate does not follow M:N relations.
            $this->assertTrue ($theI->ls->validate()); // Because the many many collection doesn't validate.
            $this->assertEquals(array(), $theI->getErrors());
            $this->assertFalse($theI->ls[0]->validate());
            $this->assertEquals(
                array(0 => array('name' => array('Name is too long (maximum is 15 characters).'))),
                $theI->ls->getErrors());
            $theI->ls[0]->name = 'L-0';
            $this->assertTrue ($theI->validate());
            $this->assertTrue ($theI->ls->validate());
            $this->assertTrue ($theI->ls[0]->validate());
        }

        /**
         * @depends testValidateAndGetErrorsOnManyToManyRelationsWhenThereAreErrorsOnBothSides
         */
        public function testSaveOnManyToManyRelationsWithAndWithoutErrorsAndValidation()
        {
            $theI = H::getByName('The First I!')->castDown(array('I'));
            $this->assertTrue ($theI->save());

            $theI->name = 'Tooooooo looooooong';
            $this->assertFalse($theI->save());
            $this->assertTrue ($theI->save(false));
            $theI->name = 'The First I!';
            $this->assertTrue ($theI->save());

            $theI->ls[0]->name = 'Tooooooo looooooong';
            $this->assertTrue ($theI->save()); // Validate does not follow M:N relations.

            $theI->ls[0]->forget();
            H::getByName('L-0'); // It is still there in the database unsaved.

            $this->assertFalse($theI->ls[0]->save());
            $this->assertTrue ($theI->ls[0]->save(false));

            $theI->ls[0]->name = 'L-0';
            $this->assertTrue ($theI->ls[0]->save());
            $this->assertTrue ($theI->ls[0]->save(false));

            $this->assertTrue ($theI->ls->save());
        }

        /**
         * @depends testOneToManyAndBelongsToRelations
         * @depends testManyToManyRelations
         */
        public function testForgettingModelsAndManyRelations()
        {
            $firstIAsH = H::getByName('The First I!');
            $firstI    = $firstIAsH->castDown(array('I'));
            $this->assertEquals(self::MANY_COUNT, $firstI->ks->count());
            $this->assertEquals(self::MANY_COUNT, $firstI->ls->count());

            $firstIAsH->forget();
            $firstI   ->forget();
            unset($firstIAsH);
            unset($firstI);

            $firstIAsH = H::getByName('The First I!');
            $firstI    = $firstIAsH->castDown(array('I'));
            $this->assertEquals(self::MANY_COUNT, $firstI->ks->count());
            $this->assertEquals(self::MANY_COUNT, $firstI->ls->count());
        }

        /**
         * MemberOf/Members attribute names are i and is.
         */
        public function testMemberMemberOfRelation()
        {
            $i1 = new I();
            $i2 = new I();
            $i3 = new I();
            $i1->is->add($i2);
            $i1->is->add($i3);
            $this->assertTrue($i1->save());
            $this->assertTrue($i2->id > 0);
            $this->assertTrue($i3->id > 0);
            $this->assertTrue($i2->isSame($i1->is[0]));
            $this->assertTrue($i3->isSame($i1->is[1]));
            $i1Id = $i1->id;
            $i2Id = $i2->id;
            $i3Id = $i3->id;
            $i1->forget();
            $i2->forget();
            $i3->forget();
            $i1 = I::getById($i1Id);
            $i2 = I::getById($i2Id);
            $i3 = I::getById($i3Id);
            $this->assertTrue($i1->isSame($i2->i));
            $this->assertTrue($i1->isSame($i3->i));

            $this->assertEquals(0, $i3->is->count());
            $i4 = new I();
            $i4->i = $i3;
            $this->assertTrue($i4->save());
            $i3Id = $i3->id;
            $i3->forget();
            $i3 = I::getById($i3Id);
            $this->assertEquals(1, $i3->is->count());
        }

        public function testChangingBelongsToSideOfHasManyRelation()
        {
            $k1 = new K();
            $k2 = new K();

            $i = new I();
            $i->ks->add($k1);
            $i->ks->add($k2);
            $this->assertTrue($i->save());
            $this->assertEquals(2, $i->ks->count());

            $k1->i = null;
            $this->assertTrue($k1->save());

            $iId = $i->id;
            $i->forget();
            unset($i);

            $i = I::getById($iId);
            $this->assertEquals(1, $i->ks->count());

            $i->ks->removeByIndex(0);
            $this->assertTrue($i->save());
            $this->assertEquals(0, $i->ks->count());

            $k2Id = $k2->id;
            $k2->forget();
            unset($k2);

            $k2 = K::getById($k2Id);
            $this->assertTrue($k2->i->id < 0);
        }
    }
?>
